<?php

namespace Smt\Masterweb\Imports;

use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\Pasien;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class HajiImport implements WithStartRow, WithCalculatedFormulas
{
    public function startRow(): int
    {
        return 5; // Mulai dari baris ke-5
    }

    // public function collection(Collection $rows)
    // {
    //   $data = [];

    //   foreach ($rows as $row) {
    //       // Kolom ke-3 adalah index 2
    //       $tgl_lahir = $row[2];

    //       // Mengonversi tanggal
    //       if (is_numeric($tgl_lahir)) {
    //           // Konversi dari format serial Excel ke DateTime
    //           $tgl_lahir = Date::excelToDateTimeObject($tgl_lahir);
    //           $tgl_lahir = $tgl_lahir->format('Y-m-d');
    //       } else {
    //           try {
    //               // Konversi dari format teks ke Carbon instance
    //               $tgl_lahir = Carbon::parse($tgl_lahir)->format('Y-m-d');
    //           } catch (\Exception $e) {
    //               // Jika format tanggal tidak valid, set ke null atau tangani sesuai kebutuhan
    //               $tgl_lahir = null;
    //           }
    //       }

    //       $data[] = [
    //           'nama'              => $row[1],  // Kolom ke-2 adalah index 1
    //           'tgl_lahir'         => $row[2] ? $tgl_lahir : null,
    //           'alamat'            => $row[3],  // Kolom ke-4 adalah index 3
    //           'jenis_kelamin'     => $row[4],  // Kolom ke-5 adalah index 4
    //           'no_lab'            => $row[5],  // Kolom ke-5 adalah index 4

    //       ];
    //   }

    // return $data;
  // }
}
