<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Menu langkah verifikasi Kesmas untuk Kasie (KSKM) — sesuai lab Kimia/Mikro.
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_23_140000_fix_kasie_kesmas_step_menus.php
 */
class FixKasieKesmasStepMenus extends Migration
{
    const PRIV_KESMAS = 'KSKM';

    /** @var array<int, string> menu_id => status_filter (reuse step menus klinik yang sama URL-nya) */
    private $sharedStepLinks = [
        216 => 'pemeriksaan',
        230 => 'input_hasil',
        210 => 'verifikasi',
        215 => 'validasi',
    ];

    public function up()
    {
        if (!Schema::hasTable('ms_menuadm') || !Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $base = '/elits-permohonan-uji-klinik/verifikasi/lists';
        $penerimaanId = $this->ensurePenerimaanSampelMenu($base);

        foreach ($this->sharedStepLinks as $menuId => $filter) {
            DB::table('ms_menuadm')
                ->where('id', $menuId)
                ->whereNull('deleted_at')
                ->update([
                    'link' => $base . '?status_filter=' . $filter,
                    'updated_at' => now(),
                ]);
        }

        $privilegeId = DB::table('ms_privilege')
            ->where('level', self::PRIV_KESMAS)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        $allowed = [
            1,             // Dashboard
            $penerimaanId, // Penerimaan Sampel
            216,           // Pemeriksaan
            230,           // Input Hasil
            210,           // Verifikasi
            215,           // Validasi Hasil
            2,             // Profil
            105,           // Edit Password
            227,           // Log Aktivitas
        ];

        $this->ensureRoles((string) $privilegeId, $allowed);
        $this->denyMenus((string) $privilegeId, [83, 96, 217, 214]); // Data Analisa lama, Permohonan Uji, Semua Sampel, Pengambilan (klinik)
        $this->denyAllOtherMenus((string) $privilegeId, $allowed);
        $this->fixMenuOrder($penerimaanId);

        // Penerimaan Sampel hanya untuk kesmas — jangan tampil di kasie klinik
        $ksklId = DB::table('ms_privilege')
            ->where('level', 'KSKL')
            ->whereNull('deleted_at')
            ->value('id');
        if ($ksklId) {
            $this->denyMenus((string) $ksklId, [$penerimaanId]);
        }
    }

    public function down()
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        DB::table('ms_menuadm')
            ->where('link', 'like', '%status_filter=penerimaan_sample%')
            ->where('name', 'Penerimaan Sampel')
            ->update(['publish' => 0, 'updated_at' => now()]);
    }

    /**
     * @param string $base
     * @return int
     */
    private function ensurePenerimaanSampelMenu($base)
    {
        $link = $base . '?status_filter=penerimaan_sample';

        $existing = DB::table('ms_menuadm')
            ->where(function ($q) use ($link) {
                $q->where('link', $link)
                    ->orWhere('name', 'Penerimaan Sampel');
            })
            ->whereNull('deleted_at')
            ->value('id');

        if ($existing) {
            DB::table('ms_menuadm')
                ->where('id', $existing)
                ->update([
                    'name' => 'Penerimaan Sampel',
                    'icon' => 'ti-import',
                    'link' => $link,
                    'order' => 14,
                    'publish' => 1,
                    'is_elits' => 1,
                    'updated_at' => now(),
                ]);

            return (int) $existing;
        }

        return (int) DB::table('ms_menuadm')->insertGetId([
            'upmenu' => 0,
            'type' => 0,
            'name' => 'Penerimaan Sampel',
            'icon' => 'ti-import',
            'link' => $link,
            'order' => 14,
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
     * @param int $penerimaanId
     * @return void
     */
    private function fixMenuOrder($penerimaanId)
    {
        $orders = [
            1 => 1,
            $penerimaanId => 14,
            216 => 15,
            230 => 16,
            210 => 17,
            215 => 18,
            2 => 19,
        ];

        foreach ($orders as $menuId => $order) {
            DB::table('ms_menuadm')
                ->where('id', $menuId)
                ->whereNull('deleted_at')
                ->update(['order' => $order, 'updated_at' => now()]);
        }
    }
}
