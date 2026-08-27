<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Persebaran Data untuk Kasie Lab Klinik (KSKL).
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_24_110000_add_kasie_klinik_persebaran_menu.php
 */
class AddKasieKlinikPersebaranMenu extends Migration
{
    const PRIV_KLINIK = 'KSKL';
    const MENU_PERSEBARAN = 218;

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

        $existing = DB::table('tb_role')
            ->where('privilege_id', $privilegeId)
            ->where('menu_id', self::MENU_PERSEBARAN)
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
        } else {
            DB::table('tb_role')->insert(array_merge($payload, [
                'id' => Uuid::uuid4()->toString(),
                'privilege_id' => $privilegeId,
                'menu_id' => self::MENU_PERSEBARAN,
                'created_at' => now(),
            ]));
        }
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

        if ($privilegeId) {
            DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', self::MENU_PERSEBARAN)
                ->whereNull('deleted_at')
                ->update(['read' => '0', 'create' => '0', 'update' => '0', 'updated_at' => now()]);
        }
    }
}
