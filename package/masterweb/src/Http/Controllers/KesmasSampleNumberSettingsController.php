<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Smt\Masterweb\Models\KesmasSampleNumberSettings;
use Smt\Masterweb\Models\Sample;

class KesmasSampleNumberSettingsController extends Controller
{
    public function index()
    {
        $settings = KesmasSampleNumberSettings::getSettings();

        return view('masterweb::module.admin.settings.kesmas-sample-number-settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'is_nomor_sampel_manual' => 'boolean',
            'is_nomor_laboratorium_manual' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        if (!KesmasSampleNumberSettings::tableExists()) {
            return response()->json([
                'status' => false,
                'message' => 'Tabel ms_kesmas_sample_number_settings belum ada. Jalankan: php artisan migrate',
            ], 422);
        }

        try {
            $settings = KesmasSampleNumberSettings::getSettings();
            $wasManual = (bool) $settings->is_nomor_sampel_manual;
            $nowManual = $request->has('is_nomor_sampel_manual')
                ? (bool) $request->is_nomor_sampel_manual
                : false;

            $settings->update([
                'is_nomor_sampel_manual' => $nowManual,
                'is_nomor_laboratorium_manual' => $request->has('is_nomor_laboratorium_manual')
                    ? (bool) $request->is_nomor_laboratorium_manual
                    : false,
                'description' => $request->description ?? null,
            ]);

            $restored = 0;
            if ($nowManual && !$wasManual) {
                $restored = Sample::restoreKesmasManualSampleCodes();
            }

            $message = 'Setting Kesmas berhasil disimpan.';
            if ($restored > 0) {
                $message .= ' ' . $restored . ' nomor sampel manual dipulihkan dari cadangan.';
            }

            return response()->json([
                'status' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
