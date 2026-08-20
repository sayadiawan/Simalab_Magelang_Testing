<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWilayahSamplingToTbPermohonanUjiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_permohonan_uji', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_permohonan_uji', 'provinsi_sampling')) {
            $table->string('provinsi_sampling', 36)->nullable()->after('is_sampling')->comment('ID Provinsi untuk sampling laboratorium');
            }
            if (!Schema::hasColumn('tb_permohonan_uji', 'kabupaten_sampling')) {
            $table->string('kabupaten_sampling', 36)->nullable()->after('provinsi_sampling')->comment('ID Kabupaten/Kota untuk sampling laboratorium');
            }
            if (!Schema::hasColumn('tb_permohonan_uji', 'kecamatan_sampling')) {
            $table->string('kecamatan_sampling', 36)->nullable()->after('kabupaten_sampling')->comment('ID Kecamatan untuk sampling laboratorium');
            }
            if (!Schema::hasColumn('tb_permohonan_uji', 'desa_sampling')) {
            $table->string('desa_sampling', 36)->nullable()->after('kecamatan_sampling')->comment('ID Desa/Kelurahan untuk sampling laboratorium');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tb_permohonan_uji', function (Blueprint $table) {
            $columnsToCheck = ['desa_sampling', 'kecamatan_sampling', 'kabupaten_sampling', 'provinsi_sampling'];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('tb_permohonan_uji', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
