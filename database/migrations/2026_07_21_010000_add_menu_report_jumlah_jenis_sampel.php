<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Menu Laporan: Rekapan Jumlah per Jenis Sampel (Klinik / Mikro / Kimia)
 * Link: /report-jumlah-jenis-sampel
 */
class AddMenuReportJumlahJenisSampel extends Migration
{
    const MENU_NAME = 'Jumlah per Jenis Sampel';
    const MENU_LINK = '/report-jumlah-jenis-sampel';
    const PARENT_LAPORAN = 99;
    const MENU_ORDER = 11;

    public function up()
    {
        $menuId = $this->ensureMenu();
        if ($menuId) {
            $this->ensureRoles($menuId);
        }
    }

    public function down()
    {
        $menuId = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$menuId) {
            return;
        }

        DB::table('tb_role')
            ->where('menu_id', $menuId)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('ms_menuadm')
            ->where('id', $menuId)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * @return int|null
     */
    private function ensureMenu()
    {
        $existing = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNull('deleted_at')
            ->value('id');

        if ($existing) {
            DB::table('ms_menuadm')
                ->where('id', $existing)
                ->update([
                    'upmenu' => self::PARENT_LAPORAN,
                    'type' => 0,
                    'name' => self::MENU_NAME,
                    'link' => self::MENU_LINK,
                    'order' => self::MENU_ORDER,
                    'publish' => 1,
                    'is_elits' => 1,
                    'updated_at' => now(),
                ]);

            return (int) $existing;
        }

        $trashedId = DB::table('ms_menuadm')
            ->where('link', self::MENU_LINK)
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashedId) {
            DB::table('ms_menuadm')
                ->where('id', $trashedId)
                ->update([
                    'upmenu' => self::PARENT_LAPORAN,
                    'type' => 0,
                    'name' => self::MENU_NAME,
                    'link' => self::MENU_LINK,
                    'order' => self::MENU_ORDER,
                    'publish' => 1,
                    'is_elits' => 1,
                    'deleted_at' => null,
                    'updated_at' => now(),
                ]);

            return (int) $trashedId;
        }

        return (int) DB::table('ms_menuadm')->insertGetId([
            'upmenu' => self::PARENT_LAPORAN,
            'type' => 0,
            'name' => self::MENU_NAME,
            'link' => self::MENU_LINK,
            'order' => self::MENU_ORDER,
            'publish' => 1,
            'is_elits' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param int $menuId
     * @return void
     */
    private function ensureRoles($menuId)
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $privileges = DB::table('ms_privilege')
            ->whereNull('deleted_at')
            ->get(['id', 'level']);

        $now = now();

        foreach ($privileges as $privilege) {
            $existingId = DB::table('tb_role')
                ->where('privilege_id', $privilege->id)
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->value('id');

            if ($existingId) {
                DB::table('tb_role')
                    ->where('id', $existingId)
                    ->update([
                        'create' => '1',
                        'read' => '1',
                        'update' => '1',
                        'delete' => '1',
                        'updated_at' => $now,
                    ]);
                continue;
            }

            $trashed = DB::table('tb_role')
                ->where('privilege_id', $privilege->id)
                ->where('menu_id', $menuId)
                ->whereNotNull('deleted_at')
                ->value('id');

            if ($trashed) {
                DB::table('tb_role')
                    ->where('id', $trashed)
                    ->update([
                        'create' => '1',
                        'read' => '1',
                        'update' => '1',
                        'delete' => '1',
                        'deleted_at' => null,
                        'updated_at' => $now,
                    ]);
                continue;
            }

            DB::table('tb_role')->insert([
                'id' => Uuid::uuid4()->toString(),
                'privilege_id' => $privilege->id,
                'menu_id' => $menuId,
                'create' => '1',
                'read' => '1',
                'update' => '1',
                'delete' => '1',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
