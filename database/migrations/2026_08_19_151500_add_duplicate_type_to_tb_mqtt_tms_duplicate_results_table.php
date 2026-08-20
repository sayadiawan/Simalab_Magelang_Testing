<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDuplicateTypeToTbMqttTmsDuplicateResultsTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_mqtt_tms_duplicate_results')) {
            return;
        }

        Schema::table('tb_mqtt_tms_duplicate_results', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_mqtt_tms_duplicate_results', 'duplicate_type')) {
                $table->string('duplicate_type', 30)->nullable()->after('total_occurrence');
                $table->index('duplicate_type', 'idx_dupres_type');
            }
            if (!Schema::hasColumn('tb_mqtt_tms_duplicate_results', 'distinct_count')) {
                $table->unsignedSmallInteger('distinct_count')->nullable()->after('duplicate_type');
            }
            if (!Schema::hasColumn('tb_mqtt_tms_duplicate_results', 'gap_minutes')) {
                $table->unsignedInteger('gap_minutes')->nullable()->after('distinct_count');
            }
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('tb_mqtt_tms_duplicate_results')) {
            return;
        }

        Schema::table('tb_mqtt_tms_duplicate_results', function (Blueprint $table) {
            if (Schema::hasColumn('tb_mqtt_tms_duplicate_results', 'gap_minutes')) {
                $table->dropColumn('gap_minutes');
            }
            if (Schema::hasColumn('tb_mqtt_tms_duplicate_results', 'distinct_count')) {
                $table->dropColumn('distinct_count');
            }
            if (Schema::hasColumn('tb_mqtt_tms_duplicate_results', 'duplicate_type')) {
                $table->dropIndex('idx_dupres_type');
                $table->dropColumn('duplicate_type');
            }
        });
    }
}
