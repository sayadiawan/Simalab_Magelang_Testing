<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Perbaiki nomor sampel kesmas (kimia/mikro) yang bentrok dengan klinik
 * dan antar kimia–mikro (batch 2026-08-11 siang, lab_number=1).
 *
 * Strategi:
 * - Klinik TIDAK diubah
 * - Sampel kimia/mikro bermasalah digeser ke nomor berikutnya setelah cluster wajar
 * - Melewati nomor yang sudah dipakai klinik / kesmas lain / sequence detail (range wajar)
 *
 * Idempotent: jika tidak ada sampel lab_number=1 di jendela waktu bug → no-op
 * (aman dijalankan ulang, termasuk di DB lokal yang sudah diperbaiki manual).
 */
class FixDuplicateKesmasSampleNumbersVsKlinik extends Migration
{
    const YEAR = 2026;

    /** Batas atas nomor "wajar" — abaikan pulau junk 6xxx di sequence. */
    const WAJAR_MAX = 5000;

    const WINDOW_START = '2026-08-11 00:00:00';
    const WINDOW_END = '2026-08-13 00:00:00';

    /**
     * Snapshot kode asli (untuk down) — id_samples => [codesample, count_id].
     */
    private function originalSnapshot(): array
    {
        return [
            '03c15de8-b21e-457b-b35d-25bc3ff3ecc6' => ['UA.02/3409/2026', 3409],
            '266712d7-f7f7-474f-b752-3d5df12bdc79' => ['UA.02/3407/2026', 3407],
            '370f7b43-3dc6-48cb-a540-c7dc86cea140' => ['UA.02/3406/2026', 3406],
            '48ecc792-bb99-411e-baf8-3481ab3f3503' => ['UA.02/3410/2026', 3410],
            'bf359457-3b83-4da8-9bee-3e31b3a2ad3c' => ['UA.02/3408/2026', 3408],
            'a095492b-ad9a-4300-ad30-8ad9adecf7fd' => ['UA.02/3405/2026', 3405],
            '7fbb5dee-c557-4ef6-841d-8539520de9da' => ['AH.02/3400/2026', 3400],
            'cc3e44d4-84b4-407a-8731-1d2efe22362c' => ['AH.01/3401/2026', 3401],
            '6565435d-d29d-4d2e-9c31-d6024ed5eae6' => ['AM.02/3521/2026', 3521],
            '45e371b9-0535-4f7d-a869-85f82ea59267' => ['AM.01/3518/2026', 3518],
            '8d866418-3e09-44bf-ab87-3bb7af4c70c7' => ['AM.02/3516/2026', 3516],
            '96a54113-37f1-418c-b467-43982922cb14' => ['AM.01/3515/2026', 3515],
            'f45b1d99-0fd3-4721-a861-132e57698666' => ['AM.02/3515/2026', 3515],
            '86fbf3f5-848f-48be-aedc-fbbd0708b298' => ['AM.01/3514/2026', 3514],
            '0f15f5ac-f112-44d4-9d64-37facd695df2' => ['AM.02/3518/2026', 3518],
            '741a82a8-4c46-4131-9599-0bded66b5820' => ['AM.01/3517/2026', 3517],
            '787c0db1-b3c8-4e79-ab3f-ce3bdf28e0ce' => ['AM.02/3520/2026', 3520],
            '9f0db332-2d9b-449c-b63d-7d08713c71e0' => ['AM.01/3516/2026', 3516],
            'bd858643-69d8-4f70-99b1-8a156129b742' => ['AM.02/3517/2026', 3517],
            'efebd936-75ba-47d5-8d96-6b2bd81e9e31' => ['AM.02/3519/2026', 3519],
        ];
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tb_samples') || !Schema::hasTable('tb_lab_num') || !Schema::hasTable('ms_laboratorium')) {
            return;
        }

        DB::transaction(function () {
            $toMove = $this->findBrokenKesmasSamples();
            if ($toMove->isEmpty()) {
                return;
            }

            $moveIds = $toMove->pluck('id_samples')->all();
            $occupied = $this->buildOccupiedNumbers($moveIds);
            $startFrom = $this->resolveStartAfter($occupied);
            $next = $startFrom + 1;
            $now = now()->format('Y-m-d H:i:s');
            $maxAssigned = $startFrom;

            foreach ($toMove as $row) {
                while (isset($occupied[$next]) && $next <= self::WAJAR_MAX + 2000) {
                    $next++;
                }

                $newNum = $next;
                $occupied[$newNum] = true;
                $next++;
                $maxAssigned = max($maxAssigned, $newNum);

                $oldCode = (string) $row->codesample_samples;
                if (!preg_match('#^([^/]+)/(\d+)/(\d{4})$#', $oldCode, $m)) {
                    throw new \RuntimeException('Format codesample tidak dikenali: ' . $oldCode);
                }
                $newCode = $m[1] . '/' . $newNum . '/' . $m[3];

                DB::table('tb_samples')
                    ->where('id_samples', $row->id_samples)
                    ->update([
                        'codesample_samples' => $newCode,
                        'count_id' => $newNum,
                        'updated_at' => $now,
                    ]);

                DB::table('tb_lab_num')
                    ->where('id_lab_num', $row->id_lab_num)
                    ->update([
                        'lab_number' => $newNum,
                        'updated_at' => $now,
                    ]);

                if (Schema::hasTable('global_lab_sequence_detail')) {
                    // Soft-delete detail lama yang tertaut lab_num ini (jika ada)
                    DB::table('global_lab_sequence_detail')
                        ->where('year', self::YEAR)
                        ->where('lab_type', 'lab')
                        ->where('reference_id', $row->id_lab_num)
                        ->whereNull('deleted_at')
                        ->update([
                            'deleted_at' => $now,
                            'updated_at' => $now,
                        ]);

                    DB::table('global_lab_sequence_detail')->insert([
                        'id' => (string) Str::uuid(),
                        'year' => self::YEAR,
                        'sequence_number' => $newNum,
                        'lab_id' => $row->lab_id,
                        'lab_type' => 'lab',
                        'reference_id' => $row->id_lab_num,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]);
                }
            }

            $this->raiseGlobalLabSequenceToAtLeast(self::YEAR, $maxAssigned);
        });
    }

    /**
     * Reverse the migrations (best-effort, berdasarkan snapshot id lokal/server yang sama).
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('tb_samples') || !Schema::hasTable('tb_lab_num')) {
            return;
        }

        $snap = $this->originalSnapshot();
        $now = now()->format('Y-m-d H:i:s');

        DB::transaction(function () use ($snap, $now) {
            foreach ($snap as $sampleId => $meta) {
                $oldCode = $meta[0];
                $oldNum = (int) $meta[1];

                $sample = DB::table('tb_samples')
                    ->where('id_samples', $sampleId)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$sample) {
                    continue;
                }

                // Hanya rollback jika masih terlihat sudah digeser (bukan kode asli)
                if ((string) $sample->codesample_samples === $oldCode
                    && (int) $sample->count_id === $oldNum) {
                    continue;
                }

                DB::table('tb_samples')
                    ->where('id_samples', $sampleId)
                    ->update([
                        'codesample_samples' => $oldCode,
                        'count_id' => $oldNum,
                        'updated_at' => $now,
                    ]);

                $labNum = DB::table('tb_lab_num')
                    ->where('sample_id', $sampleId)
                    ->whereNull('deleted_at')
                    ->first();

                if ($labNum) {
                    DB::table('tb_lab_num')
                        ->where('id_lab_num', $labNum->id_lab_num)
                        ->update([
                            'lab_number' => 1,
                            'updated_at' => $now,
                        ]);

                    if (Schema::hasTable('global_lab_sequence_detail')) {
                        DB::table('global_lab_sequence_detail')
                            ->where('year', self::YEAR)
                            ->where('lab_type', 'lab')
                            ->where('reference_id', $labNum->id_lab_num)
                            ->whereNull('deleted_at')
                            ->update([
                                'deleted_at' => $now,
                                'updated_at' => $now,
                            ]);
                    }
                }
            }
        });
    }

    /**
     * Sampel kesmas otomatis di jendela bug dengan lab_number=1 (belum tersinkron sequence).
     */
    private function findBrokenKesmasSamples()
    {
        return DB::table('tb_samples as s')
            ->join('tb_lab_num as ln', function ($j) {
                $j->on('ln.sample_id', '=', 's.id_samples')->whereNull('ln.deleted_at');
            })
            ->join('ms_laboratorium as lab', 'lab.id_laboratorium', '=', 'ln.lab_id')
            ->whereNull('s.deleted_at')
            ->whereIn('lab.kode_laboratorium', ['KIM', 'KMA', 'FKA', 'MBI'])
            ->where(function ($q) {
                $q->where('s.is_nomor_sampel_manual', 0)
                    ->orWhereNull('s.is_nomor_sampel_manual');
            })
            ->where('ln.lab_number', 1)
            ->where('s.created_at', '>=', self::WINDOW_START)
            ->where('s.created_at', '<', self::WINDOW_END)
            ->orderBy('s.created_at')
            ->orderBy('lab.kode_laboratorium')
            ->select([
                's.id_samples',
                's.codesample_samples',
                's.count_id',
                'ln.id_lab_num',
                'ln.lab_id',
                'lab.kode_laboratorium',
                's.created_at',
            ])
            ->get();
    }

    /**
     * @param  array<int, string>  $excludeSampleIds
     * @return array<int, true>
     */
    private function buildOccupiedNumbers(array $excludeSampleIds): array
    {
        $occupied = [];

        // Klinik (nomor spesimen efektif, range wajar)
        if (Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            $klinikRows = DB::table('tb_permohonan_uji_klinik_2')
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->whereYear('tglregister_permohonan_uji_klinik', self::YEAR)
                        ->orWhere(function ($q2) {
                            $q2->whereNull('tglregister_permohonan_uji_klinik')
                                ->whereYear('created_at', self::YEAR);
                        });
                })
                ->get([
                    'is_nomor_spesimen_manual',
                    'nomor_spesimen_manual',
                    'nourut_permohonan_uji_klinik',
                ]);

            foreach ($klinikRows as $p) {
                if ((int) ($p->is_nomor_spesimen_manual ?? 0) === 1
                    && trim((string) ($p->nomor_spesimen_manual ?? '')) !== '') {
                    $n = (int) preg_replace('/\D+/', '', (string) $p->nomor_spesimen_manual);
                } else {
                    $n = (int) $p->nourut_permohonan_uji_klinik;
                }
                if ($n >= 1 && $n <= self::WAJAR_MAX) {
                    $occupied[$n] = true;
                }
            }
        }

        // Kesmas yang dipertahankan (bukan yang digeser)
        $kesmasKeep = DB::table('tb_samples as s')
            ->join('tb_lab_num as ln', function ($j) {
                $j->on('ln.sample_id', '=', 's.id_samples')->whereNull('ln.deleted_at');
            })
            ->join('ms_laboratorium as lab', 'lab.id_laboratorium', '=', 'ln.lab_id')
            ->whereNull('s.deleted_at')
            ->whereIn('lab.kode_laboratorium', ['KIM', 'KMA', 'FKA', 'MBI'])
            ->whereNotIn('s.id_samples', $excludeSampleIds)
            ->whereNotNull('s.count_id')
            ->whereRaw('CAST(s.count_id AS UNSIGNED) BETWEEN ? AND ?', [1, self::WAJAR_MAX])
            ->pluck('s.count_id');

        foreach ($kesmasKeep as $n) {
            $occupied[(int) $n] = true;
        }

        // Sequence detail aktif di range wajar
        if (Schema::hasTable('global_lab_sequence_detail')) {
            $seq = DB::table('global_lab_sequence_detail')
                ->where('year', self::YEAR)
                ->whereNull('deleted_at')
                ->whereBetween('sequence_number', [1, self::WAJAR_MAX])
                ->pluck('sequence_number');

            foreach ($seq as $n) {
                $occupied[(int) $n] = true;
            }
        }

        return $occupied;
    }

    /**
     * @param  array<int, true>  $occupied
     */
    private function resolveStartAfter(array $occupied): int
    {
        $maxOccupied = $occupied === [] ? 0 : max(array_keys($occupied));

        $seqLast = 0;
        if (Schema::hasTable('global_lab_sequence')) {
            $seqLast = (int) DB::table('global_lab_sequence')
                ->where('year', self::YEAR)
                ->whereNull('deleted_at')
                ->value('last_number');
        }

        // Jangan ikut loncat ke pulau junk (> WAJAR_MAX)
        if ($seqLast > self::WAJAR_MAX) {
            $seqLast = $maxOccupied;
        }

        return max($maxOccupied, $seqLast);
    }

    private function raiseGlobalLabSequenceToAtLeast(int $year, int $minNumber): void
    {
        if ($minNumber < 1 || !Schema::hasTable('global_lab_sequence')) {
            return;
        }

        $seq = DB::table('global_lab_sequence')
            ->where('year', $year)
            ->whereNull('deleted_at')
            ->first();

        if (!$seq) {
            return;
        }

        if ((int) $seq->last_number < $minNumber) {
            DB::table('global_lab_sequence')
                ->where('id', $seq->id)
                ->update([
                    'last_number' => $minNumber,
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
        }
    }
}
