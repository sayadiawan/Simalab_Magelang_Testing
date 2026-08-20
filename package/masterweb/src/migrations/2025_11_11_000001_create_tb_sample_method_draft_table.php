<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbSampleMethodDraftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_sample_method_draft')) {
        Schema::create('tb_sample_method_draft', function (Blueprint $table) {
            $table->uuid('id_sample_method_draft')->primary();
            $table->uuid('sample_draft_id');
            $table->uuid('method_id');
            $table->uuid('laboratorium_id');
            $table->decimal('price_method', 15, 2)->default(0);
            $table->tinyInteger('is_sub')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
                // Indexes only - no foreign key constraints due to potential data type incompatibility
                // Using indexes provides query performance without strict referential integrity
                // Referential integrity should be maintained at application level
            $table->index('sample_draft_id');
            $table->index('method_id');
            $table->index('laboratorium_id');
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
        if (Schema::hasTable('tb_sample_method_draft')) {
        Schema::dropIfExists('tb_sample_method_draft');
        }
    }
}


