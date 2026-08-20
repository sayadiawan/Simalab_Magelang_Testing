<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom nomor spesimen & nomor lab manual (Kesmas) pada draft — tanpa ->after()
 * agar aman di semua varian MySQL/MariaDB.
 */
class AddKesmasManualNomorColumnsToTbSampleDraft extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_sample_draft')) {
            return;
        }

        Schema::table('tb_sample_draft', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_sample_draft', 'nomor_spesimen_manual')) {
                $table->string('nomor_spesimen_manual', 50)->nullable()
                    ->comment('Angka urut nomor spesimen, kosong = otomatis');
            }
            if (!Schema::hasColumn('tb_sample_draft', 'nomor_lab_kimia_manual')) {
                $table->string('nomor_lab_kimia_manual', 50)->nullable()
                    ->comment('Angka urut nomor lab Kimia');
            }
            if (!Schema::hasColumn('tb_sample_draft', 'nomor_lab_mikro_manual')) {
                $table->string('nomor_lab_mikro_manual', 50)->nullable()
                    ->comment('Angka urut nomor lab Mikrobiologi');
            }
            if (!Schema::hasColumn('tb_sample_draft', 'is_nomor_sampel_manual')) {
                $table->boolean('is_nomor_sampel_manual')->default(false)
                    ->comment('1 = kode sampel di draft diisi manual (Kesmas)');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('tb_sample_draft')) {
            return;
        }

        Schema::table('tb_sample_draft', function (Blueprint $table) {
            if (Schema::hasColumn('tb_sample_draft', 'is_nomor_sampel_manual')) {
                $table->dropColumn('is_nomor_sampel_manual');
            }
            if (Schema::hasColumn('tb_sample_draft', 'nomor_lab_mikro_manual')) {
                $table->dropColumn('nomor_lab_mikro_manual');
            }
            if (Schema::hasColumn('tb_sample_draft', 'nomor_lab_kimia_manual')) {
                $table->dropColumn('nomor_lab_kimia_manual');
            }
            if (Schema::hasColumn('tb_sample_draft', 'nomor_spesimen_manual')) {
                $table->dropColumn('nomor_spesimen_manual');
            }
        });
    }
}
