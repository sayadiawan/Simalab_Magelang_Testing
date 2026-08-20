<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeteranganMetodeToTbSamples extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_samples')) {
            return;
        }

        if (!Schema::hasColumn('tb_samples', 'keterangan_metode')) {
            Schema::table('tb_samples', function (Blueprint $table) {
                $table->text('keterangan_metode')->nullable()->after('show_kop_hasil_baca_hasil');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('tb_samples')) {
            return;
        }

        if (Schema::hasColumn('tb_samples', 'keterangan_metode')) {
            Schema::table('tb_samples', function (Blueprint $table) {
                $table->dropColumn('keterangan_metode');
            });
        }
    }
}
