<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDraftGroupIdToTbSampleDraftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_sample_draft', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_sample_draft', 'draft_group_id')) {
            $table->uuid('draft_group_id')->nullable()->after('permohonan_uji_id')->comment('Group ID for drafts from same input session');
            $table->index('draft_group_id');
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
        Schema::table('tb_sample_draft', function (Blueprint $table) {
            if (Schema::hasColumn('tb_sample_draft', 'draft_group_id')) {
            $table->dropIndex(['draft_group_id']);
            $table->dropColumn('draft_group_id');
            }
        });
    }
}
