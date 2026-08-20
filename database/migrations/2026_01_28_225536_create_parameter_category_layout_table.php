<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParameterCategoryLayoutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_param_category_layout', function (Blueprint $table) {
            $table->uuid('id_param_category_layout')->primary();
            $table->string('category_code', 10)->comment('A, B, C, D, dst');
            $table->string('category_name', 100)->comment('HEMATOLOGI, URIN, IMUNOLOGI, dst');
            $table->integer('column_width')->default(4)->comment('3, 4, 6, 12 untuk col-md-x');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('sort_order');
            $table->index('is_active');
        });

        Schema::create('ms_param_category_items', function (Blueprint $table) {
            $table->uuid('id_param_category_item')->primary();
            $table->uuid('id_param_category_layout');
            $table->uuid('id_parameter_paket_klinik');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('id_param_category_layout')
                ->references('id_param_category_layout')
                ->on('ms_param_category_layout')
                ->onDelete('cascade');
                
            $table->foreign('id_parameter_paket_klinik')
                ->references('id_parameter_paket_klinik')
                ->on('ms_parameter_paket_klinik')
                ->onDelete('cascade');
                
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_param_category_items');
        Schema::dropIfExists('ms_param_category_layout');
    }
}
