<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Smt\Masterweb\Models\User;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\VerificationActivity;
use Smt\Masterweb\Helpers\SatuSehatHelper;
use Smt\Masterweb\Helpers\DateHelper;
use Smt\Masterweb\Models\Pasien;
use Smt\Masterweb\Models\Petugas;
use Smt\Masterweb\Models\PengambilanSampleKlinik;
use Smt\Masterweb\Models\PermohonanUjiPaketKlinik;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\SatuSehatPractitioner;
use Smt\Masterweb\Models\SatuSehatLocation;
use Smt\Masterweb\Models\ParameterPaketKlinik;
use Smt\Masterweb\Models\VerificationActivitySample;
use Carbon\Carbon;
use Exception;

class MobileSamplingKlinikController extends Controller
{
    protected $satuSehatHelper;

    public function __construct(SatuSehatHelper $satuSehatHelper)
    {
        $this->satuSehatHelper = $satuSehatHelper;
    }
    /**
     * Mobile sampling klinik home: scan or input ID
     */
    public function home(Request $request)
    {
        $isAuthenticated = $request->session()->get('mobile_sampling_klinik_auth', false);
        return view('masterweb::module.mobile.sampling-klinik.index', [
            'is_authenticated' => $isAuthenticated
        ]);
    }

    /**
     * Show mobile sampling login page (scan QR code first)
     */
    public function index(Request $request)
    {
        // If already authenticated, show index page (can scan/input directly)
        // Don't redirect - let user scan/input new permohonan
        $is_authenticated = $request->session()->get('mobile_sampling_klinik_auth', false);
        
        return view('masterweb::module.mobile.sampling-klinik.index', [
            'is_authenticated' => $is_authenticated
        ]);
    }

    /**
     * Process manual ID input
     */
    public function inputId(Request $request)
    {
        $request->validate([
            'id_permohonan' => 'required|string',
        ]);

        $id_permohonan_uji_klinik = trim($request->id_permohonan);

        // Validate permohonan uji klinik exists
        $permohonan_uji_klinik = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $id_permohonan_uji_klinik)
            ->with('pasien')
            ->first();

        if (!$permohonan_uji_klinik) {
            return redirect()->route('mobile.sampling.klinik.home')
                ->with('error', 'Data permohonan uji klinik tidak ditemukan. Pastikan ID yang dimasukkan benar.')
                ->withInput();
        }

        // Store permohonan ID in session for later use
        session(['mobile_sampling_klinik_id' => $id_permohonan_uji_klinik]);

        // Check if already authenticated, redirect to form
        if ($request->session()->get('mobile_sampling_klinik_auth', false)) {
            return redirect()->route('mobile.sampling.klinik.form', ['id' => $id_permohonan_uji_klinik]);
        }

        // Redirect to login
        return redirect()->route('mobile.sampling.klinik.login')
            ->with('permohonan_uji_klinik', $permohonan_uji_klinik);
    }

    /**
     * Process QR code scan and redirect to login or form
     */
    public function scan(Request $request, $id_permohonan_uji_klinik)
    {
        // Validate permohonan uji klinik exists
        $permohonan_uji_klinik = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $id_permohonan_uji_klinik)
            ->with('pasien')
            ->first();

        if (!$permohonan_uji_klinik) {
            return view('masterweb::module.mobile.sampling-klinik.error', [
                'message' => 'Data permohonan uji klinik tidak ditemukan'
            ]);
        }

        // Store permohonan ID in session for later use
        session(['mobile_sampling_klinik_id' => $id_permohonan_uji_klinik]);

        // Check if already authenticated, redirect to form
        if ($request->session()->get('mobile_sampling_klinik_auth', false)) {
            return redirect()->route('mobile.sampling.klinik.form', ['id' => $id_permohonan_uji_klinik]);
        }

        // Redirect to login
        return redirect()->route('mobile.sampling.klinik.login')
            ->with('permohonan_uji_klinik', $permohonan_uji_klinik);
    }

    /**
     * Show mobile sampling login page
     */
    public function login(Request $request)
    {
        // Check if already authenticated
        if ($request->session()->get('mobile_sampling_klinik_auth', false)) {
            $id = session('mobile_sampling_klinik_id');
            if ($id) {
                return redirect()->route('mobile.sampling.klinik.form', ['id' => $id]);
            }
            return redirect()->route('mobile.sampling.klinik.scan');
        }

        // Get permohonan from session if available
        $permohonan_uji_klinik = session('permohonan_uji_klinik') ?? 
            (session('mobile_sampling_klinik_id') ? 
                PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', session('mobile_sampling_klinik_id'))
                    ->with('pasien')
                    ->first() : 
                null);

        if (!$permohonan_uji_klinik && !session('mobile_sampling_klinik_id')) {
            return redirect()->route('mobile.sampling.klinik.home')
                ->with('error', 'Silakan scan QR code terlebih dahulu');
        }

        return view('masterweb::module.mobile.sampling-klinik.login', compact('permohonan_uji_klinik'));
    }

    /**
     * Process mobile sampling login
     */
    public function doLogin(Request $request)
    {
        // Honeypot simple bot check
        if ($request->filled('hp_field')) {
            return redirect()->back()->with('error', 'Permintaan tidak valid.')->withInput($request->only('username'));
        }
        // CAPTCHA validation (2 minutes TTL)
        $sessionCode = $request->session()->get('captcha_code');
        $sessionTime = (int) $request->session()->get('captcha_time', 0);
        $userInput = trim((string)$request->input('captcha', ''));
        $ttl = 120;
        
        // Clear expired captcha
        if ($sessionTime > 0 && (time() - $sessionTime) > $ttl) {
            $request->session()->forget(['captcha_code', 'captcha_time']);
            $sessionCode = null;
        }
        
        if (!$sessionCode || empty($userInput) || strcasecmp(trim($sessionCode), $userInput) !== 0) {
            // Clear captcha on failure
            $request->session()->forget(['captcha_code', 'captcha_time']);
            return redirect()->back()
                ->with('error', 'Kode keamanan (CAPTCHA) tidak valid atau kedaluwarsa.')
                ->withInput($request->only('username'));
        }
        
        // Clear captcha on success
        $request->session()->forget(['captcha_code', 'captcha_time']);
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Try to find user (without role filter since role column doesn't exist)
        $user = User::where('username', $request->username)
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            try {
                SatuSehatHelper::ensureAccessToken();
            } catch (\Throwable $e) {
                Log::warning('Satu Sehat token refresh skipped for mobile sampling klinik: ' . $e->getMessage());
            }

            // Set mobile sampling klinik session
            $request->session()->put([
                'mobile_sampling_klinik_auth' => true,
                'mobile_sampling_klinik_user_id' => $user->id,
                'mobile_sampling_klinik_user_name' => $user->name,
                'mobile_sampling_klinik_user_username' => $user->username,
            ]);
            
            // Force save session
            $request->session()->save();

            // Get permohonan ID from session
            $id = session('mobile_sampling_klinik_id');
            if ($id) {
                return redirect()->route('mobile.sampling.klinik.form', ['id' => $id])
                    ->with('success', 'Login berhasil! Selamat datang, ' . $user->name);
            }

            return redirect()->route('mobile.sampling.klinik.scan')
                ->with('success', 'Login berhasil! Silakan scan QR code');
        }

        return redirect()->back()
            ->withInput($request->only('username'))
            ->with('error', 'Username atau password salah!');
    }

    /**
     * Show mobile sampling form
     */
    public function form(Request $request, $id_permohonan_uji_klinik, $count = null)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_klinik_auth', false)) {
            return redirect()->route('mobile.sampling.klinik.login')
                ->with('error', 'Silakan login terlebih dahulu');
        }

        // Get permohonan uji klinik data
        $permohonan_uji_klinik = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $id_permohonan_uji_klinik)
            ->with('pasien')
            ->first();

        if (!$permohonan_uji_klinik) {
            return view('masterweb::module.mobile.sampling-klinik.error', [
                'message' => 'Data permohonan uji klinik tidak ditemukan'
            ]);
        }

        // Get existing sampling data
        $existing_count = \Smt\Masterweb\Models\PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
            ->whereNull('deleted_at')
            ->count();

        // Check if latest sampling is already completed and successful
        // If so, prevent new sampling (resampling)
        if ($existing_count > 0) {
            // Get latest sampling
            $latest_sampling = \Smt\Masterweb\Models\PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->first();

            // Check if latest sampling is successful and verification is done
            if ($latest_sampling && $latest_sampling->status_sampling === 'Berhasil') {
             
                // Get resampling value for latest sampling
                $latest_resampling = $latest_sampling->resampling ?? 0;
                
                // Check verification for this sampling
                $latest_verification = \Smt\Masterweb\Models\VerificationActivitySample::where('is_klinik', $id_permohonan_uji_klinik)
                    ->where('id_verification_activity', 6)
                    ->where('resampling', $latest_resampling)
                    ->first();

                // If verification is done (is_done = 1), prevent new sampling
                if ($latest_verification && $latest_verification->is_done == 1) {
                    // Determine requested count

                    $requested_count = $count !== null ? (int)$count : ($existing_count + 1);
                    
                    // dd($existing_count);
                    // If requested count is for new sampling (count > existing_count), reject
                    // if ($requested_count >= $existing_count) {
                        return view('masterweb::module.mobile.sampling-klinik.error', [
                            'message' => 'Sampling terakhir sudah berhasil dan selesai. Tidak dapat melakukan sampling ulang. Silakan edit data sampling melalui website.'
                        ]);
                    // }
                }
            }
        }

        // Determine count: if provided in route, use it; otherwise check if we should edit existing or create new
        if ($count !== null) {
            $count = (int)$count;
            
            // If count > 1, this is a resampling - form should be empty
            if ($count > 1) {
                // For resampling, don't load previous data - form should be empty
                $pengambilan_sample = null;
            } else {
                // Count = 1, first sampling - load existing data if exists (for editing)
                $pengambilan_sample = \Smt\Masterweb\Models\PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
                    ->where('resampling', 0)
                    ->whereNull('deleted_at')
                    ->first();
            }
        } else {
            // No count provided - check if we should edit existing or create new
            if ($existing_count > 0) {
                // There's existing data - edit the latest one
                $latest_sampling = \Smt\Masterweb\Models\PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
                    ->whereNull('deleted_at')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($latest_sampling) {
                    // Load the latest sampling for editing
                    $pengambilan_sample = $latest_sampling;
                    // Use total count so form knows which record to edit
                    // For status "Berhasil": uses limit($count) and takes first, so count = total_count
                    // For status "Gagal": uses where('resampling', $count-1), so count = resampling + 1
                    // But to be safe, we use total_count which works for both cases
                    $count = $existing_count;
                } else {
                    // Fallback: create new
                    $pengambilan_sample = null;
                    $count = $existing_count + 1;
                }
            } else {
                // No existing data (existing_count = 0) - this is for NEW sampling (not editing)
                // Form should be empty, count will be 1
                $pengambilan_sample = null;
                $count = 1;
            }
        }

        // Prepare data for form (handle null pengambilan_sample)
        $tgl_register = Carbon::parse($permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->format('d/m/Y');
        
        if ($pengambilan_sample) {
            $tgl_sampling = $pengambilan_sample->created_at ? 
                Carbon::parse($pengambilan_sample->created_at)->format('Y-m-d') : 
                Carbon::now()->format('Y-m-d');
            
            $jam_sampling = $pengambilan_sample->created_at ? 
                Carbon::parse($pengambilan_sample->created_at)->format('H:i') : 
                Carbon::now()->format('H:i');

            $tindakan_medis_khusus = $pengambilan_sample->tindakan_medis_khusus ?? '';
            $kondisi_pasien = $pengambilan_sample->kondisi_pasien ?? '';
            
            // Handle jenis_sample - could be JSON string or array
            if (!empty($pengambilan_sample->jenis_sample)) {
                if (is_string($pengambilan_sample->jenis_sample)) {
                    $decoded = json_decode($pengambilan_sample->jenis_sample, true);
                    $jenis_sampel = $decoded ?: [];
                } else {
                    $jenis_sampel = is_array($pengambilan_sample->jenis_sample) ? $pengambilan_sample->jenis_sample : [];
                }
            } else {
                $jenis_sampel = [];
            }
            
            // If tindakan medis khusus or jenis sampel is empty, get from parameter using helper
            if (empty($jenis_sampel)) {
                $jenis_sampel = \Smt\Masterweb\Helpers\Smt::getJenisSampelFromParameter(
                    $id_permohonan_uji_klinik,
                    null
                );
            }

            if (empty($tindakan_medis_khusus)) {
                $tindakan_medis_khusus = \Smt\Masterweb\Helpers\Smt::getTindakanMedisKhususFromParameter(
                    $id_permohonan_uji_klinik,
                    null
                );
            } elseif (!empty($jenis_sampel)) {
                // Selaraskan tindakan tersimpan dengan jenis sampel (hindari Darah tanpa Pengambilan Darah Vena)
                $savedList = is_array($tindakan_medis_khusus)
                    ? $tindakan_medis_khusus
                    : array_values(array_filter(array_map('trim', explode(',', (string) $tindakan_medis_khusus))));
                if (is_string($tindakan_medis_khusus)) {
                    $decodedT = json_decode($tindakan_medis_khusus, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedT)) {
                        $savedList = array_values(array_filter(array_map('trim', $decodedT)));
                    }
                }
                $tindakan_medis_khusus = \Smt\Masterweb\Helpers\Smt::reconcileTindakanMedisWithJenisSampel(
                    $savedList,
                    is_array($jenis_sampel) ? $jenis_sampel : []
                );
            }
            
            $status_sampling = $pengambilan_sample->status_sampling ?? 'Berhasil';
            $resample_reason = $pengambilan_sample->resample_reason ?? '';
        } else {
            // If pengambilan_sample is null, set default values from parameter
            $tgl_sampling = Carbon::now()->format('Y-m-d');
            $jam_sampling = Carbon::now()->format('H:i');
            
            // Get default tindakan medis khusus dan jenis sampel from parameter using helper
            $tindakan_medis_khusus = \Smt\Masterweb\Helpers\Smt::getTindakanMedisKhususFromParameter(
                $id_permohonan_uji_klinik,
                null
            );
            $jenis_sampel = \Smt\Masterweb\Helpers\Smt::getJenisSampelFromParameter(
                $id_permohonan_uji_klinik,
                null
            );
            
            $kondisi_pasien = '';
            $status_sampling = 'Berhasil';
            $resample_reason = '';
        }

        // Get user info from session
        $petugas_name = $request->session()->get('mobile_sampling_klinik_user_name', 'Petugas');

        // Get list petugas from VerificationActivity (id 6 = Pengambil Sample)
        $verificationActivity = VerificationActivity::where('id', 6)->first();
        $list_petugas = [];
        if ($verificationActivity && !empty($verificationActivity->klinik) && $verificationActivity->klinik != '-') {
            $list_petugas = array_map('trim', explode(',', $verificationActivity->klinik));
        }

        // Get existing verification activity sample for pengambil sample (step 6)
        // Only load verification_sample if we're editing existing data (not new resampling)
        $verification_sample = null;
        if ($pengambilan_sample) {
            // Only load verification_sample if we have existing pengambilan_sample (editing mode)
            $resampling_value = $pengambilan_sample->resampling ?? 0;
            $verification_sample = \Smt\Masterweb\Models\VerificationActivitySample::where('is_klinik', $id_permohonan_uji_klinik)
                ->where('id_verification_activity', 6)
                ->where('resampling', $resampling_value)
                ->first();
        }
        // For new resampling (count > 1 and no pengambilan_sample), verification_sample remains null

        // Determine if this is resampling
        $is_resampling = $count > 1;

        // Get parameters list grouped by paket
        $data_permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
            ->with(['parameterjenisklinik', 'parameterpaketklinik'])
            ->whereNull('deleted_at')
            ->get();

        $parameters_list = [];
        foreach ($data_permohonan_uji_paket_klinik as $paket) {
            $data_parameters = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
                ->where('permohonan_uji_paket_klinik', $paket->id_permohonan_uji_paket_klinik)
                ->with(['parametersatuanklinik'])
                ->whereNull('deleted_at')
                ->get();

            foreach ($data_parameters as $param) {
                $param->nama_jenis = $paket->parameterjenisklinik->name_parameter_jenis_klinik ?? '-';
                $param->nama_paket = $paket->parameterpaketklinik->name_parameter_paket_klinik ?? '-';
                $parameters_list[] = $param;
            }
        }

        return view('masterweb::module.mobile.sampling-klinik.form', compact(
            'permohonan_uji_klinik',
            'tgl_register',
            'tgl_sampling',
            'jam_sampling',
            'tindakan_medis_khusus',
            'kondisi_pasien',
            'jenis_sampel',
            'status_sampling',
            'resample_reason',
            'count',
            'is_resampling',
            'petugas_name',
            'list_petugas',
            'verification_sample',
            'pengambilan_sample',
            'parameters_list'
        ));
    }

    /**
     * Store sampling data - Copy from storePermohonanUjiSample with mobile adjustments
     */
    public function store(Request $request, $id_permohonan_uji_klinik)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_klinik_auth', false)) {
            return response()->json([
                'status' => false,
                'pesan' => 'Silakan login terlebih dahulu'
            ], 401);
        }

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            $item_permohonan_uji_klinik = PermohonanUjiKlinik2::find($id_permohonan_uji_klinik);

            // Temukan data permohonan uji klinik berdasarkan ID
            $post = $request->all();

            // Get count - if null or 0, check if there's existing data to edit
            $requested_count = $request->count ?? 1;
            
            // If count is null, 0, or not provided, check for existing data
            if ($requested_count === null || $requested_count === '' || (int)$requested_count === 0) {
                // Check if there's existing sampling data
                $existing_sampling = PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
                    ->whereNull('deleted_at')
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($existing_sampling) {
                    // Edit existing data - use existing resampling value + 1
                    $total_count = PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
                        ->whereNull('deleted_at')
                        ->count();
                    $count = $total_count;
                } else {
                    // No existing data, create new (first sampling)
                    $count = 1;
                }
            } else {
                $count = (int)$requested_count;
            }

            if (strtolower($request->status_sampling) === 'gagal') {
                $resampleTime = $request->jam_sampling ?? $request->jam_sampling_display ?? null;
                $baseDate = $request->tgl_sampling ?: Carbon::createFromFormat('Y-m-d H:i', $post['tgl_sampling'].' '.($post['jam_sampling'] ?? $post['jam_sampling_display'] ?? Carbon::now()->format('H:i')))->format('Y-m-d');
                $resamplePetugas = $request->nama_petugas_pengambil ?? $request->session()->get('mobile_sampling_klinik_user_name', null);

                $pengambilan_sample_klinik = PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
                    ->orderBy('created_at', 'desc')
                    ->where('resampling', $count-1)
                    ->first();

                if (!isset($pengambilan_sample_klinik)) {
                    $pengambilan_sample_klinik = new PengambilanSampleKlinik();
                    $pengambilan_sample_klinik->id_pengambilan_sample_klinik = Uuid::uuid4()->toString();
                    $pengambilan_sample_klinik->permohonan_uji_klinik_id = $id_permohonan_uji_klinik;
                    $pengambilan_sample_klinik->jenis_sample = is_array($request['jenis_sampel']) ? json_encode($request['jenis_sampel']) : ($request->jenis_sampel ?? null);
                    $pengambilan_sample_klinik->signature_pengambil_sample_petugas = null;
                    $pengambilan_sample_klinik->signature_pengambil_sample_pasien = null;
                    $pengambilan_sample_klinik->tindakan_medis_khusus = $request->tindakan_medis_khusus ?? null;
                    $pengambilan_sample_klinik->id_spesimen_satu_sehat = null;
                    $pengambilan_sample_klinik->pasien_permohonan_uji_klinik = $item_permohonan_uji_klinik->pasien_permohonan_uji_klinik ?? null;
                    $pengambilan_sample_klinik->kondisi_pasien = $request->kondisi_pasien ?? null;
                    $pengambilan_sample_klinik->status_sampling = $request->status_sampling ?? null;
                    $pengambilan_sample_klinik->resampling = $count-1;
                    $pengambilan_sample_klinik->petugas_name = $resamplePetugas ?: ($item_permohonan_uji_klinik->plebotomist_permohonan_uji_klinik ?? null);
                    $pengambilan_sample_klinik->resample_reason = $request->resample_reason ?? null;
                    $pengambilan_sample_klinik->petugas_id = null;
                    $pengambilan_sample_klinik->number_sampling_success = PengambilanSampleKlinik::calculateNumberSamplingSuccess($id_permohonan_uji_klinik);
                    $pengambilan_sample_klinik->save();
                } else {
                    $pengambilan_sample_klinik = PengambilanSampleKlinik::find($pengambilan_sample_klinik->id_pengambilan_sample_klinik);
                    $pengambilan_sample_klinik->jenis_sample = is_array($request->jenis_sampel) ? json_encode($request['jenis_sampel']) : ($request->jenis_sampel ?? null);
                    $pengambilan_sample_klinik->kondisi_pasien = $request->kondisi_pasien ?? null;
                    $old_status = $pengambilan_sample_klinik->status_sampling;
                    $pengambilan_sample_klinik->status_sampling = $request->status_sampling ?? null;
                    $pengambilan_sample_klinik->tindakan_medis_khusus = $request->tindakan_medis_khusus ?? null;
                    $pengambilan_sample_klinik->resample_reason = $request->resample_reason ?? null;
                    if (isset($request->nama_petugas_pengambil)) {
                        $pengambilan_sample_klinik->petugas_name = $request->nama_petugas_pengambil;
                    }
                    if ($old_status !== $pengambilan_sample_klinik->status_sampling) {
                        $pengambilan_sample_klinik->number_sampling_success = PengambilanSampleKlinik::calculateNumberSamplingSuccess($id_permohonan_uji_klinik);
                    }
                    $pengambilan_sample_klinik->save();

                    $new_count = PengambilanSampleKlinik::calculateNumberSamplingSuccess($id_permohonan_uji_klinik);
                    PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
                        ->whereNull('deleted_at')
                        ->update(['number_sampling_success' => $new_count]);

                    $item_permohonan_uji_klinik->refresh();
                    $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik = $item_permohonan_uji_klinik->generateNoregister();
                    $item_permohonan_uji_klinik->save();
                }
            } else {
                $baseDate = $request->tgl_sampling ?: Carbon::createFromFormat('Y-m-d H:i', $post['tgl_sampling'].' '.($post['jam_sampling'] ?? $post['jam_sampling_display'] ?? Carbon::now()->format('H:i')))->format('Y-m-d');
                $resamplePetugas = $request->nama_petugas_pengambil ?? $request->session()->get('mobile_sampling_klinik_user_name', null);
                
                $pengambilan_sample_klinik = PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
                    ->orderBy('created_at', 'desc')
                    ->limit($count)
                    ->get();

                if (count($pengambilan_sample_klinik) == $count) {
                    $pengambilan_sample_klinik = $pengambilan_sample_klinik[0];
                } else {
                    $pengambilan_sample_klinik = null;
                }

                if (!isset($pengambilan_sample_klinik)) {
                    $pengambilan_sample_klinik = new PengambilanSampleKlinik();
                    $pengambilan_sample_klinik->id_pengambilan_sample_klinik = Uuid::uuid4()->toString();
                    $pengambilan_sample_klinik->permohonan_uji_klinik_id = $id_permohonan_uji_klinik;
                    $pengambilan_sample_klinik->jenis_sample = is_array($post['jenis_sampel']) ? json_encode($request['jenis_sampel']) : ($request->jenis_sampel ?? null);
                    $pengambilan_sample_klinik->signature_pengambil_sample_petugas = null;
                    $pengambilan_sample_klinik->signature_pengambil_sample_pasien = null;
                    $pengambilan_sample_klinik->tindakan_medis_khusus = $request->tindakan_medis_khusus ?? null;
                    $pengambilan_sample_klinik->id_spesimen_satu_sehat = null;
                    $pengambilan_sample_klinik->pasien_permohonan_uji_klinik = $item_permohonan_uji_klinik->pasien_permohonan_uji_klinik ?? null;
                    $pengambilan_sample_klinik->kondisi_pasien = $request->kondisi_pasien ?? null;
                    $pengambilan_sample_klinik->status_sampling = $request->status_sampling ?? null;
                    $pengambilan_sample_klinik->resampling = $count-1;
                    $pengambilan_sample_klinik->petugas_name = $resamplePetugas ?: ($item_permohonan_uji_klinik->plebotomist_permohonan_uji_klinik ?? null);
                    $pengambilan_sample_klinik->resample_reason = $request->resample_reason ?? null;
                    $pengambilan_sample_klinik->petugas_id = null;
                    $pengambilan_sample_klinik->number_sampling_success = PengambilanSampleKlinik::calculateNumberSamplingSuccess($id_permohonan_uji_klinik);
                    $pengambilan_sample_klinik->save();

                    $new_count = PengambilanSampleKlinik::calculateNumberSamplingSuccess($id_permohonan_uji_klinik);
                    PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
                        ->where('id_pengambilan_sample_klinik', '!=', $pengambilan_sample_klinik->id_pengambilan_sample_klinik)
                        ->whereNull('deleted_at')
                        ->update(['number_sampling_success' => $new_count]);

                    $item_permohonan_uji_klinik->refresh();
                    $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik = $item_permohonan_uji_klinik->generateNoregister();
                    $item_permohonan_uji_klinik->save();
                } else {
                    $old_status = $pengambilan_sample_klinik->status_sampling;
                    $pengambilan_sample_klinik->jenis_sample = is_array($request->jenis_sampel) ? json_encode($request['jenis_sampel']) : ($request->jenis_sampel ?? null);
                    $pengambilan_sample_klinik->kondisi_pasien = $request->kondisi_pasien ?? null;
                    $pengambilan_sample_klinik->status_sampling = $request->status_sampling ?? null;
                    $pengambilan_sample_klinik->tindakan_medis_khusus = $request->tindakan_medis_khusus ?? null;
                    $pengambilan_sample_klinik->resample_reason = $request->resample_reason ?? null;

                    if (isset($request->nama_petugas_pengambil)) {
                        $pengambilan_sample_klinik->petugas_name = $request->nama_petugas_pengambil ?? null;
                    }
                    
                    if ($old_status !== $pengambilan_sample_klinik->status_sampling) {
                        $pengambilan_sample_klinik->number_sampling_success = PengambilanSampleKlinik::calculateNumberSamplingSuccess($id_permohonan_uji_klinik);
                    }

                    $pengambilan_sample_klinik->save();

                    $new_count = PengambilanSampleKlinik::calculateNumberSamplingSuccess($id_permohonan_uji_klinik);
                    PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
                        ->whereNull('deleted_at')
                        ->update(['number_sampling_success' => $new_count]);

                    $item_permohonan_uji_klinik->refresh();
                    $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik = $item_permohonan_uji_klinik->generateNoregister();
                    $item_permohonan_uji_klinik->save();
                }

                // Jika resampling (status_sampling = 'Berhasil' atau 'Gagal' dan resampling > 0), buat Specimen dan ServiceRequest baru
                if ($pengambilan_sample_klinik->resampling > 0 &&
                    (strtolower($pengambilan_sample_klinik->status_sampling) === 'berhasil' ||
                     strtolower($pengambilan_sample_klinik->status_sampling) === 'gagal')) {
                    try {
                        $this->createResamplingSpecimenAndServiceRequest($id_permohonan_uji_klinik, $pengambilan_sample_klinik, $request);
                    } catch (\Exception $e) {
                        Log::error('Error creating resampling Specimen and ServiceRequest: ' . $e->getMessage(), [
                            'permohonan_id' => $id_permohonan_uji_klinik,
                            'resampling' => $pengambilan_sample_klinik->resampling,
                            'status_sampling' => $pengambilan_sample_klinik->status_sampling,
                            'exception' => $e->getTraceAsString()
                        ]);
                    }
                }
            }

            // Validasi required fields
            if (empty($request->kondisi_pasien)) {
                DB::rollBack();
                return response()->json(['status' => false, 'pesan' => "Kondisi pasien wajib diisi!"], 200);
            }

            if (empty($request->jenis_sampel)) {
                DB::rollBack();
                return response()->json(['status' => false, 'pesan' => "Jenis sampel wajib diisi!"], 200);
            }

            if (empty($request->status_sampling)) {
                DB::rollBack();
                return response()->json(['status' => false, 'pesan' => "Status sampling wajib diisi!"], 200);
            }

            // Kirim ke SatuSehat (baik berhasil maupun gagal)
            try {
                // Refresh pengambilan_sample_klinik untuk mendapatkan data terbaru
                if (isset($pengambilan_sample_klinik) && is_object($pengambilan_sample_klinik)) {
                    $pengambilan_sample_klinik->refresh();
                }
                $this->sendSpecimenToSatuSehat($item_permohonan_uji_klinik, $request, $pengambilan_sample_klinik ?? null);
            } catch (\Exception $e) {
                // Log error tapi tidak gagalkan transaksi
                Log::error('Error sending specimen to SatuSehat: ' . $e->getMessage(), [
                    'permohonan_id' => $id_permohonan_uji_klinik,
                    'exception' => $e->getTraceAsString()
                ]);
            }

            // Commit transaksi jika semua berhasil
            DB::commit();

                // If status sampling is "Berhasil", automatically mark as done (verification-analytic)
            if ($request->status_sampling === 'Berhasil') {
                    try {
                        $currentDate = Carbon::now()->format('d/m/Y');
                        $currentTime = $request->jam_sampling_display ?? Carbon::now()->format('H:i');
                        $startDateTime = $currentDate . ' ' . $currentTime;
                        $resamplingValue = ($count > 1) ? ($count - 1) : 0;
                    $namaPetugas = $request->nama_petugas_pengambil ?? $request->session()->get('mobile_sampling_klinik_user_name', 'Petugas');
                        
                        // Call local method instead of external controller (includes updateEncounter)
                        $this->verificationAnalyticStep6($id_permohonan_uji_klinik, $startDateTime, $namaPetugas, $resamplingValue);
                    
                    // Send to Satu Sehat - Step 6 (Pengambilan Sample)
                    try {
                        if (config('services.satu_sehat.version') == 'prd') {
                            $tgl_sampling = $request->tgl_sampling ?? Carbon::now()->format('Y-m-d');
                            $jam_sampling = $request->jam_sampling_display ?? Carbon::now()->format('H:i');
                            $collectedDateTime = Carbon::createFromFormat('Y-m-d H:i', $tgl_sampling . ' ' . $jam_sampling)->format('Y-m-d H:i:s');
                            
                            // Kirim spesimen ke SatuSehat dan update ServiceRequest ketika sampling selesai (is_done = 1)
                            $specimenRequest = new Request([
                                'nama_petugas' => $namaPetugas,
                            ]);
                            $this->sendSpecimenAndUpdateServiceRequest($id_permohonan_uji_klinik, $specimenRequest, $collectedDateTime);
                        }
                    } catch (\Exception $e) {
                        // Log error tapi tidak gagalkan transaksi
                        Log::error('Error sending to SatuSehat in store: ' . $e->getMessage(), [
                            'permohonan_id' => $id_permohonan_uji_klinik,
                            'exception' => $e->getTraceAsString()
                        ]);
                    }
                } catch (\Exception $e) {
                        Log::warning('Failed to auto-complete verification: ' . $e->getMessage());
                    }
            }
                
                return response()->json([
                    'status' => true,
                'pesan' => "Data permohonan uji klinik untuk sample berhasil diubah!",
                    'status_sampling' => $request->status_sampling,
                    'auto_completed' => $request->status_sampling === 'Berhasil'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saat menyimpan permohonan uji sample: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'pesan' => "Data permohonan uji klinik untuk sample tidak berhasil diubah! Error: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create resampling Specimen and ServiceRequest for Satu Sehat
     */
    private function createResamplingSpecimenAndServiceRequest($id_permohonan_uji_klinik, $pengambilan_sample_klinik, $request)
    {
        // Only send if VERSION_SATUSEHAT = prd
        if (config('services.satu_sehat.version') !== 'prd') {
            Log::info('SatuSehat resampling integration skipped: VERSION_SATUSEHAT is not prd');
            return;
        }

        // Get permohonan uji klinik data
        $item_permohonan_uji_klinik = PermohonanUjiKlinik2::find($id_permohonan_uji_klinik);
        if (!$item_permohonan_uji_klinik) {
            Log::warning('Permohonan uji klinik not found for resampling: ' . $id_permohonan_uji_klinik);
            return;
        }

        // Get patient data
        $pasien = Pasien::find($item_permohonan_uji_klinik->pasien_permohonan_uji_klinik);
        if (!$pasien || empty($pasien->id_pasien_satu_sehat)) {
            Log::warning('Patient SatuSehat ID not found for resampling permohonan: ' . $id_permohonan_uji_klinik);
            return;
        }

        $patientId = $pasien->id_pasien_satu_sehat;
        $patientName = $pasien->nama_pasien ?? 'Unknown';

        // Parse jenis_sample (bisa JSON string atau array)
        $jenis_sampel = $pengambilan_sample_klinik->jenis_sample ?? null;
        if (is_string($jenis_sampel)) {
            $decoded = json_decode($jenis_sampel, true);
            $jenis_sampel = $decoded !== null ? $decoded : [$jenis_sampel];
        }
        if (!is_array($jenis_sampel)) {
            $jenis_sampel = $jenis_sampel ? [$jenis_sampel] : [];
        }

        if (empty($jenis_sampel)) {
            Log::warning('No jenis sample found in PengambilanSampleKlinik for resampling permohonan: ' . $id_permohonan_uji_klinik);
            return;
        }

        // Format collectedDateTime from time_sampling or created_at
        $collectedDateTime = null;
        if (!empty($pengambilan_sample_klinik->time_sampling)) {
            try {
                $collectedDateTime = Carbon::parse($pengambilan_sample_klinik->time_sampling)->toIso8601String();
            } catch (\Exception $e) {
                $collectedDateTime = Carbon::parse($pengambilan_sample_klinik->created_at)->toIso8601String();
            }
        } else {
            $collectedDateTime = Carbon::parse($pengambilan_sample_klinik->created_at)->toIso8601String();
        }
        $receivedTime = $collectedDateTime;

        // Get encounter data - use existing Encounter from permohonan awal
        if (empty($item_permohonan_uji_klinik->id_satu_sehat_encounter)) {
            Log::warning('Encounter SatuSehat ID not found for resampling permohonan: ' . $id_permohonan_uji_klinik . ' - skipping resampling Specimen and ServiceRequest creation');
            return;
        }

        $encounterId = $item_permohonan_uji_klinik->id_satu_sehat_encounter;
        $encounterDisplay = "Permohonan Pengujian Resampling";

        // Get practitioner data (from petugas_name or pendaftaran)
        $practitionerId = null;
        $practitionerName = null;

        if (!empty($pengambilan_sample_klinik->petugas_name)) {
            // Cari Petugas berdasarkan nama dengan metode pencarian yang sama
            $namaPetugas_normalized = str_replace(',', '', $pengambilan_sample_klinik->petugas_name);
            $petugas = Petugas::whereRaw("REPLACE(REPLACE(nama, ',', ' '), '  ', ' ') LIKE ?", ['%' . $namaPetugas_normalized . '%'])->first();
            
            // Gunakan relasi untuk mendapatkan practitioner
            if ($petugas) {
                $practitioner = $petugas->getSatuSehatPractitioner();
            } else {
                // Fallback: cari langsung di SatuSehatPractitioner jika Petugas tidak ditemukan
                $practitioner = SatuSehatPractitioner::whereRaw("REPLACE(name_petugas, ',', '') LIKE ?", ['%' . $namaPetugas_normalized . '%'])
                    ->orWhereRaw("REPLACE(name_satu_sehat_practitioner, ',', '') LIKE ?", ['%' . $namaPetugas_normalized . '%'])
                    ->first();
            }

            if ($practitioner && isset($practitioner->code_satu_sehat_practitioner) && !empty($practitioner->code_satu_sehat_practitioner)) {
                $practitionerId = $practitioner->code_satu_sehat_practitioner;
                $practitionerName = $practitioner->name_satu_sehat_practitioner ?? $pengambilan_sample_klinik->name_satu_sehat_practitioner;
            }
        }

        // Fallback: get practitioner from pendaftaran if not found
        if (!$practitionerId) {
            $pendaftaran = VerificationActivitySample::where('is_klinik', $id_permohonan_uji_klinik)
                ->where('id_verification_activity', 1)
                ->first();

            if ($pendaftaran && !empty($pendaftaran->nama_petugas)) {
                // Cari Petugas berdasarkan nama dengan metode pencarian yang sama
                $namaPetugas_normalized = str_replace(',', '', $pendaftaran->nama_petugas);
                $petugas = Petugas::whereRaw("REPLACE(REPLACE(nama, ',', ' '), '  ', ' ') LIKE ?", ['%' . $namaPetugas_normalized . '%'])->first();
                
                // Gunakan relasi untuk mendapatkan practitioner
                if ($petugas) {
                    $practitioner = $petugas->getSatuSehatPractitioner();
                } else {
                    // Fallback: cari langsung di SatuSehatPractitioner jika Petugas tidak ditemukan
                    $practitioner = SatuSehatPractitioner::whereRaw("REPLACE(name_petugas, ',', '') LIKE ?", ['%' . $namaPetugas_normalized . '%'])
                        ->orWhereRaw("REPLACE(name_satu_sehat_practitioner, ',', '') LIKE ?", ['%' . $namaPetugas_normalized . '%'])
                        ->first();
                }

                if ($practitioner && isset($practitioner->code_satu_sehat_practitioner) && !empty($practitioner->code_satu_sehat_practitioner)) {
                    $practitionerId = $practitioner->code_satu_sehat_practitioner;
                    $practitionerName = $practitioner->name_satu_sehat_practitioner ?? $pendaftaran->name_satu_sehat_practitioner;
                }
            }
        }

        if (!$practitionerId) {
            Log::warning('Practitioner SatuSehat not found for resampling permohonan: ' . $id_permohonan_uji_klinik);
            return;
        }

        // Get all paket klinik for this permohonan to create new ServiceRequest for each
        $permohonanUjiPaketKlinikList = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
            ->whereNull('deleted_at')
            ->get();

        if ($permohonanUjiPaketKlinikList->isEmpty()) {
            Log::warning('No paket klinik found for resampling permohonan: ' . $id_permohonan_uji_klinik);
            return;
        }

        // Create unique UUID for this resampling
        $uniqueId = Uuid::uuid4()->toString();
        $newServiceRequestIds = [];

        foreach ($permohonanUjiPaketKlinikList as $permohonanUjiPaketKlinik) {
            $parameterpaketklinik = ParameterPaketKlinik::find($permohonanUjiPaketKlinik->parameter_paket_klinik);
            if (!$parameterpaketklinik) {
                continue;
            }

            $packetName = $parameterpaketklinik->name_parameter_paket_klinik ?? 'Unknown';
            $detailPacket = [];
            $detailPacket[] = [
                "system" => "http://snomed.info/sct",
                "code" => "108252007",
                "display" => "Laboratory procedure"
            ];

            $resamplingIdentifier = $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik . '-RESAMPLE-' . $pengambilan_sample_klinik->resampling . '-' . $uniqueId;

            try {
                $data = [
                    "resourceType" => "ServiceRequest",
                    "identifier" => [
                        [
                            "system" => "http://sys-ids.kemkes.go.id/servicerequest/".config('services.satu_sehat.org_id'),
                            "value" => $resamplingIdentifier,
                        ],
                    ],
                    "status" => "active",
                    "intent" => "original-order",
                    "priority" => "routine",
                    "category" => [
                        [
                            "coding" => [
                                [
                                    "system" => "http://snomed.info/sct",
                                    "code" => "108252007",
                                    "display" => "Laboratory procedure",
                                ],
                            ],
                        ],
                    ],
                    "code" => [
                        "coding" => $detailPacket,
                        "text" => $packetName
                    ],
                    "subject" => ["reference" => "Patient/".$patientId],
                    "encounter" => [
                        "reference" => "Encounter/".$encounterId,
                        "display" => $encounterDisplay,
                    ],
                    "occurrenceDateTime" => $collectedDateTime,
                    "authoredOn" => $collectedDateTime,
                    "requester" => [
                        "reference" => "Practitioner/".$practitionerId,
                        "display" => $practitionerName,
                    ],
                    "performer" => [
                        ["reference" => "Practitioner/".$practitionerId, "display" => $practitionerName],
                    ]
                ];

                $response = $this->satuSehatHelper->post('ServiceRequest', $data);

                if ($response['status_code'] == '201'){
                    $serviceRequestId = $response['body']["id"];
                    $newServiceRequestIds[] = ["reference" => "ServiceRequest/" . $serviceRequestId];

                    Log::info('Resampling ServiceRequest created successfully', [
                        'permohonan_id' => $id_permohonan_uji_klinik,
                        'paket_id' => $permohonanUjiPaketKlinik->parameter_paket_klinik,
                        'resampling' => $pengambilan_sample_klinik->resampling,
                        'service_request_id' => $serviceRequestId,
                        'identifier' => $resamplingIdentifier,
                        'collected_datetime' => $collectedDateTime,
                        'unique_id' => $uniqueId
                    ]);
                } else {
                    throw new \Exception("Gagal membuat service request untuk resampling! Response: " . json_encode($response));
                }
            } catch (\Exception $e) {
                Log::error('Error creating ServiceRequest for resampling: ' . $e->getMessage(), [
                    'permohonan_id' => $id_permohonan_uji_klinik,
                    'paket_id' => $permohonanUjiPaketKlinik->parameter_paket_klinik,
                    'resampling' => $pengambilan_sample_klinik->resampling,
                    'exception' => $e->getTraceAsString()
                ]);
            }
        }

        // Check if we have valid ServiceRequest IDs before creating Specimen
        if (empty($newServiceRequestIds)) {
            Log::warning('No ServiceRequest IDs created for resampling - cannot create Specimen (request field is mandatory)', [
                'permohonan_id' => $id_permohonan_uji_klinik,
                'resampling' => $pengambilan_sample_klinik->resampling
            ]);
            return;
        }

        // Create Specimen for resampling
        $identifier = $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik . '-RESAMPLE-' . $pengambilan_sample_klinik->resampling . '-' . $uniqueId;
        $initialStatus = (strtolower($pengambilan_sample_klinik->status_sampling ?? '') === 'gagal') ? 'unavailable' : 'available';

        try {
            $result = $this->createSpecimen(
                $identifier,
                $jenis_sampel,
                $collectedDateTime,
                $patientId,
                $patientName,
                $newServiceRequestIds,
                $receivedTime,
                $initialStatus
            );

            if ($result) {
                if (!empty($result['specimen_ids'])) {
                    $pengambilan_sample_klinik->id_spesimen_satu_sehat = implode(',', $result['specimen_ids']);
                }

                if (!empty($result['service_request_ids'])) {
                    $pengambilan_sample_klinik->id_service_request_satu_sehat = implode(',', $result['service_request_ids']);
                }

                if (empty($result['service_request_ids']) && !empty($newServiceRequestIds)) {
                    $serviceRequestIdsArray = array_map(function($ref) {
                        return str_replace('ServiceRequest/', '', $ref['reference']);
                    }, $newServiceRequestIds);
                    $pengambilan_sample_klinik->id_service_request_satu_sehat = implode(',', array_filter($serviceRequestIdsArray));
                }

                $pengambilan_sample_klinik->save();

                Log::info('Resampling Specimen and ServiceRequest created successfully with 1:1 mapping', [
                    'permohonan_id' => $id_permohonan_uji_klinik,
                    'resampling' => $pengambilan_sample_klinik->resampling,
                    'specimen_ids' => $result['specimen_ids'] ?? [],
                    'service_request_ids' => $result['service_request_ids'] ?? [],
                    'id_spesimen_satu_sehat' => $pengambilan_sample_klinik->id_spesimen_satu_sehat,
                    'id_service_request_satu_sehat' => $pengambilan_sample_klinik->id_service_request_satu_sehat,
                    'identifier' => $identifier,
                    'collected_datetime' => $collectedDateTime,
                    'unique_id' => $uniqueId,
                    'initial_status' => $initialStatus,
                    'status_sampling' => $pengambilan_sample_klinik->status_sampling
                ]);
            } else {
                if (!empty($newServiceRequestIds)) {
                    $serviceRequestIdsArray = array_map(function($ref) {
                        return str_replace('ServiceRequest/', '', $ref['reference']);
                    }, $newServiceRequestIds);
                    $pengambilan_sample_klinik->id_service_request_satu_sehat = implode(',', array_filter($serviceRequestIdsArray));
                    $pengambilan_sample_klinik->save();
                }

                Log::warning('createSpecimen returned empty result for resampling, but ServiceRequest IDs saved', [
                    'permohonan_id' => $id_permohonan_uji_klinik,
                    'resampling' => $pengambilan_sample_klinik->resampling,
                    'service_request_ids' => $newServiceRequestIds
                ]);
            }
        } catch (\Exception $e) {
            if (!empty($newServiceRequestIds)) {
                $serviceRequestIdsArray = array_map(function($ref) {
                    return str_replace('ServiceRequest/', '', $ref['reference']);
                }, $newServiceRequestIds);
                $pengambilan_sample_klinik->id_service_request_satu_sehat = implode(',', array_filter($serviceRequestIdsArray));
                $pengambilan_sample_klinik->save();
            }

            Log::error('Error creating Specimen for resampling: ' . $e->getMessage(), [
                'permohonan_id' => $id_permohonan_uji_klinik,
                'resampling' => $pengambilan_sample_klinik->resampling,
                'exception' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Update Encounter di Satu Sehat
     */
    private function updateEncounter($idPermohonanUjiKlinik, $namaPetugas, $status, $ruangan, $class_code, $class_display, $coding_code, $startDate, $endDate, $newStatusHistory)
    {
        $permohonanUjiKlinik = PermohonanUjiKlinik2::query()->where('id_permohonan_uji_klinik', '=', $idPermohonanUjiKlinik)->first();

        if (isset($permohonanUjiKlinik)){
            if (isset($permohonanUjiKlinik->pasien->id_pasien_satu_sehat) and $permohonanUjiKlinik->pasien->id_pasien_satu_sehat != "" and isset($permohonanUjiKlinik->id_satu_sehat_encounter) and $permohonanUjiKlinik->id_satu_sehat_encounter != "") {
                if ( config('services.satu_sehat.version')=="prd") {
                    $location_satusehat = SatuSehatLocation::where('name_satusehat_location',"LIKE",'%'.$ruangan.'%')->where('version_satusehat_location','prd')->first();

                    // Normalize nama petugas untuk matching (hilangkan koma dari kedua sisi)
                    $namaPetugas_normalized = str_replace(',', '', $namaPetugas);
                    $practitioner_satusehat = SatuSehatPractitioner::whereRaw("REPLACE(name_petugas, ',', '') LIKE ?", ['%' . $namaPetugas_normalized . '%'])->first();

                    // Jika tidak ditemukan dengan like, coba cari dari ms_petugas
                    if (!$practitioner_satusehat) {
                        $petugas = Petugas::whereRaw("REPLACE(REPLACE(nama, ',', ' '), '  ', ' ') LIKE ?", ['%' . $namaPetugas_normalized . '%'])->first();
                        if ($petugas && !empty($petugas->code_satu_sehat_practitioner)) {
                            $practitioner_satusehat = SatuSehatPractitioner::where('code_satu_sehat_practitioner', $petugas->code_satu_sehat_practitioner)->first();
                        }
                    }

                    // Jika masih tidak ditemukan, coba cari dengan nama asli (dengan koma) dari ms_petugas
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

                    // Tentukan practitioner reference dan display
                    $practitionerReference = '';
                    $practitionerDisplay = $namaPetugas; // Default: gunakan nama asli dari request (dengan koma jika ada)

                    if ($practitioner_satusehat && !empty($practitioner_satusehat->code_satu_sehat_practitioner)) {
                        $practitionerReference = "Practitioner/" . $practitioner_satusehat->code_satu_sehat_practitioner;
                        // Gunakan name_petugas jika ada, jika tidak gunakan nama asli dari request
                        $practitionerDisplay = !empty($practitioner_satusehat->name_satu_sehat_practitioner)
                            ? $practitioner_satusehat->name_satu_sehat_practitioner
                            : $namaPetugas;
                    } else {
                        // Jika practitioner tidak ditemukan, log warning tapi tetap tambahkan participant dengan nama asli
                        Log::warning('Practitioner SatuSehat not found for PRCP participant', [
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

                    Log::info('Adding participant to Encounter', [
                        'permohonan_id' => $idPermohonanUjiKlinik,
                        'coding_code' => $coding_code,
                        'class_display' => $class_display,
                        'practitioner_reference' => $practitionerReference,
                        'practitioner_display' => $practitionerDisplay,
                        'total_participants' => count($participants)
                    ]);

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
                        throw new Exception("gagal update encounter, ". $response['body']['issue'][0]['details']['text']);
                    }
                }
            }
        }
    }

    /**
     * Send specimen data to SatuSehat from storePermohonanUjiSample
     */
    private function sendSpecimenToSatuSehat($item_permohonan_uji_klinik, $request, $pengambilan_sample_klinik)
    {
        // Only send if VERSION_SATUSEHAT = prd
        if (config('services.satu_sehat.version') !== 'prd') {
            Log::info('SatuSehat integration skipped: VERSION_SATUSEHAT is not prd');
            return;
        }

        // Get patient data
        $pasien = Pasien::find($item_permohonan_uji_klinik->pasien_permohonan_uji_klinik);
        if (!$pasien || empty($pasien->id_pasien_satu_sehat)) {
            Log::warning('Patient SatuSehat ID not found for permohonan: ' . $item_permohonan_uji_klinik->id_permohonan_uji_klinik);
            return;
        }

        $patientId = $pasien->id_pasien_satu_sehat;
        $patientName = $pasien->nama_pasien ?? 'Unknown';

        // Parse jenis sample (bisa array atau JSON string)
        $jenis_sampel = $request->jenis_sampel ?? null;
        if (is_string($jenis_sampel)) {
            $decoded = json_decode($jenis_sampel, true);
            $jenis_sampel = $decoded !== null ? $decoded : [$jenis_sampel];
        }
        if (!is_array($jenis_sampel)) {
            $jenis_sampel = $jenis_sampel ? [$jenis_sampel] : [];
        }

        if (empty($jenis_sampel)) {
            Log::warning('No jenis sample found for permohonan: ' . $item_permohonan_uji_klinik->id_permohonan_uji_klinik);
            return;
        }

        // Get ServiceRequest IDs - filter out null and empty values
        $serviceRequests = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $item_permohonan_uji_klinik->id_permohonan_uji_klinik)
            ->whereNotNull('id_service_request')
            ->where('id_service_request', '!=', '')
            ->pluck('id_service_request')
            ->filter(function($id) {
                return !empty($id) && trim($id) !== '';
            })
            ->map(function($id) {
                return ["reference" => "ServiceRequest/" . $id];
            })
            ->values()
            ->toArray();

        // Format collectedDateTime from tgl_sampling and jam_sampling
        $tgl_sampling = $request->tgl_sampling ?? null;
        $jam_sampling = $request->jam_sampling ?? $request->jam_sampling_display ?? null;

        if ($tgl_sampling && $jam_sampling) {
            try {
                $collectedDateTime = Carbon::createFromFormat('Y-m-d H:i', $tgl_sampling . ' ' . $jam_sampling)->toIso8601String();
            } catch (\Exception $e) {
                $collectedDateTime = Carbon::now()->toIso8601String();
            }
        } else {
            // Fallback to registration date or current time
            $collectedDateTime = $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik
                ? Carbon::parse($item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->toIso8601String()
                : Carbon::now()->toIso8601String();
        }

        $receivedTime = $collectedDateTime; // Use same time for receivedTime

        // Call createSpecimen
        $identifier = $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik;

        // Determine initial status based on status_sampling
        $initialStatus = 'available'; // Default untuk sampling normal
        if ($pengambilan_sample_klinik && !empty($pengambilan_sample_klinik->status_sampling)) {
            $initialStatus = (strtolower($pengambilan_sample_klinik->status_sampling) === 'gagal') ? 'unavailable' : 'available';
        }

        $result = $this->createSpecimen(
            $identifier,
            $jenis_sampel,
            $collectedDateTime,
            $patientId,
            $patientName,
            $serviceRequests,
            $receivedTime,
            $initialStatus
        );

        // Update id_spesimen_satu_sehat and id_service_request_satu_sehat in pengambilan_sample_klinik if exists
        if ($pengambilan_sample_klinik) {
            // Simpan specimen ID langsung dari result createSpecimen (per pengambilan sample)
            if ($result && !empty($result['specimen_ids'])) {
                $pengambilan_sample_klinik->id_spesimen_satu_sehat = implode(',', $result['specimen_ids']);
            }

            // Simpan ServiceRequest ID sesuai dengan spesimen (1:1 mapping)
            if ($result && !empty($result['service_request_ids'])) {
                $pengambilan_sample_klinik->id_service_request_satu_sehat = implode(',', $result['service_request_ids']);
            }

            $pengambilan_sample_klinik->save();
        }
    }

    /**
     * Logout from mobile sampling
     */
    public function logout(Request $request)
    {
        $request->session()->forget([
            'mobile_sampling_klinik_auth',
            'mobile_sampling_klinik_user_id',
            'mobile_sampling_klinik_user_name',
            'mobile_sampling_klinik_user_username',
            'mobile_sampling_klinik_id',
            'permohonan_uji_klinik'
        ]);
        
        // Force save session
        $request->session()->save();

        return redirect()->route('mobile.sampling.klinik.index')
            ->with('success', 'Anda telah logout');
    }

    /**
     * Show success page
     */
    public function success(Request $request, $id_permohonan_uji_klinik)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_klinik_auth', false)) {
            return redirect()->route('mobile.sampling.klinik.login')
                ->with('error', 'Silakan login terlebih dahulu');
        }
        
        $permohonan_uji_klinik = PermohonanUjiKlinik2::where('id_permohonan_uji_klinik', $id_permohonan_uji_klinik)
            ->with('pasien')
            ->first();

        // Check if verification is already done
        $verification_sample = \Smt\Masterweb\Models\VerificationActivitySample::where('is_klinik', $id_permohonan_uji_klinik)
            ->where('id_verification_activity', 6)
            ->where('resampling', 0)
            ->first();

        $is_done = $verification_sample && $verification_sample->is_done == 1;

        // Get list petugas for tombol selesai
        $verificationActivity = VerificationActivity::where('id', 6)->first();
        $list_petugas = [];
        if ($verificationActivity && !empty($verificationActivity->klinik) && $verificationActivity->klinik != '-') {
            $list_petugas = array_map('trim', explode(',', $verificationActivity->klinik));
        }

        // Get pengambilan sample data to check status
        $pengambilan_sample = \Smt\Masterweb\Models\PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->first();

        $status_sampling = $pengambilan_sample->status_sampling ?? 'Berhasil';
        $jam_sampling = $pengambilan_sample && $pengambilan_sample->created_at ? 
            Carbon::parse($pengambilan_sample->created_at)->format('H:i') : 
            Carbon::now()->format('H:i');

        // Get count for next sampling (resampling)
        $next_count = \Smt\Masterweb\Models\PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
            ->whereNull('deleted_at')
            ->count() + 1;

        return view('masterweb::module.mobile.sampling-klinik.success', compact(
            'permohonan_uji_klinik',
            'is_done',
            'verification_sample',
            'list_petugas',
            'status_sampling',
            'jam_sampling',
            'next_count'
        ));
    }

    /**
     * Mark verification as done (Selesai)
     */
    public function markAsDone(Request $request, $id_permohonan_uji_klinik)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_klinik_auth', false)) {
            return response()->json([
                'status' => false,
                'pesan' => 'Silakan login terlebih dahulu'
            ], 401);
        }

        $request->validate([
            'jam_sampling' => 'required',
            'nama_petugas' => 'required|string',
        ]);

        try {
            // Get current date and time
            $currentDate = Carbon::now()->format('d/m/Y');
            $currentTime = $request->jam_sampling;
            $startDateTime = $currentDate . ' ' . $currentTime;
            
            // Get count from existing sampling data to determine resampling value
            $existing_count = \Smt\Masterweb\Models\PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
                ->whereNull('deleted_at')
                ->count();
            
            // Calculate resampling value based on count (count - 1, or 0 for first sampling)
            $count = $existing_count > 0 ? $existing_count : 1;
            $resamplingValue = ($count > 1) ? ($count - 1) : 0;
            
            // Call verification-analytic (local method)
            $this->verificationAnalyticStep6($id_permohonan_uji_klinik, $startDateTime, $request->nama_petugas, $resamplingValue);
            
            // Send to Satu Sehat - Step 6 (Pengambilan Sample)
            try {
                if (config('services.satu_sehat.version') == 'prd') {
                    // Format collectedDateTime from current date and jam_sampling
                    $currentDate = Carbon::now()->format('Y-m-d');
                    $jam_sampling = $request->jam_sampling;
                    $collectedDateTime = Carbon::createFromFormat('Y-m-d H:i', $currentDate . ' ' . $jam_sampling)->format('Y-m-d H:i:s');
                    
                    // Create a request-like object for sendSpecimenAndUpdateServiceRequest
                    $specimenRequest = new Request([
                        'nama_petugas' => $request->nama_petugas,
                    ]);
                    $this->sendSpecimenAndUpdateServiceRequest($id_permohonan_uji_klinik, $specimenRequest, $collectedDateTime);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the request
                Log::error('Error sending to SatuSehat in markAsDone: ' . $e->getMessage(), [
                    'permohonan_id' => $id_permohonan_uji_klinik,
                    'exception' => $e->getTraceAsString()
                ]);
            }
            
            return response()->json([
                'status' => true,
                'pesan' => 'Pengambilan sample telah diselesaikan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'pesan' => 'Gagal menyelesaikan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save signature for mobile
     */
    public function saveSignature(Request $request, $id_permohonan_uji_klinik)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_klinik_auth', false)) {
            return response()->json([
                'status' => false,
                'pesan' => 'Silakan login terlebih dahulu'
            ], 401);
        }

        $request->validate([
            'signature_pasien' => 'nullable|string',
            'signature_petugas' => 'nullable|string',
            'sampling' => 'nullable|integer',
        ]);

        try {
            // Get count for resampling
            $count = $request->count ?? 1;
            $sampling = $request->sampling ?? ($count - 1);

            // Copy logic from saveSignaturePengambilSample directly
            $post = PermohonanUjiKlinik2::find($id_permohonan_uji_klinik);
            if (!$post) {
                return response()->json(['status' => false, 'pesan' => 'Data tidak ditemukan'], 404);
            }

            // Expecting base64 PNGs
            $pasien = $request->input('signature_pasien');
            $petugas = $request->input('signature_petugas');
            $samplingValue = (int) $sampling; // 0 = sampling pertama, 1+ = resampling

            if (!$pasien && !$petugas) {
                return response()->json(['status' => false, 'pesan' => 'Tanda tangan kosong'], 422);
            }

            // Cari record PengambilanSampleKlinik berdasarkan resampling flag
            $pengambilanSample = PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
                ->where('resampling', $samplingValue)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->first();

            // Jika tidak ada, buat record baru
            if (!$pengambilanSample) {
                $pengambilanSample = new PengambilanSampleKlinik();
                $pengambilanSample->id_pengambilan_sample_klinik = Uuid::uuid4()->toString();
                $pengambilanSample->permohonan_uji_klinik_id = $id_permohonan_uji_klinik;
                $pengambilanSample->resampling = $samplingValue;
                $pengambilanSample->pasien_permohonan_uji_klinik = $post->pasien_permohonan_uji_klinik ?? null;
                $pengambilanSample->petugas_name = $post->plebotomist_permohonan_uji_klinik ?? null;
                $pengambilanSample->status_sampling = 'Berhasil'; // Default status
                $pengambilanSample->jenis_sample = null;
                $pengambilanSample->kondisi_pasien = null;

                // Calculate number_sampling_success
                $pengambilanSample->number_sampling_success = PengambilanSampleKlinik::calculateNumberSamplingSuccess($id_permohonan_uji_klinik);
            }

            // Update signature
            if ($pasien) {
                $pengambilanSample->signature_pengambil_sample_pasien = $pasien; // base64 data URL
            }
            if ($petugas) {
                $pengambilanSample->signature_pengambil_sample_petugas = $petugas; // base64 data URL
            }

            $pengambilanSample->save();

            // Fallback: juga simpan ke tabel utama untuk kompatibilitas
            if ($samplingValue == 0) {
                if ($pasien) {
                    $post->signature_pengambil_sample_pasien = $pasien;
                }
                if ($petugas) {
                    $post->signature_pengambil_sample_petugas = $petugas;
                }
                $post->save();
            }

            return response()->json(['status' => true, 'pesan' => 'Tanda tangan tersimpan']);
        } catch (\Exception $e) {
            Log::error('Mobile save signature error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'pesan' => 'Gagal menyimpan tanda tangan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verification Analytic Step 6 (Pengambilan Sample) - copied logic from LaboratoriumPermohonanUjiKlinikManagement2
     * This method handles verification activity for sampling step
     */
    private function verificationAnalyticStep6($id_permohonan_uji_klinik, $startDateTime, $namaPetugas, $resamplingValue)
    {
        DB::beginTransaction();
        try {
            $permohonanForDate = PermohonanUjiKlinik2::find($id_permohonan_uji_klinik);
            $registerAt = $permohonanForDate ? DateHelper::permohonanAnchorAt($permohonanForDate) : null;

            // Parse datetime - support d/m/Y H:i format
            $parseToCarbon = function (?string $val) use ($registerAt): ?\Carbon\Carbon {
                if (!$val) return null;
                $val = trim($val);
                try {
                    if (str_contains($val, '/')) { // d/m/Y H:i
                        return Carbon::createFromFormat('d/m/Y H:i', $val);
                    }
                    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $val)) { // Y-m-d H:i:s
                        return Carbon::createFromFormat('Y-m-d H:i:s', $val);
                    }
                    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $val)) { // Y-m-d H:i
                        return Carbon::createFromFormat('Y-m-d H:i', $val);
                    }
                    if (preg_match('/^\d{1,2}:\d{2}$/', $val)) { // HH:mm (jam saja) — tanggal registrasi
                        return DateHelper::clockOnRegisterDate($registerAt, $val);
                    }
                } catch (\Throwable $e) {
                    return null;
                }
                return null;
            };

            $start = $parseToCarbon($startDateTime);
            $stop = $start ? $start->copy() : null;

            // Fallback: tanggal registrasi + jam klik
            if (!$start) $start = DateHelper::clockOnRegisterDate($registerAt);
            if (!$stop) $stop = $start->copy();

            // Simpan kembali ke bentuk d/m/Y H:i untuk downstream logic
            $requestStartDate = $start->format('d/m/Y H:i');
            $requestStopDate = $requestStartDate; // Klinik: tanggal mulai dan tanggal selesai disamakan

            // Check if klinik
            $is_klinik = VerificationActivitySample::where([
                ['is_klinik', '=', $id_permohonan_uji_klinik],
                ['id_verification_activity', '=', 1],
            ])->first();

            if (isset($is_klinik)) {
                $data_verifikasi = VerificationActivitySample::where([
                    ['is_klinik', '=', $id_permohonan_uji_klinik],
                    ['resampling', '=', $resamplingValue],
                    ['id_verification_activity', '=', 6],
                ])->first();

                if (isset($data_verifikasi) && $data_verifikasi->is_done == 0) {
                    // Update existing record
                    $data_verifikasi->nama_petugas = $namaPetugas;
                    $data_verifikasi->is_done = 1;
                    $data_verifikasi->save();

                    $data_permohonan_uji_klinik = PermohonanUjiKlinik2::findOrFail($id_permohonan_uji_klinik);
                    $data_permohonan_uji_klinik->tgl_sampling_permohonan_uji_klinik = Carbon::createFromFormat('d/m/Y H:i', $requestStartDate)->format('Y-m-d');
                    $data_permohonan_uji_klinik->jam_sampling_permohonan_uji_klinik = Carbon::createFromFormat('d/m/Y H:i', $requestStartDate)->format('H:i:s');
                    $data_permohonan_uji_klinik->plebotomist_permohonan_uji_klinik = $namaPetugas;
                    $data_permohonan_uji_klinik->save();

                } else if (isset($data_verifikasi) && $data_verifikasi->is_done == 1) {
                    // Update start date, stop date, nama petugas
                    $data_verifikasi->start_date = Carbon::createFromFormat('d/m/Y H:i', $requestStartDate)->format('Y-m-d H:i:s');
                    $data_verifikasi->stop_date = Carbon::createFromFormat('d/m/Y H:i', $requestStopDate)->format('Y-m-d H:i:s');
                    $data_verifikasi->nama_petugas = $namaPetugas;

                    $data_permohonan_uji_klinik = PermohonanUjiKlinik2::findOrFail($id_permohonan_uji_klinik);
                    $data_permohonan_uji_klinik->tgl_sampling_permohonan_uji_klinik = Carbon::createFromFormat('d/m/Y H:i', $requestStartDate)->format('Y-m-d');
                    $data_permohonan_uji_klinik->jam_sampling_permohonan_uji_klinik = Carbon::createFromFormat('d/m/Y H:i', $requestStartDate)->format('H:i:s');
                    $data_permohonan_uji_klinik->plebotomist_permohonan_uji_klinik = $namaPetugas;
                    $data_permohonan_uji_klinik->save();

                    $data_verifikasi->save();
                } else {
                    // Create new record
                    $verificationActivitySample = new VerificationActivitySample();
                    $verificationActivitySample->id = Uuid::uuid4()->toString();
                    $verificationActivitySample->id_verification_activity = 6;
                    $verificationActivitySample->start_date = Carbon::createFromFormat('d/m/Y H:i', $requestStartDate)->format('Y-m-d H:i:s');
                    $verificationActivitySample->stop_date = Carbon::createFromFormat('d/m/Y H:i', $requestStopDate)->format('Y-m-d H:i:s');
                    $verificationActivitySample->nama_petugas = $namaPetugas;
                    $verificationActivitySample->resampling = $resamplingValue;
                    $verificationActivitySample->is_klinik = $id_permohonan_uji_klinik;
                    $verificationActivitySample->is_done = 1;
                    $verificationActivitySample->save();
                }

                // Update Encounter di Satu Sehat untuk step 6
                if (config('services.satu_sehat.version') == 'prd') {
                    $start_date = Carbon::createFromFormat('d/m/Y H:i', $requestStartDate)->format('Y-m-d H:i:s');
                    $stop_date = Carbon::createFromFormat('d/m/Y H:i', $requestStopDate)->format('Y-m-d H:i:s');

                    $newStatusHistory = [
                        "status" => "in-progress",
                        "period" => [
                            "start" => Carbon::createFromFormat('Y-m-d H:i:s', $start_date)->toIso8601String(),
                            "end" => Carbon::createFromFormat('Y-m-d H:i:s', $stop_date)->toIso8601String()
                        ]
                    ];

                    // Call updateEncounter method
                    $this->updateEncounter(
                        $id_permohonan_uji_klinik,
                        $namaPetugas,
                        "in-progress",
                        "Ruang Pengambilan Sample",
                        "OBSENC",
                        "Pengambilan Sample",
                        "ATND",
                        Carbon::createFromFormat('Y-m-d H:i:s', $start_date)->toIso8601String(),
                        Carbon::createFromFormat('Y-m-d H:i:s', $stop_date)->toIso8601String(),
                        $newStatusHistory
                    );
                }

                DB::commit();
            } else {
                DB::rollBack();
                Log::warning('verificationAnalyticStep6: is_klinik not found', ['id' => $id_permohonan_uji_klinik]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('verificationAnalyticStep6 error: ' . $e->getMessage(), [
                'id' => $id_permohonan_uji_klinik,
                'exception' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Send Specimen to SatuSehat and update ServiceRequest when sampling is completed
     * Called when verification_step = 6 and is_done = 1
     */
    private function sendSpecimenAndUpdateServiceRequest($id_permohonan_uji_klinik, $request, $collectedDateTime)
    {
        // Only send if VERSION_SATUSEHAT = prd
        if (config('services.satu_sehat.version') !== 'prd') {
            Log::info('SatuSehat integration skipped: VERSION_SATUSEHAT is not prd');
            return;
        }

        // Get permohonan uji klinik data
        $item_permohonan_uji_klinik = PermohonanUjiKlinik2::find($id_permohonan_uji_klinik);
        if (!$item_permohonan_uji_klinik) {
            Log::warning('Permohonan uji klinik not found: ' . $id_permohonan_uji_klinik);
            return;
        }

        // Get patient data
        $pasien = Pasien::find($item_permohonan_uji_klinik->pasien_permohonan_uji_klinik);
        if (!$pasien || empty($pasien->id_pasien_satu_sehat)) {
            Log::warning('Patient SatuSehat ID not found for permohonan: ' . $id_permohonan_uji_klinik);
            return;
        }

        $patientId = $pasien->id_pasien_satu_sehat;
        $patientName = $pasien->nama_pasien ?? 'Unknown';

        // Get PengambilanSampleKlinik data (latest one for this permohonan)
        $pengambilan_sample_klinik = PengambilanSampleKlinik::where('permohonan_uji_klinik_id', $id_permohonan_uji_klinik)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$pengambilan_sample_klinik) {
            Log::warning('PengambilanSampleKlinik not found for permohonan: ' . $id_permohonan_uji_klinik);
            return;
        }

        // Parse jenis_sample (bisa JSON string atau array)
        $jenis_sampel = $pengambilan_sample_klinik->jenis_sample ?? null;
        if (is_string($jenis_sampel)) {
            $decoded = json_decode($jenis_sampel, true);
            $jenis_sampel = $decoded !== null ? $decoded : [$jenis_sampel];
        }
        if (!is_array($jenis_sampel)) {
            $jenis_sampel = $jenis_sampel ? [$jenis_sampel] : [];
        }

        if (empty($jenis_sampel)) {
            Log::warning('No jenis sample found in PengambilanSampleKlinik for permohonan: ' . $id_permohonan_uji_klinik);
            return;
        }

        // Get ServiceRequest IDs - prioritize id_service_request_satu_sehat from PengambilanSampleKlinik (for resampling)
        // If not available, fallback to PermohonanUjiPaketKlinik
        $serviceRequests = [];

        if (!empty($pengambilan_sample_klinik->id_service_request_satu_sehat)) {
            // Use ServiceRequest IDs from PengambilanSampleKlinik (for resampling)
            $serviceRequestIds = explode(',', $pengambilan_sample_klinik->id_service_request_satu_sehat);
            $serviceRequests = array_map(function($id) {
                $id = trim($id);
                return !empty($id) ? ["reference" => "ServiceRequest/" . $id] : null;
            }, $serviceRequestIds);
            $serviceRequests = array_filter($serviceRequests); // Remove null values
            $serviceRequests = array_values($serviceRequests); // Re-index array

            Log::info('Using ServiceRequest IDs from PengambilanSampleKlinik for Specimen creation', [
                'permohonan_id' => $id_permohonan_uji_klinik,
                'service_request_ids' => $serviceRequestIds,
                'resampling' => $pengambilan_sample_klinik->resampling ?? 0
            ]);
        }

        // Fallback to PermohonanUjiPaketKlinik if no ServiceRequest IDs from PengambilanSampleKlinik
        if (empty($serviceRequests)) {
            $serviceRequests = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
                ->whereNotNull('id_service_request')
                ->where('id_service_request', '!=', '')
                ->pluck('id_service_request')
                ->filter(function($id) {
                    return !empty($id) && trim($id) !== '';
                })
                ->map(function($id) {
                    return ["reference" => "ServiceRequest/" . $id];
                })
                ->values()
                ->toArray();
        }

        // Format collectedDateTime (already in Y-m-d H:i:s format)
        $collectedDateTimeIso = Carbon::parse($collectedDateTime)->toIso8601String();
        $receivedTime = $collectedDateTimeIso; // Use same time for receivedTime

        // Call createSpecimen
        $identifier = $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik;

        // Determine initial status based on status_sampling
        // If status_sampling is "gagal", use "unavailable", otherwise "available"
        $initialStatus = 'available'; // Default untuk sampling normal
        if ($pengambilan_sample_klinik && !empty($pengambilan_sample_klinik->status_sampling)) {
            $initialStatus = (strtolower($pengambilan_sample_klinik->status_sampling) === 'gagal') ? 'unavailable' : 'available';
        }

        $result = $this->createSpecimen(
            $identifier,
            $jenis_sampel,
            $collectedDateTimeIso,
            $patientId,
            $patientName,
            $serviceRequests,
            $receivedTime,
            $initialStatus
        );

        // Simpan specimen ID dan ServiceRequest ID sesuai dengan spesimen (1:1 mapping)
        if ($result) {
            if (!empty($result['specimen_ids'])) {
                $pengambilan_sample_klinik->id_spesimen_satu_sehat = implode(',', $result['specimen_ids']);
            }
            if (!empty($result['service_request_ids'])) {
                $pengambilan_sample_klinik->id_service_request_satu_sehat = implode(',', $result['service_request_ids']);
            }
            $pengambilan_sample_klinik->save();
        }

        // Update ServiceRequest dengan petugas pengambil sample (performer)
        $nama_petugas = $request->get('nama_petugas');
        if (!empty($nama_petugas) && !empty($serviceRequests)) {
            // Get practitioner code from SatuSehatPractitioner - normalize dengan menghilangkan koma dari kedua sisi
            $nama_petugas_normalized = str_replace(',', '', $nama_petugas);

            $practitioner = SatuSehatPractitioner::whereRaw("REPLACE(name_petugas, ',', '') LIKE ?", ['%' . $nama_petugas_normalized . '%'])->first();

            // Jika tidak ditemukan dengan like, coba cari dari ms_petugas
            if (!$practitioner) {
                $petugas = Petugas::where('nama', 'like', '%' . $nama_petugas_normalized . '%')->first();
                if ($petugas && !empty($petugas->code_satu_sehat_practitioner)) {
                    $practitioner = SatuSehatPractitioner::where('code_satu_sehat_practitioner', $petugas->code_satu_sehat_practitioner)->first();
                }
            }

            // Pastikan $practitioner adalah object dan memiliki code_satu_sehat_practitioner
            if ($practitioner !== null && is_object($practitioner) && isset($practitioner->code_satu_sehat_practitioner) && !empty($practitioner->code_satu_sehat_practitioner)) {
                $practitionerId = $practitioner->code_satu_sehat_practitioner;
                $practitionerName = $practitioner->name_satu_sehat_practitioner ?? $nama_petugas;

                // Update each ServiceRequest
                foreach ($serviceRequests as $serviceRequestRef) {
                    $serviceRequestId = str_replace('ServiceRequest/', '', $serviceRequestRef['reference']);

                    // Get existing ServiceRequest data
                    $permohonanUjiPaketKlinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
                        ->where('id_service_request', $serviceRequestId)
                        ->first();

                    if ($permohonanUjiPaketKlinik && !empty($permohonanUjiPaketKlinik->response_service_request)) {
                        $existingData = json_decode($permohonanUjiPaketKlinik->response_service_request, true);

                        // Update performer with sampling practitioner
                        $existingData['performer'] = [
                            ["reference" => "Practitioner/" . $practitionerId, "display" => $practitionerName]
                        ];

                        // Update ServiceRequest via PUT
                        try {
                            $response = $this->satuSehatHelper->put('ServiceRequest', $serviceRequestId, $existingData);

                            if ($response['status_code'] == '200' || $response['status_code'] == '201') {
                                // Update response_service_request dengan data terbaru
                                $permohonanUjiPaketKlinik->response_service_request = json_encode($response['body']);
                                $permohonanUjiPaketKlinik->save();

                                Log::info('ServiceRequest updated successfully', [
                                    'service_request_id' => $serviceRequestId,
                                    'practitioner_id' => $practitionerId,
                                    'practitioner_name' => $practitionerName
                                ]);
                            } else {
                                Log::warning('Failed to update ServiceRequest', [
                                    'service_request_id' => $serviceRequestId,
                                    'response' => $response
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Error updating ServiceRequest: ' . $e->getMessage(), [
                                'service_request_id' => $serviceRequestId,
                                'exception' => $e->getTraceAsString()
                            ]);
                        }
                    }
                }
            } else {
                Log::warning('Practitioner SatuSehat not found for petugas: ' . $nama_petugas);
            }
        }
    }

    /**
     * Create Specimen untuk Satu Sehat
     */
    private function createSpecimen($identifier, $specimens, $collectedDateTime, $patientId, $patientName, $idRequests, $receivedTime, $initialStatus = "available")
    {
        // Mapping jenis sample Indonesia ke SNOMED CT code
        $specimenCode = [
            "Darah Beku" => [
                "code" => "119294007",
                "display" => "Dried blood specimen"
            ],
            "NaF" => [
                "code" => "129501009",
                "display" => "NaF"
            ],
            "EDTA" => [
                "code" => "69519002",
                "display" => "EDTA"
            ],
            "Urine" => [
                "code" => "78014005",
                "display" => "Urine"
            ],
            "Darah" => [
                "code" => "119297000",
                "display" => "Blood specimen"
            ],
            "Serum" => [
                "code" => "119364003",
                "display" => "Serum specimen"
            ],
            "Plasma" => [
                "code" => "119361006",
                "display" => "Plasma specimen"
            ],
            "Feses" => [
                "code" => "119339001",
                "display" => "Stool specimen"
            ],
            "Swab" => [
                "code" => "258467004",
                "display" => "Swab specimen"
            ]
        ];

        // Normalize specimen names (case-insensitive matching)
        $normalizedSpecimens = [];
        foreach ($specimens as $specimen) {
            $specimenKey = null;
            $specimenTrimmed = trim($specimen);
            foreach (array_keys($specimenCode) as $key) {
                if (strcasecmp($specimenTrimmed, $key) === 0) {
                    $specimenKey = $key;
                    break;
                }
            }
            if ($specimenKey && !in_array($specimenKey, $normalizedSpecimens)) {
                $normalizedSpecimens[] = $specimenKey;
            } else if (!$specimenKey) {
                Log::warning('Unknown specimen type: ' . $specimenTrimmed);
            }
        }

        if (empty($normalizedSpecimens)) {
            Log::warning('No valid specimen codes found for specimens: ' . json_encode($specimens));
            return;
        }

        // Validate and filter idRequests - remove empty ServiceRequest references
        $validIdRequests = [];
        if (!empty($idRequests) && is_array($idRequests)) {
            foreach ($idRequests as $request) {
                if (isset($request['reference'])) {
                    $serviceRequestId = str_replace('ServiceRequest/', '', $request['reference']);
                    // Only add if ServiceRequest ID is not empty
                    if (!empty($serviceRequestId)) {
                        $validIdRequests[] = $request;
                    }
                }
            }
        }

        // If no valid ServiceRequest IDs, cannot create Specimen (SatuSehat requires request field)
        if (empty($validIdRequests)) {
            Log::warning('No valid ServiceRequest IDs found for Specimen creation - skipping Specimen creation', [
                'identifier' => $identifier,
                'idRequests' => $idRequests
            ]);
            return; // Cannot create Specimen without ServiceRequest (mandatory field)
        }

        $idSpecimenSatuSehat = [];
        $idServiceRequestSatuSehat = []; // Store ServiceRequest ID for each specimen
        $specimenIndex = 0;

        foreach ($normalizedSpecimens as $specimen){
            // Use one ServiceRequest ID per specimen (1:1 mapping)
            // If there are more specimens than ServiceRequests, use the last ServiceRequest for remaining specimens
            $serviceRequestForSpecimen = [];
            if (!empty($validIdRequests)) {
                $requestIndex = min($specimenIndex, count($validIdRequests) - 1);
                $serviceRequestForSpecimen = [$validIdRequests[$requestIndex]];
            }

            $data = [
                "resourceType" => "Specimen",
                "identifier" => [
                    [
                        "system" => "http://sys-ids.kemkes.go.id/specimen/".config("services.satu_sehat.org_id"),
                        "value" => $identifier,
                        "assigner" => [
                            "reference" => "Organization/".config("services.satu_sehat.org_id")
                        ]
                    ]
                ],
                "status" => $initialStatus,
                "type" => [
                    "coding" => [
                        [
                            "system" => "http://snomed.info/sct",
                            "code" => $specimenCode[$specimen]["code"],
                            "display" => $specimenCode[$specimen]["display"]
                        ]
                    ]
                ],
                "collection" => [
                    "collectedDateTime" => $collectedDateTime,
                    "extension" => [
                        [
                            "url" => "https://fhir.kemkes.go.id/r4/StructureDefinition/CollectorOrganization",
                            "valueReference" => [
                                "reference" => "Organization/".config("services.satu_sehat.org_id")
                            ]
                        ]
                    ]
                ],
                "subject" => [
                    "reference" => "Patient/".$patientId,
                    "display" => $patientName
                ],
                "receivedTime" => $receivedTime,
                "request" => $serviceRequestForSpecimen  // One ServiceRequest per Specimen
            ];

            $response = $this->satuSehatHelper->post("Specimen", $data);

            if ($response['status_code'] == '201'){
                $specimenId = $response['body']["id"];
                $idSpecimenSatuSehat[] = $specimenId;

                // Store ServiceRequest ID for this specimen
                if (!empty($serviceRequestForSpecimen)) {
                    $serviceRequestId = str_replace('ServiceRequest/', '', $serviceRequestForSpecimen[0]['reference']);
                    $idServiceRequestSatuSehat[] = $serviceRequestId;
                }
            } else {
                // Jika gagal dengan status "available", coba lagi dengan status "unavailable"
                Log::warning('Failed to create specimen with status "available", trying with "unavailable"', [
                    'specimen' => $specimen,
                    'response' => $response,
                    'data' => $data
                ]);

                // Ubah status menjadi "unavailable" dan coba lagi
                $data['status'] = 'unavailable';

                $response = $this->satuSehatHelper->post("Specimen", $data);

                if ($response['status_code'] == '201'){
                    $specimenId = $response['body']["id"];
                    $idSpecimenSatuSehat[] = $specimenId;

                    // Store ServiceRequest ID for this specimen
                    if (!empty($serviceRequestForSpecimen)) {
                        $serviceRequestId = str_replace('ServiceRequest/', '', $serviceRequestForSpecimen[0]['reference']);
                        $idServiceRequestSatuSehat[] = $serviceRequestId;
                    }

                    Log::info('Specimen created successfully with status "unavailable"', [
                        'specimen' => $specimen,
                        'specimen_id' => $specimenId
                    ]);
                } else {
                    Log::error('Failed to create specimen in SatuSehat even with status "unavailable"', [
                        'specimen' => $specimen,
                        'response' => $response,
                        'data' => $data
                    ]);
                    throw new \Exception("Gagal membuat spesimen! Response: " . json_encode($response));
                }
            }

            $specimenIndex++; // Increment index for next specimen
        }

        // Return both specimen IDs and ServiceRequest IDs (1:1 mapping)
        return [
            'specimen_ids' => $idSpecimenSatuSehat,
            'service_request_ids' => $idServiceRequestSatuSehat
        ];
    }

}