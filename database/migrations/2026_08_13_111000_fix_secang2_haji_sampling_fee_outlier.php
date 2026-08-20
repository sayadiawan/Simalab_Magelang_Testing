<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Satu pasien Secang 2 Haji punya biaya_pengambilan_sampel = 19998
 * (harusnya 20000 seperti pasien lain), sehingga nota konsolidasi
 * tampil 859.998 bukan 860.000.
 */
class FixSecang2HajiSamplingFeeOutlier extends Migration
{
    const HAJI_ID = 'd7f0bdc8-2b9e-41f1-8e57-bfa95b3c149e';
    const EXPECTED_FEE = 20000;

    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            return;
        }

        DB::table('tb_permohonan_uji_klinik_2')
            ->where('id_permohonan_uji_klinik_haji', self::HAJI_ID)
            ->whereNull('deleted_at')
            ->where('mode_pengambilan_sampel', 'diambil_lokasi_rumah')
            ->where('biaya_pengambilan_sampel', '!=', self::EXPECTED_FEE)
            ->where('biaya_pengambilan_sampel', '>', 0)
            ->update([
                'biaya_pengambilan_sampel' => self::EXPECTED_FEE,
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        // Tidak rollback — nilai 19998 adalah data corrupt.
    }
}
