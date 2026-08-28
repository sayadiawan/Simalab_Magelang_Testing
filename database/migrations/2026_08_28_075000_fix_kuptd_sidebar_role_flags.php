<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Perbaiki menu sidebar Kepala UPTD (KUPTD).
 *
 * Migration awal sempat menulis flag role sebagai 'on', sementara
 * asside.blade.php hanya menampilkan menu jika read === '1'.
 * Migrasi ini memaksa ulang role menu yang diizinkan ke '1'.
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_28_075000_fix_kuptd_sidebar_role_flags.php
 */
class FixKuptdSidebarRoleFlags extends Migration
{
    const PRIV_LEVEL = 'KUPTD';

    const MENU_VALIDASI_KESMAS_NAME = 'Validasi Hasil Kesmas';
    const MENU_VALIDASI_KESMAS_LINK = '/elits-analys?status_filter=validasi';

    /**
     * Preferensi resolusi menu: id tetap, lalu fallback name/link.
     *
     * @var array
     */
    private $menuSpecs = [
        ['id' => 1, 'name' => 'Dashboard'],
        ['id' => 105, 'name' => 'Edit Password'],
        ['id' => 215, 'name' => 'Validasi Hasil Klinik', 'link' => '/elits-permohonan-uji-klinik/verifikasi/lists?status_filter=validasi'],
        ['id' => 227, 'name' => 'Log Aktivitas Sistem'],
        ['id' => 99, 'name' => 'Laporan'],
        ['id' => 100, 'name' => 'Laporan Harian'],
        ['id' => 102, 'name' => 'Laporan Bulanan'],
        ['id' => 115, 'name' => 'Laporan Tahunan'],
        ['id' => 125, 'name' => 'Laporan Bulanan Tanggal Pengujian'],
        ['id' => 219, 'name' => 'Laporan Tahunan Klinik'],
        ['id' => 223, 'name' => 'Jumlah per Jenis Sampel'],
        ['id' => 218, 'name' => 'Persebaran Data'],
    ];

    public function up()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role') || !Schema::hasTable('ms_menuadm')) {
            return;
        }

        $privId = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privId) {
            return;
        }

        $menuIds = [];
        foreach ($this->menuSpecs as $spec) {
            $id = $this->resolveMenuId($spec);
            if ($id) {
                $menuIds[] = $id;
            }
        }

        $kesmasId = $this->ensureValidasiKesmasMenu();
        if ($kesmasId) {
            $menuIds[] = $kesmasId;
        }

        $menuIds = array_values(array_unique(array_map('intval', $menuIds)));

        foreach ($menuIds as $menuId) {
            $this->ensureRole((string) $privId, $menuId);
        }

        // Normalisasi sisa flag 'on' / 'true' milik KUPTD agar konsisten
        DB::table('tb_role')
            ->where('privilege_id', $privId)
            ->whereNull('deleted_at')
            ->whereIn('read', ['on', 'true', 'ON', 'TRUE'])
            ->update([
                'read' => '1',
                'create' => '1',
                'update' => '1',
                'delete' => '1',
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        // no-op: tidak mengembalikan flag 'on'
    }

    /**
     * @param array $spec
     * @return int|null
     */
    private function resolveMenuId(array $spec)
    {
        if (!empty($spec['id'])) {
            $byId = DB::table('ms_menuadm')
                ->where('id', (int) $spec['id'])
                ->whereNull('deleted_at')
                ->value('id');
            if ($byId) {
                return (int) $byId;
            }
        }

        if (!empty($spec['link'])) {
            $byLink = DB::table('ms_menuadm')
                ->where('link', $spec['link'])
                ->whereNull('deleted_at')
                ->value('id');
            if ($byLink) {
                return (int) $byLink;
            }
        }

        if (!empty($spec['name'])) {
            $byName = DB::table('ms_menuadm')
                ->where('name', $spec['name'])
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->value('id');
            if ($byName) {
                return (int) $byName;
            }
        }

        return null;
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
     * @param int $menuId
     */
    private function ensureRole($privId, $menuId)
    {
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
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('tb_role')->where('id', $existing)->update($payload);

            return;
        }

        DB::table('tb_role')->insert(array_merge($payload, [
            'id' => Uuid::uuid4()->toString(),
            'privilege_id' => $privId,
            'menu_id' => $menuId,
            'created_at' => now(),
        ]));
    }
}
