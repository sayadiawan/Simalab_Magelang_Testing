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
use PhpOffice\PhpSpreadsheet\Style\Font;

class HasilPemeriksaanDarahHajiExport implements FromView, ShouldAutoSize, WithEvents
{
    private $data;
    private $haji;
    private $tgl_pemeriksaan;
    private $parameterColumns;

    public function __construct($data, $haji, $tgl_pemeriksaan = null, $parameterColumns = [])
    {
        $this->data = $data;
        $this->haji = $haji;
        $this->tgl_pemeriksaan = $tgl_pemeriksaan ?: \Carbon\Carbon::now()->format('d F Y');
        $this->parameterColumns = $parameterColumns;
    }

    public function view(): View
    {
        return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.haji.excel.hasil-pemeriksaan-darah', [
            'data' => $this->data,
            'haji' => $this->haji,
            'tgl_pemeriksaan' => $this->tgl_pemeriksaan,
            'parameterColumns' => $this->parameterColumns
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set default font
                $sheet->getDefaultRowDimension()->setRowHeight(15);
                
                // Find header row (row with "No.")
                $headerRow = null;
                for ($row = 1; $row <= 20; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    if ($cellValue && stripos($cellValue, 'No.') !== false) {
                        $headerRow = $row;
                        break;
                    }
                }
                
                if ($headerRow) {
                    $highestRow = $sheet->getHighestRow();
                    $highestColumn = $sheet->getHighestColumn();
                    
                    // Style for header row
                    $headerStyle = [
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                        ],
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
                    
                    // Apply header style
                    $sheet->getStyle('A' . $headerRow . ':' . $highestColumn . $headerRow)
                        ->applyFromArray($headerStyle);
                    
                    // Style for data rows
                    $dataStyle = [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ];
                    
                    // Apply data style
                    if ($highestRow > $headerRow) {
                        $sheet->getStyle('A' . ($headerRow + 1) . ':' . $highestColumn . $highestRow)
                            ->applyFromArray($dataStyle);
                    }
                    
                    // Center align for No. column
                    $sheet->getStyle('A' . ($headerRow + 1) . ':A' . $highestRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Center align for UMUR, JK columns
                    $sheet->getStyle('D' . ($headerRow + 1) . ':E' . $highestRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Wrap text for ALAMAT column
                    $sheet->getStyle('F' . ($headerRow + 1) . ':F' . $highestRow)
                        ->getAlignment()
                        ->setWrapText(true);
                    
                    // Alternate row colors (yellow for even rows)
                    for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
                        if (($row - $headerRow) % 2 == 0) {
                            $fillStyle = [
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FFFFE0'], // Light yellow
                                ],
                            ];
                            $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)
                                ->applyFromArray($fillStyle);
                        }
                    }
                }
                
                // Auto-size columns
                foreach (range('A', $highestColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}






