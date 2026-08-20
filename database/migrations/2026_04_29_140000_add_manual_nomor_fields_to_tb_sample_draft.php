<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nomor spesimen & laboratorium manual (angka urut) untuk draft sampel Kesmas,
 * pola penyimpanan sama seperti klinik: urut saja; format penuh dibentuk saat konfirmasi.
 */
class AddManualNomorFieldsToTbSampleDraft extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_sample_draft')) {
            return;
        }

        Schema::table('tb_sample_draft', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_sample_draft', 'nomor_spesimen_manual')) {
                $table->string('nomor_spesimen_manual', 50)->nullable()
                    ->comment('Angka urut nomor spesimen (format 03/urut/tahun), kosong = otomatis');
            }
            if (!Schema::hasColumn('tb_sample_draft', 'nomor_lab_kimia_manual')) {
                $table->string('nomor_lab_kimia_manual', 50)->nullable()
                    ->comment('Angka urut nomor lab Kimia (449.5/03/urut/tahun)');
            }
            if (!Schema::hasColumn('tb_sample_draft', 'nomor_lab_mikro_manual')) {
                $table->string('nomor_lab_mikro_manual', 50)->nullable()
                    ->comment('Angka urut nomor lab Mikrobiologi');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('tb_sample_draft')) {
            return;
        }

        Schema::table('tb_sample_draft', function (Blueprint $table) {
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
