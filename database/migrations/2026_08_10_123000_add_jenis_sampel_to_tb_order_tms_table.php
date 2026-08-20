<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddJenisSampelToTbOrderTmsTable extends Migration
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
            if (!Schema::hasColumn('tb_order_tms', 'jenis_sampel')) {
                $table->string('jenis_sampel', 50)->nullable()->after('jenis_kelamin');
                $table->index('jenis_sampel', 'idx_order_tms_jenis_sampel');
            }
        });

        // Backfill dari suffix barcode: .../Serum, .../Urine, dst.
        $specimenTypes = ['Darah', 'Serum', 'Plasma', 'Urine', 'Feses', 'Swab'];
        foreach ($specimenTypes as $jenis) {
            DB::table('tb_order_tms')
                ->whereNull('deleted_at')
                ->whereNull('jenis_sampel')
                ->where('kode_barcode', 'like', '%/' . $jenis)
                ->update(['jenis_sampel' => $jenis]);
        }
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
            if (Schema::hasColumn('tb_order_tms', 'jenis_sampel')) {
                $table->dropIndex('idx_order_tms_jenis_sampel');
                $table->dropColumn('jenis_sampel');
            }
        });
    }
}
