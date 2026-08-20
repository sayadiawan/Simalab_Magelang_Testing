<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Untuk baku mutu dengan jenis sampel Makanan/Minuman/Lainnya: perbaiki simbol derajat
 * yang sempat tersimpan sebagai ? (korupsi encoding) menjadi ° di name_report.
 */
class FixNameReportDegreeCharForMakananMinumanLainnyaSampleType extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_baku_mutu') || !Schema::hasTable('ms_sample_type')) {
            return;
        }

        if (!Schema::hasColumn('tb_baku_mutu', 'name_report') || !Schema::hasColumn('tb_baku_mutu', 'sampletype_id')) {
            return;
        }

        $sampleTypeIds = DB::table('ms_sample_type')
            ->whereNull('deleted_at')
            ->where('name_sample_type', 'like', '%Makanan%')
            ->where('name_sample_type', 'like', '%Minuman%')
            ->where('name_sample_type', 'like', '%Lainnya%')
            ->pluck('id_sample_type');

        if ($sampleTypeIds->isEmpty()) {
            return;
        }

        DB::table('tb_baku_mutu')
            ->whereNull('deleted_at')
            ->whereIn('sampletype_id', $sampleTypeIds->all())
            ->where('name_report', 'like', '%?%')
            ->update([
                'name_report' => DB::raw('REPLACE(`name_report`, \'?\', \'°\')'),
            ]);
    }

    public function down()
    {
        // Tidak mengembalikan ? vs ° per baris.
    }
}
