<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Resync urutan permohonan memakai sort terkecil per nama parameter master
 * (menangani duplikat ms_parameter_satuan_klinik, mis. Cholesterol sort 5 vs 86).
 */
class ResyncSortingPermohonanUjiParameterKlinikByCanonicalMasterName extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            || !Schema::hasTable('ms_parameter_satuan_klinik')) {
            return;
        }

        if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'parameter_satuan_klinik')
            || !Schema::hasColumn('ms_parameter_satuan_klinik', 'sort_parameter_satuan_klinik')) {
            return;
        }

        $permohonanDeletedFilter = Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'deleted_at')
            ? 'AND p.deleted_at IS NULL'
            : '';
        $masterDeletedFilter = Schema::hasColumn('ms_parameter_satuan_klinik', 'deleted_at')
            ? 'AND m.deleted_at IS NULL'
            : '';

        $canonicalSortSql = "
            SELECT MIN(m2.sort_parameter_satuan_klinik) AS canon_sort
            FROM ms_parameter_satuan_klinik AS m2
            WHERE m2.sort_parameter_satuan_klinik IS NOT NULL
              " . (Schema::hasColumn('ms_parameter_satuan_klinik', 'deleted_at') ? 'AND m2.deleted_at IS NULL' : '') . "
              AND (
                    LOWER(TRIM(m2.name_parameter_satuan_klinik)) = LOWER(TRIM(m.name_parameter_satuan_klinik))
                    OR (
                        LOWER(TRIM(m.name_parameter_satuan_klinik)) IN ('kreatinin', 'creatinine')
                        AND LOWER(TRIM(m2.name_parameter_satuan_klinik)) IN ('kreatinin', 'creatinine')
                    )
              )
        ";

        if (Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'sorting_permohonan_uji_parameter_klinik')) {
            DB::statement("
                UPDATE tb_permohonan_uji_parameter_klinik AS p
                INNER JOIN ms_parameter_satuan_klinik AS m
                    ON m.id_parameter_satuan_klinik = p.parameter_satuan_klinik
                SET p.sorting_permohonan_uji_parameter_klinik = ({$canonicalSortSql})
                WHERE p.parameter_satuan_klinik IS NOT NULL
                  AND p.parameter_satuan_klinik <> ''
                  AND m.sort_parameter_satuan_klinik IS NOT NULL
                  {$permohonanDeletedFilter}
                  {$masterDeletedFilter}
            ");
        }

        if (Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'sorting_parameter_satuan')) {
            DB::statement("
                UPDATE tb_permohonan_uji_parameter_klinik AS p
                INNER JOIN ms_parameter_satuan_klinik AS m
                    ON m.id_parameter_satuan_klinik = p.parameter_satuan_klinik
                SET p.sorting_parameter_satuan = ({$canonicalSortSql})
                WHERE p.parameter_satuan_klinik IS NOT NULL
                  AND p.parameter_satuan_klinik <> ''
                  AND m.sort_parameter_satuan_klinik IS NOT NULL
                  {$permohonanDeletedFilter}
                  {$masterDeletedFilter}
            ");
        }
    }

    public function down(): void
    {
        // Nilai sorting sebelum resync tidak disimpan.
    }
}
