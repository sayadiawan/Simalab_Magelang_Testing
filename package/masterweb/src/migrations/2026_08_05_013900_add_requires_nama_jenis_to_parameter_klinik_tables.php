<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom requires_nama_jenis untuk penanda dropdown Negatif/Positif
 * yang butuh input nama jenis (form analis/verifikasi klinik).
 *
 * Tabel:
 * - ms_parameter_satuan_klinik
 * - tb_permohonan_uji_parameter_klinik
 */
class AddRequiresNamaJenisToParameterKlinikTables extends Migration
{
    public function up()
    {
        if (Schema::hasTable('ms_parameter_satuan_klinik')
            && !Schema::hasColumn('ms_parameter_satuan_klinik', 'requires_nama_jenis')) {
            Schema::table('ms_parameter_satuan_klinik', function (Blueprint $table) {
                $table->boolean('requires_nama_jenis')->default(0)->after('is_option');
            });
        }

        if (Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            && !Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'requires_nama_jenis')) {
            Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
                $table->boolean('requires_nama_jenis')->default(0)->after('parameter_satuan_klinik');
            });
        }

        // Default ON untuk parameter yang sudah biasa pakai nama jenis.
        if (Schema::hasColumn('ms_parameter_satuan_klinik', 'requires_nama_jenis')) {
            DB::table('ms_parameter_satuan_klinik')
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->where('name_parameter_satuan_klinik', 'like', '%Kristal%')
                        ->orWhere('name_parameter_satuan_klinik', 'like', '%Silinder%')
                        ->orWhere('name_parameter_satuan_klinik', 'like', '%Cast%')
                        ->orWhere('name_parameter_satuan_klinik', 'like', '%Lain-lain%')
                        ->orWhere('name_parameter_satuan_klinik', 'like', '%Lain lain%');
                })
                ->update(['requires_nama_jenis' => 1]);
        }

        if (Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'requires_nama_jenis')
            && Schema::hasColumn('ms_parameter_satuan_klinik', 'requires_nama_jenis')) {
            DB::statement("
                UPDATE tb_permohonan_uji_parameter_klinik pup
                INNER JOIN ms_parameter_satuan_klinik psk
                    ON psk.id_parameter_satuan_klinik = pup.parameter_satuan_klinik
                SET pup.requires_nama_jenis = psk.requires_nama_jenis
                WHERE pup.deleted_at IS NULL
                  AND psk.deleted_at IS NULL
            ");
        }
    }

    public function down()
    {
        if (Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            && Schema::hasColumn('tb_permohonan_uji_parameter_klinik', 'requires_nama_jenis')) {
            Schema::table('tb_permohonan_uji_parameter_klinik', function (Blueprint $table) {
                $table->dropColumn('requires_nama_jenis');
            });
        }

        if (Schema::hasTable('ms_parameter_satuan_klinik')
            && Schema::hasColumn('ms_parameter_satuan_klinik', 'requires_nama_jenis')) {
            Schema::table('ms_parameter_satuan_klinik', function (Blueprint $table) {
                $table->dropColumn('requires_nama_jenis');
            });
        }
    }
}
