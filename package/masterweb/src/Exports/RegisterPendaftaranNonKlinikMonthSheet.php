<?php

namespace Smt\Masterweb\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Smt\Masterweb\Models\Sample;
use Smt\Masterweb\Models\SampleMethod;
use Smt\Masterweb\Helpers\KesmasNotaHelper;

class RegisterPendaftaranNonKlinikMonthSheet implements FromArray, WithTitle, WithEvents
{
  const LAST_COL = 'K';

  /** @var array */
  protected $dataRows;

  /** @var int */
  protected $dataStartRow = 6;

  /** @var int */
  protected $year;

  /** @var int */
  protected $month;

  /** @var string */
  protected $laboratoriumId;

  /** @var string */
  protected $unitLabel;

  /** @var string|null Filter satu hari (Y-m-d), untuk laporan harian */
  protected $filterDate;

  public function __construct($year, $month, $laboratoriumId, $unitLabel, $filterDate = null)
  {
    $this->year = (int) $year;
    $this->month = (int) $month;
    $this->laboratoriumId = (string) $laboratoriumId;
    $this->unitLabel = (string) $unitLabel;
    $this->filterDate = ($filterDate !== null && $filterDate !== '') ? (string) $filterDate : null;
    $this->dataRows = $this->loadDataRows();
  }

  public function title(): string
  {
    if ($this->filterDate !== null) {
      try {
        return Carbon::parse($this->filterDate)->format('d-m-Y');
      } catch (\Throwable $e) {
        return (string) $this->filterDate;
      }
    }

    $names = [
      1 => 'Januari',
      2 => 'Februari',
      3 => 'Maret',
      4 => 'April',
      5 => 'Mei',
      6 => 'Juni',
      7 => 'Juli',
      8 => 'Agustus',
      9 => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember',
    ];

    return isset($names[$this->month]) ? $names[$this->month] : (string) $this->month;
  }

  protected function periodHeaderLabel(): string
  {
    if ($this->filterDate !== null) {
      return 'TANGGAL : ' . $this->formatTanggal($this->filterDate);
    }

    return 'BULAN : ' . $this->monthYearLabel();
  }

  public function array(): array
  {
    // Baris 1–3: judul; baris 4–5: header tabel (sertifikat register seperti lembar fisik)
    $rows = [
      ['REGISTER PENDAFTARAN'],
      ['Unit ' . $this->unitLabel],
      [$this->periodHeaderLabel()],
      [
        'NO',
        'TANGGAL',
        'NOMOR SAMPEL',
        'ASAL SAMPEL',
        'Alamat',
        'JENIS SAMPEL',
        'PENGIRIM SAMPEL',
        'UNIT PEMERIKSA',
        'PARAMETER PEMERIKSAAN',
        'TARIF',
        'KET',
      ],
      [
        '', '', '', '', '', '', '', '', '', '', '',
      ],
    ];

    // Group rows by tanggal_raw so we can blank out repeated dates
    $no = 1;
    $prevTanggalRaw = null;
    foreach ($this->dataRows as $line) {
      $showTanggal = ($line['tanggal_raw'] !== $prevTanggalRaw)
        ? $line['tanggal']
        : '';
      $prevTanggalRaw = $line['tanggal_raw'];

      $rows[] = [
        $no++,
        $showTanggal,
        $line['nomor_sampel'],
        $line['asal'],
        $line['alamat'],
        $line['jenis'],
        $line['pengirim'],
        $line['unit'],
        $line['parameter'],
        $line['tarif'],
        $line['ket'],
      ];
    }

    return $rows;
  }

  public function registerEvents(): array
  {
    $dataStartRow = $this->dataStartRow;
    $lastCol = self::LAST_COL;

    return [
      AfterSheet::class => function (AfterSheet $event) use ($dataStartRow, $lastCol) {
        $sheet = $event->sheet->getDelegate();
        $highestRow = (int) $sheet->getHighestRow();

        // ── Title rows merge ────────────────────────────────────────────────
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->mergeCells('A3:' . $lastCol . '3');

        // ── Header row merges (baris 4–5) ─────────────────────────────────────
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'] as $col) {
          $sheet->mergeCells($col . '4:' . $col . '5');
        }

        // ── Title rows style ─────────────────────────────────────────────────
        for ($r = 1; $r <= 3; $r++) {
          $sheet->getStyle('A' . $r . ':' . $lastCol . $r)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
          $sheet->getStyle('A' . $r . ':' . $lastCol . $r)->getFont()->setBold(true);
        }

        // ── Header style ─────────────────────────────────────────────────────
        $headerRange = 'A4:' . $lastCol . '5';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_CENTER)
          ->setVertical(Alignment::VERTICAL_CENTER)
          ->setWrapText(true);
        $sheet->getRowDimension(4)->setRowHeight(36);
        $sheet->getRowDimension(5)->setRowHeight(28);

        // ── Column widths ────────────────────────────────────────────────────
        $widths = [
          'A' => 5,
          'B' => 14,  // wider to fit "04 Mei 2026"
          'C' => 18,
          'D' => 20,
          'E' => 24,
          'F' => 11,
          'G' => 11,
          'H' => 11,
          'I' => 26,
          'J' => 13,
          'K' => 8,
        ];
        foreach ($widths as $col => $w) {
          $sheet->getColumnDimension($col)->setWidth($w)->setAutoSize(false);
        }

        // ── Data rows alignment ──────────────────────────────────────────────
        if ($highestRow >= $dataStartRow) {
          $sheet->getStyle('A' . $dataStartRow . ':A' . $highestRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('B' . $dataStartRow . ':B' . $highestRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
          $sheet->getStyle('C' . $dataStartRow . ':C' . $highestRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('D' . $dataStartRow . ':E' . $highestRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);
          $sheet->getStyle('F' . $dataStartRow . ':I' . $highestRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);
          $sheet->getStyle('J' . $dataStartRow . ':J' . $highestRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Alignment::VERTICAL_TOP);
          $sheet->getStyle('K' . $dataStartRow . ':K' . $highestRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_TOP);
        }

        // ── Merge tanggal (col B) for same-date groups ───────────────────────
        // Col B already has blanks for repeated dates (set in array()),
        // so here we merge the cells within each same-date block so the
        // displayed date is vertically centred across the group.
        $lastData = $highestRow;
        if ($lastData >= $dataStartRow) {
          $mergeStart = $dataStartRow;
          $mergeVal = $sheet->getCell('B' . $mergeStart)->getValue();
          for ($r = $dataStartRow + 1; $r <= $lastData + 1; $r++) {
            // When we reach a row with a new (non-empty) date value, close the
            // previous group. Empty cells belong to the preceding group.
            $val = $r <= $lastData ? $sheet->getCell('B' . $r)->getValue() : null;
            $isNewGroup = ($r > $lastData) || ($val !== '' && $val !== null);
            if ($isNewGroup) {
              if ($r - 1 > $mergeStart) {
                $sheet->mergeCells('B' . $mergeStart . ':B' . ($r - 1));
                $sheet->getStyle('B' . $mergeStart . ':B' . ($r - 1))->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                  ->setVertical(Alignment::VERTICAL_CENTER);
              }
              $mergeStart = $r;
              $mergeVal = $val;
            }
          }
        }

        // ── Borders ──────────────────────────────────────────────────────────
        $highestRow = (int) $sheet->getHighestRow();
        $tableEnd = max($highestRow, 5);
        $tableRange = 'A4:' . $lastCol . $tableEnd;
        $thinBlack = [
          'borderStyle' => Border::BORDER_THIN,
          'color' => ['rgb' => '000000'],
        ];
        $sheet->getStyle($tableRange)->applyFromArray([
          'borders' => [
            'allBorders' => $thinBlack,
          ],
          'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
          ],
        ]);
        $sheet->getStyle('A4:' . $lastCol . '4')->getBorders()->getTop()
          ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('000000');
        $sheet->getStyle('A3:' . $lastCol . '3')->getBorders()->getBottom()
          ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('000000');

        // Re-apply header style (after border pass may reset some)
        $sheet->getStyle($headerRange)->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_CENTER)
          ->setVertical(Alignment::VERTICAL_CENTER)
          ->setWrapText(true);
        $sheet->getStyle($headerRange)->getFont()->setBold(true);

        // ── Freeze & page setup ───────────────────────────────────────────────
        $sheet->freezePane('A' . $dataStartRow);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToPage(false);
        $sheet->getPageSetup()->setScale(100);
      },
    ];
  }

  protected function monthYearLabel(): string
  {
    try {
      return Carbon::create($this->year, $this->month, 1)->locale('id')->translatedFormat('F Y');
    } catch (\Throwable $e) {
      $names = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
      ];

      return (isset($names[$this->month]) ? $names[$this->month] : $this->month) . ' ' . $this->year;
    }
  }

  /**
   * Format a date string as "04 Mei 2026" (Indonesian long format).
   */
  protected function formatTanggal(?string $dateRaw): string
  {
    if (!$dateRaw) {
      return '';
    }

    $names = [
      1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
      5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
      9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    try {
      $dt = Carbon::parse($dateRaw);
      $bulan = $names[$dt->month] ?? $dt->format('m');

      return $dt->format('d') . ' ' . $bulan . ' ' . $dt->format('Y');
    } catch (\Throwable $e) {
      return (string) $dateRaw;
    }
  }

  protected function loadDataRows(): array
  {
    $labId = $this->laboratoriumId;

    $sampleIds = Sample::query()
      ->join('tb_permohonan_uji', function ($join) {
        $join->on('tb_permohonan_uji.id_permohonan_uji', '=', 'tb_samples.permohonan_uji_id')
          ->whereNull('tb_permohonan_uji.deleted_at')
          ->whereNull('tb_samples.deleted_at');
      })
      ->join('tb_sample_method', function ($join) use ($labId) {
        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
          ->whereNull('tb_sample_method.deleted_at')
          ->where('tb_sample_method.laboratorium_id', $labId);
      })
      ->whereYear('tb_permohonan_uji.date_permohonan_uji', $this->year)
      ->whereMonth('tb_permohonan_uji.date_permohonan_uji', $this->month);

    if ($this->filterDate !== null) {
      $sampleIds = $sampleIds->whereDate('tb_permohonan_uji.date_permohonan_uji', $this->filterDate);
    }

    $sampleIds = $sampleIds->distinct()->pluck('tb_samples.id_samples');

    if ($sampleIds->isEmpty()) {
      return [];
    }

    $methodsBySample = SampleMethod::query()
      ->whereIn('tb_sample_method.sample_id', $sampleIds)
      ->where('tb_sample_method.laboratorium_id', $labId)
      ->join('ms_method', function ($join) {
        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
          ->whereNull('ms_method.deleted_at');
      })
      ->select('tb_sample_method.sample_id', 'ms_method.params_method')
      ->get()
      ->groupBy('sample_id');

    $samples = Sample::query()
      ->with(['permohonanuji.customer', 'sampletype', 'packet'])
      ->whereIn('id_samples', $sampleIds)
      ->get();

    $sorted = $samples->sortBy(function (Sample $s) {
      $date = $s->permohonanuji ? $s->permohonanuji->date_permohonan_uji : '';

      return [(string) $date, (string) $s->codesample_samples];
    })->values();

    $rows = [];
    foreach ($sorted as $sample) {
      $pu = $sample->permohonanuji;
      $cust = ($pu && $pu->customer) ? $pu->customer : null;
      $asal = ($cust && $cust->name_customer) ? $cust->name_customer : ($sample->name_pelanggan ?? '-');
      $alamat = ($cust && isset($cust->address_customer)) ? $cust->address_customer : '';

      $paramColl = $methodsBySample->get($sample->id_samples, collect());
      $paramLabels = implode(', ', $paramColl->pluck('params_method')->filter()->unique()->values()->all());

      $dateRaw = $pu ? $pu->date_permohonan_uji : null;

      $jenis = '-';
      if ($sample->sampletype && $sample->sampletype->name_sample_type) {
        $jenis = $sample->sampletype->name_sample_type;
      }

      $costSample = KesmasNotaHelper::amountForSample(
        $sample->id_samples,
        $sample->permohonan_uji_id
      );

      $sp = $pu ? ($pu->status_pembayaran ?? null) : null;
      $statusLunas = $pu && ($sp === '1' || $sp === 1);

      $rows[] = [
        // tanggal_raw is kept for grouping logic in array(); not written to sheet
        'tanggal_raw' => $dateRaw ? (string) $dateRaw : '',
        // tanggal is the human-readable label, e.g. "04 Mei 2026"
        'tanggal'     => $this->formatTanggal($dateRaw),
        'nomor_sampel' => $sample->codesample_samples ? $sample->codesample_samples : '-',
        'asal'         => $asal,
        'alamat'       => $alamat,
        'jenis'        => $jenis,
        'pengirim'     => $sample->name_send_sample ? $sample->name_send_sample : '-',
        'unit'         => $this->unitLabel,
        'parameter'    => $paramLabels ? $paramLabels : '-',
        'tarif'        => rupiah($costSample),
        'ket'          => $statusLunas ? 'lunas' : '',
      ];
    }

    return $rows;
  }
}
