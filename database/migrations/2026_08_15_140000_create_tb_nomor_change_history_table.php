<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat penggantian nomor spesimen/lab/kode sampel.
 * Tidak mengubah nomor yang sudah ada — hanya menyimpan jejak perubahan ke depan.
 */
class CreateTbNomorChangeHistoryTable extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_nomor_change_history')) {
            return;
        }

        Schema::create('tb_nomor_change_history', function (Blueprint $table) {
            $table->uuid('id_nomor_change_history')->primary();
            $table->string('subject_type', 32);
            $table->uuid('subject_id');
            $table->string('field_name', 64);
            $table->string('old_value', 191)->nullable();
            $table->string('new_value', 191)->nullable();
            $table->string('event', 32)->default('penggantian');
            $table->string('source', 64)->nullable();
            $table->string('note', 255)->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['subject_type', 'subject_id'], 'idx_nomor_hist_subject');
            $table->index(['field_name', 'created_at'], 'idx_nomor_hist_field_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_nomor_change_history');
    }
}
