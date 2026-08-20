<?php

namespace Smt\Masterweb\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Sisipkan kop Magelang resmi + rapikan layout halaman rekap Haji Excel.
 */
trait AppliesMagelangKopDrawing
{
    protected function applyMagelangKopDrawing(Worksheet $sheet, $lastColumn = null): void
    {
        $kopPath = public_path('assets/admin/images/logo/kop_magelang.png');
        if (!is_file($kopPath)) {
            $kopPath = public_path('assets/admin/images/logo/kop_magelang_rev.png');
        }
        if (!is_file($kopPath)) {
            return;
        }

        $collection = $sheet->getDrawingCollection();
        while ($collection->count() > 0) {
            $collection->offsetUnset(0);
        }

        if ($lastColumn === null || $lastColumn === '') {
            $lastColumn = $sheet->getHighestColumn();
        }
        $lastIndex = Coordinate::columnIndexFromString($lastColumn);

        // Hitung lebar total kolom (pakai width aktual atau fallback)
        $totalWidthUnits = 0.0;
        for ($i = 1; $i <= $lastIndex; $i++) {
            $col = Coordinate::stringFromColumnIndex($i);
            $width = (float) $sheet->getColumnDimension($col)->getWidth();
            $totalWidthUnits += ($width > 0) ? $width : 9.0;
        }

        // Full sheet width — jangan dipotong kecil agar judul tetap di bawah kop (bukan di samping)
        $pixelWidth = (int) max(900, min(1600, round($totalWidthUnits * 7.2)));

        $drawing = new Drawing();
        $drawing->setName('Kop Magelang');
        $drawing->setDescription('Kop UPTD Laboratorium Kesehatan Kab. Magelang');
        $drawing->setPath($kopPath);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(0);
        $drawing->setOffsetY(2);
        $drawing->setWidth($pixelWidth);
        $drawing->setWorksheet($sheet);

        $imgSize = @getimagesize($kopPath);
        $aspect = ($imgSize && $imgSize[0] > 0) ? ($imgSize[1] / $imgSize[0]) : (308 / 1608);
        $pixelHeight = (int) round($pixelWidth * $aspect);
        $sheet->getRowDimension(1)->setRowHeight(max(60, min(110, $pixelHeight * 0.72)));
    }

    /**
     * Layout cetak: landscape, fit width, konten di tengah halaman.
     */
    protected function applyRekapHajiSheetLayout(Worksheet $sheet, $lastColumn = null): void
    {
        if ($lastColumn === null || $lastColumn === '') {
            $lastColumn = $sheet->getHighestColumn();
        }
        $lastIndex = Coordinate::columnIndexFromString($lastColumn);

        // Lebar kolom minimal agar tabel terasa penuh (bukan menumpuk kiri)
        $minWidths = [
            1 => 5,   // NO
            2 => 12,  // NO SPESIMEN
            3 => 22,  // NAMA
            4 => 6,   // UMUR
            5 => 7,   // JK
            6 => 28,  // ALAMAT
        ];
        for ($i = 1; $i <= $lastIndex; $i++) {
            $col = Coordinate::stringFromColumnIndex($i);
            $dim = $sheet->getColumnDimension($col);
            $current = (float) $dim->getWidth();
            $min = isset($minWidths[$i]) ? $minWidths[$i] : 8;
            if ($current < $min) {
                $dim->setAutoSize(false);
                $dim->setWidth($min);
            }
        }

        // Judul & puskesmas (baris 2–3) center
        $sheet->getStyle('A2:' . $lastColumn . '3')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $page = $sheet->getPageSetup();
        $page->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $page->setFitToPage(true);
        $page->setFitToWidth(1);
        $page->setFitToHeight(0);
        $page->setHorizontalCentered(true);
        $page->setVerticalCentered(false);

        $sheet->getPageMargins()->setTop(0.4);
        $sheet->getPageMargins()->setBottom(0.4);
        $sheet->getPageMargins()->setLeft(0.35);
        $sheet->getPageMargins()->setRight(0.35);

        // Kop setelah lebar kolom final
        $this->applyMagelangKopDrawing($sheet, $lastColumn);
    }
}
