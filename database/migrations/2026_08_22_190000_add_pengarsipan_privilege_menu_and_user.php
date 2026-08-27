<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Akun Pengarsip Hasil (ARSP) — PPT: Pencetak Hasil & Arsiparis
 *
 * Jalankan:
 *   php artisan migrate --path=database/migrations/2026_08_22_190000_add_pengarsipan_privilege_menu_and_user.php
 */
class AddPengarsipanPrivilegeMenuAndUser extends Migration
{
    const PRIV_LEVEL = 'ARSP';
    const PRIV_NAME = 'Pengarsip Hasil';
    const PRIV_DESC = 'Pencetak & arsip hasil laboratorium';

    const MENU_NAME = 'Pengarsipan Hasil';
    const MENU_LINK = '/pengarsipan';
    const MENU_ICON = 'ti-archive';
    const MENU_ORDER = 28;

    /** Menu yang boleh diakses pengarsip (read saja). */
    private $menuIds = [
        1,   // Dashboard
        2,   // Profil
        99,  // Laporan (parent)
        105, // Edit Password
        217, // Data Semua Sampel
        220, // Buku Register Hasil Klinis
        221, // Monitoring Sampling
        223, // Jumlah per Jenis Sampel
        227, // Log Aktivitas
    ];

    public function up()
    {
        if (!Schema::hasTable('ms_privilege')) {
            return;
        }

        $privilegeId = $this->ensurePrivilege();
        $menuId = $this->ensureMenu();
        $allowedMenuIds = $this->resolveAllowedMenuIds($menuId);

        $this->ensureRoles($privilegeId, $allowedMenuIds);
        $this->denyAllOtherMenus($privilegeId, $allowedMenuIds);
        $this->ensureParentMenuRead($privilegeId, [99]);
        $this->ensureDemoUser($privilegeId);
    }

    public function down()
    {
        if (!Schema::hasTable('ms_privilege')) {
            return;
        }

        $privilegeId = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        $menuId = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNull('deleted_at')
            ->value('id');

        if ($privilegeId) {
            DB::table('ms_users')
                ->where('level', $privilegeId)
                ->where('username', 'pengarsip')
                ->update(['deleted_at' => now()]);

            if (Schema::hasTable('tb_role') && $menuId) {
                DB::table('tb_role')
                    ->where('privilege_id', $privilegeId)
                    ->where('menu_id', $menuId)
                    ->update(['read' => '0', 'updated_at' => now()]);
            }
        }

        if ($menuId) {
            DB::table('ms_menuadm')
                ->where('id', $menuId)
                ->update(['publish' => 0, 'updated_at' => now()]);
        }
    }

    /**
     * @return string
     */
    private function ensurePrivilege()
    {
        $existing = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        if ($existing) {
            DB::table('ms_privilege')
                ->where('id', $existing)
                ->update([
                    'name' => self::PRIV_NAME,
                    'description' => self::PRIV_DESC,
                    'is_elits' => 1,
                    'updated_at' => now(),
                ]);

            return (string) $existing;
        }

        $trashed = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashed) {
            DB::table('ms_privilege')
                ->where('id', $trashed)
                ->update([
                    'name' => self::PRIV_NAME,
                    'description' => self::PRIV_DESC,
                    'is_elits' => 1,
                    'deleted_at' => null,
                    'updated_at' => now(),
                ]);

            return (string) $trashed;
        }

        $id = Uuid::uuid4()->toString();
        DB::table('ms_privilege')->insert([
            'id' => $id,
            'level' => self::PRIV_LEVEL,
            'name' => self::PRIV_NAME,
            'description' => self::PRIV_DESC,
            'is_elits' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    /**
     * @return int
     */
    private function ensureMenu()
    {
        $payload = [
            'upmenu' => 0,
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

        $trashed = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashed) {
            DB::table('ms_menuadm')->where('id', $trashed)->update($payload);

            return (int) $trashed;
        }

        return (int) DB::table('ms_menuadm')->insertGetId(array_merge($payload, [
            'created_at' => now(),
        ]));
    }

    /**
     * @param int $pengarsipMenuId
     * @return int[]
     */
    private function resolveAllowedMenuIds($pengarsipMenuId)
    {
        $ids = array_map('intval', $this->menuIds);
        $ids[] = (int) $pengarsipMenuId;

        if (Schema::hasTable('ms_menuadm')) {
            $activityLogId = DB::table('ms_menuadm')
                ->where('link', '/activity-log')
                ->whereNull('deleted_at')
                ->value('id');
            if ($activityLogId) {
                $ids[] = (int) $activityLogId;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Semua menu selain daftar izin diset read=0 agar tidak tampil di sidebar.
     *
     * @param string $privilegeId
     * @param int[] $allowedMenuIds
     * @return void
     */
    private function denyAllOtherMenus($privilegeId, array $allowedMenuIds)
    {
        if (!Schema::hasTable('tb_role') || !Schema::hasTable('ms_menuadm')) {
            return;
        }

        $allowedMenuIds = array_map('intval', array_unique($allowedMenuIds));
        $allMenuIds = DB::table('ms_menuadm')
            ->whereNull('deleted_at')
            ->where('publish', 1)
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        $denyMenuIds = array_diff($allMenuIds, $allowedMenuIds);
        $now = now();

        foreach ($denyMenuIds as $menuId) {
            $existing = DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->value('id');

            $payload = [
                'create' => '0',
                'read' => '0',
                'update' => '0',
                'delete' => '0',
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('tb_role')->where('id', $existing)->update($payload);
                continue;
            }

            $trashed = DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', $menuId)
                ->whereNotNull('deleted_at')
                ->value('id');

            if ($trashed) {
                DB::table('tb_role')
                    ->where('id', $trashed)
                    ->update(array_merge($payload, ['deleted_at' => null]));
                continue;
            }

            DB::table('tb_role')->insert([
                'id' => Uuid::uuid4()->toString(),
                'privilege_id' => $privilegeId,
                'menu_id' => $menuId,
                'create' => '0',
                'read' => '0',
                'update' => '0',
                'delete' => '0',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * @param string $privilegeId
     * @param int[] $menuIds
     * @return void
     */
    private function ensureRoles($privilegeId, array $menuIds)
    {
        if (!Schema::hasTable('tb_role')) {
            return;
        }

        $now = now();

        foreach ($menuIds as $menuId) {
            $existing = DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->value('id');

            $payload = [
                'create' => '0',
                'read' => '1',
                'update' => '0',
                'delete' => '0',
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('tb_role')->where('id', $existing)->update($payload);
                continue;
            }

            $trashed = DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', $menuId)
                ->whereNotNull('deleted_at')
                ->value('id');

            if ($trashed) {
                DB::table('tb_role')
                    ->where('id', $trashed)
                    ->update(array_merge($payload, ['deleted_at' => null]));
                continue;
            }

            DB::table('tb_role')->insert([
                'id' => Uuid::uuid4()->toString(),
                'privilege_id' => $privilegeId,
                'menu_id' => $menuId,
                'create' => '0',
                'read' => '1',
                'update' => '0',
                'delete' => '0',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * @param string $privilegeId
     * @param int[] $parentMenuIds
     * @return void
     */
    private function ensureParentMenuRead($privilegeId, array $parentMenuIds)
    {
        if (!Schema::hasTable('tb_role')) {
            return;
        }

        foreach ($parentMenuIds as $menuId) {
            DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->update(['read' => '1', 'updated_at' => now()]);
        }
    }

    /**
     * @param string $privilegeId
     * @return void
     */
    private function ensureDemoUser($privilegeId)
    {
        if (!Schema::hasTable('ms_users')) {
            return;
        }

        $existing = DB::table('ms_users')
            ->where('username', 'pengarsip')
            ->whereNull('deleted_at')
            ->value('id');

        if ($existing) {
            DB::table('ms_users')
                ->where('id', $existing)
                ->update([
                    'name' => 'Petugas Pengarsipan',
                    'level' => $privilegeId,
                    'updated_at' => now(),
                ]);

            return;
        }

        $trashed = DB::table('ms_users')
            ->where('username', 'pengarsip')
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashed) {
            DB::table('ms_users')
                ->where('id', $trashed)
                ->update([
                    'name' => 'Petugas Pengarsipan',
                    'level' => $privilegeId,
                    'deleted_at' => null,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('ms_users')->insert([
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Petugas Pengarsipan',
            'username' => 'pengarsip',
            'email' => 'pengarsip@simlab.local',
            'password' => bcrypt('elits'),
            'level' => $privilegeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
