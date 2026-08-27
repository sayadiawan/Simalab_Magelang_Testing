<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Sesuaikan menu langkah klinik (kasie):
 * - Hapus parent Klinik & submenu Verifikasi dari KSKL
 * - Setiap menu arah ke status_filter yang sesuai
 * - Tambah menu Input Hasil
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_23_130000_fix_kasie_klinik_step_menus.php
 */
class FixKasieKlinikStepMenus extends Migration
{
    const PRIV_KLINIK = 'KSKL';

    /** @var array<int, string> menu_id => status_filter */
    private $stepLinks = [
        214 => 'pengambilan_sample', // Pengambilan Sampel
        216 => 'pemeriksaan',        // Pemeriksaan
        215 => 'validasi',           // Validasi Hasil
        210 => 'verifikasi',         // Verifikasi Klinik
    ];

    public function up()
    {
        if (!Schema::hasTable('ms_menuadm') || !Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $this->updateStepMenuLinks();
        $inputHasilId = $this->ensureInputHasilMenu();

        $privilegeId = DB::table('ms_privilege')
            ->where('level', self::PRIV_KLINIK)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        $allowed = [
            1,   // Dashboard
            2,   // Profil
            105, // Edit Password
            214, // Pengambilan Sampel
            216, // Pemeriksaan
            $inputHasilId, // Input Hasil
            210, // Verifikasi Klinik
            215, // Validasi Hasil
            207, // Verifikasi Dokumen
            227, // Log Aktivitas
        ];

        $this->ensureRoles((string) $privilegeId, $allowed);
        $this->denyMenus((string) $privilegeId, [118, 212]); // Klinik parent + Verifikasi submenu
        $this->denyAllOtherMenus((string) $privilegeId, $allowed);
        $this->fixMenuOrder();
    }

    public function down()
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        foreach ($this->stepLinks as $menuId => $filter) {
            DB::table('ms_menuadm')
                ->where('id', $menuId)
                ->update([
                    'link' => '/elits-permohonan-uji-klinik/verifikasi/lists',
                    'updated_at' => now(),
                ]);
        }

        DB::table('ms_menuadm')
            ->where('link', 'like', '%status_filter=input_hasil%')
            ->update(['publish' => 0, 'updated_at' => now()]);
    }

    private function updateStepMenuLinks()
    {
        $base = '/elits-permohonan-uji-klinik/verifikasi/lists';

        foreach ($this->stepLinks as $menuId => $filter) {
            DB::table('ms_menuadm')
                ->where('id', $menuId)
                ->whereNull('deleted_at')
                ->update([
                    'link' => $base . '?status_filter=' . $filter,
                    'updated_at' => now(),
                ]);
        }

        // Rename Verifikasi Klinik → Verifikasi (lebih jelas di sidebar flat)
        DB::table('ms_menuadm')
            ->where('id', 210)
            ->whereNull('deleted_at')
            ->update(['name' => 'Verifikasi', 'updated_at' => now()]);
    }

    /**
     * @return int
     */
    private function ensureInputHasilMenu()
    {
        $link = '/elits-permohonan-uji-klinik/verifikasi/lists?status_filter=input_hasil';

        $existing = DB::table('ms_menuadm')
            ->where(function ($q) use ($link) {
                $q->where('link', $link)
                    ->orWhere('name', 'Input Hasil');
            })
            ->whereNull('deleted_at')
            ->value('id');

        if ($existing) {
            DB::table('ms_menuadm')->where('id', $existing)->update([
                'name' => 'Input Hasil',
                'link' => $link,
                'icon' => 'ti-pencil-alt',
                'upmenu' => 0,
                'type' => 0,
                'order' => 15,
                'publish' => 1,
                'is_elits' => 1,
                'updated_at' => now(),
            ]);

            return (int) $existing;
        }

        return (int) DB::table('ms_menuadm')->insertGetId([
            'upmenu' => 0,
            'type' => 0,
            'name' => 'Input Hasil',
            'icon' => 'ti-pencil-alt',
            'link' => $link,
            'order' => 15,
            'publish' => 1,
            'is_elits' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
            $menuId = (int) $menuId;
            if ($menuId <= 0) {
                continue;
            }

            $existing = DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', $menuId)
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
                continue;
            }

            DB::table('tb_role')->insert([
                'id' => Uuid::uuid4()->toString(),
                'privilege_id' => $privilegeId,
                'menu_id' => $menuId,
                'create' => '1',
                'read' => '1',
                'update' => '1',
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
    private function denyMenus($privilegeId, array $menuIds)
    {
        foreach ($menuIds as $menuId) {
            DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->update([
                    'read' => '0',
                    'create' => '0',
                    'update' => '0',
                    'delete' => '0',
                    'updated_at' => now(),
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
     * Dashboard selalu paling atas, lalu menu langkah klinik.
     *
     * @return void
     */
    private function fixMenuOrder()
    {
        $orders = [
            1 => 1,    // Dashboard
            214 => 14, // Pengambilan Sampel
            216 => 15, // Pemeriksaan
            230 => 16, // Input Hasil
            210 => 17, // Verifikasi
            215 => 18, // Validasi Hasil
            2 => 19,   // Profil
        ];

        foreach ($orders as $menuId => $order) {
            DB::table('ms_menuadm')
                ->where('id', $menuId)
                ->whereNull('deleted_at')
                ->update(['order' => $order, 'updated_at' => now()]);
        }
    }
}
