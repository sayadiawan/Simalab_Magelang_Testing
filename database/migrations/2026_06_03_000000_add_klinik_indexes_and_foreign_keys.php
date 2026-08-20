<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Index + foreign key untuk modul klinik (list, analis, verifikasi, hasil).
 * Index: mempercepat JOIN/WHERE/ORDER BY pada pola query terbanyak.
 * FK: integritas relasi parent-child (dibuat aman, dilewati jika sudah ada / data orphan).
 */
class AddKlinikIndexesAndForeignKeys extends Migration
{
    public function up()
    {
        // --- tb_permohonan_uji_klinik_2 (list & filter utama) ---
        $this->createIndexIfMissing('tb_permohonan_uji_klinik_2', 'idx_puk2_pasien_deleted', ['pasien_permohonan_uji_klinik', 'deleted_at']);
        $this->createIndexIfMissing('tb_permohonan_uji_klinik_2', 'idx_puk2_deleted_created', ['deleted_at', 'created_at']);
        $this->createIndexIfMissing('tb_permohonan_uji_klinik_2', 'idx_puk2_status_deleted', ['status_permohonan_uji_klinik', 'deleted_at']);
        $this->createIndexIfMissing('tb_permohonan_uji_klinik_2', 'idx_puk2_status_bayar_deleted', ['status_pembayaran', 'deleted_at']);
        $this->createIndexIfMissing('tb_permohonan_uji_klinik_2', 'idx_puk2_prolanis_flags', ['is_prolanis_gula', 'is_prolanis_urine', 'is_haji']);

        // --- Child tables: lookup by permohonan (N+1 / prefetch) ---
        $this->createIndexIfMissing('tb_permohonan_uji_parameter_klinik', 'idx_param_klinik_permohonan_deleted', ['permohonan_uji_klinik', 'deleted_at']);
        $this->createIndexIfMissing('tb_permohonan_uji_parameter_klinik', 'idx_param_klinik_satuan_deleted', ['parameter_satuan_klinik', 'deleted_at']);
        $this->createIndexIfMissing('tb_permohonan_uji_parameter_klinik', 'idx_param_klinik_paket_deleted', ['permohonan_uji_paket_klinik', 'deleted_at']);

        $this->createIndexIfMissing('tb_permohonan_uji_sub_parameter_klinik', 'idx_sub_param_klinik_param_deleted', ['permohonan_uji_parameter_klinik_id', 'deleted_at']);

        $this->createIndexIfMissing('tb_permohonan_uji_paket_klinik', 'idx_paket_klinik_permohonan_deleted', ['permohonan_uji_klinik', 'deleted_at']);

        $this->createIndexIfMissing('tb_permohonan_uji_payment_klinik', 'idx_payment_klinik_permohonan_deleted', ['permohonan_uji_klinik_id', 'deleted_at']);

        $this->createIndexIfMissing('tb_permohonan_uji_analis_klinik', 'idx_analis_klinik_permohonan_deleted', ['permohonan_uji_klinik_id', 'deleted_at']);

        $this->createIndexIfMissing('tb_pengambilan_sample_klinik', 'idx_pengambilan_klinik_permohonan', ['permohonan_uji_klinik_id']);

        // --- History (analisis / verifikasi hasil) ---
        if (Schema::hasTable('tb_permohonan_uji_parameter_klinik_history')) {
            $this->createIndexIfMissing(
                'tb_permohonan_uji_parameter_klinik_history',
                'idx_hist_param_klinik_param_created',
                ['permohonan_uji_parameter_klinik_id', 'created_at']
            );
        }

        if (Schema::hasTable('tb_permohonan_uji_sub_parameter_klinik_history')) {
            $this->createIndexIfMissing(
                'tb_permohonan_uji_sub_parameter_klinik_history',
                'idx_hist_sub_param_klinik_sub_created',
                ['permohonan_uji_sub_parameter_klinik_id', 'created_at']
            );
        }

        // --- Verifikasi aktivitas per permohonan klinik ---
        if (Schema::hasColumn('tb_verification_activity_samples', 'is_klinik')) {
            $this->createIndexIfMissing('tb_verification_activity_samples', 'idx_verif_klinik_activity', ['is_klinik', 'id_verification_activity']);
            $this->createIndexIfMissing('tb_verification_activity_samples', 'idx_verif_klinik_activity_done', ['is_klinik', 'id_verification_activity', 'is_done']);
        }

        // --- ms_pasien: join list klinik ---
        if (Schema::hasTable('ms_pasien') && Schema::hasColumn('ms_pasien', 'no_rekammedis_pasien')) {
            $this->createIndexIfMissing('ms_pasien', 'idx_pasien_no_rm', ['no_rekammedis_pasien']);
        }

        // --- Foreign keys (aman: skip jika sudah ada atau gagal karena data orphan) ---
        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_klinik_2',
            'fk_puk2_pasien',
            'pasien_permohonan_uji_klinik',
            'ms_pasien',
            'id_pasien'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_parameter_klinik',
            'fk_param_klinik_permohonan',
            'permohonan_uji_klinik',
            'tb_permohonan_uji_klinik_2',
            'id_permohonan_uji_klinik'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_sub_parameter_klinik',
            'fk_sub_param_klinik_param',
            'permohonan_uji_parameter_klinik_id',
            'tb_permohonan_uji_parameter_klinik',
            'id_permohonan_uji_parameter_klinik'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_paket_klinik',
            'fk_paket_klinik_permohonan',
            'permohonan_uji_klinik',
            'tb_permohonan_uji_klinik_2',
            'id_permohonan_uji_klinik'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_payment_klinik',
            'fk_payment_klinik_permohonan',
            'permohonan_uji_klinik_id',
            'tb_permohonan_uji_klinik_2',
            'id_permohonan_uji_klinik'
        );

        $this->addForeignKeyIfMissing(
            'tb_permohonan_uji_analis_klinik',
            'fk_analis_klinik_permohonan',
            'permohonan_uji_klinik_id',
            'tb_permohonan_uji_klinik_2',
            'id_permohonan_uji_klinik'
        );

        if (Schema::hasTable('tb_pengambilan_sample_klinik')) {
            $this->addForeignKeyIfMissing(
                'tb_pengambilan_sample_klinik',
                'fk_pengambilan_klinik_permohonan',
                'permohonan_uji_klinik_id',
                'tb_permohonan_uji_klinik_2',
                'id_permohonan_uji_klinik'
            );
        }
    }

    public function down()
    {
        $this->dropForeignKeyIfExists('tb_pengambilan_sample_klinik', 'fk_pengambilan_klinik_permohonan');
        $this->dropForeignKeyIfExists('tb_permohonan_uji_analis_klinik', 'fk_analis_klinik_permohonan');
        $this->dropForeignKeyIfExists('tb_permohonan_uji_payment_klinik', 'fk_payment_klinik_permohonan');
        $this->dropForeignKeyIfExists('tb_permohonan_uji_paket_klinik', 'fk_paket_klinik_permohonan');
        $this->dropForeignKeyIfExists('tb_permohonan_uji_sub_parameter_klinik', 'fk_sub_param_klinik_param');
        $this->dropForeignKeyIfExists('tb_permohonan_uji_parameter_klinik', 'fk_param_klinik_permohonan');
        $this->dropForeignKeyIfExists('tb_permohonan_uji_klinik_2', 'fk_puk2_pasien');

        $this->dropIndexIfExists('ms_pasien', 'idx_pasien_no_rm');

        $this->dropIndexIfExists('tb_verification_activity_samples', 'idx_verif_klinik_activity_done');
        $this->dropIndexIfExists('tb_verification_activity_samples', 'idx_verif_klinik_activity');

        $this->dropIndexIfExists('tb_permohonan_uji_sub_parameter_klinik_history', 'idx_hist_sub_param_klinik_sub_created');
        $this->dropIndexIfExists('tb_permohonan_uji_parameter_klinik_history', 'idx_hist_param_klinik_param_created');

        $this->dropIndexIfExists('tb_pengambilan_sample_klinik', 'idx_pengambilan_klinik_permohonan');
        $this->dropIndexIfExists('tb_permohonan_uji_analis_klinik', 'idx_analis_klinik_permohonan_deleted');
        $this->dropIndexIfExists('tb_permohonan_uji_payment_klinik', 'idx_payment_klinik_permohonan_deleted');
        $this->dropIndexIfExists('tb_permohonan_uji_paket_klinik', 'idx_paket_klinik_permohonan_deleted');
        $this->dropIndexIfExists('tb_permohonan_uji_sub_parameter_klinik', 'idx_sub_param_klinik_param_deleted');
        $this->dropIndexIfExists('tb_permohonan_uji_parameter_klinik', 'idx_param_klinik_paket_deleted');
        $this->dropIndexIfExists('tb_permohonan_uji_parameter_klinik', 'idx_param_klinik_satuan_deleted');
        $this->dropIndexIfExists('tb_permohonan_uji_parameter_klinik', 'idx_param_klinik_permohonan_deleted');

        $this->dropIndexIfExists('tb_permohonan_uji_klinik_2', 'idx_puk2_prolanis_flags');
        $this->dropIndexIfExists('tb_permohonan_uji_klinik_2', 'idx_puk2_status_bayar_deleted');
        $this->dropIndexIfExists('tb_permohonan_uji_klinik_2', 'idx_puk2_status_deleted');
        $this->dropIndexIfExists('tb_permohonan_uji_klinik_2', 'idx_puk2_deleted_created');
        $this->dropIndexIfExists('tb_permohonan_uji_klinik_2', 'idx_puk2_pasien_deleted');
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

        try {
            DB::statement(
                "ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraintName}` " .
                "FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}` (`{$refColumn}`) " .
                "ON DELETE RESTRICT ON UPDATE CASCADE"
            );
        } catch (\Throwable $e) {
            // Data orphan / tipe kolom tidak cocok — index tetap dipakai, FK optional.
            if (function_exists('logger')) {
                logger()->warning("Skip FK {$constraintName}: " . $e->getMessage());
            }
        }
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
