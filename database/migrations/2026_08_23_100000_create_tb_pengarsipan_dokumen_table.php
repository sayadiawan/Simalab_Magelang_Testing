<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel arsip dokumen tambahan pengarsip.
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_23_100000_create_tb_pengarsipan_dokumen_table.php
 */
class CreateTbPengarsipanDokumenTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_pengarsipan_dokumen')) {
            Schema::create('tb_pengarsipan_dokumen', function (Blueprint $table) {
                $table->uuid('id_pengarsipan_dokumen')->primary();
                $table->string('nomor_arsip', 100)->nullable();
                $table->string('bidang', 20)->default('umum');
                $table->string('judul', 255);
                $table->text('keterangan')->nullable();
                $table->string('ref_bidang', 20)->nullable();
                $table->string('ref_id', 64)->nullable();
                $table->string('ref_label', 255)->nullable();
                $table->string('file_path', 500);
                $table->string('file_name_original', 255);
                $table->string('file_mime', 120)->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->unsignedSmallInteger('tahun')->nullable();
                $table->uuid('uploaded_by')->nullable();
                $table->string('uploaded_by_name', 191)->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['nomor_arsip'], 'idx_pengarsip_dok_nomor');
                $table->index(['bidang', 'tahun'], 'idx_pengarsip_dok_bidang_tahun');
                $table->index(['judul'], 'idx_pengarsip_dok_judul');
                $table->index(['ref_bidang', 'ref_id'], 'idx_pengarsip_dok_ref');
                $table->index(['created_at'], 'idx_pengarsip_dok_created');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('tb_pengarsipan_dokumen');
    }
}
