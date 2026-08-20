<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJenisSampelHajiToMsParameterSatuanKlinikTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ms_parameter_satuan_klinik')) {
            return;
        }

        if (!Schema::hasColumn('ms_parameter_satuan_klinik', 'jenis_sampel_haji')) {
            Schema::table('ms_parameter_satuan_klinik', function (Blueprint $table) {
                $table->mediumText('jenis_sampel_haji')->nullable()->after('jenis_sampel')
                    ->comment('Jenis sampel khusus permohonan haji (JSON array). Null/empty = fallback ke jenis_sampel');
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
        if (!Schema::hasTable('ms_parameter_satuan_klinik')) {
            return;
        }

        if (Schema::hasColumn('ms_parameter_satuan_klinik', 'jenis_sampel_haji')) {
            Schema::table('ms_parameter_satuan_klinik', function (Blueprint $table) {
                $table->dropColumn('jenis_sampel_haji');
            });
        }
    }
}
