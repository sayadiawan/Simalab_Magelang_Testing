<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sesuaikan tanggal tahap verifikasi klinik dengan DATE(created_at) permohonan.
 * Jam (H:i:s) pada start_date / stop_date dipertahankan.
 *
 * Idempotent: hanya baris yang tanggalnya belum sama dengan created_at.
 */
class AlignKlinikVerificationDatesToPermohonanCreatedAt extends Migration
{
    public function up(): void
    {
        $rows = DB::table('tb_verification_activity_samples as vas')
            ->join('tb_permohonan_uji_klinik_2 as p', 'p.id_permohonan_uji_klinik', '=', 'vas.is_klinik')
            ->whereNotNull('vas.is_klinik')
            ->whereNull('p.deleted_at')
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

            $update = ['start_date' => $newStart];
            if (!empty($row->stop_date)) {
                $update['stop_date'] = $baseDate . ' ' . Carbon::parse($row->stop_date)->format('H:i:s');
            }

            DB::table('tb_verification_activity_samples')
                ->where('id', $row->id)
                ->update($update);
        }

        // Kolom permohonan yang menyimpan tanggal pengujian (step pemeriksaan)
        $permohonanRows = DB::table('tb_permohonan_uji_klinik_2')
            ->whereNull('deleted_at')
            ->whereNotNull('tglpengujian_permohonan_uji_klinik')
            ->whereNotNull('created_at')
            ->whereRaw('DATE(tglpengujian_permohonan_uji_klinik) <> DATE(created_at)')
            ->select(['id_permohonan_uji_klinik', 'tglpengujian_permohonan_uji_klinik', 'created_at'])
            ->get();

        foreach ($permohonanRows as $row) {
            $baseDate = Carbon::parse($row->created_at)->format('Y-m-d');
            $newTgl = $baseDate . ' ' . Carbon::parse($row->tglpengujian_permohonan_uji_klinik)->format('H:i:s');

            DB::table('tb_permohonan_uji_klinik_2')
                ->where('id_permohonan_uji_klinik', $row->id_permohonan_uji_klinik)
                ->update(['tglpengujian_permohonan_uji_klinik' => $newTgl]);
        }
    }

    public function down(): void
    {
        // Tidak di-rollback otomatis.
    }
}
