<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Aktifkan menu Print Label Klinik untuk privilege Registrasi (RGSTR).
 */
class GrantPrintLabelMenuToRgstrPrivilege extends Migration
{
    /** @var int Menu Print Label Klinik */
    private $printLabelMenuId = 124;

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
            ->where('menu_id', $this->printLabelMenuId)
            ->whereNull('deleted_at')
            ->update([
                'read' => '1',
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
            ->where('menu_id', $this->printLabelMenuId)
            ->whereNull('deleted_at')
            ->update([
                'read' => '0',
                'updated_at' => now(),
            ]);
    }
}
