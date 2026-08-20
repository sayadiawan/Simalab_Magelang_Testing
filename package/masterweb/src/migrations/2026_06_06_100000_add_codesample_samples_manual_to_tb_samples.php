<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCodesampleSamplesManualToTbSamples extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_samples')) {
            return;
        }

        Schema::table('tb_samples', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_samples', 'codesample_samples_manual')) {
                $table->string('codesample_samples_manual', 100)->nullable()
                    ->comment('Cadangan kode sampel manual Kesmas untuk pemulihan setelah sinkronisasi otomatis');
            }
        });

        if (Schema::hasColumn('tb_samples', 'codesample_samples_manual')
            && Schema::hasColumn('tb_samples', 'is_nomor_sampel_manual')) {
            DB::table('tb_samples')
                ->where('is_nomor_sampel_manual', 1)
                ->whereNull('codesample_samples_manual')
                ->whereNotNull('codesample_samples')
                ->update(['codesample_samples_manual' => DB::raw('codesample_samples')]);
        }
    }

    public function down()
    {
        if (!Schema::hasTable('tb_samples')) {
            return;
        }

        Schema::table('tb_samples', function (Blueprint $table) {
            if (Schema::hasColumn('tb_samples', 'codesample_samples_manual')) {
                $table->dropColumn('codesample_samples_manual');
            }
        });
    }
}
