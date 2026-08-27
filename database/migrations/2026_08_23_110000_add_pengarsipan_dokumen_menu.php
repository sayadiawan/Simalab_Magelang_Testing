<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Menu terpisah: Dokumen Arsip Tambahan (/pengarsipan-dokumen)
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_23_110000_add_pengarsipan_dokumen_menu.php
 */
class AddPengarsipanDokumenMenu extends Migration
{
    const PRIV_LEVEL = 'ARSP';
    const MENU_NAME = 'Dokumen Arsip Tambahan';
    const MENU_LINK = '/pengarsipan-dokumen';
    const MENU_ICON = 'ti-folder';
    const MENU_ORDER = 29;

    public function up()
    {
        if (!Schema::hasTable('ms_menuadm') || !Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $privilegeId = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        $menuId = $this->ensureMenu();
        $this->ensureDokumenMenuRole((string) $privilegeId, $menuId);
        $this->resetPengarsipanMenuReadOnly((string) $privilegeId);
        $this->denyNewMenuForOthers($menuId);
    }

    public function down()
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        $menuId = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNull('deleted_at')
            ->value('id');

        if ($menuId && Schema::hasTable('tb_role')) {
            DB::table('tb_role')
                ->where('menu_id', $menuId)
                ->update(['read' => '0', 'create' => '0', 'update' => '0', 'updated_at' => now()]);
        }

        if ($menuId) {
            DB::table('ms_menuadm')
                ->where('id', $menuId)
                ->update(['publish' => 0, 'updated_at' => now()]);
        }
    }

    /**
     * @return int
     */
    private function ensureMenu()
    {
        $payload = [
            'upmenu' => 0,
            'type' => 0,
            'name' => self::MENU_NAME,
            'icon' => self::MENU_ICON,
            'link' => self::MENU_LINK,
            'order' => self::MENU_ORDER,
            'publish' => 1,
            'is_elits' => 1,
            'deleted_at' => null,
            'updated_at' => now(),
        ];

        $existing = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNull('deleted_at')
            ->value('id');

        if ($existing) {
            DB::table('ms_menuadm')->where('id', $existing)->update($payload);

            return (int) $existing;
        }

        $trashed = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashed) {
            DB::table('ms_menuadm')->where('id', $trashed)->update($payload);

            return (int) $trashed;
        }

        return (int) DB::table('ms_menuadm')->insertGetId(array_merge($payload, [
            'created_at' => now(),
        ]));
    }

    /**
     * @param string $privilegeId
     * @param int $menuId
     * @return void
     */
    private function ensureDokumenMenuRole($privilegeId, $menuId)
    {
        $now = now();
        $payload = [
            'create' => '1',
            'read' => '1',
            'update' => '1',
            'delete' => '0',
            'updated_at' => $now,
        ];

        $existing = DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->where('menu_id', $menuId)
            ->whereNull('deleted_at')
            ->value('id');

        if ($existing) {
            DB::table('tb_role')->where('id', $existing)->update($payload);

            return;
        }

        DB::table('tb_role')->insert(array_merge($payload, [
            'id' => Uuid::uuid4()->toString(),
            'privilege_id' => $privilegeId,
            'menu_id' => $menuId,
            'created_at' => $now,
        ]));
    }

    /**
     * Menu Pengarsipan Hasil tetap read-only (cetak hasil saja).
     *
     * @param string $privilegeId
     * @return void
     */
    private function resetPengarsipanMenuReadOnly($privilegeId)
    {
        $pengarsipMenuId = DB::table('ms_menuadm')
            ->where('link', '/pengarsipan')
            ->whereNull('deleted_at')
            ->value('id');

        if (!$pengarsipMenuId) {
            return;
        }

        DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->where('menu_id', $pengarsipMenuId)
            ->whereNull('deleted_at')
            ->update([
                'create' => '0',
                'read' => '1',
                'update' => '0',
                'delete' => '0',
                'updated_at' => now(),
            ]);
    }

    /**
     * Pastikan privilege selain ARSP tidak otomatis dapat menu ini.
     *
     * @param int $menuId
     * @return void
     */
    private function denyNewMenuForOthers($menuId)
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $arspId = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        $privileges = DB::table('ms_privilege')
            ->whereNull('deleted_at')
            ->where('id', '!=', $arspId)
            ->pluck('id');

        $now = now();
        foreach ($privileges as $privilegeId) {
            DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->update([
                    'read' => '0',
                    'create' => '0',
                    'update' => '0',
                    'delete' => '0',
                    'updated_at' => $now,
                ]);
        }
    }
}
