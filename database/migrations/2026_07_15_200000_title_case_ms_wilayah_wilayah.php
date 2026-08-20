<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Normalisasi kolom ms_wilayah.wilayah ke Title Case
 * (huruf besar di depan, kecil di belakang),
 * kecuali akronim DIY / DI / DKI / NAD dan numeral Romawi (I–X, terutama II).
 */
class TitleCaseMsWilayahWilayah extends Migration
{
    /** Token yang tetap huruf besar penuh. */
    private const KEEP_UPPER = [
        'DIY' => true,
        'DI' => true,
        'DKI' => true,
        'NAD' => true,
        'I' => true,
        'II' => true,
        'III' => true,
        'IV' => true,
        'V' => true,
        'VI' => true,
        'VII' => true,
        'VIII' => true,
        'IX' => true,
        'X' => true,
    ];

    public function up(): void
    {
        if (!Schema::hasTable('ms_wilayah') || !Schema::hasColumn('ms_wilayah', 'wilayah')) {
            return;
        }

        DB::table('ms_wilayah')
            ->orderBy('id_wilayah')
            ->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    $original = (string) ($row->wilayah ?? '');
                    $formatted = $this->formatWilayahName($original);

                    if ($formatted === $original) {
                        continue;
                    }

                    DB::table('ms_wilayah')
                        ->where('id_wilayah', $row->id_wilayah)
                        ->update(['wilayah' => $formatted]);
                }
            });
    }

    public function down(): void
    {
        // Perubahan data title-case tidak dapat di-rollback tanpa snapshot asli.
    }

    private function formatWilayahName(string $name): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '');
        if ($name === '') {
            return $name;
        }

        $parts = preg_split('/(\s+|\/|\.|\(|\)|`)/u', $name, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return $name;
        }

        $out = '';
        foreach ($parts as $part) {
            if ($part === '' || preg_match('/^[\s\/\.()\`]+$/u', $part)) {
                $out .= $part;
                continue;
            }

            $out .= $this->formatWilayahToken($part);
        }

        return $out;
    }

    private function formatWilayahToken(string $token): string
    {
        // Simpan tanda baca di pinggir; izinkan apostrophe di kata (HILIGANOWOSA'UA)
        if (!preg_match('/^([^\p{L}\p{N}]*)([\p{L}\p{N}\-\x{27}\x{2019}]+)([^\p{L}\p{N}]*)$/u', $token, $m)) {
            return $token;
        }

        $prefix = $m[1];
        $core = $m[2];
        $suffix = $m[3];

        if (strpos($core, '-') !== false) {
            $segments = explode('-', $core);
            $segments = array_map(function ($segment) {
                return $this->formatPlainWord($segment);
            }, $segments);

            return $prefix . implode('-', $segments) . $suffix;
        }

        if (preg_match('/[\x{27}\x{2019}]/u', $core)) {
            $core = preg_replace_callback(
                '/[^\x{27}\x{2019}]+|[\x{27}\x{2019}]/u',
                function ($mm) {
                    $seg = $mm[0];
                    if ($seg === "'" || $seg === "\u{2019}") {
                        return $seg;
                    }

                    return $this->formatPlainWord($seg);
                },
                $core
            );

            return $prefix . $core . $suffix;
        }

        return $prefix . $this->formatPlainWord($core) . $suffix;
    }

    private function formatPlainWord(string $word): string
    {
        if ($word === '') {
            return $word;
        }

        $upper = mb_strtoupper($word, 'UTF-8');
        if (isset(self::KEEP_UPPER[$upper])) {
            return $upper;
        }

        $lower = mb_strtolower($word, 'UTF-8');
        $first = mb_substr($lower, 0, 1, 'UTF-8');
        $rest = mb_substr($lower, 1, null, 'UTF-8');

        return mb_strtoupper($first, 'UTF-8') . $rest;
    }
}
