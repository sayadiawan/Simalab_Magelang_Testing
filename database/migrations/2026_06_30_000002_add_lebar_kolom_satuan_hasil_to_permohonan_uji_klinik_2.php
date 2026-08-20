<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLebarKolomSatuanHasilToPermohonanUjiKlinik2 extends Migration
{
    public function up()
    {
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->decimal('lebar_kolom_satuan_hasil_permohonan_uji_klinik', 4, 1)
                ->nullable()
                ->after('margin_right_hasil_permohonan_uji_klinik')
                ->comment('Lebar kolom SATUAN di cetak hasil (%), default 14');
        });
    }

    public function down()
    {
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->dropColumn('lebar_kolom_satuan_hasil_permohonan_uji_klinik');
        });
    }
}
