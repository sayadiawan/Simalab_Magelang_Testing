<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Menu "Semua Pemeriksaan" untuk Kasie Klinik & Kesmas — data semua langkah sesuai lab.
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_23_150000_add_semua_pemeriksaan_menu_kasie.php
 */
class AddSemuaPemeriksaanMenuKasie extends Migration
{
    const LINK = '/elits-permohonan-uji-klinik/verifikasi/lists';

    public function up()
    {
        if (!Schema::hasTable('ms_menuadm') || !Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $menuId = $this->ensureSemuaPemeriksaanMenu();

        foreach (['KSKL', 'KSKM'] as $level) {
            $privilegeId = DB::table('ms_privilege')
                ->where('level', $level)
                ->whereNull('deleted_at')
                ->value('id');

            if (!$privilegeId) {
                continue;
            }

            $this->ensureRole((string) $privilegeId, $menuId);
        }

        $this->fixMenuOrder($menuId);
    }

    public function down()
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        DB::table('ms_menuadm')
            ->where('name', 'Semua Pemeriksaan')
            ->where('link', self::LINK)
            ->update(['publish' => 0, 'updated_at' => now()]);
    }

    /**
     * @return int
     */
    private function ensureSemuaPemeriksaanMenu()
    {
        $existing = DB::table('ms_menuadm')
            ->where('name', 'Semua Pemeriksaan')
            ->whereNull('deleted_at')
            ->value('id');

        if ($existing) {
            DB::table('ms_menuadm')
                ->where('id', $existing)
                ->update([
                    'link' => self::LINK,
                    'icon' => 'ti-view-list',
                    'order' => 13,
                    'publish' => 1,
                    'is_elits' => 1,
                    'updated_at' => now(),
                ]);

            return (int) $existing;
        }

        return (int) DB::table('ms_menuadm')->insertGetId([
            'upmenu' => 0,
            'type' => 0,
            'name' => 'Semua Pemeriksaan',
            'icon' => 'ti-view-list',
            'link' => self::LINK,
            'order' => 13,
            'publish' => 1,
            'is_elits' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param string $privilegeId
     * @param int $menuId
     * @return void
     */
    private function ensureRole($privilegeId, $menuId)
    {
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
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('tb_role')->where('id', $existing)->update($payload);
            return;
        }

        DB::table('tb_role')->insert([
            'id' => Uuid::uuid4()->toString(),
            'privilege_id' => $privilegeId,
            'menu_id' => $menuId,
            'create' => '1',
            'read' => '1',
            'update' => '1',
            'delete' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param int $semuaId
     * @return void
     */
    private function fixMenuOrder($semuaId)
    {
        $orders = [
            1 => 1,
            $semuaId => 13,
            214 => 14,
            231 => 14,
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
