<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Tampilkan menu Log Aktivitas langsung di sidebar (bukan submenu Laporan).
 * Jalankan: php artisan migrate --path=database/migrations/2026_08_22_180000_promote_activity_log_menu_to_sidebar.php
 */
class PromoteActivityLogMenuToSidebar extends Migration
{
    const MENU_LINK = '/activity-log';
    const MENU_NAME = 'Log Aktivitas Sistem';
    const MENU_ICON = 'ti-time';
    /** Tampil setelah menu Laporan (order 26). */
    const MENU_ORDER = 27;

    public function up()
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        $menuId = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$menuId) {
            $menuId = $this->insertMenu();
        }

        if (!$menuId) {
            return;
        }

        DB::table('ms_menuadm')
            ->where('id', $menuId)
            ->update([
                'upmenu' => 0,
                'type' => 0,
                'name' => self::MENU_NAME,
                'icon' => self::MENU_ICON,
                'link' => self::MENU_LINK,
                'order' => self::MENU_ORDER,
                'publish' => 1,
                'is_elits' => 1,
                'updated_at' => now(),
            ]);

        $this->ensureRoles((int) $menuId);
    }

    public function down()
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNull('deleted_at')
            ->update([
                'upmenu' => 99,
                'order' => 50,
                'updated_at' => now(),
            ]);
    }

    /**
     * @return int|null
     */
    private function insertMenu()
    {
        return (int) DB::table('ms_menuadm')->insertGetId([
            'upmenu' => 0,
            'type' => 0,
            'name' => self::MENU_NAME,
            'icon' => self::MENU_ICON,
            'link' => self::MENU_LINK,
            'order' => self::MENU_ORDER,
            'publish' => 1,
            'is_elits' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param int $menuId
     * @return void
     */
    private function ensureRoles($menuId)
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $privileges = DB::table('ms_privilege')->whereNull('deleted_at')->get(['id']);
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
                        'read' => '1',
                        'create' => '0',
                        'update' => '0',
                        'delete' => '0',
                        'updated_at' => $now,
                    ]);
                continue;
            }

            $trashedId = DB::table('tb_role')
                ->where('privilege_id', $privilege->id)
                ->where('menu_id', $menuId)
                ->whereNotNull('deleted_at')
                ->value('id');

            if ($trashedId) {
                DB::table('tb_role')
                    ->where('id', $trashedId)
                    ->update([
                        'read' => '1',
                        'create' => '0',
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
    }
}
