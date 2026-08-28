<?php

namespace Smt\Masterweb\Helpers;

use Illuminate\Http\Request;

/**
 * Lebar kolom (%) tabel hasil Kesmas — dinamis per profil layout.
 *
 * Disimpan di tb_samples.column_widths_hasil_baca_hasil (JSON):
 * {
 *   "lhu_6col": { "no": 5, "parameter": 20, ... },
 *   "mikro_makanan_8col": { "no": 4, "kode_sampel": 11, ... }
 * }
 */
class KesmasHasilColumnWidth
{
    public const PROFILE_LHU_6COL = 'lhu_6col';
    public const PROFILE_MIKRO_MAKANAN_8COL = 'mikro_makanan_8col';

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
            self::PROFILE_MIKRO_MAKANAN_8COL => [
                'no' => 'No',
                'kode_sampel' => 'Kode Sampel',
                'titik_sampel' => 'Titik Sampel',
                'jenis_sampel' => 'Jenis Sampel',
                'parameter' => 'Parameter Pemeriksaan',
                'satuan' => 'Satuan',
                'batas_maksimal' => 'Batas Maksimal',
                'hasil' => 'Hasil Pemeriksaan',
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
            self::PROFILE_MIKRO_MAKANAN_8COL => [
                'no' => 4.0,
                'kode_sampel' => 11.0,
                'titik_sampel' => 14.0,
                'jenis_sampel' => 11.0,
                'parameter' => 20.0,
                'satuan' => 9.0,
                'batas_maksimal' => 13.0,
                'hasil' => 18.0,
            ],
        ];
    }

    public static function isMakananMinumanSample($sample = null): bool
    {
        $type = trim((string) data_get($sample, 'name_sample_type', ''));

        // Saat save setting sering hanya tb_samples.* — name_sample_type tidak ikut.
        if ($type === '' && $sample) {
            if (method_exists($sample, 'relationLoaded')) {
                if (!$sample->relationLoaded('sampletype') && method_exists($sample, 'loadMissing')) {
                    try {
                        $sample->loadMissing('sampletype');
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            }
            $type = trim((string) data_get($sample, 'sampletype.name_sample_type', ''));
        }

        if ($type === '') {
            return false;
        }

        return stripos($type, 'Makanan') !== false
            || stripos($type, 'Minuman') !== false;
    }

    /**
     * Profil layout aktif untuk sample type / lab Kesmas.
     */
    public static function resolveProfile($sample = null, $lab = null): string
    {
        $labCode = strtoupper(trim((string) (
            data_get($lab, 'kode_laboratorium')
            ?? data_get($sample, 'kode_laboratorium')
            ?? ''
        )));

        // LHU mikro makanan/minuman — kolom beda dari LHU kimia 6 kolom
        if (self::isMakananMinumanSample($sample)) {
            if ($labCode === '' || $labCode === 'MBI') {
                return self::PROFILE_MIKRO_MAKANAN_8COL;
            }
        }

        return self::PROFILE_LHU_6COL;
    }

    /**
     * Preferensi profil dari payload request (nested keys), fallback resolveProfile.
     */
    public static function resolveProfileFromIncoming($incoming, $sample = null, $lab = null): string
    {
        if (is_string($incoming) && $incoming !== '') {
            $decoded = json_decode($incoming, true);
            $incoming = is_array($decoded) ? $decoded : [];
        }

        if (is_array($incoming)) {
            foreach (array_keys(self::profiles()) as $profile) {
                if (isset($incoming[$profile]) && is_array($incoming[$profile])) {
                    return $profile;
                }
            }
        }

        return self::resolveProfile($sample, $lab);
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
     * Apakah payload flat (keys kolom) bukan nested per profil.
     */
    public static function looksLikeFlatColumns(array $incoming): bool
    {
        if (isset($incoming[self::PROFILE_LHU_6COL]) || isset($incoming[self::PROFILE_MIKRO_MAKANAN_8COL])) {
            return false;
        }

        $knownKeys = [];
        foreach (self::profiles() as $cols) {
            foreach (array_keys($cols) as $key) {
                $knownKeys[$key] = true;
            }
        }

        foreach ($incoming as $key => $val) {
            if (isset($knownKeys[$key])) {
                return true;
            }
        }

        return false;
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
        } elseif (is_array($incoming) && self::looksLikeFlatColumns($incoming)) {
            $stored[$activeProfile] = self::normalizeProfile($activeProfile, $incoming);
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
    public static function resolve($sample = null, ?Request $request = null, ?string $profile = null, $lab = null): array
    {
        $profile = $profile ?: self::resolveProfile($sample, $lab);
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
                    } elseif (self::looksLikeFlatColumns($decoded)) {
                        $fromRequest = self::normalizeProfile($profile, $decoded);
                    }
                }
            } elseif (is_array($raw)) {
                if (isset($raw[$profile]) && is_array($raw[$profile])) {
                    $fromRequest = self::normalizeProfile($profile, $raw[$profile]);
                } elseif (self::looksLikeFlatColumns($raw)) {
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
    public static function uiPayload($sample = null, ?string $profile = null, $lab = null): array
    {
        $profile = $profile ?: self::resolveProfile($sample, $lab);
        $labels = self::profiles()[$profile] ?? [];
        $widths = self::resolve($sample, null, $profile, $lab);

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
