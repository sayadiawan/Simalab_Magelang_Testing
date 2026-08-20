<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetHba1cBloodCellAndHbDarahOnMsParameterTms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ms_parameter_tms')
            || !Schema::hasColumn('ms_parameter_tms', 'jenis_sampel')
        ) {
            return;
        }

        $now = now()->format('Y-m-d H:i:s');

        DB::table('ms_parameter_tms')
            ->whereIn('id_parameter_tms', [101, 107])
            ->update([
                'jenis_sampel' => 'Darah',
                'updated_at' => $now,
            ]);

        DB::table('ms_parameter_tms')
            ->whereIn('id_parameter_tms', [102, 106, 108])
            ->update([
                'jenis_sampel' => 'Blood Cell',
                'updated_at' => $now,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('ms_parameter_tms')
            || !Schema::hasColumn('ms_parameter_tms', 'jenis_sampel')
        ) {
            return;
        }

        DB::table('ms_parameter_tms')
            ->whereIn('id_parameter_tms', [101, 102, 106, 107, 108])
            ->update([
                'jenis_sampel' => 'Darah',
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ]);
    }
}
