<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Smt\Masterweb\Models\KlinikNumberSettings;
use Illuminate\Support\Facades\DB;

class KlinikNumberSettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        $settings = KlinikNumberSettings::getSettings();
        return view('masterweb::module.admin.settings.klinik-number-settings', compact('settings'));
    }

    /**
     * Update the settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'is_nomor_lab_manual' => 'boolean',
            'is_nomor_spesimen_manual' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $settings = KlinikNumberSettings::getSettings();
            $settings->update([
                'is_nomor_lab_manual' => $request->has('is_nomor_lab_manual') ? (bool)$request->is_nomor_lab_manual : false,
                'is_nomor_spesimen_manual' => $request->has('is_nomor_spesimen_manual') ? (bool)$request->is_nomor_spesimen_manual : false,
                'description' => $request->description ?? null,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Setting berhasil diupdate!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengupdate setting: ' . $e->getMessage()
            ], 500);
        }
    }
}
