<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Semua tahap verifikasi klinik (step 1–7) sejak 2026-08-11:
 * sesuaikan DATE(start_date/stop_date) dengan DATE(created_at) permohonan. Jam tetap.
 */
class AlignAllKlinikVerificationStepsFromAug11 extends Migration
{
    const FROM_DATE = '2026-08-11 00:00:00';

    /** @var int[] */
    const STEPS = [1, 2, 3, 4, 5, 6, 7];

    public function up(): void
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')
            || !Schema::hasTable('tb_verification_activity_samples')) {
            return;
        }

        $this->alignVerificationSteps();
        $this->alignTglPengujianPermohonan();
    }

    public function down(): void
    {
        // Tidak di-rollback otomatis.
    }

    private function alignVerificationSteps(): void
    {
        $rows = DB::table('tb_verification_activity_samples as vas')
            ->join('tb_permohonan_uji_klinik_2 as p', 'p.id_permohonan_uji_klinik', '=', 'vas.is_klinik')
            ->whereNotNull('vas.is_klinik')
            ->whereNull('p.deleted_at')
            ->whereIn('vas.id_verification_activity', self::STEPS)
            ->where('p.created_at', '>=', self::FROM_DATE)
            ->whereNotNull('vas.start_date')
            ->whereNotNull('p.created_at')
            ->whereRaw('DATE(vas.start_date) <> DATE(p.created_at)')
            ->select([
                'vas.id',
                'vas.start_date',
                'vas.stop_date',
                'p.created_at',
            ])
            ->get();

        foreach ($rows as $row) {
            $baseDate = Carbon::parse($row->created_at)->format('Y-m-d');
            $newStart = $baseDate . ' ' . Carbon::parse($row->start_date)->format('H:i:s');

            $update = [
                'start_date' => $newStart,
                'updated_at' => now(),
            ];

            if (!empty($row->stop_date)) {
                $update['stop_date'] = $baseDate . ' ' . Carbon::parse($row->stop_date)->format('H:i:s');
            }

            DB::table('tb_verification_activity_samples')
                ->where('id', $row->id)
                ->update($update);
        }
    }

    private function alignTglPengujianPermohonan(): void
    {
        if (!Schema::hasColumn('tb_permohonan_uji_klinik_2', 'tglpengujian_permohonan_uji_klinik')) {
            return;
        }

        $rows = DB::table('tb_permohonan_uji_klinik_2')
            ->whereNull('deleted_at')
            ->where('created_at', '>=', self::FROM_DATE)
            ->whereNotNull('tglpengujian_permohonan_uji_klinik')
            ->whereNotNull('created_at')
            ->whereRaw('DATE(tglpengujian_permohonan_uji_klinik) <> DATE(created_at)')
            ->select(['id_permohonan_uji_klinik', 'tglpengujian_permohonan_uji_klinik', 'created_at'])
            ->get();

        foreach ($rows as $row) {
            $baseDate = Carbon::parse($row->created_at)->format('Y-m-d');
            $newTgl = $baseDate . ' ' . Carbon::parse($row->tglpengujian_permohonan_uji_klinik)->format('H:i:s');

            DB::table('tb_permohonan_uji_klinik_2')
                ->where('id_permohonan_uji_klinik', $row->id_permohonan_uji_klinik)
                ->update([
                    'tglpengujian_permohonan_uji_klinik' => $newTgl,
                    'updated_at' => now(),
                ]);
        }
    }
}
