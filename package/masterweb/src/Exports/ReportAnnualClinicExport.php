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

class ReportAnnualClinicExport implements FromView, ShouldAutoSize, WithEvents
{
    private $data;
    private $month;
    private $year;
    private $isYearly;
    private $tipe;

    public function __construct($data, $month, $year, $isYearly = false, string $tipe = 'biasa')
    {
        $this->data = $data;
        $this->month = $month;
        $this->year = $year;
        $this->isYearly = $isYearly;
        $this->tipe = $tipe === 'haji' ? 'haji' : 'biasa';
    }

    public function view(): View
    {
        return view('masterweb::module.admin.laboratorium.report.v2.klinik.report-annual.excel', [
            'data' => $this->data,
            'month' => $this->month,
            'year' => $this->year,
            'isYearly' => $this->isYearly,
            'tipe' => $this->tipe,
            'reportTitle' => $this->tipe === 'haji'
                ? 'Catatan Harian Pemeriksaan Unit Klinik (Haji)'
                : 'Catatan Harian Pemeriksaan Unit Klinik',
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
                
                // Find all table sections (for yearly export, there will be multiple tables)
                $tables = [];
                $currentTableStart = null;
                
                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    $cellValueStr = (string)$cellValue;
                    
                    // Find table header (row with "No" - case insensitive)
                    // Check for exact match "No" or contains "No" (but not "No." or other variations)
                    if ($cellValue && (
                        trim($cellValueStr) === 'No' || 
                        (stripos($cellValueStr, 'No') === 0 && strlen(trim($cellValueStr)) <= 3)
                    )) {
                        // If we have a previous table, close it first
                        if ($currentTableStart !== null) {
                            // Find end of previous table
                            $prevTableInfo = $this->findTableInfo($sheet, $currentTableStart, $row - 1);
                            if ($prevTableInfo) {
                                $tables[] = $prevTableInfo;
                            }
                        }
                        $currentTableStart = $row;
                    }
                    
                    // Check if this is empty row before signature or signature itself
                    if ($currentTableStart !== null) {
                        if (($cellValue === null || $cellValue === '') && $row > $currentTableStart + 5) {
                            $nextRowValue = $sheet->getCell('A' . ($row + 1))->getValue();
                            if ($nextRowValue && (stripos($nextRowValue, 'Mengetahui') !== false || stripos($nextRowValue, 'Magelang') !== false)) {
                                $tableInfo = $this->findTableInfo($sheet, $currentTableStart, $row - 1);
                                if ($tableInfo) {
                                    $tables[] = $tableInfo;
                                }
                                $currentTableStart = null;
                            }
                        } elseif ($cellValue && (stripos($cellValue, 'Mengetahui') !== false || stripos($cellValue, 'Magelang') !== false)) {
                            $tableInfo = $this->findTableInfo($sheet, $currentTableStart, $row - 2);
                            if ($tableInfo) {
                                $tables[] = $tableInfo;
                            }
                            $currentTableStart = null;
                        }
                    }
                }
                
                // Handle last table if exists
                if ($currentTableStart !== null) {
                    $lastTableInfo = $this->findTableInfo($sheet, $currentTableStart, $highestRow);
                    if ($lastTableInfo) {
                        $tables[] = $lastTableInfo;
                    }
                }
                
                // Apply borders to all found tables with correct column count
                $styleArray = [
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
                
                // First, remove all borders from entire sheet
                $noBorderStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ];
                $allRange = 'A1:' . $highestColumn . $highestRow;
                $sheet->getStyle($allRange)->applyFromArray($noBorderStyle);
                
                // Then, apply borders only to table sections
                if (count($tables) > 0) {
                    foreach ($tables as $table) {
                        if (isset($table['start']) && isset($table['end']) && isset($table['daysInMonth'])) {
                            // Calculate end column based on days in month
                            // No (1) + Uraian (1) + Days (daysInMonth) + Jumlah (1) + Ket (1) = daysInMonth + 4
                            $endColumn = $this->getColumnLetter($table['daysInMonth'] + 4);
                            $range = 'A' . $table['start'] . ':' . $endColumn . $table['end'];
                            $sheet->getStyle($range)->applyFromArray($styleArray);
                        }
                    }
                }
                
                // Center align for numeric columns
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
    
    private function findTableInfo($sheet, $tableStart, $maxRow)
    {
        // Find days in month by checking the header row (second row of table header)
        // The header row 2 contains day numbers (1, 2, 3, ...) starting from column C
        $headerRow2 = $tableStart + 1;
        $daysInMonth = 31; // default
        
        // Count columns in header row 2 starting from column C (after No=A and Uraian=B)
        // Day numbers start from column C (column 3)
        $dayCount = 0;
        $maxCol = 35; // Max 31 days + 4 columns
        
        for ($colNum = 3; $colNum <= $maxCol; $colNum++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colNum);
            try {
                $cellValue = $sheet->getCell($col . $headerRow2)->getValue();
                
                // Check if cell contains a number (day number)
                if ($cellValue !== null && $cellValue !== '') {
                    $numValue = is_numeric($cellValue) ? (int)$cellValue : null;
                    if ($numValue !== null && $numValue > 0 && $numValue <= 31) {
                        $dayCount = max($dayCount, $numValue); // Use the highest day number found
                    } else {
                        // If we found days and hit non-day value, stop
                        if ($dayCount > 0) {
                            break;
                        }
                    }
                } else {
                    // If we found days and hit empty, stop
                    if ($dayCount > 0) {
                        break;
                    }
                }
            } catch (\Exception $e) {
                // If column doesn't exist, stop
                if ($dayCount > 0) {
                    break;
                }
            }
        }
        
        if ($dayCount > 0) {
            $daysInMonth = $dayCount;
        }
        
        // Find table end row
        $tableEnd = null;
        for ($row = $tableStart + 5; $row <= $maxRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            
            // Check if this is empty row before signature
            if (($cellValue === null || $cellValue === '') && $row > $tableStart + 5) {
                $nextRowValue = $sheet->getCell('A' . ($row + 1))->getValue();
                if ($nextRowValue && (stripos($nextRowValue, 'Mengetahui') !== false || stripos($nextRowValue, 'Magelang') !== false)) {
                    $tableEnd = $row - 1; // End before empty row
                    break;
                }
            } elseif ($cellValue && (stripos($cellValue, 'Mengetahui') !== false || stripos($cellValue, 'Magelang') !== false)) {
                $tableEnd = $row - 2; // End before empty row and signature
                break;
            }
        }
        
        if ($tableEnd) {
            return [
                'start' => $tableStart,
                'end' => $tableEnd,
                'daysInMonth' => $daysInMonth
            ];
        }
        
        return null;
    }
    
    private function getColumnLetter($columnNumber)
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnNumber);
    }
}

