<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Tambahkan menu Baku Mutu untuk Kasie Kesmas (Mikro & Kimia) dan Kasie Klinik (Klinik).
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_28_050000_add_baku_mutu_menus_to_kasie_roles.php
 */
class AddBakuMutuMenusToKasieRoles extends Migration
{
    const PRIV_KLINIK = 'KSKL';
    const PRIV_KESMAS = 'KSKM';

    const MENU_BAKU_MUTU_PARENT = 107;
    const MENU_BAKU_MUTU_MIKRO  = 108;
    const MENU_BAKU_MUTU_KIMIA  = 109;
    const MENU_BAKU_MUTU_KLINIK = 116;

    public function up()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role') || !Schema::hasTable('ms_menuadm')) {
            return;
        }

        $kskmId = DB::table('ms_privilege')
            ->where('level', self::PRIV_KESMAS)
            ->whereNull('deleted_at')
            ->value('id');

        $ksklId = DB::table('ms_privilege')
            ->where('level', self::PRIV_KLINIK)
            ->whereNull('deleted_at')
            ->value('id');

        // 1. Kasie Kesmas (KSKM): Menu Baku Mutu, Baku Mutu Lab. Mikro, Baku Mutu Lab. Kimia
        if ($kskmId) {
            $this->ensureRole((string) $kskmId, self::MENU_BAKU_MUTU_PARENT, true);
            $this->ensureRole((string) $kskmId, self::MENU_BAKU_MUTU_MIKRO, true);
            $this->ensureRole((string) $kskmId, self::MENU_BAKU_MUTU_KIMIA, true);
            $this->ensureRole((string) $kskmId, self::MENU_BAKU_MUTU_KLINIK, false);
        }

        // 2. Kasie Klinik (KSKL): Menu Baku Mutu, Baku Mutu Lab. Klinik
        if ($ksklId) {
            $this->ensureRole((string) $ksklId, self::MENU_BAKU_MUTU_PARENT, true);
            $this->ensureRole((string) $ksklId, self::MENU_BAKU_MUTU_KLINIK, true);
            $this->ensureRole((string) $ksklId, self::MENU_BAKU_MUTU_MIKRO, false);
            $this->ensureRole((string) $ksklId, self::MENU_BAKU_MUTU_KIMIA, false);
        }
    }

    public function down()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $kskmId = DB::table('ms_privilege')
            ->where('level', self::PRIV_KESMAS)
            ->whereNull('deleted_at')
            ->value('id');

        $ksklId = DB::table('ms_privilege')
            ->where('level', self::PRIV_KLINIK)
            ->whereNull('deleted_at')
            ->value('id');

        if ($kskmId) {
            $this->ensureRole((string) $kskmId, self::MENU_BAKU_MUTU_PARENT, false);
            $this->ensureRole((string) $kskmId, self::MENU_BAKU_MUTU_MIKRO, false);
            $this->ensureRole((string) $kskmId, self::MENU_BAKU_MUTU_KIMIA, false);
        }

        if ($ksklId) {
            $this->ensureRole((string) $ksklId, self::MENU_BAKU_MUTU_PARENT, false);
            $this->ensureRole((string) $ksklId, self::MENU_BAKU_MUTU_KLINIK, false);
        }
    }

    /**
     * @param string $privilegeId
     * @param int $menuId
     * @param bool $allow
     * @return void
     */
    private function ensureRole($privilegeId, $menuId, $allow = true)
    {
        $existing = DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->where('menu_id', $menuId)
            ->whereNull('deleted_at')
            ->value('id');

        $val = $allow ? '1' : '0';
        $payload = [
            'create' => $val,
            'read' => $val,
            'update' => $val,
            'delete' => $val,
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('tb_role')->where('id', $existing)->update($payload);
            return;
        }

        DB::table('tb_role')->insert(array_merge($payload, [
            'id' => Uuid::uuid4()->toString(),
            'privilege_id' => $privilegeId,
            'menu_id' => $menuId,
            'created_at' => now(),
        ]));
    }
}
