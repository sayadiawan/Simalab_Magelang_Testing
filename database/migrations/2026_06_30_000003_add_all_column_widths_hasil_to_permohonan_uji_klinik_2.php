<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllColumnWidthsHasilToPermohonanUjiKlinik2 extends Migration
{
    public function up()
    {
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->decimal('lebar_kolom_pemeriksaan_hasil_permohonan_uji_klinik', 4, 1)
                ->nullable()
                ->after('lebar_kolom_satuan_hasil_permohonan_uji_klinik');
            $table->decimal('lebar_kolom_hasil_hasil_permohonan_uji_klinik', 4, 1)
                ->nullable()
                ->after('lebar_kolom_pemeriksaan_hasil_permohonan_uji_klinik');
            $table->decimal('lebar_kolom_metode_hasil_permohonan_uji_klinik', 4, 1)
                ->nullable()
                ->after('lebar_kolom_hasil_hasil_permohonan_uji_klinik');
            $table->decimal('lebar_kolom_nilai_normal_hasil_permohonan_uji_klinik', 4, 1)
                ->nullable()
                ->after('lebar_kolom_metode_hasil_permohonan_uji_klinik');
        });
    }

    public function down()
    {
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->dropColumn([
                'lebar_kolom_pemeriksaan_hasil_permohonan_uji_klinik',
                'lebar_kolom_hasil_hasil_permohonan_uji_klinik',
                'lebar_kolom_metode_hasil_permohonan_uji_klinik',
                'lebar_kolom_nilai_normal_hasil_permohonan_uji_klinik',
            ]);
        });
    }
}
