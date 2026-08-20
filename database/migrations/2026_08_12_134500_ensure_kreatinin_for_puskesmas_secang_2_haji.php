<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Pastikan paket Kreatinin (+ satuan Kreatinin & e-GFR) ada di SEMUA pasien
 * batch Haji Puskesmas Secang 2.
 *
 * Idempotent — aman dijalankan ulang:
 * - Lewati pasien yang sudah punya satuan Kreatinin aktif
 * - Jika baris paket ada tapi parameternya hilang → backfill parameter saja
 * - Hapus varian Kreatinin (Klaim/BPJS) bila ikut tersimpan
 */
class EnsureKreatininForPuskesmasSecang2Haji extends Migration
{
    const HAJI_ID = 'd7f0bdc8-2b9e-41f1-8e57-bfa95b3c149e';
    const KREATININ_PAKET_NAME = 'Kreatinin';

    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')
            || !Schema::hasTable('tb_permohonan_uji_paket_klinik')
            || !Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            || !Schema::hasTable('ms_parameter_paket_klinik')
        ) {
            return;
        }

        if (!DB::table('tb_permohonan_uji_klinik_haji')
            ->where('id_permohonan_uji_klinik_haji', self::HAJI_ID)
            ->whereNull('deleted_at')
            ->exists()
        ) {
            return;
        }

        $kreatininMeta = $this->loadKreatininPackageMeta();
        if ($kreatininMeta === null) {
            return;
        }

        $billingPaketIds = DB::table('ms_parameter_paket_klinik')
            ->whereNull('deleted_at')
            ->where('name_parameter_paket_klinik', '<>', self::KREATININ_PAKET_NAME)
            ->where(function ($q) {
                $q->where('name_parameter_paket_klinik', 'like', 'Kreatinin (%')
                    ->orWhere('name_parameter_paket_klinik', 'like', '%Kreatinin (Klaim)%')
                    ->orWhere('name_parameter_paket_klinik', 'like', '%Kreatinin (BPJS)%');
            })
            ->pluck('id_parameter_paket_klinik')
            ->all();

        $permohonanIds = DB::table('tb_permohonan_uji_klinik_2')
            ->where('id_permohonan_uji_klinik_haji', self::HAJI_ID)
            ->whereNull('deleted_at')
            ->pluck('id_permohonan_uji_klinik')
            ->all();

        DB::transaction(function () use ($permohonanIds, $kreatininMeta, $billingPaketIds) {
            $now = now();
            $kreatininPaketId = $kreatininMeta['paket']->id_parameter_paket_klinik;

            foreach ($permohonanIds as $permohonanId) {
                $this->removeBillingKreatininVariants($permohonanId, $billingPaketIds, $now);

                if ($this->patientHasActiveKreatininSatuan($permohonanId)) {
                    continue;
                }

                $existingPaketRow = DB::table('tb_permohonan_uji_paket_klinik')
                    ->where('permohonan_uji_klinik', $permohonanId)
                    ->where('parameter_paket_klinik', $kreatininPaketId)
                    ->whereNull('deleted_at')
                    ->first();

                if ($existingPaketRow) {
                    $this->insertKreatininParameters(
                        $permohonanId,
                        $existingPaketRow->id_permohonan_uji_paket_klinik,
                        $kreatininMeta,
                        $now
                    );
                } else {
                    $this->insertKreatininPackageAndParameters($permohonanId, $kreatininMeta, $now);
                }

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

    private function loadKreatininPackageMeta(): ?array
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

                if (!$masterSatuan) {
                    continue;
                }

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

    private function patientHasActiveKreatininSatuan(string $permohonanId): bool
    {
        return DB::table('tb_permohonan_uji_parameter_klinik as pp')
            ->join('ms_parameter_satuan_klinik as s', 's.id_parameter_satuan_klinik', '=', 'pp.parameter_satuan_klinik')
            ->where('pp.permohonan_uji_klinik', $permohonanId)
            ->whereNull('pp.deleted_at')
            ->where(function ($q) {
                $q->whereRaw('LOWER(s.name_parameter_satuan_klinik) LIKE ?', ['%kreatinin%'])
                    ->orWhereRaw('LOWER(s.name_parameter_satuan_klinik) LIKE ?', ['%creatinin%'])
                    ->orWhereRaw('LOWER(s.name_parameter_satuan_klinik) LIKE ?', ['%creatinine%']);
            })
            ->exists();
    }

    private function removeBillingKreatininVariants(string $permohonanId, array $billingPaketIds, $now): void
    {
        if (empty($billingPaketIds)) {
            return;
        }

        $rowIds = DB::table('tb_permohonan_uji_paket_klinik')
            ->where('permohonan_uji_klinik', $permohonanId)
            ->whereIn('parameter_paket_klinik', $billingPaketIds)
            ->whereNull('deleted_at')
            ->pluck('id_permohonan_uji_paket_klinik')
            ->all();

        if (empty($rowIds)) {
            return;
        }

        DB::table('tb_permohonan_uji_parameter_klinik')
            ->whereIn('permohonan_uji_paket_klinik', $rowIds)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => $now, 'updated_at' => $now]);

        DB::table('tb_permohonan_uji_paket_klinik')
            ->whereIn('id_permohonan_uji_paket_klinik', $rowIds)
            ->update(['deleted_at' => $now, 'updated_at' => $now]);
    }

    private function insertKreatininPackageAndParameters(string $permohonanId, array $meta, $now): void
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

        $this->insertKreatininParameters($permohonanId, $paketRowId, $meta, $now);
    }

    private function insertKreatininParameters(string $permohonanId, string $paketRowId, array $meta, $now): void
    {
        $existingSatuanIds = DB::table('tb_permohonan_uji_parameter_klinik')
            ->where('permohonan_uji_klinik', $permohonanId)
            ->whereNull('deleted_at')
            ->pluck('parameter_satuan_klinik')
            ->filter()
            ->all();

        $existingSet = array_flip($existingSatuanIds);

        foreach ($meta['satuan_links'] as $link) {
            $satuanId = $link['satuan_link']->parameter_satuan_klinik;
            if (isset($existingSet[$satuanId])) {
                continue;
            }

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
                'parameter_satuan_klinik' => $satuanId,
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
}
