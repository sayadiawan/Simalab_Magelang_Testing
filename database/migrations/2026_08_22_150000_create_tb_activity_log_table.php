<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Log aktivitas pengguna di seluruh modul SimaLab.
 */
class CreateTbActivityLogTable extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_activity_log')) {
            return;
        }

        Schema::create('tb_activity_log', function (Blueprint $table) {
            $table->uuid('id_activity_log')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('user_name', 191)->nullable();
            $table->string('username', 64)->nullable();
            $table->string('privilege_level', 32)->nullable();
            $table->string('action', 32);
            $table->string('bidang', 32)->default('umum');
            $table->string('module', 128)->nullable();
            $table->string('description', 500)->nullable();
            $table->string('subject_type', 64)->nullable();
            $table->string('subject_id', 64)->nullable();
            $table->string('route_name', 191)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('http_method', 10)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('request_data')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['created_at'], 'idx_activity_log_created');
            $table->index(['user_id', 'created_at'], 'idx_activity_log_user_time');
            $table->index(['action', 'created_at'], 'idx_activity_log_action_time');
            $table->index(['bidang', 'created_at'], 'idx_activity_log_bidang_time');
            $table->index(['subject_type', 'subject_id'], 'idx_activity_log_subject');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_activity_log');
    }
}
