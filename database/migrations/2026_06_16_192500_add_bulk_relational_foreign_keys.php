<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddBulkRelationalForeignKeys extends Migration
{
    public function up()
    {
        foreach ($this->mappings() as $map) {
            [$childTable, $childColumn, $parentTable, $parentColumn] = $map;
            $this->processMapping($childTable, $childColumn, $parentTable, $parentColumn);
        }
    }

    public function down()
    {
        foreach ($this->mappings() as $map) {
            [$childTable, $childColumn] = $map;
            $this->dropForeignKeyIfExists($childTable, $this->constraintName($childTable, $childColumn));
        }
    }

    private function mappings(): array
    {
        return [
            ['tb_baku_mutu', 'jenis_makanan_id', 'ms_jenis_makanan', 'id_jenis_makanan'],
            ['tb_baku_mutu', 'method_id', 'ms_method', 'id_method'],
            ['tb_baku_mutu_detail_parameter_non_klinik', 'method_id', 'ms_method', 'id_method'],
            ['tb_baku_mutu_sample_override', 'method_id', 'ms_method', 'id_method'],
            ['tb_delegation_sampling', 'method_id', 'ms_method', 'id_method'],
            ['tb_delegation_sampling', 'permohonan_uji_id', 'tb_permohonan_uji', 'id_permohonan_uji'],
            ['tb_kebisingan', 'permohonan_uji_id', 'tb_permohonan_uji', 'id_permohonan_uji'],
            ['tb_lab_num', 'permohonan_uji_id', 'tb_permohonan_uji', 'id_permohonan_uji'],
            ['tb_lab_num', 'sample_id', 'tb_samples', 'id_samples'],
            ['tb_laboratorium_method', 'laboratorium_id', 'ms_laboratorium', 'id_laboratorium'],
            ['tb_laboratorium_method', 'method_id', 'ms_method', 'id_method'],
            ['tb_laboratorium_packet', 'laboratorium_id', 'ms_laboratorium', 'id_laboratorium'],
            ['tb_laboratorium_packet', 'packet_id', 'ms_packet', 'id_packet'],
            ['tb_laboratorium_progress', 'laboratorium_id', 'ms_laboratorium', 'id_laboratorium'],
            ['tb_lhu', 'sample_id', 'tb_samples', 'id_samples'],
            ['tb_method_sampling', 'method_id', 'ms_method', 'id_method'],
            ['tb_method_sampling', 'permohonan_uji_id', 'tb_permohonan_uji', 'id_permohonan_uji'],
            ['tb_nomer_lab_kesmas', 'laboratorium_id', 'ms_laboratorium', 'id_laboratorium'],
            ['tb_nomer_lab_kesmas', 'permohonan_uji_id', 'tb_permohonan_uji', 'id_permohonan_uji'],
            ['tb_pelaporan_hasil', 'laboratorium_id', 'ms_laboratorium', 'id_laboratorium'],
            ['tb_pelaporan_hasil', 'sample_id', 'tb_samples', 'id_samples'],
            ['tb_pencahayaan', 'permohonan_uji_id', 'tb_permohonan_uji', 'id_permohonan_uji'],
            ['tb_pengambilan_sample', 'permohonan_uji_id', 'tb_permohonan_uji', 'id_permohonan_uji'],
            ['tb_pengesahan_hasil', 'laboratorium_id', 'ms_laboratorium', 'id_laboratorium'],
            ['tb_pengesahan_hasil', 'sample_id', 'tb_samples', 'id_samples'],
            ['tb_pengetikan_hasil', 'laboratorium_id', 'ms_laboratorium', 'id_laboratorium'],
            ['tb_pengetikan_hasil', 'sample_id', 'tb_samples', 'id_samples'],
            ['tb_sample_analitik_progress', 'laboratorium_id', 'ms_laboratorium', 'id_laboratorium'],
            ['tb_sample_analitik_progress', 'sample_id', 'tb_samples', 'id_samples'],
            ['tb_sample_draft', 'packet_id', 'ms_packet', 'id_packet'],
            ['tb_sample_draft', 'permohonan_uji_id', 'tb_permohonan_uji', 'id_permohonan_uji'],
            ['tb_sample_draft', 'program_samples', 'ms_program', 'id_program'],
            ['tb_sample_draft', 'typesample_samples', 'ms_sample_type', 'id_sample_type'],
            ['tb_sample_method_draft', 'laboratorium_id', 'ms_laboratorium', 'id_laboratorium'],
            ['tb_sample_method_draft', 'method_id', 'ms_method', 'id_method'],
            ['tb_sample_penanganan', 'laboratorium_id', 'ms_laboratorium', 'id_laboratorium'],
            ['tb_sample_penanganan', 'sample_id', 'tb_samples', 'id_samples'],
            ['tb_sample_penerimaan', 'laboratorium_id', 'ms_laboratorium', 'id_laboratorium'],
            ['tb_sample_penerimaan', 'sample_id', 'tb_samples', 'id_samples'],
            ['tb_sample_result_detail', 'method_id', 'ms_method', 'id_method'],
            ['tb_sample_result_detail', 'sample_id', 'tb_samples', 'id_samples'],
            ['tb_verifikasi_hasil', 'laboratorium_id', 'ms_laboratorium', 'id_laboratorium'],
            ['tb_verifikasi_hasil', 'sample_id', 'tb_samples', 'id_samples'],
        ];
    }

    private function processMapping(string $childTable, string $childColumn, string $parentTable, string $parentColumn): void
    {
        if (!Schema::hasTable($childTable) || !Schema::hasTable($parentTable)) {
            return;
        }

        if (!Schema::hasColumn($childTable, $childColumn) || !Schema::hasColumn($parentTable, $parentColumn)) {
            return;
        }

        if ($this->foreignKeyOnColumnExists($childTable, $childColumn)) {
            return;
        }

        $childMeta = $this->getColumnMeta($childTable, $childColumn);
        $parentMeta = $this->getColumnMeta($parentTable, $parentColumn);
        if (!$childMeta || !$parentMeta) {
            return;
        }

        $isNullable = strtoupper((string) $childMeta->IS_NULLABLE) === 'YES';
        $this->modifyColumnToMatch($childTable, $childColumn, $parentMeta, $isNullable);

        $orphans = $this->countOrphans($childTable, $childColumn, $parentTable, $parentColumn);

        // Sesuai keputusan: orphan hanya dinullkan jika child nullable.
        if ($orphans > 0 && $isNullable) {
            $this->nullifyOrphans($childTable, $childColumn, $parentTable, $parentColumn);
            $orphans = $this->countOrphans($childTable, $childColumn, $parentTable, $parentColumn);
        }

        // Jika masih orphan (biasanya kolom NOT NULL), skip agar migration tidak gagal.
        if ($orphans > 0) {
            return;
        }

        $onDelete = $isNullable ? 'SET NULL' : 'RESTRICT';
        $this->addForeignKeyIfMissing(
            $childTable,
            $this->constraintName($childTable, $childColumn),
            $childColumn,
            $parentTable,
            $parentColumn,
            $onDelete
        );
    }

    private function getColumnMeta(string $table, string $column)
    {
        return DB::table('information_schema.columns')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('column_name', $column)
            ->first(['COLUMN_TYPE', 'CHARACTER_SET_NAME', 'COLLATION_NAME', 'IS_NULLABLE']);
    }

    private function modifyColumnToMatch(string $table, string $column, $parentMeta, bool $nullable): void
    {
        $type = $parentMeta->COLUMN_TYPE ?? 'varchar(36)';
        $charset = $parentMeta->CHARACTER_SET_NAME;
        $collation = $parentMeta->COLLATION_NAME;
        $nullSql = $nullable ? 'NULL' : 'NOT NULL';

        $charsetSql = $charset && $collation ? " CHARACTER SET {$charset} COLLATE {$collation}" : '';

        DB::statement(
            "ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` {$type}{$charsetSql} {$nullSql}"
        );
    }

    private function countOrphans(string $childTable, string $childColumn, string $parentTable, string $parentColumn): int
    {
        $row = DB::selectOne(
            "SELECT COUNT(*) AS c FROM `{$childTable}` AS c " .
            "LEFT JOIN `{$parentTable}` AS p ON p.`{$parentColumn}` = c.`{$childColumn}` " .
            "WHERE c.`{$childColumn}` IS NOT NULL AND c.`{$childColumn}` <> '' AND p.`{$parentColumn}` IS NULL"
        );

        return (int) ($row->c ?? 0);
    }

    private function nullifyOrphans(string $childTable, string $childColumn, string $parentTable, string $parentColumn): void
    {
        DB::statement(
            "UPDATE `{$childTable}` AS c " .
            "LEFT JOIN `{$parentTable}` AS p ON p.`{$parentColumn}` = c.`{$childColumn}` " .
            "SET c.`{$childColumn}` = NULL " .
            "WHERE c.`{$childColumn}` IS NOT NULL AND c.`{$childColumn}` <> '' AND p.`{$parentColumn}` IS NULL"
        );
    }

    private function addForeignKeyIfMissing(
        string $table,
        string $constraintName,
        string $column,
        string $refTable,
        string $refColumn,
        string $onDelete
    ): void {
        if ($this->foreignKeyExists($table, $constraintName)) {
            return;
        }

        DB::statement(
            "ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraintName}` " .
            "FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}` (`{$refColumn}`) " .
            "ON DELETE {$onDelete} ON UPDATE CASCADE"
        );
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

    private function dropForeignKeyIfExists(string $table, string $constraintName): void
    {
        if (!Schema::hasTable($table) || !$this->foreignKeyExists($table, $constraintName)) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraintName}`");
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('constraint_name', $constraintName)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }

    private function constraintName(string $childTable, string $childColumn): string
    {
        return substr('fk_' . str_replace('tb_', '', $childTable) . '_' . $childColumn, 0, 62);
    }
}
