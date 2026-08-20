<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Backfill parameter yang hilang pada batch Haji "Puskesmas Secang 2" (2026-08-12).
 *
 * Konteks:
 * - 39 pasien pakai master "Paket Haji" → sudah punya LED (1/2 Jam) + Kreatinin,
 *   tapi GDP / GD 2 Jam PP / Cholesterol Total tidak ada di master Paket Haji.
 * - 4 pasien order paket individu → LED, GDP, GD 2 Jam PP, Cholesterol, Kreatinin
 *   tidak tersimpan (hanya HbA1c, PP Tes, SGOT, SGPT, Trigliseride, Ureum).
 *
 * Migration ini menambahkan paket yang masih kurang per pasien (idempotent).
 */
class AddMissingParametersForPuskesmasSecang2Haji extends Migration
{
    const HAJI_ID = 'd7f0bdc8-2b9e-41f1-8e57-bfa95b3c149e';

    /**
     * Nama paket UI yang harus ada + pola deteksi satuan yang sudah ada di pasien.
     */
    private function requiredPackages(): array
    {
        return [
            [
                'name' => 'LED (Laju Endap Darah)',
                'detect' => ['laju endap', 'led'],
            ],
            [
                'name' => 'Gula Darah Puasa',
                'detect' => ['gula darah puasa'],
            ],
            [
                'name' => 'Gula Darah 2 Jam PP',
                'detect' => ['gula darah 2 jam'],
            ],
            [
                'name' => 'Cholesterol Total',
                'detect' => ['cholesterol total', 'kolesterol total'],
            ],
            [
                'name' => 'Kreatinin',
                'detect' => ['kreatinin', 'creatinine', 'creatinin'],
            ],
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

        $haji = DB::table('tb_permohonan_uji_klinik_haji')
            ->where('id_permohonan_uji_klinik_haji', self::HAJI_ID)
            ->whereNull('deleted_at')
            ->first();

        if (!$haji) {
            return;
        }

        $packageMap = [];
        foreach ($this->requiredPackages() as $req) {
            $paket = DB::table('ms_parameter_paket_klinik')
                ->where('name_parameter_paket_klinik', $req['name'])
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

            $packageMap[$req['name']] = [
                'detect' => $req['detect'],
                'paket' => $paket,
                'satuan_links' => $satuanLinks,
            ];
        }

        if (empty($packageMap)) {
            return;
        }

        $permohonans = DB::table('tb_permohonan_uji_klinik_2')
            ->where('id_permohonan_uji_klinik_haji', self::HAJI_ID)
            ->whereNull('deleted_at')
            ->get();

        DB::transaction(function () use ($permohonans, $packageMap) {
            foreach ($permohonans as $permohonan) {
                $existingSatuanIds = DB::table('tb_permohonan_uji_parameter_klinik')
                    ->where('permohonan_uji_klinik', $permohonan->id_permohonan_uji_klinik)
                    ->whereNull('deleted_at')
                    ->whereNotNull('parameter_satuan_klinik')
                    ->pluck('parameter_satuan_klinik')
                    ->all();

                $existingSatuanNames = [];
                if (!empty($existingSatuanIds)) {
                    $existingSatuanNames = DB::table('ms_parameter_satuan_klinik')
                        ->whereIn('id_parameter_satuan_klinik', $existingSatuanIds)
                        ->pluck('name_parameter_satuan_klinik')
                        ->map(function ($name) {
                            return mb_strtolower(trim((string) $name), 'UTF-8');
                        })
                        ->all();
                }

                $addedHarga = 0;

                foreach ($packageMap as $meta) {
                    if ($this->patientAlreadyHasParameter($existingSatuanNames, $meta['detect'])) {
                        continue;
                    }

                    // Jangan duplikasi baris paket yang sama
                    $existingPaket = DB::table('tb_permohonan_uji_paket_klinik')
                        ->where('permohonan_uji_klinik', $permohonan->id_permohonan_uji_klinik)
                        ->where('parameter_paket_klinik', $meta['paket']->id_parameter_paket_klinik)
                        ->whereNull('deleted_at')
                        ->first();

                    if ($existingPaket) {
                        continue;
                    }

                    $firstJenis = $meta['satuan_links'][0]['jenis'];
                    $harga = (int) ($meta['paket']->harga_parameter_paket_klinik ?? 0);
                    $paketId = Uuid::uuid4()->toString();
                    $now = now();

                    DB::table('tb_permohonan_uji_paket_klinik')->insert([
                        'id_permohonan_uji_paket_klinik' => $paketId,
                        'permohonan_uji_klinik' => $permohonan->id_permohonan_uji_klinik,
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

                    $addedHarga += $harga;

                    foreach ($meta['satuan_links'] as $link) {
                        $masterSatuan = $link['master_satuan'];
                        $jenis = $link['jenis'];
                        $satuanLink = $link['satuan_link'];

                        $paramId = Uuid::uuid4()->toString();
                        $row = [
                            'id_permohonan_uji_parameter_klinik' => $paramId,
                            'permohonan_uji_klinik' => $permohonan->id_permohonan_uji_klinik,
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

                        if ($masterSatuan && !empty($masterSatuan->name_parameter_satuan_klinik)) {
                            $existingSatuanNames[] = mb_strtolower(trim($masterSatuan->name_parameter_satuan_klinik), 'UTF-8');
                        }
                    }
                }

                if ($addedHarga > 0) {
                    DB::table('tb_permohonan_uji_klinik_2')
                        ->where('id_permohonan_uji_klinik', $permohonan->id_permohonan_uji_klinik)
                        ->update([
                            'total_harga_permohonan_uji_klinik' => (int) ($permohonan->total_harga_permohonan_uji_klinik ?? 0) + $addedHarga,
                            'updated_at' => now(),
                        ]);
                }
            }
        });
    }

    public function down()
    {
        // Data klinis — tidak di-rollback otomatis.
    }

    private function patientAlreadyHasParameter(array $existingSatuanNames, array $detectPatterns): bool
    {
        foreach ($existingSatuanNames as $name) {
            foreach ($detectPatterns as $pattern) {
                if ($name === $pattern || strpos($name, $pattern) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
