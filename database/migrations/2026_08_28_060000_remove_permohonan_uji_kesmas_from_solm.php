<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hapus menu Permohonan Uji Kesmas (ID 96) dari hak akses Pengambil Sampel Kesmas (SOLM).
 */
class RemovePermohonanUjiKesmasFromSolm extends Migration
{
    const LEVEL_KESMAS = 'SOLM';
    const MENU_PERMOHONAN_KESMAS = 96;

    public function up()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $solMId = DB::table('ms_privilege')
            ->where('level', self::LEVEL_KESMAS)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$solMId) {
            return;
        }

        DB::table('tb_role')
            ->where('privilege_id', $solMId)
            ->where('menu_id', self::MENU_PERMOHONAN_KESMAS)
            ->update([
                'read' => '0',
                'create' => '0',
                'update' => '0',
                'delete' => '0',
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $solMId = DB::table('ms_privilege')
            ->where('level', self::LEVEL_KESMAS)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$solMId) {
            return;
        }

        DB::table('tb_role')
            ->where('privilege_id', $solMId)
            ->where('menu_id', self::MENU_PERMOHONAN_KESMAS)
            ->update([
                'read' => '1',
                'create' => '1',
                'update' => '1',
                'delete' => '1',
                'updated_at' => now(),
            ]);
    }
}
