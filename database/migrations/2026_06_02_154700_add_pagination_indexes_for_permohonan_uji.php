<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPaginationIndexesForPermohonanUji extends Migration
{
    public function up()
    {
        // Core filter/sort path for permohonan list.
        $this->createIndexIfMissing('tb_permohonan_uji', 'idx_perm_uji_status_deleted_date', ['status', 'deleted_at', 'date_permohonan_uji']);
        $this->createIndexIfMissing('tb_permohonan_uji', 'idx_perm_uji_customer_deleted', ['customer_id', 'deleted_at']);

        // Hot paths for pagination prefetch and join (samples by permohonan + soft delete).
        $this->createIndexIfMissing('tb_samples', 'idx_samples_permohonan_deleted', ['permohonan_uji_id', 'deleted_at']);
        $this->createIndexIfMissing('tb_samples', 'idx_samples_permohonan_packet_deleted', ['permohonan_uji_id', 'packet_id', 'deleted_at']);
        $this->createIndexIfMissing('tb_samples', 'idx_samples_permohonan_type_deleted', ['permohonan_uji_id', 'typesample_samples', 'deleted_at']);

        // Method lookup from sample.
        $this->createIndexIfMissing('tb_sample_method', 'idx_sample_method_sample_deleted', ['sample_id', 'deleted_at']);

        // Validation-activity count per sample.
        $this->createIndexIfMissing('tb_verification_activity_samples', 'idx_verif_sample_activity', ['id_sample', 'id_verification_activity']);
    }

    public function down()
    {
        $this->dropIndexIfExists('tb_verification_activity_samples', 'idx_verif_sample_activity');

        $this->dropIndexIfExists('tb_sample_method', 'idx_sample_method_sample_deleted');

        $this->dropIndexIfExists('tb_samples', 'idx_samples_permohonan_type_deleted');
        $this->dropIndexIfExists('tb_samples', 'idx_samples_permohonan_packet_deleted');
        $this->dropIndexIfExists('tb_samples', 'idx_samples_permohonan_deleted');

        $this->dropIndexIfExists('tb_permohonan_uji', 'idx_perm_uji_customer_deleted');
        $this->dropIndexIfExists('tb_permohonan_uji', 'idx_perm_uji_status_deleted_date');
    }

    private function createIndexIfMissing($table, $indexName, array $columns)
    {
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
        if (!$this->indexExists($table, $indexName)) {
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
}

