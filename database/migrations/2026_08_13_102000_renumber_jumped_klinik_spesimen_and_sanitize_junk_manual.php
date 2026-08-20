<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Models\GlobalLabSequence;

/**
 * Rapikan nomor spesimen yang salah:
 * - Loncatan 5000+/6000-an (Grabag, HADI 6217, STEFANUS 6216, dll)
 * - nomor_spesimen_manual junk (>6 digit, mis. 25991559)
 * - Manual yang ikut loncat (>=5000) ikut direnumber, bukan dipertahankan
 * - lab_number kesmas junk diselaraskan ke kode sampel
 * - Sync global_lab_sequence.last_number
 *
 * Idempotent — aman dijalankan ulang.
 */
class RenumberJumpedKlinikSpesimenAndSanitizeJunkManual extends Migration
{
    const YEAR = 2026;
    const JUMP_THRESHOLD = 5000;

    public function up()
    {
        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            return;
        }

        DB::transaction(function () {
            $now = now()->format('Y-m-d H:i:s');
            $year = self::YEAR;
            $threshold = self::JUMP_THRESHOLD;

            // 1) Junk nomor_spesimen_manual (>6 digit)
            $junkManuals = DB::table('tb_permohonan_uji_klinik_2')
                ->whereNull('deleted_at')
                ->whereNotNull('nomor_spesimen_manual')
                ->whereRaw("TRIM(nomor_spesimen_manual) <> ''")
                ->whereRaw("LENGTH(REGEXP_REPLACE(TRIM(nomor_spesimen_manual), '[^0-9]', '')) > 6")
                ->get([
                    'id_permohonan_uji_klinik',
                    'nourut_permohonan_uji_klinik',
                    'noregister_permohonan_uji_klinik',
                    'nomor_spesimen_manual',
                ]);

            foreach ($junkManuals as $row) {
                $fallback = $this->resolveFallbackSpesimen(
                    (string) ($row->noregister_permohonan_uji_klinik ?? ''),
                    (string) ($row->nourut_permohonan_uji_klinik ?? '')
                );

                DB::table('tb_permohonan_uji_klinik_2')
                    ->where('id_permohonan_uji_klinik', $row->id_permohonan_uji_klinik)
                    ->update([
                        'nomor_spesimen_manual' => $fallback > 0 ? (string) $fallback : null,
                        'is_nomor_spesimen_manual' => $fallback > 0 ? 1 : 0,
                        'noregister_permohonan_uji_klinik' => $fallback > 0
                            ? (string) $fallback
                            : $row->noregister_permohonan_uji_klinik,
                        'updated_at' => $now,
                    ]);
            }

            // 2) Semua klinik dengan nourut >= threshold
            $high = DB::table('tb_permohonan_uji_klinik_2')
                ->whereNull('deleted_at')
                ->whereRaw('CAST(nourut_permohonan_uji_klinik AS UNSIGNED) >= ?', [$threshold])
                ->orderBy('created_at')
                ->orderByRaw('CAST(nourut_permohonan_uji_klinik AS UNSIGNED)')
                ->get([
                    'id_permohonan_uji_klinik',
                    'nourut_permohonan_uji_klinik',
                    'noregister_permohonan_uji_klinik',
                    'nomor_spesimen_manual',
                    'is_nomor_spesimen_manual',
                ]);

            if ($high->isNotEmpty()) {
                // Manual valid (< threshold) → samakan nourut ke manual
                $manualKeep = $high->filter(function ($row) use ($threshold) {
                    $manual = trim((string) ($row->nomor_spesimen_manual ?? ''));
                    return (int) ($row->is_nomor_spesimen_manual ?? 0) === 1
                        && preg_match('/^\d{1,6}$/', $manual)
                        && (int) $manual > 0
                        && (int) $manual < $threshold;
                });

                foreach ($manualKeep as $row) {
                    $spesimen = (int) $row->nomor_spesimen_manual;
                    $this->updateKlinikSpesimenNumber(
                        (string) $row->id_permohonan_uji_klinik,
                        $spesimen,
                        (string) ($row->noregister_permohonan_uji_klinik ?? ''),
                        true,
                        $now,
                        $year
                    );
                }

                // Sisanya (otomatis ATAU manual ikut loncat seperti HADI/STEFANUS) → slot bebas
                $toRenumber = $high->filter(function ($row) use ($manualKeep) {
                    return !$manualKeep->contains('id_permohonan_uji_klinik', $row->id_permohonan_uji_klinik);
                })->values();

                if ($toRenumber->isNotEmpty()) {
                    $free = $this->nextFreeSpesimenNumbers($year, $threshold, $toRenumber->count());

                    foreach ($toRenumber as $idx => $row) {
                        $newSpesimen = $free[$idx] ?? null;
                        if ($newSpesimen === null) {
                            break;
                        }

                        $this->updateKlinikSpesimenNumber(
                            (string) $row->id_permohonan_uji_klinik,
                            (int) $newSpesimen,
                            (string) ($row->noregister_permohonan_uji_klinik ?? ''),
                            false,
                            $now,
                            $year
                        );

                        // Hapus manual junk yang ikut loncat
                        DB::table('tb_permohonan_uji_klinik_2')
                            ->where('id_permohonan_uji_klinik', $row->id_permohonan_uji_klinik)
                            ->update([
                                'nomor_spesimen_manual' => null,
                                'is_nomor_spesimen_manual' => 0,
                                'updated_at' => $now,
                            ]);
                    }
                }
            }

            // 3) lab_number kesmas junk
            if (Schema::hasTable('tb_lab_num') && Schema::hasTable('tb_samples')) {
                $labRows = DB::table('tb_lab_num as ln')
                    ->join('tb_samples as s', 's.id_samples', '=', 'ln.sample_id')
                    ->whereNull('ln.deleted_at')
                    ->whereNull('s.deleted_at')
                    ->where('ln.lab_number', '>=', $threshold)
                    ->get([
                        'ln.id_lab_num',
                        'ln.sample_id',
                        's.codesample_samples',
                        's.count_id',
                    ]);

                foreach ($labRows as $lab) {
                    $fromCode = $this->parseSampleMidNumber((string) ($lab->codesample_samples ?? ''));
                    if ($fromCode < 1 || $fromCode >= $threshold) {
                        continue;
                    }

                    DB::table('tb_lab_num')
                        ->where('id_lab_num', $lab->id_lab_num)
                        ->update([
                            'lab_number' => $fromCode,
                            'updated_at' => $now,
                        ]);

                    $countId = (int) preg_replace('/\D+/', '', (string) ($lab->count_id ?? ''));
                    if ($countId >= $threshold) {
                        DB::table('tb_samples')
                            ->where('id_samples', $lab->sample_id)
                            ->update([
                                'count_id' => str_pad((string) $fromCode, 4, '0', STR_PAD_LEFT),
                                'updated_at' => $now,
                            ]);
                    }
                }
            }

            // 4) Soft-delete booking sequence detail di zona loncatan
            if (Schema::hasTable('global_lab_sequence_detail')) {
                DB::table('global_lab_sequence_detail')
                    ->where('year', $year)
                    ->whereNull('deleted_at')
                    ->where('sequence_number', '>=', $threshold)
                    ->update([
                        'deleted_at' => $now,
                        'updated_at' => $now,
                    ]);
            }

            // 5) Sync counter
            if (class_exists(GlobalLabSequence::class)) {
                GlobalLabSequence::ensureSyncedWithManualSources($year);
            } elseif (Schema::hasTable('global_lab_sequence')) {
                $occupied = $this->occupiedSpesimenNumbers($year, $threshold);
                $maxLive = $occupied === [] ? 0 : (int) max(array_keys($occupied));
                DB::table('global_lab_sequence')
                    ->where('year', $year)
                    ->whereNull('deleted_at')
                    ->update([
                        'last_number' => $maxLive,
                        'updated_at' => $now,
                    ]);
            }
        });
    }

    public function down()
    {
        // no-op — nomor sudah dikoreksi, jangan kembalikan ke 6xxx
    }

    private function resolveFallbackSpesimen(string $noregister, string $nourut): int
    {
        $noreg = trim($noregister);
        if (preg_match('/^(\d{1,6})\b/', $noreg, $m) && (int) $m[1] < self::JUMP_THRESHOLD) {
            return (int) $m[1];
        }
        $n = (int) preg_replace('/\D+/', '', $nourut);
        if ($n > 0 && $n < self::JUMP_THRESHOLD) {
            return $n;
        }

        return 0;
    }

    private function updateKlinikSpesimenNumber(
        string $permohonanId,
        int $newSpesimen,
        string $oldNoregister,
        bool $keepManualRegister,
        string $now,
        int $year
    ): void {
        $newNoregister = (string) $newSpesimen;
        if ($keepManualRegister && strpos($oldNoregister, '/') !== false && !preg_match('/^\d+$/', $oldNoregister)) {
            $newNoregister = preg_replace('/^\d+/', (string) $newSpesimen, $oldNoregister, 1) ?: (string) $newSpesimen;
        }

        DB::table('tb_permohonan_uji_klinik_2')
            ->where('id_permohonan_uji_klinik', $permohonanId)
            ->update([
                'nourut_permohonan_uji_klinik' => $newSpesimen,
                'noregister_permohonan_uji_klinik' => $newNoregister,
                'updated_at' => $now,
            ]);

        if (Schema::hasTable('tb_number_klinik')) {
            DB::table('tb_number_klinik')
                ->where('id_permohonan_uji_klinik', $permohonanId)
                ->whereNull('deleted_at')
                ->update([
                    'new_number' => $newSpesimen,
                    'last_number' => $newSpesimen,
                    'updated_at' => $now,
                ]);
        }

        if (!Schema::hasTable('global_lab_sequence_detail')) {
            return;
        }

        $detail = DB::table('global_lab_sequence_detail')
            ->where('year', $year)
            ->where('lab_type', 'klinik')
            ->where('reference_id', $permohonanId)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->first();

        if ($detail) {
            DB::table('global_lab_sequence_detail')
                ->where('id', $detail->id)
                ->update([
                    'sequence_number' => $newSpesimen,
                    'updated_at' => $now,
                ]);
            return;
        }

        DB::table('global_lab_sequence_detail')->insert([
            'id' => $this->newUuid(),
            'year' => $year,
            'sequence_number' => $newSpesimen,
            'lab_type' => 'klinik',
            'reference_id' => $permohonanId,
            'lab_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);
    }

    private function nextFreeSpesimenNumbers(int $year, int $threshold, int $needed): array
    {
        $occupied = $this->occupiedSpesimenNumbers($year, $threshold);
        $result = [];
        $candidate = 3606;

        while (count($result) < $needed && $candidate < 100000) {
            if (!isset($occupied[$candidate])) {
                $result[] = $candidate;
                $occupied[$candidate] = true;
            }
            $candidate++;
        }

        return $result;
    }

    private function occupiedSpesimenNumbers(int $year, int $threshold): array
    {
        $occupied = [];

        $klinikRows = DB::table('tb_permohonan_uji_klinik_2')
            ->whereNull('deleted_at')
            ->where(function ($q) use ($year) {
                $q->whereYear('tglregister_permohonan_uji_klinik', $year)
                    ->orWhere(function ($q2) use ($year) {
                        $q2->whereNull('tglregister_permohonan_uji_klinik')
                            ->whereYear('created_at', $year);
                    });
            })
            ->get([
                'nourut_permohonan_uji_klinik',
                'noregister_permohonan_uji_klinik',
                'nomor_spesimen_manual',
                'is_nomor_spesimen_manual',
            ]);

        foreach ($klinikRows as $row) {
            if ((int) ($row->is_nomor_spesimen_manual ?? 0) === 1
                && preg_match('/^\d{1,6}$/', trim((string) ($row->nomor_spesimen_manual ?? '')))
            ) {
                $manual = (int) $row->nomor_spesimen_manual;
                if ($manual > 0 && $manual < $threshold) {
                    $occupied[$manual] = true;
                }
            }

            $nourut = (int) preg_replace('/\D+/', '', (string) ($row->nourut_permohonan_uji_klinik ?? ''));
            if ($nourut > 0 && $nourut < $threshold) {
                $occupied[$nourut] = true;
            }

            $noreg = trim((string) ($row->noregister_permohonan_uji_klinik ?? ''));
            if (preg_match('/^(\d{1,6})$/', $noreg, $m) && (int) $m[1] < $threshold) {
                $occupied[(int) $m[1]] = true;
            } elseif (preg_match('/^(\d{1,6})\s*\//', $noreg, $m) && (int) $m[1] < $threshold) {
                $occupied[(int) $m[1]] = true;
            }
        }

        if (Schema::hasTable('tb_samples')) {
            foreach (DB::table('tb_samples')->whereNull('deleted_at')->whereYear('created_at', $year)->whereNotNull('codesample_samples')->pluck('codesample_samples') as $code) {
                $mid = $this->parseSampleMidNumber((string) $code);
                if ($mid > 0 && $mid < $threshold) {
                    $occupied[$mid] = true;
                }
            }
        }

        return $occupied;
    }

    private function parseSampleMidNumber(string $code): int
    {
        if (preg_match('#/(\d{1,6})/\d{4}\s*$#', $code, $m)) {
            return (int) $m[1];
        }

        return 0;
    }

    private function newUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
