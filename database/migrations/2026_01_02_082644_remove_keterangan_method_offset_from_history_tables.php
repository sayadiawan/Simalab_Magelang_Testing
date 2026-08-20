<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveKeteranganMethodOffsetFromHistoryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Remove columns from tb_permohonan_uji_parameter_klinik_history
        if (Schema::hasTable('tb_permohonan_uji_parameter_klinik_history')) {
            Schema::table('tb_permohonan_uji_parameter_klinik_history', function (Blueprint $table) {
                if (Schema::hasColumn('tb_permohonan_uji_parameter_klinik_history', 'keterangan_permohonan_uji_parameter_klinik')) {
                    $table->dropColumn('keterangan_permohonan_uji_parameter_klinik');
                }
                if (Schema::hasColumn('tb_permohonan_uji_parameter_klinik_history', 'method_permohonan_uji_parameter_klinik')) {
                    $table->dropColumn('method_permohonan_uji_parameter_klinik');
                }
                if (Schema::hasColumn('tb_permohonan_uji_parameter_klinik_history', 'offset_baku_mutu')) {
                    $table->dropColumn('offset_baku_mutu');
                }
            });
        }

        // Remove columns from tb_permohonan_uji_sub_parameter_klinik_history
        if (Schema::hasTable('tb_permohonan_uji_sub_parameter_klinik_history')) {
            Schema::table('tb_permohonan_uji_sub_parameter_klinik_history', function (Blueprint $table) {
                if (Schema::hasColumn('tb_permohonan_uji_sub_parameter_klinik_history', 'keterangan_permohonan_uji_sub_parameter_klinik')) {
                    $table->dropColumn('keterangan_permohonan_uji_sub_parameter_klinik');
                }
                if (Schema::hasColumn('tb_permohonan_uji_sub_parameter_klinik_history', 'offset_baku_mutu')) {
                    $table->dropColumn('offset_baku_mutu');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Restore columns to tb_permohonan_uji_parameter_klinik_history
        if (Schema::hasTable('tb_permohonan_uji_parameter_klinik_history')) {
            Schema::table('tb_permohonan_uji_parameter_klinik_history', function (Blueprint $table) {
                if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik_history', 'keterangan_permohonan_uji_parameter_klinik')) {
                    $table->text('keterangan_permohonan_uji_parameter_klinik')->nullable()->after('hasil_permohonan_uji_parameter_klinik');
                }
                if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik_history', 'method_permohonan_uji_parameter_klinik')) {
                    $table->string('method_permohonan_uji_parameter_klinik')->nullable()->after('keterangan_permohonan_uji_parameter_klinik');
                }
                if (!Schema::hasColumn('tb_permohonan_uji_parameter_klinik_history', 'offset_baku_mutu')) {
                    $table->string('offset_baku_mutu', 20)->default('default')->nullable()->after('method_permohonan_uji_parameter_klinik');
                }
            });
        }

        // Restore columns to tb_permohonan_uji_sub_parameter_klinik_history
        if (Schema::hasTable('tb_permohonan_uji_sub_parameter_klinik_history')) {
            Schema::table('tb_permohonan_uji_sub_parameter_klinik_history', function (Blueprint $table) {
                if (!Schema::hasColumn('tb_permohonan_uji_sub_parameter_klinik_history', 'keterangan_permohonan_uji_sub_parameter_klinik')) {
                    $table->text('keterangan_permohonan_uji_sub_parameter_klinik')->nullable()->after('hasil_permohonan_uji_sub_parameter_klinik');
                }
                if (!Schema::hasColumn('tb_permohonan_uji_sub_parameter_klinik_history', 'offset_baku_mutu')) {
                    $table->string('offset_baku_mutu', 20)->default('default')->nullable()->after('keterangan_permohonan_uji_sub_parameter_klinik');
                }
            });
        }
    }
}
