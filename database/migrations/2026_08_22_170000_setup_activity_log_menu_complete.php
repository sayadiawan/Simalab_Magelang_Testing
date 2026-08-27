<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Setup menu Log Aktivitas Sistem — idempotent, aman dijalankan ulang.
 *
 * Menu: Laporan → Log Aktivitas Sistem (/activity-log)
 *
 * Deploy di server (PHP sesuai env, mis. php7.4):
 *   php artisan migrate --path=database/migrations/2026_08_22_150000_create_tb_activity_log_table.php
 *   php artisan migrate --path=database/migrations/2026_08_22_170000_setup_activity_log_menu_complete.php
 *
 * Atau sekaligus:
 *   php artisan migrate
 */
class SetupActivityLogMenuComplete extends Migration
{
    const MENU_NAME = 'Log Aktivitas Sistem';
    const MENU_LINK = '/activity-log';
    const MENU_ICON = 'ti-time';
    const PARENT_LAPORAN = 99;
    const MENU_ORDER = 50;

    /**
     * Privilege level yang boleh melihat menu (data tetap difilter di ActivityLogAccess).
     *
     * @var string[]
     */
    private $grantedLevels = [
        '00', 'elits-dev',              // Super Administrator
        'LAB', 'ADMD', 'MAN', 'admin',  // Admin
        'PLAB', 'KLAB', 'DKTR', 'KSKL', 'KSKM', // Koordinator / Kepala Lab / Kasie
        'ANLS', 'SOLAB', 'ALAB',        // Analis & petugas lab
        'RGSTR', 'RCPT', 'CLI', 'USR',  // Registrasi & operasional
    ];

    public function up()
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        $menuId = $this->ensureMenu();
        if (!$menuId) {
            return;
        }

        $this->ensureRoles($menuId);
        $this->ensureParentLaporanRead($menuId);
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
                    'read' => '0',
                    'updated_at' => now(),
                ]);
        }

        DB::table('ms_menuadm')
            ->where('id', $menuId)
            ->update([
                'publish' => 0,
                'updated_at' => now(),
            ]);
    }

    /**
     * @return int|null
     */
    private function ensureMenu()
    {
        $payload = [
            'upmenu' => self::PARENT_LAPORAN,
            'type' => 0,
            'name' => self::MENU_NAME,
            'icon' => self::MENU_ICON,
            'link' => self::MENU_LINK,
            'order' => self::MENU_ORDER,
            'publish' => 1,
            'is_elits' => 1,
            'deleted_at' => null,
            'updated_at' => now(),
        ];

        $existing = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNull('deleted_at')
            ->value('id');

        if ($existing) {
            DB::table('ms_menuadm')->where('id', $existing)->update($payload);

            return (int) $existing;
        }

        $trashedId = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashedId) {
            DB::table('ms_menuadm')->where('id', $trashedId)->update($payload);

            return (int) $trashedId;
        }

        return (int) DB::table('ms_menuadm')->insertGetId(array_merge($payload, [
            'created_at' => now(),
        ]));
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

        $privileges = DB::table('ms_privilege')
            ->whereNull('deleted_at')
            ->get(['id', 'level']);

        $grantedIds = $privileges
            ->filter(function ($row) {
                return in_array((string) $row->level, $this->grantedLevels, true);
            })
            ->pluck('id');

        $now = now();

        foreach ($privileges as $privilege) {
            $canRead = $grantedIds->contains($privilege->id) ? '1' : '0';

            $existingId = DB::table('tb_role')
                ->where('privilege_id', $privilege->id)
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->value('id');

            $rolePayload = [
                'create' => '0',
                'read' => $canRead,
                'update' => '0',
                'delete' => '0',
                'updated_at' => $now,
            ];

            if ($existingId) {
                DB::table('tb_role')->where('id', $existingId)->update($rolePayload);
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
                    ->update(array_merge($rolePayload, ['deleted_at' => null]));
                continue;
            }

            DB::table('tb_role')->insert([
                'id' => Uuid::uuid4()->toString(),
                'privilege_id' => $privilege->id,
                'menu_id' => $menuId,
                'create' => '0',
                'read' => $canRead,
                'update' => '0',
                'delete' => '0',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        }
    }

    /**
     * @param int $menuId
     * @return void
     */
    private function ensureParentLaporanRead($menuId)
    {
        if (!Schema::hasTable('tb_role')) {
            return;
        }

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
