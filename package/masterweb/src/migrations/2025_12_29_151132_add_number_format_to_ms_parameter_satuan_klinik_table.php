<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNumberFormatToMsParameterSatuanKlinikTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ms_parameter_satuan_klinik', function (Blueprint $table) {
            // Cek apakah kolom number_format sudah ada
            if (!Schema::hasColumn('ms_parameter_satuan_klinik', 'number_format')) {
                // Menambahkan kolom number_format untuk menentukan format angka
                // 'id' = Indonesia (1.234,56 - ribuan: titik, desimal: koma)
                // 'en' = International (1,234.56 - ribuan: koma, desimal: titik)
                $table->enum('number_format', ['id', 'en'])->default('en')->after('option')->comment('Format angka: id=Indonesia(1.234,56), en=International(1,234.56)');
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
        Schema::table('ms_parameter_satuan_klinik', function (Blueprint $table) {
            // Cek apakah kolom number_format ada sebelum drop
            if (Schema::hasColumn('ms_parameter_satuan_klinik', 'number_format')) {
                $table->dropColumn('number_format');
            }
        });
    }
}
