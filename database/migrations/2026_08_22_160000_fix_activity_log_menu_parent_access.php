<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pastikan submenu Log Aktivitas tampil di sidebar:
 * parent "Laporan" (id=99) harus read=1 jika privilege punya akses baca menu /activity-log.
 */
class FixActivityLogMenuParentAccess extends Migration
{
    const MENU_LINK = '/activity-log';
    const PARENT_LAPORAN = 99;

    public function up()
    {
        if (!Schema::hasTable('ms_menuadm') || !Schema::hasTable('tb_role')) {
            return;
        }

        $menuId = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$menuId) {
            return;
        }

        DB::table('ms_menuadm')
            ->where('id', $menuId)
            ->update([
                'name' => 'Log Aktivitas Sistem',
                'icon' => 'ti-time',
                'upmenu' => self::PARENT_LAPORAN,
                'publish' => 1,
                'is_elits' => 1,
                'updated_at' => now(),
            ]);

        $privilegeIds = DB::table('tb_role')
            ->where('menu_id', $menuId)
            ->where('read', '1')
            ->whereNull('deleted_at')
            ->pluck('privilege_id');

        foreach ($privilegeIds as $privilegeId) {
            $parentRoleId = DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', self::PARENT_LAPORAN)
                ->whereNull('deleted_at')
                ->value('id');

            if ($parentRoleId) {
                DB::table('tb_role')
                    ->where('id', $parentRoleId)
                    ->update([
                        'read' => '1',
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down()
    {
        // Tidak mengembalikan read parent — bisa memengaruhi menu laporan lain.
    }
}
