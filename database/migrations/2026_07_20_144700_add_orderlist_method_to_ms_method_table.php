<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddOrderlistMethodToMsMethodTable extends Migration
{
    /** Default sumber urutan: Air Higiene */
    const DEFAULT_SAMPLE_TYPE_ID = 'c7c770a9-6bd7-4e30-83fc-0e4cc6a01fe0';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('ms_method', 'orderlist_method')) {
            Schema::table('ms_method', function (Blueprint $table) {
                $table->integer('orderlist_method')->nullable()->after('params_method');
            });
        }

        $this->seedFromSampleTypeDetail();
    }

    /**
     * Seed orderlist_method dari detail jenis sarana default,
     * lalu method yang belum ada urutannya diurutkan di belakang.
     */
    protected function seedFromSampleTypeDetail()
    {
        $sampleTypeId = self::DEFAULT_SAMPLE_TYPE_ID;

        $details = DB::table('ms_sample_type_detail')
            ->where('sample_type_id', $sampleTypeId)
            ->whereNull('deleted_at')
            ->whereNotNull('method_id')
            ->orderBy('is_tambahan', 'asc')
            ->orderByRaw('COALESCE(orderlist_sample_type_detail, 999999) ASC')
            ->get(['method_id', 'is_tambahan', 'orderlist_sample_type_detail']);

        $order = 1;
        $seen = [];

        foreach ($details as $detail) {
            $methodId = $detail->method_id;
            if (!$methodId || isset($seen[$methodId])) {
                continue;
            }
            $seen[$methodId] = true;

            DB::table('ms_method')
                ->where('id_method', $methodId)
                ->whereNull('deleted_at')
                ->update(['orderlist_method' => $order++]);
        }

        $remaining = DB::table('ms_method')
            ->whereNull('deleted_at')
            ->whereNull('orderlist_method')
            ->orderBy('params_method', 'asc')
            ->get(['id_method']);

        foreach ($remaining as $method) {
            DB::table('ms_method')
                ->where('id_method', $method->id_method)
                ->update(['orderlist_method' => $order++]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('ms_method', 'orderlist_method')) {
            Schema::table('ms_method', function (Blueprint $table) {
                $table->dropColumn('orderlist_method');
            });
        }
    }
}
