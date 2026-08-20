<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalLabSequenceDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('global_lab_sequence_detail')) {
        Schema::create('global_lab_sequence_detail', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('year')->index();
            $table->bigInteger('sequence_number')->index();
            $table->uuid('lab_id')->nullable()->index();
            $table->string('lab_type')->nullable()->comment('lab: untuk LabNum, klinik: untuk NumberKlinik');
            $table->uuid('reference_id')->nullable()->comment('ID dari LabNum atau NumberKlinik atau PermohonanUjiKlinik');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['year', 'sequence_number']);
            $table->index(['year', 'lab_id']);
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
        if (Schema::hasTable('global_lab_sequence_detail')) {
        Schema::dropIfExists('global_lab_sequence_detail');
        }
    }
}
