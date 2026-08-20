<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitIdToTbBakuMutuSampleOverride extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_baku_mutu_sample_override')) {
            return;
        }

        if (!Schema::hasColumn('tb_baku_mutu_sample_override', 'unit_id')) {
            Schema::table('tb_baku_mutu_sample_override', function (Blueprint $table) {
                $table->uuid('unit_id')->nullable()->after('equal');
                $table->index('unit_id');
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('tb_baku_mutu_sample_override')) {
            return;
        }

        if (Schema::hasColumn('tb_baku_mutu_sample_override', 'unit_id')) {
            Schema::table('tb_baku_mutu_sample_override', function (Blueprint $table) {
                $table->dropIndex(['unit_id']);
                $table->dropColumn('unit_id');
            });
        }
    }
}
