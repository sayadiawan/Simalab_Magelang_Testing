<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Traits\Uuid;

class JenisSampelKlinik extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = 'ms_jenis_sampel_klinik';
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_jenis_sampel_klinik';

    protected $fillable = [
        'name_jenis_sampel_klinik',
        'code_jenis_sampel_klinik',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Nama jenis sampel aktif untuk opsi select (value = label = nama).
     * Disimpan sebagai string agar kompatibel dengan data JSON existing.
     *
     * @return array<int, string>
     */
    public static function optionsForSelect(): array
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('ms_jenis_sampel_klinik')) {
                return self::fallbackOptions();
            }

            $names = static::query()
                ->where('is_active', 1)
                ->orderBy('sort_order', 'asc')
                ->orderBy('name_jenis_sampel_klinik', 'asc')
                ->pluck('name_jenis_sampel_klinik')
                ->filter(function ($name) {
                    return trim((string) $name) !== '';
                })
                ->values()
                ->all();

            return !empty($names) ? $names : self::fallbackOptions();
        } catch (\Throwable $e) {
            return self::fallbackOptions();
        }
    }

    /**
     * Opsi select + nilai lama yang mungkin sudah tidak ada di master (tetap tampil agar selected tidak hilang).
     *
     * @param  array<int, string>|null  $extra
     * @return array<int, string>
     */
    public static function optionsForSelectWithExtra(?array $extra = null): array
    {
        $options = self::optionsForSelect();
        foreach ((array) $extra as $name) {
            $name = trim((string) $name);
            if ($name !== '' && !in_array($name, $options, true)) {
                $options[] = $name;
            }
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public static function fallbackOptions(): array
    {
        return ['Darah', 'Serum', 'Plasma', 'Urine', 'Feses'];
    }
}
