<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Smt\Masterweb\Models\User;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\VerificationActivitySample;
use Smt\Masterweb\Models\VerificationActivity;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\PermohonanUjiPaketKlinik;
use Smt\Masterweb\Models\PengambilanSampleKlinik;
use Smt\Masterweb\Models\BakuMutu;
use Smt\Masterweb\Helpers\SatuSehatHelper;
use Smt\Masterweb\Helpers\DateHelper;
use Smt\Masterweb\Models\Pasien;
use Smt\Masterweb\Models\Petugas;
use Smt\Masterweb\Models\Unit;
use Smt\Masterweb\Models\ParameterSatuanKlinik;
use Smt\Masterweb\Models\SatuSehatLocation;
use Smt\Masterweb\Models\SatuSehatPractitioner;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class MobileTestingKlinikController extends Controller
{
    protected $satuSehatHelper;

    public function __construct(SatuSehatHelper $satuSehatHelper)
    {
        $this->satuSehatHelper = $satuSehatHelper;
    }
    /**
     * Mobile testing klinik home: scan or input ID permohonan
     */
    public function home(Request $request)
    {
        $isAuthenticated = $request->session()->get('mobile_testing_klinik_auth', false);
        return view('masterweb::module.mobile.testing.klinik.index', [
            'is_authenticated' => $isAuthenticated
        ]);
    }

    /**
     * Handle manual input of ID permohonan
     */
    public function inputId(Request $request)
    {
        $request->validate([
            'id_permohonan' => 'required|string'
        ]);

        $id_permohonan = $request->input('id_permohonan');
        
        // Check if permohonan exists
        $permohonan = PermohonanUjiKlinik2::find($id_permohonan);
        
        if (!$permohonan) {
            return redirect()->route('mobile.testing.klinik.home')
                ->with('error', 'ID Permohonan tidak ditemukan.');
        }

        // Check if user is authenticated
        $isAuthenticated = $request->session()->get('mobile_testing_klinik_auth', false);
        
        if (!$isAuthenticated) {
            // Store ID in session and redirect to login
            $request->session()->put('mobile_testing_klinik_temp_id', $id_permohonan);
            return redirect()->route('mobile.testing.klinik.login', ['id' => $id_permohonan]);
        }

        // Redirect to appropriate step
        return $this->redirectToCorrectStep($request, $id_permohonan);
    }

    /**
     * Scan QR code handler
     */
    public function scan(Request $request, $id)
    {
        $permohonan = PermohonanUjiKlinik2::find($id);
        
        if (!$permohonan) {
            return redirect()->route('mobile.testing.klinik.home')
                ->with('error', 'ID Permohonan tidak ditemukan.');
        }

        // Check if user is authenticated
        $isAuthenticated = $request->session()->get('mobile_testing_klinik_auth', false);
        
        if (!$isAuthenticated) {
            // Store ID in session and redirect to login
            $request->session()->put('mobile_testing_klinik_temp_id', $id);
            return redirect()->route('mobile.testing.klinik.login', ['id' => $id]);
        }

        // Redirect to appropriate step
        return $this->redirectToCorrectStep($request, $id);
    }

    /**
     * Login page
     */
    public function login(Request $request, $id)
    {
        $permohonan = PermohonanUjiKlinik2::find($id);
        
        if (!$permohonan) {
            return redirect()->route('mobile.testing.klinik.home')
                ->with('error', 'ID Permohonan tidak ditemukan.');
        }

        return view('masterweb::module.mobile.testing.klinik.login', [
            'id' => $id,
            'permohonan' => $permohonan
        ]);
    }

    /**
     * Handle login
     */
    public function doLogin(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // Find user by username or email
        $user = User::where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return redirect()->route('mobile.testing.klinik.login', ['id' => $id])
                ->with('error', 'Username atau password salah.');
        }

        // Check user level - must be ANLS, SOLAB, or ADMIN
        $userLevel = $user->getlevel->level ?? null;
        $allowedLevels = ['ANLS','ALAB', 'SOLK', 'ADMIN','LAB'];
        
        if (!in_array($userLevel, $allowedLevels)) {
            return redirect()->route('mobile.testing.klinik.login', ['id' => $id])
                ->with('error', 'Anda tidak memiliki akses untuk mobile testing klinik.');
        }

        try {
            SatuSehatHelper::ensureAccessToken();
        } catch (\Throwable $e) {
            Log::warning('Satu Sehat token refresh skipped for mobile testing klinik: ' . $e->getMessage());
        }

        // Store session
        $request->session()->put('mobile_testing_klinik_auth', true);
        $request->session()->put('mobile_testing_klinik_user_id', $user->id);
        $request->session()->put('mobile_testing_klinik_user_name', $user->name);
        $request->session()->put('mobile_testing_klinik_user_level', $userLevel);

        // Redirect to appropriate step
        return $this->redirectToCorrectStep($request, $id);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->session()->forget('mobile_testing_klinik_auth');
        $request->session()->forget('mobile_testing_klinik_user_id');
        $request->session()->forget('mobile_testing_klinik_user_name');
        $request->session()->forget('mobile_testing_klinik_user_level');
        $request->session()->forget('mobile_testing_klinik_temp_id');

        return redirect()->route('mobile.testing.klinik.home')
            ->with('success', 'Anda telah logout.');
    }

    /**
     * Redirect to correct step based on verification status
     */
    private function redirectToCorrectStep(Request $request, $id_permohonan)
    {
        // Get all verification activities for this permohonan
        $verification_activities = VerificationActivitySample::where('is_klinik', $id_permohonan)
            ->where('resampling', 0)
            ->get()
            ->keyBy('id_verification_activity');

        // Check if step 6 (Pengambilan Sample) is done - required before any testing-klinik step
        $step6_done = $verification_activities->get(6) && $verification_activities->get(6)->is_done == 1;

        if (!$step6_done) {
            // If step 6 not done, redirect to home with error message
            return redirect()->route('mobile.testing.klinik.home')
                ->with('error', 'Proses pengambilan sample belum selesai. Silakan selesaikan pengambilan sample terlebih dahulu sebelum melakukan testing-klinik.');
        }

        // Check step status
        $step7_done = $verification_activities->get(7) && $verification_activities->get(7)->is_done == 1; // Penerima Sampel
        $step2_done = $verification_activities->get(2) && $verification_activities->get(2)->is_done == 1; // Pengolah Sampel
        $step3_done = $verification_activities->get(3) && $verification_activities->get(3)->is_done == 1; // Pemeriksa Sampel
        $step4_done = $verification_activities->get(4) && $verification_activities->get(4)->is_done == 1; // Verifikasi

        // Redirect to first incomplete step
        if (!$step7_done) {
            return redirect()->route('mobile.testing.klinik.penerimaan', ['id' => $id_permohonan]);
        } elseif (!$step2_done) {
            return redirect()->route('mobile.testing.klinik.pengolah', ['id' => $id_permohonan]);
        } elseif (!$step3_done) {
            return redirect()->route('mobile.testing.klinik.pemeriksa', ['id' => $id_permohonan]);
        } elseif (!$step4_done) {
            return redirect()->route('mobile.testing.klinik.verifikasi', ['id' => $id_permohonan]);
        } else {
            // All steps done, show status
            return redirect()->route('mobile.testing.klinik.status', ['id' => $id_permohonan]);
        }
    }

    /**
     * Step 1: Penerimaan Sampel (Step 7)
     */
    public function penerimaan(Request $request, $id)
    {

        $user =User::find(  $request->session()->get('mobile_testing_klinik_user_id', false));
        
        $permohonan = PermohonanUjiKlinik2::find($id);
        
        if (!$permohonan) {
            return redirect()->route('mobile.testing.klinik.home')
                ->with('error', 'ID Permohonan tidak ditemukan.');
        }

        // Check authentication
        if (!$request->session()->get('mobile_testing_klinik_auth', false)) {
            $request->session()->put('mobile_testing_klinik_temp_id', $id);
            return redirect()->route('mobile.testing.klinik.login', ['id' => $id]);
        }

        // Check if step 6 (Pengambilan Sample) is done
        $step6 = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 6)
            ->where('is_done', 1)
            ->where('resampling', 0)
            ->first();

        if (!$step6) {
            return redirect()->route('mobile.testing.klinik.home')
                ->with('error', 'Proses pengambilan sample belum selesai. Silakan selesaikan pengambilan sample terlebih dahulu sebelum melakukan testing-klinik.');
        }

        $user_id = $request->session()->get('mobile_testing_klinik_user_id');
        $user_name = $user->petugas->getSatuSehatPractitioner()->name_petugas ?? $request->session()->get('mobile_testing_klinik_user_name', 'Petugas');
        $user_level = $request->session()->get('mobile_testing_klinik_user_level', '');
        $is_analis = ($user_level === 'ANLS' || $user_level === 'ALAB' );
        // Get existing verification activity
        $verification = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 7)
            ->where('resampling', 0)
            ->first();

        // Get petugas list if not analis (Step 7 = Penerima Sampel)
        $petugas_list = [];
        $selected_petugas = null;
        if (!$is_analis) {
            $petugas_list = $this->getPetugasList(7);
            // Check if user's petugas is in the list
            $selected_petugas = $this->getUserPetugasInList($user_id, $petugas_list);
        }

        // Get latest sampling data
        $latest_sampling = PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id)
            ->where('status_sampling', 'Berhasil')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->first();

        // Get jenis sampel as array
        $jenis_sampel_array = [];
        if ($latest_sampling && !empty($latest_sampling->jenis_sample)) {
            if (is_string($latest_sampling->jenis_sample)) {
                $decoded = json_decode($latest_sampling->jenis_sample, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $jenis_sampel_array = $decoded;
                } else {
                    // Jika ada koma, split menjadi array
                    if (strpos($latest_sampling->jenis_sample, ',') !== false) {
                        $jenis_sampel_array = array_map('trim', explode(',', $latest_sampling->jenis_sample));
                    } else {
                        $jenis_sampel_array = [$latest_sampling->jenis_sample];
                    }
                }
            } else if (is_array($latest_sampling->jenis_sample)) {
                $jenis_sampel_array = $latest_sampling->jenis_sample;
            } else {
                $jenis_sampel_array = [$latest_sampling->jenis_sample];
            }
        } else if ($permohonan->jenis_sampel !== null) {
            $jenis_sampel = $permohonan->jenis_sampel;
            if (is_string($jenis_sampel)) {
                if (strpos($jenis_sampel, ',') !== false) {
                    $jenis_sampel_array = array_map('trim', explode(',', $jenis_sampel));
                } else {
                    $jenis_sampel_array = [$jenis_sampel];
                }
            } elseif (is_array($jenis_sampel)) {
                $jenis_sampel_array = $jenis_sampel;
            }
        }

        // If no jenis sampel found, use default common types
        if (empty($jenis_sampel_array)) {
            $jenis_sampel_array = ['Darah', 'Serum', 'Plasma', 'Urine'];
        }

        // Parse existing penerimaan data (JSON)
        $penerimaan_sampel_data = [];
        $penerimaan_sampel_raw = $permohonan->penerimaan_sampel ?? '';
        if (!empty($penerimaan_sampel_raw)) {
            if (is_string($penerimaan_sampel_raw)) {
                $decoded = json_decode($penerimaan_sampel_raw, true);
                $penerimaan_sampel_data = is_array($decoded) ? $decoded : [];
            } elseif (is_array($penerimaan_sampel_raw)) {
                $penerimaan_sampel_data = $penerimaan_sampel_raw;
            }
        }

        // Parse existing volume data (JSON)
        $volume_sampel_data = [];
        $volume_sampel_raw = $permohonan->volume_sampel ?? '';
        if (!empty($volume_sampel_raw)) {
            if (is_string($volume_sampel_raw)) {
                $decoded = json_decode($volume_sampel_raw, true);
                $volume_sampel_data = is_array($decoded) ? $decoded : [];
            } elseif (is_array($volume_sampel_raw)) {
                $volume_sampel_data = $volume_sampel_raw;
            }
        }

        // Parse existing kualitas data (JSON)
        $kualitas_sampel_data = [];
        $kualitas_sampel_raw = $permohonan->kualitas_sampel ?? '';
        if (!empty($kualitas_sampel_raw)) {
            if (is_string($kualitas_sampel_raw)) {
                $decoded = json_decode($kualitas_sampel_raw, true);
                $kualitas_sampel_data = is_array($decoded) ? $decoded : [];
            } elseif (is_array($kualitas_sampel_raw)) {
                $kualitas_sampel_data = $kualitas_sampel_raw;
            }
        }

        // For display purposes
        $jenis_sampel = implode(', ', $jenis_sampel_array);

        return view('masterweb::module.mobile.testing.klinik.penerimaan', [
            'id' => $id,
            'permohonan' => $permohonan,
            'verification' => $verification,
            'user_name' => $user_name,
            'is_analis' => $is_analis,
            'petugas_list' => $petugas_list,
            'selected_petugas' => $selected_petugas,
            'latest_sampling' => $latest_sampling,
            'jenis_sampel' => $jenis_sampel,
            'jenis_sampel_array' => $jenis_sampel_array,
            'penerimaan_sampel_data' => $penerimaan_sampel_data,
            'volume_sampel_data' => $volume_sampel_data,
            'kualitas_sampel_data' => $kualitas_sampel_data
        ]);
    }

    /**
     * Store Penerimaan Sampel
     */
    public function storePenerimaan(Request $request, $id)
    {

        $user =User::find(  $request->session()->get('mobile_testing_klinik_user_id', false));
       


        $user_level = $request->session()->get('mobile_testing_klinik_user_level', '');
        $is_analis = ($user_level === 'ANLS' || $user_level === 'ALAB' ); 
        $rules = [
            'waktu' => 'required|string',
            'penerimaan_sampel' => 'required|array',
            'penerimaan_sampel.*' => 'required|string',
            'volume_sampel' => 'required|array',
            'volume_sampel.*' => 'required|string',
            'kualitas_sampel' => 'required|array',
            'kualitas_sampel.*' => 'required|array|min:1'
        ];
        
        if (!$is_analis) {
            $rules['nama_petugas'] = 'required|string';
        }
        
        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Determine petugas name
            if ($is_analis) {
                $user_name = $user->petugas->getSatuSehatPractitioner()->name_petugas ?? $request->session()->get('mobile_testing_klinik_user_name', 'Petugas');
            } else {
                $user_name = $request->input('nama_petugas');
                if (empty($user_name)) {
                    throw new \Exception('Nama petugas wajib dipilih.');
                }
            }

            
            $waktu = $request->input('waktu'); // Format: HH:mm

            // Tanggal registrasi + jam klik (bukan hari ini)
            $start_date = $this->clockOnRegisterDateForPermohonan($id, $waktu);
            $stop_date = $start_date->copy(); // Same time for klinik

            // Update or create verification activity
            $verification = VerificationActivitySample::where('is_klinik', $id)
                ->where('id_verification_activity', 7)
                ->where('resampling', 0)
                ->first();

            if ($verification) {
                $verification->start_date = $start_date->format('Y-m-d H:i:s');
                $verification->stop_date = $stop_date->format('Y-m-d H:i:s');
                $verification->nama_petugas = $user_name;
                $verification->is_done = 1;
                $verification->save();
            } else {
                $verification = new VerificationActivitySample();
                $verification->id = Uuid::uuid4()->toString();
                $verification->is_klinik = $id;
                $verification->id_verification_activity = 7;
                $verification->start_date = $start_date->format('Y-m-d H:i:s');
                $verification->stop_date = $stop_date->format('Y-m-d H:i:s');
                $verification->nama_petugas = $user_name;
                $verification->is_done = 1;
                $verification->resampling = 0;
                $verification->save();
            }

            // Update permohonan data
            $permohonan = PermohonanUjiKlinik2::find($id);
            
            // Penerimaan sampel - handle JSON format
            if ($request->has('penerimaan_sampel')) {
                $penerimaan_sampel_data = $request->input('penerimaan_sampel');
                if (is_array($penerimaan_sampel_data)) {
                    $permohonan->penerimaan_sampel = json_encode($penerimaan_sampel_data);
                } else {
                    $permohonan->penerimaan_sampel = $penerimaan_sampel_data;
                }
            }
            
            // Volume sampel - handle JSON format
            if ($request->has('volume_sampel')) {
                $volume_sampel_data = $request->input('volume_sampel');
                if (is_array($volume_sampel_data)) {
                    $permohonan->volume_sampel = json_encode($volume_sampel_data);
                } else {
                    $permohonan->volume_sampel = $volume_sampel_data;
                }
            }
            
            // Kualitas sampel - handle JSON format and legacy columns
            if ($request->has('kualitas_sampel')) {
                $kualitas_sampel_data = $request->input('kualitas_sampel');
                if (is_array($kualitas_sampel_data)) {
                    $permohonan->kualitas_sampel = json_encode($kualitas_sampel_data);
                    
                    // Update legacy columns for backward compatibility
                    $all_qualities = [];
                    foreach ($kualitas_sampel_data as $sampel_type => $qualities) {
                        if (is_array($qualities)) {
                            $all_qualities = array_merge($all_qualities, $qualities);
                        }
                    }
                    $permohonan->kualitas_lisis = in_array('Lisis', $all_qualities) ? 1 : 0;
                    $permohonan->kualitas_ikterik = in_array('Ikterik', $all_qualities) ? 1 : 0;
                    $permohonan->kualitas_lipemik = in_array('Lipemik', $all_qualities) ? 1 : 0;
                    $permohonan->kualitas_cukup = in_array('Cukup', $all_qualities) ? 1 : 0;
                    $permohonan->kualitas_beku = in_array('Beku', $all_qualities) ? 1 : 0;
                } else {
                    $permohonan->kualitas_sampel = $kualitas_sampel_data;
                }
            }
            
            $permohonan->save();

            // Send to Satu Sehat - Step 7 (Penerimaan Sampel)
            try {
                if (config('services.satu_sehat.version') == 'prd') {
                    $newStatusHistory = [
                        "status" => "in-progress",
                        "period" => [
                            "start" => $start_date->toIso8601String(),
                            "end" => $stop_date->toIso8601String()
                        ]
                    ];

                    $this->updateEncounter(
                        $id,
                        $user_name,
                        "in-progress",
                        "Ruang Penerimaan Sampel",
                        "OBSENC",
                        "Petugas penerimaan sampel",
                        "PRCP",
                        $start_date->toIso8601String(),
                        $stop_date->toIso8601String(),
                        $newStatusHistory
                    );

                    // Update Specimen di SatuSehat (sama seperti di LaboratoriumPermohonanUjiKlinikManagement2)
                    try {
                        // Cari PengambilanSampleKlinik yang berhasil (status_sampling = 'berhasil')
                        // Ambil yang terbaru untuk permohonan ini
                        $pengambilanSample = PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id)
                            ->where('status_sampling', 'berhasil')
                            ->whereNull('deleted_at')
                            ->whereNotNull('id_spesimen_satu_sehat')
                            ->where('id_spesimen_satu_sehat', '!=', '')
                            ->orderBy('created_at', 'desc')
                            ->first();

                        if ($pengambilanSample && !empty($pengambilanSample->id_spesimen_satu_sehat)) {
                            // Get specimen IDs (bisa multiple, dipisah koma)
                            $specimenIds = array_filter(array_map('trim', explode(',', $pengambilanSample->id_spesimen_satu_sehat)));

                            // Update setiap specimen
                            foreach ($specimenIds as $specimenId) {
                                if (empty($specimenId)) continue;

                                // Get existing Specimen from SatuSehat
                                $getResponse = $this->satuSehatHelper->get('Specimen/' . $specimenId);

                                if ($getResponse['status_code'] == 200 && isset($getResponse['body'])) {
                                    $existingSpecimen = $getResponse['body'];

                                    // Build condition array based on kualitas flags
                                    $conditions = [];
                                    if ($permohonan->kualitas_cukup) {
                                        $conditions[] = ["text" => "Cukup"];
                                    }
                                    if ($permohonan->kualitas_lisis) {
                                        $conditions[] = ["text" => "Lisis"];
                                    }
                                    if ($permohonan->kualitas_ikterik) {
                                        $conditions[] = ["text" => "Ikterik"];
                                    }
                                    if ($permohonan->kualitas_lipemik) {
                                        $conditions[] = ["text" => "Lipemik"];
                                    }
                                    if ($permohonan->kualitas_beku) {
                                        $conditions[] = ["text" => "Beku"];
                                    }

                                    // Build note text
                                    $noteText = "Sampel diterima";
                                    $conditionTexts = [];
                                    if ($permohonan->kualitas_cukup) $conditionTexts[] = "cukup";
                                    if ($permohonan->kualitas_lisis) $conditionTexts[] = "sedikit lisis";
                                    if ($permohonan->kualitas_ikterik) $conditionTexts[] = "ikterik";
                                    if ($permohonan->kualitas_lipemik) $conditionTexts[] = "lipemik";
                                    if ($permohonan->kualitas_beku) $conditionTexts[] = "beku";

                                    if (!empty($conditionTexts)) {
                                        $noteText .= " dalam kondisi " . implode(", ", $conditionTexts);
                                    }

                                    // Handle volume_sampel (bisa array atau string)
                                    $volumeSampel = $permohonan->volume_sampel;
                                    if (is_string($volumeSampel)) {
                                        $decoded = json_decode($volumeSampel, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                            // Jika array, ambil yang pertama atau gabungkan
                                            $volumeSampel = !empty($decoded) ? implode(", ", $decoded) : null;
                                        }
                                    }

                                    if (!empty($volumeSampel)) {
                                        $noteText .= ". Volume " . $volumeSampel;
                                    }

                                    // Format receivedTime (gunakan waktu dari start_date)
                                    $receivedTime = $start_date->toIso8601String();

                                    // Update Specimen data
                                    $existingSpecimen['receivedTime'] = $receivedTime;
                                    if (!empty($conditions)) {
                                        $existingSpecimen['condition'] = $conditions;
                                    }
                                    $existingSpecimen['note'] = [
                                        [
                                            "text" => $noteText
                                        ]
                                    ];

                                    // Update quantity jika volume_sampel ada
                                    if (!empty($volumeSampel) && isset($existingSpecimen['collection']['quantity'])) {
                                        // Extract numeric value and unit from volume_sampel (e.g., "5 mL" or "5")
                                        preg_match('/(\d+(?:\.\d+)?)\s*(mL|ml|L|l|g|kg)?/i', $volumeSampel, $matches);
                                        if (!empty($matches[1])) {
                                            $existingSpecimen['collection']['quantity']['value'] = (float) $matches[1];
                                            if (!empty($matches[2])) {
                                                $existingSpecimen['collection']['quantity']['unit'] = $matches[2];
                                            } else {
                                                $existingSpecimen['collection']['quantity']['unit'] = 'mL'; // Default unit
                                            }
                                        }
                                    }

                                    // PUT update to SatuSehat
                                    $putResponse = $this->satuSehatHelper->put('Specimen', $specimenId, $existingSpecimen);

                                    if ($putResponse['status_code'] == 200 || $putResponse['status_code'] == 201) {
                                        Log::info('Specimen updated successfully in SatuSehat', [
                                            'specimen_id' => $specimenId,
                                            'permohonan_id' => $id
                                        ]);
                                    } else {
                                        Log::warning('Failed to update Specimen in SatuSehat', [
                                            'specimen_id' => $specimenId,
                                            'response' => $putResponse
                                        ]);
                                    }
                                } else {
                                    Log::warning('Failed to get existing Specimen from SatuSehat', [
                                        'specimen_id' => $specimenId,
                                        'response' => $getResponse
                                    ]);
                                }
                            }
                        } else {
                            Log::info('No PengambilanSampleKlinik with specimen ID found for permohonan', [
                                'permohonan_id' => $id
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error updating Specimen in SatuSehat: ' . $e->getMessage(), [
                            'permohonan_id' => $id,
                            'exception' => $e->getTraceAsString()
                        ]);
                        // Continue transaction even if Specimen update fails
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error sending to SatuSehat in storePenerimaan: ' . $e->getMessage(), [
                    'permohonan_id' => $id,
                    'exception' => $e->getTraceAsString()
                ]);
                // Continue transaction even if Satu Sehat fails
            }

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data penerimaan sampel berhasil disimpan.',
                    'redirect_url' => route('mobile.testing.klinik.pengolah', ['id' => $id])
                ]);
            }

            return redirect()->route('mobile.testing.klinik.pengolah', ['id' => $id])
                ->with('success', 'Data penerimaan sampel berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Step 2: Pengolah Sampel (Step 2)
     */
    public function pengolah(Request $request, $id)
    {
        $permohonan = PermohonanUjiKlinik2::find($id);
        
        if (!$permohonan) {
            return redirect()->route('mobile.testing.klinik.home')
                ->with('error', 'ID Permohonan tidak ditemukan.');
        }

        // Check authentication
        if (!$request->session()->get('mobile_testing_klinik_auth', false)) {
            $request->session()->put('mobile_testing_klinik_temp_id', $id);
            return redirect()->route('mobile.testing.klinik.login', ['id' => $id]);
        }

        // Check if step 6 (Pengambilan Sample) is done
        $step6 = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 6)
            ->where('is_done', 1)
            ->where('resampling', 0)
            ->first();

        if (!$step6) {
            return redirect()->route('mobile.testing.klinik.home')
                ->with('error', 'Proses pengambilan sample belum selesai. Silakan selesaikan pengambilan sample terlebih dahulu sebelum melakukan testing-klinik.');
        }

        // Check if step 7 is done
        $step7 = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 7)
            ->where('resampling', 0)
            ->first();

        if (!$step7 || $step7->is_done != 1) {
            return redirect()->route('mobile.testing.klinik.penerimaan', ['id' => $id])
                ->with('error', 'Silakan selesaikan penerimaan sampel terlebih dahulu.');
        }

        $user_id = $request->session()->get('mobile_testing_klinik_user_id');
        $user_name = $request->session()->get('mobile_testing_klinik_user_name', 'Petugas');
        $user_level = $request->session()->get('mobile_testing_klinik_user_level', '');
        $is_analis = ($user_level === 'ANLS' || $user_level === 'ALAB' );
        // Get existing verification activity
        $verification = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 2)
            ->where('resampling', 0)
            ->first();

        $petugas = User::find($user_id)->petugas;
        $user_name = isset($petugas) ? $petugas->nama :  $user_name;
        if (isset($petugas)) {
            $is_analis = true;
        } else {
            $is_analis = false;
        }


        // Get petugas list if not analis (Step 2 = Pemeriksaan / Analitik)
        $petugas_list = [];
        $selected_petugas = null;
        if (!$is_analis) {
            $petugas_list = $this->getPetugasList(2);
            // Check if user's petugas is in the list
            $selected_petugas = $this->getUserPetugasInList($user_id, $petugas_list);
        }

        return view('masterweb::module.mobile.testing.klinik.pengolah', [
            'id' => $id,
            'permohonan' => $permohonan,
            'verification' => $verification,
            'user_name' => $user_name,
            'is_analis' => $is_analis,
            'petugas_list' => $petugas_list,
            'selected_petugas' => $selected_petugas
        ]);
    }

    /**
     * Store Pengolah Sampel
     */
    public function storePengolah(Request $request, $id)
    {
        $user_level = $request->session()->get('mobile_testing_klinik_user_level', '');

        $user_id = $request->session()->get('mobile_testing_klinik_user_id');
        $user_name = $request->session()->get('mobile_testing_klinik_user_name', 'Petugas');
        $petugas = User::find($user_id)->petugas;
        $user_name = isset($petugas) ? $petugas->nama :  $user_name;
        if (isset($petugas)) {
            $is_analis = true;
        } else {
            $is_analis = false;
        }
        $rules = [
            'waktu' => 'required|string'
        ];
        
        if (!$is_analis) {
            $rules['nama_petugas'] = 'required|string';
        }
        
        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Determine petugas name
            

            $user_id = $request->session()->get('mobile_testing_klinik_user_id');
            $user_name = $request->session()->get('mobile_testing_klinik_user_name', 'Petugas');
            $petugas = User::find($user_id)->petugas;
            $user_name = isset($petugas) ? $petugas->nama :  $user_name;
            if (isset($petugas)) {
                $is_analis = true;
            } else {
                $is_analis = false;
                $user_name = $request->input('nama_petugas');
                if (empty($user_name)) {
                    throw new \Exception('Nama petugas wajib dipilih.');
                }
            }
            
            $waktu = $request->input('waktu'); // Format: HH:mm

            // Tanggal registrasi + jam klik (bukan hari ini)
            $start_date = $this->clockOnRegisterDateForPermohonan($id, $waktu);
            $stop_date = $start_date->copy(); // Same time for klinik

            // Update or create verification activity
            $verification = VerificationActivitySample::where('is_klinik', $id)
                ->where('id_verification_activity', 2)
                ->where('resampling', 0)
                ->first();

            if ($verification) {
                $verification->start_date = $start_date->format('Y-m-d H:i:s');
                $verification->stop_date = $stop_date->format('Y-m-d H:i:s');
                $verification->nama_petugas = $user_name;
                $verification->is_done = 1;
                $verification->save();
            } else {
                $verification = new VerificationActivitySample();
                $verification->id = Uuid::uuid4()->toString();
                $verification->is_klinik = $id;
                $verification->id_verification_activity = 2;
                $verification->start_date = $start_date->format('Y-m-d H:i:s');
                $verification->stop_date = $stop_date->format('Y-m-d H:i:s');
                $verification->nama_petugas = $user_name;
                $verification->is_done = 1;
                $verification->resampling = 0;
                $verification->save();
            }

            // Send to Satu Sehat - Step 2 (Pengolah Sampel)
            try {
                if (config('services.satu_sehat.version') == 'prd') {
                    $newStatusHistory = [
                        "status" => "in-progress",
                        "period" => [
                            "start" => $start_date->toIso8601String(),
                            "end" => $stop_date->toIso8601String()
                        ]
                    ];

                    $this->updateEncounter(
                        $id,
                        $user_name,
                        "in-progress",
                        "Ruang Pengolah Sampel",
                        "OBSENC",
                        "Petugas pengolah sampel",
                        "PPRF",
                        $start_date->toIso8601String(),
                        $stop_date->toIso8601String(),
                        $newStatusHistory
                    );
                }
            } catch (\Exception $e) {
                Log::error('Error sending to SatuSehat in storePengolah: ' . $e->getMessage(), [
                    'permohonan_id' => $id,
                    'exception' => $e->getTraceAsString()
                ]);
                // Continue transaction even if Satu Sehat fails
            }

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pengolah sampel berhasil disimpan.',
                    'redirect_url' => route('mobile.testing.klinik.pemeriksa', ['id' => $id])
                ]);
            }

            return redirect()->route('mobile.testing.klinik.pemeriksa', ['id' => $id])
                ->with('success', 'Data pengolah sampel berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Step 3: Pemeriksa Sampel (Step 3) - Similar to baca-hasil
     */
    public function pemeriksa(Request $request, $id)
    {
        $permohonan = PermohonanUjiKlinik2::find($id);
        
        if (!$permohonan) {
            return redirect()->route('mobile.testing.klinik.home')
                ->with('error', 'ID Permohonan tidak ditemukan.');
        }

        // Check authentication
        if (!$request->session()->get('mobile_testing_klinik_auth', false)) {
            $request->session()->put('mobile_testing_klinik_temp_id', $id);
            return redirect()->route('mobile.testing.klinik.login', ['id' => $id]);
        }

        // Check if step 6 (Pengambilan Sample) is done
        $step6 = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 6)
            ->where('is_done', 1)
            ->where('resampling', 0)
            ->first();

        if (!$step6) {
            return redirect()->route('mobile.testing.klinik.home')
                ->with('error', 'Proses pengambilan sample belum selesai. Silakan selesaikan pengambilan sample terlebih dahulu sebelum melakukan testing-klinik.');
        }

        // Check if step 2 is done
        $step2 = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 2)
            ->where('resampling', 0)
            ->first();

        if (!$step2 || $step2->is_done != 1) {
            return redirect()->route('mobile.testing.klinik.pengolah', ['id' => $id])
                ->with('error', 'Silakan selesaikan pengolah sampel terlebih dahulu.');
        }

        $user_id = $request->session()->get('mobile_testing_klinik_user_id');
        $user_name = $request->session()->get('mobile_testing_klinik_user_name', 'Petugas');
        $user_level = $request->session()->get('mobile_testing_klinik_user_level', '');
        $is_analis = ($user_level === 'ANLS' || $user_level === 'ALAB' );

        $petugas = User::find($user_id)->petugas;
        $user_name = isset($petugas) ? $petugas->nama :  $user_name;
        if (isset($petugas)) {
            $is_analis = true;
        } else {
            $is_analis = false;
        }


        // Get existing verification activity
        $verification = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 3)
            ->where('resampling', 0)
            ->first();

        // Get parameters - only get parameters with jenis_pemeriksaan = 0 (bukan sub parameter)
        $parameters = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id)
            ->whereHas('parametersatuanklinik', function ($query) {
                $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '0')
                    ->whereNull('deleted_at');
            })
            ->whereNull('deleted_at')
            ->with(['parametersatuanklinik', 'unit'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Get patient data for baku mutu calculation
        $pasien_umur = $permohonan->umurtahun_pasien_permohonan_uji_klinik ?? 0;
        $pasien_gender = $permohonan->pasien->gender_pasien ?? null;

        // Enrich parameters with baku mutu data
        $parameters = $parameters->map(function($parameter) use ($pasien_umur, $pasien_gender) {
            $baku_mutu_result = $this->getBakuMutuForParameter($parameter, $pasien_umur, $pasien_gender);
            $parameter->baku_mutu_data = $baku_mutu_result['selected'];
            $parameter->baku_mutu_multiple = $baku_mutu_result['multiple'];
            $parameter->baku_mutu_all = $baku_mutu_result['all'];
            return $parameter;
        });

        // Get petugas list if not analis (Step 3 = Input / Output Hasil Px)
        $petugas_list = [];
        $selected_petugas = null;
        if (!$is_analis) {
            $petugas_list = $this->getPetugasList(3);
            // Check if user's petugas is in the list
            $selected_petugas = $this->getUserPetugasInList($user_id, $petugas_list);
        }

        // Get pengambilan sample data
        $pengambilan_sample = PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->first();

        // Get pengambilan sample verification data (Step 6)
        $pengambilan_verification = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 6)
            ->where('is_done', 1)
            ->where('resampling', 0)
            ->first();

        // Get penerimaan sampel data (Step 7)
        $penerimaan_verification = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 7)
            ->where('resampling', 0)
            ->first();

        // Get pengolah sampel data (Step 2)
        $pengolah_verification = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 2)
            ->where('resampling', 0)
            ->first();

        return view('masterweb::module.mobile.testing.klinik.pemeriksa', [
            'id' => $id,
            'permohonan' => $permohonan,
            'verification' => $verification,
            'user_name' => $user_name,
            'is_analis' => $is_analis,
            'petugas_list' => $petugas_list,
            'selected_petugas' => $selected_petugas,
            'parameters' => $parameters,
            'pengambilan_sample' => $pengambilan_sample,
            'pengambilan_verification' => $pengambilan_verification,
            'penerimaan_verification' => $penerimaan_verification,
            'pengolah_verification' => $pengolah_verification
        ]);
    }

    /**
     * Store Pemeriksa Sampel
     */
    public function storePemeriksa(Request $request, $id)
    {
        $user_level = $request->session()->get('mobile_testing_klinik_user_level', '');
        $is_analis = ($user_level === 'ANLS' || $user_level === 'ALAB' );
        $rules = [
            'waktu' => 'required|string',
            'parameters' => 'required|array',
            'parameters.*.id' => 'required|string',
            'parameters.*.hasil' => 'nullable|string'
        ];
        
        if (!$is_analis) {
            $rules['nama_petugas'] = 'required|string';
        }
        
        $request->validate($rules);

        try {
            DB::beginTransaction();

            $user_level = $request->session()->get('mobile_testing_klinik_user_level', '');
           // Determine petugas name
           
            $is_analis = ($user_level === 'ANLS' || $user_level === 'ALAB' ); 
           
            $user_id = $request->session()->get('mobile_testing_klinik_user_id');
            $user_name = $request->session()->get('mobile_testing_klinik_user_name', 'Petugas');
            $petugas = User::find($user_id)->petugas;
            $user_name = isset($petugas) ? $petugas->nama :  $user_name;
            if (isset($petugas)) {
                $is_analis = true;
            } else {
                $is_analis = false;
                $user_name = $request->input('nama_petugas');
                if (empty($user_name)) {
                    throw new \Exception('Nama petugas wajib dipilih.');
                }
            }
            
            $waktu = $request->input('waktu'); // Format: HH:mm

            // Tanggal registrasi + jam klik (bukan hari ini)
            $start_date = $this->clockOnRegisterDateForPermohonan($id, $waktu);
            $stop_date = $start_date->copy(); // Same time for klinik

            // Update or create verification activity
            $verification = VerificationActivitySample::where('is_klinik', $id)
                ->where('id_verification_activity', 3)
                ->where('resampling', 0)
                ->first();

            if ($verification) {
                $verification->start_date = $start_date->format('Y-m-d H:i:s');
                $verification->stop_date = $stop_date->format('Y-m-d H:i:s');
                $verification->nama_petugas = $user_name;
                $verification->is_done = 1;
                $verification->save();
            } else {
                $verification = new VerificationActivitySample();
                $verification->id = Uuid::uuid4()->toString();
                $verification->is_klinik = $id;
                $verification->id_verification_activity = 3;
                $verification->start_date = $start_date->format('Y-m-d H:i:s');
                $verification->stop_date = $stop_date->format('Y-m-d H:i:s');
                $verification->nama_petugas = $user_name;
                $verification->is_done = 1;
                $verification->resampling = 0;
                $verification->save();
            }

            // Update parameter results
            $parameters = $request->input('parameters', []);
            foreach ($parameters as $param) {
                $parameter = PermohonanUjiParameterKlinik::find($param['id']);
                if ($parameter) {
                    $parameter->hasil_permohonan_uji_parameter_klinik = $param['hasil'] ?? null;
                    $parameter->save();
                }
            }

            // Update permohonan
            $permohonan = PermohonanUjiKlinik2::find($id);
            $permohonan->tglpengujian_permohonan_uji_klinik = $start_date->format('Y-m-d H:i:s');
            $permohonan->name_analis_permohonan_uji_klinik = $user_name;
            $permohonan->save();

            // Send to Satu Sehat - Step 3 (Pemeriksa Sampel)
            try {
                if (config('services.satu_sehat.version') == 'prd') {
                    $pasien = Pasien::find($permohonan->pasien_permohonan_uji_klinik);
                    if ($pasien && !empty($pasien->id_pasien_satu_sehat) && !empty($permohonan->id_satu_sehat_encounter)) {
                        // Get practitioner - normalize dengan menghilangkan koma dari kedua sisi
                        $namaPetugas_normalized = str_replace(',', '', $user_name);
                        $practitioner = SatuSehatPractitioner::whereRaw("REPLACE(name_petugas, ',', '') LIKE ?", ['%' . $namaPetugas_normalized . '%'])->first();
                        
                        if (!$practitioner) {
                            $petugas = Petugas::whereRaw("REPLACE(REPLACE(nama, ',', ' '), '  ', ' ') LIKE ?", ['%' . $namaPetugas_normalized . '%'])->first();
                            if ($petugas && !empty($petugas->code_satu_sehat_practitioner)) {
                                $practitioner = SatuSehatPractitioner::where('code_satu_sehat_practitioner', $petugas->code_satu_sehat_practitioner)->first();
                            }
                        }

                        if ($practitioner && !empty($practitioner->code_satu_sehat_practitioner)) {
                            $encounterId = $permohonan->id_satu_sehat_encounter;
                            $encounter = json_decode($permohonan->encounter_json_satu_sehat, true);
                            $encounterDisplay = $encounter['class']['display'] ?? 'Pemeriksaan Sample';
                            
                            $observationResults = [];
                            $codingParameters = [];
                            
                            // Create Observation for each parameter with result
                            foreach ($parameters as $param) {
                                $parameter = PermohonanUjiParameterKlinik::find($param['id']);
                                if ($parameter && !empty($param['hasil'])) {
                                    $parameterSatuan = ParameterSatuanKlinik::find($parameter->parameter_satuan_klinik);
                                    if ($parameterSatuan) {
                                        $codeIonic = \Smt\Masterweb\Helpers\Smt::pickLoincForContext($parameterSatuan, $permohonan);
                                        $unit = Unit::find($parameter->satuan_permohonan_uji_parameter_klinik);
                                        
                                        // Extract numeric value from hasil (remove HTML tags if any)
                                        $hasil_clean = strip_tags($param['hasil']);
                                        $hasil_numeric = preg_replace('/[^0-9.,-]/', '', $hasil_clean);
                                        $hasil_numeric = str_replace(',', '.', $hasil_numeric);
                                        
                                        if (!empty($hasil_numeric) && is_numeric($hasil_numeric)) {
                                            try {
                                                $observationId = $this->createObservation(
                                                    $parameter,
                                                    $codeIonic,
                                                    $parameterSatuan->name_parameter_satuan_klinik ?? 'Parameter',
                                                    $pasien->id_pasien_satu_sehat,
                                                    $practitioner->code_satu_sehat_practitioner,
                                                    $encounterId,
                                                    $encounterDisplay,
                                                    $start_date->toIso8601String(),
                                                    (float) $hasil_numeric,
                                                    $unit->nama_unit ?? "microliter",
                                                    $unit->code_satu_sehat_unit ?? "uL"
                                                );
                                                
                                                if ($observationId) {
                                                    $observationResults[] = ["reference" => "Observation/" . $observationId];
                                                    $codingParameters[] = [
                                                        "system" => "http://loinc.org",
                                                        "code" => $codeIonic,
                                                        "display" => $parameterSatuan->name_parameter_satuan_klinik ?? 'Parameter'
                                                    ];
                                                }
                                            } catch (\Exception $e) {
                                                Log::warning('Error creating Observation for parameter: ' . $e->getMessage(), [
                                                    'parameter_id' => $parameter->id_permohonan_uji_parameter_klinik
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                            
                            // Update Encounter
                            $newStatusHistory = [
                                "status" => "in-progress",
                                "period" => [
                                    "start" => $start_date->toIso8601String(),
                                    "end" => $stop_date->toIso8601String()
                                ]
                            ];

                            $this->updateEncounter(
                                $id,
                                $user_name,
                                "in-progress",
                                "Ruang Lab Klinik",
                                "OBSENC",
                                "Pemeriksaan Sample",
                                "analyte",
                                $start_date->toIso8601String(),
                                $stop_date->toIso8601String(),
                                $newStatusHistory
                            );
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error sending to SatuSehat in storePemeriksa: ' . $e->getMessage(), [
                    'permohonan_id' => $id,
                    'exception' => $e->getTraceAsString()
                ]);
                // Continue transaction even if Satu Sehat fails
            }

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pemeriksa sampel berhasil disimpan.',
                    'redirect_url' => route('mobile.testing.klinik.verifikasi', ['id' => $id])
                ]);
            }

            return redirect()->route('mobile.testing.klinik.verifikasi', ['id' => $id])
                ->with('success', 'Data pemeriksa sampel berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Step 4: Verifikasi (Step 4) - Similar to verifikasi-hasil
     */
    public function verifikasi(Request $request, $id)
    {
        $permohonan = PermohonanUjiKlinik2::find($id);
        
        if (!$permohonan) {
            return redirect()->route('mobile.testing.klinik.home')
                ->with('error', 'ID Permohonan tidak ditemukan.');
        }

        // Check authentication
        if (!$request->session()->get('mobile_testing_klinik_auth', false)) {
            $request->session()->put('mobile_testing_klinik_temp_id', $id);
            return redirect()->route('mobile.testing.klinik.login', ['id' => $id]);
        }

        // Check if step 6 (Pengambilan Sample) is done
        $step6 = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 6)
            ->where('is_done', 1)
            ->where('resampling', 0)
            ->first();

        if (!$step6) {
            return redirect()->route('mobile.testing.klinik.home')
                ->with('error', 'Proses pengambilan sample belum selesai. Silakan selesaikan pengambilan sample terlebih dahulu sebelum melakukan testing-klinik.');
        }

        // Check if step 3 is done
        $step3 = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 3)
            ->where('resampling', 0)
            ->first();

        if (!$step3 || $step3->is_done != 1) {
            return redirect()->route('mobile.testing.klinik.pemeriksa', ['id' => $id])
                ->with('error', 'Silakan selesaikan pemeriksa sampel terlebih dahulu.');
        }

        $user_id = $request->session()->get('mobile_testing_klinik_user_id');
        $user_name = $request->session()->get('mobile_testing_klinik_user_name', 'Petugas');
        $user_level = $request->session()->get('mobile_testing_klinik_user_level', '');
        $is_analis = ($user_level === 'ANLS' || $user_level === 'ALAB' );
        
        $petugas = User::find($user_id)->petugas;
        $user_name = isset($petugas) ? $petugas->nama :  $user_name;
        if (isset($petugas)) {
            $is_analis = true;
        } else {
            $is_analis = false;
        }

        // Get existing verification activity
        $verification = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 4)
            ->where('resampling', 0)
            ->first();

        // Get parameters with results - only get parameters with jenis_pemeriksaan = 0
        $parameters = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id)
            ->whereHas('parametersatuanklinik', function ($query) {
                $query->where('jenis_pemeriksaan_parameter_satuan_klinik', '0')
                    ->whereNull('deleted_at');
            })
            ->whereNull('deleted_at')
            ->with(['parametersatuanklinik', 'unit'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Get patient data for baku mutu calculation
        $pasien_umur = $permohonan->umurtahun_pasien_permohonan_uji_klinik ?? 0;
        $pasien_gender = $permohonan->pasien->gender_pasien ?? null;

        // Enrich parameters with baku mutu data
        $parameters = $parameters->map(function($parameter) use ($pasien_umur, $pasien_gender) {
            $baku_mutu_result = $this->getBakuMutuForParameter($parameter, $pasien_umur, $pasien_gender);
            $parameter->baku_mutu_data = $baku_mutu_result['selected'];
            $parameter->baku_mutu_multiple = $baku_mutu_result['multiple'];
            $parameter->baku_mutu_all = $baku_mutu_result['all'];
            return $parameter;
        });

        // Get petugas list if not analis (Step 4 = Verifikasi)
        $petugas_list = [];
        $selected_petugas = null;
        if (!$is_analis) {
            $petugas_list = $this->getPetugasList(4);
            // Check if user's petugas is in the list
            $selected_petugas = $this->getUserPetugasInList($user_id, $petugas_list);
        }

        // Get pengambilan sample data
        $pengambilan_sample = PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->first();

        // Get pengambilan sample verification data (Step 6)
        $pengambilan_verification = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 6)
            ->where('is_done', 1)
            ->where('resampling', 0)
            ->first();

        // Get penerimaan sampel data (Step 7)
        $penerimaan_verification = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 7)
            ->where('resampling', 0)
            ->first();

        // Get pengolah sampel data (Step 2)
        $pengolah_verification = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 2)
            ->where('resampling', 0)
            ->first();

        return view('masterweb::module.mobile.testing.klinik.verifikasi', [
            'id' => $id,
            'permohonan' => $permohonan,
            'verification' => $verification,
            'user_name' => $user_name,
            'is_analis' => $is_analis,
            'pengambilan_sample' => $pengambilan_sample,
            'pengambilan_verification' => $pengambilan_verification,
            'penerimaan_verification' => $penerimaan_verification,
            'pengolah_verification' => $pengolah_verification,
            'petugas_list' => $petugas_list,
            'selected_petugas' => $selected_petugas,
            'parameters' => $parameters
        ]);
    }

    /**
     * Store Verifikasi
     */
    public function storeVerifikasi(Request $request, $id)
    {
        $user_level = $request->session()->get('mobile_testing_klinik_user_level', '');
        $is_analis = ($user_level === 'ANLS' || $user_level === 'ALAB' );

        $rules = [
            'waktu' => 'required|string',
            'parameters' => 'required|array',
            'parameters.*.id' => 'required|string',
            'parameters.*.verifikasi' => 'nullable|boolean'
        ];
        
        if (!$is_analis) {
            $rules['nama_petugas'] = 'required|string';
        }
        
        $request->validate($rules);

        try {
            DB::beginTransaction();

            $user_level = $request->session()->get('mobile_testing_klinik_user_level', '');
            $is_analis = ($user_level === 'ANLS' || $user_level === 'ALAB' );
            
            $user_id = $request->session()->get('mobile_testing_klinik_user_id');
            $user_name = $request->session()->get('mobile_testing_klinik_user_name', 'Petugas');
            $petugas = User::find($user_id)->petugas;
            $user_name = isset($petugas) ? $petugas->nama :  $user_name;
            if (isset($petugas)) {
                $is_analis = true;
            } else {
                $is_analis = false;
                $user_name = $request->input('nama_petugas');
                if (empty($user_name)) {
                    throw new \Exception('Nama petugas wajib dipilih.');
                }
            }
            
            $waktu = $request->input('waktu'); // Format: HH:mm

            // Tanggal registrasi + jam klik (bukan hari ini)
            $start_date = $this->clockOnRegisterDateForPermohonan($id, $waktu);
            $stop_date = $start_date->copy(); // Same time for klinik

            // Update or create verification activity
            $verification = VerificationActivitySample::where('is_klinik', $id)
                ->where('id_verification_activity', 4)
                ->where('resampling', 0)
                ->first();

            if ($verification) {
                $verification->start_date = $start_date->format('Y-m-d H:i:s');
                $verification->stop_date = $stop_date->format('Y-m-d H:i:s');
                $verification->nama_petugas = $user_name;
                $verification->is_done = 1;
                $verification->save();
            } else {
                $verification = new VerificationActivitySample();
                $verification->id = Uuid::uuid4()->toString();
                $verification->is_klinik = $id;
                $verification->id_verification_activity = 4;
                $verification->start_date = $start_date->format('Y-m-d H:i:s');
                $verification->stop_date = $stop_date->format('Y-m-d H:i:s');
                $verification->nama_petugas = $user_name;
                $verification->is_done = 1;
                $verification->resampling = 0;
                $verification->save();
            }

            // Update parameter verification status
            $parameters = $request->input('parameters', []);
            $hasNonApprovedStatus = false; // Flag untuk menandai ada status yang bukan approved
            
            foreach ($parameters as $param) {
                $parameter = PermohonanUjiParameterKlinik::find($param['id']);
                if ($parameter) {
                    // Update hasil jika ada koreksi (hanya jika diisi)
                    if (isset($param['hasil_koreksi'])) {
                        $hasil_koreksi = trim($param['hasil_koreksi']);
                        if (!empty($hasil_koreksi)) {
                            $parameter->hasil_permohonan_uji_parameter_klinik = $hasil_koreksi;
                        }
                    }
                    
                    // Update verification status from dropdown
                    if (isset($param['status_verifikasi'])) {
                        $status_verifikasi = $param['status_verifikasi'];
                        // Validate status value
                        $allowed_statuses = ['pending', 'approved', 'rejected', 'corrected'];
                        if (in_array($status_verifikasi, $allowed_statuses)) {
                            $parameter->status_verifikasi = $status_verifikasi;
                            
                            // Check if status is not approved (rejected, corrected, or pending)
                            // Mengikuti logika dari storeVerificationPermohonanUjiParamaterKlinik
                            if ($status_verifikasi != 'approved') {
                                $hasNonApprovedStatus = true;
                            }
                        } else {
                            // Default to approved if invalid value
                            $parameter->status_verifikasi = 'pending';
                        }
                    } elseif (isset($param['verifikasi'])) {
                        // Fallback: support old checkbox format for backward compatibility
                        $status_verifikasi = $param['verifikasi'] ? 'approved' : 'rejected';
                        $parameter->status_verifikasi = $status_verifikasi;
                        
                        if ($status_verifikasi != 'approved') {
                            $hasNonApprovedStatus = true;
                        }
                    } else {
                        // Default to approved if not specified
                        $parameter->status_verifikasi = 'pending';
                    }
                    
                    // Update komentar if provided
                    if (isset($param['komentar'])) {
                        $parameter->komentar_verifikasi = $param['komentar'];
                    }
                    
                    $parameter->save();
                }
            }
            
            // Jika ada status verifikasi yang bukan "approved", kembalikan ke step 3 (Pemeriksa) dan step 4 (Verifikasi)
            // Mengikuti logika dari storeVerificationPermohonanUjiParamaterKlinik
            if ($hasNonApprovedStatus) {
                // Set is_done = 0 untuk step 3 (Pemeriksa Sampel)
                $step3 = VerificationActivitySample::where('is_klinik', $id)
                    ->where('id_verification_activity', 3)
                    ->where('resampling', 0)
                    ->first();
                if ($step3) {
                    $step3->is_done = 0;
                    $step3->save();
                }
                
                // Set is_done = 0 untuk step 4 (Verifikasi) agar bisa dikoreksi kembali
                if ($verification) {
                    $verification->is_done = 0;
                    $verification->save();
                }
            } else {
                // Jika semua status approved, set is_done = 1 untuk step 4 (Verifikasi)
                if ($verification) {
                    $verification->is_done = 1;
                    $verification->save();
                }
                
                // Send to Satu Sehat - Step 4 (Verifikasi) - Create DiagnosticReport
                try {
                    if (config('services.satu_sehat.version') == 'prd') {
                        $permohonan = PermohonanUjiKlinik2::find($id);
                        $pasien = Pasien::find($permohonan->pasien_permohonan_uji_klinik);
                        
                        if ($pasien && !empty($pasien->id_pasien_satu_sehat) && !empty($permohonan->id_satu_sehat_encounter)) {
                            // Get practitioner - normalize dengan menghilangkan koma dari kedua sisi
                            $namaPetugas_normalized = str_replace(',', '', $user_name);
                            $practitioner = SatuSehatPractitioner::whereRaw("REPLACE(name_petugas, ',', '') LIKE ?", ['%' . $namaPetugas_normalized . '%'])->first();
                            
                            if (!$practitioner) {
                                $petugas = Petugas::whereRaw("REPLACE(REPLACE(nama, ',', ' '), '  ', ' ') LIKE ?", ['%' . $namaPetugas_normalized . '%'])->first();
                                if ($petugas && !empty($petugas->code_satu_sehat_practitioner)) {
                                    $practitioner = SatuSehatPractitioner::where('code_satu_sehat_practitioner', $petugas->code_satu_sehat_practitioner)->first();
                                }
                            }

                            if ($practitioner && !empty($practitioner->code_satu_sehat_practitioner)) {
                                $encounterId = $permohonan->id_satu_sehat_encounter;
                                
                                // Get all observations for approved parameters
                                $observationResults = [];
                                $codingParameters = [];
                                
                                $allParameters = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id)
                                    ->whereNull('deleted_at')
                                    ->get();
                                
                                foreach ($allParameters as $param) {
                                    if ($param->status_verifikasi == 'approved' && !empty($param->id_observation)) {
                                        $parameterSatuan = ParameterSatuanKlinik::find($param->parameter_satuan_klinik);
                                        if ($parameterSatuan) {
                                            $codeIonic = \Smt\Masterweb\Helpers\Smt::pickLoincForContext($parameterSatuan, $permohonan);
                                            $observationResults[] = ["reference" => "Observation/" . $param->id_observation];
                                            $codingParameters[] = [
                                                "system" => "http://loinc.org",
                                                "code" => $codeIonic,
                                                "display" => $parameterSatuan->name_parameter_satuan_klinik ?? 'Parameter'
                                            ];
                                        }
                                    }
                                }
                                
                                // Get ServiceRequests
                                $serviceRequests = [];
                                $pakets = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id)->get(['id_service_request']);
                                foreach ($pakets as $paket) {
                                    if (!empty($paket->id_service_request)) {
                                        $serviceRequests[] = ["reference" => "ServiceRequest/".$paket->id_service_request];
                                    }
                                }
                                
                                // Create DiagnosticReport if there are observations
                                if (count($observationResults) > 0 && count($serviceRequests) > 0) {
                                    $this->createDiagnosticReport(
                                        $permohonan,
                                        $codingParameters,
                                        $pasien->id_pasien_satu_sehat,
                                        $encounterId,
                                        $start_date->toIso8601String(),
                                        $practitioner->code_satu_sehat_practitioner,
                                        $observationResults,
                                        $permohonan->id_spesimen ?? '',
                                        $serviceRequests
                                    );
                                }
                                
                                // Update Encounter
                                $newStatusHistory = [
                                    "status" => "in-progress",
                                    "period" => [
                                        "start" => $start_date->toIso8601String(),
                                        "end" => $stop_date->toIso8601String()
                                    ]
                                ];

                                $this->updateEncounter(
                                    $id,
                                    $user_name,
                                    "in-progress",
                                    "Ruang Lab Klinik",
                                    "OBSENC",
                                    "Verifikasi",
                                    "analyte",
                                    $start_date->toIso8601String(),
                                    $stop_date->toIso8601String(),
                                    $newStatusHistory
                                );
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error sending to SatuSehat in storeVerifikasi: ' . $e->getMessage(), [
                        'permohonan_id' => $id,
                        'exception' => $e->getTraceAsString()
                    ]);
                    // Continue transaction even if Satu Sehat fails
                }
            }

            DB::commit();

            // Return response dengan informasi apakah ada yang perlu diperbaiki
            // Mengikuti logika dari storeVerificationPermohonanUjiParamaterKlinik
            if ($request->expectsJson() || $request->ajax()) {
                if ($hasNonApprovedStatus) {
                    return response()->json([
                        'status' => true,
                        'success' => true,
                        'pesan' => 'Verifikasi berhasil disimpan. Terdapat parameter yang perlu diperbaiki, permohonan dikembalikan ke step Pemeriksa dan Verifikasi untuk dikoreksi kembali.',
                        'message' => 'Verifikasi berhasil disimpan. Terdapat parameter yang perlu diperbaiki, permohonan dikembalikan ke step Pemeriksa dan Verifikasi untuk dikoreksi kembali.',
                        'kembali_ke_pemeriksaan' => true,
                        'redirect_url' => route('mobile.testing.klinik.pemeriksa', ['id' => $id])
                    ]);
                } else {
                    return response()->json([
                        'status' => true,
                        'success' => true,
                        'pesan' => 'Verifikasi berhasil disimpan.',
                        'message' => 'Verifikasi berhasil disimpan.',
                        'redirect_url' => route('mobile.testing.klinik.status', ['id' => $id])
                    ]);
                }
            }

            if ($hasNonApprovedStatus) {
                return redirect()->route('mobile.testing.klinik.pemeriksa', ['id' => $id])
                    ->with('warning', 'Verifikasi berhasil disimpan. Terdapat parameter yang perlu diperbaiki, permohonan dikembalikan ke step Pemeriksa dan Verifikasi untuk dikoreksi kembali.');
            }

            // Send WhatsApp to dokter for validasi after verification is done (step 4)
            if (!$hasNonApprovedStatus && $verification && $verification->is_done == 1) {
                try {
                    $permohonan = PermohonanUjiKlinik2::find($id);
                    if ($permohonan) {
                        $pasien = Pasien::find($permohonan->pasien_permohonan_uji_klinik);
                        $noRegister = $permohonan->noregister_permohonan_uji_klinik;
                        if ($pasien && $noRegister) {
                            $this->sendWhatsAppToDokterValidasi($id, $pasien, $noRegister);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error sending WhatsApp to dokter for validasi (MobileTestingKlinik): ' . $e->getMessage(), [
                        'permohonan_id' => $id,
                        'exception' => $e->getTraceAsString()
                    ]);
                    // Continue even if WhatsApp fails
                }
            }

            return redirect()->route('mobile.testing.klinik.status', ['id' => $id])
                ->with('success', 'Verifikasi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Status page - show all steps
     */
    public function status(Request $request, $id)
    {
        $permohonan = PermohonanUjiKlinik2::find($id);
        
        if (!$permohonan) {
            return redirect()->route('mobile.testing.klinik.home')
                ->with('error', 'ID Permohonan tidak ditemukan.');
        }

        // Get all verification activities
        $verification_activities = VerificationActivitySample::where('is_klinik', $id)
            ->where('resampling', 0)
            ->get()
            ->keyBy('id_verification_activity');

        return view('masterweb::module.mobile.testing.klinik.status', [
            'id' => $id,
            'permohonan' => $permohonan,
            'verification_activities' => $verification_activities
        ]);
    }

    /**
     * Get baku mutu for parameter based on patient age and gender
     * Returns all matching baku mutu (can be multiple) sorted from min to max
     * 
     * @param PermohonanUjiParameterKlinik $parameter
     * @param int $pasien_umur
     * @param string|null $pasien_gender
     * @return array ['selected' => array, 'multiple' => array, 'all' => collection]
     */
    private function getBakuMutuForParameter($parameter, $pasien_umur, $pasien_gender)
    {
        $id_parameter_jenis_klinik = $parameter->jenis_parameter_klinik_id;
        $id_parameter_satuan_klinik = $parameter->parameter_satuan_klinik;

        $data_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
            ->where('parameter_satuan_klinik_id', $id_parameter_satuan_klinik)
            ->get();

        if ($data_parameter_by_baku_mutu->count() == 0) {
            return [
                'selected' => [],
                'multiple' => [],
                'all' => collect([])
            ];
        }

        // Check for general baku mutu (is_khusus_baku_mutu = 0)
        $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
            ->where('parameter_satuan_klinik_id', $id_parameter_satuan_klinik)
            ->where('is_khusus_baku_mutu', '0')
            ->first();

        if ($check_parameter_by_baku_mutu) {
            return [
                'selected' => [$check_parameter_by_baku_mutu],
                'multiple' => [$check_parameter_by_baku_mutu->toArray()],
                'all' => collect([$check_parameter_by_baku_mutu])
            ];
        }

        // Get all specific baku mutu (is_khusus_baku_mutu = 1)
        $all_baku_mutu_khusus = BakuMutu::where('parameter_jenis_klinik_id', $id_parameter_jenis_klinik)
            ->where('parameter_satuan_klinik_id', $id_parameter_satuan_klinik)
            ->where('is_khusus_baku_mutu', '1')
            ->get();

        $selected_baku_mutu_list = [];
        $multiple_baku_mutu = [];

        // Helper function to extract numeric value for sorting
        $getNumericValue = function($value) {
            if ($value === null || $value === '') return 999999999;
            $cleaned = preg_replace('/[^0-9\,\.-]/', '', $value);
            $cleaned = str_replace(',', '.', $cleaned);
            return (float) $cleaned;
        };

        // Helper function to check if baku mutu matches patient
        $matchesPatient = function($item) use ($pasien_gender, $pasien_umur) {
            $gender_match = !isset($item->gender_baku_mutu) || $item->gender_baku_mutu === null || $item->gender_baku_mutu === $pasien_gender;
            $umur_match = (!isset($item->minimal_umur_baku_mutu) && !isset($item->maksimal_umur_baku_mutu))
                || (isset($item->minimal_umur_baku_mutu) && isset($item->maksimal_umur_baku_mutu)
                    && $item->minimal_umur_baku_mutu <= $pasien_umur && $item->maksimal_umur_baku_mutu >= $pasien_umur);
            return $gender_match && $umur_match;
        };

        if ($all_baku_mutu_khusus->count() > 1) {
            $normal_baku_mutu_collection = $all_baku_mutu_khusus->where('is_normal', 1);
            
            // Get all matching baku mutu (can be multiple)
            if ($normal_baku_mutu_collection->count() > 0) {
                // Filter all normal baku mutu that match patient
                $selected_baku_mutu_list = $normal_baku_mutu_collection->filter(function($item) use ($matchesPatient) {
                    return $matchesPatient($item);
                });

                // If no match found, try matching by gender only
                if ($selected_baku_mutu_list->count() == 0) {
                    $selected_baku_mutu_list = $normal_baku_mutu_collection->filter(function($item) use ($pasien_gender) {
                        return !isset($item->gender_baku_mutu) || $item->gender_baku_mutu === null || $item->gender_baku_mutu === $pasien_gender;
                    });
                }

                // If still no match, try matching by age only
                if ($selected_baku_mutu_list->count() == 0) {
                    $selected_baku_mutu_list = $normal_baku_mutu_collection->filter(function($item) use ($pasien_umur) {
                        return (!isset($item->minimal_umur_baku_mutu) && !isset($item->maksimal_umur_baku_mutu))
                            || (isset($item->minimal_umur_baku_mutu) && isset($item->maksimal_umur_baku_mutu)
                                && $item->minimal_umur_baku_mutu <= $pasien_umur && $item->maksimal_umur_baku_mutu >= $pasien_umur);
                    });
                }

                // If still no match, use all normal baku mutu
                if ($selected_baku_mutu_list->count() == 0) {
                    $selected_baku_mutu_list = $normal_baku_mutu_collection;
                }
            } else {
                // Fallback: match by gender and age
                $selected_baku_mutu_list = $all_baku_mutu_khusus->filter(function($item) use ($matchesPatient) {
                    return $matchesPatient($item);
                });

                if ($selected_baku_mutu_list->count() == 0) {
                    $selected_baku_mutu_list = $all_baku_mutu_khusus->filter(function($item) use ($pasien_gender) {
                        return !isset($item->gender_baku_mutu) || $item->gender_baku_mutu === null || $item->gender_baku_mutu === $pasien_gender;
                    });
                }
            }

            // Sort selected baku mutu from min to max
            $selected_baku_mutu_list = $selected_baku_mutu_list->sortBy(function($item) use ($getNumericValue) {
                $min = $getNumericValue($item->min ?? null);
                $max = $getNumericValue($item->max ?? null);
                return sprintf('%020.6f-%020.6f', $min, $max);
            })->values();

            // Convert to array and include all baku mutu for checking
            $multiple_baku_mutu = $all_baku_mutu_khusus->toArray();
        } else {
            // Single baku mutu
            $single_baku_mutu = $all_baku_mutu_khusus->first();
            if ($single_baku_mutu) {
                $selected_baku_mutu_list = collect([$single_baku_mutu]);
                $multiple_baku_mutu = [$single_baku_mutu->toArray()];
            }
        }

        return [
            'selected' => $selected_baku_mutu_list->toArray(),
            'multiple' => $multiple_baku_mutu,
            'all' => $all_baku_mutu_khusus
        ];
    }

    /**
     * Get list of petugas from VerificationActivity klinik field based on verification activity id
     * 
     * @param int $verification_activity_id ID dari VerificationActivity (7=Penerima Sampel, 2=Pemeriksaan/Analitik, 3=Input/Output Hasil, 4=Verifikasi)
     * @return array
     */
    private function getPetugasList($verification_activity_id)
    {
        $petugas_list = [];

        // Get from VerificationActivity klinik field berdasarkan id
        $verificationActivity = VerificationActivity::find($verification_activity_id);
        
        if ($verificationActivity && !empty($verificationActivity->klinik) && 
            $verificationActivity->klinik !== '-' && 
            $verificationActivity->klinik !== 'NULL') {
            
            // Parse names from klinik column (comma-separated)
            // Names are stored as "Name1, Name2, Name3" format
            $names = explode(', ', $verificationActivity->klinik);
            foreach ($names as $name) {
                $name = trim($name);
                if (!empty($name)) {
                    $petugas_list[] = [
                        'name' => $name,
                        'id' => null
                    ];
                }
            }
        }

        // Sort by name
        usort($petugas_list, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $petugas_list;
    }

    /**
     * Check if user's petugas is in the petugas list for a step
     * 
     * @param string $user_id User ID from session
     * @param array $petugas_list List of petugas from getPetugasList()
     * @return string|null Petugas name if found in list, null otherwise
     */
    private function getUserPetugasInList($user_id, $petugas_list)
    {
        if (empty($user_id) || empty($petugas_list)) {
            return null;
        }

        // Load user with petugas relationship
        $user = User::with('petugas')->find($user_id);
        
        if (!$user || !$user->petugas) {
            return null;
        }

        $petugas_nama = $user->petugas->nama;
        
        // Normalize petugas name (remove commas, trim spaces)
        $petugas_nama_normalized = trim(str_replace(',', '', $petugas_nama));
        
        // Check if petugas name exists in list (case-insensitive, partial match)
        foreach ($petugas_list as $petugas_item) {
            $list_name = trim($petugas_item['name']);
            $list_name_normalized = trim(str_replace(',', '', $list_name));
            
            // Exact match (case-insensitive)
            if (strtolower($petugas_nama_normalized) === strtolower($list_name_normalized)) {
                return $list_name; // Return original name from list
            }
            
            // Partial match (check if one contains the other)
            if (stripos($petugas_nama_normalized, $list_name_normalized) !== false ||
                stripos($list_name_normalized, $petugas_nama_normalized) !== false) {
                return $list_name; // Return original name from list
            }
        }

        return null;
    }

    /**
     * Update Encounter untuk Satu Sehat
     */
    private function updateEncounter($idPermohonanUjiKlinik, $namaPetugas, $status, $ruangan, $class_code, $class_display, $coding_code, $startDate, $endDate, $newStatusHistory)
    {
        $permohonanUjiKlinik = PermohonanUjiKlinik2::query()->where('id_permohonan_uji_klinik', '=', $idPermohonanUjiKlinik)->first();

        if (isset($permohonanUjiKlinik)){
            if (isset($permohonanUjiKlinik->pasien->id_pasien_satu_sehat) && $permohonanUjiKlinik->pasien->id_pasien_satu_sehat != "" && isset($permohonanUjiKlinik->id_satu_sehat_encounter) && $permohonanUjiKlinik->id_satu_sehat_encounter != "") {
                if (config('services.satu_sehat.version') == "prd") {
                    $location_satusehat = SatuSehatLocation::where('name_satusehat_location', "LIKE", '%'.$ruangan.'%')->where('version_satusehat_location','prd')->first();

                    // Normalize nama petugas untuk matching (hilangkan koma dari kedua sisi)
                    $namaPetugas_normalized = str_replace(',', '', $namaPetugas);
                    $practitioner_satusehat = SatuSehatPractitioner::whereRaw("REPLACE(name_petugas, ',', '') LIKE ?", ['%' . $namaPetugas_normalized . '%'])->first();

                    if (!$practitioner_satusehat) {
                        $petugas = Petugas::whereRaw("REPLACE(REPLACE(nama, ',', ' '), '  ', ' ') LIKE ?", ['%' . $namaPetugas_normalized . '%'])->first();
                        if ($petugas && !empty($petugas->code_satu_sehat_practitioner)) {
                            $practitioner_satusehat = SatuSehatPractitioner::where('code_satu_sehat_practitioner', $petugas->code_satu_sehat_practitioner)->first();
                        }
                    }

                    if (!$practitioner_satusehat) {
                        $petugas = Petugas::where('nama', 'like', '%' . $namaPetugas . '%')->first();
                        if ($petugas && !empty($petugas->code_satu_sehat_practitioner)) {
                            $practitioner_satusehat = SatuSehatPractitioner::where('code_satu_sehat_practitioner', $petugas->code_satu_sehat_practitioner)->first();
                        }
                    }

                    $arrayStatusHistory = json_decode($permohonanUjiKlinik->encounter_json_satu_sehat, true);
                    $statusHistory = $arrayStatusHistory['statusHistory'];

                    $statusHistory[] = $newStatusHistory;

                    $participants = $arrayStatusHistory["participant"];
                    $display = $arrayStatusHistory["class"]["display"];

                    $practitionerReference = '';
                    $practitionerDisplay = $namaPetugas;

                    if ($practitioner_satusehat && !empty($practitioner_satusehat->code_satu_sehat_practitioner)) {
                        $practitionerReference = "Practitioner/" . $practitioner_satusehat->code_satu_sehat_practitioner;
                        $practitionerDisplay = !empty($practitioner_satusehat->name_satu_sehat_practitioner)
                            ? $practitioner_satusehat->name_satu_sehat_practitioner
                            : $namaPetugas;
                    } else {
                        Log::warning('Practitioner SatuSehat not found', [
                            'permohonan_id' => $idPermohonanUjiKlinik,
                            'nama_petugas' => $namaPetugas,
                            'coding_code' => $coding_code
                        ]);
                    }

                    $participants[] = [
                        "type" => [
                            [
                                "coding" => [
                                    [
                                        "system" => "http://terminology.hl7.org/CodeSystem/v3-ParticipationType",
                                        "code" => $coding_code,
                                        "display" => $class_display
                                    ]
                                ]
                            ]
                        ],
                        "individual" => [
                            "reference" => $practitionerReference,
                            "display" => $practitionerDisplay
                        ]
                    ];

                    $data = [
                        "resourceType" => "Encounter",
                        "id" => $permohonanUjiKlinik->id_satu_sehat_encounter,
                        "identifier" => [
                            [
                                "system" => "http://sys-ids.kemkes.go.id/encounter/".config('services.satu_sehat.org_id'),
                                "value" => $permohonanUjiKlinik->noregister_permohonan_uji_klinik
                            ]
                        ],
                        "status" => $status,
                        "class" => [
                            "system" => "http://terminology.hl7.org/CodeSystem/v3-ActCode",
                            "code" => $class_code,
                            "display" => $display
                        ],
                        "subject" => [
                            "reference" => "Patient/".$permohonanUjiKlinik->pasien->id_pasien_satu_sehat,
                            "display" => $permohonanUjiKlinik->pasien->nama_pasien
                        ],
                        "participant" => $participants,
                        "period" => [
                            "start" => $startDate,
                            "end" => $endDate
                        ],
                        "location" => [
                            [
                                "location" => [
                                    "reference" => "Location/".$location_satusehat->kode_satusehat_location,
                                    "display" => $location_satusehat->name_satusehat_location
                                ]
                            ]
                        ],
                        "statusHistory" => $statusHistory,
                        "serviceProvider" => [
                            "reference" => "Organization/".config('services.satu_sehat.org_id')
                        ]
                    ];

                    $response = $this->satuSehatHelper->put('Encounter', $permohonanUjiKlinik->id_satu_sehat_encounter, $data);

                    if ($response['status_code'] == '200') {
                        $permohonanUjiKlinik->encounter_json_satu_sehat = json_encode($response['body']);
                        $permohonanUjiKlinik->save();
                    } else {
                        throw new \Exception("gagal update encounter, ". $response['body']['issue'][0]['details']['text']);
                    }
                }
            }
        }
    }

    /**
     * Create Observation untuk Satu Sehat
     */
    private function createObservation($parameter, $ionicCode, $parameterName, $patientId, $practitionerId, $encounterId, $encounterDisplay, $datetime, $result, $resultUnit, $resultCode)
    {
        $data = [
            "resourceType" => "Observation",
            "status" => "final",
            "category" => [
                [
                    "coding" => [
                        [
                            "system" => "http://terminology.hl7.org/CodeSystem/observation-category",
                            "code" => "laboratory",
                            "display" => "Laboratory"
                        ]
                    ]
                ]
            ],
            "code" => [
                "coding" => [
                    [
                        "system" => "http://loinc.org",
                        "code" => $ionicCode,
                        "display" => $parameterName
                    ]
                ]
            ],
            "subject" => [
                "reference" => "Patient/".$patientId
            ],
            "performer" => [
                [
                    "reference" => "Practitioner/".$practitionerId
                ]
            ],
            "encounter" => [
                "reference" => "Encounter/".$encounterId,
                "display" => $encounterDisplay
            ],
            "effectiveDateTime" => $datetime,
            "issued" => $datetime,
            "valueQuantity" => [
                "value" => $result,
                "unit" => $resultUnit,
                "system" => "http://unitsofmeasure.org",
                "code" => $resultCode
            ]
        ];

        $idObservation = $parameter->id_observation;
        if ($idObservation != null && $idObservation != ""){
            $data['id'] = $idObservation;
            $response = $this->satuSehatHelper->put('Observation', $idObservation, $data);

            if ($response['status_code'] == '200'){
                return $idObservation;
            } else {
                throw new \Exception("Gagal update observasi!, ". $response['body']['issue'][0]['details']['text']);
            }
        }

        $response = $this->satuSehatHelper->post('Observation', $data);

        if ($response['status_code'] == '201'){
            $parameter->id_observation = $response['body']['id'];
            $parameter->save();

            return $response['body']['id'];
        } else {
            throw new \Exception("Gagal membuat observasi!, ". $response['body']['issue'][0]['details']['text']);
        }
    }

    /**
     * Create DiagnosticReport untuk Satu Sehat
     */
    private function createDiagnosticReport($permohonanUji, $codingParameters, $patientId, $encounterId, $datePengujian, $practitionerId, $observations, $specimenId, $serviceRequests)
    {
        $diagnosticReport = [
            "resourceType" => "DiagnosticReport",
            "identifier" => [
                [
                    "system" => "http://sys-ids.kemkes.go.id/diagnostic/".config("services.satu_sehat.org_id")."/lab",
                    "use" => "official",
                    "value" => $permohonanUji->noregister_permohonan_uji_klinik
                ]
            ],
            "status" => "final",
            "category" => [
                [
                    "coding" => [
                        [
                            "system" => "http://terminology.hl7.org/CodeSystem/v2-0074",
                            "code" => "LAB",
                            "display" => "Laboratory"
                        ]
                    ]
                ]
            ],
            "code" => [
                "coding" => $codingParameters
            ],
            "subject" => [
                "reference" => "Patient/".$patientId
            ],
            "encounter" => [
                "reference" => "Encounter/".$encounterId
            ],
            "effectiveDateTime" => $datePengujian,
            "issued" => $datePengujian,
            "performer" => [
                [
                    "reference" => "Practitioner/".$practitionerId
                ],
                [
                    "reference" => "Organization/".config("services.satu_sehat.org_id")
                ]
            ],
            "basedOn" => $serviceRequests,
            "result" => $observations,
            "specimen" => "",
            "conclusionCode" => [
                [
                    "coding" => [
                        [
                            "system" => "http://snomed.info/sct",
                            "code" => "260347006",
                            "display" => "+"
                        ]
                    ]
                ]
            ]
        ];

        if ($specimenId == ""){
            unset($diagnosticReport['specimen']);
        } else {
            $idSpecimenArray = explode(',', $specimenId);
            $speciments = [];
            foreach ($idSpecimenArray as $idSpecimen){
                $speciments[] = ["reference" => "Specimen/".$idSpecimen];
            }
            $diagnosticReport["specimen"] = $speciments;
        }

        $idDiagnosticReport = $permohonanUji->id_diagnostic_report;
        if ($idDiagnosticReport != null && $idDiagnosticReport != ""){
            $diagnosticReport['id'] = $idDiagnosticReport;
            $response = $this->satuSehatHelper->put('DiagnosticReport', $idDiagnosticReport, $diagnosticReport);

            if ($response['status_code'] != '200'){
                throw new \Exception("Gagal update diagnostic report!, ". $response['body']['issue'][0]['details']['text']);
            }
        }

        $response = $this->satuSehatHelper->post('DiagnosticReport', $diagnosticReport);
        if ($response['status_code'] == '201'){
            $permohonanUji->id_diagnostic_report = $response['body']["id"];
            $permohonanUji->save();
        } else {
            throw new \Exception("Gagal membuat diagnostic report! " . $response['body']['issue'][0]['details']['text']);
        }
    }

    /**
     * Send WhatsApp notification to all doctors with DKTR level for validation
     * 
     * @param string $permohonanId
     * @param Pasien $pasien
     * @param string $noRegister
     * @return void
     */
    private function sendWhatsAppToDokterValidasi($permohonanId, $pasien, $noRegister)
    {
        try {
            Log::info('sendWhatsAppToDokterValidasi called (MobileTestingKlinik)', ['permohonan_id' => $permohonanId]);
            
            // Get Wablas configuration
            $wablasHost = config('services.wablas.host');
            $wablasToken = config('services.wablas.token');
            $wablasSecret = config('services.wablas.secret');

            // Check if Wablas is configured
            if (empty($wablasHost) || empty($wablasToken) || empty($wablasSecret)) {
                Log::warning('Wablas configuration is missing. WhatsApp notification skipped.');
                return;
            }

            // Get all users with DKTR level
            $dokters = User::whereHas('getlevel', function ($query) {
                $query->where('level', 'DKTR');
            })
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get();

            if ($dokters->isEmpty()) {
                Log::info('No doctors with DKTR level and phone number found.');
                return;
            }

            // Prepare message content untuk template "send-link"
            $pasienNama = $pasien->nama_pasien ?? 'Pasien';
            $loginUrl = url('/mobile/dokter/login/' . $permohonanId);
            $previewText  = "Permohonan uji klinik baru.\n";
            $previewText .= "No. Reg: {$noRegister}\n";
            $previewText .= "Pasien : {$pasienNama}\n\n";
            $previewText .= "Harus dilakukan validasi untuk pasien tersebut.";

            // Prepare data untuk endpoint Wablas send-link
            $data = [];
            foreach ($dokters as $dokter) {
                $phone = $dokter->phone;
                if (!empty($phone)) {
                    // Bersihkan karakter non-digit
                    $phone = preg_replace('/[^0-9]/', '', $phone);
                    // Pastikan diawali 62
                    if (substr($phone, 0, 2) !== '62') {
                        if (substr($phone, 0, 1) === '0') {
                            $phone = '62' . substr($phone, 1);
                        } else {
                            $phone = '62' . $phone;
                        }
                    }

                    $data[] = [
                        'phone' => $phone,
                        'message' => [
                            'text' => $previewText,
                            'link' => $loginUrl,
                        ],
                    ];
                }
            }

            if (empty($data)) {
                Log::info('No valid phone numbers found for doctors.');
                return;
            }

            // Kirim WhatsApp menggunakan Wablas endpoint send-link dengan cURL native
            $url = rtrim($wablasHost, '/') . '/api/v2/send-link';
            $authHeader = $wablasToken . '.' . $wablasSecret;
            $payload = [
                'data' => $data,
            ];

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                "Authorization: {$authHeader}",
                "Content-Type: application/json",
            ]);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            $result = curl_exec($curl);
            $curlError = curl_error($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($result === false) {
                Log::error('Error sending WhatsApp send-link via cURL', [
                    'error' => $curlError,
                    'permohonan_id' => $permohonanId,
                ]);
                return;
            }

            $responseBody = json_decode($result, true);

            if ($httpCode === 200 && isset($responseBody['status']) && $responseBody['status'] === true) {
                Log::info('WhatsApp send-link notification sent successfully to doctors for validation', [
                    'permohonan_id' => $permohonanId,
                    'no_register' => $noRegister,
                    'dokters_count' => count($data),
                ]);
                return;
            }

            Log::warning('Wablas send-link API did not return success', [
                'http_code' => $httpCode,
                'response' => $responseBody,
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending WhatsApp notification to doctors for validation: ' . $e->getMessage(), [
                'permohonan_id' => $permohonanId,
                'no_register' => $noRegister,
                'error' => $e->getTraceAsString()
            ]);
            // Don't throw exception, just log the error
        }
    }

    /**
     * Tanggal tahap mobile: tanggal registrasi permohonan + jam yang diklik.
     */
    private function clockOnRegisterDateForPermohonan($id, $waktu): Carbon
    {
        $permohonan = PermohonanUjiKlinik2::find($id);

        return DateHelper::clockOnRegisterDate(
            $permohonan ? DateHelper::permohonanAnchorAt($permohonan) : null,
            $waktu
        );
    }
}

