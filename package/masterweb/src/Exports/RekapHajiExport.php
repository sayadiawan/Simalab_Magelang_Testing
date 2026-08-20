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

class RekapHajiExport implements FromView, ShouldAutoSize, WithEvents
{
    use AppliesMagelangKopDrawing;

    private $data;
    private $tahun_haji;
    private $nama_pusklesmas;
    private $no_lab;
    private $tgl_diambil;
    private $tgl_diperiksa;

    public function __construct($data, $tahun_haji, $nama_pusklesmas, $no_lab = null, $tgl_diambil = null, $tgl_diperiksa = null)
    {
        $this->data = $data;
        $this->tahun_haji = $tahun_haji;
        $this->nama_pusklesmas = $nama_pusklesmas;
        $this->no_lab = $no_lab;
        $this->tgl_diambil = $tgl_diambil;
        $this->tgl_diperiksa = $tgl_diperiksa;
    }

    public function view(): View
    {
        return view('masterweb::module.admin.laboratorium.persuratan.export.rekap-haji', [
            'data' => $this->data,
            'tahun_haji' => $this->tahun_haji,
            'nama_pusklesmas' => $this->nama_pusklesmas,
            'no_lab' => $this->no_lab,
            'tgl_diambil' => $this->tgl_diambil,
            'tgl_diperiksa' => $this->tgl_diperiksa,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set default font
                $sheet->getDefaultRowDimension()->setRowHeight(15);
                
                // Find header row (row with "NO")
                $headerRow = null;
                $umurRow = null;
                
                // First try to find row with "UMUR" in column D
                for ($row = 1; $row <= 20; $row++) {
                    $cellValue = $sheet->getCell('D' . $row)->getValue();
                    if ($cellValue && stripos($cellValue, 'UMUR') !== false) {
                        $umurRow = $row;
                        $headerRow = $row;
                        break;
                    }
                }
                
                // If not found, try to find row with "NO" in column A
                if (!$headerRow) {
                    for ($row = 1; $row <= 20; $row++) {
                        $cellValue = $sheet->getCell('A' . $row)->getValue();
                        if ($cellValue && stripos($cellValue, 'NO') !== false && $row > 5) {
                            $headerRow = $row;
                            break;
                        }
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
                    
                    // Vertical text for UMUR and JENIS KELAMIN columns (D and E) in header rows
                    // Header spans 4 rows (rowspan="4"), so we need to rotate text in those cells
                    // Apply rotation to each cell individually to ensure it works
                    // PhpSpreadsheet: 90 = 90 degrees counter-clockwise (vertical), 255 = vertical stacked
                    if ($umurRow) {
                        // Apply rotation to the header cells (4 rows)
                        for ($row = $umurRow; $row <= $umurRow + 3; $row++) {
                            $sheet->getStyle('D' . $row)->getAlignment()
                                ->setTextRotation(90)
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                ->setVertical(Alignment::VERTICAL_CENTER);
                            
                            $sheet->getStyle('E' . $row)->getAlignment()
                                ->setTextRotation(90)
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                ->setVertical(Alignment::VERTICAL_CENTER);
                        }
                    } elseif ($headerRow) {
                        // Fallback: apply to header row and next 3 rows
                        for ($row = $headerRow; $row <= $headerRow + 3; $row++) {
                            $sheet->getStyle('D' . $row)->getAlignment()
                                ->setTextRotation(90)
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                ->setVertical(Alignment::VERTICAL_CENTER);
                            
                            $sheet->getStyle('E' . $row)->getAlignment()
                                ->setTextRotation(90)
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                ->setVertical(Alignment::VERTICAL_CENTER);
                        }
                    }
                    
                    // Find footer row (empty row before "Ket")
                    $footerStartRow = null;
                    for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
                        $cellValue = $sheet->getCell('A' . $row)->getValue();
                        if ($cellValue && stripos($cellValue, 'Ket') !== false) {
                            // Footer starts from the empty row before "Ket" row
                            $footerStartRow = $row - 1;
                            break;
                        }
                    }
                    
                    // Determine last data row (before footer)
                    $lastDataRow = $footerStartRow ? $footerStartRow - 1 : $highestRow;
                    
                    // Style for data rows (exclude footer)
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
                    
                    // Apply data style (only to data rows, not footer)
                    if ($highestRow > $headerRow && $lastDataRow >= $headerRow + 1) {
                        $sheet->getStyle('A' . ($headerRow + 1) . ':' . $highestColumn . $lastDataRow)
                            ->applyFromArray($dataStyle);
                    }
                    
                    // Center align for NO column (only data rows)
                    if ($lastDataRow >= $headerRow + 1) {
                        $sheet->getStyle('A' . ($headerRow + 1) . ':A' . $lastDataRow)
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                    
                    // Center align for UMUR and JK data columns (only data rows)
                    if ($lastDataRow >= $headerRow + 1) {
                        $sheet->getStyle('D' . ($headerRow + 1) . ':E' . $lastDataRow)
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                    
                    // Wrap text for ALAMAT column (only data rows)
                    if ($lastDataRow >= $headerRow + 1) {
                        $sheet->getStyle('F' . ($headerRow + 1) . ':F' . $lastDataRow)
                            ->getAlignment()
                            ->setWrapText(true);
                    }
                    
                    // Remove borders from footer rows (keep colors)
                    if ($footerStartRow) {
                        $footerStyle = [
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_NONE,
                                ],
                            ],
                        ];
                        $sheet->getStyle('A' . $footerStartRow . ':' . $highestColumn . $highestRow)
                            ->applyFromArray($footerStyle);
                    }
                    
                    // Alternate row colors (yellow for even rows) - only for data rows, not footer
                    for ($row = $headerRow + 1; $row <= $lastDataRow; $row++) {
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
                    
                    // Auto-size columns
                    foreach (range('A', $highestColumn) as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    // Layout full + kop di tengah halaman cetak
                    $this->applyRekapHajiSheetLayout($sheet, $highestColumn);
                } else {
                    // Auto-size columns even if header not found
                    $highestColumn = $sheet->getHighestColumn();
                    foreach (range('A', $highestColumn) as $col) {
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }
                    $this->applyRekapHajiSheetLayout($sheet, $highestColumn);
                }
            },
        ];
    }
}

