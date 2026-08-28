<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Privilege KUPTD (Kepala UPTD Labkes) + akun demo + menu terbatas.
 *
 * Fitur (sesuai presentasi):
 * - Melihat seluruh laporan aktivitas laboratorium
 * - Memvalidasi hasil pemeriksaan laboratorium
 * - Dashboard rangkuman visual aktivitas laboratorium
 * - Laporan & Persebaran Data
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_28_074000_add_kepala_uptd_labkes_privilege_menu_and_user.php
 */
class AddKepalaUptdLabkesPrivilegeMenuAndUser extends Migration
{
    const PRIV_LEVEL = 'KUPTD';
    const PRIV_NAME = 'Kepala UPTD Labkes';
    const PRIV_DESC = 'Validasi hasil, log aktivitas seluruh lab, dashboard rangkuman, laporan & persebaran data';

    const USERNAME = 'kepala-labkes';
    const USER_NAME = 'Kepala UPTD Labkes';
    const USER_EMAIL = 'kepala.labkes@simalab.local';
    const PASSWORD = 'elits';

    const MENU_VALIDASI_KESMAS_NAME = 'Validasi Hasil Kesmas';
    const MENU_VALIDASI_KESMAS_LINK = '/elits-analys?status_filter=validasi';

    /**
     * Menu tetap (id ms_menuadm).
     * Validasi Hasil Kesmas ditambahkan dinamis via link.
     *
     * @var int[]
     */
    private $fixedMenuIds = [
        1,   // Dashboard
        105, // Edit Password
        215, // Validasi Hasil Klinik
        227, // Log Aktivitas Sistem
        99,  // Laporan (parent)
        100, // Laporan Harian
        102, // Laporan Bulanan
        115, // Laporan Tahunan
        125, // Laporan Bulanan Tanggal Pengujian
        219, // Laporan Tahunan Klinik
        223, // Jumlah per Jenis Sampel
        218, // Persebaran Data
    ];

    public function up()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role') || !Schema::hasTable('ms_users')) {
            return;
        }

        $privId = $this->ensurePrivilege();
        $menuIds = $this->fixedMenuIds;

        if (Schema::hasTable('ms_menuadm')) {
            $validasiKesmasId = $this->ensureValidasiKesmasMenu();
            if ($validasiKesmasId) {
                $menuIds[] = (int) $validasiKesmasId;
            }
        }

        $menuIds = array_values(array_unique(array_map('intval', $menuIds)));

        $this->ensureRoles($privId, $menuIds);
        $this->denyAllOtherMenus($privId, $menuIds);
        $this->ensureUser($privId);
    }

    public function down()
    {
        if (!Schema::hasTable('ms_privilege')) {
            return;
        }

        $privId = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        if ($privId && Schema::hasTable('tb_role')) {
            DB::table('tb_role')
                ->where('privilege_id', $privId)
                ->whereNull('deleted_at')
                ->update([
                    'read' => '0',
                    'create' => '0',
                    'update' => '0',
                    'delete' => '0',
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('ms_users')) {
            DB::table('ms_users')
                ->where('username', self::USERNAME)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now(), 'updated_at' => now()]);
        }

        // Soft-delete menu Validasi Hasil Kesmas hanya jika tidak dipakai privilege lain
        if ($privId && Schema::hasTable('ms_menuadm') && Schema::hasTable('tb_role')) {
            $menuId = DB::table('ms_menuadm')
                ->where('link', self::MENU_VALIDASI_KESMAS_LINK)
                ->whereNull('deleted_at')
                ->value('id');

            if ($menuId) {
                $usedElsewhere = DB::table('tb_role')
                    ->where('menu_id', $menuId)
                    ->where('privilege_id', '!=', $privId)
                    ->whereNull('deleted_at')
                    ->where(function ($q) {
                        $q->where('read', 'on')->orWhere('read', '1')->orWhere('read', 1);
                    })
                    ->exists();

                if (!$usedElsewhere) {
                    DB::table('ms_menuadm')
                        ->where('id', $menuId)
                        ->update(['deleted_at' => now(), 'updated_at' => now()]);
                }
            }
        }
    }

    private function ensurePrivilege()
    {
        $existing = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            DB::table('ms_privilege')->where('id', $existing->id)->update([
                'name' => self::PRIV_NAME,
                'description' => self::PRIV_DESC,
                'is_elits' => 1,
                'updated_at' => now(),
            ]);

            return (string) $existing->id;
        }

        $trashed = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashed) {
            DB::table('ms_privilege')->where('id', $trashed)->update([
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
     * @return int|null
     */
    private function ensureValidasiKesmasMenu()
    {
        $existing = DB::table('ms_menuadm')
            ->where('link', self::MENU_VALIDASI_KESMAS_LINK)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            DB::table('ms_menuadm')->where('id', $existing->id)->update([
                'name' => self::MENU_VALIDASI_KESMAS_NAME,
                'icon' => 'fa-check-square',
                'publish' => '1',
                'is_elits' => 1,
                'updated_at' => now(),
            ]);

            return (int) $existing->id;
        }

        $trashed = DB::table('ms_menuadm')
            ->where('link', self::MENU_VALIDASI_KESMAS_LINK)
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashed) {
            DB::table('ms_menuadm')->where('id', $trashed)->update([
                'name' => self::MENU_VALIDASI_KESMAS_NAME,
                'icon' => 'fa-check-square',
                'upmenu' => 0,
                'type' => 'LE',
                'order' => 20,
                'publish' => '1',
                'is_elits' => 1,
                'deleted_at' => null,
                'updated_at' => now(),
            ]);

            return (int) $trashed;
        }

        return (int) DB::table('ms_menuadm')->insertGetId([
            'upmenu' => 0,
            'name' => self::MENU_VALIDASI_KESMAS_NAME,
            'icon' => 'fa-check-square',
            'link' => self::MENU_VALIDASI_KESMAS_LINK,
            'type' => 'LE',
            'order' => 20,
            'publish' => '1',
            'is_elits' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param string $privId
     * @param int[] $menuIds
     */
    private function ensureRoles($privId, array $menuIds)
    {
        $now = now();

        foreach ($menuIds as $menuId) {
            $menuId = (int) $menuId;
            if ($menuId <= 0) {
                continue;
            }

            // Pastikan menu masih ada
            $menuExists = DB::table('ms_menuadm')
                ->where('id', $menuId)
                ->whereNull('deleted_at')
                ->exists();
            if (!$menuExists) {
                continue;
            }

            $existing = DB::table('tb_role')
                ->where('privilege_id', $privId)
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->value('id');

            $payload = [
                'create' => '1',
                'read' => '1',
                'update' => '1',
                'delete' => '1',
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('tb_role')->where('id', $existing)->update($payload);
            } else {
                DB::table('tb_role')->insert(array_merge($payload, [
                    'id' => Uuid::uuid4()->toString(),
                    'privilege_id' => $privId,
                    'menu_id' => $menuId,
                    'created_at' => $now,
                ]));
            }
        }
    }

    /**
     * Nonaktifkan menu lain agar akses KUPTD tetap terbatas.
     *
     * @param string $privId
     * @param int[] $allowed
     */
    private function denyAllOtherMenus($privId, array $allowed)
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        $allowed = array_map('intval', $allowed);
        $now = now();

        $allIds = DB::table('ms_menuadm')
            ->whereNull('deleted_at')
            ->where('publish', '1')
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        foreach (array_diff($allIds, $allowed) as $menuId) {
            $existing = DB::table('tb_role')
                ->where('privilege_id', $privId)
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
            } else {
                DB::table('tb_role')->insert(array_merge($payload, [
                    'id' => Uuid::uuid4()->toString(),
                    'privilege_id' => $privId,
                    'menu_id' => $menuId,
                    'created_at' => $now,
                ]));
            }
        }
    }

    /**
     * @param string $privId
     */
    private function ensureUser($privId)
    {
        $now = now();
        $payload = [
            'name' => self::USER_NAME,
            'email' => self::USER_EMAIL,
            'level' => $privId,
            'laboratory_users' => null,
            'publish' => '1',
            'updated_at' => $now,
        ];

        $existing = DB::table('ms_users')
            ->where('username', self::USERNAME)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            DB::table('ms_users')->where('id', $existing->id)->update($payload);

            return;
        }

        $trashed = DB::table('ms_users')
            ->where('username', self::USERNAME)
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashed) {
            DB::table('ms_users')->where('id', $trashed)->update(array_merge($payload, [
                'password' => Hash::make(self::PASSWORD),
                'deleted_at' => null,
            ]));

            return;
        }

        DB::table('ms_users')->insert(array_merge($payload, [
            'id' => Uuid::uuid4()->toString(),
            'username' => self::USERNAME,
            'password' => Hash::make(self::PASSWORD),
            'created_at' => $now,
        ]));
    }
}
