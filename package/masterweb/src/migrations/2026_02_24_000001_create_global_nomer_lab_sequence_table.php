<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalNomerLabSequenceTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('global_nomer_lab_sequence')) {
            Schema::create('global_nomer_lab_sequence', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->integer('year')->index();
                $table->bigInteger('last_number')->default(0);
                $table->timestamps();

                $table->unique('year');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('global_nomer_lab_sequence');
    }
}
