<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom sample_type_id ke tb_nomer_lab_kesmas.
 *
 * Nomer lab sekarang unik per (permohonan_uji, laboratorium, sample_type):
 * setiap kombinasi jenis sampel + lab mendapat nomer lab sendiri ketika semua
 * sampel dengan jenis tersebut di lab yang sama sudah selesai pengesahan.
 */
class AddSampleTypeIdToTbNomerLabKesmas extends Migration
{
    public function up()
    {
        // Tambah kolom sample_type_id jika belum ada
        if (!Schema::hasColumn('tb_nomer_lab_kesmas', 'sample_type_id')) {
            Schema::table('tb_nomer_lab_kesmas', function (Blueprint $table) {
                $table->string('sample_type_id', 36)->nullable()->after('laboratorium_id');
                $table->index('sample_type_id', 'idx_nomer_lab_kesmas_sample_type');
            });
        }

        // Hapus unique constraint lama (permohonan_uji_id, laboratorium_id)
        try {
            Schema::table('tb_nomer_lab_kesmas', function (Blueprint $table) {
                $table->dropUnique('unique_nomer_lab_kesmas');
            });
        } catch (\Throwable $e) {
            // Constraint mungkin sudah tidak ada atau berbeda nama
        }

        // Tambahkan unique constraint baru jika belum ada
        $indexExists = \DB::select("
            SELECT COUNT(*) as cnt
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'tb_nomer_lab_kesmas'
              AND INDEX_NAME = 'unique_nomer_lab_kesmas_per_type'
        ");
        if (empty($indexExists) || $indexExists[0]->cnt == 0) {
            Schema::table('tb_nomer_lab_kesmas', function (Blueprint $table) {
                $table->unique(
                    ['permohonan_uji_id', 'laboratorium_id', 'sample_type_id'],
                    'unique_nomer_lab_kesmas_per_type'
                );
            });
        }
    }

    public function down()
    {
        Schema::table('tb_nomer_lab_kesmas', function (Blueprint $table) {
            try {
                $table->dropUnique('unique_nomer_lab_kesmas_per_type');
            } catch (\Throwable $e) {
            }

            if (Schema::hasColumn('tb_nomer_lab_kesmas', 'sample_type_id')) {
                $table->dropColumn('sample_type_id');
            }

            // Pulihkan unique constraint lama
            $table->unique(['permohonan_uji_id', 'laboratorium_id'], 'unique_nomer_lab_kesmas');
        });
    }
}
