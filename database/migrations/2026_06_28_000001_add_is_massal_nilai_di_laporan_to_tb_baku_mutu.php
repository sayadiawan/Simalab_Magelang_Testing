<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsMassalNilaiDiLaporanToTbBakuMutu extends Migration
{
    public function up()
    {
        Schema::table('tb_baku_mutu', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_baku_mutu', 'is_massal_nilai_di_laporan')) {
                $table->tinyInteger('is_massal_nilai_di_laporan')
                    ->default(0)
                    ->after('nilai_baku_mutu')
                    ->comment('1 = gunakan satu nilai di laporan untuk seluruh grup');
            }
        });
    }

    public function down()
    {
        Schema::table('tb_baku_mutu', function (Blueprint $table) {
            if (Schema::hasColumn('tb_baku_mutu', 'is_massal_nilai_di_laporan')) {
                $table->dropColumn('is_massal_nilai_di_laporan');
            }
        });
    }
}
