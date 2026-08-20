<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsHajiToMsParameterSatuanKlinikTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ms_parameter_satuan_klinik', function (Blueprint $table) {
            if (!Schema::hasColumn('ms_parameter_satuan_klinik', 'is_haji')) {
                $table->tinyInteger('is_haji')->default(0)->after('number_format')->comment('0 = Parameter Biasa, 1 = Parameter Haji (memerlukan 2 baku mutu: haji dan non-haji)');
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
        Schema::table('ms_parameter_satuan_klinik', function (Blueprint $table) {
            if (Schema::hasColumn('ms_parameter_satuan_klinik', 'is_haji')) {
                $table->dropColumn('is_haji');
            }
        });
    }
}
