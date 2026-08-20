<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupIdToTbSamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_samples', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_samples', 'group_id')) {
            $table->uuid('group_id')->nullable()->after('permohonan_uji_id')
                ->comment('Group ID for samples from same input session');
            $table->index('group_id');
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
        Schema::table('tb_samples', function (Blueprint $table) {
            if (Schema::hasColumn('tb_samples', 'group_id')) {
            $table->dropIndex(['group_id']);
            $table->dropColumn('group_id');
            }
        });
    }
}
