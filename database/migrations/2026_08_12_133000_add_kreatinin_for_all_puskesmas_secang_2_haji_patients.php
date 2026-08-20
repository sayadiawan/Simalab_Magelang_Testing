<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Tambahkan paket Kreatinin ke semua pasien batch Haji Puskesmas Secang 2
 * yang belum memilikinya (idempotent).
 *
 * Paket Kreatinin: Rp 22.000 (termasuk satuan e-GFR).
 */
class AddKreatininForAllPuskesmasSecang2HajiPatients extends Migration
{
    const HAJI_ID = 'd7f0bdc8-2b9e-41f1-8e57-bfa95b3c149e';
    const KREATININ_PAKET_NAME = 'Kreatinin';

    private function kreatininDetectPatterns(): array
    {
        return ['kreatinin', 'creatinine', 'creatinin', 'e-gfr'];
    }

    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_haji')
            || !Schema::hasTable('tb_permohonan_uji_klinik_2')
            || !Schema::hasTable('tb_permohonan_uji_paket_klinik')
            || !Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            || !Schema::hasTable('ms_parameter_paket_klinik')
        ) {
            return;
        }

        $hajiExists = DB::table('tb_permohonan_uji_klinik_haji')
            ->where('id_permohonan_uji_klinik_haji', self::HAJI_ID)
            ->whereNull('deleted_at')
            ->exists();

        if (!$hajiExists) {
            return;
        }

        $meta = $this->buildKreatininPackageMeta();
        if ($meta === null) {
            return;
        }

        $permohonans = DB::table('tb_permohonan_uji_klinik_2')
            ->where('id_permohonan_uji_klinik_haji', self::HAJI_ID)
            ->whereNull('deleted_at')
            ->get();

        DB::transaction(function () use ($permohonans, $meta) {
            $now = now();

            foreach ($permohonans as $permohonan) {
                $permohonanId = $permohonan->id_permohonan_uji_klinik;

                $existingPaket = DB::table('tb_permohonan_uji_paket_klinik')
                    ->where('permohonan_uji_klinik', $permohonanId)
                    ->where('parameter_paket_klinik', $meta['paket']->id_parameter_paket_klinik)
                    ->whereNull('deleted_at')
                    ->first();

                if ($existingPaket) {
                    continue;
                }

                $existingSatuanNames = DB::table('tb_permohonan_uji_parameter_klinik as pp')
                    ->join('ms_parameter_satuan_klinik as s', 's.id_parameter_satuan_klinik', '=', 'pp.parameter_satuan_klinik')
                    ->where('pp.permohonan_uji_klinik', $permohonanId)
                    ->whereNull('pp.deleted_at')
                    ->pluck('s.name_parameter_satuan_klinik')
                    ->map(function ($name) {
                        return mb_strtolower(trim((string) $name), 'UTF-8');
                    })
                    ->all();

                if ($this->patientAlreadyHasKreatinin($existingSatuanNames)) {
                    continue;
                }

                $this->insertKreatininForPatient($permohonanId, $meta, $now);

                $total = (int) DB::table('tb_permohonan_uji_paket_klinik')
                    ->where('permohonan_uji_klinik', $permohonanId)
                    ->whereNull('deleted_at')
                    ->sum('harga_permohonan_uji_paket_klinik');

                DB::table('tb_permohonan_uji_klinik_2')
                    ->where('id_permohonan_uji_klinik', $permohonanId)
                    ->update([
                        'total_harga_permohonan_uji_klinik' => $total,
                        'updated_at' => $now,
                    ]);
            }
        });
    }

    public function down()
    {
        // Data klinis — tidak di-rollback otomatis.
    }

    private function buildKreatininPackageMeta(): ?array
    {
        $paket = DB::table('ms_parameter_paket_klinik')
            ->where('name_parameter_paket_klinik', self::KREATININ_PAKET_NAME)
            ->whereNull('deleted_at')
            ->first();

        if (!$paket) {
            return null;
        }

        $jenisRows = DB::table('ms_parameter_paket_jenis_klinik')
            ->where('parameter_paket_klinik_id', $paket->id_parameter_paket_klinik)
            ->whereNull('deleted_at')
            ->orderBy('sort')
            ->get();

        $satuanLinks = [];
        foreach ($jenisRows as $jenis) {
            $satuans = DB::table('ms_parameter_satuan_paket_klinik')
                ->where('parameter_paket_jenis_klinik', $jenis->id_parameter_paket_jenis_klinik)
                ->whereNull('deleted_at')
                ->orderBy('sorting')
                ->get();

            foreach ($satuans as $satuan) {
                $masterSatuan = DB::table('ms_parameter_satuan_klinik')
                    ->where('id_parameter_satuan_klinik', $satuan->parameter_satuan_klinik)
                    ->whereNull('deleted_at')
                    ->first();

                $satuanLinks[] = [
                    'jenis' => $jenis,
                    'satuan_link' => $satuan,
                    'master_satuan' => $masterSatuan,
                ];
            }
        }

        if (empty($satuanLinks)) {
            return null;
        }

        return [
            'paket' => $paket,
            'satuan_links' => $satuanLinks,
        ];
    }

    private function insertKreatininForPatient(string $permohonanId, array $meta, $now): void
    {
        $firstJenis = $meta['satuan_links'][0]['jenis'];
        $harga = (int) ($meta['paket']->harga_parameter_paket_klinik ?? 0);
        $paketRowId = Uuid::uuid4()->toString();

        DB::table('tb_permohonan_uji_paket_klinik')->insert([
            'id_permohonan_uji_paket_klinik' => $paketRowId,
            'permohonan_uji_klinik' => $permohonanId,
            'parameter_jenis_klinik' => $firstJenis->parameter_jenis_klinik_id,
            'type_permohonan_uji_paket_klinik' => 'P',
            'parameter_paket_klinik' => $meta['paket']->id_parameter_paket_klinik,
            'parameter_paket_extra' => null,
            'harga_permohonan_uji_paket_klinik' => $harga,
            'is_prolanis_gula' => 0,
            'is_prolanis_urine' => 0,
            'is_haji' => 1,
            'id_service_request' => '',
            'response_service_request' => '',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);

        foreach ($meta['satuan_links'] as $link) {
            $masterSatuan = $link['master_satuan'];
            $jenis = $link['jenis'];
            $satuanLink = $link['satuan_link'];

            $row = [
                'id_permohonan_uji_parameter_klinik' => Uuid::uuid4()->toString(),
                'permohonan_uji_klinik' => $permohonanId,
                'permohonan_uji_paket_klinik' => $paketRowId,
                'parameter_paket_klinik' => $meta['paket']->id_parameter_paket_klinik,
                'parameter_paket_jenis_klinik' => $jenis->id_parameter_paket_jenis_klinik,
                'jenis_parameter_klinik_id' => $jenis->parameter_jenis_klinik_id,
                'parameter_satuan_klinik' => $satuanLink->parameter_satuan_klinik,
                'sorting_permohonan_uji_parameter_klinik' => $satuanLink->sorting ?? 0,
                'harga_permohonan_uji_parameter_klinik' => (int) ($masterSatuan->harga_satuan_parameter_satuan_klinik ?? 0),
                'hasil_permohonan_uji_parameter_klinik' => null,
                'flag_permohonan_uji_parameter_klinik' => null,
                'satuan_permohonan_uji_parameter_klinik' => null,
                'method_permohonan_uji_parameter_klinik' => '-',
                'baku_mutu_permohonan_uji_parameter_klinik' => null,
                'keterangan_permohonan_uji_parameter_klinik' => $masterSatuan->ket_default_parameter_satuan_klinik ?? '',
                'offset_baku_mutu' => null,
                'selected_history_id' => null,
                'status_verifikasi' => 'pending',
                'komentar_verifikasi' => null,
                'sort_jenis_klinik' => $jenis->sort ?? 0,
                'sorting_parameter_satuan' => $satuanLink->sorting ?? 0,
                'is_prolanis_gula' => 0,
                'is_prolanis_urine' => 0,
                'is_haji' => 1,
                'id_observation' => '',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ];

            if (Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'requires_nama_jenis')) {
                $row['requires_nama_jenis'] = (int) ($masterSatuan->requires_nama_jenis ?? 0);
            }

            DB::table('tb_permohonan_uji_parameter_klinik')->insert($row);
        }
    }

    private function patientAlreadyHasKreatinin(array $existingSatuanNames): bool
    {
        foreach ($existingSatuanNames as $name) {
            foreach ($this->kreatininDetectPatterns() as $pattern) {
                if ($name === $pattern || strpos($name, $pattern) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
