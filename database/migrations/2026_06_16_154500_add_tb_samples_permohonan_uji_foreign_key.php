<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Foreign key tb_samples:
 * - permohonan_uji_id → tb_permohonan_uji.id_permohonan_uji
 */
class AddTbSamplesPermohonanUjiForeignKey extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_samples')) {
            return;
        }

        $this->alignForeignKeyColumn();
        $this->nullifyOrphanRows();

        $this->addForeignKeyIfMissing(
            'tb_samples',
            'fk_samples_permohonan_uji',
            'permohonan_uji_id',
            'tb_permohonan_uji',
            'id_permohonan_uji'
        );
    }

    public function down()
    {
        $this->dropForeignKeyIfExists('tb_samples', 'fk_samples_permohonan_uji');
    }

    private function alignForeignKeyColumn(): void
    {
        if (!Schema::hasTable('tb_permohonan_uji') || !Schema::hasColumn('tb_samples', 'permohonan_uji_id')) {
            return;
        }

        $meta = $this->getColumnMeta('tb_permohonan_uji', 'id_permohonan_uji');
        if (!$meta) {
            return;
        }

        $this->modifyColumnToMatch('tb_samples', 'permohonan_uji_id', $meta, true);
    }

    private function nullifyOrphanRows(): void
    {
        if (!Schema::hasTable('tb_permohonan_uji') || !Schema::hasColumn('tb_samples', 'permohonan_uji_id')) {
            return;
        }

        DB::statement(
            'UPDATE `tb_samples` AS s ' .
            'LEFT JOIN `tb_permohonan_uji` AS p ON p.`id_permohonan_uji` = s.`permohonan_uji_id` ' .
            'SET s.`permohonan_uji_id` = NULL ' .
            'WHERE s.`permohonan_uji_id` IS NOT NULL AND s.`permohonan_uji_id` <> \'\' AND p.`id_permohonan_uji` IS NULL'
        );
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
            "ON DELETE SET NULL ON UPDATE CASCADE"
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
