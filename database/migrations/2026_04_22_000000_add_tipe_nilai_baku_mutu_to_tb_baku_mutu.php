<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTipeNilaiBakuMutuToTbBakuMutu extends Migration
{
    public function up()
    {
        Schema::table('tb_baku_mutu', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_baku_mutu', 'tipe_nilai_baku_mutu')) {
                $table->string('tipe_nilai_baku_mutu', 20)->nullable()->after('jenis_makanan_id');
            }
        });
    }

    public function down()
    {
        Schema::table('tb_baku_mutu', function (Blueprint $table) {
            if (Schema::hasColumn('tb_baku_mutu', 'tipe_nilai_baku_mutu')) {
                $table->dropColumn('tipe_nilai_baku_mutu');
            }
        });
    }
}
