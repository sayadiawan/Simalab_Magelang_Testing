<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFontsizeToPermohonanUjiKlinik2 extends Migration
{
    public function up()
    {
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->decimal('fontsize_hasil_permohonan_uji_klinik', 4, 1)
                  ->nullable()
                  ->default(12.0)
                  ->after('catatan_hasil')
                  ->comment('Ukuran font (pt) untuk cetak hasil pemeriksaan');
        });
    }

    public function down()
    {
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->dropColumn('fontsize_hasil_permohonan_uji_klinik');
        });
    }
}
