<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hapus paket BPJS/Klaim yang ikut tersimpan pada batch Haji Puskesmas Secang 2
 * padahal user tidak memilihnya (varian billing berbagi satuan dengan paket normal).
 */
class RemoveBillingVariantPackagesFromPuskesmasSecang2Haji extends Migration
{
    const HAJI_ID = 'd7f0bdc8-2b9e-41f1-8e57-bfa95b3c149e';

    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')
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

        DB::transaction(function () {
            $billingPaketIds = DB::table('ms_parameter_paket_klinik')
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->where('name_parameter_paket_klinik', 'like', '%(BPJS)%')
                        ->orWhere('name_parameter_paket_klinik', 'like', '%(Klaim)%')
                        ->orWhere('name_parameter_paket_klinik', 'like', '% BPJS%')
                        ->orWhere('name_parameter_paket_klinik', 'like', '% Klaim%');
                })
                ->pluck('id_parameter_paket_klinik')
                ->all();

            if (empty($billingPaketIds)) {
                return;
            }

            $permohonanIds = DB::table('tb_permohonan_uji_klinik_2')
                ->where('id_permohonan_uji_klinik_haji', self::HAJI_ID)
                ->whereNull('deleted_at')
                ->pluck('id_permohonan_uji_klinik')
                ->all();

            if (empty($permohonanIds)) {
                return;
            }

            $paketRows = DB::table('tb_permohonan_uji_paket_klinik')
                ->whereIn('permohonan_uji_klinik', $permohonanIds)
                ->whereIn('parameter_paket_klinik', $billingPaketIds)
                ->whereNull('deleted_at')
                ->get(['id_permohonan_uji_paket_klinik', 'permohonan_uji_klinik']);

            if ($paketRows->isEmpty()) {
                return;
            }

            $paketRowIds = $paketRows->pluck('id_permohonan_uji_paket_klinik')->all();
            $now = now();

            DB::table('tb_permohonan_uji_parameter_klinik')
                ->whereIn('permohonan_uji_paket_klinik', $paketRowIds)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);

            DB::table('tb_permohonan_uji_paket_klinik')
                ->whereIn('id_permohonan_uji_paket_klinik', $paketRowIds)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);

            $touchedPermohonan = $paketRows->pluck('permohonan_uji_klinik')->unique()->values();
            foreach ($touchedPermohonan as $permohonanId) {
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
        // Data klinis — tidak di-rollback.
    }
}
