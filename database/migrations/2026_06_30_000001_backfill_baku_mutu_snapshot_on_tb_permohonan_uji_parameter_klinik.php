<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 1. Tambah kolom snapshot baku mutu ke tb_permohonan_uji_parameter_klinik (idempoten).
 * 2. Backfill baris yang ke-lima kolomnya masih NULL dengan memilih baris baku mutu
 *    yang sesuai gender + usia pasien. Jika ada ≥2 baris is_normal=1 yang cocok,
 *    nilai min/max/equal/kesimpulan/keterangan_dilaporan dipisah koma.
 */
class BackfillBakuMutuSnapshotOnTbPermohonanUjiParameterKlinik extends Migration
{
    private array $snapshotColumns = [
        'min_baku_mutu_permohonan_uji_parameter_klinik',
        'max_baku_mutu_permohonan_uji_parameter_klinik',
        'equal_baku_mutu_permohonan_uji_parameter_klinik',
        'kesimpulan_baku_mutu_permohonan_uji_parameter_klinik',
        'keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik',
    ];

    // ---------------------------------------------------------------
    // DDL
    // ---------------------------------------------------------------
    public function up(): void
    {
        Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'min_baku_mutu_permohonan_uji_parameter_klinik')) {
                $table->string('min_baku_mutu_permohonan_uji_parameter_klinik', 100)->nullable();
            }
            if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'max_baku_mutu_permohonan_uji_parameter_klinik')) {
                $table->string('max_baku_mutu_permohonan_uji_parameter_klinik', 100)->nullable();
            }
            if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'equal_baku_mutu_permohonan_uji_parameter_klinik')) {
                $table->string('equal_baku_mutu_permohonan_uji_parameter_klinik', 100)->nullable();
            }
            if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'kesimpulan_baku_mutu_permohonan_uji_parameter_klinik')) {
                $table->string('kesimpulan_baku_mutu_permohonan_uji_parameter_klinik', 255)->nullable();
            }
            if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik')) {
                $table->text('keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik')->nullable();
            }
        });

        // Backfill hanya jika semua kolom sudah ada
        if (!$this->allSnapshotColumnsExist()) {
            return;
        }

        $this->backfill();
    }

    public function down(): void
    {
        // Down hanya menghapus kolom jika migrasi DDL sebelumnya (000004) belum ada,
        // sehingga aman untuk tidak menghapus di sini agar tidak konflik.
        // Data backfill tidak bisa di-rollback otomatis.
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------
    private function allSnapshotColumnsExist(): bool
    {
        foreach ($this->snapshotColumns as $col) {
            if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', $col)) {
                return false;
            }
        }
        return true;
    }

    private function backfill(): void
    {
        // Ambil semua parameter yang ke-5 kolomnya masih NULL
        // JOIN ke permohonan (untuk is_haji + umur) dan pasien (untuk gender + umur)
        // umur diambil dari permohonan; jika kosong hitung dari tgllahir pasien
        $rows = DB::table('tb_permohonan_uji_parameter_klinik as p')
            ->leftJoin('tb_permohonan_uji_klinik_2 as k', 'k.id_permohonan_uji_klinik', '=', 'p.permohonan_uji_klinik')
            ->leftJoin('ms_pasien as pas', 'pas.id_pasien', '=', 'k.pasien_permohonan_uji_klinik')
            ->whereNull('p.deleted_at')
            ->whereNull('p.min_baku_mutu_permohonan_uji_parameter_klinik')
            ->whereNull('p.max_baku_mutu_permohonan_uji_parameter_klinik')
            ->whereNull('p.equal_baku_mutu_permohonan_uji_parameter_klinik')
            ->whereNull('p.kesimpulan_baku_mutu_permohonan_uji_parameter_klinik')
            ->whereNull('p.keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik')
            ->whereNotNull('p.parameter_satuan_klinik')
            ->select([
                'p.id_permohonan_uji_parameter_klinik',
                'p.parameter_satuan_klinik',
                DB::raw('COALESCE(k.is_haji, 0) as is_haji'),
                DB::raw("COALESCE(pas.gender_pasien, '') as gender_pasien"),
                // umur: ambil dari permohonan; jika NULL hitung dari tgllahir pasien
                DB::raw("COALESCE(
                    k.umurtahun_pasien_permohonan_uji_klinik,
                    TIMESTAMPDIFF(YEAR, pas.tgllahir_pasien, CURDATE())
                ) as umur"),
            ])
            ->orderBy('p.id_permohonan_uji_parameter_klinik')
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        // Cache baku mutu per satuan agar tidak query ulang untuk setiap baris
        $bakuMutuCache = [];

        foreach ($rows as $row) {
            $satuanId = $row->parameter_satuan_klinik;
            $isHaji   = (int) $row->is_haji;
            $gender   = $this->normalizeGender($row->gender_pasien);
            $umur     = ($row->umur !== null && $row->umur !== '') ? (int) $row->umur : null;

            $cacheKey = $satuanId . '_' . $isHaji;
            if (!isset($bakuMutuCache[$cacheKey])) {
                $bakuMutuCache[$cacheKey] = $this->loadBakuMutu($satuanId, $isHaji);
            }
            $allBm = $bakuMutuCache[$cacheKey];

            if (empty($allBm)) {
                continue;
            }

            $matched = $this->resolveAllNormal($allBm, $gender, $umur);

            if (empty($matched)) {
                continue;
            }

            $update = [
                'min_baku_mutu_permohonan_uji_parameter_klinik'        => $this->joinField($matched, 'min'),
                'max_baku_mutu_permohonan_uji_parameter_klinik'        => $this->joinField($matched, 'max'),
                'equal_baku_mutu_permohonan_uji_parameter_klinik'      => $this->joinField($matched, 'equal'),
                'kesimpulan_baku_mutu_permohonan_uji_parameter_klinik' => $this->joinField($matched, 'kesimpulan_baku_mutu'),
                'keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik' => $this->resolveKeteranganForBackfill($allBm, $matched),
            ];

            // Juga update FK baku_mutu jika masih null (gunakan baris pertama)
            if (Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'baku_mutu_permohonan_uji_parameter_klinik')) {
                $update['baku_mutu_permohonan_uji_parameter_klinik'] = $matched[0]->id_baku_mutu ?? null;
            }

            DB::table('tb_permohonan_uji_parameter_klinik')
                ->where('id_permohonan_uji_parameter_klinik', $row->id_permohonan_uji_parameter_klinik)
                ->update($update);
        }
    }

    // ---------------------------------------------------------------
    // Logika gender/umur (mirror BakuMutuPermohonanKlinikHelper)
    // ---------------------------------------------------------------
    private function normalizeGender(?string $gender): ?string
    {
        if ($gender === null || $gender === '') {
            return null;
        }
        return in_array(strtoupper($gender), ['L', 'M', 'MALE'], true) ? 'L' : 'P';
    }

    private function loadBakuMutu(string $satuanId, int $isHaji): array
    {
        $query = DB::table('tb_baku_mutu')
            ->where('parameter_satuan_klinik_id', $satuanId)
            ->whereNull('deleted_at');

        if ($isHaji === 1) {
            $query->where('is_haji', 1);
        } else {
            $query->where(function ($q) {
                $q->where('is_haji', 0)->orWhereNull('is_haji');
            });
        }

        return $query->get([
            'id_baku_mutu',
            'min',
            'max',
            'equal',
            'nilai_baku_mutu',
            'kesimpulan_baku_mutu',
            'is_normal',
            'is_khusus_baku_mutu',
            'is_massal_nilai_di_laporan',
            'gender_baku_mutu',
            'minimal_umur_baku_mutu',
            'maksimal_umur_baku_mutu',
        ])->all(); // array of stdClass
    }

    /**
     * Kembalikan array semua baris is_normal=1 yang cocok gender+umur pasien.
     * Jika tidak ada is_normal=1, kembalikan satu baris fallback.
     */
    private function resolveAllNormal(array $allBm, ?string $gender, ?int $umur): array
    {
        // Pisah general (is_khusus=0) dan specific (is_khusus=1)
        $general  = array_values(array_filter($allBm, fn($b) => (int)($b->is_khusus_baku_mutu ?? 0) === 0));
        $specific = array_values(array_filter($allBm, fn($b) => (int)($b->is_khusus_baku_mutu ?? 0) === 1));

        if (empty($specific)) {
            $first = $this->genderFallback($general ?: $allBm, $gender);
            return $first ? [$first] : [];
        }

        if (count($specific) === 1) {
            return [$specific[0]];
        }

        $normalRows = array_values(array_filter($specific, fn($b) => (int)($b->is_normal ?? 0) === 1));

        if (empty($normalRows)) {
            $first = $this->genderFallback($specific, $gender);
            return $first ? [$first] : [];
        }

        // Filter yang cocok gender+umur
        $matched = array_values(array_filter($normalRows, function ($b) use ($gender, $umur) {
            $hasGender = isset($b->gender_baku_mutu) && $b->gender_baku_mutu !== null && $b->gender_baku_mutu !== '';
            $hasUmur   = isset($b->minimal_umur_baku_mutu) && isset($b->maksimal_umur_baku_mutu);

            if (!$hasGender && !$hasUmur) {
                return true; // tidak ada filter → selalu cocok
            }

            $genderOk = $hasGender && $b->gender_baku_mutu === $gender;
            $umurOk   = $hasUmur && $umur !== null
                && $b->minimal_umur_baku_mutu <= $umur
                && $b->maksimal_umur_baku_mutu >= $umur;

            return ($genderOk && $umurOk)
                || ($umurOk && !$hasGender)
                || ($genderOk && !$hasUmur);
        }));

        if (!empty($matched)) {
            return $matched;
        }

        // Fallback: gender saja
        if ($gender !== null) {
            $byGender = array_values(array_filter($normalRows, fn($b) => ($b->gender_baku_mutu ?? null) === $gender));
            if (!empty($byGender)) {
                return $byGender;
            }
        }

        // Fallback: umur saja
        $byUmur = array_values(array_filter($normalRows, function ($b) use ($umur) {
            $hasUmur = isset($b->minimal_umur_baku_mutu) && isset($b->maksimal_umur_baku_mutu);
            return !$hasUmur
                || ($umur !== null && $b->minimal_umur_baku_mutu <= $umur && $b->maksimal_umur_baku_mutu >= $umur);
        }));
        if (!empty($byUmur)) {
            return $byUmur;
        }

        return [$normalRows[0]];
    }

    private function genderFallback(array $list, ?string $gender): ?object
    {
        if (empty($list)) {
            return null;
        }
        if ($gender !== null) {
            foreach ($list as $b) {
                if (($b->gender_baku_mutu ?? null) === $gender) {
                    return $b;
                }
            }
        }
        foreach ($list as $b) {
            if (($b->gender_baku_mutu ?? null) === null || $b->gender_baku_mutu === '') {
                return $b;
            }
        }
        return $list[0];
    }

    private function joinField(array $rows, string $field): ?string
    {
        $values = [];
        foreach ($rows as $b) {
            $v = $b->$field ?? null;
            if ($v !== null && $v !== '') {
                $values[] = $v;
            }
        }
        if (empty($values)) {
            return null;
        }
        // Jika semua nilai sama, cukup satu
        $unique = array_unique($values);
        return implode(', ', $unique);
    }

    private function joinKeteranganField(array $rows): ?string
    {
        $values = [];
        foreach ($rows as $b) {
            $formatted = \Smt\Masterweb\Helpers\BakuMutuPermohonanKlinikHelper::keteranganDilaporanFromBakuMutuItem($b);
            if ($formatted !== null && $formatted !== '') {
                $values[] = $formatted;
            }
        }

        if (empty($values)) {
            return $this->joinField($rows, 'nilai_baku_mutu');
        }

        $unique = array_values(array_unique($values));
        if (count($unique) === 1) {
            return $unique[0];
        }

        return implode(', ', $unique);
    }

    private function resolveKeteranganForBackfill(array $allBm, array $matched): ?string
    {
        foreach ($allBm as $b) {
            if ((int) ($b->is_massal_nilai_di_laporan ?? 0) === 1) {
                foreach ($allBm as $row) {
                    if (!empty($row->nilai_baku_mutu)) {
                        return $row->nilai_baku_mutu;
                    }
                }
                break;
            }
        }

        return $this->joinKeteranganField($matched);
    }
}
