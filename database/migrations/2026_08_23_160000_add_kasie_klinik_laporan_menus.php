<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Menu Laporan khusus klinik untuk Kasie Lab Klinik (KSKL).
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_23_160000_add_kasie_klinik_laporan_menus.php
 */
class AddKasieKlinikLaporanMenus extends Migration
{
    const PRIV_KLINIK = 'KSKL';

    /** Parent + submenu laporan khusus klinik. */
    private $menuLaporanKlinik = [
        99,  // Laporan (parent)
        121, // Pendapatan Klinik
        219, // Laporan Tahunan Klinik
        220, // Buku Register Hasil Klinis
        221, // Buku Monitoring Sampling Dan Penerimaan Sampel
    ];

    public function up()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $privilegeId = DB::table('ms_privilege')
            ->where('level', self::PRIV_KLINIK)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        $this->ensureRoles((string) $privilegeId, $this->menuLaporanKlinik);
    }

    public function down()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $privilegeId = DB::table('ms_privilege')
            ->where('level', self::PRIV_KLINIK)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        foreach ($this->menuLaporanKlinik as $menuId) {
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
}
