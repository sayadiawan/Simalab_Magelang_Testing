<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCatatanHasilToTbSamples extends Migration
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

        if (!Schema::hasColumn('tb_samples', 'catatan_hasil')) {
            Schema::table('tb_samples', function (Blueprint $table) {
                $table->text('catatan_hasil')->nullable()->after('keterangan_metode');
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

        if (Schema::hasColumn('tb_samples', 'catatan_hasil')) {
            Schema::table('tb_samples', function (Blueprint $table) {
                $table->dropColumn('catatan_hasil');
            });
        }
    }
}
