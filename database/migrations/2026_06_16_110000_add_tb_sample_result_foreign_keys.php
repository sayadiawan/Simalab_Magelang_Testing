<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Foreign key tb_sample_result:
 * - method_id      → ms_method.id_method
 * - sample_id      → tb_samples.id_samples
 * - laboratorium_id → ms_laboratorium.id_laboratorium
 */
class AddTbSampleResultForeignKeys extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_sample_result')) {
            return;
        }

        $this->alignForeignKeyColumns();
        $this->deleteOrphanRows();

        $this->addForeignKeyIfMissing(
            'tb_sample_result',
            'fk_sample_result_method',
            'method_id',
            'ms_method',
            'id_method'
        );

        $this->addForeignKeyIfMissing(
            'tb_sample_result',
            'fk_sample_result_sample',
            'sample_id',
            'tb_samples',
            'id_samples'
        );

        $this->addForeignKeyIfMissing(
            'tb_sample_result',
            'fk_sample_result_laboratorium',
            'laboratorium_id',
            'ms_laboratorium',
            'id_laboratorium'
        );
    }

    public function down()
    {
        $this->dropForeignKeyIfExists('tb_sample_result', 'fk_sample_result_laboratorium');
        $this->dropForeignKeyIfExists('tb_sample_result', 'fk_sample_result_sample');
        $this->dropForeignKeyIfExists('tb_sample_result', 'fk_sample_result_method');
    }

    private function alignForeignKeyColumns(): void
    {
        if (Schema::hasTable('ms_method') && Schema::hasColumn('tb_sample_result', 'method_id')) {
            $meta = $this->getColumnMeta('ms_method', 'id_method');
            if ($meta) {
                $this->modifyColumnToMatch('tb_sample_result', 'method_id', $meta, false);
            }
        }

        if (Schema::hasTable('tb_samples') && Schema::hasColumn('tb_sample_result', 'sample_id')) {
            $meta = $this->getColumnMeta('tb_samples', 'id_samples');
            if ($meta) {
                $this->modifyColumnToMatch('tb_sample_result', 'sample_id', $meta, false);
            }
        }

        if (Schema::hasTable('ms_laboratorium') && Schema::hasColumn('tb_sample_result', 'laboratorium_id')) {
            $meta = $this->getColumnMeta('ms_laboratorium', 'id_laboratorium');
            if ($meta) {
                $this->modifyColumnToMatch('tb_sample_result', 'laboratorium_id', $meta, false);
            }
        }
    }

    private function deleteOrphanRows(): void
    {
        if (Schema::hasTable('ms_method') && Schema::hasColumn('tb_sample_result', 'method_id')) {
            DB::statement(
                'DELETE sr FROM `tb_sample_result` AS sr ' .
                'LEFT JOIN `ms_method` AS m ON m.`id_method` = sr.`method_id` ' .
                'WHERE sr.`method_id` IS NOT NULL AND sr.`method_id` <> \'\' AND m.`id_method` IS NULL'
            );
        }

        if (Schema::hasTable('tb_samples') && Schema::hasColumn('tb_sample_result', 'sample_id')) {
            DB::statement(
                'DELETE sr FROM `tb_sample_result` AS sr ' .
                'LEFT JOIN `tb_samples` AS s ON s.`id_samples` = sr.`sample_id` ' .
                'WHERE sr.`sample_id` IS NOT NULL AND sr.`sample_id` <> \'\' AND s.`id_samples` IS NULL'
            );
        }

        if (Schema::hasTable('ms_laboratorium') && Schema::hasColumn('tb_sample_result', 'laboratorium_id')) {
            DB::statement(
                'DELETE sr FROM `tb_sample_result` AS sr ' .
                'LEFT JOIN `ms_laboratorium` AS l ON l.`id_laboratorium` = sr.`laboratorium_id` ' .
                'WHERE sr.`laboratorium_id` IS NOT NULL AND sr.`laboratorium_id` <> \'\' AND l.`id_laboratorium` IS NULL'
            );
        }
    }

    private function getColumnMeta(string $table, string $column)
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.columns')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('column_name', $column)
            ->first(['COLUMN_TYPE', 'CHARACTER_SET_NAME', 'COLLATION_NAME', 'IS_NULLABLE']);
    }

    private function modifyColumnToMatch(string $table, string $column, $parentMeta, bool $nullable): void
    {
        $type = $parentMeta->COLUMN_TYPE ?? 'varchar(36)';
        $charset = $parentMeta->CHARACTER_SET_NAME ?? 'latin1';
        $collation = $parentMeta->COLLATION_NAME ?? 'latin1_swedish_ci';
        $nullSql = ($nullable || strtoupper((string) $parentMeta->IS_NULLABLE) === 'YES') ? 'NULL' : 'NOT NULL';

        DB::statement(
            "ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` {$type} " .
            "CHARACTER SET {$charset} COLLATE {$collation} {$nullSql}"
        );
    }

    private function addForeignKeyIfMissing($table, $constraintName, $column, $refTable, $refColumn)
    {
        if (!Schema::hasTable($table) || !Schema::hasTable($refTable)) {
            return;
        }

        if (!Schema::hasColumn($table, $column) || !Schema::hasColumn($refTable, $refColumn)) {
            return;
        }

        if ($this->foreignKeyExists($table, $constraintName)) {
            return;
        }

        DB::statement(
            "ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraintName}` " .
            "FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}` (`{$refColumn}`) " .
            "ON DELETE RESTRICT ON UPDATE CASCADE"
        );
    }

    private function dropForeignKeyIfExists($table, $constraintName)
    {
        if (!Schema::hasTable($table) || !$this->foreignKeyExists($table, $constraintName)) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraintName}`");
    }

    private function foreignKeyExists($table, $constraintName)
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', $database)
            ->where('table_name', $table)
            ->where('constraint_name', $constraintName)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }
}
