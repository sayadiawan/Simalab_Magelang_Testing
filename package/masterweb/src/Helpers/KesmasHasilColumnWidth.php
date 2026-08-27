<?php

namespace Smt\Masterweb\Helpers;

use Illuminate\Http\Request;

/**
 * Lebar kolom (%) tabel hasil Kesmas — dinamis per profil layout.
 *
 * Disimpan di tb_samples.column_widths_hasil_baca_hasil (JSON):
 * {
 *   "lhu_6col": { "no": 5, "parameter": 20, "hasil": 15, "baku_mutu": 25, "satuan": 15, "metode": 20 }
 * }
 */
class KesmasHasilColumnWidth
{
    public const PROFILE_LHU_6COL = 'lhu_6col';

    /**
     * Definisi kolom per profil: key => label UI.
     *
     * @return array<string, array<string, string>>
     */
    public static function profiles(): array
    {
        return [
            self::PROFILE_LHU_6COL => [
                'no' => 'NO',
                'parameter' => 'Parameter Pemeriksaan',
                'hasil' => 'Hasil Pemeriksaan',
                'baku_mutu' => 'Kadar Maksimum',
                'satuan' => 'Satuan',
                'metode' => 'Metode',
            ],
        ];
    }

    /**
     * Default % per profil (total 100).
     *
     * @return array<string, array<string, float>>
     */
    public static function defaults(): array
    {
        return [
            self::PROFILE_LHU_6COL => [
                'no' => 5.0,
                'parameter' => 20.0,
                'hasil' => 15.0,
                'baku_mutu' => 25.0,
                'satuan' => 15.0,
                'metode' => 20.0,
            ],
        ];
    }

    /**
     * Profil layout aktif untuk sample type / lab Kesmas.
     * Saat ini fokus LHU 6 kolom (Air Minum/Bersih/Higiene, printLHU).
     */
    public static function resolveProfile($sample = null, $lab = null): string
    {
        return self::PROFILE_LHU_6COL;
    }

    /**
     * Decode JSON dari DB sample.
     *
     * @return array<string, array<string, float>>
     */
    public static function decodeStored($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Encode untuk disimpan ke DB.
     */
    public static function encodeStored(array $allProfiles): string
    {
        return json_encode($allProfiles, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Normalisasi satu profil: hanya key valid, clamp 1–80, scale ke ~100.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, float>
     */
    public static function normalizeProfile(string $profile, array $input): array
    {
        $labels = self::profiles()[$profile] ?? null;
        $defaults = self::defaults()[$profile] ?? null;
        if (!$labels || !$defaults) {
            return [];
        }

        $out = [];
        foreach ($labels as $key => $label) {
            $val = isset($input[$key]) ? (float) $input[$key] : (float) $defaults[$key];
            if ($val < 1) {
                $val = 1.0;
            }
            if ($val > 80) {
                $val = 80.0;
            }
            $out[$key] = $val;
        }

        $sum = array_sum($out);
        if ($sum <= 0) {
            return $defaults;
        }

        // Scale ke 100, lalu bulatkan 1 desimal; koreksi sisa di kolom terakhir.
        $scaled = [];
        $keys = array_keys($out);
        $acc = 0.0;
        $last = count($keys) - 1;
        foreach ($keys as $i => $key) {
            if ($i === $last) {
                $scaled[$key] = round(100.0 - $acc, 1);
            } else {
                $scaled[$key] = round(($out[$key] / $sum) * 100.0, 1);
                $acc += $scaled[$key];
            }
            if ($scaled[$key] < 1) {
                $scaled[$key] = 1.0;
            }
        }

        return $scaled;
    }

    /**
     * Merge input (request/UI) ke stored JSON semua profil.
     *
     * @param  array<string, array<string, mixed>>|array<string, mixed>  $incoming
     * @return array<string, array<string, float>>
     */
    public static function mergeIncoming($storedRaw, $incoming, string $activeProfile): array
    {
        $stored = self::decodeStored($storedRaw);

        // Incoming bisa flat (hanya profil aktif) atau nested per profil.
        if (isset($incoming[$activeProfile]) && is_array($incoming[$activeProfile])) {
            $stored[$activeProfile] = self::normalizeProfile($activeProfile, $incoming[$activeProfile]);
        } elseif (is_array($incoming) && !isset($incoming[self::PROFILE_LHU_6COL])) {
            // Flat keys: no, parameter, ...
            $looksFlat = isset($incoming['no']) || isset($incoming['parameter']);
            if ($looksFlat) {
                $stored[$activeProfile] = self::normalizeProfile($activeProfile, $incoming);
            } else {
                foreach ($incoming as $profile => $cols) {
                    if (is_array($cols) && isset(self::profiles()[$profile])) {
                        $stored[$profile] = self::normalizeProfile($profile, $cols);
                    }
                }
            }
        } elseif (is_array($incoming)) {
            foreach ($incoming as $profile => $cols) {
                if (is_array($cols) && isset(self::profiles()[$profile])) {
                    $stored[$profile] = self::normalizeProfile($profile, $cols);
                }
            }
        }

        return $stored;
    }

    /**
     * Resolve lebar kolom untuk render tabel.
     *
     * @return array<string, float>
     */
    public static function resolve($sample = null, ?Request $request = null, ?string $profile = null): array
    {
        $profile = $profile ?: self::resolveProfile($sample);
        $defaults = self::defaults()[$profile] ?? [];

        $fromDb = [];
        if ($sample) {
            $stored = self::decodeStored(data_get($sample, 'column_widths_hasil_baca_hasil'));
            if (isset($stored[$profile]) && is_array($stored[$profile])) {
                $fromDb = self::normalizeProfile($profile, $stored[$profile]);
            }
        }

        $fromRequest = [];
        if ($request) {
            $raw = $request->input('column_widths', null);
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    if (isset($decoded[$profile]) && is_array($decoded[$profile])) {
                        $fromRequest = self::normalizeProfile($profile, $decoded[$profile]);
                    } elseif (isset($decoded['no']) || isset($decoded['parameter'])) {
                        $fromRequest = self::normalizeProfile($profile, $decoded);
                    }
                }
            } elseif (is_array($raw)) {
                if (isset($raw[$profile]) && is_array($raw[$profile])) {
                    $fromRequest = self::normalizeProfile($profile, $raw[$profile]);
                } elseif (isset($raw['no']) || isset($raw['parameter'])) {
                    $fromRequest = self::normalizeProfile($profile, $raw);
                }
            }
        }

        if (!empty($fromRequest)) {
            return $fromRequest;
        }
        if (!empty($fromDb)) {
            return $fromDb;
        }

        return $defaults;
    }

    /**
     * Data untuk UI modal (labels + current %).
     *
     * @return array{profile: string, columns: array<int, array{key: string, label: string, width: float}>}
     */
    public static function uiPayload($sample = null, ?string $profile = null): array
    {
        $profile = $profile ?: self::resolveProfile($sample);
        $labels = self::profiles()[$profile] ?? [];
        $widths = self::resolve($sample, null, $profile);

        $columns = [];
        foreach ($labels as $key => $label) {
            $columns[] = [
                'key' => $key,
                'label' => $label,
                'width' => (float) ($widths[$key] ?? 0),
            ];
        }

        return [
            'profile' => $profile,
            'columns' => $columns,
        ];
    }
}
