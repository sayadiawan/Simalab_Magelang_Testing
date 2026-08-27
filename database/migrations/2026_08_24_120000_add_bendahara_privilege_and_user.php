<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

/**
 * Privilege BNDR (Bendahara Penerimaan) — akses pembayaran, laporan keuangan klinik.
 *
 *   php7.4 artisan migrate --path=database/migrations/2026_08_24_120000_add_bendahara_privilege_and_user.php
 */
class AddBendaharaPrivilegeAndUser extends Migration
{
    const PRIV_LEVEL  = 'BNDR';
    const PRIV_NAME   = 'Bendahara Penerimaan';
    const PRIV_DESC   = 'Konfirmasi pembayaran, laporan keuangan klinik & kesmas';
    const USERNAME    = 'bendahara';
    const PASSWORD    = 'elits';
    const LAB_KLINIK  = 'bbed2259-2826-4711-b0fc-abdad5aace22'; // KLI

    /**
     * Menu yang boleh diakses Bendahara:
     * - Dashboard, Profil, Edit Password
     * - Semua Pemeriksaan (untuk lihat & konfirmasi status bayar)
     * - Registrasi Klinik (pagination-registrasi: bisa konfirmasi dari sini)
     * - Pendapatan Klinik, Pendapatan Non-Klinik
     * - Laporan lengkap keuangan
     * - Log Aktivitas, Verifikasi Dokumen
     */
    private $menuIds = [
        1,   // Dashboard
        2,   // Profil
        105, // Edit Password
        232, // Semua Pemeriksaan
        209, // Registrasi Klinik
        213, // Registrasi Pasien
        118, // Klinik (parent)
        211, // Registrasi (sub Klinik)
        120, // Pendapatan Non-Klinik
        121, // Pendapatan Klinik
        99,  // Laporan (parent)
        100, // Laporan Harian
        102, // Laporan Bulanan
        115, // Laporan Tahunan
        125, // Laporan Bulanan Tanggal Pengujian
        219, // Laporan Tahunan Klinik
        220, // Buku Register Hasil Klinis
        221, // Buku Monitoring Sampling
        223, // Jumlah per Jenis Sampel
        227, // Log Aktivitas Sistem
        207, // Verifikasi Dokumen
    ];

    public function up()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('tb_role') || !Schema::hasTable('ms_users')) {
            return;
        }

        $privId = $this->ensurePrivilege();
        $this->ensureRoles($privId, $this->menuIds);
        $this->denyAllOtherMenus($privId, $this->menuIds);
        $this->ensureUser($privId);
    }

    public function down()
    {
        $privId = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        if ($privId) {
            DB::table('tb_role')
                ->where('privilege_id', $privId)
                ->whereNull('deleted_at')
                ->update(['read' => '0', 'create' => '0', 'update' => '0', 'delete' => '0', 'updated_at' => now()]);
        }

        DB::table('ms_users')
            ->where('username', self::USERNAME)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
    }

    // -------------------------------------------------------------------------

    private function ensurePrivilege()
    {
        $existing = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            DB::table('ms_privilege')->where('id', $existing->id)->update([
                'name' => self::PRIV_NAME,
                'description' => self::PRIV_DESC,
                'is_elits' => 1,
                'updated_at' => now(),
            ]);
            return (string) $existing->id;
        }

        $trashed = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashed) {
            DB::table('ms_privilege')->where('id', $trashed)->update([
                'name' => self::PRIV_NAME,
                'description' => self::PRIV_DESC,
                'is_elits' => 1,
                'deleted_at' => null,
                'updated_at' => now(),
            ]);
            return (string) $trashed;
        }

        $id = Uuid::uuid4()->toString();
        DB::table('ms_privilege')->insert([
            'id' => $id,
            'level' => self::PRIV_LEVEL,
            'name' => self::PRIV_NAME,
            'description' => self::PRIV_DESC,
            'is_elits' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return $id;
    }

    private function ensureRoles(string $privId, array $menuIds)
    {
        $now = now();
        foreach ($menuIds as $menuId) {
            $menuId = (int) $menuId;
            if ($menuId <= 0) continue;

            $existing = DB::table('tb_role')
                ->where('privilege_id', $privId)
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->value('id');

            $payload = ['create' => '1', 'read' => '1', 'update' => '1', 'delete' => '0', 'updated_at' => $now];

            if ($existing) {
                DB::table('tb_role')->where('id', $existing)->update($payload);
            } else {
                DB::table('tb_role')->insert(array_merge($payload, [
                    'id' => Uuid::uuid4()->toString(),
                    'privilege_id' => $privId,
                    'menu_id' => $menuId,
                    'created_at' => $now,
                ]));
            }
        }
    }

    private function denyAllOtherMenus(string $privId, array $allowed)
    {
        $allowed = array_map('intval', $allowed);
        $now = now();

        $allIds = DB::table('ms_menuadm')->whereNull('deleted_at')->where('publish', 1)
            ->pluck('id')->map(fn($id) => (int)$id)->all();

        foreach (array_diff($allIds, $allowed) as $menuId) {
            $existing = DB::table('tb_role')
                ->where('privilege_id', $privId)
                ->where('menu_id', $menuId)
                ->whereNull('deleted_at')
                ->value('id');

            $payload = ['create' => '0', 'read' => '0', 'update' => '0', 'delete' => '0', 'updated_at' => $now];

            if ($existing) {
                DB::table('tb_role')->where('id', $existing)->update($payload);
            } else {
                DB::table('tb_role')->insert(array_merge($payload, [
                    'id' => Uuid::uuid4()->toString(),
                    'privilege_id' => $privId,
                    'menu_id' => $menuId,
                    'created_at' => $now,
                ]));
            }
        }
    }

    private function ensureUser(string $privId)
    {
        $petugasId = $this->ensurePetugas();

        $existing = DB::table('ms_users')
            ->where('username', self::USERNAME)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            DB::table('ms_users')->where('id', $existing->id)->update([
                'level' => $privId,
                'laboratory_users' => self::LAB_KLINIK,
                'updated_at' => now(),
            ]);
            return;
        }

        DB::table('ms_users')->insert([
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Bendahara Penerimaan',
            'username' => self::USERNAME,
            'email' => 'bendahara@labkes.local',
            'password' => Hash::make(self::PASSWORD),
            'level' => $privId,
            'laboratory_users' => self::LAB_KLINIK,
            'id_petugas' => $petugasId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensurePetugas()
    {
        $existing = DB::table('ms_petugas')
            ->where('nama', 'Bendahara Penerimaan')
            ->value('id_petugas');

        if ($existing) return $existing;

        $id = Uuid::uuid4()->toString();
        DB::table('ms_petugas')->insert([
            'id_petugas' => $id,
            'nama' => 'Bendahara Penerimaan',
            'is_kepala_lab' => 0,
        ]);
        return $id;
    }
}
