<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDiambilLokasiRumahToModePengambilanSampel extends Migration
{
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

        if (!Schema::hasColumn('tb_permohonan_uji_klinik_2', 'mode_pengambilan_sampel')) {
            return;
        }

        DB::statement("ALTER TABLE tb_permohonan_uji_klinik_2
            MODIFY COLUMN mode_pengambilan_sampel
            ENUM('diambil_lab','dibawa_pelanggan','diambil_lokasi_rumah','') NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            return;
        }

        if (!Schema::hasColumn('tb_permohonan_uji_klinik_2', 'mode_pengambilan_sampel')) {
            return;
        }

        // Reset nilai yang tidak ada di enum lama agar rollback aman
        DB::table('tb_permohonan_uji_klinik_2')
            ->where('mode_pengambilan_sampel', 'diambil_lokasi_rumah')
            ->update(['mode_pengambilan_sampel' => 'diambil_lab']);

        DB::statement("ALTER TABLE tb_permohonan_uji_klinik_2
            MODIFY COLUMN mode_pengambilan_sampel
            ENUM('diambil_lab','dibawa_pelanggan','') NULL DEFAULT NULL");
    }
}
