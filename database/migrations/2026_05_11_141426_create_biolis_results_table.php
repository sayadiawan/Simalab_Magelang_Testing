<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBiolisResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('biolis_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('result_date')->nullable();
            $table->string('sample_id', 50)->nullable()->index();
            $table->integer('parameter_id')->nullable();
            $table->string('parameter_name', 100)->nullable();
            $table->string('patient_name', 150)->nullable();
            $table->double('result_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('biolis_results');
    }
}
