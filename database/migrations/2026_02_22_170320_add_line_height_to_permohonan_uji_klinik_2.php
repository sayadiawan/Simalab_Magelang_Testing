<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLineHeightToPermohonanUjiKlinik2 extends Migration
{
    public function up()
    {
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->decimal('line_height_hasil_permohonan_uji_klinik', 3, 1)
                  ->default(1.5)
                  ->after('show_kop_hasil_permohonan_uji_klinik')
                  ->comment('Line spacing / line-height untuk hasil cetak, misal 1.0, 1.5, 2.0');
        });
    }

    public function down()
    {
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->dropColumn('line_height_hasil_permohonan_uji_klinik');
        });
    }
}
