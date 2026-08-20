<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Foreign keys tb_permohonan_uji:
 * - customer_id            → ms_customer.id_customer
 * - nota_petugas_penerima  → ms_users.id
 * - provinsi_sampling      → ms_wilayah.id_wilayah
 * - kabupaten_sampling     → ms_wilayah.id_wilayah
 * - kecamatan_sampling     → ms_wilayah.id_wilayah
 * - desa_sampling          → ms_wilayah.id_wilayah
 */
class AddTbPermohonanUjiForeignKeys extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji')) {
            return;
        }

        $this->alignForeignKeyColumns();
        $this->nullifyOrphanRows();
        $this->addForeignKeys();
    }

    public function down()
    {
        $this->dropForeignKeyIfExists('tb_permohonan_uji', 'fk_permohonan_uji_desa');
        $this->dropForeignKeyIfExists('tb_permohonan_uji', 'fk_permohonan_uji_kecamatan');
        $this->dropForeignKeyIfExists('tb_permohonan_uji', 'fk_permohonan_uji_kabupaten');
        $this->dropForeignKeyIfExists('tb_permohonan_uji', 'fk_permohonan_uji_provinsi');
        $this->dropForeignKeyIfExists('tb_permohonan_uji', 'fk_permohonan_uji_petugas');
        $this->dropForeignKeyIfExists('tb_permohonan_uji', 'fk_permohonan_uji_customer');
    }

    private function addForeignKeys(): void
    {
        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji',
            'fk_permohonan_uji_customer',
            'customer_id',
            'ms_customer',
            'id_customer',
            'RESTRICT'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji',
            'fk_permohonan_uji_petugas',
            'nota_petugas_penerima',
            'ms_users',
            'id',
            'SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji',
            'fk_permohonan_uji_provinsi',
            'provinsi_sampling',
            'ms_wilayah',
            'id_wilayah',
            'SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji',
            'fk_permohonan_uji_kabupaten',
            'kabupaten_sampling',
            'ms_wilayah',
            'id_wilayah',
            'SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji',
            'fk_permohonan_uji_kecamatan',
            'kecamatan_sampling',
            'ms_wilayah',
            'id_wilayah',
            'SET NULL'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji',
            'fk_permohonan_uji_desa',
            'desa_sampling',
            'ms_wilayah',
            'id_wilayah',
            'SET NULL'
        );
    }

    private function alignForeignKeyColumns(): void
    {
        $this->alignChildToParent('tb_permohonan_uji', 'customer_id', 'ms_customer', 'id_customer', false);
        $this->alignChildToParent('tb_permohonan_uji', 'nota_petugas_penerima', 'ms_users', 'id', true);
        $this->alignChildToParent('tb_permohonan_uji', 'provinsi_sampling', 'ms_wilayah', 'id_wilayah', true);
        $this->alignChildToParent('tb_permohonan_uji', 'kabupaten_sampling', 'ms_wilayah', 'id_wilayah', true);
        $this->alignChildToParent('tb_permohonan_uji', 'kecamatan_sampling', 'ms_wilayah', 'id_wilayah', true);
        $this->alignChildToParent('tb_permohonan_uji', 'desa_sampling', 'ms_wilayah', 'id_wilayah', true);
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
        $this->nullifyOrphans('tb_permohonan_uji', 'nota_petugas_penerima', 'ms_users', 'id');
        $this->nullifyOrphans('tb_permohonan_uji', 'provinsi_sampling', 'ms_wilayah', 'id_wilayah');
        $this->nullifyOrphans('tb_permohonan_uji', 'kabupaten_sampling', 'ms_wilayah', 'id_wilayah');
        $this->nullifyOrphans('tb_permohonan_uji', 'kecamatan_sampling', 'ms_wilayah', 'id_wilayah');
        $this->nullifyOrphans('tb_permohonan_uji', 'desa_sampling', 'ms_wilayah', 'id_wilayah');
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
