<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Helpers\BakuMutuPermohonanKlinikHelper;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;

/**
 * Isi ulang snapshot nilai rujukan permohonan haji (Urin/Sedimen) yang masih kosong
 * setelah master baku mutu haji diperbarui.
 *
 * Idempotent: hanya baris dengan keterangan_dilaporan kosong.
 */
class BackfillHajiEmptyBakuMutuSnapshotUrinSedimen extends Migration
{
    public function up(): void
    {
        if (!BakuMutuPermohonanKlinikHelper::hasSnapshotColumns()) {
            return;
        }

        $query = PermohonanUjiParameterKlinik::query()
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik')
                    ->orWhere('keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik', '')
                    ->orWhere('keterangan_dilaporan_baku_mutu_permohonan_uji_parameter_klinik', '-');
            })
            ->whereHas('permohonanujiklinik', function ($q) {
                $q->where('is_haji', 1)->whereNull('deleted_at');
            })
            ->whereHas('jenisparameterklinik', function ($q) {
                $q->where(function ($qq) {
                    $qq->where('name_parameter_jenis_klinik', 'like', '%Urin%')
                        ->orWhere('name_parameter_jenis_klinik', 'like', '%Sedimen%');
                });
            })
            ->with(['permohonanujiklinik.pasien']);

        $query->orderBy('id_permohonan_uji_parameter_klinik')
            ->chunk(200, function ($params) {
                foreach ($params as $param) {
                    $permohonan = $param->permohonanujiklinik;
                    if (!$permohonan) {
                        continue;
                    }

                    $pasien = $permohonan->pasien;
                    $gender = BakuMutuPermohonanKlinikHelper::normalizePasienGender($pasien->gender_pasien ?? null);
                    $umur = null;
                    if ($permohonan->umurtahun_pasien_permohonan_uji_klinik !== null
                        && $permohonan->umurtahun_pasien_permohonan_uji_klinik !== '') {
                        $umur = (int) $permohonan->umurtahun_pasien_permohonan_uji_klinik;
                    } elseif (!empty($pasien->tgllahir_pasien)) {
                        $umur = (int) \Carbon\Carbon::parse($pasien->tgllahir_pasien)->age;
                    }

                    BakuMutuPermohonanKlinikHelper::repairSnapshotIfNeeded($param, $gender, $umur, 1);
                }
            });
    }

    public function down(): void
    {
        // Data backfill tidak di-rollback otomatis.
    }
}
