<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbMqttTmsDuplicateResultsTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tb_mqtt_tms_duplicate_results')) {
            return;
        }

        Schema::create('tb_mqtt_tms_duplicate_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('entry_key', 64)->unique();
            $table->string('sample_id', 64)->index();
            $table->unsignedInteger('parameter_id')->index();
            $table->string('parameter_name', 120)->nullable();
            $table->unsignedSmallInteger('occurrence');
            $table->unsignedSmallInteger('total_occurrence');
            $table->string('label', 60)->nullable();
            $table->timestamp('received_at')->nullable()->index();
            $table->string('value', 60)->nullable();
            $table->string('tray', 50)->nullable();
            $table->string('pos', 50)->nullable();
            $table->string('log_status', 20)->nullable();
            $table->unsignedSmallInteger('db_slots')->nullable();
            $table->unsignedSmallInteger('db_filled')->nullable();
            $table->string('verdict', 60)->nullable()->index();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();

            $table->index(['sample_id', 'parameter_id'], 'idx_dupres_sample_param');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tb_mqtt_tms_duplicate_results');
    }
}
