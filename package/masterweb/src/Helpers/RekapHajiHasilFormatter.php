<?php

namespace Smt\Masterweb\Helpers;

/**
 * Format nilai hasil rekap Haji: tambah '*' bila di luar baku mutu (baris rujukan di header Excel).
 */
class RekapHajiHasilFormatter
{
    /**
     * @param  mixed  $value
     * @param  string  $key  Kunci kolom (hba1c, gdp, ...)
     * @param  string|null  $jk  L/P/laki/perempuan
     */
    public static function format($value, string $key, $jk = null): string
    {
        if ($value === null) {
            return '-';
        }

        $raw = trim((string) $value);
        if ($raw === '' || $raw === '-') {
            return '-';
        }

        // Sudah bertanda *
        if (substr($raw, -1) === '*') {
            return $raw;
        }

        if (self::isOutOfRange($raw, $key, $jk)) {
            return $raw . '*';
        }

        return $raw;
    }

    public static function isOutOfRange(string $raw, string $key, $jk = null): bool
    {
        $gender = self::normalizeGender($jk);
        $num = self::parseNumber($raw);

        switch ($key) {
            case 'hba1c':
                return $num !== null && $num >= 5.7;

            case 'gdp':
                return $num !== null && ($num < 70 || $num > 110);

            case 'gpp':
                return $num !== null && ($num < 70 || $num > 140);

            case 'chol':
                return $num !== null && ($num < 150 || $num > 200);

            case 'tg':
                return $num !== null && ($num < 120 || $num > 150);

            case 'kreatinin':
                return $num !== null && ($num < 0.5 || $num > 1.2);

            case 'egfr':
                return $num !== null && $num < 90;

            case 'ureum':
                return $num !== null && ($num < 20 || $num > 50);

            case 'sgot':
                if ($num === null) {
                    return false;
                }
                return $gender === 'P' ? ($num >= 27) : ($num >= 33);

            case 'sgpt':
                if ($num === null) {
                    return false;
                }
                return $gender === 'P' ? ($num >= 36) : ($num >= 46);

            case 'delta_leu':
                return $num !== null && ($num < 5 || $num > 10);

            case 'delta_eri':
                if ($num === null) {
                    return false;
                }
                if ($gender === 'P') {
                    return $num < 4.0 || $num > 5.0;
                }
                return $num < 4.5 || $num > 5.5;

            case 'hb':
                if ($num === null) {
                    return false;
                }
                if ($gender === 'P') {
                    return $num < 12 || $num > 14;
                }
                return $num < 13 || $num > 16;

            case 'hematokrit':
                if ($num === null) {
                    return false;
                }
                if ($gender === 'P') {
                    return $num < 37 || $num > 43;
                }
                return $num < 40 || $num > 48;

            case 'trombosit':
                return $num !== null && ($num < 150 || $num > 400);

            case 'neu':
                return $num !== null && ($num < 50 || $num > 75);

            case 'lym':
                return $num !== null && ($num < 20 || $num > 40);

            case 'mono':
                return $num !== null && ($num < 2 || $num > 8);

            case 'eos':
                return $num !== null && ($num < 1 || $num > 3);

            case 'baso':
                return $num !== null && ($num < 0 || $num > 1);

            case 'led':
                if ($num === null) {
                    return false;
                }
                return $gender === 'P' ? ($num >= 20) : ($num >= 15);

            // Urin rutin — numerik
            case 'ph':
                return $num !== null && ($num < 5 || $num > 7);

            case 'berat_jenis':
                return $num !== null && ($num < 1.005 || $num > 1.030);

            // Urin rutin — kualitatif: baku "Negatif"
            case 'eritrosit':
            case 'bilirubin':
            case 'protein':
            case 'nitrat':
            case 'keton':
            case 'glukosa':
            case 'leu':
            case 'cyli':
                return self::isUnexpectedPositive($raw);

            case 'urobilinogen':
                // Norm (0.2–1) — anggap abnormal jika jelas positif kuat / di luar norm
                if (self::isUnexpectedPositive($raw) && !preg_match('/norm|normal|0\.?[2-9]|1(\.0)?/i', $raw)) {
                    return true;
                }
                return false;

            // Urin rutin — makroskopis
            case 'warna':
                // Rujukan: kuning muda–tua
                if (preg_match('/merah|coklat|cokelat|hitam|hijau|orange|oranye|jingga|seperti\s*teh|susu|keruh/i', $raw)) {
                    return true;
                }
                return false;

            case 'bau':
                // Rujukan: tidak menyengat
                if (preg_match('/tidak\s*me?nyengat|khas|normal/i', $raw)) {
                    return false;
                }
                return (bool) preg_match('/menyengat|busuk|amoni|tajam|aseton|buah/i', $raw);

            case 'kejernihan':
                // Rujukan: jernih
                return (bool) preg_match('/keruh|tidak\s*jernih|berkabut|cloudy|turbid/i', $raw);

            // Sedimen urine — hitung per lapang pandang
            case 'epitel':
                // Rujukan: <10 /LPK
                return self::isCountAboveLimit($raw, 10, true);

            case 'leu_sedimen':
                // Rujukan: 0–5 /LPB
                return self::isCountAboveLimit($raw, 5);

            case 'ery':
                // Rujukan: 0–3 /LPB
                return self::isCountAboveLimit($raw, 3);

            case 'kristal':
                // Rujukan: < pos 1(+)
                if (self::isNegatif($raw)) {
                    return false;
                }
                return self::plusGrade($raw) >= 1 || (bool) preg_match('/banyak|penuh/i', $raw);

            case 'lain2':
                // Kolom deskriptif (bakteri dsb), tandai hanya bila jelas positif kuat
                if (self::isNegatif($raw)) {
                    return false;
                }
                return self::plusGrade($raw) >= 2 || (bool) preg_match('/banyak|penuh/i', $raw);

            default:
                return false;
        }
    }

    private static function normalizeGender($jk): string
    {
        $g = strtoupper(trim((string) $jk));
        if ($g === 'P' || strpos($g, 'P') === 0 || strpos($g, 'W') === 0) {
            return 'P';
        }
        return 'L';
    }

    /**
     * @return float|null
     */
    private static function parseNumber(string $raw)
    {
        $s = trim($raw);
        $s = preg_replace('/\s*%?\s*$/', '', $s);
        $s = str_replace(['*', ' '], '', $s);

        // "L 4.5" / teks non-angka → coba ekstrak angka pertama
        if (preg_match('/-?\d+(?:[.,]\d+)?/', $s, $m)) {
            $s = $m[0];
        } else {
            return null;
        }

        // Indonesia: 7,1 atau 1.005
        if (strpos($s, ',') !== false && strpos($s, '.') !== false) {
            // 1.234,56 → hapus ribuan
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } elseif (strpos($s, ',') !== false) {
            $s = str_replace(',', '.', $s);
        }

        if (!is_numeric($s)) {
            return null;
        }

        return (float) $s;
    }

    private static function isUnexpectedPositive(string $raw): bool
    {
        $s = strtolower(trim($raw));
        if (self::isNegatif($raw)) {
            return false;
        }
        // Trace / positif / + / angka > 0
        if (preg_match('/pos|\+|reaktif|trace|jejak/i', $s)) {
            return true;
        }
        // Hasil hitung ("0", "0-1"): baru abnormal kalau batas atasnya di atas nol
        $max = self::parseMaxNumber($raw);
        if ($max !== null) {
            return $max > 0;
        }
        // Teks lain yang bukan negatif dianggap perlu ditandai
        return true;
    }

    private static function isNegatif(string $raw): bool
    {
        $s = strtolower(trim($raw));

        return $s === '' || $s === '-' || $s === 'n' || strpos($s, 'neg') === 0;
    }

    /**
     * Batas atas sebuah hasil hitung: "0-1" -> 1, "3–5" -> 5, "12 /LPB" -> 12.
     *
     * @return float|null
     */
    private static function parseMaxNumber(string $raw)
    {
        $s = str_replace(['–', '—', ','], ['-', '-', '.'], trim($raw));

        if (!preg_match_all('/\d+(?:\.\d+)?/', $s, $match)) {
            return null;
        }

        return max(array_map('floatval', $match[0]));
    }

    /**
     * Derajat positif tertinggi pada hasil: "Asam Urat (++)" -> 2, "pos 3" -> 3.
     */
    private static function plusGrade(string $raw): int
    {
        $grade = 0;

        if (preg_match_all('/\++/', $raw, $match)) {
            foreach ($match[0] as $run) {
                $grade = max($grade, strlen($run));
            }
        }

        if (preg_match_all('/pos(?:itif)?\s*([1-4])/i', $raw, $match)) {
            foreach ($match[1] as $angka) {
                $grade = max($grade, (int) $angka);
            }
        }

        return $grade;
    }

    /**
     * Hasil hitung sedimen di luar rujukan. Batas eksklusif dipakai untuk rujukan
     * bergaya "<10" (10 sudah di luar), selain itu rujukan "0–5" (6 baru di luar).
     */
    private static function isCountAboveLimit(string $raw, float $limit, bool $limitSudahDiLuar = false): bool
    {
        $s = strtolower(trim($raw));

        if (self::isNegatif($raw)) {
            return false;
        }

        if (preg_match('/banyak|penuh|full/', $s)) {
            return true;
        }

        // "<10" tetap di bawah batas walaupun angkanya 10; ">10" sebaliknya
        if (strpos($s, '<') === 0) {
            return false;
        }
        if (strpos($s, '>') === 0) {
            return true;
        }

        if (self::plusGrade($raw) >= 1) {
            return true;
        }

        $max = self::parseMaxNumber($raw);
        if ($max === null) {
            return false;
        }

        return $limitSudahDiLuar ? ($max >= $limit) : ($max > $limit);
    }
}
