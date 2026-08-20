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

class PasienHajiExport implements FromView, ShouldAutoSize, WithEvents
{
    private $rows;
    private $haji;
    private $filterTanggal;

    public function __construct($rows, $haji, ?string $filterTanggal = null)
    {
        $this->rows = $rows;
        $this->haji = $haji;
        $this->filterTanggal = $filterTanggal;
    }

    public function view(): View
    {
        return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.export-pasien-haji', [
            'rows' => $this->rows,
            'haji' => $this->haji,
            'filterTanggal' => $this->filterTanggal,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $headerRow = null;
                for ($row = 1; $row <= min(15, $highestRow); $row++) {
                    if (stripos((string) $sheet->getCell('A' . $row)->getValue(), 'NO') !== false) {
                        $headerRow = $row;
                        break;
                    }
                }

                if (!$headerRow) {
                    return;
                }

                $headerStyle = [
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E9ECEF'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ];

                $sheet->getStyle('A' . $headerRow . ':' . $highestColumn . $headerRow)->applyFromArray($headerStyle);

                if ($highestRow > $headerRow) {
                    $dataStyle = [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ];
                    $sheet->getStyle('A' . ($headerRow + 1) . ':' . $highestColumn . $highestRow)
                        ->applyFromArray($dataStyle);

                    // NO SPESIMEN, NO, JK, TANGGAL LAHIR, UMUR → center
                    foreach (['A', 'B', 'D', 'E', 'F'] as $col) {
                        $sheet->getStyle($col . ($headerRow + 1) . ':' . $col . $highestRow)
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // ALAMAT & KETERANGAN → wrap text
                    foreach (['G', 'H'] as $col) {
                        $sheet->getStyle($col . ($headerRow + 1) . ':' . $col . $highestRow)
                            ->getAlignment()->setWrapText(true);
                    }
                }
            },
        ];
    }
}
