<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeVolumeSampelToTextOnTbPermohonanUjiKlinik2 extends Migration
{
    /**
     * Run the migrations.
     *
     * volume_sampel sebelumnya VARCHAR(100) — JSON per jenis sampel
     * (Darah, Serum, Plasma, Urine, Blood Cell, Plasma NaF, ...) mudah terpotong
     * sehingga tampilan analis menjadi "-" / JSON rusak.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            return;
        }

        if (!Schema::hasColumn('tb_permohonan_uji_klinik_2', 'volume_sampel')) {
            return;
        }

        DB::statement('ALTER TABLE tb_permohonan_uji_klinik_2 MODIFY volume_sampel TEXT NULL');
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

        if (!Schema::hasColumn('tb_permohonan_uji_klinik_2', 'volume_sampel')) {
            return;
        }

        DB::statement("UPDATE tb_permohonan_uji_klinik_2 SET volume_sampel = LEFT(volume_sampel, 100) WHERE volume_sampel IS NOT NULL AND CHAR_LENGTH(volume_sampel) > 100");
        DB::statement('ALTER TABLE tb_permohonan_uji_klinik_2 MODIFY volume_sampel VARCHAR(100) NULL');
    }
}
