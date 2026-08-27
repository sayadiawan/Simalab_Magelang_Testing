<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hapus menu Registrasi Klinik dari privilege Bendahara (BNDR).
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_24_160000_remove_registrasi_klinik_from_bendahara.php
 */
class RemoveRegistrasiKlinikFromBendahara extends Migration
{
    const PRIV_LEVEL = 'BNDR';
    const MENU_REGISTRASI_KLINIK = 209;

    public function up()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $privilegeId = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->where('menu_id', self::MENU_REGISTRASI_KLINIK)
            ->whereNull('deleted_at')
            ->update([
                'create' => '0',
                'read' => '0',
                'update' => '0',
                'delete' => '0',
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role')) {
            return;
        }

        $privilegeId = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->where('menu_id', self::MENU_REGISTRASI_KLINIK)
            ->whereNull('deleted_at')
            ->update([
                'create' => '1',
                'read' => '1',
                'update' => '1',
                'delete' => '0',
                'updated_at' => now(),
            ]);
    }
}
