<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentToParameterJenisKlinikTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ms_parameter_jenis_klinik', function (Blueprint $table) {
            // Add parent_id column for hierarchical structure
            $table->uuid('id_parameter_jenis_klinik_parent')->nullable()->after('id_parameter_jenis_klinik');
            $table->integer('level')->default(0)->after('id_parameter_jenis_klinik_parent'); // 0 = parent, 1 = child
            $table->integer('sort_order')->default(0)->after('level'); // For ordering within same parent
            
            // Add foreign key constraint
            $table->foreign('id_parameter_jenis_klinik_parent', 'fk_param_jenis_parent')
                  ->references('id_parameter_jenis_klinik')
                  ->on('ms_parameter_jenis_klinik')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ms_parameter_jenis_klinik', function (Blueprint $table) {
            $table->dropForeign('fk_param_jenis_parent');
            $table->dropColumn(['id_parameter_jenis_klinik_parent', 'level', 'sort_order']);
        });
    }
}
