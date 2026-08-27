<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Rapikan menu akun Registrasi (RGSTR) agar hanya tupoksi pendaftaran.
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_24_170000_refine_register_menu_scope.php
 */
class RefineRegisterMenuScope extends Migration
{
    const PRIV_LEVEL = 'RGSTR';

    /**
     * Dashboard, registrasi klinik/pasien, kesmas, haji, label, akun.
     *
     * @var int[]
     */
    private $allowedMenuIds = [
        1,   // Dashboard
        213, // Registrasi Pasien
        118, // Klinik (parent)
        211, // Registrasi (sub Klinik)
        205, // Haji Klinik
        124, // Print Label
        96,  // Permohonan Uji KESMAS
        2,   // Profil
        105, // Edit Password
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

        $this->ensureRoles((string) $privilegeId, $this->allowedMenuIds);
        $this->denyAllOtherMenus((string) $privilegeId, $this->allowedMenuIds);
    }

    public function down()
    {
        // Tidak rollback ke 24 menu lama; set ulang lewat migration sebelumnya bila perlu.
    }

    private function ensureRoles(string $privilegeId, array $menuIds)
    {
        $now = now();
        foreach ($menuIds as $menuId) {
            $existing = DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', (int) $menuId)
                ->whereNull('deleted_at')
                ->value('id');

            $payload = [
                'create' => '1',
                'read' => '1',
                'update' => '1',
                'delete' => '0',
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('tb_role')->where('id', $existing)->update($payload);
            } else {
                DB::table('tb_role')->insert(array_merge($payload, [
                    'id' => Uuid::uuid4()->toString(),
                    'privilege_id' => $privilegeId,
                    'menu_id' => (int) $menuId,
                    'created_at' => $now,
                ]));
            }
        }
    }

    private function denyAllOtherMenus(string $privilegeId, array $allowedMenuIds)
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
            } else {
                DB::table('tb_role')->insert(array_merge($payload, [
                    'id' => Uuid::uuid4()->toString(),
                    'privilege_id' => $privilegeId,
                    'menu_id' => (int) $menuId,
                    'created_at' => $now,
                ]));
            }
        }
    }
}
