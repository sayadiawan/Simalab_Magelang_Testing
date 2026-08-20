<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * line_height_hasil = 0 menyebabkan validasi save-fontsize-hasil gagal (min 0.5).
 */
class FixInvalidLineHeightHasilZeroOnPermohonan extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')
            || !Schema::hasColumn('tb_permohonan_uji_klinik_2', 'line_height_hasil_permohonan_uji_klinik')
        ) {
            return;
        }

        DB::table('tb_permohonan_uji_klinik_2')
            ->whereNull('deleted_at')
            ->where('line_height_hasil_permohonan_uji_klinik', '<', 0.5)
            ->update([
                'line_height_hasil_permohonan_uji_klinik' => 1,
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        // Data operasional — tidak di-rollback otomatis.
    }
}
