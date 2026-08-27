<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cabut akses menu Permohonan Uji KESMAS (id=96) dari privilege Pengarsip (ARSP).
 */
class RevokeKesmasMenuFromPengarsip extends Migration
{
    const PRIV_LEVEL = 'ARSP';
    const MENU_ID = 96;

    public function up()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $privilegeId = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->where('menu_id', self::MENU_ID)
            ->whereNull('deleted_at')
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

        $privilegeId = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->where('menu_id', self::MENU_ID)
            ->whereNull('deleted_at')
            ->update([
                'read' => '1',
                'create' => '0',
                'update' => '0',
                'delete' => '0',
                'updated_at' => now(),
            ]);
    }
}
