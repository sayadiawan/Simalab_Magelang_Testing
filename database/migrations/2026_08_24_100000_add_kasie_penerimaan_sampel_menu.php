<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Menu Penerimaan Sampel untuk Kasie Lab Klinik (KSKL).
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_24_100000_add_kasie_penerimaan_sampel_menu.php
 */
class AddKasiePenerimaanSampelMenu extends Migration
{
    const PRIV_KLINIK = 'KSKL';
    const MENU_PENERIMAAN = 231;

    public function up()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role') || !Schema::hasTable('ms_menuadm')) {
            return;
        }

        $privilegeId = DB::table('ms_privilege')
            ->where('level', self::PRIV_KLINIK)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        $this->ensureRole((string) $privilegeId, self::MENU_PENERIMAAN);
        $this->fixMenuOrder();
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

        DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->where('menu_id', self::MENU_PENERIMAAN)
            ->whereNull('deleted_at')
            ->update([
                'read' => '0',
                'create' => '0',
                'update' => '0',
                'delete' => '0',
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
     * @return void
     */
    private function fixMenuOrder()
    {
        $orders = [
            1 => 1,
            232 => 13,
            214 => 14, // Pengambilan Sampel (KSKL)
            231 => 15, // Penerimaan Sampel
            216 => 16,
            230 => 17,
            210 => 18,
            215 => 19,
            2 => 20,
        ];

        foreach ($orders as $menuId => $order) {
            DB::table('ms_menuadm')
                ->where('id', $menuId)
                ->whereNull('deleted_at')
                ->update(['order' => $order, 'updated_at' => now()]);
        }
    }
}
