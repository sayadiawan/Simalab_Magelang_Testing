<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pindahkan tb_baku_mutu.method_id dari metode "APM … Escherichia" (params/name)
 * ke metode Escherichia coli tanpa APM.
 */
class UpdateTbBakuMutuMethodIdApmEscherichiaToEscherichia extends Migration
{
    private function methodSearchExpression(): string
    {
        return "LOWER(CONCAT(COALESCE(`params_method`, ''), ' ', COALESCE(`name_method`, '')))";
    }

    public function up()
    {
        if (!Schema::hasTable('tb_baku_mutu') || !Schema::hasTable('ms_method')) {
            return;
        }

        if (!Schema::hasColumn('tb_baku_mutu', 'method_id')) {
            return;
        }

        $expr = $this->methodSearchExpression();

        $oldIds = DB::table('ms_method')
            ->whereNull('deleted_at')
            ->whereRaw($expr.' LIKE ?', ['%apm%'])
            ->where(function ($q) use ($expr) {
                $q->whereRaw($expr.' LIKE ?', ['%escherichia%'])
                    ->orWhereRaw($expr.' LIKE ?', ['%escerichia%']);
            })
            ->pluck('id_method');

        if ($oldIds->isEmpty()) {
            return;
        }

        $newRow = DB::table('ms_method')
            ->whereNull('deleted_at')
            ->where(function ($q) use ($expr) {
                $q->whereRaw($expr.' LIKE ?', ['%escherichia%'])
                    ->orWhereRaw($expr.' LIKE ?', ['%escerichia%']);
            })
            ->whereRaw($expr.' NOT LIKE ?', ['%apm%'])
            ->orderBy('params_method')
            ->orderBy('name_method')
            ->first(['id_method']);

        if (!$newRow) {
            return;
        }

        $newId = $newRow->id_method;

        $q = DB::table('tb_baku_mutu')->whereIn('method_id', $oldIds->all());
        if (Schema::hasColumn('tb_baku_mutu', 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        $q->where('method_id', '<>', $newId)->update([
            'method_id' => $newId,
        ]);
    }

    public function down()
    {
        //
    }
}
