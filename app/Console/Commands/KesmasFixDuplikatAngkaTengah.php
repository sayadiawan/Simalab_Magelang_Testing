<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Smt\Masterweb\Helpers\NomorChangeLogger;
use Smt\Masterweb\Models\GlobalLabSequence;

class KesmasFixDuplikatAngkaTengah extends Command
{
    protected $signature = 'kesmas:fix-duplikat-angka-tengah
                            {--year=2026 : Tahun nomor spesimen}
                            {--from=2026-08-12 : Tanggal mulai kesmas yang dirapikan (date_sending)}
                            {--dry-run : Tampilkan rencana tanpa menulis database}
                            {--force : Terapkan tanpa konfirmasi}';

    protected $description = 'Naikkan nomor sampel kesmas bentrok/duplikat tanpa mengubah nomor klinik & klinik haji';

    public function handle(): int
    {
        $year = (int) $this->option('year');
        $from = trim((string) $this->option('from'));
        $dryRun = (bool) $this->option('dry-run');

        if ($year < 2000 || $year > 2100 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $this->error('Tahun atau tanggal --from tidak valid (YYYY-MM-DD).');

            return 1;
        }

        $assigned = $this->collectReservedNumbers($year, $from);

        $samples = DB::table('tb_samples')
            ->whereNull('deleted_at')
            ->whereYear('date_sending', $year)
            ->whereDate('date_sending', '>=', $from)
            ->where(function ($q) {
                $q->where('is_nomor_sampel_manual', 0)
                    ->orWhereNull('is_nomor_sampel_manual');
            })
            ->orderBy('date_sending')
            ->orderBy('created_at')
            ->orderBy('id_samples')
            ->get([
                'id_samples',
                'codesample_samples',
                'count_id',
                'date_sending',
                'created_at',
            ]);

        $plan = [];
        foreach ($samples as $sample) {
            $want = (int) $sample->count_id;
            if ($want < 1) {
                $want = $this->extractMiddleNumber((string) $sample->codesample_samples);
            }
            if ($want < 1) {
                continue;
            }

            if (!isset($assigned[$want])) {
                $new = $want;
            } else {
                $new = $this->nextFreeFrom($assigned, $want + 1);
            }

            $assigned[$new] = true;

            if ($new === $want) {
                continue;
            }

            $oldCode = (string) $sample->codesample_samples;
            $newCode = $this->rebuildSampleCode($oldCode, $new);

            $plan[] = [
                'id_samples' => $sample->id_samples,
                'old_code' => $oldCode,
                'new_code' => $newCode,
                'old_count' => $want,
                'new_count' => $new,
                'date_sending' => $sample->date_sending,
            ];
        }

        if (empty($plan)) {
            $this->info("Tidak ada nomor kesmas yang perlu disesuaikan (mulai {$from}).");

            return 0;
        }

        $this->info(($dryRun ? '[DRY-RUN] ' : '') . 'Rencana ubah ' . count($plan) . ' sampel kesmas:');
        foreach ($plan as $row) {
            $this->line(sprintf(
                '  %s | %d -> %d | %s -> %s',
                substr((string) $row['date_sending'], 0, 10),
                $row['old_count'],
                $row['new_count'],
                $row['old_code'],
                $row['new_code']
            ));
        }

        if ($dryRun) {
            $this->comment('Jalankan tanpa --dry-run untuk menerapkan perubahan.');

            return 0;
        }

        if (!$dryRun && !$this->option('force') && !$this->confirm('Terapkan ' . count($plan) . ' perubahan nomor sampel kesmas?', true)) {
            $this->info('Dibatalkan.');

            return 0;
        }

        DB::transaction(function () use ($plan, $year) {
            foreach ($plan as $row) {
                DB::table('tb_samples')
                    ->where('id_samples', $row['id_samples'])
                    ->update([
                        'count_id' => $row['new_count'],
                        'codesample_samples' => $row['new_code'],
                        'updated_at' => now(),
                    ]);

                NomorChangeLogger::record([
                    'subject_type' => 'sample',
                    'subject_id' => $row['id_samples'],
                    'field_name' => 'codesample_samples',
                    'old_value' => $row['old_code'],
                    'new_value' => $row['new_code'],
                    'event' => 'perbaikan_duplikat_kesmas',
                    'source' => 'kesmas:fix-duplikat-angka-tengah',
                    'note' => 'Angka tengah ' . $row['old_count'] . ' -> ' . $row['new_count']
                        . ' (hindari bentrok klinik/haji)',
                ]);
            }

            GlobalLabSequence::ensureSyncedWithManualSources($year, true);
        });

        $this->info('Selesai. Nomor klinik & klinik haji tidak diubah.');
        $this->line('Counter global_lab_sequence diselaraskan ke max nomor hidup tahun ' . $year . '.');

        return 0;
    }

    /**
     * Nomor yang sudah dipakai klinik, haji, dan kesmas sebelum --from (tidak disentuh).
     *
     * @return array<int, true>
     */
    private function collectReservedNumbers(int $year, string $from): array
    {
        $assigned = [];

        $klinikYear = function ($q) use ($year) {
            $q->whereYear('tglregister_permohonan_uji_klinik', $year)
                ->orWhere(function ($q2) use ($year) {
                    $q2->whereNull('tglregister_permohonan_uji_klinik')
                        ->whereYear('created_at', $year);
                });
        };

        $klinikRows = DB::table('tb_permohonan_uji_klinik_2')
            ->whereNull('deleted_at')
            ->where($klinikYear)
            ->get([
                'nourut_permohonan_uji_klinik',
                'noregister_permohonan_uji_klinik',
                'nomor_spesimen_manual',
            ]);

        foreach ($klinikRows as $row) {
            $this->markNumber($assigned, (int) $row->nourut_permohonan_uji_klinik);
            $this->markNumber($assigned, $this->extractLeadingNumber((string) $row->noregister_permohonan_uji_klinik));
            $this->markNumber($assigned, $this->extractLeadingNumber((string) $row->nomor_spesimen_manual));
        }

        $oldKesmas = DB::table('tb_samples')
            ->whereNull('deleted_at')
            ->whereYear('date_sending', $year)
            ->whereDate('date_sending', '<', $from)
            ->where(function ($q) {
                $q->where('is_nomor_sampel_manual', 0)
                    ->orWhereNull('is_nomor_sampel_manual');
            })
            ->get(['count_id', 'codesample_samples']);

        foreach ($oldKesmas as $row) {
            $n = (int) $row->count_id;
            if ($n < 1) {
                $n = $this->extractMiddleNumber((string) $row->codesample_samples);
            }
            $this->markNumber($assigned, $n);
        }

        return $assigned;
    }

    /**
     * @param  array<int, true>  $assigned
     */
    private function markNumber(array &$assigned, int $number): void
    {
        if ($number > 0 && $number <= 999999) {
            $assigned[$number] = true;
        }
    }

    /**
     * @param  array<int, true>  $assigned
     */
    private function nextFreeFrom(array $assigned, int $start): int
    {
        $n = max(1, $start);
        while (isset($assigned[$n])) {
            $n++;
        }

        return $n;
    }

    private function extractMiddleNumber(string $code): int
    {
        if (preg_match('#/(\d{1,6})/\d{4}\s*$#', $code, $m)) {
            return (int) $m[1];
        }

        return 0;
    }

    private function extractLeadingNumber(string $value): int
    {
        if (preg_match('/^(\d{1,6})/', trim($value), $m)) {
            return (int) $m[1];
        }

        return 0;
    }

    private function rebuildSampleCode(string $code, int $newNum): string
    {
        if (preg_match('#^(.+/)(\d{1,6})(/\d{4}\s*)$#', $code, $m)) {
            $padLen = max(4, strlen($m[2]));

            return $m[1] . str_pad((string) $newNum, $padLen, '0', STR_PAD_LEFT) . $m[3];
        }

        return $code;
    }
}
