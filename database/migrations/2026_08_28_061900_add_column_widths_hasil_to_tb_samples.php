<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lebar kolom tabel hasil Kesmas (JSON % per profil layout).
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_28_061900_add_column_widths_hasil_to_tb_samples.php
 */
class AddColumnWidthsHasilToTbSamples extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_samples')) {
            return;
        }

        Schema::table('tb_samples', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_samples', 'column_widths_hasil_baca_hasil')) {
                $table->text('column_widths_hasil_baca_hasil')->nullable();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('tb_samples')) {
            return;
        }

        Schema::table('tb_samples', function (Blueprint $table) {
            if (Schema::hasColumn('tb_samples', 'column_widths_hasil_baca_hasil')) {
                $table->dropColumn('column_widths_hasil_baca_hasil');
            }
        });
    }
}
