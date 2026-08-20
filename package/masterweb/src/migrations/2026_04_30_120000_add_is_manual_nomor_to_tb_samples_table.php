<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsManualNomorToTbSamplesTable extends Migration
{
    public function up()
    {
        Schema::table('tb_samples', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_samples', 'is_nomor_sampel_manual')) {
                $table->boolean('is_nomor_sampel_manual')->default(false)
                    ->after('codesample_samples')
                    ->comment('1 = kode/nomor sampel diisi manual (Kesmas)');
            }
            if (!Schema::hasColumn('tb_samples', 'is_nomor_laboratorium_manual')) {
                $table->boolean('is_nomor_laboratorium_manual')->default(false)
                    ->after('is_nomor_sampel_manual')
                    ->comment('1 = nomor laboratorium diisi manual (Kesmas)');
            }
        });
    }

    public function down()
    {
        Schema::table('tb_samples', function (Blueprint $table) {
            if (Schema::hasColumn('tb_samples', 'is_nomor_laboratorium_manual')) {
                $table->dropColumn('is_nomor_laboratorium_manual');
            }
            if (Schema::hasColumn('tb_samples', 'is_nomor_sampel_manual')) {
                $table->dropColumn('is_nomor_sampel_manual');
            }
        });
    }
}
