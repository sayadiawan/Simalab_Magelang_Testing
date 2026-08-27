<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Rapikan menu Bendahara agar sesuai kebutuhan operasional pembayaran.
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_24_123000_refine_bendahara_menu_scope.php
 */
class RefineBendaharaMenuScope extends Migration
{
    const PRIV_LEVEL = 'BNDR';

    /**
     * Dashboard, pembayaran, tarif/paket layanan, laporan keuangan, akun.
     *
     * @var int[]
     */
    private $allowedMenuIds = [
        1,   // Dashboard
        232, // Semua Pemeriksaan
        209, // Registrasi Klinik
        16,  // Master Data
        95,  // Data Paket
        113, // Data Parameter Paket Klinik
        201, // Data Parameter Paket Extra
        99,  // Laporan
        120, // Pendapatan Non-Klinik
        121, // Pendapatan Klinik
        2,   // Profil
        227, // Log Aktivitas Sistem
        105, // Edit Password
        207, // Verifikasi Dokumen
    ];

    public function up()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role') || !Schema::hasTable('ms_menuadm')) {
            return;
        }

        $privilegeId = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        $this->ensureRoles((string) $privilegeId, $this->allowedMenuIds);
        $this->denyAllOtherMenus((string) $privilegeId, $this->allowedMenuIds);
        $this->fixMenuOrder();
    }

    public function down()
    {
        // Tidak perlu rollback spesifik; migration sebelumnya sudah men-setup role BNDR.
    }

    private function ensureRoles(string $privilegeId, array $menuIds)
    {
        $now = now();
        foreach ($menuIds as $menuId) {
            $existing = DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', (int) $menuId)
                ->whereNull('deleted_at')
                ->value('id');

            $payload = [
                'create' => '1',
                'read' => '1',
                'update' => '1',
                'delete' => '0',
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('tb_role')->where('id', $existing)->update($payload);
            } else {
                DB::table('tb_role')->insert(array_merge($payload, [
                    'id' => Uuid::uuid4()->toString(),
                    'privilege_id' => $privilegeId,
                    'menu_id' => (int) $menuId,
                    'created_at' => $now,
                ]));
            }
        }
    }

    private function denyAllOtherMenus(string $privilegeId, array $allowedMenuIds)
    {
        $allowedMenuIds = array_map('intval', array_unique($allowedMenuIds));
        $allMenuIds = DB::table('ms_menuadm')
            ->whereNull('deleted_at')
            ->where('publish', 1)
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        $denyMenuIds = array_diff($allMenuIds, $allowedMenuIds);
        $now = now();

        foreach ($denyMenuIds as $menuId) {
            $existing = DB::table('tb_role')
                ->where('privilege_id', $privilegeId)
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->value('id');

            $payload = [
                'create' => '0',
                'read' => '0',
                'update' => '0',
                'delete' => '0',
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('tb_role')->where('id', $existing)->update($payload);
            } else {
                DB::table('tb_role')->insert(array_merge($payload, [
                    'id' => Uuid::uuid4()->toString(),
                    'privilege_id' => $privilegeId,
                    'menu_id' => (int) $menuId,
                    'created_at' => $now,
                ]));
            }
        }
    }

    private function fixMenuOrder()
    {
        $orders = [
            1 => 1,
            232 => 13,
            209 => 14,
            16 => 15,
            2 => 19,
            99 => 26,
            227 => 27,
            105 => 28,
            207 => 31,
        ];

        foreach ($orders as $menuId => $order) {
            DB::table('ms_menuadm')
                ->where('id', $menuId)
                ->whereNull('deleted_at')
                ->update([
                    'order' => $order,
                    'updated_at' => now(),
                ]);
        }
    }
}
