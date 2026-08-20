<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Perbaiki nomor spesimen/lab dobel akibat import Excel haji (2026-08-12).
 *
 * Kasus:
 * - CANDRA RIZQI DARMAWAN & SOFIATUN sama-sama nourut 3590 + lab 2233
 * - DINTA DWI MARDANTI juga memakai lab 2233
 *
 * Strategi:
 * - Pertahankan CANDRA (paling awal) → spesimen 3590, lab 2233
 * - SOFIATUN → spesimen & lab unik berikutnya
 * - DINTA → lab unik berikutnya (spesimen 3591 tetap)
 * - Sinkronkan tb_number_klinik, global_lab_sequence_detail, dan counter sequence
 */
class FixDuplicateKlinikSampleNumbersFromHajiImport extends Migration
{
    const YEAR = 2026;

    /** CANDRA RIZQI DARMAWAN — keeper */
    const KEEPER_ID = 'dee0110c-db39-4ab3-a52b-728af5bcc927';

    /** SOFIATUN — dobel spesimen + lab */
    const SOFIATUN_ID = 'e3081118-282d-4c99-b559-49afb5fa0e48';

    /** DINTA DWI MARDANTI — dobel lab saja */
    const DINTA_ID = 'fafa72b6-528b-42bd-b77e-f14fe2378187';

    /**
     * Snapshot nilai asli untuk rollback.
     */
    private function originalSnapshot(): array
    {
        return [
            self::SOFIATUN_ID => [
                'nourut_permohonan_uji_klinik' => 3590,
                'noregister_permohonan_uji_klinik' => '3590/1472',
                'nomor_lab_manual' => '2233',
                'number_klinik' => 3590,
                'sequence_number' => 3590,
            ],
            self::DINTA_ID => [
                'nomor_lab_manual' => '2233',
                'nomer_lab' => 2233,
            ],
        ];
    }

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
            $sofiatun = DB::table('tb_permohonan_uji_klinik_2')
                ->where('id_permohonan_uji_klinik', self::SOFIATUN_ID)
                ->whereNull('deleted_at')
                ->first();

            $dinta = DB::table('tb_permohonan_uji_klinik_2')
                ->where('id_permohonan_uji_klinik', self::DINTA_ID)
                ->whereNull('deleted_at')
                ->first();

            $keeper = DB::table('tb_permohonan_uji_klinik_2')
                ->where('id_permohonan_uji_klinik', self::KEEPER_ID)
                ->whereNull('deleted_at')
                ->first();

            // Sudah tidak dobel / data tidak ada → no-op (aman di-jalankan ulang)
            if (!$keeper || !$sofiatun) {
                return;
            }

            $sofiatunStillDupSpesimen = (int) $sofiatun->nourut_permohonan_uji_klinik === 3590
                && (int) $keeper->nourut_permohonan_uji_klinik === 3590;
            $sofiatunStillDupLab = trim((string) ($sofiatun->nomor_lab_manual ?? '')) === '2233';
            $dintaStillDupLab = $dinta && trim((string) ($dinta->nomor_lab_manual ?? '')) === '2233';

            if (!$sofiatunStillDupSpesimen && !$sofiatunStillDupLab && !$dintaStillDupLab) {
                return;
            }

            $now = now()->format('Y-m-d H:i:s');
            $nextSpesimen = null;
            $nextLabCursor = $this->peekNextLabNumber(self::YEAR);

            if ($sofiatunStillDupSpesimen || $sofiatunStillDupLab) {
                $nextSpesimen = $sofiatunStillDupSpesimen
                    ? $this->nextFreeSpesimenNumber(self::YEAR)
                    : (int) $sofiatun->nourut_permohonan_uji_klinik;

                $nextLab = $sofiatunStillDupLab
                    ? $this->nextFreeLabNumber(self::YEAR, $nextLabCursor)
                    : (int) preg_replace('/\D+/', '', (string) ($sofiatun->nomor_lab_manual ?? '0'));

                if ($sofiatunStillDupLab) {
                    $nextLabCursor = $nextLab;
                }

                $newNoregister = $this->rewriteNoregisterSpesimen(
                    (string) ($sofiatun->noregister_permohonan_uji_klinik ?? ''),
                    $nextSpesimen
                );

                DB::table('tb_permohonan_uji_klinik_2')
                    ->where('id_permohonan_uji_klinik', self::SOFIATUN_ID)
                    ->update([
                        'nourut_permohonan_uji_klinik' => $nextSpesimen,
                        'noregister_permohonan_uji_klinik' => $newNoregister,
                        'nomor_lab_manual' => (string) $nextLab,
                        'updated_at' => $now,
                    ]);

                if (Schema::hasTable('tb_number_klinik')) {
                    DB::table('tb_number_klinik')
                        ->where('id_permohonan_uji_klinik', self::SOFIATUN_ID)
                        ->whereNull('deleted_at')
                        ->update([
                            'new_number' => $nextSpesimen,
                            'last_number' => $nextSpesimen,
                            'updated_at' => $now,
                        ]);
                }

                if (Schema::hasTable('global_lab_sequence_detail')) {
                    DB::table('global_lab_sequence_detail')
                        ->where('year', self::YEAR)
                        ->where('lab_type', 'klinik')
                        ->where('reference_id', self::SOFIATUN_ID)
                        ->whereNull('deleted_at')
                        ->update([
                            'sequence_number' => $nextSpesimen,
                            'updated_at' => $now,
                        ]);
                }

                $this->raiseGlobalLabSequenceToAtLeast(self::YEAR, $nextSpesimen);
                $this->raiseNomerLabSequenceToAtLeast(self::YEAR, $nextLab);
            }

            if ($dintaStillDupLab) {
                $nextLabDinta = $this->nextFreeLabNumber(self::YEAR, $nextLabCursor);
                $nextLabCursor = $nextLabDinta;

                $dintaUpdate = [
                    'nomor_lab_manual' => (string) $nextLabDinta,
                    'updated_at' => $now,
                ];
                if (Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomer_lab')
                    && (string) ($dinta->nomer_lab ?? '') === '2233') {
                    $dintaUpdate['nomer_lab'] = $nextLabDinta;
                }

                DB::table('tb_permohonan_uji_klinik_2')
                    ->where('id_permohonan_uji_klinik', self::DINTA_ID)
                    ->update($dintaUpdate);

                $this->raiseNomerLabSequenceToAtLeast(self::YEAR, $nextLabDinta);
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
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            return;
        }

        $snap = $this->originalSnapshot();
        $now = now()->format('Y-m-d H:i:s');

        DB::transaction(function () use ($snap, $now) {
            $sofiatun = $snap[self::SOFIATUN_ID];
            DB::table('tb_permohonan_uji_klinik_2')
                ->where('id_permohonan_uji_klinik', self::SOFIATUN_ID)
                ->update([
                    'nourut_permohonan_uji_klinik' => $sofiatun['nourut_permohonan_uji_klinik'],
                    'noregister_permohonan_uji_klinik' => $sofiatun['noregister_permohonan_uji_klinik'],
                    'nomor_lab_manual' => $sofiatun['nomor_lab_manual'],
                    'updated_at' => $now,
                ]);

            if (Schema::hasTable('tb_number_klinik')) {
                DB::table('tb_number_klinik')
                    ->where('id_permohonan_uji_klinik', self::SOFIATUN_ID)
                    ->whereNull('deleted_at')
                    ->update([
                        'new_number' => $sofiatun['number_klinik'],
                        'last_number' => $sofiatun['number_klinik'],
                        'updated_at' => $now,
                    ]);
            }

            if (Schema::hasTable('global_lab_sequence_detail')) {
                DB::table('global_lab_sequence_detail')
                    ->where('year', self::YEAR)
                    ->where('lab_type', 'klinik')
                    ->where('reference_id', self::SOFIATUN_ID)
                    ->whereNull('deleted_at')
                    ->update([
                        'sequence_number' => $sofiatun['sequence_number'],
                        'updated_at' => $now,
                    ]);
            }

            $dinta = $snap[self::DINTA_ID];
            $dintaUpdate = [
                'nomor_lab_manual' => $dinta['nomor_lab_manual'],
                'updated_at' => $now,
            ];
            if (Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomer_lab')) {
                $dintaUpdate['nomer_lab'] = $dinta['nomer_lab'];
            }
            DB::table('tb_permohonan_uji_klinik_2')
                ->where('id_permohonan_uji_klinik', self::DINTA_ID)
                ->update($dintaUpdate);
        });
    }

    private function rewriteNoregisterSpesimen(string $noregister, int $newSpesimen): string
    {
        $noregister = trim($noregister);
        if ($noregister !== '' && preg_match('/^(\d+)\s*\/\s*(\d+)\s*$/', $noregister, $m)) {
            return $newSpesimen . '/' . $m[2];
        }

        return (string) $newSpesimen;
    }

    private function peekNextLabNumber(int $year): int
    {
        $seqLast = 0;
        if (Schema::hasTable('global_nomer_lab_sequence')) {
            $seqLast = (int) DB::table('global_nomer_lab_sequence')
                ->where('year', $year)
                ->value('last_number');
        }

        return max(1, $seqLast + 1);
    }

    private function nextFreeSpesimenNumber(int $year): int
    {
        $seqLast = 0;
        if (Schema::hasTable('global_lab_sequence')) {
            $seqLast = (int) DB::table('global_lab_sequence')
                ->where('year', $year)
                ->whereNull('deleted_at')
                ->value('last_number');
        }

        $candidate = max(1, $seqLast + 1);
        $guard = 0;
        while ($guard < 10000 && $this->isSpesimenNumberTaken($candidate, $year)) {
            $candidate++;
            $guard++;
        }

        return $candidate;
    }

    private function nextFreeLabNumber(int $year, int $from): int
    {
        $candidate = max(1, $from);
        $guard = 0;
        while ($guard < 10000 && $this->isLabNumberTaken($candidate, $year)) {
            $candidate++;
            $guard++;
        }

        return $candidate;
    }

    private function isSpesimenNumberTaken(int $number, int $year): bool
    {
        $existsPermohonan = DB::table('tb_permohonan_uji_klinik_2')
            ->whereNull('deleted_at')
            ->where(function ($q) use ($year) {
                $q->whereYear('tglregister_permohonan_uji_klinik', $year)
                    ->orWhere(function ($q2) use ($year) {
                        $q2->whereNull('tglregister_permohonan_uji_klinik')
                            ->whereYear('created_at', $year);
                    });
            })
            ->where(function ($q) use ($number) {
                $q->where('nourut_permohonan_uji_klinik', $number)
                    ->orWhere('nomor_spesimen_manual', (string) $number)
                    ->orWhere('noregister_permohonan_uji_klinik', (string) $number)
                    ->orWhere('noregister_permohonan_uji_klinik', 'like', $number . '/%');
            })
            ->exists();

        if ($existsPermohonan) {
            return true;
        }

        if (!Schema::hasTable('global_lab_sequence_detail')) {
            return false;
        }

        return DB::table('global_lab_sequence_detail')
            ->where('year', $year)
            ->where('sequence_number', $number)
            ->whereNull('deleted_at')
            ->exists();
    }

    private function isLabNumberTaken(int $number, int $year): bool
    {
        return DB::table('tb_permohonan_uji_klinik_2')
            ->whereNull('deleted_at')
            ->where(function ($q) use ($year) {
                $q->whereYear('tglregister_permohonan_uji_klinik', $year)
                    ->orWhere(function ($q2) use ($year) {
                        $q2->whereNull('tglregister_permohonan_uji_klinik')
                            ->whereYear('created_at', $year);
                    });
            })
            ->where(function ($q) use ($number) {
                $q->where('nomor_lab_manual', (string) $number);
                if (Schema::hasColumn('tb_permohonan_uji_klinik_2', 'nomer_lab')) {
                    $q->orWhere('nomer_lab', $number)
                        ->orWhere('nomer_lab', (string) $number);
                }
            })
            ->exists();
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

    private function raiseNomerLabSequenceToAtLeast(int $year, int $minNumber): void
    {
        if ($minNumber < 1 || !Schema::hasTable('global_nomer_lab_sequence')) {
            return;
        }

        $seq = DB::table('global_nomer_lab_sequence')
            ->where('year', $year)
            ->first();

        if (!$seq) {
            return;
        }

        if ((int) $seq->last_number < $minNumber) {
            DB::table('global_nomer_lab_sequence')
                ->where('id', $seq->id)
                ->update([
                    'last_number' => $minNumber,
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
        }
    }
}
