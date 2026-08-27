<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notifikasi LIMS berbasis unread/read (bukan filter tanggal mutlak).
 * Worklist tetap sumber kebenaran pekerjaan; tabel ini hanya event + status baca.
 */
class CreateTbNotificationsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tb_notifications')) {
            return;
        }

        Schema::create('tb_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->string('role_level', 32)->nullable()->index();
            $table->string('type', 64)->index();
            $table->string('reference_type', 64)->nullable();
            $table->string('reference_id', 64)->nullable()->index();
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('icon', 64)->nullable();
            $table->string('color', 32)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();

            $table->unique(
                ['user_id', 'type', 'reference_id'],
                'tb_notifications_user_type_ref_unique'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_notifications');
    }
}
