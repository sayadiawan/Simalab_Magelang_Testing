<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Index + foreign key: tb_verification_activity_samples.is_klinik → tb_permohonan_uji_klinik_2.id_permohonan_uji_klinik
 */
class AddVerificationActivitySamplesKlinikForeignKey extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_verification_activity_samples')
            || !Schema::hasColumn('tb_verification_activity_samples', 'is_klinik')) {
            return;
        }

        $this->alignKlinikForeignKeyColumns();
        $this->nullifyOrphanIsKlinik();

        $this->createIndexIfMissing(
            'tb_verification_activity_samples',
            'idx_verif_sample_is_klinik',
            ['is_klinik']
        );

        $this->addForeignKeyIfMissing(
            'tb_verification_activity_samples',
            'fk_verif_klinik_permohonan',
            'is_klinik',
            'tb_permohonan_uji_klinik_2',
            'id_permohonan_uji_klinik'
        );
    }

    public function down()
    {
        $this->dropForeignKeyIfExists('tb_verification_activity_samples', 'fk_verif_klinik_permohonan');
        $this->dropIndexIfExists('tb_verification_activity_samples', 'idx_verif_sample_is_klinik');
    }

    private function alignKlinikForeignKeyColumns(): void
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            return;
        }

        $parent = $this->getColumnMeta('tb_permohonan_uji_klinik_2', 'id_permohonan_uji_klinik');
        $charset = $parent->CHARACTER_SET_NAME ?? 'latin1';
        $collation = $parent->COLLATION_NAME ?? 'latin1_swedish_ci';

        DB::statement(
            'ALTER TABLE `tb_verification_activity_samples` ' .
            'MODIFY COLUMN `is_klinik` CHAR(36) ' .
            "CHARACTER SET {$charset} COLLATE {$collation} NULL"
        );
    }

    private function getColumnMeta(string $table, string $column)
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.columns')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('column_name', $column)
            ->first(['CHARACTER_SET_NAME', 'COLLATION_NAME']);
    }

    private function nullifyOrphanIsKlinik(): void
    {
        DB::statement(
            "UPDATE `tb_verification_activity_samples` SET `is_klinik` = NULL WHERE `is_klinik` = ''"
        );

        DB::statement(
            'UPDATE `tb_verification_activity_samples` AS v ' .
            'LEFT JOIN `tb_permohonan_uji_klinik_2` AS p ' .
            'ON p.`id_permohonan_uji_klinik` = v.`is_klinik` ' .
            'SET v.`is_klinik` = NULL ' .
            'WHERE v.`is_klinik` IS NOT NULL AND p.`id_permohonan_uji_klinik` IS NULL'
        );
    }

    private function createIndexIfMissing($table, $indexName, array $columns)
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return;
            }
        }

        if ($this->indexExists($table, $indexName)) {
            return;
        }

        $wrappedColumns = array_map(function ($column) {
            return "`{$column}`";
        }, $columns);

        DB::statement("CREATE INDEX `{$indexName}` ON `{$table}` (" . implode(', ', $wrappedColumns) . ")");
    }

    private function dropIndexIfExists($table, $indexName)
    {
        if (!Schema::hasTable($table) || !$this->indexExists($table, $indexName)) {
            return;
        }

        DB::statement("DROP INDEX `{$indexName}` ON `{$table}`");
    }

    private function indexExists($table, $indexName)
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
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

        try {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraintName}`");
        } catch (\Throwable $e) {
            // ignore
        }
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
