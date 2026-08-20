<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ketika sampel / permohonan sudah dihapus (klinik & kesmas),
 * soft-delete nomor terkait di:
 * - tb_lab_num (LabNum)
 * - tb_number_klinik (NumberKlinik)
 * - tb_nomer_lab_kesmas (NomerLabKesmas)
 * - global_lab_sequence_detail (GlobalLabSequenceDetail)
 *
 * Juga menambahkan deleted_at pada tb_nomer_lab_kesmas (belum SoftDeletes).
 */
class CleanupOrphanLabNumbersWhenSampleDeleted extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tb_nomer_lab_kesmas') && !Schema::hasColumn('tb_nomer_lab_kesmas', 'deleted_at')) {
            Schema::table('tb_nomer_lab_kesmas', function (Blueprint $table) {
                $table->softDeletes()->index();
            });
        }

        $now = now();

        // 1) LabNum: sample atau permohonan uji kesmas sudah hilang / soft-deleted
        if (Schema::hasTable('tb_lab_num')) {
            DB::update("
                UPDATE tb_lab_num ln
                LEFT JOIN tb_samples s
                  ON BINARY s.id_samples = BINARY ln.sample_id
                LEFT JOIN tb_permohonan_uji pu
                  ON BINARY pu.id_permohonan_uji = BINARY ln.permohonan_uji_id
                SET ln.deleted_at = COALESCE(s.deleted_at, pu.deleted_at, ?)
                WHERE ln.deleted_at IS NULL
                  AND (
                    s.id_samples IS NULL
                    OR s.deleted_at IS NOT NULL
                    OR pu.id_permohonan_uji IS NULL
                    OR pu.deleted_at IS NOT NULL
                  )
            ", [$now]);
        }

        // 2) NumberKlinik: permohonan uji klinik terkait sudah hilang / soft-deleted
        if (Schema::hasTable('tb_number_klinik') && Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            DB::update("
                UPDATE tb_number_klinik nk
                LEFT JOIN tb_permohonan_uji_klinik_2 p
                  ON BINARY p.id_permohonan_uji_klinik = BINARY nk.id_permohonan_uji_klinik
                SET nk.deleted_at = COALESCE(p.deleted_at, ?)
                WHERE nk.deleted_at IS NULL
                  AND nk.id_permohonan_uji_klinik IS NOT NULL
                  AND (p.id_permohonan_uji_klinik IS NULL OR p.deleted_at IS NOT NULL)
            ", [$now]);

            // Baris yang hanya tertaut haji (tanpa PUK aktif)
            if (Schema::hasTable('tb_permohonan_uji_klinik_haji')) {
                DB::update("
                    UPDATE tb_number_klinik nk
                    LEFT JOIN tb_permohonan_uji_klinik_haji h
                      ON BINARY h.id_permohonan_uji_klinik_haji = BINARY nk.id_haji
                    LEFT JOIN tb_permohonan_uji_klinik_2 p
                      ON BINARY p.id_permohonan_uji_klinik = BINARY nk.id_permohonan_uji_klinik
                     AND p.deleted_at IS NULL
                    SET nk.deleted_at = COALESCE(h.deleted_at, ?)
                    WHERE nk.deleted_at IS NULL
                      AND nk.id_haji IS NOT NULL
                      AND p.id_permohonan_uji_klinik IS NULL
                      AND (h.id_permohonan_uji_klinik_haji IS NULL OR h.deleted_at IS NOT NULL)
                ", [$now]);
            }
        }

        // 3) NomerLabKesmas: permohonan uji kesmas sudah hilang / soft-deleted
        if (Schema::hasTable('tb_nomer_lab_kesmas') && Schema::hasColumn('tb_nomer_lab_kesmas', 'deleted_at')) {
            DB::update("
                UPDATE tb_nomer_lab_kesmas n
                LEFT JOIN tb_permohonan_uji pu
                  ON BINARY pu.id_permohonan_uji = BINARY n.permohonan_uji_id
                SET n.deleted_at = COALESCE(pu.deleted_at, ?)
                WHERE n.deleted_at IS NULL
                  AND (pu.id_permohonan_uji IS NULL OR pu.deleted_at IS NOT NULL)
            ", [$now]);
        }

        // 4) GlobalLabSequenceDetail — klinik: reference_id ke PUK yang sudah dihapus
        if (Schema::hasTable('global_lab_sequence_detail') && Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            DB::update("
                UPDATE global_lab_sequence_detail d
                LEFT JOIN tb_permohonan_uji_klinik_2 p
                  ON BINARY p.id_permohonan_uji_klinik = BINARY d.reference_id
                SET d.deleted_at = COALESCE(p.deleted_at, ?)
                WHERE d.deleted_at IS NULL
                  AND d.lab_type = 'klinik'
                  AND d.reference_id IS NOT NULL
                  AND (p.id_permohonan_uji_klinik IS NULL OR p.deleted_at IS NOT NULL)
            ", [$now]);
        }

        // 5) GlobalLabSequenceDetail — lab/kesmas: reference_id ke LabNum yang sudah dihapus
        if (Schema::hasTable('global_lab_sequence_detail') && Schema::hasTable('tb_lab_num')) {
            DB::update("
                UPDATE global_lab_sequence_detail d
                LEFT JOIN tb_lab_num ln
                  ON BINARY ln.id_lab_num = BINARY d.reference_id
                SET d.deleted_at = COALESCE(ln.deleted_at, ?)
                WHERE d.deleted_at IS NULL
                  AND d.lab_type = 'lab'
                  AND d.reference_id IS NOT NULL
                  AND (ln.id_lab_num IS NULL OR ln.deleted_at IS NOT NULL)
            ", [$now]);
        }

        // Sync last_number per tahun agar counter tidak menggantung di nomor orphan
        if (Schema::hasTable('global_lab_sequence') && Schema::hasTable('global_lab_sequence_detail')) {
            $years = DB::table('global_lab_sequence')
                ->whereNull('deleted_at')
                ->distinct()
                ->pluck('year');

            foreach ($years as $year) {
                $year = (int) $year;
                if ($year < 1900) {
                    continue;
                }

                $maxDetail = (int) (DB::table('global_lab_sequence_detail')
                    ->where('year', $year)
                    ->whereNull('deleted_at')
                    ->max('sequence_number') ?? 0);

                $maxKlinik = 0;
                if (Schema::hasTable('tb_permohonan_uji_klinik_2')) {
                    $maxKlinik = (int) (DB::table('tb_permohonan_uji_klinik_2')
                        ->whereNull('deleted_at')
                        ->where(function ($q) use ($year) {
                            $q->whereYear('tglregister_permohonan_uji_klinik', $year)
                                ->orWhere(function ($q2) use ($year) {
                                    $q2->whereNull('tglregister_permohonan_uji_klinik')
                                        ->whereYear('created_at', $year);
                                });
                        })
                        ->max('nourut_permohonan_uji_klinik') ?? 0);
                }

                $maxLabNum = 0;
                if (Schema::hasTable('tb_lab_num')) {
                    $maxLabNum = (int) (DB::table('tb_lab_num')
                        ->whereNull('deleted_at')
                        ->where(function ($q) use ($year) {
                            $q->where('year_lab_num', $year)
                                ->orWhere(function ($q2) use ($year) {
                                    $q2->whereNull('year_lab_num')
                                        ->whereYear('created_at', $year);
                                });
                        })
                        ->max('lab_number') ?? 0);
                }

                $target = max(0, $maxDetail, $maxKlinik, $maxLabNum);

                DB::table('global_lab_sequence')
                    ->where('year', $year)
                    ->whereNull('deleted_at')
                    ->update([
                        'last_number' => $target,
                        'updated_at' => $now,
                    ]);
            }
        }
    }

    public function down()
    {
        // Soft-delete data tidak di-restore (irreversible).
        // Hanya rollback kolom deleted_at pada tb_nomer_lab_kesmas bila ditambahkan di up().
        if (Schema::hasTable('tb_nomer_lab_kesmas') && Schema::hasColumn('tb_nomer_lab_kesmas', 'deleted_at')) {
            Schema::table('tb_nomer_lab_kesmas', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
}
