<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Smt\Masterweb\Models\PermohonanUji;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;

class MobileSigningController extends Controller
{
    public function home(Request $request)
    {
        $isAuthenticatedKlinik = $request->session()->get('mobile_sampling_klinik_auth', false);
        $isAuthenticatedKesmas = $request->session()->get('mobile_sampling_auth', false);
        return view('masterweb::module.mobile.signing.index', [
            'is_authenticated' => ($isAuthenticatedKlinik || $isAuthenticatedKesmas)
        ]);
    }

    public function inputId(Request $request)
    {
        $request->validate(['id_permohonan' => 'required|string']);
        $id = trim($request->id_permohonan);
        if (PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $id)->exists()) {
            return redirect()->route('mobile.signing.klinik.select', ['id' => $id]);
        }
        if (PermohonanUji::where('id_permohonan_uji', $id)->exists()) {
            return redirect()->route('mobile.signing.kesmas.nota', ['id' => $id]);
        }
        return redirect()->route('mobile.signing.home')->with('error', 'ID tidak ditemukan (bukan Kesmas atau Klinik).');
    }

    public function scan(Request $request, string $id)
    {
        if (PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $id)->exists()) {
            return redirect()->route('mobile.signing.klinik.select', ['id' => $id]);
        }
        if (PermohonanUji::where('id_permohonan_uji', $id)->exists()) {
            return redirect()->route('mobile.signing.kesmas.nota', ['id' => $id]);
        }
        return redirect()->route('mobile.signing.home')->with('error', 'ID tidak valid untuk penandatanganan.');
    }

    public function klinikSelect(Request $request, string $id)
    {
        $item = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $id)->with('pasien')->first();
        if (!$item) {
            return view('masterweb::module.mobile.signing.error', ['message' => 'Data klinik tidak ditemukan']);
        }

        $notaDone = !empty($item->signature_nota_pasien);
        $consentDone = !empty($item->signature_persetujuan_pasien);

        return view('masterweb::module.mobile.signing.klinik-select', [
            'permohonan' => $item,
            'nota_done' => $notaDone,
            'consent_done' => $consentDone,
        ]);
    }

    // Klinik Nota
    public function klinikNota(Request $request, string $id)
    {
        $item = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $id)->with('pasien')->first();
        if (!$item) {
            return view('masterweb::module.mobile.signing.error', ['message' => 'Data klinik tidak ditemukan']);
        }
        return view('masterweb::module.mobile.signing.klinik-nota', ['permohonan' => $item]);
    }

    // Kesmas Nota
    public function kesmasNota(Request $request, string $id)
    {
        $item = PermohonanUji::where('id_permohonan_uji', $id)->with('customer')->first();
        if (!$item) {
            return view('masterweb::module.mobile.signing.error', ['message' => 'Data kesmas tidak ditemukan']);
        }
        return view('masterweb::module.mobile.signing.kesmas-nota', ['permohonan' => $item]);
    }

    // Klinik Consent
    public function klinikConsent(Request $request, string $id)
    {
        $item = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $id)->with('pasien')->first();
        if (!$item) {
            return view('masterweb::module.mobile.signing.error', ['message' => 'Data klinik tidak ditemukan']);
        }
        return view('masterweb::module.mobile.signing.klinik-consent', ['permohonan' => $item]);
    }

    // Save signatures (placeholder: saves image to storage/public and returns success)
    public function saveKlinikNotaSignature(Request $request, string $id)
    {
        $request->validate(['sig_customer' => 'required|string', 'sig_officer' => 'nullable|string']);
        $item = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $id)->first();
        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Data klinik tidak ditemukan'], 404);
        }

        try {
            $pasienData = $request->post('sig_customer');
            $petugasData = $request->post('sig_officer');

            if (preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $pasienData)) {
                $pasienData = substr($pasienData, strpos($pasienData, ',') + 1);
            }
            $pasienBinary = base64_decode($pasienData);
            $item->signature_nota_pasien = $pasienBinary;

            if ($petugasData) {
                if (preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $petugasData)) {
                    $petugasData = substr($petugasData, strpos($petugasData, ',') + 1);
                }
                $petugasBinary = base64_decode($petugasData);
                $item->signature_nota_petugas = $petugasBinary;
            }

            $item->save();
            return response()->json(['status' => true]);
        } catch (\Throwable $e) {
            Log::error('Save klinik nota signature failed: '.$e->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal menyimpan tanda tangan'], 500);
        }
    }

    public function saveKesmasNotaSignature(Request $request, string $id)
    {
        $request->validate(['sig_customer' => 'required|string', 'sig_officer' => 'nullable|string']);
        $item = PermohonanUji::where('id_permohonan_uji', $id)->first();
        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Data kesmas tidak ditemukan'], 404);
        }

        try {
            $pasienData = $request->post('sig_customer');
            $petugasData = $request->post('sig_officer');

            if (preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $pasienData)) {
                $pasienData = substr($pasienData, strpos($pasienData, ',') + 1);
            }
            $pasienBinary = base64_decode($pasienData);
            $item->signature_nota_pasien = $pasienBinary;

            if ($petugasData) {
                if (preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $petugasData)) {
                    $petugasData = substr($petugasData, strpos($petugasData, ',') + 1);
                }
                $petugasBinary = base64_decode($petugasData);
                $item->signature_nota_petugas = $petugasBinary;
            }

            $item->save();
            return response()->json(['status' => true]);
        } catch (\Throwable $e) {
            Log::error('Save kesmas nota signature failed: '.$e->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal menyimpan tanda tangan'], 500);
        }
    }

    public function saveKlinikConsentSignature(Request $request, string $id)
    {
        $request->validate(['sig_patient' => 'required|string', 'sig_officer' => 'nullable|string']);
        $item = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $id)->first();
        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Data klinik tidak ditemukan'], 404);
        }

        try {
            $patientData = $request->post('sig_patient');
            $officerData = $request->post('sig_officer');

            if (preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $patientData)) {
                $patientData = substr($patientData, strpos($patientData, ',') + 1);
            }
            $patientBinary = base64_decode($patientData);
            $item->signature_persetujuan_pasien = $patientBinary;

            if ($officerData) {
                if (preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $officerData)) {
                    $officerData = substr($officerData, strpos($officerData, ',') + 1);
                }
                $officerBinary = base64_decode($officerData);
                $item->signature_persetujuan_petugas = $officerBinary;
            }

            $item->save();
            return response()->json(['status' => true]);
        } catch (\Throwable $e) {
            Log::error('Save klinik consent signature failed: '.$e->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal menyimpan tanda tangan'], 500);
        }
    }

    private function saveSignatureFiles()
    {
        try {
            $files = [];
            foreach (['sig_customer','sig_officer','sig_patient'] as $key) {
                $data = request()->post($key);
                if (!$data) continue;
                if (preg_match('/^data:image\/(png|jpg|jpeg);base64,/', $data)) {
                    $data = substr($data, strpos($data, ',') + 1);
                }
                $binary = base64_decode($data);
                $name = 'signatures/'.date('Y/m/d/').$key.'_'.uniqid().'.png';
                Storage::disk('public')->put($name, $binary);
                $files[$key] = Storage::url($name);
            }
            return response()->json(['status' => true, 'files' => $files]);
        } catch (\Throwable $e) {
            Log::error('Save signature failed: '.$e->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal menyimpan tanda tangan'], 500);
        }
    }
}