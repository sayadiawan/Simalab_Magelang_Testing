<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPosAndIsExecutedToTbOrderTmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_order_tms')) {
            return;
        }

        Schema::table('tb_order_tms', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_order_tms', 'pos')) {
                $table->string('pos', 50)->nullable()->after('tray');
                $table->index('pos', 'idx_order_tms_pos');
            }

            if (!Schema::hasColumn('tb_order_tms', 'is_executed')) {
                $table->boolean('is_executed')->default(0)->after('pos');
                $table->index('is_executed', 'idx_order_tms_executed');
            }

            if (!Schema::hasColumn('tb_order_tms', 'executed_at')) {
                $table->timestamp('executed_at')->nullable()->after('is_executed');
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
        if (!Schema::hasTable('tb_order_tms')) {
            return;
        }

        Schema::table('tb_order_tms', function (Blueprint $table) {
            if (Schema::hasColumn('tb_order_tms', 'executed_at')) {
                $table->dropColumn('executed_at');
            }

            if (Schema::hasColumn('tb_order_tms', 'is_executed')) {
                $table->dropIndex('idx_order_tms_executed');
                $table->dropColumn('is_executed');
            }

            if (Schema::hasColumn('tb_order_tms', 'pos')) {
                $table->dropIndex('idx_order_tms_pos');
                $table->dropColumn('pos');
            }
        });
    }
}
