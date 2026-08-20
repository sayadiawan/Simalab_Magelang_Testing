<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Models\GlobalLabSequence;
use Smt\Masterweb\Models\NomerLabSequence;

/**
 * Cegah & rapikan nomor spesimen/lab klinik dobel.
 *
 * Akar: alokasi nomor di dalam transaksi bisnis → request paralel bisa dapat nomor sama
 * (contoh MAKHRUSIN & ANDRIANI = 3673/2347, BUDI & ABDULLAH = 3682/2356).
 *
 * 1) Soft-delete booking detail dobel (keep earliest with reference)
 * 2) Renumber permohonan klinik dengan nourut dobel (keep earliest)
 * 3) Renumber nomer_lab dobel
 * 4) Unique partial: active_sequence generated column
 * 5) Sync counters
 */
class FixDuplicateKlinikSpesimenLabAndAddUniqueGuard extends Migration
{
    const YEAR = 2026;

    public function up()
    {
        $now = now()->format('Y-m-d H:i:s');

        if (Schema::hasTable('global_lab_sequence_detail')) {
            $dupSeqs = DB::table('global_lab_sequence_detail')
                ->whereNull('deleted_at')
                ->where('year', self::YEAR)
                ->select('sequence_number', DB::raw('COUNT(*) as c'))
                ->groupBy('sequence_number')
                ->having('c', '>', 1)
                ->pluck('sequence_number');

            foreach ($dupSeqs as $seqNum) {
                $rows = DB::table('global_lab_sequence_detail')
                    ->whereNull('deleted_at')
                    ->where('year', self::YEAR)
                    ->where('sequence_number', $seqNum)
                    ->orderByRaw("CASE WHEN reference_id IS NULL OR reference_id = '' THEN 1 ELSE 0 END")
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->get(['id']);

                $keepId = optional($rows->first())->id;
                foreach ($rows as $row) {
                    if ($row->id === $keepId) {
                        continue;
                    }
                    DB::table('global_lab_sequence_detail')
                        ->where('id', $row->id)
                        ->update(['deleted_at' => $now, 'updated_at' => $now]);
                }
            }
        }

        if (Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            $dupNouruts = DB::table('tb_permohonan_uji_klinik_2')
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereYear('tglregister_permohonan_uji_klinik', self::YEAR)
                        ->orWhere(function ($q2) {
                            $q2->whereNull('tglregister_permohonan_uji_klinik')
                                ->whereYear('created_at', self::YEAR);
                        });
                })
                ->select('nourut_permohonan_uji_klinik', DB::raw('COUNT(*) as c'))
                ->groupBy('nourut_permohonan_uji_klinik')
                ->having('c', '>', 1)
                ->orderBy('nourut_permohonan_uji_klinik')
                ->pluck('nourut_permohonan_uji_klinik');

            foreach ($dupNouruts as $nourut) {
                $rows = DB::table('tb_permohonan_uji_klinik_2')
                    ->whereNull('deleted_at')
                    ->where('nourut_permohonan_uji_klinik', $nourut)
                    ->orderBy('created_at')
                    ->orderBy('id_permohonan_uji_klinik')
                    ->get([
                        'id_permohonan_uji_klinik',
                        'nourut_permohonan_uji_klinik',
                        'noregister_permohonan_uji_klinik',
                        'nomor_spesimen_manual',
                        'is_nomor_spesimen_manual',
                        'nomer_lab',
                        'nomor_lab_manual',
                    ]);

                $keep = $rows->shift();
                if (!$keep) {
                    continue;
                }

                foreach ($rows as $row) {
                    $newSpesimen = (int) GlobalLabSequence::getNextNumber(self::YEAR, null, 'klinik', $row->id_permohonan_uji_klinik);

                    $noreg = (string) ($row->noregister_permohonan_uji_klinik ?? '');
                    if ($noreg === '' || preg_match('/^\d+$/', $noreg) || preg_match('/^\d+\s*\/\s*\d+/', $noreg)) {
                        if (preg_match('/^(\d+)\s*\/\s*(\d+)/', $noreg, $m)) {
                            $noreg = $newSpesimen . '/' . $m[2];
                        } else {
                            $noreg = (string) $newSpesimen;
                        }
                    }

                    $update = [
                        'nourut_permohonan_uji_klinik' => $newSpesimen,
                        'noregister_permohonan_uji_klinik' => $noreg,
                        'updated_at' => $now,
                    ];
                    if ((int) ($row->is_nomor_spesimen_manual ?? 0) === 1) {
                        $update['nomor_spesimen_manual'] = (string) $newSpesimen;
                    }

                    DB::table('tb_permohonan_uji_klinik_2')
                        ->where('id_permohonan_uji_klinik', $row->id_permohonan_uji_klinik)
                        ->update($update);

                    if (Schema::hasTable('tb_number_klinik')) {
                        DB::table('tb_number_klinik')
                            ->where('id_permohonan_uji_klinik', $row->id_permohonan_uji_klinik)
                            ->update([
                                'new_number' => $newSpesimen,
                                'last_number' => $newSpesimen,
                                'updated_at' => $now,
                            ]);
                    }
                }
            }

            // Lab number duplicates
            if (Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomer_lab')) {
                $dupLabs = DB::table('tb_permohonan_uji_klinik_2')
                    ->whereNull('deleted_at')
                    ->whereNotNull('nomer_lab')
                    ->where('nomer_lab', '!=', '')
                    ->where('nomer_lab', '>', 0)
                    ->where(function ($q) {
                        $q->whereYear('tglregister_permohonan_uji_klinik', self::YEAR)
                            ->orWhere(function ($q2) {
                                $q2->whereNull('tglregister_permohonan_uji_klinik')
                                    ->whereYear('created_at', self::YEAR);
                            });
                    })
                    ->select('nomer_lab', DB::raw('COUNT(*) as c'))
                    ->groupBy('nomer_lab')
                    ->having('c', '>', 1)
                    ->pluck('nomer_lab');

                foreach ($dupLabs as $labNum) {
                    $rows = DB::table('tb_permohonan_uji_klinik_2')
                        ->whereNull('deleted_at')
                        ->where('nomer_lab', $labNum)
                        ->orderBy('created_at')
                        ->orderBy('id_permohonan_uji_klinik')
                        ->get(['id_permohonan_uji_klinik']);

                    $rows->shift(); // keep first
                    foreach ($rows as $row) {
                        $newLab = (int) NomerLabSequence::resolveUniqueLabNumber(null, self::YEAR, $row->id_permohonan_uji_klinik);
                        DB::table('tb_permohonan_uji_klinik_2')
                            ->where('id_permohonan_uji_klinik', $row->id_permohonan_uji_klinik)
                            ->update([
                                'nomer_lab' => $newLab,
                                'nomor_lab_manual' => (string) $newLab,
                                'updated_at' => $now,
                            ]);
                    }
                }
            }
        }

        // Unique guard: hanya satu detail aktif per (year, sequence_number)
        if (Schema::hasTable('global_lab_sequence_detail')
            && !Schema::hasColumn('global_lab_sequence_detail', 'active_sequence')
        ) {
            DB::statement("
                ALTER TABLE global_lab_sequence_detail
                ADD COLUMN active_sequence INT
                    GENERATED ALWAYS AS (CASE WHEN deleted_at IS NULL THEN sequence_number ELSE NULL END) STORED
            ");
            Schema::table('global_lab_sequence_detail', function (Blueprint $table) {
                $table->unique(['year', 'active_sequence'], 'uq_gls_detail_year_active_sequence');
            });
        }

        try {
            GlobalLabSequence::ensureSyncedWithManualSources(self::YEAR);
        } catch (\Throwable $e) {
            // ignore
        }
        try {
            NomerLabSequence::syncLastNumberFromLiveData(self::YEAR);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down()
    {
        if (Schema::hasTable('global_lab_sequence_detail')
            && Schema::hasColumn('global_lab_sequence_detail', 'active_sequence')
        ) {
            Schema::table('global_lab_sequence_detail', function (Blueprint $table) {
                $table->dropUnique('uq_gls_detail_year_active_sequence');
            });
            DB::statement('ALTER TABLE global_lab_sequence_detail DROP COLUMN active_sequence');
        }
    }
}
