<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalLabSequenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('global_lab_sequence')) {
        Schema::create('global_lab_sequence', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('year')->index();
            $table->bigInteger('last_number')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique('year');
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
        if (Schema::hasTable('global_lab_sequence')) {
        Schema::dropIfExists('global_lab_sequence');
        }
    }
}
