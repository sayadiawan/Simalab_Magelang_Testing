<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOffsetBakuMutuToPermohonanUjiParameterKlinikTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add offset_baku_mutu column to tb_permohonan_uji_parameter_klinik
        if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'offset_baku_mutu')) {
            Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
                $table->string('offset_baku_mutu', 20)->default('default')->nullable()->after('keterangan_permohonan_uji_parameter_klinik');
            });
        }

        // Add offset_baku_mutu column to tb_permohonan_uji_sub_parameter_klinik
        if (!Schema::hasColumn('tb_permohonan_uji_sub_parameter_klinik', 'offset_baku_mutu')) {
            Schema::table('tb_permohonan_uji_sub_parameter_klinik', function (Blueprint $table) {
                $table->string('offset_baku_mutu', 20)->default('default')->nullable()->after('keterangan_permohonan_uji_sub_parameter_klinik');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove offset_baku_mutu column from tb_permohonan_uji_sub_parameter_klinik
        if (Schema::hasColumn('tb_permohonan_uji_sub_parameter_klinik', 'offset_baku_mutu')) {
            Schema::table('tb_permohonan_uji_sub_parameter_klinik', function (Blueprint $table) {
                $table->dropColumn('offset_baku_mutu');
            });
        }

        // Remove offset_baku_mutu column from tb_permohonan_uji_parameter_klinik
        if (Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'offset_baku_mutu')) {
            Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
                $table->dropColumn('offset_baku_mutu');
            });
        }
    }
}
