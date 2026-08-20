<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Isi method_permohonan_uji_parameter_klinik dari master
 * ms_parameter_satuan_klinik.metode_parameter_satuan_klinik
 * untuk baris permohonan yang metodenya masih kosong / "-".
 */
class BackfillMethodPermohonanUjiParameterKlinikFromMetodeParameterSatuanKlinik extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            || !Schema::hasTable('ms_parameter_satuan_klinik')) {
            return;
        }

        if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'method_permohonan_uji_parameter_klinik')
            || !Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'parameter_satuan_klinik')
            || !Schema::hasColumn('ms_parameter_satuan_klinik', 'metode_parameter_satuan_klinik')) {
            return;
        }

        $permohonanDeletedFilter = Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'deleted_at')
            ? 'AND p.deleted_at IS NULL'
            : '';
        $masterDeletedFilter = Schema::hasColumn('ms_parameter_satuan_klinik', 'deleted_at')
            ? 'AND m.deleted_at IS NULL'
            : '';

        DB::statement("
            UPDATE tb_permohonan_uji_parameter_klinik AS p
            INNER JOIN ms_parameter_satuan_klinik AS m
                ON m.id_parameter_satuan_klinik = p.parameter_satuan_klinik
            SET p.method_permohonan_uji_parameter_klinik = TRIM(m.metode_parameter_satuan_klinik)
            WHERE p.parameter_satuan_klinik IS NOT NULL
              AND p.parameter_satuan_klinik <> ''
              AND m.metode_parameter_satuan_klinik IS NOT NULL
              AND TRIM(m.metode_parameter_satuan_klinik) <> ''
              AND TRIM(m.metode_parameter_satuan_klinik) <> '-'
              AND (
                    p.method_permohonan_uji_parameter_klinik IS NULL
                    OR TRIM(p.method_permohonan_uji_parameter_klinik) = ''
                    OR TRIM(p.method_permohonan_uji_parameter_klinik) = '-'
              )
              {$permohonanDeletedFilter}
              {$masterDeletedFilter}
        ");
    }

    public function down(): void
    {
        // Data backfill tidak dapat di-rollback otomatis tanpa snapshot nilai lama.
    }
}
