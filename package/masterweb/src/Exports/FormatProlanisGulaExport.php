<?php

namespace Smt\Masterweb\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class FormatProlanisGulaExport implements FromView
{
    protected $count;
    protected $nama_prolanis;
    protected $tgl_prolanis;
    protected $kuota_prolanis;

    public function __construct($count, $nama_prolanis, $tgl_prolanis, $kuota_prolanis)
    {
        $this->count = $count;
        $this->nama_prolanis = $nama_prolanis;
        $this->tgl_prolanis = $tgl_prolanis;
        $this->kuota_prolanis = $kuota_prolanis;
    }

    public function view(): View
    {
        
        $romanMonths = [
          1 => 'I',
          2 => 'II',
          3 => 'III',
          4 => 'IV',
          5 => 'V',
          6 => 'VI',
          7 => 'VII',
          8 => 'VIII',
          9 => 'IX',
          10 => 'X',
          11 => 'XI',
          12 => 'XII'
        ];

        $currentMonth = date('n'); // Mendapatkan bulan saat ini dalam format numerik (1-12)
        $romanMonth = $romanMonths[$currentMonth]; // Mendapatkan bulan dalam format Romawi
        $rows = array_fill(0, $this->kuota_prolanis, []);
        
        return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.prolanis.format_prolanis_gula_excel', [
            'rows' => $rows,
            'nama_prolanis' => $this->nama_prolanis,
            'tgl_prolanis' => $this->tgl_prolanis,
            'count' => $this->count,
            'romanMonth' => $romanMonth,
        ]);
    }
}