<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class KesmasSampleNumberSettings extends Model
{
    protected $table = 'ms_kesmas_sample_number_settings';

    protected $fillable = [
        'is_nomor_sampel_manual',
        'is_nomor_laboratorium_manual',
        'description',
    ];

    public $timestamps = true;

    protected $casts = [
        'is_nomor_sampel_manual' => 'boolean',
        'is_nomor_laboratorium_manual' => 'boolean',
    ];

    /**
     * Singleton settings row (sama pola seperti KlinikNumberSettings).
     *
     * Jika migrasi belum dijalankan dan tabel belum ada, mengembalikan model
     * tidak tersimpan dengan nilai default agar halaman (mis. elits-samples/create) tidak error.
     */
    public static function getSettings(): self
    {
        $empty = new static;
        if (!Schema::hasTable($empty->getTable())) {
            $empty->exists = false;
            $empty->is_nomor_sampel_manual = false;
            $empty->is_nomor_laboratorium_manual = false;
            $empty->description = null;

            return $empty;
        }

        $settings = self::query()->first();
        if (!$settings) {
            $settings = self::create([
                'is_nomor_sampel_manual' => false,
                'is_nomor_laboratorium_manual' => false,
                'description' => 'Setting default Kesmas: otomatis',
            ]);
        }

        return $settings;
    }

    public static function updateSettings(array $data): bool
    {
        $model = new static;
        if (!Schema::hasTable($model->getTable())) {
            return false;
        }

        return self::getSettings()->update($data);
    }

    public static function tableExists(): bool
    {
        return Schema::hasTable((new static)->getTable());
    }
}
