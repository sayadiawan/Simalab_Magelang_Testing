<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Perjelas label Validasi Hasil Klinik & Kesmas untuk Kepala UPTD (KUPTD).
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_28_074500_rename_validasi_hasil_klinik_kesmas_menus.php
 */
class RenameValidasiHasilKlinikKesmasMenus extends Migration
{
    const PRIV_LEVEL = 'KUPTD';
    const MENU_KLINIK_ID = 215;
    const MENU_KLINIK_NAME = 'Validasi Hasil Klinik';
    const MENU_KLINIK_LINK = '/elits-permohonan-uji-klinik/verifikasi/lists?status_filter=validasi';

    const MENU_KESMAS_NAME = 'Validasi Hasil Kesmas';
    const MENU_KESMAS_LINK = '/elits-analys?status_filter=validasi';

    public function up()
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        // Rename menu klinik (global label lebih jelas)
        if (DB::table('ms_menuadm')->where('id', self::MENU_KLINIK_ID)->whereNull('deleted_at')->exists()) {
            DB::table('ms_menuadm')->where('id', self::MENU_KLINIK_ID)->update([
                'name' => self::MENU_KLINIK_NAME,
                'icon' => 'fa-check-circle',
                'updated_at' => now(),
            ]);
        }

        $kesmasId = $this->ensureKesmasMenu();

        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $privId = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privId) {
            return;
        }

        foreach ([self::MENU_KLINIK_ID, $kesmasId] as $menuId) {
            if (!$menuId) {
                continue;
            }
            $this->ensureRole((string) $privId, (int) $menuId);
        }
    }

    public function down()
    {
        if (!Schema::hasTable('ms_menuadm')) {
            return;
        }

        DB::table('ms_menuadm')
            ->where('id', self::MENU_KLINIK_ID)
            ->whereNull('deleted_at')
            ->update([
                'name' => 'Validasi Hasil',
                'updated_at' => now(),
            ]);
    }

    /**
     * @return int|null
     */
    private function ensureKesmasMenu()
    {
        $existing = DB::table('ms_menuadm')
            ->where('link', self::MENU_KESMAS_LINK)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            DB::table('ms_menuadm')->where('id', $existing->id)->update([
                'name' => self::MENU_KESMAS_NAME,
                'icon' => 'fa-check-square',
                'publish' => '1',
                'is_elits' => 1,
                'updated_at' => now(),
            ]);

            return (int) $existing->id;
        }

        return (int) DB::table('ms_menuadm')->insertGetId([
            'upmenu' => 0,
            'name' => self::MENU_KESMAS_NAME,
            'icon' => 'fa-check-square',
            'link' => self::MENU_KESMAS_LINK,
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
