<?php

namespace Smt\Masterweb\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Smt\Masterweb\Models\KlinikNumberSettings;
use Smt\Masterweb\Models\VerificationActivity;

class FormatHajiExport implements FromView, WithEvents
{
    protected $count;
    protected $nama_haji;
    protected $tgl_haji;
    protected $kuota;
    protected $petugasOptions;
    protected $nextLabNumber;

    public function __construct($count, $nama_haji, $tgl_haji, $kuota, $nextLabNumber = 1)
    {
        $this->count = $count;
        $this->nama_haji = $nama_haji;
        $this->tgl_haji = $tgl_haji;
        $this->kuota = $kuota;
        $this->nextLabNumber = (int) $nextLabNumber;
        $this->petugasOptions = $this->getPetugasOptions();
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

        // Get VerificationActivity data
        $verificationActivities = VerificationActivity::all();
        
        // Parse staff names for each activity
        $petugasOptions = [];
        foreach ($verificationActivities as $activity) {
            // Pendaftaran / Registrasi (id=1) - use register column
            if ($activity->id == 1) {
                $names = $this->parseNames($activity->klinik);
                $petugasOptions['register'] = $names;
            }
            // Pengambil Sample (id=6) - use klinik column
            if ($activity->id == 6) {
                $names = $this->parseNames($activity->klinik);
                $petugasOptions['pengambil_sample'] = $names;
            }
            // Penerima Sampel (id=7) - use klinik column
            if ($activity->id == 7) {
                $names = $this->parseNames($activity->klinik);
                $petugasOptions['penerima_sampel'] = $names;
            }
            // Pengolah Sampel (id=8) - use klinik column
            if ($activity->id == 8) {
                $names = $this->parseNames($activity->klinik);
                $petugasOptions['pengolah_sampel'] = $names;
            }
            // Pemeriksa Sampel (id=2 or id=3) - use klinik column, prefer id=2
            if ($activity->id == 2) {
                $names = $this->parseNames($activity->klinik);
                $petugasOptions['pemeriksa_sampel'] = $names;
            }
            // Verifikasi (id=4) - use klinik column
            if ($activity->id == 4) {
                $names = $this->parseNames($activity->klinik);
                $petugasOptions['verifikasi'] = $names;
            }
            // Validasi (id=5) - use klinik column
            if ($activity->id == 5) {
                $names = $this->parseNames($activity->klinik);
                $petugasOptions['validasi'] = $names;
            }
        }

        $rows = array_fill(0, $this->kuota, []);
        $klinikNumberSettings = KlinikNumberSettings::getSettings();

        return view('masterweb::module.admin.laboratorium.permohonan-uji-klinik-2.formatPrint.format_haji_excel', [
            'rows' => $rows,
            'nama_haji' => $this->nama_haji,
            'tgl_haji' => $this->tgl_haji,
            'count' => $this->count,
            'romanMonth' => $romanMonth,
            'petugasOptions' => $petugasOptions,
            'klinikNumberSettings' => $klinikNumberSettings,
            'nextLabNumber' => max(1, $this->nextLabNumber),
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Tidak ada lagi kolom petugas yang perlu divalidasi
                // Template Excel sekarang hanya berisi identitas pasien saja
            },
        ];
    }

    private function getPetugasOptions()
    {
        // Get VerificationActivity data
        $verificationActivities = VerificationActivity::all();
        
        // Parse staff names for each activity
        $petugasOptions = [];
        foreach ($verificationActivities as $activity) {
            // Pendaftaran / Registrasi (id=1) - use register column
            if ($activity->id == 1) {
                $names = $this->parseNames($activity->klinik);
                $petugasOptions['register'] = $names;
            }
            // Pengambil Sample (id=6) - use klinik column
            if ($activity->id == 6) {
                $names = $this->parseNames($activity->klinik);
                $petugasOptions['pengambil_sample'] = $names;
            }
            // Penerima Sampel (id=7) - use klinik column
            if ($activity->id == 7) {
                $names = $this->parseNames($activity->klinik);
                $petugasOptions['penerima_sampel'] = $names;
            }
            // Pengolah Sampel (id=8) - use klinik column
            if ($activity->id == 8) {
                $names = $this->parseNames($activity->klinik);
                $petugasOptions['pengolah_sampel'] = $names;
            }
            // Pemeriksa Sampel (id=2 or id=3) - use klinik column, prefer id=2
            if ($activity->id == 2) {
                $names = $this->parseNames($activity->klinik);
                $petugasOptions['pemeriksa_sampel'] = $names;
            }
            // Verifikasi (id=4) - use klinik column
            if ($activity->id == 4) {
                $names = $this->parseNames($activity->klinik);
                $petugasOptions['verifikasi'] = $names;
            }
            // Validasi (id=5) - use klinik column
            if ($activity->id == 5) {
                $names = $this->parseNames($activity->klinik);
                $petugasOptions['validasi'] = $names;
            }
        }
        
        return $petugasOptions;
    }

    private function parseNames($namesString)
    {
        if (empty($namesString) || $namesString === '-' || $namesString === 'NULL') {
            return [];
        }

        // Split by ", " (comma + space) which is the standard separator
        $parts = explode(', ', $namesString);
        
        // Trim each name
        $names = [];
        foreach ($parts as $part) {
            $trimmed = trim($part);
            if (!empty($trimmed)) {
                $names[] = $trimmed;
            }
        }
        
        return array_values($names);
    }
}
