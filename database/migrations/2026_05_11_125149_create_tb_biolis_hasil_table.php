<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbBiolisHasilTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_biolis_hasil', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sample_no', 50)->index();
            $table->string('parameter', 50);
            $table->string('hasil', 100)->nullable();
            $table->string('unit', 30)->nullable();
            $table->string('status', 20)->default('diterima');
            $table->timestamp('received_at')->useCurrent();
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
        Schema::dropIfExists('tb_biolis_hasil');
    }
}
