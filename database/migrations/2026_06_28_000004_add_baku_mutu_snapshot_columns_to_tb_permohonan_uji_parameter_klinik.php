<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBakuMutuSnapshotColumnsToTbPermohonanUjiParameterKlinik extends Migration
{
    public function up()
    {
        Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'min_baku_mutu_permohonan_uji_parameter_klinik')) {
                $table->string('min_baku_mutu_permohonan_uji_parameter_klinik', 100)->nullable();
            }
            if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'max_baku_mutu_permohonan_uji_parameter_klinik')) {
                $table->string('max_baku_mutu_permohonan_uji_parameter_klinik', 100)->nullable();
            }
            if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'equal_baku_mutu_permohonan_uji_parameter_klinik')) {
                $table->string('equal_baku_mutu_permohonan_uji_parameter_klinik', 100)->nullable();
            }
            if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'kesimpulan_baku_mutu_permohonan_uji_parameter_klinik')) {
                $table->string('kesimpulan_baku_mutu_permohonan_uji_parameter_klinik', 255)->nullable();
            }
            if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik')) {
                $table->text('keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
            $columns = [
                'min_baku_mutu_permohonan_uji_parameter_klinik',
                'max_baku_mutu_permohonan_uji_parameter_klinik',
                'equal_baku_mutu_permohonan_uji_parameter_klinik',
                'kesimpulan_baku_mutu_permohonan_uji_parameter_klinik',
                'keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('tb_permohonan_uji_parameter_klinik', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
