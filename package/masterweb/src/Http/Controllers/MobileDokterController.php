<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smt\Masterweb\Models\User;
use Smt\Masterweb\Models\BakuMutu;
use Smt\Masterweb\Models\Pasien;
use Smt\Masterweb\Models\Petugas;
use Smt\Masterweb\Models\PermohonanUjiKlinik2;
use Smt\Masterweb\Models\PermohonanUjiParameterKlinik;
use Smt\Masterweb\Models\VerificationActivitySample;
use Smt\Masterweb\Models\ParameterJenisKlinik;
use Smt\Masterweb\Models\ParameterPaketExtra;
use Smt\Masterweb\Models\ParameterPaketKlinik;
use Smt\Masterweb\Models\ParameterSubPaketExtra;
use Smt\Masterweb\Models\ParameterSubSatuanKlinik;
use Smt\Masterweb\Models\PermohonanUjiPaketKlinik;
use Smt\Masterweb\Models\PermohonanUjiSubParameterKlinik;
use Smt\Masterweb\Models\BakuMutuDetailParameterKlinik;
use Smt\Masterweb\Models\SatuSehatLocation;
use Smt\Masterweb\Models\SatuSehatPractitioner;
use Smt\Masterweb\Models\NomerLabSequence;
use Smt\Masterweb\Models\ParameterPaketJenisKlinik;
use Smt\Masterweb\Models\ParameterSatuanPaketKlinik;
use Smt\Masterweb\Models\PermohonanUjiPaymentKlinik;
use Smt\Masterweb\Models\VerificationActivity;
use Smt\Masterweb\Helpers\SatuSehatHelper;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class MobileDokterController extends Controller
{
    protected $satuSehatHelper;

    public function __construct(SatuSehatHelper $satuSehatHelper)
    {
        $this->satuSehatHelper = $satuSehatHelper;
    }

    /**
     * Mobile dokter home: scan or input ID permohonan
     */
    public function home(Request $request)
    {
        $isAuthenticated = $request->session()->get('mobile_dokter_auth', false);
        return view('masterweb::module.mobile.dokter.index', [
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

        $id_permohonan = trim($request->input('id_permohonan'));
        
        // Check if permohonan exists
        $permohonan = PermohonanUjiKlinik2::find($id_permohonan);
        
        if (!$permohonan) {
            return redirect()->route('mobile.dokter.home')
                ->with('error', 'ID Permohonan tidak ditemukan.');
        }

        // Check if sample is still in process
        $sampleInProcess = $this->checkSampleInProcess($id_permohonan);
        if ($sampleInProcess['in_process']) {
            return redirect()->route('mobile.dokter.home')
                ->with('error', $sampleInProcess['message']);
        }

        // Check if user is authenticated
        $isAuthenticated = $request->session()->get('mobile_dokter_auth', false);
        
        if (!$isAuthenticated) {
            // Store ID in session and redirect to login
            $request->session()->put('mobile_dokter_temp_id', $id_permohonan);
            return redirect()->route('mobile.dokter.login', ['id' => $id_permohonan]);
        }

        // Check if already has diagnosis and parameters
        return $this->checkAndRedirect($request, $id_permohonan);
    }

    /**
     * Scan QR code handler
     */
    public function scan(Request $request, $id)
    {
        $permohonan = PermohonanUjiKlinik2::find($id);
        
        if (!$permohonan) {
            return redirect()->route('mobile.dokter.home')
                ->with('error', 'ID Permohonan tidak ditemukan.');
        }

        // Check if sample is still in process
        $sampleInProcess = $this->checkSampleInProcess($id);
        if ($sampleInProcess['in_process']) {
            return redirect()->route('mobile.dokter.home')
                ->with('error', $sampleInProcess['message']);
        }

        // Check if user is authenticated
        $isAuthenticated = $request->session()->get('mobile_dokter_auth', false);
        
        if (!$isAuthenticated) {
            // Store ID in session and redirect to login
            $request->session()->put('mobile_dokter_temp_id', $id);
            return redirect()->route('mobile.dokter.login', ['id' => $id]);
        }

        // Check if already has diagnosis and parameters
        return $this->checkAndRedirect($request, $id);
    }

    /**
     * Show login page
     */
    public function login(Request $request, $id)
    {
        $permohonan = PermohonanUjiKlinik2::find($id);
        
        if (!$permohonan) {
            return redirect()->route('mobile.dokter.home')
                ->with('error', 'ID Permohonan tidak ditemukan.');
        }

        // Get pasien data
        $pasien = Pasien::where('id_pasien', $permohonan->pasien_permohonan_uji_klinik)->first();

        return view('masterweb::module.mobile.dokter.login', compact('permohonan', 'pasien'));
    }

    /**
     * Handle login
     */
    public function doLogin(Request $request, $id)
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

        // Find user
        $user = User::where('username', $request->username)->first();
      
        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Username atau password salah.');
        }

    

        // Check if user has dokter role
        if ($user->privilege->level !== 'DKTR' && $user->privilege->level !== 'ADMIN' && $user->privilege->level !== 'LAB') {
       
            return redirect()->back()
                ->withInput()
                ->with('error', 'Anda tidak memiliki akses sebagai dokter.');
        }

        // Check if user is active
        if ($user->publish != '1') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun Anda tidak aktif. Silakan hubungi admin.');
        }

        try {
            SatuSehatHelper::ensureAccessToken();
        } catch (\Throwable $e) {
            Log::warning('Satu Sehat token refresh skipped for mobile dokter: ' . $e->getMessage());
        }

        // Set session
        $request->session()->put('mobile_dokter_auth', true);
        $request->session()->put('mobile_dokter_user_id', $user->id);
        $request->session()->put('mobile_dokter_username', $user->username);

        // Clear temp ID
        $request->session()->forget('mobile_dokter_temp_id');

        // Check if sample is still in process before redirecting
        $sampleInProcess = $this->checkSampleInProcess($id);
        if ($sampleInProcess['in_process']) {
            return redirect()->route('mobile.dokter.home')
                ->with('error', $sampleInProcess['message']);
        }

        // Check and redirect
        return $this->checkAndRedirect($request, $id);
    }

    /**
     * Show diagnosis page
     */
    public function diagnosis(Request $request, $id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_dokter_auth', false)) {
            return redirect()->route('mobile.dokter.login', ['id' => $id]);
        }

        $item = PermohonanUjiKlinik2::find($id);

        if (!$item) {
            return redirect()->route('mobile.dokter.home')
                ->with('error', 'Data tidak ditemukan');
        }

        $tgl_register = Carbon::createFromFormat('Y-m-d H:i:s', $item->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y');
        $code = $item->noregister_permohonan_uji_klinik;

        $pasien = Pasien::where('id_pasien', $item->pasien_permohonan_uji_klinik)->first();

        if ($pasien && $pasien->tgllahir_pasien) {
            $tanggal_lahir = Carbon::parse($pasien->tgllahir_pasien);
            $tanggal_sekarang = Carbon::now();
            $umur_tahun = $tanggal_sekarang->diffInYears($tanggal_lahir);
            $umur_bulan = $tanggal_sekarang->diffInMonths($tanggal_lahir) % 12;
            $umur_hari = $tanggal_sekarang->diffInDays($tanggal_lahir->copy()->addYears($umur_tahun)->addMonths($umur_bulan));
            $umur_string = "$umur_tahun tahun $umur_bulan bulan $umur_hari hari";
        } else {
            $umur_string = "Tanggal lahir tidak tersedia";
        }

        return view('masterweb::module.mobile.dokter.diagnosis', [
            'item' => $item,
            'tgl_register' => $tgl_register,
            'id' => $id,
            'code' => $code,
            'pasien' => $pasien,
            'umur_string' => $umur_string,
        ]);
    }

    /**
     * Store diagnosis
     */
    public function storeDiagnosis(Request $request, $id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_dokter_auth', false)) {
            if ($request->expectsJson()) {
                return response()->json(['status' => false, 'pesan' => 'Anda harus login terlebih dahulu.'], 401);
            }
            return redirect()->route('mobile.dokter.login', ['id' => $id]);
        }

        $request->validate([
            'diagnosa_permohonan_uji_klinik' => 'required|string'
        ]);

        DB::beginTransaction();

        try {
            $post = PermohonanUjiKlinik2::find($id);

            if (!$post) {
                if ($request->expectsJson()) {
                    return response()->json(['status' => false, 'pesan' => 'Data tidak ditemukan!'], 404);
                }
                return redirect()->route('mobile.dokter.home')
                    ->with('error', 'Data tidak ditemukan.');
            }

            // Update field diagnosis
            $post->diagnosa_permohonan_uji_klinik = $request->diagnosa_permohonan_uji_klinik;
            $post->done_register = true;
            $post->save();

            DB::commit();

            // Redirect ke halaman parameter
            if ($request->expectsJson()) {
                $urlNextStep = route('mobile.dokter.create-parameter', $id);
                return response()->json([
                    'status' => true,
                    'pesan' => "Diagnosis berhasil disimpan!",
                    'urlNextStep' => $urlNextStep
                ], 200);
            }

            return redirect()->route('mobile.dokter.create-parameter', ['id' => $id])
                ->with('success', 'Diagnosis berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saat menyimpan diagnosis: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json(['status' => false, 'pesan' => 'Gagal menyimpan diagnosis! ' . $e->getMessage()], 500);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan diagnosis! ' . $e->getMessage());
        }
    }

    /**
     * Show create parameter page
     */
    public function createParameter(Request $request, $id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_dokter_auth', false)) {
            return redirect()->route('mobile.dokter.login', ['id' => $id]);
        }

        $item = PermohonanUjiKlinik2::find($id);

        if (!$item) {
            return redirect()->route('mobile.dokter.home')
                ->with('error', 'Data tidak ditemukan');
        }

        $tgl_register = Carbon::createFromFormat('Y-m-d H:i:s', $item->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y');
        $code = $item->noregister_permohonan_uji_klinik;

        $pasien = Pasien::where('id_pasien', $item->pasien_permohonan_uji_klinik)->first();

        if ($pasien && $pasien->tgllahir_pasien) {
            $tanggal_lahir = Carbon::parse($pasien->tgllahir_pasien);
            $tanggal_sekarang = Carbon::now();
            $umur_tahun = $tanggal_sekarang->diffInYears($tanggal_lahir);
            $umur_bulan = $tanggal_sekarang->diffInMonths($tanggal_lahir) % 12;
            $umur_hari = $tanggal_sekarang->diffInDays($tanggal_lahir->copy()->addYears($umur_tahun)->addMonths($umur_bulan));
            $umur_string = "$umur_tahun tahun $umur_bulan bulan $umur_hari hari";
        } else {
            $umur_string = "Tanggal lahir tidak tersedia";
        }

        $parameter_jenis_klinik = ParameterJenisKlinik::with([
            'pakets' => function ($q) {
                $q->orderBy('sort', 'asc');
            }
        ])->orderBy('created_at', 'asc')->get();

        $parameter_paket_extra = ParameterPaketExtra::with('parameterSubPaketExtra')->orderBy('created_at', 'asc')->get();

        // Get already selected pakets
        $data_paket = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id)
            ->orderBy('parameter_paket_extra', 'desc')
            ->get();

        $temp_datas = [];
        $temp_extra_paket = null;
        foreach($data_paket as $val){
            if(isset($val->parameter_paket_extra) && $temp_extra_paket != $val->parameter_paket_extra && $val->parameter_paket_extra != ''){
                $extra_paket = ParameterPaketExtra::where('id_parameter_paket_extra', $val->parameter_paket_extra)->first();
                if ($extra_paket) {
                    $value_extra_paket = [
                        "id_permohonan_uji_paket_klinik" => $val->id_permohonan_uji_paket_klinik,
                        "permohonan_uji_klinik" => $val->permohonan_uji_klinik,
                        "parameter_jenis_klinik" =>  $val->parameter_jenis_klinik,
                        "type_permohonan_uji_paket_klinik" =>  'EP',
                        "nama_parameter_paket_extra" => $extra_paket->nama_parameter_paket_extra,
                        "parameter_paket_klinik" =>  $val->parameter_paket_klinik,
                        "parameter_paket_extra" =>  $val->parameter_paket_extra,
                        "harga_permohonan_uji_paket_klinik" =>  $extra_paket->harga_parameter_paket_extra,
                    ];
                    array_push($temp_datas, $value_extra_paket);
                    $temp_extra_paket = $val->parameter_paket_extra;
                }
            }elseif($val->parameter_paket_extra == ''){
                array_push($temp_datas, $val);
            }
        }

        $data_paket_extra = $temp_datas;
        $selectedPaketExtra = collect($data_paket_extra)->pluck('parameter_paket_extra')->toArray();

        // Get selected paket names for checking
        $paket_array = [];
        $selected_paket_ids = [];
        foreach ($data_paket as $val) {
            if (empty($val->parameter_paket_extra)) {
                $paket = ParameterPaketKlinik::where('id_parameter_paket_klinik', $val->parameter_paket_klinik)->first();
                if ($paket) {
                    $paket_array[] = [
                        'name_parameter_paket_klinik' => $paket->name_parameter_paket_klinik,
                    ];
                    $selected_paket_ids[] = $val->parameter_paket_klinik;
                }
            }
        }

        return view('masterweb::module.mobile.dokter.create-parameter', [
            'item' => $item,
            'tgl_register' => $tgl_register,
            'id' => $id,
            'code' => $code,
            'pasien' => $pasien,
            'umur_string' => $umur_string,
            'parameter_paket_extra' => $parameter_paket_extra,
            'parameter_jenis_klinik' => $parameter_jenis_klinik,
            'paket' => $paket_array,
            'paket_extra' => $selectedPaketExtra,
            'selected_paket_ids' => $selected_paket_ids,
        ]);
    }

    /**
     * Store parameter - copy all logic from web version including Satu Sehat integration
     */
    public function storeParameter(Request $request, $id_permohonan_uji_klinik)
    {
        // Check authentication
        if (!$request->session()->get('mobile_dokter_auth', false)) {
            return response()->json(['status' => false, 'pesan' => 'Anda harus login terlebih dahulu.'], 401);
        }

        // Get user from session
        $user_id = $request->session()->get('mobile_dokter_user_id');
        $user = User::find($user_id);
        
        if (!$user) {
            return response()->json(['status' => false, 'pesan' => 'User tidak ditemukan.'], 401);
        }

        // Temporarily login user to Laravel auth system
        // This is needed because some parts use auth()->id() and auth()->user()->name
        Auth::login($user);

        try {
            // Inisialisasi total harga
            $total_harga = 0;

            // Mulai DB transaction
            DB::beginTransaction();

            //hapus parameter paket permohonan uji klinik
            $permohonanUjiPaketKlinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
                ->where([
                    ['is_prolanis_gula', '=', 0],
                    ['is_prolanis_urine', '=', 0],
                    ['is_haji', '=', 0]
                ])
                ->delete();

            $permohonanUjiParameterKlinik = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
                ->where([
                    ['is_prolanis_gula', '=', 0],
                    ['is_prolanis_urine', '=', 0],
                    ['is_haji', '=', 0]
                ])
                ->delete();

            $paketExtras = $request->input('paket_extra');

            $total_harga_paket_extra = 0;
            $no_ajaib = array();

            if (!empty($paketExtras)) {
                foreach ($paketExtras as $id => $value) {
                    // Pisahkan value yang digabungkan dengan underscore
                    [$id_parameter_paket_extra, $harga_parameter_paket_extra] = explode('_', $value);
                    //total harga paket extra
                    $total_harga_paket_extra += $harga_parameter_paket_extra;

                    $dataSubPaketExtra = ParameterSubPaketExtra::where('id_parameter_paket_extra', $id_parameter_paket_extra)->get();
                    foreach ($dataSubPaketExtra as $subPaket) {
                        $packet_id = $subPaket->id_parameter_paket_klinik;
                        $jenisKlinikId = $this->resolveParameterJenisKlinikIdForPaket($packet_id);
                        if (empty($jenisKlinikId)) {
                            throw new Exception('Jenis parameter klinik tidak ditemukan untuk paket: ' . $packet_id);
                        }

                        $paketExtra = new PermohonanUjiPaketKlinik();
                        $paketExtra->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
                        $paketExtra->parameter_paket_extra = $id_parameter_paket_extra;
                        $paketExtra->parameter_jenis_klinik = $jenisKlinikId;
                        $paketExtra->type_permohonan_uji_paket_klinik = "P";
                        $paketExtra->harga_permohonan_uji_paket_klinik = $subPaket->parameterPaketKlinik->harga_parameter_paket_klinik;
                        $paketExtra->parameter_paket_klinik = $packet_id;
                        $paketExtra->save();

                        array_push($no_ajaib, $packet_id);
                    }
                }
            }

            // store ke permohonan uji paket klinik
            // Catatan: key form jenis_parameters[] = id_parameter_paket_klinik, BUKAN id jenis.
            // Jangan pakai index loop paket (0,1,2...) sebagai parameter_jenis_klinik.
            $price_total = 0;
            $name_permohonan = "";
            if (isset($request->jenis_parameters) && is_array($request->jenis_parameters)) {
                foreach ($request->jenis_parameters as $formKey => $value) {
                    if (!isset($value["pakets"]) || !is_array($value["pakets"])) {
                        continue;
                    }

                    foreach ($value["pakets"] as $packectParameter) {
                        $array_packet = explode("_", $packectParameter);
                        if (count($array_packet) < 2) {
                            continue;
                        }

                        $packet_id = $array_packet[0];
                        $price = $array_packet[1];
                        $jenisKlinikId = $this->resolveParameterJenisKlinikIdForPaket($packet_id, $formKey);
                        if (empty($jenisKlinikId)) {
                            throw new Exception('Jenis parameter klinik tidak ditemukan untuk paket: ' . $packet_id);
                        }

                        $post_paket = new PermohonanUjiPaketKlinik();
                        $post_paket->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
                        $post_paket->parameter_jenis_klinik = $jenisKlinikId;
                        $post_paket->type_permohonan_uji_paket_klinik = "P";
                        $post_paket->parameter_paket_klinik = $packet_id;
                        $post_paket->harga_permohonan_uji_paket_klinik = $price;
                        $post_paket->save();

                        $parameterpaketklinik = ParameterPaketKlinik::where('id_parameter_paket_klinik', $packet_id)
                            ->whereNull('deleted_at')
                            ->first();
                        if ($parameterpaketklinik) {
                            $name_permohonan = $name_permohonan . $parameterpaketklinik->name_parameter_paket_klinik . " ";
                        }
                        array_push($no_ajaib, $packet_id);

                        $price_total = $price_total + (int)$price;
                    }
                }
            }

            $item_permohonan_uji_klinik = PermohonanUjiKlinik2::find($request->permohonan_uji_klinik);
            $item_permohonan_uji_klinik->total_harga_permohonan_uji_klinik = $price_total + $total_harga_paket_extra;

            // Satu Sehat Integration - Create Encounter if needed
            $encounterId = null;
            $encounterDisplay = "Permohonanan Pengujian " . $name_permohonan;
            $practitionerId = null;
            $practitionerName = null;
            
            if (isset($item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat)) {
                if (config('services.satu_sehat.version') == "prd") {
                    $pendaftaran = VerificationActivitySample::where('is_klinik', ($request->permohonan_uji_klinik))
                        ->where('id_verification_activity', 1)
                        ->first();

                    $location_satusehat = SatuSehatLocation::where('name_satusehat_location', "LIKE", '%Administrasi%')
                        ->where('version_satusehat_location', 'prd')
                        ->first();

                    // Normalize nama petugas untuk matching (hilangkan koma dari kedua sisi)
                    $namaPetugas_normalized = str_replace(',', '', $pendaftaran->nama_petugas);
                    $practitioner_satusehat = SatuSehatPractitioner::whereRaw("REPLACE(name_petugas, ',', '') LIKE ?", ['%' . $namaPetugas_normalized . '%'])->first();

                    // Jika tidak ditemukan dengan like, coba cari dari ms_petugas
                    if (!$practitioner_satusehat) {
                        $petugas = Petugas::where('nama', 'like', '%' . $namaPetugas_normalized . '%')->first();
                        if ($petugas && !empty($petugas->code_satu_sehat_practitioner)) {
                            $practitioner_satusehat = SatuSehatPractitioner::where('code_satu_sehat_practitioner', $petugas->code_satu_sehat_practitioner)->first();
                        }
                    }

                    // Pastikan $practitioner_satusehat tidak null sebelum mengakses properti
                    if ($practitioner_satusehat && isset($practitioner_satusehat->code_satu_sehat_practitioner)) {
                        $practitionerId = $practitioner_satusehat->code_satu_sehat_practitioner;
                        $practitionerName = $practitioner_satusehat->name_satu_sehat_practitioner ?? $pendaftaran->name_satu_sehat_practitioner;
                    } else {
                        // Fallback jika practitioner tidak ditemukan
                        Log::warning('Practitioner SatuSehat not found for petugas: ' . $pendaftaran->name_satu_sehat_practitioner);
                        $practitionerId = null;
                        $practitionerName = $pendaftaran->name_satu_sehat_practitioner;
                    }

                  

                    $data = [
                        "resourceType" => "Encounter",
                        "status" => "arrived",
                        "subject" => [
                            "reference" => "Patient/" . $item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat,
                            "display" => $item_permohonan_uji_klinik->pasien->nama_pasien
                        ],
                        "class" => [
                            "system" => "http://terminology.hl7.org/CodeSystem/v3-ActCode",
                            "code" => "PRENC",
                            "display" => "Permohonanan Pengujian " . $name_permohonan
                        ],
                        "participant" => [
                            [
                                "type" => [
                                    [
                                        "coding" => [
                                            [
                                                "system" => "http://terminology.hl7.org/CodeSystem/v3-ParticipationType",
                                                "code" => "ADM",
                                                "display" => "pendaftaran"
                                            ]
                                        ]
                                    ]
                                ],
                                "individual" => [
                                    "reference" => "Practitioner/" . ($practitioner_satusehat && isset($practitioner_satusehat->code_satu_sehat_practitioner) ? $practitioner_satusehat->code_satu_sehat_practitioner : ''),
                                    "display" => ($practitioner_satusehat && isset($practitioner_satusehat->name_satu_sehat_practitioner) ? $practitioner_satusehat->name_satu_sehat_practitioner : $pendaftaran->name_satu_sehat_practitioner)
                                ]
                            ]
                        ],
                        "period" => [
                            "start" => Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->toIso8601String()
                        ],
                        "location" => [
                            [
                                "location" => [
                                    "reference" => "Location/" . $location_satusehat->kode_satusehat_location,
                                    "display" => $location_satusehat->name_satusehat_location
                                ]
                            ]
                        ],
                        "statusHistory" => [
                            [
                                "status" => "arrived",
                                "period" => [
                                    "start" => Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->toIso8601String(),
                                    "end" => Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->toIso8601String()
                                ]
                            ]
                        ],
                        "serviceProvider" => [
                            "reference" => "Organization/" . config('services.satu_sehat.org_id')
                        ],
                        "identifier" => [
                            [
                                "system" => "http://sys-ids.kemkes.go.id/encounter/" . config('services.satu_sehat.org_id'),
                                "value" => $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik
                            ]
                        ]
                    ];

                   
                    
                    $response = $this->satuSehatHelper->post('Encounter', $data);


                    if ($response['status_code'] == '201') {
                        $encounterId = $response['body']["id"];
                        $item_permohonan_uji_klinik->id_satu_sehat_encounter = $response['body']["id"];
                        $item_permohonan_uji_klinik->encounter_json_satu_sehat = json_encode($response['body']);
                        $response = $item_permohonan_uji_klinik->save();
                        $satu_sehat = true;
                    } else {
                        // Meskipun encounter gagal, tetap set $satu_sehat=true jika ada id_pasien_satu_sehat
                        // karena ServiceRequest tetap bisa dibuat tanpa encounter (field optional)
                        $satu_sehat = true;
                        // Log warning untuk tracking
                        Log::warning('Encounter creation failed but continuing with ServiceRequest. Response: ' . json_encode($response));
                    }
                } else {
                    // Jika bukan production, tetap set $satu_sehat jika ada id_pasien_satu_sehat
                    $satu_sehat = isset($item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat);
                }
            } else {
                // Jika tidak ada id_pasien_satu_sehat, set $satu_sehat = null
                $satu_sehat = null;
            }

            $item_permohonan_uji_klinik->save();

            // Loop semua paket dan buat PermohonanUjiParameterKlinik
            foreach ($no_ajaib as $key => $value) {
                $parameterpaketklinik = ParameterPaketKlinik::where('id_parameter_paket_klinik', $value)
                    ->whereNull('deleted_at')
                    ->first();

                $detailPacket = [];
                $packetName = $parameterpaketklinik->name_parameter_paket_klinik;
                
                foreach ($parameterpaketklinik->parameterpaketjenisklinik as $key_parameterpaketjenisklinik => $value_parameterpaketjenisklinik) {
                    #looping satuan parameter dari paket
                    foreach ($value_parameterpaketjenisklinik->parametersatuanpaketklinik as $key_parametersatuanpaketklinik => $value_parametersatuanpaketklinik) {

                        $code = \Smt\Masterweb\Helpers\Smt::pickLoincForContext(
                            $value_parametersatuanpaketklinik->parametersatuanklinik ?? null,
                            $item_permohonan_uji_klinik
                        );
                        $detailPacket[] = [
                            "system" => "http://loinc.org",
                            "code" => $code,
                            "display" => $value_parametersatuanpaketklinik->parametersatuanklinik->name_parameter_satuan_klinik,
                        ];

                        // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
                        $pasien_umur = $item_permohonan_uji_klinik->umurtahun_pasien_permohonan_uji_klinik;
                        $pasien_gender = $item_permohonan_uji_klinik->pasien->gender_pasien;

                        $check_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                            ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                            ->where('is_khusus_baku_mutu', '0')
                            ->leftJoin('ms_library', function ($join) {
                                $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                                    ->whereNull('ms_library.deleted_at')
                                    ->whereNull('tb_baku_mutu.deleted_at');
                            })
                            ->first();

                        // cek dulu apakah ada data dengan data khusus sperti general atau specific
                        if ($check_parameter_by_baku_mutu) {
                            $item_parameter_by_baku_mutu = $check_parameter_by_baku_mutu;
                        } else {
                            $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                                ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                                ->where('is_khusus_baku_mutu', '1')
                                ->where('gender_baku_mutu', $pasien_gender == "male" ? "L" : "P")
                                ->where(function ($query) use ($pasien_umur) {
                                    $query->where('minimal_umur_baku_mutu', '<=', $pasien_umur)
                                        ->where('maksimal_umur_baku_mutu', '>=', $pasien_umur);
                                })
                                ->leftJoin('ms_library', function ($join) {
                                    $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                                        ->whereNull('ms_library.deleted_at')
                                        ->whereNull('tb_baku_mutu.deleted_at');
                                })
                                ->first();
                            if (!isset($item_parameter_by_baku_mutu)) {
                                $item_parameter_by_baku_mutu = BakuMutu::where('parameter_jenis_klinik_id', $value_parameterpaketjenisklinik->parameter_jenis_klinik_id)
                                    ->where('parameter_satuan_klinik_id', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                                    ->where('is_khusus_baku_mutu', '1')
                                    ->leftJoin('ms_library', function ($join) {
                                        $join->on('ms_library.id_library', '=', 'tb_baku_mutu.library_id')
                                            ->whereNull('ms_library.deleted_at')
                                            ->whereNull('tb_baku_mutu.deleted_at');
                                    })
                                    ->where('gender_baku_mutu', $pasien_gender == "male" ? "L" : "P")
                                    ->first();
                            }
                        }

                        $permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('parameter_paket_klinik', $value)
                            ->where('permohonan_uji_klinik', $request->post('permohonan_uji_klinik'))->first();

                        $post_parameter = new PermohonanUjiParameterKlinik();
                        $post_parameter->permohonan_uji_klinik = $request->post('permohonan_uji_klinik');
                        $post_parameter->permohonan_uji_paket_klinik = $permohonan_uji_paket_klinik->id_permohonan_uji_paket_klinik;
                        $post_parameter->parameter_paket_jenis_klinik = $value_parameterpaketjenisklinik->id_parameter_paket_jenis_klinik;
                        $post_parameter->parameter_paket_klinik = $value;
                        $post_parameter->parameter_satuan_klinik = $value_parametersatuanpaketklinik->parameter_satuan_klinik;
                        $post_parameter->jenis_parameter_klinik_id = $value_parameterpaketjenisklinik->parameter_jenis_klinik_id;

                        $post_parameter->method_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->title_library ?? "-";

                        $post_parameter->sort_jenis_klinik = $value_parameterpaketjenisklinik->sort;
                        $post_parameter->sorting_parameter_satuan = $value_parametersatuanpaketklinik->sorting;

                        $post_parameter->harga_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik->harga_satuan_parameter_satuan_klinik;

                        if (isset($item_parameter_by_baku_mutu->unit_id)) {
                            $post_parameter->satuan_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->unit_id;
                            $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = $item_parameter_by_baku_mutu->id_baku_mutu;
                        } else {
                            $post_parameter->satuan_permohonan_uji_parameter_klinik = null;
                            $post_parameter->baku_mutu_permohonan_uji_parameter_klinik = null;
                        }

                        $post_parameter->keterangan_permohonan_uji_parameter_klinik = $value_parametersatuanpaketklinik->parametersatuanklinik->ket_default_parameter_satuan_klinik;

                        $simpan_post_parameter = $post_parameter->save();

                        // jika ada parameter yang memiliki subparameter maka akan diinputkan juga ke permohonan_uji_sub_parameter_klinik
                        $data_parameter_subsatuan = ParameterSubSatuanKlinik::where('parameter_satuan_klinik', $value_parametersatuanpaketklinik->parameter_satuan_klinik)
                            ->get();

                        if (count($data_parameter_subsatuan) > 0) {
                            foreach ($data_parameter_subsatuan as $key_parameter_subsatuan => $value_parameter_subsatuan) {
                                $post_parameter_subsatuan = new PermohonanUjiSubParameterKlinik();
                                $post_parameter_subsatuan->permohonan_uji_parameter_klinik_id = $post_parameter->id_permohonan_uji_parameter_klinik;
                                $post_parameter_subsatuan->parameter_sub_satuan_klinik_id = $value_parameter_subsatuan->id_parameter_sub_satuan_klinik;

                                // mapping baku mutu untuk data parameter yang memiliki subparameter guna memasukkan data baku mutu dan satuan/unitnya
                                if (isset($item_parameter_by_baku_mutu->id_baku_mutu)) {
                                    $item_parameter_subsatuan_by_baku_mutu = BakuMutuDetailParameterKlinik::where('baku_mutu_id', $item_parameter_by_baku_mutu->id_baku_mutu)
                                        ->where('parameter_sub_satuan_baku_mutu_detail_parameter_klinik', $value_parameter_subsatuan->parameter_sub_satuan_klinik_id)
                                        ->first();

                                    $post_parameter_subsatuan->satuan_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->unit_id_baku_mutu_detail_parameter_klinik;
                                    $post_parameter_subsatuan->baku_mutu_permohonan_uji_sub_parameter_klinik = $item_parameter_subsatuan_by_baku_mutu->id_baku_mutu_detail_parameter_klinik;
                                }

                                $simpan_parameter_subsatuan = $post_parameter_subsatuan->save();
                            }
                        }
                    }
                }

                // ServiceRequest
                if (isset($item_permohonan_uji_klinik)) {
                    $encounterId = $item_permohonan_uji_klinik->id_satu_sehat_encounter;
                    $data = json_decode($item_permohonan_uji_klinik->encounter_json_satu_sehat, true);
                    $practitionerId = null;
                    $practitionerName = null;
                    if (isset($data['participant'][0]['individual']['reference']) && isset($data['participant'][0]['individual']['display'])) {
                        $practitionerId = str_replace('Practitioner/', '', $data['participant'][0]['individual']['reference']);
                        $practitionerName = $data['participant'][0]['individual']['display'];
                    }

                    $permohonanUjiPaketKlinik = PermohonanUjiPaketKlinik::query()
                        ->where('permohonan_uji_klinik', '=', $request->post('permohonan_uji_klinik'))
                        ->where('parameter_paket_klinik', '=', $parameterpaketklinik->id_parameter_paket_klinik)
                        ->first();

                    // Skip createServiceRequest if practitionerId is NULL
                    // Tambahkan check: practitionerId TIDAK NULL dan practitionerName TIDAK NULL
                    // Hanya kirim ke SatuSehat jika VERSION_SATUSEHAT == 'prd'
                    if (
                        config('services.satu_sehat.version') == 'prd' &&
                        isset($item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat) &&
                        $item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat != "" &&
                        isset($encounterId) &&
                        isset($practitionerId) && $practitionerId !== null &&
                        isset($practitionerName) && $practitionerName !== null
                    ) {
                        $this->createServiceRequest(
                            $item_permohonan_uji_klinik->noregister_permohonan_uji_klinik,
                            $item_permohonan_uji_klinik->pasien->id_pasien_satu_sehat,
                            $encounterId,
                            $encounterDisplay,
                            $practitionerId,
                            $practitionerName,
                            Carbon::createFromFormat('Y-m-d H:i:s', $item_permohonan_uji_klinik->tglregister_permohonan_uji_klinik)->toIso8601String(),
                            $detailPacket,
                            $packetName,
                            $request->post('permohonan_uji_klinik'),
                            $parameterpaketklinik->id_parameter_paket_klinik
                        );
                    }
                }
            }

            // Commit transaction
            DB::commit();

            // Get permohonan data for payment modal
            $permohonan = PermohonanUjiKlinik2::with('pasien')->find($request->permohonan_uji_klinik);

            // Get biaya_pengambilan_sampel directly from database
            $biaya_pengambilan_sampel_raw = DB::table('tb_permohonan_uji_klinik_2')
                ->where('id_permohonan_uji_klinik', $request->permohonan_uji_klinik)
                ->value('biaya_pengambilan_sampel');

            $biaya_pengambilan_sampel = (int) ($biaya_pengambilan_sampel_raw ?? 0);

            // Calculate total akhir: harga parameter + biaya pengambilan sampel
            $total_harga_parameter = $permohonan->total_harga_permohonan_uji_klinik;
            $total_akhir = $total_harga_parameter + $biaya_pengambilan_sampel;

            $payment_data = [
                'id_permohonan_uji_klinik' => $permohonan->id_permohonan_uji_klinik,
                'nama_pasien' => $permohonan->pasien->nama_pasien ?? '-',
                'alamat_pasien' => $permohonan->pasien->alamat_pasien ?? '-',
                'total_harga_parameter' => $total_harga_parameter,
                'biaya_pengambilan_sampel' => $biaya_pengambilan_sampel,
                'total_harga' => $total_akhir,
                'total_harga_custom' => rupiah($total_akhir),
                'nota_petugas' => auth()->id(),
                'nota_namapetugas' => auth()->user()->name,
            ];

            return response()->json([
                'status' => true,
                'pesan' => "Parameter permohonan uji klinik berhasil disimpan!",
                'show_payment' => false,
                'payment_data' => $payment_data
            ], 200);
        } catch (\Exception $e) {
            // Jika ada kesalahan, rollback transaction
            DB::rollBack();

            // Log error dan kirim response dengan status 500
            Log::error('Error menyimpan parameter permohonan uji klinik (MobileDokter): ' . $e->getMessage(), [
                'permohonan_uji_klinik' => $id_permohonan_uji_klinik,
                'jenis_parameters' => $request->jenis_parameters,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'pesan' => 'System gagal melakukan penyimpanan! ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        } finally {
            // Logout user after the method call to avoid side effects
            Auth::logout();
        }
    }

    /**
     * Create ServiceRequest for Satu Sehat
     */
    private function createServiceRequest($identifier, $patientId, $encounterId, $encounterDisplay, $practitionerId, $practitionerName, $date, $coding, $text, $permohonanUjiKlinikId, $parameterPaketKlinikId)
    {
        $data = [
            "resourceType" => "ServiceRequest",
            "identifier" => [
                [
                    "system" => "http://sys-ids.kemkes.go.id/servicerequest/" . config('services.satu_sehat.org_id'),
                    "value" => $identifier,
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
                "coding" => $coding,
                "text" => $text
            ],
            "subject" => ["reference" => "Patient/" . $patientId],
            "encounter" => [
                "reference" => "Encounter/" . $encounterId,
                "display" => $encounterDisplay,
            ],
            "occurrenceDateTime" => $date,
            "authoredOn" => $date,
            "requester" => [
                "reference" => "Practitioner/" . $practitionerId,
                "display" => $practitionerName,
            ],
            "performer" => [
                ["reference" => "Practitioner/" . $practitionerId, "display" => $practitionerName],
            ]
        ];

        $response = $this->satuSehatHelper->post('ServiceRequest', $data);

        if ($response['status_code'] == '201') {
            $permohonanUjiPaketKlinik = PermohonanUjiPaketKlinik::query()
                ->where('permohonan_uji_klinik', '=', $permohonanUjiKlinikId)
                ->where('parameter_paket_klinik', '=', $parameterPaketKlinikId)
                ->first();
            if (isset($permohonanUjiPaketKlinik)) {
                $permohonanUjiPaketKlinik->id_service_request = $response['body']["id"];
                $permohonanUjiPaketKlinik->response_service_request = json_encode($response['body']);
                $permohonanUjiPaketKlinik->save();
            }
        } else {
            Log::warning('ServiceRequest creation failed. Response: ' . json_encode($response));
            // Don't throw exception, just log the warning
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->session()->forget('mobile_dokter_auth');
        $request->session()->forget('mobile_dokter_user_id');
        $request->session()->forget('mobile_dokter_username');
        $request->session()->forget('mobile_dokter_temp_id');

        return redirect()->route('mobile.dokter.home')
            ->with('success', 'Anda telah logout.');
    }

    /**
     * Check if sample is still in process (verification activities 7, 6, 2, 3, or 4 not done)
     */
    private function checkSampleInProcess($id_permohonan)
    {
        // Get all verification activities for this permohonan
        $verification_activities = VerificationActivitySample::where('is_klinik', $id_permohonan)
            ->where('resampling', 0)
            ->get()
            ->keyBy('id_verification_activity');

        // Check if any of these steps exist (regardless of is_done status): 7, 6, 2, 3
        $step1 = $verification_activities->get(1);
        $step7 = $verification_activities->get(7); // Penerimaan Sampel
        $step6 = $verification_activities->get(6); // Sampling
        $step2 = $verification_activities->get(2); // Pengolah Sampel
        $step3 = $verification_activities->get(3); // Pemeriksa Sampel

        // Check if step 4 exists but is_done = 0
        $step4 = $verification_activities->get(4); // Verifikasi

        if ($step1 && $step1->is_done == 1 && !isset($step7)) {
            return [
                'in_process' => false,
                'message' => ''
            ];
        }

        // If any of steps 7, 6, 2, 3 exist (regardless of is_done), sample is in process
        if ((($step7 && $step7->is_done == 0) || !isset($step7)) || (($step6 && $step6->is_done == 0) || !isset($step6)) || (($step2 && $step2->is_done == 0) || !isset($step2)) || (($step3 && $step3->is_done == 0) || !isset($step3))) {
            $steps = [];
            if ($step7) $steps[] = 'Penerimaan Sampel';
            if ($step6) $steps[] = 'Sampling';
            if ($step2) $steps[] = 'Pengolah Sampel';
            if ($step3) $steps[] = 'Pemeriksa Sampel';
            
            return [
                'in_process' => true,
                'message' => 'Sample masih dalam proses tahapan: ' . implode(', ', $steps) . '. Silakan selesaikan terlebih dahulu sebelum melakukan diagnosis ulang.'
            ];
        }


        // If step 4 exists but is_done = 0, sample is still in process
        if ($step4 && $step4->is_done == 0) {
            return [
                'in_process' => true,
                'message' => 'Sample masih dalam proses tahapan Verifikasi. Silakan selesaikan verifikasi terlebih dahulu sebelum melakukan diagnosis ulang.'
            ];
        }

        return [
            'in_process' => false,
            'message' => ''
        ];
    }

    /**
     * Show validasi page (after verification is done)
     */
    public function validasi(Request $request, $id)
    {
        
        // Check authentication
        if (!$request->session()->get('mobile_dokter_auth', false)) {
            $request->session()->put('mobile_dokter_temp_id', $id);
            return redirect()->route('mobile.dokter.login', ['id' => $id]);
        }

        $permohonan = PermohonanUjiKlinik2::find($id);
        
        if (!$permohonan) {
            return redirect()->route('mobile.dokter.home')
                ->with('error', 'ID Permohonan tidak ditemukan.');
        }

        // Check if step 4 (Verifikasi) is done
        $step4 = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 4)
            ->where('resampling', 0)
            ->first();

        if (!$step4 || $step4->is_done != 1) {
            return redirect()->route('mobile.dokter.home')
                ->with('error', 'Verifikasi belum selesai. Silakan tunggu hingga verifikasi selesai.');
        }

        // Get verification activity for step 4
        $verificationActivitySample = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 4)
            ->first();

        // Get verifikator data
        $verifikator_data = null;
        if ($verificationActivitySample && $verificationActivitySample->nama_petugas) {
            $verifikator_data = [
                'nama' => $verificationActivitySample->nama_petugas,
                'start_date' => $verificationActivitySample->start_date,
                'stop_date' => $verificationActivitySample->stop_date,
            ];
        }

        // Format dates
        $tgl_register = Carbon::createFromFormat('Y-m-d H:i:s', $permohonan->tglregister_permohonan_uji_klinik)->isoFormat('D MMMM Y');
        
        if ($permohonan->tglpengujian_permohonan_uji_klinik !== null && $verificationActivitySample && $verificationActivitySample->start_date) {
            $tgl_pengujian = Carbon::createFromFormat('Y-m-d H:i:s', $verificationActivitySample->start_date)->isoFormat('D MMMM Y HH:mm');
        } else {
            $tgl_pengujian = null;
        }

        if ($permohonan->spesimen_darah_permohonan_uji_klinik !== null) {
            $tgl_spesimen_darah = Carbon::createFromFormat('Y-m-d H:i:s', $permohonan->spesimen_darah_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
        } else {
            $tgl_spesimen_darah = null;
        }

        if ($permohonan->spesimen_urine_permohonan_uji_klinik !== null) {
            $tgl_spesimen_urine = Carbon::createFromFormat('Y-m-d H:i:s', $permohonan->spesimen_urine_permohonan_uji_klinik)->isoFormat('D MMMM Y HH:mm');
        } else {
            $tgl_spesimen_urine = null;
        }

        // Get paket data
        $data_permohonan_uji_paket_klinik = PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id)
            ->whereNull('deleted_at')
            ->get();

        // Get parameters with baku mutu relationship
        $parameters = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id)
            ->whereNull('deleted_at')
            ->with(['parametersatuanklinik', 'unit', 'bakumutu'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Build preview PDF URL
        $previewUrl = route('elits-permohonan-uji-klinik-2.preview-pdf-hasil', $id);

        // Get user from session
        $user_id = $request->session()->get('mobile_dokter_user_id');
        $user = User::find($user_id);
        $user_level = $user->privilege->level ?? null;

        // Get list petugas validator for step 5
        $petugasValidator = $this->getPetugasValidator();

        // Check existing validasi (step 5)
        $existing_validasi = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 5)
            ->where('resampling', 0)
            ->first();

        // Default waktu validasi (current time)
        $default_waktu = Carbon::now()->format('H:i');
        if ($existing_validasi && $existing_validasi->start_date) {
            $default_waktu = Carbon::createFromFormat('Y-m-d H:i:s', $existing_validasi->start_date)->format('H:i');
        }

        // Default nama petugas berdasarkan level
        // Jika user memiliki relasi petugas, gunakan nama + gelar dari petugas
        $default_nama_petugas = $user->name ?? '';
        if ($user && $user->petugas) {
            $petugas = $user->petugas;
            $nama_petugas = $petugas->nama ?? '';
            $gelar = $petugas->gelar ?? '';
            if (!empty($nama_petugas)) {
                $default_nama_petugas = trim($nama_petugas . ' ' . $gelar);
            }
        }
        if ($existing_validasi && $existing_validasi->nama_petugas) {
            $default_nama_petugas = $existing_validasi->nama_petugas;
        } elseif (count($petugasValidator) === 1) {
            // Jika daftar petugas validator hanya satu, pilih otomatis
            $default_nama_petugas = $petugasValidator[0];
        }


        // Force save session
    
        $request->session()->put('mobile_dokter_auth', true);
        $request->session()->put('mobile_dokter_user_id', $user->id);
        $request->session()->put('mobile_dokter_user_name', $user->name);
        $request->session()->put('mobile_dokter_user_level', $user_level);
        $request->session()->save();
        // Get kesimpulan hasil
        $kesimpulan_hasil = $permohonan->kesimpulan_hasil ?? '';

        return view('masterweb::module.mobile.dokter.validasi', [
            'permohonan' => $permohonan,
            'tgl_register' => $tgl_register,
            'tgl_pengujian' => $tgl_pengujian,
            'tgl_spesimen_darah' => $tgl_spesimen_darah,
            'tgl_spesimen_urine' => $tgl_spesimen_urine,
            'data_detail_uji_paket' => $data_permohonan_uji_paket_klinik,
            'parameters' => $parameters,
            'verifikator_data' => $verifikator_data,
            'previewUrl' => $previewUrl,
            'petugasValidator' => $petugasValidator,
            'user_level' => $user_level,
            'user_name' => $user->name ?? '',
            'default_waktu' => $default_waktu,
            'default_nama_petugas' => $default_nama_petugas,
            'existing_validasi' => $existing_validasi,
            'kesimpulan_hasil' => $kesimpulan_hasil,
        ]);
    }

    /**
     * Get petugas validator for step 5 (Validasi) from ms_verification_activities klinik column
     */
    private function getPetugasValidator()
    {
        $petugasValidator = [];

        // Get from ms_verification_activities klinik column for step 5 (id = 5)
        $verificationActivity = VerificationActivity::find(5);
        if ($verificationActivity && !empty($verificationActivity->klinik) && $verificationActivity->klinik !== '-' && $verificationActivity->klinik !== 'NULL') {
            // Parse names from klinik column (comma-separated)
            // Names are stored as "Name1, Name2, Name3" format
            $names = explode(', ', $verificationActivity->klinik);
            foreach ($names as $name) {
                $name = trim($name);
                if (!empty($name) && !in_array($name, $petugasValidator)) {
                    $petugasValidator[] = $name;
                }
            }
        }

        // Sort alphabetically
        sort($petugasValidator);

        return $petugasValidator;
    }

    /**
     * Store validasi (step 5)
     */
    public function storeValidasi(Request $request, $id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_dokter_auth', false)) {
            return response()->json(['status' => false, 'pesan' => 'Anda harus login terlebih dahulu.'], 401);
        }

        // Get user from session
        $user_id = $request->session()->get('mobile_dokter_user_id');
        $user = User::find($user_id);
        
        if (!$user) {
            return response()->json(['status' => false, 'pesan' => 'User tidak ditemukan.'], 401);
        }

        $user_level = $user->privilege->level ?? null;

        // Validasi manual untuk waktu (format HH:mm) sebelum Laravel validation
        $waktu_input = $request->input('waktu', '');
        if (empty($waktu_input)) {
            return response()->json([
                'status' => false,
                'pesan' => 'Waktu validasi harus diisi.'
            ], 422);
        }

        // Validasi format waktu dengan preg_match
        if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $waktu_input)) {
            return response()->json([
                'status' => false,
                'pesan' => 'Format waktu tidak valid. Gunakan format HH:mm (contoh: 22:20)'
            ], 422);
        }

        // Validation rules untuk field lainnya
        $rules = [
            'waktu' => 'required|string',
        ];

        // Jika level bukan DKTR, wajib pilih petugas dari dropdown
        if ($user_level !== 'DKTR') {
            $rules['nama_petugas'] = 'required|string';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Parse waktu inputan (format: HH:mm)
            $waktu_input = $request->input('waktu');
            $tanggal_sekarang = Carbon::now()->format('Y-m-d');
            $start_date = Carbon::createFromFormat('Y-m-d H:i', $tanggal_sekarang . ' ' . $waktu_input)->format('Y-m-d H:i:s');
            $stop_date = $start_date; // start_date dan stop_date sama

            // Tentukan nama_petugas berdasarkan level
            $nama_petugas = '';
            if ($user_level === 'DKTR') {
                // Jika level DKTR, gunakan nama + gelar dari relasi petugas jika ada
                if ($user && $user->petugas) {
                    $petugas = $user->petugas;
                    $nama_petugas = $petugas->nama ?? '';
                    $gelar = $petugas->gelar ?? '';
                    if (!empty($nama_petugas)) {
                        $nama_petugas = trim($nama_petugas . ' ' . $gelar);
                    }
                }
                // Fallback ke user->name jika tidak ada relasi petugas
                if (empty($nama_petugas)) {
                    $nama_petugas = $user->name ?? '';
                }
            } else {
                // Jika level LAB, elits-dev, atau admin, gunakan dari dropdown
                $nama_petugas = $request->input('nama_petugas', '');
                
                // Validasi: jika level bukan DKTR, nama_petugas harus diisi
                if (empty($nama_petugas)) {
                    return response()->json([
                        'status' => false,
                        'pesan' => 'Nama petugas harus diisi.'
                    ], 422);
                }
            }

            // Get kesimpulan hasil
            $kesimpulan_hasil = $request->input('kesimpulan_hasil', '');

            // Cek apakah sudah ada validasi (step 5)
            $validasi = VerificationActivitySample::where('is_klinik', $id)
                ->where('id_verification_activity', 5)
                ->where('resampling', 0)
                ->first();

            if ($validasi) {
                // Update existing
                $validasi->start_date = $start_date;
                $validasi->stop_date = $stop_date;
                $validasi->nama_petugas = $nama_petugas;
                $validasi->is_done = 1;
                $validasi->save();
            } else {
                // Create new
                $validasi = new VerificationActivitySample();
                $validasi->id = Uuid::uuid4()->toString();
                $validasi->is_klinik = $id;
                $validasi->id_verification_activity = 5;
                $validasi->start_date = $start_date;
                $validasi->stop_date = $stop_date;
                $validasi->nama_petugas = $nama_petugas;
                $validasi->is_done = 1;
                $validasi->resampling = 0;
                $validasi->save();
            }

            // Update kesimpulan hasil di permohonan
            $permohonan = PermohonanUjiKlinik2::find($id);
            if ($permohonan) {
                $permohonan->kesimpulan_hasil = $kesimpulan_hasil;
                $permohonan->save();
            }

            DB::commit();

            // Assign Nomer Lab klinik saat step 5 (validasi) is_done = 1
            try {
                NomerLabSequence::assignKlinik($id);
            } catch (\Throwable $e) {
                Log::error('Gagal assign nomer_lab klinik (MobileDokter): ' . $e->getMessage(), ['id' => $id]);
            }

            // Kirim PDF hasil via WhatsApp jika flag kirim_hasil_whatsapp aktif
            // (sama seperti validasi desktop di LaboratoriumPermohonanUjiKlinikManagement2)
            try {
                $dataPermohonan = PermohonanUjiKlinik2::find($id);
                $pasien = $dataPermohonan
                    ? Pasien::find($dataPermohonan->pasien_permohonan_uji_klinik)
                    : null;

                if (
                    $dataPermohonan
                    && $pasien
                    && !empty($pasien->phone_pasien)
                    && (int) ($dataPermohonan->kirim_hasil_whatsapp ?? 0) === 1
                ) {
                    app(LaboratoriumPermohonanUjiKlinikManagement2::class)
                        ->sendPdfHasilToPasien($id, $pasien);
                } else {
                    Log::info('Skip kirim PDF WA setelah validasi mobile', [
                        'permohonan_id' => $id,
                        'kirim_hasil_whatsapp' => $dataPermohonan->kirim_hasil_whatsapp ?? null,
                        'has_phone' => !empty(optional($pasien)->phone_pasien),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Kirim PDF WA setelah validasi mobile gagal: ' . $e->getMessage(), [
                    'permohonan_id' => $id,
                ]);
            }

            return response()->json([
                'status' => true,
                'pesan' => 'Validasi berhasil disimpan!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error menyimpan validasi (MobileDokter): ' . $e->getMessage(), [
                'permohonan_uji_klinik' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'pesan' => 'Terjadi kesalahan saat menyimpan validasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve id_parameter_jenis_klinik dari id paket.
     * Form jenis_parameters[] memakai id_parameter_paket_klinik sebagai key, bukan id jenis.
     */
    private function resolveParameterJenisKlinikIdForPaket($packetId, $formKey = null)
    {
        if (!empty($formKey) && ParameterJenisKlinik::where('id_parameter_jenis_klinik', $formKey)->exists()) {
            return $formKey;
        }

        $bridge = ParameterPaketJenisKlinik::where('parameter_paket_klinik_id', $packetId)
            ->orderBy('sort')
            ->first();

        return $bridge ? $bridge->parameter_jenis_klinik_id : null;
    }

    /**
     * Check status and redirect to appropriate page
     */
    private function checkAndRedirect(Request $request, $id)
    {
        $permohonan = PermohonanUjiKlinik2::find($id);
        if (!$permohonan) {
            return redirect()->route('mobile.dokter.home')
                ->with('error', 'ID Permohonan tidak ditemukan.');
        }

        // Check if step 4 (Verifikasi) is done - if yes, redirect to validasi
        $step4 = VerificationActivitySample::where('is_klinik', $id)
            ->where('id_verification_activity', 4)
            ->where('resampling', 0)
            ->first();

        if ($step4 && $step4->is_done == 1) {
            return redirect()->route('mobile.dokter.validasi', ['id' => $id]);
        }

        // Check if already has parameters
        $hasParameters = PermohonanUjiParameterKlinik::where('permohonan_uji_klinik', $id)->exists();
        
        // Check verification activity step 1 (diagnosis) - id_verification_activity = 1, is_done = 0
        // For klinik, use is_klinik field instead of id_sample
        $verificationActivity = VerificationActivitySample::where('id_verification_activity', 1)
            ->where('is_klinik', $id)
            ->where('is_done', 0)
            ->first();

        // If no parameters and step 1 not done (or doesn't exist), go to diagnosis
        if (!$hasParameters) {
            // Check if diagnosis is empty
            if (empty($permohonan->diagnosa_permohonan_uji_klinik)) {
                return redirect()->route('mobile.dokter.diagnosis', ['id' => $id]);
            } else {
                // Diagnosis exists but no parameters, go to create parameter
                return redirect()->route('mobile.dokter.create-parameter', ['id' => $id]);
            }
        }

        // If has parameters, redirect to success or home
        return redirect()->route('mobile.dokter.home')
            ->with('success', 'Permohonan sudah memiliki diagnosis dan parameter.');
    }
}

