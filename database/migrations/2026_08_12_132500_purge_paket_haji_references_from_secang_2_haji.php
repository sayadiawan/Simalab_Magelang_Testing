<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bersihkan semua jejak master komposit "Paket Haji" pada batch Puskesmas Secang 2.
 *
 * Data aktif sudah paket individu; baris Paket Haji lama masih soft-delete di DB
 * dan masih menunjuk parameter_paket_klinik = master Paket Haji.
 *
 * Migration ini:
 * 1. Soft-delete baris aktif yang masih menyangkut Paket Haji (safety net)
 * 2. Hard-delete baris soft-delete legacy Paket Haji + parameternya
 * 3. Recalc total biaya per permohonan
 */
class PurgePaketHajiReferencesFromSecang2Haji extends Migration
{
    const HAJI_ID = 'd7f0bdc8-2b9e-41f1-8e57-bfa95b3c149e';
    const PAKET_HAJI_NAME = 'Paket Haji';

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

        $paketHajiId = DB::table('ms_parameter_paket_klinik')
            ->where('name_parameter_paket_klinik', self::PAKET_HAJI_NAME)
            ->whereNull('deleted_at')
            ->value('id_parameter_paket_klinik');

        if (!$paketHajiId) {
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

        DB::transaction(function () use ($permohonanIds, $paketHajiId) {
            $now = now();

            // --- Safety net: soft-delete baris aktif yang masih menyangkut Paket Haji ---
            $activePaketHajiRowIds = DB::table('tb_permohonan_uji_paket_klinik')
                ->whereIn('permohonan_uji_klinik', $permohonanIds)
                ->where('parameter_paket_klinik', $paketHajiId)
                ->whereNull('deleted_at')
                ->pluck('id_permohonan_uji_paket_klinik')
                ->all();

            if (!empty($activePaketHajiRowIds)) {
                DB::table('tb_permohonan_uji_parameter_klinik')
                    ->whereIn('permohonan_uji_paket_klinik', $activePaketHajiRowIds)
                    ->whereNull('deleted_at')
                    ->update([
                        'deleted_at' => $now,
                        'updated_at' => $now,
                    ]);

                DB::table('tb_permohonan_uji_paket_klinik')
                    ->whereIn('id_permohonan_uji_paket_klinik', $activePaketHajiRowIds)
                    ->update([
                        'deleted_at' => $now,
                        'updated_at' => $now,
                    ]);
            }

            DB::table('tb_permohonan_uji_parameter_klinik')
                ->whereIn('permohonan_uji_klinik', $permohonanIds)
                ->where('parameter_paket_klinik', $paketHajiId)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);

            // --- Hard-delete legacy soft-deleted Paket Haji ---
            $legacyPaketRowIds = DB::table('tb_permohonan_uji_paket_klinik')
                ->whereIn('permohonan_uji_klinik', $permohonanIds)
                ->where('parameter_paket_klinik', $paketHajiId)
                ->pluck('id_permohonan_uji_paket_klinik')
                ->all();

            if (!empty($legacyPaketRowIds)) {
                DB::table('tb_permohonan_uji_parameter_klinik')
                    ->whereIn('permohonan_uji_paket_klinik', $legacyPaketRowIds)
                    ->delete();

                DB::table('tb_permohonan_uji_paket_klinik')
                    ->whereIn('id_permohonan_uji_paket_klinik', $legacyPaketRowIds)
                    ->delete();
            }

            // Parameter aktif/soft-delete yang masih FK langsung ke master Paket Haji
            DB::table('tb_permohonan_uji_parameter_klinik')
                ->whereIn('permohonan_uji_klinik', $permohonanIds)
                ->where('parameter_paket_klinik', $paketHajiId)
                ->delete();

            // Recalc total dari paket aktif
            foreach ($permohonanIds as $permohonanId) {
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
        // Data legacy Paket Haji sudah dihapus permanen — tidak di-rollback.
    }
}
