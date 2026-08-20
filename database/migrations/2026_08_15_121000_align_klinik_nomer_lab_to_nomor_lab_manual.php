<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Klinik dengan nomor lab MANUAL: samakan nomer_lab ke nomor_lab_manual.
 *
 * assignKlinik() sempat mengisi nomer_lab otomatis meski flag manual aktif.
 * Idempotent — hanya baris yang nomer_lab kosong / tidak cocok.
 */
class AlignKlinikNomerLabToNomorLabManual extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')
            || !Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomer_lab')
            || !Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomor_lab_manual')
            || !Schema::hasColumn('tb_permohonan_uji_klinik_2', 'is_nomor_lab_manual')
        ) {
            return;
        }

        DB::update("
            UPDATE tb_permohonan_uji_klinik_2
            SET
                nomer_lab = CAST(TRIM(nomor_lab_manual) AS UNSIGNED),
                updated_at = NOW()
            WHERE deleted_at IS NULL
              AND IFNULL(is_nomor_lab_manual, 0) = 1
              AND TRIM(IFNULL(nomor_lab_manual, '')) REGEXP '^[0-9]+$'
              AND CAST(TRIM(nomor_lab_manual) AS UNSIGNED) > 0
              AND (
                nomer_lab IS NULL
                OR CAST(nomer_lab AS UNSIGNED) <> CAST(TRIM(nomor_lab_manual) AS UNSIGNED)
              )
        ");
    }

    public function down(): void
    {
        // Tidak di-rollback otomatis.
    }
}
