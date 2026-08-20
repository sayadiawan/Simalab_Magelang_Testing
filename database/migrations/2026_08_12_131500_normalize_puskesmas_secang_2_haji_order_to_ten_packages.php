<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Rapikan order batch Haji "Puskesmas Secang 2" agar sesuai order yang benar:
 *
 * 1. HbA1c
 * 2. Gula Darah Puasa
 * 3. Gula Darah 2 Jam PP
 * 4. Trigliseride
 * 5. SGOT
 * 6. SGPT
 * 7. Ureum
 * 8. LED (Laju Endap Darah)
 * 9. PP Tes (Tes Kehamilan)
 * 10. Cholesterol Total
 * Total: Rp 282.000
 *
 * Masalah saat ini:
 * - 39 pasien: masih "Paket Haji" + GDP/GD2PP/Cholesterol saja (total Rp 60.000)
 * - 4 pasien: 10 paket benar + Kreatinin ekstra (total Rp 304.000)
 */
class NormalizePuskesmasSecang2HajiOrderToTenPackages extends Migration
{
    const HAJI_ID = 'd7f0bdc8-2b9e-41f1-8e57-bfa95b3c149e';
    const EXPECTED_TOTAL = 282000;

    private function requiredPackageNames(): array
    {
        return [
            'HbA1c',
            'Gula Darah Puasa',
            'Gula Darah 2 Jam PP',
            'Trigliseride',
            'SGOT',
            'SGPT',
            'Ureum',
            'LED (Laju Endap Darah)',
            'PP Tes (Tes Kehamilan)',
            'Cholesterol Total',
        ];
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

        $packageMap = $this->buildPackageMap($this->requiredPackageNames());
        if (count($packageMap) !== count($this->requiredPackageNames())) {
            return;
        }

        $removePaketIds = DB::table('ms_parameter_paket_klinik')
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('name_parameter_paket_klinik', 'Paket Haji')
                    ->orWhere('name_parameter_paket_klinik', 'Kreatinin')
                    ->orWhere('name_parameter_paket_klinik', 'like', '%(BPJS)%')
                    ->orWhere('name_parameter_paket_klinik', 'like', '%(Klaim)%')
                    ->orWhere('name_parameter_paket_klinik', 'like', '% BPJS%')
                    ->orWhere('name_parameter_paket_klinik', 'like', '% Klaim%');
            })
            ->pluck('id_parameter_paket_klinik')
            ->all();

        $permohonans = DB::table('tb_permohonan_uji_klinik_2')
            ->where('id_permohonan_uji_klinik_haji', self::HAJI_ID)
            ->whereNull('deleted_at')
            ->get();

        DB::transaction(function () use ($permohonans, $packageMap, $removePaketIds) {
            $now = now();

            foreach ($permohonans as $permohonan) {
                $permohonanId = $permohonan->id_permohonan_uji_klinik;

                // 1) Hapus paket yang tidak boleh ada (Paket Haji / Kreatinin / BPJS-Klaim)
                if (!empty($removePaketIds)) {
                    $toRemove = DB::table('tb_permohonan_uji_paket_klinik')
                        ->where('permohonan_uji_klinik', $permohonanId)
                        ->whereIn('parameter_paket_klinik', $removePaketIds)
                        ->whereNull('deleted_at')
                        ->pluck('id_permohonan_uji_paket_klinik')
                        ->all();

                    if (!empty($toRemove)) {
                        DB::table('tb_permohonan_uji_parameter_klinik')
                            ->whereIn('permohonan_uji_paket_klinik', $toRemove)
                            ->whereNull('deleted_at')
                            ->update([
                                'deleted_at' => $now,
                                'updated_at' => $now,
                            ]);

                        DB::table('tb_permohonan_uji_paket_klinik')
                            ->whereIn('id_permohonan_uji_paket_klinik', $toRemove)
                            ->whereNull('deleted_at')
                            ->update([
                                'deleted_at' => $now,
                                'updated_at' => $now,
                            ]);
                    }
                }

                // 2) Pastikan 10 paket order ada
                foreach ($packageMap as $meta) {
                    $existingPaket = DB::table('tb_permohonan_uji_paket_klinik')
                        ->where('permohonan_uji_klinik', $permohonanId)
                        ->where('parameter_paket_klinik', $meta['paket']->id_parameter_paket_klinik)
                        ->whereNull('deleted_at')
                        ->first();

                    if ($existingPaket) {
                        // Sinkron harga paket ke master bila beda
                        $harga = (int) ($meta['paket']->harga_parameter_paket_klinik ?? 0);
                        if ((int) $existingPaket->harga_permohonan_uji_paket_klinik !== $harga) {
                            DB::table('tb_permohonan_uji_paket_klinik')
                                ->where('id_permohonan_uji_paket_klinik', $existingPaket->id_permohonan_uji_paket_klinik)
                                ->update([
                                    'harga_permohonan_uji_paket_klinik' => $harga,
                                    'updated_at' => $now,
                                ]);
                        }
                        continue;
                    }

                    $this->insertPackageForPatient($permohonanId, $meta, $now);
                }

                // 3) Soft-delete paket lain di luar 10 order (jika ada sisa)
                $allowedIds = collect($packageMap)->pluck('paket.id_parameter_paket_klinik')->all();
                $extraPaketRows = DB::table('tb_permohonan_uji_paket_klinik')
                    ->where('permohonan_uji_klinik', $permohonanId)
                    ->whereNull('deleted_at')
                    ->where(function ($q) use ($allowedIds) {
                        $q->whereNotIn('parameter_paket_klinik', $allowedIds)
                            ->orWhereNull('parameter_paket_klinik');
                    })
                    ->where(function ($q) {
                        // Jangan hapus paket extra custom kecuali kosong sama sekali
                        $q->whereNull('parameter_paket_extra')
                            ->orWhere('parameter_paket_extra', '');
                    })
                    ->pluck('id_permohonan_uji_paket_klinik')
                    ->all();

                if (!empty($extraPaketRows)) {
                    DB::table('tb_permohonan_uji_parameter_klinik')
                        ->whereIn('permohonan_uji_paket_klinik', $extraPaketRows)
                        ->whereNull('deleted_at')
                        ->update([
                            'deleted_at' => $now,
                            'updated_at' => $now,
                        ]);

                    DB::table('tb_permohonan_uji_paket_klinik')
                        ->whereIn('id_permohonan_uji_paket_klinik', $extraPaketRows)
                        ->whereNull('deleted_at')
                        ->update([
                            'deleted_at' => $now,
                            'updated_at' => $now,
                        ]);
                }

                // 4) Recalc total dari paket aktif
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

    private function buildPackageMap(array $names): array
    {
        $packageMap = [];

        foreach ($names as $name) {
            $paket = DB::table('ms_parameter_paket_klinik')
                ->where('name_parameter_paket_klinik', $name)
                ->whereNull('deleted_at')
                ->first();

            if (!$paket) {
                continue;
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
                continue;
            }

            $packageMap[$name] = [
                'paket' => $paket,
                'satuan_links' => $satuanLinks,
            ];
        }

        return $packageMap;
    }

    private function insertPackageForPatient(string $permohonanId, array $meta, $now): void
    {
        $firstJenis = $meta['satuan_links'][0]['jenis'];
        $harga = (int) ($meta['paket']->harga_parameter_paket_klinik ?? 0);
        $paketId = Uuid::uuid4()->toString();

        DB::table('tb_permohonan_uji_paket_klinik')->insert([
            'id_permohonan_uji_paket_klinik' => $paketId,
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
                'permohonan_uji_paket_klinik' => $paketId,
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
}
