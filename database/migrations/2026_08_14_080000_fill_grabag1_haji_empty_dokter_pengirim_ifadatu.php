<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Puskesmas Grabag 1 Haji: 21 pasien batch 12 Agu punya dokter pengirim kosong
 * (session wizard kosong saat storePasien). Isi ke dr. Ifadatu Rahmatika.
 *
 * Batch 16 pasien (13 Agu) yang sudah berisi dr. Fauzan Abdurrahman tidak diubah.
 */
class FillGrabag1HajiEmptyDokterPengirimIfadatu extends Migration
{
    const HAJI_ID = '6f927d50-29aa-4c4b-93ee-885b59d537d1';
    const HAJI_NAME = 'Puskesmas Grabag 1';
    const DOKTER = 'dr. Ifadatu Rahmatika';

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
                    ->orWhere('nama_dokter_pengirim_permohonan_uji_klinik', '');
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

        // Kembalikan hanya yang masih berisi Ifadatu (hasil migrasi ini).
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
