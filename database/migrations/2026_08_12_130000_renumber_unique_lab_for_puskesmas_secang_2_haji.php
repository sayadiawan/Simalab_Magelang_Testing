<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rapikan nomor lab batch Haji Puskesmas Secang 2 agar unik & berurutan.
 *
 * Masalah:
 * - Export daftar hadir sempat menampilkan suffix noregister (jumlah pendaftaran)
 *   sebagai "nomor lab" (mis. 1440), dan beberapa lab Excel masih dobel.
 * - Lab hasil bump tabrakan dengan pasien non-haji menjadi loncat-loncat
 *   (2207, 2257, 2242, …) sehingga terlihat "tidak sama".
 *
 * Strategi:
 * - Beri blok nomor lab kontigu mulai setelah max lab hidup 2026
 * - Urutkan pasien Secang 2 by spesimen lalu created_at
 * - Sinkron nomor_lab_manual + nomer_lab + counter global_nomer_lab_sequence
 */
class RenumberUniqueLabForPuskesmasSecang2Haji extends Migration
{
    const HAJI_ID = 'd7f0bdc8-2b9e-41f1-8e57-bfa95b3c149e';
    const YEAR = 2026;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            return;
        }

        DB::transaction(function () {
            $rows = DB::table('tb_permohonan_uji_klinik_2')
                ->where('id_permohonan_uji_klinik_haji', self::HAJI_ID)
                ->whereNull('deleted_at')
                ->orderByRaw('CAST(nourut_permohonan_uji_klinik AS UNSIGNED) ASC')
                ->orderBy('created_at', 'ASC')
                ->get([
                    'id_permohonan_uji_klinik',
                    'nourut_permohonan_uji_klinik',
                    'nomor_lab_manual',
                    'nomer_lab',
                ]);

            if ($rows->isEmpty()) {
                return;
            }

            $maxLive = (int) DB::table('tb_permohonan_uji_klinik_2')
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereYear('tglregister_permohonan_uji_klinik', self::YEAR)
                        ->orWhere(function ($q2) {
                            $q2->whereNull('tglregister_permohonan_uji_klinik')
                                ->whereYear('created_at', self::YEAR);
                        });
                })
                ->where(function ($q) {
                    $q->whereNull('id_permohonan_uji_klinik_haji')
                        ->orWhere('id_permohonan_uji_klinik_haji', '<>', self::HAJI_ID);
                })
                ->selectRaw(
                    'GREATEST('
                    . 'COALESCE(MAX(CAST(NULLIF(TRIM(nomor_lab_manual), \'\') AS UNSIGNED)), 0),'
                    . 'COALESCE(MAX(CAST(NULLIF(TRIM(nomer_lab), \'\') AS UNSIGNED)), 0)'
                    . ') AS m'
                )
                ->value('m');

            $seqLast = 0;
            if (Schema::hasTable('global_nomer_lab_sequence')) {
                $seqLast = (int) DB::table('global_nomer_lab_sequence')
                    ->where('year', self::YEAR)
                    ->value('last_number');
            }

            $start = max($maxLive, $seqLast, 0) + 1;
            $now = now()->format('Y-m-d H:i:s');
            $lastAssigned = $start - 1;

            foreach ($rows as $index => $row) {
                $lab = $start + (int) $index;
                $lastAssigned = $lab;

                $update = [
                    'nomor_lab_manual' => (string) $lab,
                    'updated_at' => $now,
                ];
                if (Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomer_lab')) {
                    $update['nomer_lab'] = $lab;
                }

                DB::table('tb_permohonan_uji_klinik_2')
                    ->where('id_permohonan_uji_klinik', $row->id_permohonan_uji_klinik)
                    ->update($update);
            }

            if ($lastAssigned >= 1 && Schema::hasTable('global_nomer_lab_sequence')) {
                $seq = DB::table('global_nomer_lab_sequence')
                    ->where('year', self::YEAR)
                    ->first();

                if ($seq && (int) $seq->last_number < $lastAssigned) {
                    DB::table('global_nomer_lab_sequence')
                        ->where('id', $seq->id)
                        ->update([
                            'last_number' => $lastAssigned,
                            'updated_at' => $now,
                        ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nomor lab sebelumnya sudah tidak konsisten; tidak di-rollback otomatis.
    }
}
