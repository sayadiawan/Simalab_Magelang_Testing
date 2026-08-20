<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bug: storePenerimaSampel memakai Carbon::today() sebagai base tanggal jam penerima.
 * Akibatnya pasien registrasi 12 Agu yang diisi penerima/pengolah/pemeriksa pada 13 Agu
 * tersimpan tanggal 13 — LHU "Tanggal Diperiksa" ikut salah.
 *
 * Perbaikan data Grabag 1: geser start/stop verification (7,2,3) dan tglpengujian
 * dari 2026-08-13 → 2026-08-12 (jam tetap).
 */
class FixGrabag1HajiVerificationDatesShiftedToAug13 extends Migration
{
    const HAJI_ID = '6f927d50-29aa-4c4b-93ee-885b59d537d1';
    const HAJI_NAME = 'Puskesmas Grabag 1';
    const WRONG_DATE = '2026-08-13';
    const CORRECT_DATE = '2026-08-12';

    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2') || !Schema::hasTable('tb_verification_activity_samples')) {
            return;
        }

        $hajiId = $this->resolveHajiId();
        if ($hajiId === null) {
            return;
        }

        $ids = DB::table('tb_permohonan_uji_klinik_2')
            ->where('id_permohonan_uji_klinik_haji', $hajiId)
            ->whereNull('deleted_at')
            ->whereDate('tglregister_permohonan_uji_klinik', self::CORRECT_DATE)
            ->pluck('id_permohonan_uji_klinik')
            ->all();

        if (empty($ids)) {
            return;
        }

        // Verification steps: 7 Penerima, 2 Pengolah, 3 Pemeriksa
        foreach ([7, 2, 3] as $step) {
            DB::table('tb_verification_activity_samples')
                ->whereIn('is_klinik', $ids)
                ->where('id_verification_activity', $step)
                ->whereDate('start_date', self::WRONG_DATE)
                ->update([
                    'start_date' => DB::raw("CONCAT('" . self::CORRECT_DATE . "', ' ', TIME(start_date))"),
                    'stop_date' => DB::raw("CASE WHEN stop_date IS NULL THEN NULL ELSE CONCAT('" . self::CORRECT_DATE . "', ' ', TIME(stop_date)) END"),
                    'updated_at' => now(),
                ]);
        }

        DB::table('tb_permohonan_uji_klinik_2')
            ->whereIn('id_permohonan_uji_klinik', $ids)
            ->whereDate('tglpengujian_permohonan_uji_klinik', self::WRONG_DATE)
            ->update([
                'tglpengujian_permohonan_uji_klinik' => DB::raw(
                    "CONCAT('" . self::CORRECT_DATE . "', ' ', TIME(tglpengujian_permohonan_uji_klinik))"
                ),
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        // Tidak rollback otomatis — risiko menggeser data yang benar-benar diperiksa 13.
    }

    /**
     * @return string|null
     */
    private function resolveHajiId()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_haji')) {
            return self::HAJI_ID;
        }

        $byId = DB::table('tb_permohonan_uji_klinik_haji')
            ->where('id_permohonan_uji_klinik_haji', self::HAJI_ID)
            ->whereNull('deleted_at')
            ->value('id_permohonan_uji_klinik_haji');

        if ($byId) {
            return (string) $byId;
        }

        $byName = DB::table('tb_permohonan_uji_klinik_haji')
            ->whereNull('deleted_at')
            ->where('nama_haji', self::HAJI_NAME)
            ->orderByDesc('created_at')
            ->value('id_permohonan_uji_klinik_haji');

        return $byName ? (string) $byName : null;
    }
}
