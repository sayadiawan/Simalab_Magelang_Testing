<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WidenNilaiBakuMutuColumns extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('tb_baku_mutu', 'nilai_baku_mutu')) {
            DB::statement('ALTER TABLE tb_baku_mutu MODIFY nilai_baku_mutu TEXT NULL');
        }

        if (Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            && Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'baku_mutu_permohonan_uji_parameter_klinik')) {
            DB::statement('ALTER TABLE tb_permohonan_uji_parameter_klinik MODIFY baku_mutu_permohonan_uji_parameter_klinik TEXT NULL');
        }

        if (Schema::hasTable('tb_permohonan_uji_parameter_klinik_2')
            && Schema::hasColumn('tb_permohonan_uji_parameter_klinik_2', 'baku_mutu_permohonan_uji_parameter_klinik')) {
            DB::statement('ALTER TABLE tb_permohonan_uji_parameter_klinik_2 MODIFY baku_mutu_permohonan_uji_parameter_klinik TEXT NULL');
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tb_baku_mutu', 'nilai_baku_mutu')) {
            DB::statement('ALTER TABLE tb_baku_mutu MODIFY nilai_baku_mutu VARCHAR(255) NULL');
        }

        if (Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            && Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'baku_mutu_permohonan_uji_parameter_klinik')) {
            DB::statement('ALTER TABLE tb_permohonan_uji_parameter_klinik MODIFY baku_mutu_permohonan_uji_parameter_klinik VARCHAR(100) NULL');
        }

        if (Schema::hasTable('tb_permohonan_uji_parameter_klinik_2')
            && Schema::hasColumn('tb_permohonan_uji_parameter_klinik_2', 'baku_mutu_permohonan_uji_parameter_klinik')) {
            DB::statement('ALTER TABLE tb_permohonan_uji_parameter_klinik_2 MODIFY baku_mutu_permohonan_uji_parameter_klinik VARCHAR(100) NULL');
        }
    }
}
