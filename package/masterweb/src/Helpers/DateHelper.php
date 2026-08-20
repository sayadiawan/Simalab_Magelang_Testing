<?php

namespace Smt\Masterweb\Helpers;

use Carbon\Carbon;
use DateTime;

class DateHelper
{
    public static function formatDateIndo($date)
    {
        $months = [
          1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $carbonDate = \Carbon\Carbon::parse($date);
        return $carbonDate->day . ' ' . $months[$carbonDate->month] . ' ' . $carbonDate->year;
    }

    public static function formatDate($date)
    {
      return Carbon::parse($date)->format('d-m-Y H:i');
    }

    /**
     * Format untuk input flatpickr (d/m/Y H:i).
     */
    public static function formatDateTimePicker($date)
    {
      if ($date === null || $date === '') {
        return '';
      }

      try {
        return Carbon::parse($date)->format('d/m/Y H:i');
      } catch (\Exception $e) {
        return '';
      }
    }

    /**
     * Format untuk input flatpickr tanggal saja (d/m/Y) — tanpa jam.
     */
    public static function formatDatePicker($date)
    {
      if ($date === null || $date === '') {
        return '';
      }

      try {
        return Carbon::parse($date)->format('d/m/Y');
      } catch (\Exception $e) {
        return '';
      }
    }

    public static function formatOnlyDate($date)
    {
      if ($date === null || $date === '') {
        return '';
      }

      try {
        return Carbon::parse($date)->format('d-m-Y');
      } catch (\Exception $e) {
        return '';
      }
    }

    /**
     * Tanggal acuan permohonan klinik: created_at (fallback tglregister).
     *
     * @param  object|string|null  $permohonan
     */
    public static function permohonanAnchorAt($permohonan)
    {
      if ($permohonan === null || $permohonan === '') {
        return null;
      }

      if (is_object($permohonan)) {
        return $permohonan->created_at ?? $permohonan->tglregister_permohonan_uji_klinik ?? null;
      }

      return $permohonan;
    }

    /**
     * Tempel jam ke tanggal acuan (default: hari ini).
     *
     * - Input kosong: tanggal acuan + waktu sekarang
     * - Input HH:mm[:ss]: tanggal acuan + jam tersebut
     * - Input sudah berisi tanggal (d/m/Y H:i, Y-m-d H:i, dll): dipertahankan (user edit tanggal)
     *
     * @param  mixed  $anchorAt  created_at / start_date tersimpan / null → hari ini
     * @param  mixed  $timeInput
     */
    public static function clockOnRegisterDate($anchorAt, $timeInput = null): Carbon
    {
      $now = Carbon::now();
      $base = ($anchorAt !== null && $anchorAt !== '')
        ? Carbon::parse($anchorAt)->copy()->startOfDay()
        : $now->copy()->startOfDay();

      $timeInput = trim((string) $timeInput);
      if ($timeInput === '') {
        return $base->copy()->setTime((int) $now->hour, (int) $now->minute, (int) $now->second);
      }

      if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $timeInput, $m)) {
        return $base->copy()->setTime((int) $m[1], (int) $m[2], isset($m[3]) ? (int) $m[3] : 0);
      }

      $parsed = self::parseStageDate($timeInput);
      if ($parsed) {
        return $parsed;
      }

      return $base->copy()->setTime((int) $now->hour, (int) $now->minute, (int) $now->second);
    }

    /**
     * @deprecated Gunakan clockOnRegisterDate(null, $timeInput) — tanggal hari ini.
     */
    public static function clockOnClickDate($ignoredBase = null, $timeInput = null): Carbon
    {
      return self::clockOnRegisterDate(null, $timeInput);
    }

    /**
     * Parse input tanggal tahap lab (d/m/Y atau d/m/Y H:i, dll).
     * Jika hanya tanggal (tanpa jam), jam diset ke waktu sekarang.
     *
     * @param  string|null  $dateString
     * @return Carbon|null
     */
    public static function parseStageDate($dateString)
    {
      $dateString = trim((string) $dateString);
      if ($dateString === '') {
        return null;
      }

      $dateOnlyFormats = ['d/m/Y', 'd-m-Y', 'Y-m-d'];
      $formats = ['d/m/Y H:i', 'd/m/Y H:i:s', 'd-m-Y H:i', 'd-m-Y H:i:s', 'Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i', 'd/m/Y', 'd-m-Y', 'Y-m-d'];

      foreach ($formats as $format) {
        try {
          $parsed = Carbon::createFromFormat($format, $dateString);
          if (in_array($format, $dateOnlyFormats, true)) {
            $now = Carbon::now();
            $parsed->setTime((int) $now->hour, (int) $now->minute, (int) $now->second);
          }
          return $parsed;
        } catch (\Exception $e) {
          // coba format berikutnya
        }
      }

      try {
        return Carbon::parse($dateString);
      } catch (\Exception $e) {
        return null;
      }
    }

    // Format $dob = "YYYY-MM-DD"
    public static function calcAge($dob): int
    {
      if (!DateTime::createFromFormat('Y-m-d', $dob)) {
        return "Tanggal lahir tidak valid. Harus dalam format YYYY-MM-DD.";
      }

      try {
        $dob = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($dob);

        return $age->y;
      } catch (\Exception $e){
        return "Terjadi kesalahan: " . $e->getMessage();
      }
    }
}
