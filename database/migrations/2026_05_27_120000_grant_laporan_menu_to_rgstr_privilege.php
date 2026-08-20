<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Aktifkan menu Laporan (harian, bulanan, tahunan) untuk privilege Registrasi (RGSTR).
 */
class GrantLaporanMenuToRgstrPrivilege extends Migration
{
    /** @var int[] Menu laporan yang boleh diakses RGSTR */
    private $menuIds = [
        99,  // Laporan (parent)
        100, // Laporan Harian
        102, // Laporan Bulanan
        115, // Laporan Tahunan
    ];

    /** @var int[] Submenu laporan yang tidak ditampilkan untuk RGSTR */
    private $hiddenMenuIds = [
        219, // Laporan Tahunan Klinik
        220, // Buku Register Hasil Klinis
    ];

    public function up()
    {
        $privilegeId = DB::table('ms_privilege')
            ->where('level', 'RGSTR')
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->whereIn('menu_id', $this->menuIds)
            ->whereNull('deleted_at')
            ->update([
                'read' => '1',
                'create' => '1',
                'update' => '1',
                'delete' => '1',
                'updated_at' => now(),
            ]);

        DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->whereIn('menu_id', $this->hiddenMenuIds)
            ->whereNull('deleted_at')
            ->update([
                'read' => '0',
                'create' => '0',
                'update' => '0',
                'delete' => '0',
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        $privilegeId = DB::table('ms_privilege')
            ->where('level', 'RGSTR')
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->whereIn('menu_id', $this->menuIds)
            ->whereNull('deleted_at')
            ->update([
                'read' => '0',
                'create' => '0',
                'update' => '0',
                'delete' => '0',
                'updated_at' => now(),
            ]);

        // Kembalikan submenu klinik ke default sebelumnya (aktif)
        DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->whereIn('menu_id', $this->hiddenMenuIds)
            ->whereNull('deleted_at')
            ->update([
                'read' => '1',
                'create' => '1',
                'update' => '1',
                'delete' => '1',
                'updated_at' => now(),
            ]);
    }
}
