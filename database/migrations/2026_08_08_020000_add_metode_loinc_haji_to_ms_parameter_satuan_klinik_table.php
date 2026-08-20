<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetodeLoincHajiToMsParameterSatuanKlinikTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Dual metode & LOINC untuk parameter haji (mirip jenis_sampel_haji).
     * Null/empty = fallback ke kolom non-haji.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ms_parameter_satuan_klinik')) {
            return;
        }

        Schema::table('ms_parameter_satuan_klinik', function (Blueprint $table) {
            if (!Schema::hasColumn('ms_parameter_satuan_klinik', 'metode_parameter_satuan_klinik_haji')) {
                $table->string('metode_parameter_satuan_klinik_haji', 256)->nullable()
                    ->after('metode_parameter_satuan_klinik')
                    ->comment('Metode khusus permohonan haji (CSV). Null/empty = fallback ke metode_parameter_satuan_klinik');
            }

            if (!Schema::hasColumn('ms_parameter_satuan_klinik', 'loinc_parameter_satuan_klinik_haji')) {
                $table->string('loinc_parameter_satuan_klinik_haji', 256)->nullable()
                    ->after('loinc_parameter_satuan_klinik')
                    ->comment('LOINC khusus permohonan haji. Null/empty = fallback ke loinc_parameter_satuan_klinik');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('ms_parameter_satuan_klinik')) {
            return;
        }

        Schema::table('ms_parameter_satuan_klinik', function (Blueprint $table) {
            if (Schema::hasColumn('ms_parameter_satuan_klinik', 'loinc_parameter_satuan_klinik_haji')) {
                $table->dropColumn('loinc_parameter_satuan_klinik_haji');
            }
            if (Schema::hasColumn('ms_parameter_satuan_klinik', 'metode_parameter_satuan_klinik_haji')) {
                $table->dropColumn('metode_parameter_satuan_klinik_haji');
            }
        });
    }
}
