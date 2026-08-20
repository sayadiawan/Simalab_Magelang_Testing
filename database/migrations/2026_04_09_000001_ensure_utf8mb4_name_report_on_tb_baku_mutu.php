<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nama parameter di laporan (TinyMCE) menyimpan Unicode, mis. simbol derajat °.
 * Jika kolom bukan utf8mb4, karakter tersebut menjadi ? di MySQL.
 */
class EnsureUtf8mb4NameReportOnTbBakuMutu extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_baku_mutu') || !Schema::hasColumn('tb_baku_mutu', 'name_report')) {
            return;
        }

        $row = DB::selectOne(
            'SELECT COLUMN_TYPE AS column_type, IS_NULLABLE AS is_nullable, COLUMN_DEFAULT AS column_default, COLLATION_NAME AS collation_name
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?',
            ['tb_baku_mutu', 'name_report']
        );

        if (!$row) {
            return;
        }

        $collation = $row->collation_name ?? '';
        if ($collation !== '' && strpos($collation, 'utf8mb4') === 0) {
            return;
        }

        $type = $row->column_type;
        $null = ($row->is_nullable ?? '') === 'YES' ? 'NULL' : 'NOT NULL';

        $defaultClause = '';
        if (($row->column_default ?? null) !== null && ($row->column_default ?? '') !== '') {
            $defaultClause = ' DEFAULT ' . DB::connection()->getPdo()->quote($row->column_default);
        }

        DB::statement(
            'ALTER TABLE `tb_baku_mutu` MODIFY `name_report` ' . $type .
            ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ' . $null . $defaultClause
        );
    }

    public function down()
    {
        // Pengembalian charset kolom tidak dilakukan agar data Unicode tetap aman.
    }
}
