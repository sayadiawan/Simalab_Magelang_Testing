<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbMqttTmsReplayHistoryTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tb_mqtt_tms_replay_history')) {
            return;
        }

        Schema::create('tb_mqtt_tms_replay_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('entry_key', 64)->unique();
            $table->string('message_id', 64)->nullable()->index();
            $table->string('sample_id', 64)->nullable()->index();
            $table->string('tray', 50)->nullable();
            $table->string('pos', 50)->nullable();
            $table->timestamp('log_received_at')->nullable()->index();
            $table->string('status', 32)->index();
            $table->text('log_error')->nullable();
            $table->text('replay_error')->nullable();
            $table->string('id_order_tms', 36)->nullable();
            $table->unsignedSmallInteger('updated_count')->default(0);
            $table->string('matched_by', 120)->nullable();
            $table->timestamp('replayed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tb_mqtt_tms_replay_history');
    }
}
