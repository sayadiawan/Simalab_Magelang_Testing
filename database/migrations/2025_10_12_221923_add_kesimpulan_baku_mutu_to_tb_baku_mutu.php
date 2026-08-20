<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKesimpulanBakuMutuToTbBakuMutu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_baku_mutu', function (Blueprint $table) {
            // Cek apakah kolom kesimpulan_baku_mutu sudah ada
            if (!Schema::hasColumn('tb_baku_mutu', 'kesimpulan_baku_mutu')) {
            $table->text('kesimpulan_baku_mutu')->nullable()->after('nilai_baku_mutu');
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
        Schema::table('tb_baku_mutu', function (Blueprint $table) {
            // Cek apakah kolom kesimpulan_baku_mutu ada sebelum drop
            if (Schema::hasColumn('tb_baku_mutu', 'kesimpulan_baku_mutu')) {
            $table->dropColumn('kesimpulan_baku_mutu');
            }
        });
    }
}
