<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;

class KlinikNumberSettings extends Model
{
    protected $table = 'ms_klinik_number_settings';
    protected $fillable = ['is_nomor_lab_manual', 'is_nomor_spesimen_manual', 'description'];
    public $timestamps = true;

    /**
     * Get current settings (singleton pattern)
     * @return self
     */
    public static function getSettings()
    {
        $settings = self::first();
        if (!$settings) {
            // Create default settings if not exists
            $settings = self::create([
                'is_nomor_lab_manual' => false,
                'is_nomor_spesimen_manual' => false,
                'description' => 'Setting default: Nomor lab dan spesimen otomatis',
            ]);
        }
        return $settings;
    }

    /**
     * Check if nomor lab is manual globally
     * @return bool
     */
    public static function isNomorLabManual()
    {
        return (bool) self::getSettings()->is_nomor_lab_manual;
    }

    /**
     * Check if nomor spesimen is manual globally
     * @return bool
     */
    public static function isNomorSpesimenManual()
    {
        return (bool) self::getSettings()->is_nomor_spesimen_manual;
    }

    /**
     * Update settings
     * @param array $data
     * @return bool
     */
    public static function updateSettings($data)
    {
        $settings = self::getSettings();
        return $settings->update($data);
    }
}
