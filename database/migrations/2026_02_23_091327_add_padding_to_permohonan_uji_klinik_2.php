<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaddingToPermohonanUjiKlinik2 extends Migration
{
    public function up()
    {
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->decimal('padding_hasil_permohonan_uji_klinik', 4, 1)
                  ->default(4.0)
                  ->after('line_height_hasil_permohonan_uji_klinik')
                  ->comment('Padding atas/bawah sel tabel hasil cetak (pt), misal 2, 4, 6');
        });
    }

    public function down()
    {
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->dropColumn('padding_hasil_permohonan_uji_klinik');
        });
    }
}
