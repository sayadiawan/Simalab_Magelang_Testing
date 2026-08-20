<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Smt\Masterweb\Exports\ReportJumlahJenisSampelExport;
use Smt\Masterweb\Helpers\Smt;
use Smt\Masterweb\Models\PengambilanSampleKlinik;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;

/**
 * Rekapan bulanan jumlah sampel per jenis:
 * Klinis | Haji | Mikrobiologi (Air Bersih/Minum/Limbah/Kolam, MM, Usap, Udara) | Kimia
 */
class ReportJumlahJenisSampelController extends Controller
{
    private const COLUMN_KEYS = [
        'klinis_darah',
        'klinis_urine',
        'klinis_feses',
        'haji_darah',
        'haji_urine',
        'haji_feses',
        'mikro_air_bersih',
        'mikro_air_minum',
        'mikro_air_limbah',
        'mikro_kolam',
        'mikro_mm',
        'mikro_usap',
        'mikro_udara',
        'kimia_air_bersih',
        'kimia_air_minum',
        'kimia_air_limbah',
        'kimia_kolam',
        'kimia_mm',
    ];

    public function index(Request $request)
    {
        $month = (int) $request->get('month', date('m'));
        $year = (int) $request->get('year', date('Y'));
        $showEmptyDays = $request->get('show_empty', '0') === '1';

        $payload = $this->buildReport($month, $year, $showEmptyDays);

        return view('masterweb::module.admin.laboratorium.report.jumlah-jenis-sampel.index', array_merge($payload, [
            'month' => sprintf('%02d', $month),
            'year' => $year,
            'showEmptyDays' => $showEmptyDays,
        ]));
    }

    public function export(Request $request)
    {
        $month = (int) $request->get('month', date('m'));
        $year = (int) $request->get('year', date('Y'));
        $showEmptyDays = $request->get('show_empty', '0') === '1';

        $payload = $this->buildReport($month, $year, $showEmptyDays);
        $bulanNama = function_exists('fbulan') ? fbulan(sprintf('%02d', $month)) : Carbon::create($year, $month, 1)->translatedFormat('F');
        $filename = 'Rekapan_Jumlah_Jenis_Sampel_' . $bulanNama . '_' . $year . '.xlsx';

        return Excel::download(
            new ReportJumlahJenisSampelExport($payload['rows'], $payload['totals'], $payload['komulatif'], $month, $year),
            $filename
        );
    }

    private function buildReport(int $month, int $year, bool $showEmptyDays): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $byDate = [];
        for ($d = 1; $d <= $end->day; $d++) {
            $key = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $byDate[$key] = $this->emptyDayCounts();
        }

        $this->accumulateKlinikCounts($byDate, $start, $end);
        $this->accumulateNonKlinikCounts($byDate, $start, $end);

        $rows = [];
        $no = 1;
        $totals = $this->emptyDayCounts();

        foreach ($byDate as $dateKey => $counts) {
            $dayTotal = array_sum($counts);
            if (!$showEmptyDays && $dayTotal === 0) {
                continue;
            }

            $carbon = Carbon::parse($dateKey)->locale('id');
            $rows[] = [
                'no' => $no++,
                'date' => $dateKey,
                'tanggal' => $carbon->translatedFormat('d F Y'),
                'counts' => $counts,
                'row_total' => $dayTotal,
            ];

            foreach (self::COLUMN_KEYS as $col) {
                $totals[$col] += $counts[$col];
            }
        }

        $komulatif = array_sum($totals);

        return [
            'rows' => $rows,
            'totals' => $totals,
            'komulatif' => $komulatif,
            'bulanNama' => $start->locale('id')->translatedFormat('F Y'),
            'columns' => self::COLUMN_KEYS,
        ];
    }

    private function emptyDayCounts(): array
    {
        $counts = [];
        foreach (self::COLUMN_KEYS as $key) {
            $counts[$key] = 0;
        }

        return $counts;
    }

    private function accumulateKlinikCounts(array &$byDate, Carbon $start, Carbon $end): void
    {
        $permohonanList = PermohonanUjiKlinik2::query()
            ->whereNull('deleted_at')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween(DB::raw('DATE(tglregister_permohonan_uji_klinik)'), [
                    $start->format('Y-m-d'),
                    $end->format('Y-m-d'),
                ])->orWhere(function ($q2) use ($start, $end) {
                    $q2->whereNull('tglregister_permohonan_uji_klinik')
                        ->whereBetween(DB::raw('DATE(created_at)'), [
                            $start->format('Y-m-d'),
                            $end->format('Y-m-d'),
                        ]);
                });
            })
            ->get([
                'id_permohonan_uji_klinik',
                'tglregister_permohonan_uji_klinik',
                'created_at',
                'is_haji',
                'id_permohonan_uji_klinik_haji',
            ]);

        if ($permohonanList->isEmpty()) {
            return;
        }

        $ids = $permohonanList->pluck('id_permohonan_uji_klinik')->all();
        $pengambilanByPermohonan = PengambilanSampleKlinik::whereIn('permohonan_uji_klinik_id', $ids)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('permohonan_uji_klinik_id');

        foreach ($permohonanList as $permohonan) {
            $dateRaw = $permohonan->tglregister_permohonan_uji_klinik ?: $permohonan->created_at;
            if (!$dateRaw) {
                continue;
            }
            $dateKey = Carbon::parse($dateRaw)->format('Y-m-d');
            if (!isset($byDate[$dateKey])) {
                continue;
            }

            $pengambilan = ($pengambilanByPermohonan->get($permohonan->id_permohonan_uji_klinik) ?? collect())->first();
            $jenisList = $this->resolveKlinikJenisSampel($permohonan->id_permohonan_uji_klinik, $pengambilan);

            $isHaji = (int) ($permohonan->is_haji ?? 0) === 1
                || !empty($permohonan->id_permohonan_uji_klinik_haji);
            $prefix = $isHaji ? 'haji_' : 'klinis_';

            $flags = [
                $prefix . 'darah' => false,
                $prefix . 'urine' => false,
                $prefix . 'feses' => false,
            ];

            foreach ($jenisList as $jenis) {
                $normalized = mb_strtolower(trim((string) $jenis));
                if ($normalized === '') {
                    continue;
                }
                if (str_contains($normalized, 'darah') || str_contains($normalized, 'serum') || str_contains($normalized, 'plasma')) {
                    $flags[$prefix . 'darah'] = true;
                }
                if (str_contains($normalized, 'urine') || str_contains($normalized, 'urin')) {
                    $flags[$prefix . 'urine'] = true;
                }
                if (str_contains($normalized, 'feses') || str_contains($normalized, 'faeces') || str_contains($normalized, 'tinja')) {
                    $flags[$prefix . 'feses'] = true;
                }
            }

            foreach ($flags as $col => $on) {
                if ($on) {
                    $byDate[$dateKey][$col]++;
                }
            }
        }
    }

    private function resolveKlinikJenisSampel(string $permohonanId, $pengambilan): array
    {
        $jenisSampel = [];

        if ($pengambilan && !empty($pengambilan->jenis_sample)) {
            $raw = $pengambilan->jenis_sample;
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $jenisSampel = $decoded;
                } else {
                    $jenisSampel = [$raw];
                }
            } elseif (is_array($raw)) {
                $jenisSampel = $raw;
            }
        }

        if (empty($jenisSampel)) {
            try {
                $fromHelper = Smt::getJenisSampelFromParameter($permohonanId, null);
                if (is_array($fromHelper)) {
                    $jenisSampel = $fromHelper;
                }
            } catch (\Throwable $e) {
                $jenisSampel = [];
            }
        }

        return array_values(array_filter(array_map('strval', $jenisSampel)));
    }

    private function accumulateNonKlinikCounts(array &$byDate, Carbon $start, Carbon $end): void
    {
        $rows = DB::table('tb_samples as s')
            ->join('tb_sample_method as sm', function ($join) {
                $join->on('sm.sample_id', '=', 's.id_samples')
                    ->whereNull('sm.deleted_at');
            })
            ->join('ms_laboratorium as l', function ($join) {
                $join->on('l.id_laboratorium', '=', 'sm.laboratorium_id')
                    ->whereNull('l.deleted_at');
            })
            ->join('ms_sample_type as st', function ($join) {
                $join->on('st.id_sample_type', '=', 's.typesample_samples')
                    ->whereNull('st.deleted_at');
            })
            ->whereNull('s.deleted_at')
            ->whereIn('l.kode_laboratorium', ['KIM', 'MBI'])
            ->whereBetween(DB::raw('DATE(COALESCE(s.date_sending, s.created_at))'), [
                $start->format('Y-m-d'),
                $end->format('Y-m-d'),
            ])
            ->groupBy(
                DB::raw('DATE(COALESCE(s.date_sending, s.created_at))'),
                'l.kode_laboratorium',
                'st.code_sample_type'
            )
            ->select([
                DB::raw('DATE(COALESCE(s.date_sending, s.created_at)) as tanggal'),
                'l.kode_laboratorium',
                'st.code_sample_type',
                DB::raw('COUNT(DISTINCT s.id_samples) as jumlah'),
            ])
            ->get();

        foreach ($rows as $row) {
            $dateKey = Carbon::parse($row->tanggal)->format('Y-m-d');
            if (!isset($byDate[$dateKey])) {
                continue;
            }

            $col = $this->mapNonKlinikColumn((string) $row->kode_laboratorium, (string) $row->code_sample_type);
            if ($col === null) {
                continue;
            }

            $byDate[$dateKey][$col] += (int) $row->jumlah;
        }
    }

    private function mapNonKlinikColumn(string $labCode, string $sampleTypeCode): ?string
    {
        $code = strtoupper(trim($sampleTypeCode));
        $lab = strtoupper(trim($labCode));

        $airKey = $this->mapAirSampleTypeKey($code);
        $isMm = ($code === 'MM');
        $isUsap = ($code === 'UA');
        $isUdara = ($code === 'KU');

        if ($lab === 'MBI') {
            if ($airKey !== null) {
                return 'mikro_' . $airKey;
            }
            if ($isMm) {
                return 'mikro_mm';
            }
            if ($isUsap) {
                return 'mikro_usap';
            }
            if ($isUdara) {
                return 'mikro_udara';
            }
        }

        if ($lab === 'KIM') {
            if ($airKey !== null) {
                return 'kimia_' . $airKey;
            }
            if ($isMm) {
                return 'kimia_mm';
            }
        }

        return null;
    }

    /**
     * Mapping kode jenis sampel air.
     * AH/AB = Air Bersih (Higiene), AM = Air Minum, AL = Air Limbah, AKR = Kolam Renang.
     */
    private function mapAirSampleTypeKey(string $code): ?string
    {
        if (in_array($code, ['AH', 'AB'], true)) {
            return 'air_bersih';
        }
        if ($code === 'AM') {
            return 'air_minum';
        }
        if ($code === 'AL') {
            return 'air_limbah';
        }
        if ($code === 'AKR') {
            return 'kolam';
        }

        return null;
    }
}
