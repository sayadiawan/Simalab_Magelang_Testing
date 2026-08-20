<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMarginSettingsToPermohonanUjiKlinik2 extends Migration
{
    public function up()
    {
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->decimal('padding_top_hasil_permohonan_uji_klinik', 4, 1)
                ->nullable()
                ->after('padding_hasil_permohonan_uji_klinik')
                ->comment('Padding atas sel tabel hasil cetak (pt)');

            $table->decimal('padding_bottom_hasil_permohonan_uji_klinik', 4, 1)
                ->nullable()
                ->after('padding_top_hasil_permohonan_uji_klinik')
                ->comment('Padding bawah sel tabel hasil cetak (pt)');

            $table->decimal('margin_left_hasil_permohonan_uji_klinik', 5, 1)
                ->nullable()
                ->default(20)
                ->after('padding_bottom_hasil_permohonan_uji_klinik')
                ->comment('Margin kiri halaman cetak hasil (px)');

            $table->decimal('margin_right_hasil_permohonan_uji_klinik', 5, 1)
                ->nullable()
                ->default(20)
                ->after('margin_left_hasil_permohonan_uji_klinik')
                ->comment('Margin kanan halaman cetak hasil (px)');
        });
    }

    public function down()
    {
        Schema::table('tb_permohonan_uji_klinik_2', function (Blueprint $table) {
            $table->dropColumn([
                'padding_top_hasil_permohonan_uji_klinik',
                'padding_bottom_hasil_permohonan_uji_klinik',
                'margin_left_hasil_permohonan_uji_klinik',
                'margin_right_hasil_permohonan_uji_klinik',
            ]);
        });
    }
}
