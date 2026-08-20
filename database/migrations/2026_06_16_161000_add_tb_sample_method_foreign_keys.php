<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Foreign key tb_sample_method:
 * - sample_id       → tb_samples.id_samples
 * - method_id       → ms_method.id_method
 * - laboratorium_id → ms_laboratorium.id_laboratorium
 */
class AddTbSampleMethodForeignKeys extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_sample_method')) {
            return;
        }

        $this->alignForeignKeyColumns();
        $this->deleteOrphanRows();

        $this->addForeignKeyIfMissing(
            'tb_sample_method',
            'fk_sample_method_sample',
            'sample_id',
            'tb_samples',
            'id_samples'
        );

        $this->addForeignKeyIfMissing(
            'tb_sample_method',
            'fk_sample_method_method',
            'method_id',
            'ms_method',
            'id_method'
        );

        $this->addForeignKeyIfMissing(
            'tb_sample_method',
            'fk_sample_method_laboratorium',
            'laboratorium_id',
            'ms_laboratorium',
            'id_laboratorium'
        );
    }

    public function down()
    {
        $this->dropForeignKeyIfExists('tb_sample_method', 'fk_sample_method_laboratorium');
        $this->dropForeignKeyIfExists('tb_sample_method', 'fk_sample_method_method');
        $this->dropForeignKeyIfExists('tb_sample_method', 'fk_sample_method_sample');
    }

    private function alignForeignKeyColumns(): void
    {
        if (Schema::hasTable('tb_samples') && Schema::hasColumn('tb_sample_method', 'sample_id')) {
            $meta = $this->getColumnMeta('tb_samples', 'id_samples');
            if ($meta) {
                $this->modifyColumnToMatch('tb_sample_method', 'sample_id', $meta, false);
            }
        }

        if (Schema::hasTable('ms_method') && Schema::hasColumn('tb_sample_method', 'method_id')) {
            $meta = $this->getColumnMeta('ms_method', 'id_method');
            if ($meta) {
                $this->modifyColumnToMatch('tb_sample_method', 'method_id', $meta, false);
            }
        }

        if (Schema::hasTable('ms_laboratorium') && Schema::hasColumn('tb_sample_method', 'laboratorium_id')) {
            $meta = $this->getColumnMeta('ms_laboratorium', 'id_laboratorium');
            if ($meta) {
                $this->modifyColumnToMatch('tb_sample_method', 'laboratorium_id', $meta, false);
            }
        }
    }

    private function deleteOrphanRows(): void
    {
        if (Schema::hasTable('tb_samples') && Schema::hasColumn('tb_sample_method', 'sample_id')) {
            DB::statement(
                'DELETE sm FROM `tb_sample_method` AS sm ' .
                'LEFT JOIN `tb_samples` AS s ON s.`id_samples` = sm.`sample_id` ' .
                'WHERE sm.`sample_id` IS NOT NULL AND sm.`sample_id` <> \'\' AND s.`id_samples` IS NULL'
            );
        }

        if (Schema::hasTable('ms_method') && Schema::hasColumn('tb_sample_method', 'method_id')) {
            DB::statement(
                'DELETE sm FROM `tb_sample_method` AS sm ' .
                'LEFT JOIN `ms_method` AS m ON m.`id_method` = sm.`method_id` ' .
                'WHERE sm.`method_id` IS NOT NULL AND sm.`method_id` <> \'\' AND m.`id_method` IS NULL'
            );
        }

        if (Schema::hasTable('ms_laboratorium') && Schema::hasColumn('tb_sample_method', 'laboratorium_id')) {
            DB::statement(
                'DELETE sm FROM `tb_sample_method` AS sm ' .
                'LEFT JOIN `ms_laboratorium` AS l ON l.`id_laboratorium` = sm.`laboratorium_id` ' .
                'WHERE sm.`laboratorium_id` IS NOT NULL AND sm.`laboratorium_id` <> \'\' AND l.`id_laboratorium` IS NULL'
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
