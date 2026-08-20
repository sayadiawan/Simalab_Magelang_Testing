<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGridPositionToParamCategoryItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add grid position fields to items
        Schema::table('ms_param_category_items', function (Blueprint $table) {
            $table->integer('row_position')->nullable()->after('sort_order')->comment('Grid row position (1, 2, 3, ...)');
            $table->integer('column_position')->nullable()->after('row_position')->comment('Grid column position (1, 2, 3)');
        });
        
        // Add grid configuration to layout
        Schema::table('ms_param_category_layout', function (Blueprint $table) {
            $table->integer('grid_rows')->default(0)->after('empty_column_position')->comment('Number of rows in grid (0 = auto)');
            $table->integer('grid_columns')->default(3)->after('grid_rows')->comment('Number of columns in grid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ms_param_category_items', function (Blueprint $table) {
            $table->dropColumn(['row_position', 'column_position']);
        });
        
        Schema::table('ms_param_category_layout', function (Blueprint $table) {
            $table->dropColumn(['grid_rows', 'grid_columns']);
        });
    }
}
