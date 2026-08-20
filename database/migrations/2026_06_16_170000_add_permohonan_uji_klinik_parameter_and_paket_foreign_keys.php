<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Foreign keys tb_permohonan_uji_parameter_klinik & tb_permohonan_uji_paket_klinik.
 */
class AddPermohonanUjiKlinikParameterAndPaketForeignKeys extends Migration
{
    public function up()
    {
        $this->alignForeignKeyColumns();
        $this->nullifyOrphanRows();
        $this->addPaketKlinikForeignKeys();
        $this->addParameterKlinikForeignKeys();
    }

    public function down()
    {
        $this->dropForeignKeyIfExists('tb_permohonan_uji_parameter_klinik', 'fk_param_klinik_paket_master');
        $this->dropForeignKeyIfExists('tb_permohonan_uji_parameter_klinik', 'fk_param_klinik_paket_jenis');
        $this->dropForeignKeyIfExists('tb_permohonan_uji_parameter_klinik', 'fk_param_klinik_jenis');
        $this->dropForeignKeyIfExists('tb_permohonan_uji_parameter_klinik', 'fk_param_klinik_satuan');
        $this->dropForeignKeyIfExists('tb_permohonan_uji_parameter_klinik', 'fk_param_klinik_paket');
        $this->dropForeignKeyIfExists('tb_permohonan_uji_parameter_klinik', 'fk_param_klinik_permohonan');

        $this->dropForeignKeyIfExists('tb_permohonan_uji_paket_klinik', 'fk_paket_klinik_extra');
        $this->dropForeignKeyIfExists('tb_permohonan_uji_paket_klinik', 'fk_paket_klinik_paket');
        $this->dropForeignKeyIfExists('tb_permohonan_uji_paket_klinik', 'fk_paket_klinik_jenis');
        $this->dropForeignKeyIfExists('tb_permohonan_uji_paket_klinik', 'fk_paket_klinik_permohonan');
    }

    private function addPaketKlinikForeignKeys(): void
    {
        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_paket_klinik',
            'fk_paket_klinik_permohonan',
            'permohonan_uji_klinik',
            'tb_permohonan_uji_klinik_2',
            'id_permohonan_uji_klinik',
            'SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_paket_klinik',
            'fk_paket_klinik_jenis',
            'parameter_jenis_klinik',
            'ms_parameter_jenis_klinik',
            'id_parameter_jenis_klinik',
            'SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_paket_klinik',
            'fk_paket_klinik_paket',
            'parameter_paket_klinik',
            'ms_parameter_paket_klinik',
            'id_parameter_paket_klinik',
            'SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_paket_klinik',
            'fk_paket_klinik_extra',
            'parameter_paket_extra',
            'ms_parameter_paket_extra',
            'id_parameter_paket_extra',
            'SET NULL'
        );
    }

    private function addParameterKlinikForeignKeys(): void
    {
        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_parameter_klinik',
            'fk_param_klinik_permohonan',
            'permohonan_uji_klinik',
            'tb_permohonan_uji_klinik_2',
            'id_permohonan_uji_klinik',
            'RESTRICT'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_parameter_klinik',
            'fk_param_klinik_paket',
            'permohonan_uji_paket_klinik',
            'tb_permohonan_uji_paket_klinik',
            'id_permohonan_uji_paket_klinik',
            'SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_parameter_klinik',
            'fk_param_klinik_satuan',
            'parameter_satuan_klinik',
            'ms_parameter_satuan_klinik',
            'id_parameter_satuan_klinik',
            'SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_parameter_klinik',
            'fk_param_klinik_jenis',
            'jenis_parameter_klinik_id',
            'ms_parameter_jenis_klinik',
            'id_parameter_jenis_klinik',
            'RESTRICT'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_parameter_klinik',
            'fk_param_klinik_paket_jenis',
            'parameter_paket_jenis_klinik',
            'ms_parameter_paket_jenis_klinik',
            'id_parameter_paket_jenis_klinik',
            'SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_parameter_klinik',
            'fk_param_klinik_paket_master',
            'parameter_paket_klinik',
            'ms_parameter_paket_klinik',
            'id_parameter_paket_klinik',
            'SET NULL'
        );
    }

    private function alignForeignKeyColumns(): void
    {
        $this->alignChildToParent('tb_permohonan_uji_parameter_klinik', 'permohonan_uji_klinik', 'tb_permohonan_uji_klinik_2', 'id_permohonan_uji_klinik', false);
        $this->alignChildToParent('tb_permohonan_uji_paket_klinik', 'permohonan_uji_klinik', 'tb_permohonan_uji_klinik_2', 'id_permohonan_uji_klinik', true);
        $this->alignChildToParent('tb_permohonan_uji_paket_klinik', 'parameter_paket_extra', 'ms_parameter_paket_extra', 'id_parameter_paket_extra', true);

        $this->alignChildToParent('tb_permohonan_uji_parameter_klinik', 'permohonan_uji_paket_klinik', 'tb_permohonan_uji_paket_klinik', 'id_permohonan_uji_paket_klinik', true);
        $this->alignChildToParent('tb_permohonan_uji_parameter_klinik', 'parameter_satuan_klinik', 'ms_parameter_satuan_klinik', 'id_parameter_satuan_klinik', true);
        $this->alignChildToParent('tb_permohonan_uji_parameter_klinik', 'jenis_parameter_klinik_id', 'ms_parameter_jenis_klinik', 'id_parameter_jenis_klinik', false);
        $this->alignChildToParent('tb_permohonan_uji_parameter_klinik', 'parameter_paket_jenis_klinik', 'ms_parameter_paket_jenis_klinik', 'id_parameter_paket_jenis_klinik', true);
        $this->alignChildToParent('tb_permohonan_uji_parameter_klinik', 'parameter_paket_klinik', 'ms_parameter_paket_klinik', 'id_parameter_paket_klinik', true);

        $this->alignChildToParent('tb_permohonan_uji_paket_klinik', 'parameter_jenis_klinik', 'ms_parameter_jenis_klinik', 'id_parameter_jenis_klinik', true);
        $this->alignChildToParent('tb_permohonan_uji_paket_klinik', 'parameter_paket_klinik', 'ms_parameter_paket_klinik', 'id_parameter_paket_klinik', true);
    }

    private function alignChildToParent(string $table, string $column, string $parentTable, string $parentColumn, bool $nullable): void
    {
        if (!Schema::hasTable($table) || !Schema::hasTable($parentTable)) {
            return;
        }

        if (!Schema::hasColumn($table, $column) || !Schema::hasColumn($parentTable, $parentColumn)) {
            return;
        }

        // Hindari SQLSTATE 1832: kolom tidak boleh diubah saat masih dipakai FK aktif.
        if ($this->foreignKeyOnColumnExists($table, $column)) {
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
        $this->nullifyOrphans(
            'tb_permohonan_uji_paket_klinik',
            'permohonan_uji_klinik',
            'tb_permohonan_uji_klinik_2',
            'id_permohonan_uji_klinik'
        );

        $this->nullifyOrphans(
            'tb_permohonan_uji_paket_klinik',
            'parameter_jenis_klinik',
            'ms_parameter_jenis_klinik',
            'id_parameter_jenis_klinik'
        );

        $this->nullifyOrphans(
            'tb_permohonan_uji_paket_klinik',
            'parameter_paket_klinik',
            'ms_parameter_paket_klinik',
            'id_parameter_paket_klinik'
        );

        $this->nullifyOrphans(
            'tb_permohonan_uji_paket_klinik',
            'parameter_paket_extra',
            'ms_parameter_paket_extra',
            'id_parameter_paket_extra'
        );

        $this->nullifyOrphans(
            'tb_permohonan_uji_parameter_klinik',
            'permohonan_uji_paket_klinik',
            'tb_permohonan_uji_paket_klinik',
            'id_permohonan_uji_paket_klinik'
        );

        $this->nullifyOrphans(
            'tb_permohonan_uji_parameter_klinik',
            'parameter_satuan_klinik',
            'ms_parameter_satuan_klinik',
            'id_parameter_satuan_klinik'
        );

        $this->nullifyOrphans(
            'tb_permohonan_uji_parameter_klinik',
            'parameter_paket_jenis_klinik',
            'ms_parameter_paket_jenis_klinik',
            'id_parameter_paket_jenis_klinik'
        );

        $this->nullifyOrphans(
            'tb_permohonan_uji_parameter_klinik',
            'parameter_paket_klinik',
            'ms_parameter_paket_klinik',
            'id_parameter_paket_klinik'
        );
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

    private function foreignKeyOnColumnExists(string $table, string $column): bool
    {
        return DB::table('information_schema.key_column_usage')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('column_name', $column)
            ->whereNotNull('referenced_table_name')
            ->exists();
    }
}
