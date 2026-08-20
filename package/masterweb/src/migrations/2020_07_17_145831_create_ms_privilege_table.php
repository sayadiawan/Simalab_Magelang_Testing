<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsPrivilegeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ms_privilege')) {
        Schema::create('ms_privilege', function (Blueprint $table) {
            $table->uuid('id')->unique();
            $table->char('level', 20);
            $table->string('name', 25);
            $table->string('description', 100)->nullable()->default();
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
        if (Schema::hasTable('ms_privilege')) {
        Schema::dropIfExists('ms_privilege');
        }
    }
}
