<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Menu Log Aktivitas Sistem — /activity-log
 * Parent: Laporan (id=99)
 */
class AddMenuActivityLog extends Migration
{
    const MENU_NAME = 'Log Aktivitas Sistem';
    const MENU_LINK = '/activity-log';
    const PARENT_LAPORAN = 99;
    const MENU_ORDER = 50;

    public function up()
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        $menuId = $this->ensureMenu();
        if ($menuId) {
            $this->ensureRoles($menuId);
        }
    }

    public function down()
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        $menuId = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$menuId) {
            return;
        }

        if (Schema::hasTable('tb_role')) {
            DB::table('tb_role')
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        DB::table('ms_menuadm')
            ->where('id', $menuId)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function ensureMenu()
    {
        $existing = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNull('deleted_at')
            ->value('id');

        if ($existing) {
            DB::table('ms_menuadm')
                ->where('id', $existing)
                ->update([
                    'upmenu' => self::PARENT_LAPORAN,
                    'type' => 0,
                    'name' => self::MENU_NAME,
                    'link' => self::MENU_LINK,
                    'order' => self::MENU_ORDER,
                    'publish' => 1,
                    'is_elits' => 1,
                    'updated_at' => now(),
                ]);

            return (int) $existing;
        }

        $trashedId = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashedId) {
            DB::table('ms_menuadm')
                ->where('id', $trashedId)
                ->update([
                    'upmenu' => self::PARENT_LAPORAN,
                    'type' => 0,
                    'name' => self::MENU_NAME,
                    'link' => self::MENU_LINK,
                    'order' => self::MENU_ORDER,
                    'publish' => 1,
                    'is_elits' => 1,
                    'deleted_at' => null,
                    'updated_at' => now(),
                ]);

            return (int) $trashedId;
        }

        return (int) DB::table('ms_menuadm')->insertGetId([
            'upmenu' => self::PARENT_LAPORAN,
            'type' => 0,
            'name' => self::MENU_NAME,
            'link' => self::MENU_LINK,
            'order' => self::MENU_ORDER,
            'publish' => 1,
            'is_elits' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureRoles($menuId)
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $privileges = DB::table('ms_privilege')
            ->whereNull('deleted_at')
            ->get(['id']);

        $now = now();

        foreach ($privileges as $privilege) {
            $existingId = DB::table('tb_role')
                ->where('privilege_id', $privilege->id)
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->value('id');

            if ($existingId) {
                DB::table('tb_role')
                    ->where('id', $existingId)
                    ->update([
                        'create' => '0',
                        'read' => '1',
                        'update' => '0',
                        'delete' => '0',
                        'updated_at' => $now,
                    ]);
                continue;
            }

            $trashed = DB::table('tb_role')
                ->where('privilege_id', $privilege->id)
                ->where('menu_id', $menuId)
                ->whereNotNull('deleted_at')
                ->value('id');

            if ($trashed) {
                DB::table('tb_role')
                    ->where('id', $trashed)
                    ->update([
                        'create' => '0',
                        'read' => '1',
                        'update' => '0',
                        'delete' => '0',
                        'deleted_at' => null,
                        'updated_at' => $now,
                    ]);
                continue;
            }

            DB::table('tb_role')->insert([
                'id' => Uuid::uuid4()->toString(),
                'privilege_id' => $privilege->id,
                'menu_id' => $menuId,
                'create' => '0',
                'read' => '1',
                'update' => '0',
                'delete' => '0',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }

        $this->ensureParentLaporanReadForGrantedPrivileges($menuId);
    }

    private function ensureParentLaporanReadForGrantedPrivileges($menuId)
    {
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
}
