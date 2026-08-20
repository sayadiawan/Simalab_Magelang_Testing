<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Foreign keys tb_samples (semua relasi yang belum terhubung):
 * - typesample_samples  → ms_sample_type.id_sample_type
 * - jenis_makanan_id    → ms_jenis_makanan.id_jenis_makanan
 * - program_samples     → ms_program.id_program
 * - unit_samples        → ms_unit.id_unit
 * - sample_type_group   → ms_sample_type.id_sample_type
 *
 * Sudah ada sebelumnya:
 * - permohonan_uji_id   → tb_permohonan_uji.id_permohonan_uji
 * - packet_id           → ms_packet.id_packet
 *
 * group_id sengaja tidak di-FK (UUID sesi input, bukan referensi id_samples).
 */
class AddTbSamplesForeignKeys extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_samples')) {
            return;
        }

        $this->alignForeignKeyColumns();
        $this->nullifyOrphanRows();
        $this->addRemainingForeignKeys();
    }

    public function down()
    {
        $this->dropForeignKeyIfExists('tb_samples', 'fk_samples_sample_type_group');
        $this->dropForeignKeyIfExists('tb_samples', 'fk_samples_unit');
        $this->dropForeignKeyIfExists('tb_samples', 'fk_samples_program');
        $this->dropForeignKeyIfExists('tb_samples', 'fk_samples_jenis_makanan');
        $this->dropForeignKeyIfExists('tb_samples', 'fk_samples_sample_type');
    }

    private function addRemainingForeignKeys(): void
    {
        $this->addForeignKeyIfMissing(
            'tb_samples',
            'fk_samples_sample_type',
            'typesample_samples',
            'ms_sample_type',
            'id_sample_type',
            'RESTRICT'
        );

        $this->addForeignKeyIfMissing(
            'tb_samples',
            'fk_samples_jenis_makanan',
            'jenis_makanan_id',
            'ms_jenis_makanan',
            'id_jenis_makanan',
            'SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'tb_samples',
            'fk_samples_program',
            'program_samples',
            'ms_program',
            'id_program',
            'SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'tb_samples',
            'fk_samples_unit',
            'unit_samples',
            'ms_unit',
            'id_unit',
            'SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'tb_samples',
            'fk_samples_sample_type_group',
            'sample_type_group',
            'ms_sample_type',
            'id_sample_type',
            'SET NULL'
        );
    }

    private function alignForeignKeyColumns(): void
    {
        $this->alignChildToParent('tb_samples', 'typesample_samples', 'ms_sample_type', 'id_sample_type', false);
        $this->alignChildToParent('tb_samples', 'jenis_makanan_id', 'ms_jenis_makanan', 'id_jenis_makanan', true);
        $this->alignChildToParent('tb_samples', 'program_samples', 'ms_program', 'id_program', true);
        $this->alignChildToParent('tb_samples', 'unit_samples', 'ms_unit', 'id_unit', true);
        $this->alignChildToParent('tb_samples', 'sample_type_group', 'ms_sample_type', 'id_sample_type', true);
    }

    private function alignChildToParent(string $table, string $column, string $parentTable, string $parentColumn, bool $nullable): void
    {
        if (!Schema::hasTable($table) || !Schema::hasTable($parentTable)) {
            return;
        }

        if (!Schema::hasColumn($table, $column) || !Schema::hasColumn($parentTable, $parentColumn)) {
            return;
        }

        $meta = $this->getColumnMeta($parentTable, $parentColumn);
        if (!$meta) {
            return;
        }

        $this->modifyColumnToMatch($table, $column, $meta, $nullable);
    }

    private function nullifyOrphanRows(): void
    {
        $this->nullifyOrphans('tb_samples', 'jenis_makanan_id', 'ms_jenis_makanan', 'id_jenis_makanan');
        $this->nullifyOrphans('tb_samples', 'program_samples', 'ms_program', 'id_program');
        $this->nullifyOrphans('tb_samples', 'unit_samples', 'ms_unit', 'id_unit');
        $this->nullifyOrphans('tb_samples', 'sample_type_group', 'ms_sample_type', 'id_sample_type');
    }

    private function nullifyOrphans(string $table, string $column, string $parentTable, string $parentColumn): void
    {
        if (!Schema::hasTable($table) || !Schema::hasTable($parentTable)) {
            return;
        }

        if (!Schema::hasColumn($table, $column) || !Schema::hasColumn($parentTable, $parentColumn)) {
            return;
        }

        DB::statement(
            "UPDATE `{$table}` AS child " .
            "LEFT JOIN `{$parentTable}` AS parent ON parent.`{$parentColumn}` = child.`{$column}` " .
            "SET child.`{$column}` = NULL " .
            "WHERE child.`{$column}` IS NOT NULL AND child.`{$column}` <> '' AND parent.`{$parentColumn}` IS NULL"
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

    private function addForeignKeyIfMissing($table, $constraintName, $column, $refTable, $refColumn, $onDelete = 'RESTRICT')
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
            "ON DELETE {$onDelete} ON UPDATE CASCADE"
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
