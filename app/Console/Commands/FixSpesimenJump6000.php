<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Smt\Masterweb\Models\GlobalLabSequence;

/**
 * Rapikan nomor spesimen salah:
 * - loncat 5000+/6000-an (mis. 6216/6217)
 * - junk nomor_spesimen_manual >6 digit (mis. 25991559)
 *
 * php7.4 artisan spesimen:fix-jump-6000
 * php7.4 artisan spesimen:fix-jump-6000 --dry-run
 */
class FixSpesimenJump6000 extends Command
{
    protected $signature = 'spesimen:fix-jump-6000
                            {--year=2026 : Tahun sekuens}
                            {--threshold=5000 : Ambang loncatan}
                            {--dry-run : Hanya tampilkan rencana, tanpa menulis DB}';

    protected $description = 'Perbaiki nomor spesimen junk/loncat 6000-an dan sync counter';

    public function handle()
    {
        $year = (int) $this->option('year');
        $threshold = (int) $this->option('threshold');
        $dryRun = (bool) $this->option('dry-run');

        if (!Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            $this->error('Tabel tb_permohonan_uji_klinik_2 tidak ada.');
            return 1;
        }

        $high = DB::table('tb_permohonan_uji_klinik_2')
            ->whereNull('deleted_at')
            ->whereRaw('CAST(nourut_permohonan_uji_klinik AS UNSIGNED) >= ?', [$threshold])
            ->orderBy('created_at')
            ->get([
                'id_permohonan_uji_klinik',
                'nourut_permohonan_uji_klinik',
                'noregister_permohonan_uji_klinik',
                'nomor_spesimen_manual',
                'nomor_lab_manual',
                'is_nomor_spesimen_manual',
                'pasien_permohonan_uji_klinik',
                'created_at',
            ]);

        $this->info('Record klinik nourut >= ' . $threshold . ': ' . $high->count());
        foreach ($high as $row) {
            $this->line($this->formatRowLine($row));
        }

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
                'pasien_permohonan_uji_klinik',
            ]);

        $this->info('Junk nomor_spesimen_manual (>6 digit): ' . $junkManuals->count());
        foreach ($junkManuals as $row) {
            $this->line(sprintf(
                '  %s | junk=%s nourut=%s noreg=%s | %s',
                $row->id_permohonan_uji_klinik,
                $row->nomor_spesimen_manual,
                $row->nourut_permohonan_uji_klinik,
                $row->noregister_permohonan_uji_klinik,
                $this->pasienName($row->pasien_permohonan_uji_klinik ?? null)
            ));
        }

        if ($dryRun) {
            $this->warn('Dry-run: tidak ada perubahan DB.');
            return 0;
        }

        DB::transaction(function () use ($year, $threshold, $high, $junkManuals) {
            $now = now()->format('Y-m-d H:i:s');

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
                $this->line('  junk ' . $row->nomor_spesimen_manual . ' → ' . ($fallback > 0 ? $fallback : '(null)'));
            }

            $manualRows = $high->filter(function ($row) use ($threshold) {
                $manual = trim((string) ($row->nomor_spesimen_manual ?? ''));
                if ((int) ($row->is_nomor_spesimen_manual ?? 0) !== 1) {
                    return false;
                }
                if (!preg_match('/^\d{1,6}$/', $manual)) {
                    return false;
                }
                // Manual yang ikut loncat (>= threshold) dianggap junk → renumber otomatis
                if ((int) $manual >= $threshold) {
                    return false;
                }

                return true;
            });

            foreach ($manualRows as $row) {
                $spesimen = (int) $row->nomor_spesimen_manual;
                $this->updateKlinikSpesimenNumber(
                    (string) $row->id_permohonan_uji_klinik,
                    $spesimen,
                    (string) ($row->noregister_permohonan_uji_klinik ?? ''),
                    true,
                    $now,
                    $year
                );
                $this->line("  manual {$row->nourut_permohonan_uji_klinik} → {$spesimen}");
            }

            $autoRows = $high->filter(function ($row) use ($manualRows) {
                return !$manualRows->contains('id_permohonan_uji_klinik', $row->id_permohonan_uji_klinik);
            })->values();

            if ($autoRows->isNotEmpty()) {
                $free = $this->nextFreeSpesimenNumbers($year, $threshold, $autoRows->count());
                foreach ($autoRows as $idx => $row) {
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
                    // Bersihkan flag/manual junk yang ikut loncat
                    DB::table('tb_permohonan_uji_klinik_2')
                        ->where('id_permohonan_uji_klinik', $row->id_permohonan_uji_klinik)
                        ->update([
                            'nomor_spesimen_manual' => null,
                            'is_nomor_spesimen_manual' => 0,
                            'updated_at' => $now,
                        ]);
                    $this->line("  auto {$row->nourut_permohonan_uji_klinik} → {$newSpesimen}");
                }
            }

            if (Schema::hasTable('tb_lab_num') && Schema::hasTable('tb_samples')) {
                $labRows = DB::table('tb_lab_num as ln')
                    ->join('tb_samples as s', 's.id_samples', '=', 'ln.sample_id')
                    ->whereNull('ln.deleted_at')
                    ->whereNull('s.deleted_at')
                    ->where('ln.lab_number', '>=', $threshold)
                    ->get(['ln.id_lab_num', 'ln.sample_id', 's.codesample_samples', 's.count_id']);

                foreach ($labRows as $lab) {
                    $fromCode = $this->parseSampleMidNumber((string) ($lab->codesample_samples ?? ''));
                    if ($fromCode < 1 || $fromCode >= $threshold) {
                        continue;
                    }
                    DB::table('tb_lab_num')->where('id_lab_num', $lab->id_lab_num)->update([
                        'lab_number' => $fromCode,
                        'updated_at' => $now,
                    ]);
                    $countId = (int) preg_replace('/\D+/', '', (string) ($lab->count_id ?? ''));
                    if ($countId >= $threshold) {
                        DB::table('tb_samples')->where('id_samples', $lab->sample_id)->update([
                            'count_id' => str_pad((string) $fromCode, 4, '0', STR_PAD_LEFT),
                            'updated_at' => $now,
                        ]);
                    }
                    $this->line("  lab_num junk → {$fromCode} ({$lab->codesample_samples})");
                }
            }

            if (Schema::hasTable('global_lab_sequence_detail')) {
                $deleted = DB::table('global_lab_sequence_detail')
                    ->where('year', $year)
                    ->whereNull('deleted_at')
                    ->where('sequence_number', '>=', $threshold)
                    ->update([
                        'deleted_at' => $now,
                        'updated_at' => $now,
                    ]);
                $this->line("  soft-delete sequence_detail >= {$threshold}: {$deleted}");
            }
        });

        $this->syncSequence($year);

        $left = DB::table('tb_permohonan_uji_klinik_2')
            ->whereNull('deleted_at')
            ->whereRaw('CAST(nourut_permohonan_uji_klinik AS UNSIGNED) >= ?', [$threshold])
            ->count();

        $this->info('Selesai. Sisa nourut >= ' . $threshold . ': ' . $left);
        $this->info('last_number: ' . DB::table('global_lab_sequence')->where('year', $year)->value('last_number'));

        return 0;
    }

    private function formatRowLine($row): string
    {
        return sprintf(
            '  %s | n=%s reg=%s lab=%s man=%s | %s',
            $row->id_permohonan_uji_klinik,
            $row->nourut_permohonan_uji_klinik,
            $row->noregister_permohonan_uji_klinik,
            $row->nomor_lab_manual ?? '',
            $row->is_nomor_spesimen_manual ?? '',
            $this->pasienName($row->pasien_permohonan_uji_klinik ?? null)
        );
    }

    private function pasienName($pasienId): string
    {
        if (empty($pasienId) || !Schema::hasTable('ms_pasien')) {
            return '';
        }

        return (string) DB::table('ms_pasien')->where('id_pasien', $pasienId)->value('nama_pasien');
    }

    private function resolveFallbackSpesimen(string $noregister, string $nourut): int
    {
        $noreg = trim($noregister);
        if (preg_match('/^(\d{1,6})\b/', $noreg, $m)) {
            return (int) $m[1];
        }
        $n = (int) preg_replace('/\D+/', '', $nourut);
        if ($n > 0 && $n <= 999999) {
            return $n;
        }

        return 0;
    }

    private function syncSequence(int $year): void
    {
        if (class_exists(GlobalLabSequence::class)) {
            GlobalLabSequence::ensureSyncedWithManualSources($year);
            return;
        }

        if (!Schema::hasTable('global_lab_sequence')) {
            return;
        }

        $occupied = $this->occupiedSpesimenNumbers($year, 5000);
        $maxLive = $occupied === [] ? 0 : (int) max(array_keys($occupied));
        DB::table('global_lab_sequence')
            ->where('year', $year)
            ->whereNull('deleted_at')
            ->update([
                'last_number' => $maxLive,
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ]);
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
                $occupied[(int) $row->nomor_spesimen_manual] = true;
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
