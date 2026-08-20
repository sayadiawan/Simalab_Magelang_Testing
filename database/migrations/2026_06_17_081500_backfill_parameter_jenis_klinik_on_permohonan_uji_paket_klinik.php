<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Perbaiki parameter_jenis_klinik di tb_permohonan_uji_paket_klinik.
 *
 * Form checkbox memakai id_parameter_paket_klinik sebagai key jenis_parameters[],
 * sehingga kolom parameter_jenis_klinik terisi UUID paket (bukan jenis klinik).
 * Migration ini menormalisasi data lama sebelum / sesudah FK fk_paket_klinik_jenis aktif.
 */
class BackfillParameterJenisKlinikOnPermohonanUjiPaketKlinik extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_paket_klinik')
            || !Schema::hasTable('ms_parameter_paket_jenis_klinik')
            || !Schema::hasTable('ms_parameter_jenis_klinik')) {
            return;
        }

        if (!Schema::hasColumn('tb_permohonan_uji_paket_klinik', 'parameter_jenis_klinik')
            || !Schema::hasColumn('tb_permohonan_uji_paket_klinik', 'parameter_paket_klinik')) {
            return;
        }

        $bridgeHasDeletedAt = Schema::hasColumn('ms_parameter_paket_jenis_klinik', 'deleted_at');
        $deletedFilter = $bridgeHasDeletedAt ? 'AND bridge.deleted_at IS NULL' : '';

        // Ambil jenis klinik pertama (sort terkecil) per paket.
        DB::statement("
            UPDATE tb_permohonan_uji_paket_klinik AS pup
            INNER JOIN (
                SELECT bridge.parameter_paket_klinik_id, bridge.parameter_jenis_klinik_id
                FROM ms_parameter_paket_jenis_klinik AS bridge
                INNER JOIN (
                    SELECT parameter_paket_klinik_id, MIN(COALESCE(sort, 0)) AS min_sort
                    FROM ms_parameter_paket_jenis_klinik
                    WHERE parameter_paket_klinik_id IS NOT NULL
                      AND parameter_jenis_klinik_id IS NOT NULL
                      " . ($bridgeHasDeletedAt ? 'AND deleted_at IS NULL' : '') . "
                    GROUP BY parameter_paket_klinik_id
                ) AS first_sort
                    ON first_sort.parameter_paket_klinik_id = bridge.parameter_paket_klinik_id
                   AND COALESCE(bridge.sort, 0) = first_sort.min_sort
                WHERE bridge.parameter_jenis_klinik_id IS NOT NULL
                  {$deletedFilter}
                GROUP BY bridge.parameter_paket_klinik_id, bridge.parameter_jenis_klinik_id
            ) AS resolved ON resolved.parameter_paket_klinik_id = pup.parameter_paket_klinik
            LEFT JOIN ms_parameter_jenis_klinik AS jenis
                ON jenis.id_parameter_jenis_klinik = pup.parameter_jenis_klinik
            SET pup.parameter_jenis_klinik = resolved.parameter_jenis_klinik_id
            WHERE pup.parameter_paket_klinik IS NOT NULL
              AND pup.parameter_paket_klinik <> ''
              AND (
                    jenis.id_parameter_jenis_klinik IS NULL
                    OR pup.parameter_jenis_klinik = pup.parameter_paket_klinik
              )
        ");

        // Sisakan NULL jika tidak ada mapping valid (hindari FK violation).
        DB::statement("
            UPDATE tb_permohonan_uji_paket_klinik AS pup
            LEFT JOIN ms_parameter_jenis_klinik AS jenis
                ON jenis.id_parameter_jenis_klinik = pup.parameter_jenis_klinik
            SET pup.parameter_jenis_klinik = NULL
            WHERE pup.parameter_jenis_klinik IS NOT NULL
              AND pup.parameter_jenis_klinik <> ''
              AND jenis.id_parameter_jenis_klinik IS NULL
        ");
    }

    public function down()
    {
        // Data backfill tidak dapat dikembalikan ke UUID paket yang salah.
    }
}
