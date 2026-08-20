<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Puskesmas Secang 2 Haji: samakan dokter pengirim semua pasien
 * ke dr. Rizal Hamdan Aziz (42 kosong + 1 sudah terisi).
 */
class FillSecang2HajiEmptyDokterPengirimRizal extends Migration
{
    const HAJI_ID = 'd7f0bdc8-2b9e-41f1-8e57-bfa95b3c149e';
    const HAJI_NAME = 'Puskesmas Secang 2';
    const DOKTER = 'dr. Rizal Hamdan Aziz';

    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            return;
        }

        $hajiId = $this->resolveHajiId();
        if ($hajiId === null) {
            return;
        }

        DB::table('tb_permohonan_uji_klinik_2')
            ->where('id_permohonan_uji_klinik_haji', $hajiId)
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('nama_dokter_pengirim_permohonan_uji_klinik')
                    ->orWhere('nama_dokter_pengirim_permohonan_uji_klinik', '')
                    ->orWhere('nama_dokter_pengirim_permohonan_uji_klinik', '!=', self::DOKTER);
            })
            ->update([
                'nama_dokter_pengirim_permohonan_uji_klinik' => self::DOKTER,
                'doctor_type' => 'rujukan',
                'updated_at' => now(),
            ]);
    }

    public function down()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            return;
        }

        $hajiId = $this->resolveHajiId();
        if ($hajiId === null) {
            return;
        }

        // Kembalikan ke kosong (sebelum migrasi: 42 null + 1 Rizal).
        DB::table('tb_permohonan_uji_klinik_2')
            ->where('id_permohonan_uji_klinik_haji', $hajiId)
            ->whereNull('deleted_at')
            ->where('nama_dokter_pengirim_permohonan_uji_klinik', self::DOKTER)
            ->update([
                'nama_dokter_pengirim_permohonan_uji_klinik' => null,
                'updated_at' => now(),
            ]);
    }

    /**
     * @return string|null
     */
    private function resolveHajiId()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_haji')) {
            return self::HAJI_ID;
        }

        $byId = DB::table('tb_permohonan_uji_klinik_haji')
            ->where('id_permohonan_uji_klinik_haji', self::HAJI_ID)
            ->whereNull('deleted_at')
            ->value('id_permohonan_uji_klinik_haji');

        if ($byId) {
            return (string) $byId;
        }

        $byName = DB::table('tb_permohonan_uji_klinik_haji')
            ->whereNull('deleted_at')
            ->where('nama_haji', self::HAJI_NAME)
            ->orderByDesc('created_at')
            ->value('id_permohonan_uji_klinik_haji');

        return $byName ? (string) $byName : null;
    }
}
