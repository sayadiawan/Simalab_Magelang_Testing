<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Privilege Kasie (Kepala Lab) — menu fokus verifikasi, terpisah dari KLAB penuh.
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_23_120000_add_kasie_privileges_and_restrict_menus.php
 */
class AddKasiePrivilegesAndRestrictMenus extends Migration
{
    const PRIV_KLINIK = 'KSKL';
    const PRIV_KESMAS = 'KSKM';

    /** Menu Kasie Lab Klinik — verifikasi & validasi saja. */
    private $menuKlinik = [
        1,   // Dashboard
        2,   // Profil
        105, // Edit Password
        118, // Klinik (parent submenu)
        210, // Verifikasi Klinik
        212, // Verifikasi (sub Klinik)
        214, // Pengambilan Sampel
        215, // Validasi Hasil
        216, // Pemeriksaan
        207, // Verifikasi Dokumen
        227, // Log Aktivitas
    ];

    /** Menu Kasie Lab Kesmas — permohonan uji & verifikasi kesmas. */
    private $menuKesmas = [
        1,   // Dashboard
        2,   // Profil
        105, // Edit Password
        96,  // Permohonan Uji KESMAS
        83,  // Data Analisa (verifikasi lists kesmas)
        217, // Data Semua Sampel
        227, // Log Aktivitas
    ];

    public function up()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $ksklId = $this->ensurePrivilege(
            self::PRIV_KLINIK,
            'Kasie Lab Klinik',
            'Kepala lab klinik — verifikasi & validasi hasil'
        );
        $kskmId = $this->ensurePrivilege(
            self::PRIV_KESMAS,
            'Kasie Lab Kesmas',
            'Kepala lab kesmas — verifikasi sampel lingkungan'
        );

        $this->ensureRoles($ksklId, $this->menuKlinik, true);
        $this->denyAllOtherMenus($ksklId, $this->menuKlinik);
        $this->ensureActivityLogRead($ksklId);

        $this->ensureRoles($kskmId, $this->menuKesmas, true);
        $this->denyAllOtherMenus($kskmId, $this->menuKesmas);
        $this->ensureActivityLogRead($kskmId);

        $this->assignDemoUsers($ksklId, $kskmId);
    }

    public function down()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('ms_users')) {
            return;
        }

        $klabId = DB::table('ms_privilege')
            ->where('level', 'KLAB')
            ->whereNull('deleted_at')
            ->value('id');

        if ($klabId) {
            foreach (['kasie-klinik', 'kasie-kesmas'] as $username) {
                DB::table('ms_users')
                    ->where('username', $username)
                    ->whereNull('deleted_at')
                    ->update(['level' => $klabId, 'updated_at' => now()]);
            }
        }
    }

    /**
     * @param string $level
     * @param string $name
     * @param string $desc
     * @return string
     */
    private function ensurePrivilege($level, $name, $desc)
    {
        $existing = DB::table('ms_privilege')
            ->where('level', $level)
            ->whereNull('deleted_at')
            ->value('id');

        if ($existing) {
            DB::table('ms_privilege')
                ->where('id', $existing)
                ->update([
                    'name' => $name,
                    'description' => $desc,
                    'is_elits' => 1,
                    'updated_at' => now(),
                ]);

            return (string) $existing;
        }

        $trashed = DB::table('ms_privilege')
            ->where('level', $level)
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashed) {
            DB::table('ms_privilege')
                ->where('id', $trashed)
                ->update([
                    'name' => $name,
                    'description' => $desc,
                    'is_elits' => 1,
                    'deleted_at' => null,
                    'updated_at' => now(),
                ]);

            return (string) $trashed;
        }

        $id = Uuid::uuid4()->toString();
        DB::table('ms_privilege')->insert([
            'id' => $id,
            'level' => $level,
            'name' => $name,
            'description' => $desc,
            'is_elits' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    /**
     * @param string $privilegeId
     * @param int[] $menuIds
     * @param bool $withWrite
     * @return void
     */
    private function ensureRoles($privilegeId, array $menuIds, $withWrite = false)
    {
        $now = now();
        $write = $withWrite ? '1' : '0';

        foreach ($menuIds as $menuId) {
            $existing = DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->value('id');

            $payload = [
                'create' => $write,
                'read' => '1',
                'update' => $write,
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
                'create' => $payload['create'],
                'read' => $payload['read'],
                'update' => $payload['update'],
                'delete' => $payload['delete'],
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
        if (!Schema::hasTable('ms_menuadm')) {
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
     * @param string $ksklId
     * @param string $kskmId
     * @return void
     */
    private function assignDemoUsers($ksklId, $kskmId)
    {
        DB::table('ms_users')
            ->where('username', 'kasie-klinik')
            ->whereNull('deleted_at')
            ->update(['level' => $ksklId, 'updated_at' => now()]);

        DB::table('ms_users')
            ->where('username', 'kasie-kesmas')
            ->whereNull('deleted_at')
            ->update(['level' => $kskmId, 'updated_at' => now()]);
    }

    /**
     * Pastikan menu activity-log readable (idempotent).
     *
     * @param string $privilegeId
     * @return void
     */
    private function ensureActivityLogRead($privilegeId)
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        $menuId = DB::table('ms_menuadm')
            ->where('link', '/activity-log')
            ->whereNull('deleted_at')
            ->value('id');

        if (!$menuId) {
            return;
        }

        DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->where('menu_id', $menuId)
            ->whereNull('deleted_at')
            ->update(['read' => '1', 'updated_at' => now()]);
    }
}
