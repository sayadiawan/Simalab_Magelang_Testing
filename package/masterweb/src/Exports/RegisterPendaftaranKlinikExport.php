<?php

namespace Smt\Masterweb\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RegisterPendaftaranKlinikExport implements WithMultipleSheets
{
  /** @var int */
  protected $year;

  /** @var string */
  protected $laboratoriumId;

  /** @var string */
  protected $unitLabel;

  /** @var int|null Satu bulan saja (1–12); null = 12 sheet */
  protected $month;

  /** @var string|null Satu hari (Y-m-d); laporan harian */
  protected $date;

  public function __construct($year, $laboratoriumId, $unitLabel, $month = null, $date = null)
  {
    $this->year = (int) $year;
    $this->laboratoriumId = (string) $laboratoriumId;
    $this->unitLabel = (string) $unitLabel;
    $this->month = ($month !== null && $month !== '') ? (int) $month : null;
    $this->date = ($date !== null && $date !== '') ? (string) $date : null;
  }

  public function sheets(): array
  {
    if ($this->month !== null && $this->month >= 1 && $this->month <= 12) {
      return [
        new RegisterPendaftaranKlinikMonthSheet($this->year, $this->month, $this->laboratoriumId, $this->unitLabel, $this->date),
      ];
    }

    $sheets = [];
    for ($m = 1; $m <= 12; $m++) {
      $sheets[] = new RegisterPendaftaranKlinikMonthSheet($this->year, $m, $this->laboratoriumId, $this->unitLabel);
    }

    return $sheets;
  }
}
