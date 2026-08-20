<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambah flag per sampel: nomor sampel / nomor laboratorium diisi manual (alur Kesmas).
 * Duplikat dari package masterweb agar migrate di root project selalu menjalankan perubahan ini.
 */
class AddIsManualNomorToTbSamplesTable extends Migration
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
            if (!Schema::hasColumn('tb_samples', 'is_nomor_sampel_manual')) {
                $table->boolean('is_nomor_sampel_manual')->default(false)
                    ->comment('1 = kode/nomor sampel diisi manual (Kesmas)');
            }
            if (!Schema::hasColumn('tb_samples', 'is_nomor_laboratorium_manual')) {
                $table->boolean('is_nomor_laboratorium_manual')->default(false)
                    ->comment('1 = nomor laboratorium diisi manual (Kesmas)');
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
            if (Schema::hasColumn('tb_samples', 'is_nomor_laboratorium_manual')) {
                $table->dropColumn('is_nomor_laboratorium_manual');
            }
            if (Schema::hasColumn('tb_samples', 'is_nomor_sampel_manual')) {
                $table->dropColumn('is_nomor_sampel_manual');
            }
        });
    }
}
