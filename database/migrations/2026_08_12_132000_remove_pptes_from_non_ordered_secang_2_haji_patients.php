<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PP Tes (Tes Kehamilan) hanya diorder untuk sebagian pasien Secang 2,
 * bukan semua 43 pasien.
 *
 * Migrasi normalize sebelumnya menambahkan PP Tes ke semua pasien.
 * Pasien yang memang order PP Tes sudah punya baris paket sebelum normalize
 * (created_at < 2026-08-12 13:15:00) — yaitu 4 pasien:
 * ALFIYAH, MASHITOH LAILATUL FITRIYAH, RUKANAH, NUR HIDAYAH.
 *
 * Migration ini menghapus PP Tes dari 39 pasien lainnya dan recalc total.
 */
class RemovePptesFromNonOrderedSecang2HajiPatients extends Migration
{
    const HAJI_ID = 'd7f0bdc8-2b9e-41f1-8e57-bfa95b3c149e';
    const PP_TES_PAKET_NAME = 'PP Tes (Tes Kehamilan)';

    /** Baris PP Tes dari normalize migration (bukan order asli pasien). */
    const NORMALIZE_CUTOFF = '2026-08-12 13:15:00';

    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')
            || !Schema::hasTable('tb_permohonan_uji_paket_klinik')
            || !Schema::hasTable('tb_permohonan_uji_parameter_klinik')
            || !Schema::hasTable('ms_parameter_paket_klinik')
        ) {
            return;
        }

        $hajiExists = DB::table('tb_permohonan_uji_klinik_haji')
            ->where('id_permohonan_uji_klinik_haji', self::HAJI_ID)
            ->whereNull('deleted_at')
            ->exists();

        if (!$hajiExists) {
            return;
        }

        $ppTesPaketId = DB::table('ms_parameter_paket_klinik')
            ->where('name_parameter_paket_klinik', self::PP_TES_PAKET_NAME)
            ->whereNull('deleted_at')
            ->value('id_parameter_paket_klinik');

        if (!$ppTesPaketId) {
            return;
        }

        DB::transaction(function () use ($ppTesPaketId) {
            $now = now();

            $toRemove = DB::table('tb_permohonan_uji_paket_klinik as pk')
                ->join('tb_permohonan_uji_klinik_2 as p', 'p.id_permohonan_uji_klinik', '=', 'pk.permohonan_uji_klinik')
                ->where('p.id_permohonan_uji_klinik_haji', self::HAJI_ID)
                ->whereNull('p.deleted_at')
                ->whereNull('pk.deleted_at')
                ->where('pk.parameter_paket_klinik', $ppTesPaketId)
                ->where('pk.created_at', '>=', self::NORMALIZE_CUTOFF)
                ->pluck('pk.id_permohonan_uji_paket_klinik')
                ->all();

            if (empty($toRemove)) {
                return;
            }

            DB::table('tb_permohonan_uji_parameter_klinik')
                ->whereIn('permohonan_uji_paket_klinik', $toRemove)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);

            $touchedPermohonan = DB::table('tb_permohonan_uji_paket_klinik')
                ->whereIn('id_permohonan_uji_paket_klinik', $toRemove)
                ->whereNull('deleted_at')
                ->pluck('permohonan_uji_klinik')
                ->unique()
                ->values()
                ->all();

            DB::table('tb_permohonan_uji_paket_klinik')
                ->whereIn('id_permohonan_uji_paket_klinik', $toRemove)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);

            foreach ($touchedPermohonan as $permohonanId) {
                $total = (int) DB::table('tb_permohonan_uji_paket_klinik')
                    ->where('permohonan_uji_klinik', $permohonanId)
                    ->whereNull('deleted_at')
                    ->sum('harga_permohonan_uji_paket_klinik');

                DB::table('tb_permohonan_uji_klinik_2')
                    ->where('id_permohonan_uji_klinik', $permohonanId)
                    ->update([
                        'total_harga_permohonan_uji_klinik' => $total,
                        'updated_at' => $now,
                    ]);
            }
        });
    }

    public function down()
    {
        // Data klinis — tidak di-rollback otomatis.
    }
}
