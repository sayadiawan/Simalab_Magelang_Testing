<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Models\GlobalLabSequence;

/**
 * Perbaiki nomor spesimen yang loncat ke 6000-an.
 *
 * Akar masalah (8 Agu 2026): sync GlobalLabSequence sempat memakai max(tb_lab_num.lab_number),
 * termasuk junk lab_number=6163 (sampel MM.02/3163/2026). Pendaftaran berikutnya dapat 6164+,
 * lalu import Haji Grabag 1 mode otomatis tampil 6194+.
 *
 * Strategi:
 * 1. Record manual (nourut 6xxx, spesimen tampil 34xx) → samakan nourut ke nomor_spesimen_manual
 * 2. Record otomatis Grabag / lainnya (nourut=noregister 6xxx) → isi ulang dari slot kosong >= 3606
 * 3. Rapikan lab_number/count_id kesmas yang >= 5000 agar selaras kode sampel
 * 4. Soft-delete booking sequence_detail >= 5000, relink ke nomor baru
 * 5. Sync ulang global_lab_sequence.last_number dari data hidup
 */
class FixSpesimenNumbersJumpedTo6000FromLabSync extends Migration
{
    const YEAR = 2026;
    const JUMP_THRESHOLD = 5000;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            return;
        }

        DB::transaction(function () {
            $now = now()->format('Y-m-d H:i:s');

            // --- 1) Klinik: manual yang nourut-nya ikut loncat ---
            $manualRows = DB::table('tb_permohonan_uji_klinik_2')
                ->whereNull('deleted_at')
                ->whereRaw('CAST(nourut_permohonan_uji_klinik AS UNSIGNED) >= ?', [self::JUMP_THRESHOLD])
                ->where('is_nomor_spesimen_manual', 1)
                ->whereNotNull('nomor_spesimen_manual')
                ->whereRaw("TRIM(nomor_spesimen_manual) <> ''")
                ->whereRaw("nomor_spesimen_manual REGEXP '^[0-9]{1,6}$'")
                ->orderBy('created_at')
                ->get([
                    'id_permohonan_uji_klinik',
                    'nourut_permohonan_uji_klinik',
                    'noregister_permohonan_uji_klinik',
                    'nomor_spesimen_manual',
                ]);

            foreach ($manualRows as $row) {
                $spesimen = (int) $row->nomor_spesimen_manual;
                if ($spesimen < 1) {
                    continue;
                }

                $this->updateKlinikSpesimenNumber(
                    (string) $row->id_permohonan_uji_klinik,
                    $spesimen,
                    (string) ($row->noregister_permohonan_uji_klinik ?? ''),
                    true,
                    $now
                );
            }

            // --- 2) Klinik: otomatis yang tampil 6xxx (Grabag dkk) ---
            $autoRows = DB::table('tb_permohonan_uji_klinik_2')
                ->whereNull('deleted_at')
                ->whereRaw('CAST(nourut_permohonan_uji_klinik AS UNSIGNED) >= ?', [self::JUMP_THRESHOLD])
                ->where(function ($q) {
                    $q->where('is_nomor_spesimen_manual', 0)
                        ->orWhereNull('is_nomor_spesimen_manual')
                        ->orWhereNull('nomor_spesimen_manual')
                        ->orWhereRaw("TRIM(COALESCE(nomor_spesimen_manual, '')) = ''");
                })
                ->orderBy('created_at')
                ->orderByRaw('CAST(nourut_permohonan_uji_klinik AS UNSIGNED)')
                ->get([
                    'id_permohonan_uji_klinik',
                    'nourut_permohonan_uji_klinik',
                    'noregister_permohonan_uji_klinik',
                ]);

            if ($autoRows->isNotEmpty()) {
                $freeNumbers = $this->nextFreeSpesimenNumbers(self::YEAR, $autoRows->count());

                foreach ($autoRows as $idx => $row) {
                    $newSpesimen = $freeNumbers[$idx] ?? null;
                    if ($newSpesimen === null) {
                        break;
                    }

                    $this->updateKlinikSpesimenNumber(
                        (string) $row->id_permohonan_uji_klinik,
                        (int) $newSpesimen,
                        (string) ($row->noregister_permohonan_uji_klinik ?? ''),
                        false,
                        $now
                    );
                }
            }

            // --- 3) Kesmas: lab_number/count_id junk >= 5000 → ambil dari kode sampel ---
            if (Schema::hasTable('tb_lab_num') && Schema::hasTable('tb_samples')) {
                $labRows = DB::table('tb_lab_num as ln')
                    ->join('tb_samples as s', 's.id_samples', '=', 'ln.sample_id')
                    ->whereNull('ln.deleted_at')
                    ->whereNull('s.deleted_at')
                    ->where('ln.lab_number', '>=', self::JUMP_THRESHOLD)
                    ->get([
                        'ln.id_lab_num',
                        'ln.lab_number',
                        'ln.sample_id',
                        's.codesample_samples',
                        's.count_id',
                    ]);

                foreach ($labRows as $lab) {
                    $fromCode = $this->parseSampleMidNumber((string) ($lab->codesample_samples ?? ''));
                    if ($fromCode < 1 || $fromCode >= self::JUMP_THRESHOLD) {
                        continue;
                    }

                    DB::table('tb_lab_num')
                        ->where('id_lab_num', $lab->id_lab_num)
                        ->update([
                            'lab_number' => $fromCode,
                            'updated_at' => $now,
                        ]);

                    $countId = (int) preg_replace('/\D+/', '', (string) ($lab->count_id ?? ''));
                    if ($countId >= self::JUMP_THRESHOLD) {
                        DB::table('tb_samples')
                            ->where('id_samples', $lab->sample_id)
                            ->update([
                                'count_id' => str_pad((string) $fromCode, 4, '0', STR_PAD_LEFT),
                                'updated_at' => $now,
                            ]);
                    }
                }
            }

            // --- 4) Hapus booking sequence detail yang masih di zona loncatan ---
            if (Schema::hasTable('global_lab_sequence_detail')) {
                DB::table('global_lab_sequence_detail')
                    ->where('year', self::YEAR)
                    ->whereNull('deleted_at')
                    ->where('sequence_number', '>=', self::JUMP_THRESHOLD)
                    ->update([
                        'deleted_at' => $now,
                        'updated_at' => $now,
                    ]);
            }

            // --- 5) Sync counter dari data hidup yang sudah bersih ---
            if (class_exists(GlobalLabSequence::class)) {
                GlobalLabSequence::ensureSyncedWithManualSources(self::YEAR);
            } elseif (Schema::hasTable('global_lab_sequence')) {
                $maxLive = $this->resolveMaxLiveSpesimen(self::YEAR);
                DB::table('global_lab_sequence')
                    ->where('year', self::YEAR)
                    ->whereNull('deleted_at')
                    ->update([
                        'last_number' => $maxLive,
                        'updated_at' => $now,
                    ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     * Data sudah terkoreksi; rollback tidak mengembalikan nomor 6xxx (hindari re-pollute).
     *
     * @return void
     */
    public function down()
    {
        // no-op
    }

    private function updateKlinikSpesimenNumber(
        string $permohonanId,
        int $newSpesimen,
        string $oldNoregister,
        bool $keepManualRegister,
        string $now
    ): void {
        $newNoregister = $keepManualRegister
            ? (string) $newSpesimen
            : (string) $newSpesimen;

        // Pertahankan format lama bila ada slash non-lab (legacy), selain itu pakai nomor spesimen murni.
        if ($keepManualRegister && strpos($oldNoregister, '/') !== false && !preg_match('/^\d+$/', $oldNoregister)) {
            $newNoregister = preg_replace('/^\d+/', (string) $newSpesimen, $oldNoregister, 1) ?: (string) $newSpesimen;
        }

        DB::table('tb_permohonan_uji_klinik_2')
            ->where('id_permohonan_uji_klinik', $permohonanId)
            ->update([
                'nourut_permohonan_uji_klinik' => $newSpesimen,
                'noregister_permohonan_uji_klinik' => $newNoregister,
                'updated_at' => $now,
            ]);

        if (Schema::hasTable('tb_number_klinik')) {
            DB::table('tb_number_klinik')
                ->where('id_permohonan_uji_klinik', $permohonanId)
                ->whereNull('deleted_at')
                ->update([
                    'new_number' => $newSpesimen,
                    'last_number' => $newSpesimen,
                    'updated_at' => $now,
                ]);
        }

        if (Schema::hasTable('global_lab_sequence_detail')) {
            $detail = DB::table('global_lab_sequence_detail')
                ->where('year', self::YEAR)
                ->where('lab_type', 'klinik')
                ->where('reference_id', $permohonanId)
                ->whereNull('deleted_at')
                ->orderByDesc('created_at')
                ->first();

            if ($detail) {
                DB::table('global_lab_sequence_detail')
                    ->where('id', $detail->id)
                    ->update([
                        'sequence_number' => $newSpesimen,
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('global_lab_sequence_detail')->insert([
                    'id' => $this->newUuid(),
                    'year' => self::YEAR,
                    'sequence_number' => $newSpesimen,
                    'lab_type' => 'klinik',
                    'reference_id' => $permohonanId,
                    'lab_id' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]);
            }
        }
    }

    /**
     * @return array<int,int>
     */
    private function nextFreeSpesimenNumbers(int $year, int $needed): array
    {
        $occupied = $this->occupiedSpesimenNumbers($year);
        $result = [];
        $candidate = 3606;

        while (count($result) < $needed && $candidate < 100000) {
            if (!isset($occupied[$candidate])) {
                $result[] = $candidate;
                $occupied[$candidate] = true;
            }
            $candidate++;
        }

        return $result;
    }

    /**
     * @return array<int,bool>
     */
    private function occupiedSpesimenNumbers(int $year): array
    {
        $occupied = [];

        $klinikRows = DB::table('tb_permohonan_uji_klinik_2')
            ->whereNull('deleted_at')
            ->where(function ($q) use ($year) {
                $q->whereYear('tglregister_permohonan_uji_klinik', $year)
                    ->orWhere(function ($q2) use ($year) {
                        $q2->whereNull('tglregister_permohonan_uji_klinik')
                            ->whereYear('created_at', $year);
                    });
            })
            ->get([
                'nourut_permohonan_uji_klinik',
                'noregister_permohonan_uji_klinik',
                'nomor_spesimen_manual',
                'is_nomor_spesimen_manual',
            ]);

        foreach ($klinikRows as $row) {
            if ((int) ($row->is_nomor_spesimen_manual ?? 0) === 1
                && preg_match('/^\d{1,6}$/', trim((string) ($row->nomor_spesimen_manual ?? '')))
            ) {
                $occupied[(int) $row->nomor_spesimen_manual] = true;
            }

            $nourut = (int) preg_replace('/\D+/', '', (string) ($row->nourut_permohonan_uji_klinik ?? ''));
            if ($nourut > 0 && $nourut < self::JUMP_THRESHOLD) {
                $occupied[$nourut] = true;
            }

            $noreg = trim((string) ($row->noregister_permohonan_uji_klinik ?? ''));
            if (preg_match('/^(\d{1,6})$/', $noreg, $m) && (int) $m[1] < self::JUMP_THRESHOLD) {
                $occupied[(int) $m[1]] = true;
            } elseif (preg_match('/^(\d{1,6})\s*\//', $noreg, $m) && (int) $m[1] < self::JUMP_THRESHOLD) {
                $occupied[(int) $m[1]] = true;
            }
        }

        if (Schema::hasTable('tb_samples')) {
            $codes = DB::table('tb_samples')
                ->whereNull('deleted_at')
                ->whereYear('created_at', $year)
                ->whereNotNull('codesample_samples')
                ->pluck('codesample_samples');

            foreach ($codes as $code) {
                $mid = $this->parseSampleMidNumber((string) $code);
                if ($mid > 0 && $mid < self::JUMP_THRESHOLD) {
                    $occupied[$mid] = true;
                }
            }
        }

        return $occupied;
    }

    private function resolveMaxLiveSpesimen(int $year): int
    {
        $occupied = $this->occupiedSpesimenNumbers($year);
        if ($occupied === []) {
            return 0;
        }

        return (int) max(array_keys($occupied));
    }

    private function parseSampleMidNumber(string $code): int
    {
        if (preg_match('#/(\d{1,6})/\d{4}\s*$#', $code, $m)) {
            return (int) $m[1];
        }

        return 0;
    }

    private function newUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
