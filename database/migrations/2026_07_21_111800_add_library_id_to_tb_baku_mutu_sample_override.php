<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLibraryIdToTbBakuMutuSampleOverride extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_baku_mutu_sample_override')) {
            return;
        }

        Schema::table('tb_baku_mutu_sample_override', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_baku_mutu_sample_override', 'library_id')) {
                $table->char('library_id', 36)->nullable()->after('unit_id');
                $table->index('library_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('tb_baku_mutu_sample_override')) {
            return;
        }

        Schema::table('tb_baku_mutu_sample_override', function (Blueprint $table) {
            if (Schema::hasColumn('tb_baku_mutu_sample_override', 'library_id')) {
                $table->dropIndex(['library_id']);
                $table->dropColumn('library_id');
            }
        });
    }
}
