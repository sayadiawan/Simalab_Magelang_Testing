<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReviewHasilSettingsToTbSamples extends Migration
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

        Schema::table('tb_samples', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_samples', 'fontsize_hasil_baca_hasil')) {
                $table->decimal('fontsize_hasil_baca_hasil', 4, 1)->nullable()->default(12);
            }
            if (!Schema::hasColumn('tb_samples', 'line_height_hasil_baca_hasil')) {
                $table->decimal('line_height_hasil_baca_hasil', 3, 1)->nullable()->default(1.5);
            }
            if (!Schema::hasColumn('tb_samples', 'padding_hasil_baca_hasil')) {
                $table->decimal('padding_hasil_baca_hasil', 4, 1)->nullable()->default(4.0);
            }
            if (!Schema::hasColumn('tb_samples', 'show_kop_hasil_baca_hasil')) {
                $table->tinyInteger('show_kop_hasil_baca_hasil')->nullable()->default(1);
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
        if (!Schema::hasTable('tb_samples')) {
            return;
        }

        Schema::table('tb_samples', function (Blueprint $table) {
            if (Schema::hasColumn('tb_samples', 'fontsize_hasil_baca_hasil')) {
                $table->dropColumn('fontsize_hasil_baca_hasil');
            }
            if (Schema::hasColumn('tb_samples', 'line_height_hasil_baca_hasil')) {
                $table->dropColumn('line_height_hasil_baca_hasil');
            }
            if (Schema::hasColumn('tb_samples', 'padding_hasil_baca_hasil')) {
                $table->dropColumn('padding_hasil_baca_hasil');
            }
            if (Schema::hasColumn('tb_samples', 'show_kop_hasil_baca_hasil')) {
                $table->dropColumn('show_kop_hasil_baca_hasil');
            }
        });
    }
}
