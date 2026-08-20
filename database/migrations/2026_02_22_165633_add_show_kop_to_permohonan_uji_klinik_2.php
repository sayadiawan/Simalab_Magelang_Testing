<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowKopToPermohonanUjiKlinik2 extends Migration
{
    public function up()
    {
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->tinyInteger('show_kop_hasil_permohonan_uji_klinik')
                  ->default(1)
                  ->after('fontsize_hasil_permohonan_uji_klinik')
                  ->comment('1 = tampilkan kop surat, 0 = sembunyikan kop (tapi beri space kosong)');
        });
    }

    public function down()
    {
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->dropColumn('show_kop_hasil_permohonan_uji_klinik');
        });
    }
}
