<?php

namespace App\Console\Commands;

use App\Exports\KesmasDuplikatAngkaTengahExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class KesmasExportDuplikatAngkaTengah extends Command
{
    protected $signature = 'kesmas:export-duplikat-angka-tengah
                            {--year=2026 : Tahun pengiriman sampel (date_sending)}
                            {--from= : Tanggal mulai (YYYY-MM-DD), filter date_sending}
                            {--to= : Tanggal akhir (YYYY-MM-DD), filter date_sending}
                            {--output= : Path file keluaran (.xlsx). Default: storage/app/exports/}';

    protected $description = 'Export Excel: sampel kesmas yang angka tengah (count_id) sama dalam satu tahun';

    public function handle(): int
    {
        $year = (int) $this->option('year');
        if ($year < 2000 || $year > 2100) {
            $this->error('Tahun tidak valid.');

            return 1;
        }

        $from = $this->option('from') ? trim((string) $this->option('from')) : null;
        $to = $this->option('to') ? trim((string) $this->option('to')) : null;

        $scope = function ($query, string $prefix = '') use ($year, $from, $to) {
            $p = $prefix !== '' ? $prefix . '.' : '';
            $query->whereNull($p . 'deleted_at')
                ->whereYear($p . 'date_sending', $year);

            if ($from) {
                $query->whereDate($p . 'date_sending', '>=', $from);
            }
            if ($to) {
                $query->whereDate($p . 'date_sending', '<=', $to);
            }
        };

        $dupQuery = DB::table('tb_samples')
            ->whereNotNull('count_id');
        $scope($dupQuery);

        $dupCounts = $dupQuery
            ->groupBy('count_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck(DB::raw('COUNT(*)'), 'count_id');

        if ($dupCounts->isEmpty()) {
            $range = $this->formatRangeLabel($from, $to);
            $this->info("Tidak ada duplikat angka tengah untuk tahun {$year}{$range}.");

            return 0;
        }

        $exactQuery = DB::table('tb_samples')
            ->whereNotNull('codesample_samples')
            ->where('codesample_samples', '!=', '');
        $scope($exactQuery);

        $exactDupCodes = $exactQuery
            ->groupBy('codesample_samples')
            ->havingRaw('COUNT(*) > 1')
            ->pluck(DB::raw('COUNT(*)'), 'codesample_samples');

        $dupTypeQuery = DB::table('tb_samples as s')
            ->join('ms_sample_type as st', 'st.id_sample_type', '=', 's.typesample_samples');
        $scope($dupTypeQuery, 's');

        $dupTypePairs = $dupTypeQuery
            ->groupBy('s.count_id', 'st.code_sample_type')
            ->havingRaw('COUNT(*) > 1')
            ->select('s.count_id', 'st.code_sample_type')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->count_id . '|' . $r->code_sample_type => true]);

        $samplesQuery = DB::table('tb_samples as s')
            ->join('ms_sample_type as st', 'st.id_sample_type', '=', 's.typesample_samples')
            ->leftJoin('tb_permohonan_uji as p', 'p.id_permohonan_uji', '=', 's.permohonan_uji_id')
            ->leftJoin('ms_customer as c', 'c.id_customer', '=', 'p.customer_id')
            ->whereIn('s.count_id', $dupCounts->keys()->all());

        $scope($samplesQuery, 's');

        $samples = $samplesQuery
            ->orderBy('s.count_id')
            ->orderBy('st.code_sample_type')
            ->orderBy('s.codesample_samples')
            ->orderBy('s.created_at')
            ->get([
                's.count_id',
                's.codesample_samples',
                'st.code_sample_type',
                'st.name_sample_type',
                's.datesampling_samples',
                's.created_at',
                's.name_pelanggan',
                'c.name_customer',
                's.titik_pengambilan',
                'p.code_permohonan_uji',
            ]);

        $rows = [];
        $no = 0;
        foreach ($samples as $s) {
            $no++;
            $code = (string) ($s->codesample_samples ?? '');
            $labPrefix = '-';
            if (preg_match('/\.(\d{2})\//', $code, $m)) {
                $labPrefix = $m[1];
            }

            $pelanggan = trim((string) ($s->name_pelanggan ?? ''));
            if ($pelanggan === '') {
                $pelanggan = trim((string) ($s->name_customer ?? ''));
            }
            if ($pelanggan === '') {
                $pelanggan = '-';
            }

            $typeKey = (int) $s->count_id . '|' . (string) $s->code_sample_type;

            $rows[] = [
                $no,
                $year,
                (int) $s->count_id,
                (int) ($dupCounts[(int) $s->count_id] ?? 0),
                $code !== '' ? $code : '-',
                (string) ($s->code_sample_type ?? '-'),
                (string) ($s->name_sample_type ?? '-'),
                $labPrefix,
                $s->datesampling_samples ? substr((string) $s->datesampling_samples, 0, 10) : '-',
                $s->created_at ? substr((string) $s->created_at, 0, 16) : '-',
                $pelanggan,
                (string) ($s->titik_pengambilan ?? '-'),
                (string) ($s->code_permohonan_uji ?? '-'),
                isset($exactDupCodes[$code]) ? 'Ya (' . (int) $exactDupCodes[$code] . ' baris)' : 'Tidak',
                isset($dupTypePairs[$typeKey]) ? 'Ya' : 'Tidak',
            ];
        }

        $dir = storage_path('app/exports');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $rangeSlug = '';
        if ($from) {
            $rangeSlug .= '-dari-' . str_replace('-', '', $from);
        }
        if ($to) {
            $rangeSlug .= '-sampai-' . str_replace('-', '', $to);
        }

        $filename = 'duplikat-angka-tengah-kesmas-' . $year . $rangeSlug . '-' . date('Ymd-His') . '.xlsx';
        $relative = 'exports/' . $filename;
        $output = $this->option('output') ?: storage_path('app/' . $relative);

        $sheetLabel = $year . $this->formatRangeLabel($from, $to);

        Excel::store(
            new KesmasDuplikatAngkaTengahExport($rows, $year, $sheetLabel),
            $relative,
            'local',
            \Maatwebsite\Excel\Excel::XLSX
        );

        if ($this->option('output') && $output !== storage_path('app/' . $relative)) {
            copy(storage_path('app/' . $relative), $output);
        } else {
            $output = storage_path('app/' . $relative);
        }

        $this->info('Duplikat angka tengah tahun ' . $year . $this->formatRangeLabel($from, $to) . ':');
        $this->line('  - Nomor angka tengah duplikat: ' . $dupCounts->count());
        $this->line('  - Total baris sampel terlibat: ' . count($rows));
        $this->line('  - Kode nomor persis duplikat: ' . $exactDupCodes->count() . ' kode');
        $this->info('File: ' . $output);

        return 0;
    }

    private function formatRangeLabel(?string $from, ?string $to): string
    {
        if ($from && $to) {
            return " ({$from} s/d {$to})";
        }
        if ($from) {
            return " (mulai {$from})";
        }
        if ($to) {
            return " (s/d {$to})";
        }

        return '';
    }
}
