<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsNomorSampelManualToTbSampleDraftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_sample_draft')) {
            return;
        }

        Schema::table('tb_sample_draft', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_sample_draft', 'is_nomor_sampel_manual')) {
                $table->boolean('is_nomor_sampel_manual')->default(false)
                    ->comment('1 = kode sampel di draft diisi manual (Kesmas)');
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
        if (!Schema::hasTable('tb_sample_draft')) {
            return;
        }

        Schema::table('tb_sample_draft', function (Blueprint $table) {
            if (Schema::hasColumn('tb_sample_draft', 'is_nomor_sampel_manual')) {
                $table->dropColumn('is_nomor_sampel_manual');
            }
        });
    }
}
