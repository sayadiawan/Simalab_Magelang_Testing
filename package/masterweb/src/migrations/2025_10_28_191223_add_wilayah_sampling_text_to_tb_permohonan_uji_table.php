<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWilayahSamplingTextToTbPermohonanUjiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_permohonan_uji', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_permohonan_uji', 'provinsi_sampling_text')) {
            $table->string('provinsi_sampling_text')->nullable()->after('desa_sampling')->comment('Nama Provinsi untuk sampling laboratorium');
            }
            if (!Schema::hasColumn('tb_permohonan_uji', 'kabupaten_sampling_text')) {
            $table->string('kabupaten_sampling_text')->nullable()->after('provinsi_sampling_text')->comment('Nama Kabupaten/Kota untuk sampling laboratorium');
            }
            if (!Schema::hasColumn('tb_permohonan_uji', 'kecamatan_sampling_text')) {
            $table->string('kecamatan_sampling_text')->nullable()->after('kabupaten_sampling_text')->comment('Nama Kecamatan untuk sampling laboratorium');
            }
            if (!Schema::hasColumn('tb_permohonan_uji', 'desa_sampling_text')) {
            $table->string('desa_sampling_text')->nullable()->after('kecamatan_sampling_text')->comment('Nama Desa/Kelurahan untuk sampling laboratorium');
            }
            if (!Schema::hasColumn('tb_permohonan_uji', 'alamat_lengkap_sampling')) {
            $table->text('alamat_lengkap_sampling')->nullable()->after('desa_sampling_text')->comment('Alamat lengkap wilayah sampling');
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
            $columnsToCheck = [
                'alamat_lengkap_sampling',
                'desa_sampling_text',
                'kecamatan_sampling_text',
                'kabupaten_sampling_text',
                'provinsi_sampling_text'
            ];
            
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('tb_permohonan_uji', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
