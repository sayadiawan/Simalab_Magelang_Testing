<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Akun demo Kasie / Kepala Lab (KLAB) — Klinik & Kesmas
 *
 * Jalankan:
 *   php7.4 artisan migrate --path=database/migrations/2026_08_22_220000_add_kasie_lab_users.php
 */
class AddKasieLabUsers extends Migration
{
    const PRIV_LEVEL = 'KLAB';

    const LAB_KLINIK = 'bbed2259-2826-4711-b0fc-abdad5aace22';
    const LAB_KIMIA = '3416ca19-6c69-4e5f-a004-ae8275de7644';
    const LAB_MIKRO = 'd3bff0b4-622e-40b0-b10f-efa97a4e1bd5';

    /** @var array<string, array<string, mixed>> */
    private $accounts = [
        'kasie-klinik' => [
            'name' => 'Kasie Lab Klinik',
            'email' => 'kasie-klinik@simlab.local',
            'lab_id' => self::LAB_KLINIK,
            'petugas_name' => 'Kasie Lab Klinik (Demo)',
            'petugas_lab_ids' => [self::LAB_KLINIK],
        ],
        'kasie-kesmas' => [
            'name' => 'Kasie Lab Kesmas',
            'email' => 'kasie-kesmas@simlab.local',
            'lab_id' => self::LAB_KIMIA,
            'petugas_name' => 'Kasie Lab Kesmas (Demo)',
            'petugas_lab_ids' => [self::LAB_KIMIA, self::LAB_MIKRO],
        ],
    ];

    public function up()
    {
        if (!Schema::hasTable('ms_privilege') || !Schema::hasTable('ms_users')) {
            return;
        }

        $privilegeId = DB::table('ms_privilege')
            ->where('level', self::PRIV_LEVEL)
            ->whereNull('deleted_at')
            ->value('id');

        if (!$privilegeId) {
            return;
        }

        foreach ($this->accounts as $username => $config) {
            $this->ensureKasieUser((string) $privilegeId, $username, $config);
        }
    }

    public function down()
    {
        if (!Schema::hasTable('ms_users')) {
            return;
        }

        foreach (array_keys($this->accounts) as $username) {
            $userId = DB::table('ms_users')
                ->where('username', $username)
                ->whereNull('deleted_at')
                ->value('id');

            if ($userId) {
                DB::table('ms_users')
                    ->where('id', $userId)
                    ->update(['deleted_at' => now()]);
            }
        }
    }

    /**
     * @param string $privilegeId
     * @param string $username
     * @param array<string, mixed> $config
     * @return void
     */
    private function ensureKasieUser($privilegeId, $username, array $config)
    {
        $petugasId = $this->ensurePetugas($config);
        $now = now();

        $existing = DB::table('ms_users')
            ->where('username', $username)
            ->whereNull('deleted_at')
            ->value('id');

        $payload = [
            'name' => $config['name'],
            'email' => $config['email'],
            'level' => $privilegeId,
            'laboratory_users' => $config['lab_id'],
            'id_petugas' => $petugasId,
            'publish' => '1',
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('ms_users')->where('id', $existing)->update($payload);

            return;
        }

        $trashed = DB::table('ms_users')
            ->where('username', $username)
            ->whereNotNull('deleted_at')
            ->value('id');

        if ($trashed) {
            DB::table('ms_users')
                ->where('id', $trashed)
                ->update(array_merge($payload, [
                    'password' => bcrypt('elits'),
                    'deleted_at' => null,
                ]));

            return;
        }

        DB::table('ms_users')->insert(array_merge($payload, [
            'id' => Uuid::uuid4()->toString(),
            'username' => $username,
            'password' => bcrypt('elits'),
            'created_at' => $now,
        ]));
    }

    /**
     * @param array<string, mixed> $config
     * @return string|null
     */
    private function ensurePetugas(array $config)
    {
        if (!Schema::hasTable('ms_petugas')) {
            return null;
        }

        $nama = (string) $config['petugas_name'];
        $labJson = json_encode(array_values($config['petugas_lab_ids']));

        $existing = DB::table('ms_petugas')
            ->where('nama', $nama)
            ->value('id_petugas');

        if ($existing) {
            DB::table('ms_petugas')
                ->where('id_petugas', $existing)
                ->update([
                    'is_kepala_lab' => 1,
                    'lab_id' => $labJson,
                ]);

            return (string) $existing;
        }

        $id = Uuid::uuid4()->toString();
        DB::table('ms_petugas')->insert([
            'id_petugas' => $id,
            'nama' => $nama,
            'is_kepala_lab' => 1,
            'lab_id' => $labJson,
        ]);

        return $id;
    }
}
