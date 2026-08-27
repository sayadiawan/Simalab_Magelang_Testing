<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Pisahkan Pengambil Sampel: SOLK (Klinik) dan SOLM (Kesmas).
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_26_060000_split_pengambil_sampel_klinik_kesmas.php
 */
class SplitPengambilSampelKlinikKesmas extends Migration
{
    const LEGACY_LEVEL = 'SOLAB';
    const LEVEL_KLINIK = 'SOLK';
    const LEVEL_KESMAS = 'SOLM';

    const MENU_PENGAMBILAN_KESMAS = 233;

    /** @var array<int, int> */
    private $solKMenus = [
        1 => 1,    // Dashboard
        2 => 2,    // Profil
        214 => 14, // Pengambilan Sampel Klinik
        221 => 15, // Monitoring Sampling Klinik
        105 => 98, // Edit Password
        227 => 99, // Log Aktivitas
    ];

    /** @var array<int, int> */
    private $solMMenus = [
        1 => 1,    // Dashboard
        2 => 2,    // Profil
        self::MENU_PENGAMBILAN_KESMAS => 14, // Pengambilan Sampel Kesmas
        105 => 98, // Edit Password
        227 => 99, // Log Aktivitas
    ];

    public function up()
    {
        if (!Schema::hasTable('ms_privilege')) {
            return;
        }

        $solKId = $this->ensureSolKPrivilege();
        $solMId = $this->ensureSolMPrivilege();
        $this->ensurePengambilanKesmasMenu();
        $this->configurePrivilegeMenus((string) $solKId, $this->solKMenus);
        $this->configurePrivilegeMenus((string) $solMId, $this->solMMenus);
        $this->ensureDemoUsers((string) $solKId, (string) $solMId);
    }

    public function down()
    {
        if (!Schema::hasTable('ms_privilege')) {
            return;
        }

        $solKId = DB::table('ms_privilege')
            ->where('level', self::LEVEL_KLINIK)
            ->whereNull('deleted_at')
            ->value('id');

        if ($solKId) {
            DB::table('ms_privilege')
                ->where('id', $solKId)
                ->update([
                    'level' => self::LEGACY_LEVEL,
                    'name' => 'Pengambil Sample LAB',
                    'description' => 'Pengambil sampel klinik & kesmas',
                    'updated_at' => now(),
                ]);
        }

        $solMId = DB::table('ms_privilege')
            ->where('level', self::LEVEL_KESMAS)
            ->whereNull('deleted_at')
            ->value('id');

        if ($solMId && Schema::hasTable('ms_users')) {
            DB::table('ms_users')
                ->where('level', $solMId)
                ->where('username', 'pengambil_sampel_kesmas')
                ->update(['deleted_at' => now()]);
        }

        if ($solMId && Schema::hasTable('tb_role')) {
            DB::table('tb_role')
                ->where('privilege_id', $solMId)
                ->update(['read' => '0', 'deleted_at' => now(), 'updated_at' => now()]);
        }

        if ($solMId) {
            DB::table('ms_privilege')
                ->where('id', $solMId)
                ->update(['deleted_at' => now(), 'updated_at' => now()]);
        }

        if (Schema::hasTable('ms_menuadm')) {
            DB::table('ms_menuadm')
                ->where('id', self::MENU_PENGAMBILAN_KESMAS)
                ->update(['publish' => '0', 'updated_at' => now()]);
        }
    }

    /**
     * @return string
     */
    private function ensureSolKPrivilege()
    {
        $legacy = DB::table('ms_privilege')
            ->where('level', self::LEGACY_LEVEL)
            ->whereNull('deleted_at')
            ->first();

        if ($legacy) {
            DB::table('ms_privilege')
                ->where('id', $legacy->id)
                ->update([
                    'level' => self::LEVEL_KLINIK,
                    'name' => 'Pengambil Sampel Klinik',
                    'description' => 'Pengambilan sampel laboratorium klinik',
                    'updated_at' => now(),
                ]);

            return (string) $legacy->id;
        }

        $existing = DB::table('ms_privilege')
            ->where('level', self::LEVEL_KLINIK)
            ->whereNull('deleted_at')
            ->value('id');

        if ($existing) {
            return (string) $existing;
        }

        $id = Uuid::uuid4()->toString();
        DB::table('ms_privilege')->insert([
            'id' => $id,
            'level' => self::LEVEL_KLINIK,
            'name' => 'Pengambil Sampel Klinik',
            'description' => 'Pengambilan sampel laboratorium klinik',
            'is_elits' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    /**
     * @return string
     */
    private function ensureSolMPrivilege()
    {
        $existing = DB::table('ms_privilege')
            ->where('level', self::LEVEL_KESMAS)
            ->whereNull('deleted_at')
            ->value('id');

        if ($existing) {
            DB::table('ms_privilege')
                ->where('id', $existing)
                ->update([
                    'name' => 'Pengambil Sampel Kesmas',
                    'description' => 'Pengambilan sampel laboratorium kesmas',
                    'is_elits' => 1,
                    'updated_at' => now(),
                ]);

            return (string) $existing;
        }

        $trashed = DB::table('ms_privilege')
            ->where('level', self::LEVEL_KESMAS)
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashed) {
            DB::table('ms_privilege')
                ->where('id', $trashed)
                ->update([
                    'name' => 'Pengambil Sampel Kesmas',
                    'description' => 'Pengambilan sampel laboratorium kesmas',
                    'is_elits' => 1,
                    'deleted_at' => null,
                    'updated_at' => now(),
                ]);

            return (string) $trashed;
        }

        $id = Uuid::uuid4()->toString();
        DB::table('ms_privilege')->insert([
            'id' => $id,
            'level' => self::LEVEL_KESMAS,
            'name' => 'Pengambil Sampel Kesmas',
            'description' => 'Pengambilan sampel laboratorium kesmas',
            'is_elits' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    /**
     * @return void
     */
    private function ensurePengambilanKesmasMenu()
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        $link = '/elits-analys?status_filter=pengambilan_sample';
        $payload = [
            'name' => 'Pengambilan Sampel Kesmas',
            'icon' => 'ti-layers',
            'link' => $link,
            'type' => 'SM',
            'order' => 14,
            'publish' => '1',
            'is_elits' => 1,
            'updated_at' => now(),
        ];

        $existing = DB::table('ms_menuadm')
            ->where('id', self::MENU_PENGAMBILAN_KESMAS)
            ->value('id');

        if ($existing) {
            DB::table('ms_menuadm')->where('id', $existing)->update($payload);

            return;
        }

        DB::table('ms_menuadm')->insert(array_merge($payload, [
            'id' => self::MENU_PENGAMBILAN_KESMAS,
            'upmenu' => 0,
            'created_at' => now(),
        ]));
    }

    /**
     * @param string $privilegeId
     * @param array<int, int> $allowedMenus menu_id => order
     * @return void
     */
    private function configurePrivilegeMenus($privilegeId, array $allowedMenus)
    {
        if (!Schema::hasTable('tb_role') || !Schema::hasTable('ms_menuadm')) {
            return;
        }

        $allowedIds = array_map('intval', array_keys($allowedMenus));

        foreach ($allowedMenus as $menuId => $order) {
            DB::table('ms_menuadm')
                ->where('id', $menuId)
                ->whereNull('deleted_at')
                ->update(['order' => $order, 'updated_at' => now()]);

            $this->ensureRole($privilegeId, (int) $menuId, true);
        }

        $this->denyAllOtherMenus($privilegeId, $allowedIds);
    }

    /**
     * @param string $privilegeId
     * @param int $menuId
     * @param bool $allowRead
     * @return void
     */
    private function ensureRole($privilegeId, $menuId, $allowRead)
    {
        $now = now();
        $read = $allowRead ? '1' : '0';

        $existing = DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->where('menu_id', $menuId)
            ->whereNull('deleted_at')
            ->value('id');

        $payload = [
            'create' => '0',
            'read' => $read,
            'update' => '0',
            'delete' => '0',
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('tb_role')->where('id', $existing)->update($payload);

            return;
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

            return;
        }

        DB::table('tb_role')->insert([
            'id' => Uuid::uuid4()->toString(),
            'privilege_id' => $privilegeId,
            'menu_id' => $menuId,
            'create' => '0',
            'read' => $read,
            'update' => '0',
            'delete' => '0',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * @param string $privilegeId
     * @param int[] $allowedMenuIds
     * @return void
     */
    private function denyAllOtherMenus($privilegeId, array $allowedMenuIds)
    {
        DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->whereNull('deleted_at')
            ->whereNotIn('menu_id', $allowedMenuIds)
            ->update(['read' => '0', 'updated_at' => now()]);
    }

    /**
     * @param string $solKId
     * @param string $solMId
     * @return void
     */
    private function ensureDemoUsers($solKId, $solMId)
    {
        if (!Schema::hasTable('ms_users')) {
            return;
        }

        $kliLabId = DB::table('ms_laboratorium')
            ->where('kode_laboratorium', 'KLI')
            ->whereNull('deleted_at')
            ->value('id_laboratorium');

        $kimLabId = DB::table('ms_laboratorium')
            ->where('kode_laboratorium', 'KIM')
            ->whereNull('deleted_at')
            ->value('id_laboratorium');

        $this->upsertDemoUser([
            'username' => 'pengambil_sampel_klinik',
            'legacy_username' => 'pengambil_sampel',
            'name' => 'Pengambil Sampel Klinik',
            'email' => 'pengambil.klinik@simlab.local',
            'level' => $solKId,
            'laboratory_users' => $kliLabId,
        ]);

        $this->upsertDemoUser([
            'username' => 'pengambil_sampel_kesmas',
            'name' => 'Pengambil Sampel Kesmas',
            'email' => 'pengambil.kesmas@simlab.local',
            'level' => $solMId,
            'laboratory_users' => $kimLabId,
        ]);
    }

    /**
     * @param array{username: string, legacy_username?: string, name: string, email: string, level: string, laboratory_users: string|null} $data
     * @return void
     */
    private function upsertDemoUser(array $data)
    {
        $usernames = array_values(array_filter([
            $data['username'],
            $data['legacy_username'] ?? null,
        ]));

        $existing = DB::table('ms_users')
            ->whereIn('username', $usernames)
            ->whereNull('deleted_at')
            ->orderByRaw('username = ? DESC', [$data['username']])
            ->value('id');

        $payload = [
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'level' => $data['level'],
            'laboratory_users' => $data['laboratory_users'],
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('ms_users')->where('id', $existing)->update($payload);

            return;
        }

        $trashed = DB::table('ms_users')
            ->whereIn('username', $usernames)
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashed) {
            DB::table('ms_users')
                ->where('id', $trashed)
                ->update(array_merge($payload, ['deleted_at' => null]));

            return;
        }

        DB::table('ms_users')->insert(array_merge($payload, [
            'id' => Uuid::uuid4()->toString(),
            'password' => bcrypt('elits'),
            'publish' => '1',
            'created_at' => now(),
        ]));
    }
}
