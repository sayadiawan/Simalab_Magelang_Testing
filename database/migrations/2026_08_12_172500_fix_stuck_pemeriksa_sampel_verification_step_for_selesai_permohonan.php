<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Perbaiki step 3 (Pemeriksa Sampel) yang is_done=0 padahal permohonan sudah SELESAI.
 * Penyebab: storePermohonanUjiAnalis hanya membuat record baru, tidak update jika sudah ada.
 */
class FixStuckPemeriksaSampelVerificationStepForSelesaiPermohonan extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tb_verification_activity_samples')
            || !Schema::hasTable('tb_permohonan_uji_klinik_2')
        ) {
            return;
        }

        $now = now();

        $ids = DB::table('tb_verification_activity_samples as v')
            ->join('tb_permohonan_uji_klinik_2 as p', 'p.id_permohonan_uji_klinik', '=', 'v.is_klinik')
            ->where('v.id_verification_activity', 3)
            ->where('v.is_done', 0)
            ->where('p.status_permohonan_uji_klinik', 'SELESAI')
            ->whereNull('p.deleted_at')
            ->where(function ($q) {
                $q->where('v.resampling', 0)->orWhereNull('v.resampling');
            })
            ->pluck('v.id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('tb_verification_activity_samples')
            ->whereIn('id', $ids->all())
            ->update([
                'is_done' => 1,
                'updated_at' => $now,
            ]);
    }

    public function down()
    {
        // Data operasional — tidak di-rollback otomatis.
    }
}
