<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom nomor_spesimen_mikro_manual yang terlewat di tb_sample_draft.
 * Kolom ini menyimpan angka urut nomor spesimen Mikrobiologi (lab kode 02).
 * Tanpa kolom ini nilai Mikro selalu jatuh kembali ke nilai Kimia saat konfirmasi.
 */
class AddNomorSpesimenMikroManualToTbSampleDraft extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_sample_draft')) {
            return;
        }

        Schema::table('tb_sample_draft', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_sample_draft', 'nomor_spesimen_mikro_manual')) {
                $table->string('nomor_spesimen_mikro_manual', 50)->nullable()
                    ->comment('Angka urut nomor spesimen Mikrobiologi (02/urut/tahun), kosong = otomatis');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('tb_sample_draft')) {
            return;
        }

        Schema::table('tb_sample_draft', function (Blueprint $table) {
            if (Schema::hasColumn('tb_sample_draft', 'nomor_spesimen_mikro_manual')) {
                $table->dropColumn('nomor_spesimen_mikro_manual');
            }
        });
    }
}
