<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Samakan urutan baris permohonan dengan master
 * ms_parameter_satuan_klinik.sort_parameter_satuan_klinik (halaman reorder).
 */
class SyncSortingPermohonanUjiParameterKlinikFromSortParameterSatuanKlinik extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            || !Schema::hasTable('ms_parameter_satuan_klinik')) {
            return;
        }

        if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'sorting_permohonan_uji_parameter_klinik')) {
            Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
                $table->unsignedInteger('sorting_permohonan_uji_parameter_klinik')
                    ->nullable()
                    ->after('parameter_satuan_klinik');
            });
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

        DB::statement("
            UPDATE tb_permohonan_uji_parameter_klinik AS p
            INNER JOIN ms_parameter_satuan_klinik AS m
                ON m.id_parameter_satuan_klinik = p.parameter_satuan_klinik
            SET p.sorting_permohonan_uji_parameter_klinik = m.sort_parameter_satuan_klinik
            WHERE p.parameter_satuan_klinik IS NOT NULL
              AND p.parameter_satuan_klinik <> ''
              AND m.sort_parameter_satuan_klinik IS NOT NULL
              {$permohonanDeletedFilter}
              {$masterDeletedFilter}
        ");

        // Kolom legacy yang masih dipakai beberapa query orderBy di aplikasi.
        if (Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'sorting_parameter_satuan')) {
            DB::statement("
                UPDATE tb_permohonan_uji_parameter_klinik AS p
                INNER JOIN ms_parameter_satuan_klinik AS m
                    ON m.id_parameter_satuan_klinik = p.parameter_satuan_klinik
                SET p.sorting_parameter_satuan = m.sort_parameter_satuan_klinik
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
        // Nilai sorting sebelum sync tidak disimpan; rollback data tidak otomatis.
    }
}
