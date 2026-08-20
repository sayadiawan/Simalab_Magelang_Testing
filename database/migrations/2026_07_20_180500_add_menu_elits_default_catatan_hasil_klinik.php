<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Menu Default Catatan Hasil Klinik + hak akses + seed value lokal.
 *
 * Menyalin kondisi lokal saat ini:
 * - Menu di Master Data (upmenu=16, order=19)
 * - Semua privilege: create/read/update/delete = 1
 * - Seed default catatan e-GFR (CKD-EPI) stadium GFR
 *
 * Link: /elits-default-catatan-hasil-klinik
 */
class AddMenuElitsDefaultCatatanHasilKlinik extends Migration
{
    const MENU_NAME = 'Default Catatan Hasil Klinik';
    const MENU_LINK = '/elits-default-catatan-hasil-klinik';
    const PARENT_MASTER_DATA = 16;
    const MENU_ORDER = 19;

    /** ID baris seed (sama seperti lokal) */
    const SEED_ID = '06e19fdb-51a4-4bcf-b1b0-d226f2e3014c';

    /** parameter_satuan_klinik: e-GFR (CKD-EPI) */
    const SEED_PARAMETER_ID = '1ad22e74-1371-446a-a8f3-7c70570f57c8';

    public function up()
    {
        $menuId = $this->ensureMenu();
        if ($menuId) {
            $this->ensureRoles($menuId);
        }

        $this->seedDefaultCatatan();
    }

    public function down()
    {
        if (Schema::hasTable('ms_default_catatan_hasil_klinik')) {
            DB::table('ms_default_catatan_hasil_klinik')
                ->where('id_default_catatan_hasil_klinik', self::SEED_ID)
                ->delete();
        }

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
                    'upmenu' => self::PARENT_MASTER_DATA,
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
                    'upmenu' => self::PARENT_MASTER_DATA,
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
            'upmenu' => self::PARENT_MASTER_DATA,
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
     * Semua privilege: CRUD = 1 (sesuai lokal).
     *
     * @param int $menuId
     * @return void
     */
    private function ensureRoles($menuId)
    {
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
                'deleted_at' => null,
            ]);
        }
    }

    /**
     * Seed value lokal: Stadium GFR untuk parameter e-GFR (CKD-EPI).
     *
     * @return void
     */
    private function seedDefaultCatatan()
    {
        if (!Schema::hasTable('ms_default_catatan_hasil_klinik')) {
            return;
        }

        // Parameter harus ada di environment tujuan
        $parameterExists = DB::table('ms_parameter_satuan_klinik')
            ->where('id_parameter_satuan_klinik', self::SEED_PARAMETER_ID)
            ->whereNull('deleted_at')
            ->exists();

        if (!$parameterExists) {
            return;
        }

        $catatan = '<p>Stadium GFR :<br />GFR 1 : &ge; 90<br />GFR 2 : 60-89<br />GFR 3 a : 45-59<br />GFR 3 b : 30-44<br />GFR 4 : 15-29<br />GFR 5 : &lt; 15</p>';

        $now = now()->format('Y-m-d H:i:s');

        $byId = DB::table('ms_default_catatan_hasil_klinik')
            ->where('id_default_catatan_hasil_klinik', self::SEED_ID)
            ->first();

        if ($byId) {
            DB::table('ms_default_catatan_hasil_klinik')
                ->where('id_default_catatan_hasil_klinik', self::SEED_ID)
                ->update([
                    'parameter_satuan_klinik' => self::SEED_PARAMETER_ID,
                    'catatan_default' => $catatan,
                    'is_active' => 1,
                    'sort_order' => 1,
                    'deleted_at' => null,
                    'updated_at' => $now,
                ]);
            return;
        }

        $byParam = DB::table('ms_default_catatan_hasil_klinik')
            ->where('parameter_satuan_klinik', self::SEED_PARAMETER_ID)
            ->whereNull('deleted_at')
            ->first();

        if ($byParam) {
            DB::table('ms_default_catatan_hasil_klinik')
                ->where('id_default_catatan_hasil_klinik', $byParam->id_default_catatan_hasil_klinik)
                ->update([
                    'catatan_default' => $catatan,
                    'is_active' => 1,
                    'sort_order' => 1,
                    'updated_at' => $now,
                ]);
            return;
        }

        DB::table('ms_default_catatan_hasil_klinik')->insert([
            'id_default_catatan_hasil_klinik' => self::SEED_ID,
            'parameter_satuan_klinik' => self::SEED_PARAMETER_ID,
            'catatan_default' => $catatan,
            'is_active' => 1,
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);
    }
}
