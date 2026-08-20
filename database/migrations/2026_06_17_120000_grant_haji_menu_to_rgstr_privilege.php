<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Aktifkan menu Haji Klinik untuk privilege Registrasi (RGSTR).
 */
class GrantHajiMenuToRgstrPrivilege extends Migration
{
    /** @var int Menu parent Klinik */
    private $parentMenuId = 118;

    /** @var int Menu Haji Klinik */
    private $hajiMenuId = 205;

    public function up()
    {
        $privilegeId = DB::table('ms_privilege')
            ->where('level', 'RGSTR')
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->where('menu_id', $this->parentMenuId)
            ->whereNull('deleted_at')
            ->update([
                'read' => '1',
                'updated_at' => now(),
            ]);

        DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->where('menu_id', $this->hajiMenuId)
            ->whereNull('deleted_at')
            ->update([
                'read' => '1',
                'create' => '1',
                'update' => '1',
                'delete' => '1',
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        $privilegeId = DB::table('ms_privilege')
            ->where('level', 'RGSTR')
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        foreach ([$this->parentMenuId, $this->hajiMenuId] as $menuId) {
            DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->update([
                    'read' => '0',
                    'create' => '0',
                    'update' => '0',
                    'delete' => '0',
                    'updated_at' => now(),
                ]);
        }
    }
}
