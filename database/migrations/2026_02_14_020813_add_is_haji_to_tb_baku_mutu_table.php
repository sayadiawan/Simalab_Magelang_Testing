<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsHajiToTbBakuMutuTable20260214 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_baku_mutu', function (Blueprint $table) {
            $table->tinyInteger('is_haji')->default(0)->after('is_khusus_baku_mutu')->comment('0 = Non-Haji, 1 = Haji');
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
            $table->dropColumn('is_haji');
        });
    }
}
