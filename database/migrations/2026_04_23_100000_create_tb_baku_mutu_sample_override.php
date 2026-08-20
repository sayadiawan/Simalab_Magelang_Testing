<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbBakuMutuSampleOverride extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tb_baku_mutu_sample_override')) {
            return;
        }

        Schema::create('tb_baku_mutu_sample_override', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // FK ke laboratorium_progress_id pada tb_sample_analitik_progress
            $table->uuid('sample_progress_id');
            $table->uuid('method_id');
            // Nilai override baku mutu
            $table->string('nilai_baku_mutu', 500)->nullable();
            $table->string('min', 100)->nullable();
            $table->string('max', 100)->nullable();
            $table->string('equal', 500)->nullable();
            $table->timestamps();

            $table->unique(['sample_progress_id', 'method_id']);
            $table->index('sample_progress_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_baku_mutu_sample_override');
    }
}
