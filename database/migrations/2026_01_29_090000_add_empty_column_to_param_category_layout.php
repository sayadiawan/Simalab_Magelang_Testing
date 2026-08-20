<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmptyColumnToParamCategoryLayout extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ms_param_category_layout', function (Blueprint $table) {
            $table->enum('empty_column_position', ['none', 'left', 'right'])->default('none')->after('column_width')
                ->comment('Position of empty column: none, left, or right');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ms_param_category_layout', function (Blueprint $table) {
            $table->dropColumn('empty_column_position');
        });
    }
}
