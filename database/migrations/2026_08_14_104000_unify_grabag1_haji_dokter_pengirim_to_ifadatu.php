<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Puskesmas Grabag 1 Haji: samakan dokter pengirim semua pasien
 * ke dr. Ifadatu Rahmatika (sebelumnya 21 Ifadatu + 16 Fauzan).
 */
class UnifyGrabag1HajiDokterPengirimToIfadatu extends Migration
{
    const HAJI_ID = '6f927d50-29aa-4c4b-93ee-885b59d537d1';
    const HAJI_NAME = 'Puskesmas Grabag 1';
    const DOKTER = 'dr. Ifadatu Rahmatika';
    const PREVIOUS_OTHER = 'dr. Fauzan Abdurrahman';

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

        // Batch 13 Agu sebelumnya Fauzan; batch 12 Agu tetap Ifadatu.
        DB::table('tb_permohonan_uji_klinik_2')
            ->where('id_permohonan_uji_klinik_haji', $hajiId)
            ->whereNull('deleted_at')
            ->whereDate('tglregister_permohonan_uji_klinik', '2026-08-13')
            ->where('nama_dokter_pengirim_permohonan_uji_klinik', self::DOKTER)
            ->update([
                'nama_dokter_pengirim_permohonan_uji_klinik' => self::PREVIOUS_OTHER,
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
