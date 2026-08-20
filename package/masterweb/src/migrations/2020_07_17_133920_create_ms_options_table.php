<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Cek apakah tabel ms_options sudah ada
        if (!Schema::hasTable('ms_options')) {
        Schema::create('ms_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 50);
            $table->string('slogan', 50);
            $table->text('description');
            $table->string('keyword');
            $table->string('footer', 100);
            $table->string('logo', 40)->nullable();
            $table->string('favicon',40)->nullable();
            $table->timestamps();
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
        Schema::dropIfExists('ms_options');
    }
}
