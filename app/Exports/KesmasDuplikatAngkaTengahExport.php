<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KesmasDuplikatAngkaTengahExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    /** @var array<int, array<int, mixed>> */
    private $rows;

    /** @var int */
    private $year;

    /** @var string */
    private $sheetLabel;

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    public function __construct(array $rows, int $year, string $sheetLabel = '')
    {
        $this->rows = $rows;
        $this->year = $year;
        $this->sheetLabel = $sheetLabel !== '' ? $sheetLabel : (string) $year;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tahun',
            'Angka Tengah',
            'Jumlah Baris Sama',
            'Kode Nomor Sampel',
            'Jenis Kode',
            'Nama Jenis Sampel',
            'Lab (01=Kimia, 02=Mikro)',
            'Tgl Sampling',
            'Tgl Input',
            'Pelanggan',
            'Titik Pengambilan',
            'Kode Permohonan',
            'Kode Persis Duplikat?',
            'Angka+Jenis Sama?',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Duplikat ' . $this->sheetLabel;
    }
}
