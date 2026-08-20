<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_role')) {
        Schema::create('tb_role', function (Blueprint $table) {
            $table->uuid('id')->unique();
            $table->uuid('privilege_id');
            $table->integer('menu_id');
            $table->char('create',4);
            $table->char('read',4);
            $table->char('update',4);
            $table->char('delete',4);
            $table->timestamps();
            $table->softDeletes();
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
        if (Schema::hasTable('tb_role')) {
        Schema::dropIfExists('tb_role');
        }
    }
}
