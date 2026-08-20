<?php

namespace Smt\Masterweb\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportJumlahJenisSampelExport implements FromView, ShouldAutoSize, WithEvents
{
    private $rows;
    private $totals;
    private $komulatif;
    private $month;
    private $year;

    public function __construct($rows, $totals, $komulatif, $month, $year)
    {
        $this->rows = $rows;
        $this->totals = $totals;
        $this->komulatif = $komulatif;
        $this->month = $month;
        $this->year = $year;
    }

    public function view(): View
    {
        return view('masterweb::module.admin.laboratorium.report.jumlah-jenis-sampel.excel', [
            'rows' => $this->rows,
            'totals' => $this->totals,
            'komulatif' => $this->komulatif,
            'month' => $this->month,
            'year' => $this->year,
            'bulanNama' => function_exists('fbulan')
                ? fbulan(sprintf('%02d', $this->month)) . ' ' . $this->year
                : $this->month . '/' . $this->year,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                $sheet->getStyle('A1:' . $highestColumn . '2')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0F0F0'],
                    ],
                ]);
            },
        ];
    }
}
