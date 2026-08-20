<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Salin ms_method.name_method ke tb_sample_result.metode
 * untuk setiap baris yang method_id-nya cocok dengan ms_method.id_method.
 *
 * Rollback tidak mengembalikan nilai metode lama.
 */
class SyncTbSampleResultMetodeFromMsMethod extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_sample_result') || !Schema::hasTable('ms_method')) {
            return;
        }

        if (!Schema::hasColumn('tb_sample_result', 'metode')
            || !Schema::hasColumn('tb_sample_result', 'method_id')
            || !Schema::hasColumn('ms_method', 'name_method')) {
            return;
        }

        DB::statement("
            UPDATE tb_sample_result AS sr
            INNER JOIN ms_method AS m
                ON m.id_method = sr.method_id
                AND m.deleted_at IS NULL
            SET sr.metode = m.name_method
            WHERE sr.deleted_at IS NULL
        ");
    }

    public function down()
    {
        // Data migration: nilai metode sebelum sync tidak disimpan.
    }
}
