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

class MonitoringSamplingPenerimaExport implements FromView, ShouldAutoSize, WithEvents
{
    private $data;
    private $month;
    private $year;

    public function __construct($data, $month, $year)
    {
        $this->data = $data;
        $this->month = $month;
        $this->year = $year;
    }

    public function view(): View
    {
        return view('masterweb::module.admin.laboratorium.report.v2.klinik.monitoring-sampling-penerima.excel', [
            'data' => $this->data,
            'month' => $this->month,
            'year' => $this->year
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set default font
                $sheet->getDefaultRowDimension()->setRowHeight(15);
                
                // Get all rows
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Find table header row (row with "No")
                $headerRow = null;
                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    if ($cellValue && stripos($cellValue, 'No') !== false) {
                        $headerRow = $row;
                        break;
                    }
                }
                
                if ($headerRow) {
                    // Apply borders to table
                    $styleArray = [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                        ],
                    ];
                    
                    // Apply borders to header rows (3 rows)
                    $headerEndRow = $headerRow + 2;
                    $range = 'A' . $headerRow . ':' . $highestColumn . $headerEndRow;
                    $sheet->getStyle($range)->applyFromArray($styleArray);
                    
                    // Apply borders to data rows
                    if ($highestRow > $headerEndRow) {
                        $dataRange = 'A' . ($headerEndRow + 1) . ':' . $highestColumn . $highestRow;
                        $sheet->getStyle($dataRange)->applyFromArray($styleArray);
                    }
                    
                    // Center align header
                    $sheet->getStyle('A' . $headerRow . ':' . $highestColumn . $headerEndRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    
                    // Wrap text for header
                    $sheet->getStyle('A' . $headerRow . ':' . $highestColumn . $headerEndRow)
                        ->getAlignment()
                        ->setWrapText(true);
                    
                    // Bold header
                    $sheet->getStyle('A' . $headerRow . ':' . $highestColumn . $headerEndRow)
                        ->getFont()
                        ->setBold(true);
                    
                    // Background color for category rows
                    $fillArray = [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E9ECEF'],
                    ];
                    $sheet->getStyle('A' . ($headerRow + 1) . ':' . $highestColumn . ($headerRow + 1))
                        ->getFill()
                        ->applyFromArray($fillArray);
                }
                
                // Center align for numeric columns (No, and result columns)
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Auto-size columns
                foreach (range('A', $highestColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}

