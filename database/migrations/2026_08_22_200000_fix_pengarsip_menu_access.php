<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Perbaiki akses menu ARSP: semua menu di luar daftar izin diset read=0.
 *
 * Jalankan:
 *   php artisan migrate --path=database/migrations/2026_08_22_200000_fix_pengarsip_menu_access.php
 */
class FixPengarsipMenuAccess extends Migration
{
    const PRIV_LEVEL = 'ARSP';

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
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role') || !Schema::hasTable('ms_menuadm')) {
            return;
        }

        $privilegeId = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        $allowedMenuIds = $this->resolveAllowedMenuIds();
        $this->ensureRoles((string) $privilegeId, $allowedMenuIds);
        $this->denyAllOtherMenus((string) $privilegeId, $allowedMenuIds);
    }

    public function down()
    {
        // Tidak di-rollback: akses menu ARSP dikelola lewat migration pengarsipan.
    }

    /**
     * @return int[]
     */
    private function resolveAllowedMenuIds()
    {
        $ids = array_map('intval', $this->menuIds);

        $pengarsipMenuId = DB::table('ms_menuadm')
            ->where('link', '/pengarsipan')
            ->whereNull('deleted_at')
            ->value('id');
        if ($pengarsipMenuId) {
            $ids[] = (int) $pengarsipMenuId;
        }

        $activityLogId = DB::table('ms_menuadm')
            ->where('link', '/activity-log')
            ->whereNull('deleted_at')
            ->value('id');
        if ($activityLogId) {
            $ids[] = (int) $activityLogId;
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param string $privilegeId
     * @param int[] $menuIds
     * @return void
     */
    private function ensureRoles($privilegeId, array $menuIds)
    {
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
     * @param int[] $allowedMenuIds
     * @return void
     */
    private function denyAllOtherMenus($privilegeId, array $allowedMenuIds)
    {
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
}
