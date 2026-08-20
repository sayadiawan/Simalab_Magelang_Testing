<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smt\Masterweb\Models\User;
use Smt\Masterweb\Models\PermohonanUji;
use Smt\Masterweb\Models\Sample;
use Smt\Masterweb\Models\SampleType;
use Smt\Masterweb\Models\Laboratorium;
use Smt\Masterweb\Models\LaboratoriumMethod;
use Smt\Masterweb\Models\Method;
use Smt\Masterweb\Models\Packet;
use Smt\Masterweb\Models\Program;
use Smt\Masterweb\Models\LabNum;
use Smt\Masterweb\Models\StartNum;
use Smt\Masterweb\Models\SampleMethod;
use Smt\Masterweb\Models\PenerimaanSample;
use Smt\Masterweb\Models\VerificationActivitySample;
use Smt\Masterweb\Models\Petugas;
use Smt\Masterweb\Models\VerificationActivity;
use Smt\Masterweb\Models\SampleDraft;
use Smt\Masterweb\Models\SampleMethodDraft;
use Smt\Masterweb\Models\GlobalLabSequence;
use Smt\Masterweb\Models\GlobalLabSequenceDetail;
use Smt\Masterweb\Helpers\BakuMutuSampletypeHelper;
use Smt\Masterweb\Models\MethodSampleTypePrice;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class MobileSamplingController extends Controller
{
    /**
     * Mobile sampling home: scan or input ID
     */
    public function home(Request $request)
    {
        $isAuthenticated = $request->session()->get('mobile_sampling_auth', false);
        return view('masterweb::module.mobile.sampling.index', [
            'is_authenticated' => $isAuthenticated
        ]);
    }

    /**
     * Get back URL based on user level
     */
    private function getBackUrl($userLevel)
    {
        // If admin, return to permohonan uji
        if (in_array($userLevel, ['admin', 'elits-dev'])) {
            return route('elits-permohonan-uji.index');
        }
        
        // If ANLS, KLAB, LAB, ALAB, return to verifikasi lists
        if (in_array($userLevel, ['ANLS', 'KLAB', 'LAB', 'ALAB'])) {
            return route('elits-permohonan-uji-klinik.verifikasi-lists');
        }
        
        // Default: return to permohonan uji
        return route('elits-permohonan-uji.index');
    }

    /**
     * Show mobile sampling form with authentication
     */
    public function index(Request $request, $id)
    {
        // Check if already authenticated
        if ($request->session()->get('mobile_sampling_auth', false)) {
            return redirect()->route('mobile.sampling.draftList', ['id' => $id]);
        }

        // Get permohonan uji data for display
        $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)
            ->with('customer')
            ->first();

        if (!$permohonan_uji) {
            return view('masterweb::module.mobile.sampling.error', [
                'message' => 'Data permohonan uji tidak ditemukan'
            ]);
        }

        // Get user level from session (if available)
        $userLevel = $request->session()->get('mobile_sampling_user_level');
        $backUrl = $this->getBackUrl($userLevel);

        return view('masterweb::module.mobile.sampling.login', compact('permohonan_uji', 'backUrl'));
    }

    /**
     * Handle manual input of ID permohonan
     */
    public function inputId(Request $request)
    {
        $request->validate([
            'id_permohonan' => 'required|string'
        ]);

        $id = trim($request->id_permohonan);
        $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)->first();
        if (!$permohonan_uji) {
            return redirect()->route('mobile.sampling.home')
                ->with('error', 'Data permohonan uji tidak ditemukan.');
        }

        // Persist id for convenience
        $request->session()->put('mobile_sampling_id', $id);

        if ($request->session()->get('mobile_sampling_auth', false)) {
            return redirect()->route('mobile.sampling.draftList', ['id' => $id]);
        }

        return redirect()->route('mobile.sampling.index', ['id' => $id]);
    }

    /**
     * Process QR scan
     */
    public function scan(Request $request, $id)
    {
        $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)->first();
        if (!$permohonan_uji) {
            return view('masterweb::module.mobile.sampling.error', [
                'message' => 'Data permohonan uji tidak ditemukan'
            ]);
        }

        $request->session()->put('mobile_sampling_id', $id);
        if ($request->session()->get('mobile_sampling_auth', false)) {
            return redirect()->route('mobile.sampling.draftList', ['id' => $id]);
        }
        return redirect()->route('mobile.sampling.index', ['id' => $id]);
    }

    /**
     * Process mobile sampling login
     */
    public function login(Request $request, $id)
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

        // Try to find user with privilege
        $user = User::where('username', $request->username)
            ->with(['getlevel', 'laboratorium'])
            ->first();



        if ($user && Hash::check($request->password, $user->password)) {
            // Check access rights
            $userLevel = $user->getlevel->level ?? null;

            $isAdmin = in_array($userLevel, ['elits-dev','ALAB','LAB','SOLAB','ANLS', 'admin']);
            $isSOLAB = $user->level === 'd3090b8d-8951-4f5b-97e5-4dedf6935da7';


            // Check if user has authorized access
            if (!$isAdmin && !$isSOLAB) {
                return redirect()->back()
                    ->withInput($request->only('username'))
                    ->with('error', 'Akses ditolak! Hanya petugas pengambil sample lab atau admin yang dapat mengakses form ini.');
            }


            // For SOLAB, check if user has lab assignment (Kimia or Mikrobiologi)
            if ($isSOLAB) {
                $labName = strtolower($user->laboratorium->nama_laboratorium ?? '');
                $hasValidLab = in_array($labName, ['kimia', 'mikrobiologi']);


                if (!$hasValidLab) {
                    return redirect()->back()
                        ->withInput($request->only('username'))
                        ->with('error', 'Akses ditolak! Anda harus terdaftar di laboratorium Kimia atau Mikrobiologi.');
                }
            }


            // Set mobile sampling session
            $request->session()->put([
                'mobile_sampling_auth' => true,
                'mobile_sampling_user_id' => $user->id,
                'mobile_sampling_user_name' => $user->name,
                'mobile_sampling_user_username' => $user->username,
                'mobile_sampling_user_level' => $userLevel,
            ]);


            // Force save session
            $request->session()->save();

            return redirect()->route('mobile.sampling.draftList', ['id' => $id])
                ->with('success', 'Login berhasil!');
        }

        return redirect()->back()
            ->withInput($request->only('username'))
            ->with('error', 'Username atau password salah!');
    }

    /**
     * Show list of sample drafts grouped by draft_group_id
     */
    public function draftList(Request $request, $id)
    {
        // Check authentication
        $hasAuth = $request->session()->get('mobile_sampling_auth', false);
        
        if (!$hasAuth) {
            // Log session state for debugging
            \Log::warning('Mobile Sampling DraftList: Session not found', [
                'session_id' => $request->session()->getId(),
                'all_session_keys' => array_keys($request->session()->all()),
                'request_id' => $id
            ]);
            
            return redirect()->route('mobile.sampling.index', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu');
        }

        // Get permohonan uji data
        $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)
            ->with('customer')
            ->first();

        if (!$permohonan_uji) {
            return view('masterweb::module.mobile.sampling.error', [
                'message' => 'Data permohonan uji tidak ditemukan'
            ]);
        }

        // Get user level from session
        $userLevel = $request->session()->get('mobile_sampling_user_level');
        
        // For mobile sampling, back URL should go to mobile sampling home, not verifikasi lists
        $backUrl = route('mobile.sampling.home');

        // Get all sample drafts for this permohonan_uji with status 'draft'
        $drafts = SampleDraft::where('permohonan_uji_id', $id)
            ->where('status', 'draft')
            ->with(['sampletype', 'packet', 'program', 'samplemethoddraft.method', 'samplemethoddraft.laboratorium', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group drafts by draft_group_id
        $groupedDrafts = $drafts->groupBy('draft_group_id');

        // Show all drafts, including single drafts (removed filter that excluded single drafts)


        // Get pengambil sampel from VerificationActivity id = 6
        $verificationActivity = VerificationActivity::where('id', 6)->first();
        $pengambil_sampel_list = [];
        
        if ($verificationActivity) {
            // Gabungkan semua petugas: register, kimia, dan mikro
            $petugas_register = !empty($verificationActivity->register) && $verificationActivity->register !== '-' && $verificationActivity->register !== 'NULL'
                ? explode(', ', $verificationActivity->register) : [];
            $petugas_kimia = !empty($verificationActivity->kimia) && $verificationActivity->kimia !== '-' && $verificationActivity->kimia !== 'NULL'
                ? explode(', ', $verificationActivity->kimia) : [];
            $petugas_mikro = !empty($verificationActivity->mikro) && $verificationActivity->mikro !== '-' && $verificationActivity->mikro !== 'NULL'
                ? explode(', ', $verificationActivity->mikro) : [];

            // Gabungkan dan hilangkan duplikat
            $pengambil_sampel_list = array_unique(
                array_merge($petugas_register, $petugas_kimia, $petugas_mikro)
            );
            
            // Hilangkan string kosong dan trim
            $pengambil_sampel_list = array_filter($pengambil_sampel_list, function ($value) {
                return trim($value) !== '';
            });
            
            // Trim semua nilai
            $pengambil_sampel_list = array_map('trim', $pengambil_sampel_list);
            sort($pengambil_sampel_list);
        }

        return view('masterweb::module.mobile.sampling.draft-list', compact(
            'permohonan_uji',
            'groupedDrafts',
            'pengambil_sampel_list',
            'backUrl'
        ));
    }

    /**
     * Finish all drafts - convert to samples and delete drafts
     */
    public function finishDrafts(Request $request, $id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_auth', false)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'pesan' => 'Silakan login terlebih dahulu'
                ], 401, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
            }
            return redirect()->route('mobile.sampling.index', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu');
        }

        try {
            DB::beginTransaction();

            // Validate request
            $validated = $request->validate([
                'nama_pelanggan' => 'required|string|max:255',
                'jabatan_pelanggan' => 'required|string|max:255',
                'nip_pelanggan' => 'nullable|string|max:255',
            ]);

            // Get permohonan uji
            $permohonan_uji = PermohonanUji::findOrFail($id);

            // Note: nama_pelanggan, jabatan_pelanggan, nip_pelanggan tidak ada di tb_permohonan_uji
            // Data ini akan disimpan di draft atau diabaikan, sesuai kebutuhan

            // Get all drafts for this permohonan uji with status 'draft'
            $drafts = SampleDraft::where('permohonan_uji_id', $id)
                ->where('status', 'draft')
                ->with(['samplemethoddraft', 'sampletype'])
                ->get();

            if ($drafts->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'pesan' => 'Tidak ada draft yang perlu diselesaikan'
                ], 400, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
            }

            // Get laboratoriums
            $laboratoriums = Laboratorium::where('kode_laboratorium', '!=', 'KLI')->get();

            $total_samples_created = 0;

            // Convert each draft to sample(s)
            foreach ($drafts as $draft) {
                // Get unique lab IDs from samplemethoddraft
                $lab_ids = [];
                foreach ($draft->samplemethoddraft as $method_draft) {
                    if ($method_draft->laboratorium_id) {
                        $lab_ids[] = $method_draft->laboratorium_id;
                    }
                }
                $lab_ids = array_unique($lab_ids);
                sort($lab_ids);

                if (empty($lab_ids)) {
                    continue; // Skip if no methods
                }

                // Get sample type code
                $sample_type = SampleType::find($draft->typesample_samples);
                $sample_type_code = $sample_type ? $sample_type->code_sample_type : 'XX';

                // Check if makanan/minuman
                $is_makanan = false;
                if ($sample_type && str_contains($sample_type->name_sample_type, "Makanan/Minuman/Lainnya")) {
                    $is_makanan = true;
                }

                // Create sample for each lab
                foreach ($lab_ids as $lab_id) {
                    $lab = $laboratoriums->where('id_laboratorium', $lab_id)->first();
                    if (!$lab) continue;

                    $current_lab_name = strtolower($lab->nama_laboratorium);
                    if ($current_lab_name == 'mikrobiologi') $current_lab_name = 'mikro';

                    // Get lab code
                    $lab_code = $current_lab_name === 'kimia' ? '01' : '02';

                    // Get start number
                    $start_num = StartNum::join('ms_laboratorium', function ($join) {
                        $join->on('ms_laboratorium.kode_laboratorium', '=', 'ms_start_number.code_lab_start_number')
                            ->whereNull('ms_laboratorium.deleted_at')
                            ->whereNull('ms_start_number.deleted_at');
                    })->where('id_laboratorium', $lab_id)->first();

                    if ($is_makanan) {
                        $start_num = StartNum::where('code_lab_start_number', 'MAK-MIN')->first();
                    }

                    // Generate lab number — urutan global (satu pool dengan klinik), atomic via getNextNumber
                    $current_year = (int) date('Y');

                    $current_global = GlobalLabSequence::getCurrentNumber($current_year);
                    if ($current_global == 0 && $start_num && $current_year === (int) ($start_num->year_start_number ?? $current_year)) {
                        GlobalLabSequence::raiseLastNumberToAtLeast((int) ($start_num->count_start_number ?? 0), $current_year);
                    }

                    $lab_num_urutan = GlobalLabSequence::getNextNumber($current_year, $lab_id, 'lab', null);
                    $sequence_detail_new = GlobalLabSequenceDetail::where('year', $current_year)
                        ->where('sequence_number', $lab_num_urutan)
                        ->where('lab_id', $lab_id)
                        ->where('lab_type', 'lab')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $code_number = str_pad((int) $lab_num_urutan, 4, '0', STR_PAD_LEFT);
                    $code_year = Carbon::now()->format('Y');
                    $code_sample = $sample_type_code . '.' . $lab_code . '/' . $code_number . '/' . $code_year;

                    $current_sample_urutan = (int) $lab_num_urutan;

                    // Calculate cost for this lab only
                    $lab_cost = 0;
                    foreach ($draft->samplemethoddraft as $method_draft) {
                        if ($method_draft->laboratorium_id == $lab_id) {
                            $lab_cost += (float)($method_draft->price_method ?? 0);
                        }
                    }

                    // Create sample (save first before creating LabNum for foreign key constraint)
                    $sample = Sample::create([
                        'id_samples' => Uuid::uuid4()->toString(),
                        'permohonan_uji_id' => $draft->permohonan_uji_id,
                        'group_id' => $draft->draft_group_id, // Copy draft_group_id to group_id
                        'typesample_samples' => $draft->typesample_samples,
                        'codesample_samples' => $code_sample,
                        'count_id' => $current_sample_urutan,
                        'pengambil_sampel' => $draft->pengambil_sampel,
                        'packet_id' => $draft->packet_id,
                        'datesampling_samples' => $draft->datesampling_samples,
                        'date_sending' => $draft->date_sending,
                        'titik_pengambilan' => $draft->titik_pengambilan,
                        'cost_samples' => $lab_cost > 0 ? $lab_cost : $draft->cost_samples,
                        'note_samples' => $draft->note_samples,
                        'program_samples' => $draft->program_samples,
                        'is_sampling' => 1,
                        'cost_sampling_samples' => $draft->cost_sampling_samples ?? 20000,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Verify sample was saved
                    if (!$sample->id_samples) {
                        throw new \Exception('Failed to save Sample record before creating LabNum');
                    }

                    // Store methods for this lab from samplemethoddraft
                    foreach ($draft->samplemethoddraft as $method_draft) {
                        if ($method_draft->laboratorium_id == $lab_id) {
                            SampleMethod::create([
                                'id_sample_method' => Uuid::uuid4()->toString(),
                                'sample_id' => $sample->id_samples,
                                'method_id' => $method_draft->method_id,
                                'laboratorium_id' => $method_draft->laboratorium_id,
                                'price_method' => (int)($method_draft->price_method ?? 0),
                                'is_sub' => $method_draft->is_sub ?? 0,
                            ]);
                        }
                    }

                    // Create LabNum using create() method (UUID will be auto-generated by Uuid trait)
                    try {
                        $lab_num = LabNum::create([
                            'sample_id' => $sample->id_samples,
                            'sample_type_id' => $sample->typesample_samples,
                            'lab_id' => $lab_id,
                            'is_makanan' => $is_makanan ? 1 : 0,
                            'mount_lab_num' => Carbon::now()->format('m'),
                            'year_lab_num' => Carbon::now()->format('Y'),
                            'permohonan_uji_id' => $sample->permohonan_uji_id,
                            'lab_number' => $lab_num_urutan,
                        ]);
                        
                        // Verify LabNum was created
                        if (!$lab_num || !$lab_num->id_lab_num) {
                            throw new \Exception('Failed to create LabNum record - no ID returned');
                        }
                        
                        // Refresh to ensure we have the latest data
                        $lab_num->refresh();
                        
                    } catch (\Exception $e) {
                        // Log detailed error information
                        Log::error('Failed to create LabNum', [
                            'error' => $e->getMessage(),
                            'sample_id' => $sample->id_samples ?? 'N/A',
                            'sample_type_id' => $sample->typesample_samples ?? 'N/A',
                            'lab_id' => $lab_id ?? 'N/A',
                            'lab_number' => $lab_num_urutan ?? 'N/A',
                            'trace' => $e->getTraceAsString()
                        ]);
                        throw new \Exception('Failed to create LabNum record: ' . $e->getMessage());
                    }

                    // Update reference_id in GlobalLabSequenceDetail (use the one we just created)
                    if ($sequence_detail_new) {
                        $sequence_detail_new->reference_id = $lab_num->id_lab_num;
                        $sequence_detail_new->save();
                        
                        // Verify update was successful
                        if ($sequence_detail_new->reference_id !== $lab_num->id_lab_num) {
                            throw new \Exception('Failed to update GlobalLabSequenceDetail reference_id');
                        }
                    } else {
                        // Log warning if sequence_detail_new is not found
                        Log::warning('GlobalLabSequenceDetail not found for sequence_number: ' . $lab_num_urutan . ', lab_id: ' . $lab_id);
                    }

                    // Create PenerimaanSample
                    PenerimaanSample::create([
                        'id_sample_penerimaan' => Uuid::uuid4()->toString(),
                        'sample_id' => $sample->id_samples,
                        'penerimaan_sample_date' => $draft->date_sending,
                        'kelayakan_tempat_kemasan' => 'LAYAK',
                        'kelayakan_berat_vol' => 'LAYAK',
                    ]);

                    // Create VerificationActivitySample for Pendaftaran/Registrasi
                    $nama_petugas_pendaftaran = $permohonan_uji->petugas_penerima ?? $draft->pengambil_sampel;
                    if (empty($nama_petugas_pendaftaran)) {
                        $nama_petugas_pendaftaran = $request->session()->get('mobile_sampling_user_name', 'Petugas');
                    }

                    VerificationActivitySample::create([
                        'id' => Uuid::uuid4()->toString(),
                        'id_verification_activity' => 1,
                        'id_sample' => $sample->id_samples,
                        'start_date' => $permohonan_uji->date_permohonan_uji,
                        'stop_date' => $permohonan_uji->date_permohonan_uji,
                        'nama_petugas' => $nama_petugas_pendaftaran,
                        'is_done' => true,
                    ]);

                    // Create VerificationActivitySample for Pengambilan Sample
                    $nama_petugas_pengambil = $draft->pengambil_sampel;
                    if (empty($nama_petugas_pengambil)) {
                        $nama_petugas_pengambil = $request->session()->get('mobile_sampling_user_name', 'Petugas');
                    }

                    VerificationActivitySample::create([
                        'id' => Uuid::uuid4()->toString(),
                        'id_verification_activity' => 6,
                        'id_sample' => $sample->id_samples,
                        'start_date' => $draft->date_sending,
                        'stop_date' => $draft->date_sending,
                        'nama_petugas' => $nama_petugas_pengambil,
                        'is_done' => true,
                    ]);

                    $total_samples_created++;
                }
            }

            // Update total harga permohonan uji
            $total_cost = Sample::where('permohonan_uji_id', $id)->sum('cost_samples');
            $total_sampling_cost = Sample::where('permohonan_uji_id', $id)
                ->where('is_sampling', 1)
                ->sum('cost_sampling_samples');
            $permohonan_uji->update(['total_harga' => $total_cost + $total_sampling_cost]);

            // Delete all drafts after successful conversion
            foreach ($drafts as $draft) {
                // Delete samplemethoddraft first
                $draft->samplemethoddraft()->delete();
                // Delete draft
                $draft->delete();
            }

            DB::commit();

            // Simpan data pelanggan di session untuk digunakan di halaman signature
            $request->session()->put('mobile_sampling_customer_data', [
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'jabatan_pelanggan' => $validated['jabatan_pelanggan'],
                'nip_pelanggan' => $validated['nip_pelanggan'] ?? null,
            ]);

            return response()->json([
                'status' => true,
                'success' => true,
                'pesan' => "Berhasil! {$total_samples_created} sample telah dibuat dari draft.",
                'total_created' => $total_samples_created,
                'redirect' => route('mobile.sampling.signature', ['id' => $id])
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mobile Sampling Finish Drafts Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            $errorMessage = mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8');

            return response()->json([
                'status' => false,
                'success' => false,
                'pesan' => 'Terjadi kesalahan: ' . $errorMessage
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
        }
    }

    /**
     * Edit a single draft
     */
    public function editDraft(Request $request, $id, $draft_id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_auth', false)) {
            return redirect()->route('mobile.sampling.index', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu');
        }

        $draft = SampleDraft::with([
            'sampletype',
            'packet',
            'program',
            'samplemethoddraft.method',
            'samplemethoddraft.laboratorium'
        ])->where('id_sample_draft', $draft_id)
            ->where('permohonan_uji_id', $id)
            ->where('status', 'draft')
            ->first();

        if (!$draft) {
            return redirect()->route('mobile.sampling.draftList', ['id' => $id])
                ->with('error', 'Draft tidak ditemukan atau sudah dikonfirmasi');
        }

        $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)
            ->with('customer')
            ->first();

        if (!$permohonan_uji) {
            return view('masterweb::module.mobile.sampling.error', [
                'message' => 'Data permohonan uji tidak ditemukan'
            ]);
        }

        // Get selected method IDs from samplemethoddraft
        $selected_methods = $draft->samplemethoddraft->pluck('method_id')->toArray();

        // Get sample types
        $sample_types = SampleType::orderBy('created_at')->get();

        // Get laboratories (exclude Klinik)
        $laboratoriums = Laboratorium::where('kode_laboratorium', '!=', 'KLI')->get();

        // Build data_methods structure
        $data_methods = array();
        foreach ($laboratoriums as $laboratorium) {
            array_push(
                $data_methods,
                (object) array(
                    'name' => $laboratorium->nama_laboratorium,
                    'id_lab' => $laboratorium->id_laboratorium,
                    'method' => array()
                )
            );
        }

        // Get methods for each laboratory
        $i = 0;
        foreach ($data_methods as $data_method) {
            $laboratoriummethods = LaboratoriumMethod::where('laboratorium_id', '=', $data_method->id_lab)
                ->orderBy('ms_method.created_at')
                ->join('ms_method', function ($join) {
                    $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
                        ->whereNull('ms_method.deleted_at')
                        ->whereNull('tb_laboratorium_method.deleted_at');
                })
                ->select('tb_laboratorium_method.*', 'ms_method.*')
                ->get();

            foreach ($laboratoriummethods as $laboratoriummethod) {
                $sampletypes_with_baku_mutu = BakuMutuSampletypeHelper::sampletypeIdsWithBakuMutu(
                    $laboratoriummethod->id_method,
                    $data_method->id_lab
                );

                array_push(
                    $data_methods[$i]->method,
                    (object) array(
                        'name_method' => $laboratoriummethod->params_method,
                        'id_method' => $laboratoriummethod->id_method,
                        'price_method' => $laboratoriummethod->price_total_method,
                        'baku_mutu_sampletypes' => $sampletypes_with_baku_mutu
                    )
                );
            }

            $i++;
        }

        $data_methods = MethodSampleTypePrice::attachPricesToDataMethods($data_methods);

        // Get programs
        $programs = Program::orderBy('created_at')->get();

        // Get user info from session
        $petugas_name = $request->session()->get('mobile_sampling_user_name', 'Petugas');

        // Get petugas from Petugas model based on role and lab
        $petugas_list = $this->getPetugasSampling();

        // Get pengambil sampel from VerificationActivity id = 6
        $verificationActivity = VerificationActivity::where('id', 6)->first();
        $pengambil_sampel_list = [];
        
        if ($verificationActivity) {
            // Gabungkan semua petugas: register, kimia, dan mikro
            $petugas_register = !empty($verificationActivity->register) && $verificationActivity->register !== '-' && $verificationActivity->register !== 'NULL'
                ? explode(', ', $verificationActivity->register) : [];
            $petugas_kimia = !empty($verificationActivity->kimia) && $verificationActivity->kimia !== '-' && $verificationActivity->kimia !== 'NULL'
                ? explode(', ', $verificationActivity->kimia) : [];
            $petugas_mikro = !empty($verificationActivity->mikro) && $verificationActivity->mikro !== '-' && $verificationActivity->mikro !== 'NULL'
                ? explode(', ', $verificationActivity->mikro) : [];

            // Gabungkan dan hilangkan duplikat
            $pengambil_sampel_list = array_unique(
                array_merge($petugas_register, $petugas_kimia, $petugas_mikro)
            );
            
            // Hilangkan string kosong dan trim
            $pengambil_sampel_list = array_filter($pengambil_sampel_list, function ($value) {
                return trim($value) !== '';
            });
            
            // Trim semua nilai
            $pengambil_sampel_list = array_map('trim', $pengambil_sampel_list);
            sort($pengambil_sampel_list);
        }

        // Parse existing pengambil_sampel (bisa JSON array atau string)
        $selected_pengambil_sampel = [];
        if ($draft->pengambil_sampel) {
            // Try to decode as JSON first
            $decoded = json_decode($draft->pengambil_sampel, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $selected_pengambil_sampel = $decoded;
            } else {
                // If not JSON, treat as comma-separated string
                $selected_pengambil_sampel = array_map('trim', explode(',', $draft->pengambil_sampel));
                $selected_pengambil_sampel = array_filter($selected_pengambil_sampel);
            }
        }

        return view('masterweb::module.mobile.sampling.draft-edit', compact(
            'permohonan_uji',
            'draft',
            'sample_types',
            'data_methods',
            'programs',
            'petugas_name',
            'petugas_list',
            'selected_methods',
            'pengambil_sampel_list',
            'selected_pengambil_sampel',
            'id',
            'draft_id'
        ));
    }

    /**
     * Verify draft (update titik_pengambilan and pengambil_sampel only)
     */
    public function verifyDraft(Request $request, $id, $draft_id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_auth', false)) {
            return response()->json([
                'status' => false,
                'pesan' => 'Silakan login terlebih dahulu'
            ], 401);
        }

        try {
            DB::beginTransaction();

            $draft = SampleDraft::where('id_sample_draft', $draft_id)
                ->where('permohonan_uji_id', $id)
                ->where('status', 'draft')
                ->first();

            if (!$draft) {
                return response()->json([
                    'status' => false,
                    'pesan' => 'Draft tidak ditemukan atau sudah dikonfirmasi'
                ], 404);
            }

            // Validate request
            $validated = $request->validate([
                'titik_pengambilan' => 'nullable|string|max:500',
                'pengambil_sampel' => 'nullable|array',
                'pengambil_sampel.*' => 'nullable|string',
            ]);

            // Handle pengambil_sampel (multiple selection)
            $pengambil_sampel_value = null;
            if ($request->filled('pengambil_sampel') && is_array($request->pengambil_sampel)) {
                // Filter empty values and trim
                $pengambil_sampel_array = array_filter(array_map('trim', $request->pengambil_sampel));
                if (!empty($pengambil_sampel_array)) {
                    // Store as JSON array
                    $pengambil_sampel_value = json_encode(array_values($pengambil_sampel_array));
                }
            }

            // Update draft fields (only titik_pengambilan and pengambil_sampel)
            $draft->update([
                'titik_pengambilan' => $request->titik_pengambilan ?? $draft->titik_pengambilan,
                'pengambil_sampel' => $pengambil_sampel_value ?? $draft->pengambil_sampel,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'pesan' => 'Verifikasi draft berhasil disimpan!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'pesan' => 'Gagal menyimpan verifikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update draft sample
     */
    public function updateDraft(Request $request, $id, $draft_id)
    {
        // Check if this is an AJAX request
        $isAjax = $request->expectsJson() || 
                  $request->ajax() || 
                  $request->wantsJson() ||
                  $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                  ($request->header('Accept') && strpos($request->header('Accept'), 'application/json') !== false);
        
        // Get session BEFORE any operations - DO NOT regenerate or invalidate
        $session = $request->session();
        
        // Check authentication FIRST - before any logging or operations
        $hasAuth = $session->get('mobile_sampling_auth', false);
        
        // If session not found, try alternative check
        if (!$hasAuth) {
            // Try to get from all session data
            $allSession = $session->all();
            $hasAuth = isset($allSession['mobile_sampling_auth']) && $allSession['mobile_sampling_auth'] === true;
        }
        
        // Log session state AFTER checking auth (for debugging only)
        if (!$hasAuth) {
            \Log::warning('Mobile Sampling UpdateDraft: Session not found', [
                'session_id' => $session->getId(),
                'session_name' => $session->getName(),
                'has_cookie' => $request->hasCookie($session->getName()),
                'all_session_keys' => array_keys($session->all()),
                'mobile_sampling_auth' => $session->get('mobile_sampling_auth'),
                'request_id' => $id,
                'draft_id' => $draft_id,
                'is_ajax' => $isAjax,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'headers' => $request->headers->all()
            ]);
        }
        
        if (!$hasAuth) {
            \Log::warning('Mobile Sampling UpdateDraft: Authentication failed', [
                'session_id' => $session->getId(),
                'session_keys' => array_keys($session->all()),
                'request_id' => $id,
                'draft_id' => $draft_id
            ]);
            
            if ($isAjax) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu',
                    'redirect' => route('mobile.sampling.index', ['id' => $id])
                ], 401);
            }
            return redirect()->route('mobile.sampling.index', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu');
        }

        try {
            DB::beginTransaction();

            $draft = SampleDraft::where('id_sample_draft', $draft_id)
                ->where('permohonan_uji_id', $id)
                ->where('status', 'draft')
                ->first();

            if (!$draft) {
                if ($isAjax) {
                    return response()->json([
                        'status' => false,
                        'success' => false,
                        'message' => 'Draft tidak ditemukan atau sudah dikonfirmasi',
                        'redirect' => route('mobile.sampling.draftList', ['id' => $id])
                    ], 404);
                }
                return redirect()->route('mobile.sampling.draftList', ['id' => $id])
                    ->with('error', 'Draft tidak ditemukan atau sudah dikonfirmasi');
            }

            // Validate request with better error handling
            try {
                // Validate request - pengambil_sampel can be array or not present
            $validated = $request->validate([
                'titik_pengambilan' => 'nullable|string|max:500',
                'cost_sampling_samples' => 'nullable|numeric|min:0',
                'note_samples' => 'nullable|string',
                'datesampling_samples' => 'nullable|date',
                'date_sending' => 'nullable|date',
                ]);
                
                // Validate pengambil_sampel separately if it exists
                if ($request->has('pengambil_sampel')) {
                    $request->validate([
                'pengambil_sampel' => 'nullable|array',
                'pengambil_sampel.*' => 'nullable|string',
            ]);
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Return validation errors as JSON for AJAX requests
                if ($isAjax) {
                    return response()->json([
                        'status' => false,
                        'success' => false,
                        'message' => 'Data tidak valid: ' . implode(', ', $e->errors()),
                        'errors' => $e->errors()
                    ], 422);
                }
                // For non-AJAX, redirect back with errors
                return redirect()->back()
                    ->withErrors($e->errors())
                    ->withInput();
            }

            // Handle pengambil_sampel (multiple selection)
            // Can come as 'pengambil_sampel[]' or 'pengambil_sampel[0]', 'pengambil_sampel[1]', etc.
            $pengambil_sampel_value = null;
            $pengambil_sampel_array = [];
            
            // Log all request data for debugging
            \Log::info('Mobile Sampling UpdateDraft: Request data', [
                'all_input' => $request->all(),
                'has_pengambil_sampel' => $request->has('pengambil_sampel'),
                'pengambil_sampel_type' => $request->has('pengambil_sampel') ? gettype($request->pengambil_sampel) : 'not_set',
                'pengambil_sampel_value' => $request->has('pengambil_sampel') ? $request->pengambil_sampel : 'not_set',
            ]);
            
            // Check for array format: pengambil_sampel[] (Laravel converts this to 'pengambil_sampel' array)
            // Try multiple ways to get the array
            $pengambil_sampel_input = null;
            
            // Method 1: Direct input
            if ($request->has('pengambil_sampel')) {
                $pengambil_sampel_input = $request->input('pengambil_sampel');
            }
            
            // Method 2: Check all() for array
            if (empty($pengambil_sampel_array) && $request->has('pengambil_sampel')) {
                $allInput = $request->all();
                if (isset($allInput['pengambil_sampel']) && is_array($allInput['pengambil_sampel'])) {
                    $pengambil_sampel_input = $allInput['pengambil_sampel'];
                }
            }
            
            // Method 3: Check for indexed array format: pengambil_sampel[0], pengambil_sampel[1], etc.
            if (empty($pengambil_sampel_array)) {
                $allInput = $request->all();
                foreach ($allInput as $key => $value) {
                    if (preg_match('/^pengambil_sampel\[(\d+)\]$/', $key)) {
                        if (!isset($pengambil_sampel_array)) {
                            $pengambil_sampel_array = [];
                        }
                        $pengambil_sampel_array[] = $value;
                    }
                }
            }
            
            // Process the input
            if ($pengambil_sampel_input !== null) {
                if (is_array($pengambil_sampel_input)) {
                    $pengambil_sampel_array = $pengambil_sampel_input;
                } elseif (is_string($pengambil_sampel_input)) {
                    $decoded = json_decode($pengambil_sampel_input, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $pengambil_sampel_array = $decoded;
                    } else {
                        $pengambil_sampel_array = [$pengambil_sampel_input];
                    }
                }
            }
            
            // Process the array if we have values
            if (!empty($pengambil_sampel_array) && is_array($pengambil_sampel_array)) {
                // Filter empty values and trim
                $pengambil_sampel_array = array_filter(array_map('trim', $pengambil_sampel_array));
                if (!empty($pengambil_sampel_array)) {
                    // Store as JSON array (string) - MUST be string, not array
                    $pengambil_sampel_value = json_encode(array_values($pengambil_sampel_array));
                    \Log::info('Mobile Sampling UpdateDraft: Processed pengambil_sampel', [
                        'array' => $pengambil_sampel_array,
                        'json' => $pengambil_sampel_value,
                        'json_type' => gettype($pengambil_sampel_value)
                    ]);
                } else {
                    // Empty array after filtering, set to null
                    $pengambil_sampel_value = null;
                }
            } else {
                // No array or empty, set to null
                $pengambil_sampel_value = null;
            }

            // Update draft fields with proper date parsing
            // Only update pengambil_sampel if we have a new value, otherwise keep existing
            // Ensure all values are not arrays
            $updateData = [];
            
            // Handle titik_pengambilan - ensure it's a string
            $titik_pengambilan = $request->input('titik_pengambilan');
            if ($titik_pengambilan !== null) {
                $updateData['titik_pengambilan'] = is_array($titik_pengambilan) ? json_encode($titik_pengambilan) : (string)$titik_pengambilan;
            } else {
                $updateData['titik_pengambilan'] = $draft->titik_pengambilan;
            }
            
            // Handle cost_sampling_samples - ensure it's numeric
            $cost_sampling = $request->input('cost_sampling_samples');
            if ($cost_sampling !== null) {
                $updateData['cost_sampling_samples'] = is_array($cost_sampling) ? json_encode($cost_sampling) : (float)$cost_sampling;
            } else {
                $updateData['cost_sampling_samples'] = $draft->cost_sampling_samples;
            }
            
            // Handle note_samples - ensure it's a string
            $note_samples = $request->input('note_samples');
            if ($note_samples !== null) {
                $updateData['note_samples'] = is_array($note_samples) ? json_encode($note_samples) : (string)$note_samples;
            } else {
                $updateData['note_samples'] = $draft->note_samples;
            }
            
            // Only set pengambil_sampel if we have a valid value (not null)
            // CRITICAL: Always ensure it's a string, not an array
            if ($pengambil_sampel_value !== null && is_string($pengambil_sampel_value)) {
                // Double check it's a string
                $updateData['pengambil_sampel'] = $pengambil_sampel_value;
                \Log::info('Mobile Sampling UpdateDraft: Setting pengambil_sampel', [
                    'value' => $pengambil_sampel_value,
                    'type' => gettype($pengambil_sampel_value),
                    'length' => strlen($pengambil_sampel_value)
                ]);
            } else {
                // Keep existing value if no new value provided
                // But ensure it's a string, not an array
                $existingValue = $draft->pengambil_sampel;
                if (is_array($existingValue)) {
                    // If existing value is array (shouldn't happen, but just in case), convert to JSON
                    $updateData['pengambil_sampel'] = json_encode($existingValue);
                    \Log::warning('Mobile Sampling UpdateDraft: Existing pengambil_sampel was array, converted to JSON');
                } elseif (is_string($existingValue) || $existingValue === null) {
                    $updateData['pengambil_sampel'] = $existingValue;
                } else {
                    // Unknown type, convert to string
                    $updateData['pengambil_sampel'] = (string)$existingValue;
                    \Log::warning('Mobile Sampling UpdateDraft: Existing pengambil_sampel had unexpected type', [
                        'type' => gettype($existingValue)
                    ]);
                }
            }
            
            // Parse dates safely - handle multiple formats
            if ($request->filled('datesampling_samples')) {
                try {
                    $dateValue = $request->datesampling_samples;
                    // Try to parse the date
                    $updateData['datesampling_samples'] = \Carbon\Carbon::parse($dateValue);
                } catch (\Exception $e) {
                    \Log::warning('Failed to parse datesampling_samples', [
                        'value' => $request->datesampling_samples,
                        'error' => $e->getMessage()
                    ]);
                    // Keep existing value if parsing fails
                    $updateData['datesampling_samples'] = $draft->datesampling_samples;
                }
            } else {
                $updateData['datesampling_samples'] = $draft->datesampling_samples;
            }
            
            if ($request->filled('date_sending')) {
                try {
                    $dateValue = $request->date_sending;
                    // Try to parse the date
                    $updateData['date_sending'] = \Carbon\Carbon::parse($dateValue);
                } catch (\Exception $e) {
                    \Log::warning('Failed to parse date_sending', [
                        'value' => $request->date_sending,
                        'error' => $e->getMessage()
                    ]);
                    // Keep existing value if parsing fails
                    $updateData['date_sending'] = $draft->date_sending;
                }
            } else {
                $updateData['date_sending'] = $draft->date_sending;
            }
            
            // Log update data before saving to ensure no arrays are passed
            \Log::info('Mobile Sampling UpdateDraft: Update data before save', [
                'update_data' => array_map(function($value) {
                    if (is_array($value)) {
                        return 'ARRAY_DETECTED: ' . json_encode($value);
                    }
                    if (is_object($value)) {
                        return 'OBJECT_DETECTED: ' . get_class($value);
                    }
                    return $value;
                }, $updateData),
                'pengambil_sampel_type' => isset($updateData['pengambil_sampel']) ? gettype($updateData['pengambil_sampel']) : 'not_set',
                'pengambil_sampel_value' => isset($updateData['pengambil_sampel']) ? (is_string($updateData['pengambil_sampel']) ? substr($updateData['pengambil_sampel'], 0, 100) : gettype($updateData['pengambil_sampel'])) : 'not_set',
            ]);
            
            // Ensure no arrays are in updateData - convert any arrays to JSON strings
            // Also ensure Carbon objects are properly formatted
            $finalUpdateData = [];
            foreach ($updateData as $key => $value) {
                if (is_array($value)) {
                    \Log::warning('Mobile Sampling UpdateDraft: Array detected in updateData, converting to JSON', [
                        'key' => $key,
                        'value' => $value
                    ]);
                    $finalUpdateData[$key] = json_encode($value);
                } elseif ($value instanceof \Carbon\Carbon) {
                    // Carbon objects are fine, Eloquent will handle them
                    $finalUpdateData[$key] = $value;
                } elseif (is_object($value)) {
                    \Log::warning('Mobile Sampling UpdateDraft: Object detected in updateData, converting to string', [
                        'key' => $key,
                        'class' => get_class($value)
                    ]);
                    // Try to convert object to string or JSON
                    if (method_exists($value, '__toString')) {
                        $finalUpdateData[$key] = (string)$value;
                    } else {
                        $finalUpdateData[$key] = json_encode($value);
                    }
                } else {
                    $finalUpdateData[$key] = $value;
                }
            }
            
            \Log::info('Mobile Sampling UpdateDraft: Final update data', [
                'final_data' => array_map(function($value) {
                    if (is_array($value)) {
                        return 'ARRAY: ' . json_encode($value);
                    }
                    if (is_object($value)) {
                        return 'OBJECT: ' . get_class($value);
                    }
                    return $value;
                }, $finalUpdateData)
            ]);
            
            // Use fill() and save() instead of update() to have more control
            // Ensure only fillable fields are included
            $fillableFields = $draft->getFillable();
            $safeUpdateData = [];
            foreach ($finalUpdateData as $key => $value) {
                if (in_array($key, $fillableFields)) {
                    $safeUpdateData[$key] = $value;
                } else {
                    \Log::warning('Mobile Sampling UpdateDraft: Field not fillable, skipping', [
                        'field' => $key,
                        'value' => is_array($value) ? 'ARRAY' : (is_object($value) ? get_class($value) : $value)
                    ]);
                }
            }
            
            \Log::info('Mobile Sampling UpdateDraft: Safe update data (fillable only)', [
                'safe_data' => array_map(function($value) {
                    if (is_array($value)) {
                        return 'ARRAY: ' . json_encode($value);
                    }
                    if (is_object($value)) {
                        return 'OBJECT: ' . get_class($value);
                    }
                    return $value;
                }, $safeUpdateData)
            ]);
            
            $draft->fill($safeUpdateData);
            $draft->save();

            DB::commit();
            
            // CRITICAL: Ensure session is saved and mobile_sampling_auth is maintained
            // DO NOT regenerate token, DO NOT invalidate, DO NOT flush
            // Just ensure the session value is set and saved
            if (!$request->session()->has('mobile_sampling_auth')) {
                $request->session()->put('mobile_sampling_auth', true);
            }
            
            // Save session explicitly
            $request->session()->save();
            
            // Log success for debugging
            \Log::info('Mobile Sampling UpdateDraft: Success', [
                'session_id' => $request->session()->getId(),
                'has_auth' => $request->session()->has('mobile_sampling_auth'),
                'request_id' => $id,
                'draft_id' => $draft_id
            ]);

            // Return JSON response for AJAX requests
            if ($isAjax) {
                return response()->json([
                    'status' => true,
                    'success' => true,
                    'message' => 'Draft sample berhasil diupdate!',
                    'redirect' => route('mobile.sampling.draftList', ['id' => $id])
                ], 200);
            }

            return redirect()->route('mobile.sampling.draftList', ['id' => $id])
                ->with('success', 'Draft sample berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log detailed error information
            \Log::error('Mobile Sampling UpdateDraft Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->all(),
                'session_id' => $request->session()->getId()
            ]);
            
            // Return JSON response for AJAX requests
            if ($isAjax) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'message' => 'Gagal mengupdate draft: ' . $e->getMessage(),
                    'error_type' => get_class($e),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal mengupdate draft sample: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a single draft
     */
    public function deleteDraft(Request $request, $id, $draft_id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_auth', false)) {
            return response()->json([
                'status' => false,
                'pesan' => 'Silakan login terlebih dahulu'
            ], 401);
        }

        try {
            $draft = SampleDraft::where('id_sample_draft', $draft_id)
                ->where('permohonan_uji_id', $id)
                ->where('status', 'draft')
                ->first();

            if (!$draft) {
                return response()->json([
                    'status' => false,
                    'pesan' => 'Draft tidak ditemukan atau sudah dikonfirmasi'
                ], 404);
            }

            $draft->status = 'deleted';
            $draft->save();
            $draft->delete();

            return response()->json([
                'status' => true,
                'pesan' => 'Draft sample berhasil dihapus!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'pesan' => 'Gagal menghapus draft sample: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete a group of drafts
     */
    public function deleteDraftGroup(Request $request, $id, $group_id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_auth', false)) {
            return response()->json([
                'status' => false,
                'pesan' => 'Silakan login terlebih dahulu'
            ], 401);
        }

        try {
            // If group_id is 'ungrouped', delete all drafts without draft_group_id
            if ($group_id === 'ungrouped') {
                $drafts = SampleDraft::where('permohonan_uji_id', $id)
                    ->whereNull('draft_group_id')
                    ->where('status', 'draft')
                    ->get();
            } else {
                $drafts = SampleDraft::where('permohonan_uji_id', $id)
                    ->where('draft_group_id', $group_id)
                    ->where('status', 'draft')
                    ->get();
            }

            if ($drafts->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'pesan' => 'Tidak ada draft dalam grup ini atau sudah dikonfirmasi'
                ], 404);
            }

            $count = 0;
            foreach ($drafts as $draft) {
                $draft->status = 'deleted';
                $draft->save();
                $draft->delete();
                $count++;
            }

            return response()->json([
                'status' => true,
                'pesan' => "Berhasil menghapus {$count} draft sample dalam grup ini!",
                'count' => $count
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'pesan' => 'Gagal menghapus grup draft: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show mobile sampling form
     * Show mobile sampling form
     */
    public function form(Request $request, $id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_auth', false)) {
            return redirect()->route('mobile.sampling.index', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu');
        }


        // Double-check access rights
        $userId = $request->session()->get('mobile_sampling_user_id');
        $user = User::where('id', $userId)->with(['getlevel', 'laboratorium'])->first();


        if ($user) {
            $userLevel = $user->getlevel->level ?? null;
            $isAdmin = in_array($userLevel, ['elits-dev','ALAB','LAB','SOLAB','ANLS', 'admin']);
            $isSOLAB = $user->level === 'd3090b8d-8951-4f5b-97e5-4dedf6935da7';


            if (!$isAdmin && !$isSOLAB) {
                return redirect()->route('mobile.sampling.index', ['id' => $id])
                    ->with('error', 'Akses ditolak! Hanya petugas pengambil sample lab atau admin yang dapat mengakses form ini.');
            }


            if ($isSOLAB) {
                $labName = strtolower($user->laboratorium->nama_laboratorium ?? '');
                if (!in_array($labName, ['kimia', 'mikrobiologi'])) {
                    return redirect()->route('mobile.sampling.index', ['id' => $id])
                        ->with('error', 'Akses ditolak! Anda harus terdaftar di laboratorium Kimia atau Mikrobiologi.');
                }
            }
        }

        // Get permohonan uji data with samples and their types
        $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)
            ->with(['customer', 'samples.sampletype'])
            ->first();

        if (!$permohonan_uji) {
            return view('masterweb::module.mobile.sampling.error', [
                'message' => 'Data permohonan uji tidak ditemukan'
            ]);
        }

        // Get sample types (Kesmas)
        $sample_types = SampleType::orderBy('created_at')->get();

        // Get laboratories (exclude Klinik)
        $laboratoriums = Laboratorium::where('kode_laboratorium', '!=', 'KLI')->get();

        // Build data_methods structure and generate sample codes
        $data_methods = array();
        $code_samples = array();
        $lab_keys = array();


        foreach ($laboratoriums as $laboratorium) {
            // Determine lab code: 01 for Kimia, 02 for Mikrobiologi
            $lab_code = strtolower($laboratorium->nama_laboratorium) === 'kimia' ? '01' : '02';
            $code_samples[strtolower($laboratorium->nama_laboratorium)] = $this->getCodeSample(
                $this->getLabNumByLabKey($laboratorium->id_laboratorium, $id),
                $lab_code,
                $this->getLabNumByLabKey($laboratorium->id_laboratorium, $id),
                $lab_code,
                '...'
            );
            $lab_keys[strtolower($laboratorium->nama_laboratorium)] = $laboratorium->id_laboratorium;


            array_push(
                $data_methods,
                (object) array(
                    'name' => $laboratorium->nama_laboratorium,
                    'id_lab' => $laboratorium->id_laboratorium,
                    'method' => array()
                )
            );
        }

        // Get methods for each laboratory
        $i = 0;
        foreach ($data_methods as $data_method) {
            $laboratoriummethods = LaboratoriumMethod::where('laboratorium_id', '=', $data_method->id_lab)
                ->orderBy('ms_method.created_at')
                ->join('ms_method', function ($join) {
                    $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
                        ->whereNull('tb_laboratorium_method.deleted_at')
                        ->whereNull('ms_method.deleted_at');
                })
                ->get();

            foreach ($laboratoriummethods as $laboratoriummethod) {
                // Get all sampletype_ids that have baku mutu for this method
                $sampletypes_with_baku_mutu = BakuMutuSampletypeHelper::sampletypeIdsWithBakuMutu(
                    $laboratoriummethod->id_method,
                    $data_method->id_lab
                );

                array_push(
                    $data_methods[$i]->method,
                    (object) array(
                        'name_method' => $laboratoriummethod->params_method,
                        'id_method' => $laboratoriummethod->id_method,
                        'price_method' => $laboratoriummethod->price_total_method,
                        'baku_mutu_sampletypes' => $sampletypes_with_baku_mutu
                    )
                );
            }

            $i++;
        }

        $data_methods = MethodSampleTypePrice::attachPricesToDataMethods($data_methods);

        // Get programs
        $programs = Program::orderBy('created_at')->get();

        // Get packets with their sample type from ms_packet table directly
        $packets = Packet::where('id_packet', '!=', '0')
            ->whereNotNull('name_packet')
            ->where('name_packet', '!=', '')
            ->with(['packet_detail.method', 'sampletype'])
            ->orderBy('created_at')
            ->get()
            ->unique('id_packet')
            ->values(); // Reset collection keys
        
        // Use sample_type_id directly from ms_packet table
        foreach ($packets as $packet) {
            // If packet has sample_type_id, use it as array for compatibility with view
            if ($packet->sample_type_id) {
                $packet->sample_type_ids = [$packet->sample_type_id];
            } else {
                $packet->sample_type_ids = [];
            }
        }

        // Get user info from session
        $petugas_name = $request->session()->get('mobile_sampling_user_name', 'Petugas');


        // Get petugas from Petugas model based on role and lab
        $petugas_list = $this->getPetugasSampling();

        // Get user level from session
        $userLevel = $request->session()->get('mobile_sampling_user_level');
        // For form page, back button should always go to draft list
        $backUrl = route('mobile.sampling.draftList', ['id' => $id]);

        return view('masterweb::module.mobile.sampling.form', compact(
            'permohonan_uji',
            'sample_types',
            'data_methods',
            'programs',
            'petugas_name',
            'code_samples',
            'lab_keys',
            'id',
            'petugas_list',
            'packets',
            'backUrl'
        ));
    }


    /**
     * Get lab number by lab key
     */
    private function getLabNumByLabKey($lab_key, $id, $is_makanan = false): int
    {
        // Use LabNum table to track sequence per laboratory (separate for each lab)
        $permohonan_uji = \Smt\Masterweb\Models\PermohonanUji::where('id_permohonan_uji', $id)
            ->with(['samples' => function($q) {
                $q->orderBy('created_at', 'desc');
            }])
            ->first();


        if (!$permohonan_uji) {
            return 0;
        }

        // Check if permohonan_uji already has samples
        if (count($permohonan_uji->samples) > 0) {
            // Ada sample sebelumnya, hitung berdasarkan sample terakhir
            $latest_sample = $permohonan_uji->samples->first();
            $lab_num = \Smt\Masterweb\Models\LabNum::where(DB::raw('YEAR(created_at)'), '=', date('Y'))
                ->where('lab_id', $lab_key)
                ->where('year_lab_num', '=', date('Y'))
                ->where('tb_lab_num.created_at', '<=', $latest_sample->created_at);
        } else {
            // Belum ada sample, hitung berdasarkan permohonan_uji
            $lab_num = \Smt\Masterweb\Models\LabNum::where(DB::raw('YEAR(created_at)'), '=', date('Y'))
                ->where('lab_id', $lab_key)
                ->where('year_lab_num', '=', date('Y'))
                ->where('tb_lab_num.created_at', '<=', $permohonan_uji->created_at);
        }

        $lab_num_count = $lab_num->count();

        if ($lab_num_count > 0) {
            // Return max lab_number for this specific lab
            return $lab_num->max('lab_number');
        } else {
            // Get start number from ms_start_number for this specific lab
            $start_num = \Smt\Masterweb\Models\StartNum::join('ms_laboratorium', function ($join) {
                    $join->on('ms_laboratorium.kode_laboratorium', '=', 'ms_start_number.code_lab_start_number')
                        ->whereNull('ms_laboratorium.deleted_at')
                        ->whereNull('ms_start_number.deleted_at');
                })
                ->where('id_laboratorium', $lab_key)
                ->first();

            return $start_num ? $start_num->count_start_number : 0;
        }
    }


    /**
     * Get code sample
     */
    private function getCodeSample($count, $lab_code = '01', $sample_type_code = '')
    {
        // Format: {kode_jenis_sample}.{kode_lab}/{nomer_urut}/{tahun}
        // Example: AM.01/0003/2025 (Kimia) or AM.02/0004/2025 (Mikro)


        $code_number = str_pad((int)($count + 1), 4, '0', STR_PAD_LEFT);
        $code_datetime = now();
        $code_year = $code_datetime->format('Y');

        // Format: SAMPLE_TYPE.LAB_CODE/NUMBER/YEAR
        $code = $sample_type_code . '.' . $lab_code . '/' . $code_number . '/' . $code_year;
        return $code;
    }

    /**
     * Store sample from mobile
     */
    public function store(Request $request, $id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_auth', false)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'pesan' => 'Silakan login terlebih dahulu'
                ], 401, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
            }
            return redirect()->route('mobile.sampling.index', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu');
        }


        // Validate access rights
        $userId = $request->session()->get('mobile_sampling_user_id');
        $user = User::where('id', $userId)->with(['getlevel', 'laboratorium'])->first();


        if ($user) {
            $userLevel = $user->getlevel->level ?? null;
            $isAdmin = in_array($userLevel, ['elits-dev','ALAB','LAB','SOLAB','ANLS', 'admin']);
            $isSOLAB = $user->level === 'd3090b8d-8951-4f5b-97e5-4dedf6935da7';


            if (!$isAdmin && !$isSOLAB) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'success' => false,
                        'pesan' => 'Akses ditolak! Hanya petugas pengambil sample lab atau admin yang dapat menyimpan data.'
                    ], 403, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
                }
                return redirect()->route('mobile.sampling.index', ['id' => $id])
                    ->with('error', 'Akses ditolak! Hanya petugas pengambil sample lab atau admin yang dapat menyimpan data.');
            }


            if ($isSOLAB) {
                $labName = strtolower($user->laboratorium->nama_laboratorium ?? '');
                if (!in_array($labName, ['kimia', 'mikrobiologi'])) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'status' => false,
                            'success' => false,
                            'pesan' => 'Akses ditolak! Anda harus terdaftar di laboratorium Kimia atau Mikrobiologi.'
                        ], 403, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
                    }
                    return redirect()->route('mobile.sampling.index', ['id' => $id])
                        ->with('error', 'Akses ditolak! Anda harus terdaftar di laboratorium Kimia atau Mikrobiologi.');
                }
            }
        }


        try {
            DB::beginTransaction();

            // Validate request - Support both old single sample and new multiple samples format
            if ($request->has('samples') && is_array($request->samples)) {
                // New format: Multiple samples
                $validated = $request->validate([
                    'id_permohonan_uji' => 'required',
                    'samples' => 'required|array|min:1',
                    'samples.*.sample_type_id' => 'required|uuid',
                    'samples.*.methods' => 'required|array|min:1',
                    'samples.*.cost_samples' => 'required|numeric|min:0',
                    'samples.*.packet_id' => 'nullable|uuid',
                    'datesampling_samples' => 'required|date',
                    'date_sending' => 'required|date',
                    'titik_pengambilan' => 'nullable|string',
                    'cost_sampling_samples' => 'nullable|numeric|min:0',
                    'note' => 'nullable|string',
                    'program_samples' => 'nullable|string',
                    'petugas_selected' => 'required|array|min:1',
                    'petugas_selected.*' => 'required|string',
                    'signature_pelanggan' => 'nullable|string',
                    'jabatan_pelanggan' => 'nullable|string',
                    'nama_pelanggan' => 'nullable|string',
                    'nip_pelanggan' => 'nullable|string',
                ]);
            } else {
                // Old format: Single sample (backward compatibility)
                $validated = $request->validate([
                    'id_permohonan_uji' => 'required',
                    'jenis_sampel' => 'required',
                    'datesampling_samples' => 'required|date',
                    'date_sending' => 'required|date',
                    'titik_pengambilan' => 'nullable|string',
                    'method' => 'required|array|min:1',
                    'cost_samples' => 'required|numeric',
                    'cost_sampling_samples' => 'nullable|numeric|min:0',
                    'note' => 'nullable|string',
                    'packet' => 'nullable|array',
                    'program_samples' => 'nullable|string',
                    'sample_qty' => 'nullable|integer|min:1|max:10',
                    'petugas_selected' => 'required|array|min:1',
                    'petugas_selected.*' => 'required|string',
                    'signature_pelanggan' => 'nullable|string',
                    'jabatan_pelanggan' => 'nullable|string',
                    'nama_pelanggan' => 'nullable|string',
                    'nip_pelanggan' => 'nullable|string',
                ]);
            }

            // Store signature pelanggan sebagai blob data
            $signature_pelanggan_blob = null;
            if ($request->filled('signature_pelanggan')) {
                try {
                    $signature_data = $request->signature_pelanggan;

                    // Decode base64 image
                    if (preg_match('/^data:image\/(\w+);base64,/', $signature_data, $type)) {
                        $signature_data = substr($signature_data, strpos($signature_data, ',') + 1);
                        $signature_data = base64_decode($signature_data);

                        if ($signature_data === false) {
                            throw new \Exception('Base64 decode failed');
                        }

                        // Store binary data directly
                        $signature_pelanggan_blob = $signature_data;
                    }
                } catch (\Exception $e) {
                    \Log::error('Error saving signature pelanggan: ' . $e->getMessage());
                    // Continue without signature if error occurs
                }
            }

            // Get jabatan pelanggan - sanitize UTF-8
            $jabatan_pelanggan = $request->input('jabatan_pelanggan');
            if ($jabatan_pelanggan) {
                $jabatan_pelanggan = mb_convert_encoding($jabatan_pelanggan, 'UTF-8', 'UTF-8');
            }
            $nip_pelanggan = $request->input('nip_pelanggan');
            if ($nip_pelanggan) {
                $nip_pelanggan = mb_convert_encoding($nip_pelanggan, 'UTF-8', 'UTF-8');
            }
            // Get permohonan uji first to access customer
            $permohonan_uji = PermohonanUji::with('customer')->findOrFail($id);
            
            // Get nama_pelanggan from customer name (always use customer name)
            $nama_pelanggan = null;
            if ($permohonan_uji->customer && isset($permohonan_uji->customer->name_customer)) {
                $nama_pelanggan = $permohonan_uji->customer->name_customer;
            }
            // If customer name is not available, try to get from form input as fallback
            if (empty($nama_pelanggan)) {
                $nama_pelanggan = $request->input('nama_pelanggan');
            }
            if ($nama_pelanggan) {
                $nama_pelanggan = mb_convert_encoding($nama_pelanggan, 'UTF-8', 'UTF-8');
            }

            // Helper function to ensure UTF-8 encoding
            $ensureUtf8 = function($value) {
                if (is_array($value)) {
                    return array_map(function($item) use (&$ensureUtf8) {
                        return is_string($item) ? mb_convert_encoding($item, 'UTF-8', 'UTF-8') : $item;
                    }, $value);
                }
                if (is_string($value)) {
                    // Remove invalid UTF-8 characters
                    return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                }
                return $value;
            };

            // Determine petugas name - from checkboxes (multiple selection)
            // Store as JSON array
            if ($request->filled('petugas_selected') && is_array($request->petugas_selected)) {
                $petugas_array = array_filter(array_map('trim', $request->petugas_selected));
                if (!empty($petugas_array)) {
                    $petugas_array = $ensureUtf8($petugas_array);
                    $petugas_name = json_encode(array_values($petugas_array), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
                } else {
                    $session_name = $ensureUtf8($request->session()->get('mobile_sampling_user_name', 'Petugas'));
                    $petugas_name = json_encode([$session_name], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
                }
            } else {
                $session_name = $ensureUtf8($request->session()->get('mobile_sampling_user_name', 'Petugas'));
                $petugas_name = json_encode([$session_name], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
            }


            // Ensure petugas_name is not empty
            if (empty($petugas_name)) {
                $petugas_name = $request->session()->get('mobile_sampling_user_name', 'Petugas');
            }


            $petugas_id = $request->session()->get('mobile_sampling_user_id');


            // Get permohonan uji (already loaded above with customer relation)
            // $permohonan_uji is already loaded above

            // Note: jabatan_pelanggan, nama_pelanggan, nip_pelanggan, dan signature_pelanggan 
            // tidak ada di tabel tb_permohonan_uji, jadi tidak perlu diupdate
            // Data tersebut akan disimpan di draft saja

            // Get default program
            $program = Program::orderBy('created_at')->first();
            $default_program = $program ? $program->id_program : null;

            // Set dates
            $datesampling = $request->datesampling_samples ? Carbon::parse($request->datesampling_samples) : Carbon::now();
            $date_sending = $request->date_sending ? Carbon::parse($request->date_sending) : Carbon::now();

            // Generate a single draft_group_id for all drafts in this submission
            $draft_group_id = Uuid::uuid4()->toString();

            // Handle multiple samples format - Save to DRAFT first
            if ($request->has('samples') && is_array($request->samples)) {
                // New format: Multiple samples - Save to draft
                $total_created = 0;
                $created_drafts = [];
                
                foreach ($request->samples as $sampleConfig) {
                    // Sanitize titik_pengambilan and note for UTF-8
                    // Get titik_pengambilan from sampleConfig (per sample type) with fallback to global
                    $titik_pengambilan = $sampleConfig['titik_pengambilan'] ?? $request->titik_pengambilan ?? null;
                    if ($titik_pengambilan) {
                        $titik_pengambilan = mb_convert_encoding($titik_pengambilan, 'UTF-8', 'UTF-8');
                    }
                    $note_samples = $request->note;
                    if ($note_samples) {
                        $note_samples = mb_convert_encoding($note_samples, 'UTF-8', 'UTF-8');
                    }
                    $program_samples = $request->program_samples;
                    if ($program_samples) {
                        $program_samples = mb_convert_encoding($program_samples, 'UTF-8', 'UTF-8');
                    }

                    // Create draft sample
                    // Set updated_at to be different from created_at to mark as "already filled"
                    $now = Carbon::now();
                    $updatedAt = $now->copy()->addSecond(); // Make updated_at 1 second later to ensure it's different
                    
                    $draft = SampleDraft::create([
                        'id_sample_draft' => Uuid::uuid4()->toString(),
                        'permohonan_uji_id' => $id,
                        'draft_group_id' => $draft_group_id, // Same group ID for all drafts in this submission
                        'typesample_samples' => $sampleConfig['sample_type_id'],
                        'datesampling_samples' => $datesampling,
                        'date_sending' => $date_sending,
                        'titik_pengambilan' => $titik_pengambilan, // Already filled from form
                        'cost_samples' => $sampleConfig['cost_samples'] ?? 0,
                        'note_samples' => $note_samples,
                        'packet_id' => !empty($sampleConfig['packet_id']) ? $sampleConfig['packet_id'] : null,
                        'program_samples' => $program_samples ?: $default_program,
                        'is_sampling' => 1,
                        'cost_sampling_samples' => $request->cost_sampling_samples ?? 20000,
                        'method_data' => null,
                        'status' => 'draft',
                        'created_by' => $petugas_id,
                        'pengambil_sampel' => $petugas_name ?: json_encode([$request->session()->get('mobile_sampling_user_name', 'Petugas')]), // Always filled from form or session
                        'name_pelanggan' => $nama_pelanggan,
                        'created_at' => $now,
                        'updated_at' => $updatedAt, // Set updated_at to mark as "already filled" (from mobile form)
                    ]);

                    // Save methods for this specific draft
                    if (isset($sampleConfig['methods']) && is_array($sampleConfig['methods'])) {
                        foreach ($sampleConfig['methods'] as $method_string) {
                            $parts = explode('_', $method_string);
                            if (count($parts) >= 3) {
                                SampleMethodDraft::create([
                                    'id_sample_method_draft' => Uuid::uuid4()->toString(),
                                    'sample_draft_id' => $draft->id_sample_draft,
                                    'method_id' => $parts[0],
                                    'laboratorium_id' => $parts[1],
                                    'price_method' => (float)$parts[2],
                                    'is_sub' => 0,
                                ]);
                            }
                        }
                    }

                    $created_drafts[] = $draft->id_sample_draft;
                    $total_created++;
                }

                DB::commit();

                // Ensure all response data is UTF-8 valid
                $message = $total_created > 1 
                    ? "Berhasil menyimpan {$total_created} draft sample!" 
                    : "Draft sample berhasil disimpan!";

                $responseData = [
                    'status' => true,
                    'success' => true,
                    'pesan' => $message,
                    'total_created' => $total_created,
                    'draft_group_id' => $draft_group_id,
                    'redirect' => route('mobile.sampling.draftList', ['id' => $id])
                ];

                // Sanitize response data
                array_walk_recursive($responseData, function(&$value) {
                    if (is_string($value)) {
                        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                });

                return response()->json($responseData, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
            }

            // OLD FORMAT: Single sample (backward compatibility)
            // Get laboratoriums
            $laboratoriums = Laboratorium::where('kode_laboratorium', '!=', 'KLI')->get();
            
            // Parse methods and group by lab
            $array_method = [];
            $lab_ids = [];

            if (isset($request->method) && is_array($request->method)) {
                foreach ($request->method as $method_data) {
                    $parts = explode('_', $method_data);
                    if (count($parts) >= 3) {
                        $array_method[] = [
                            'method' => $parts[0],
                            'lab' => $parts[1],
                            'price_method' => $parts[2],
                        ];
                        $lab_ids[] = $parts[1];
                    }
                }
            }

            // Sort and unique labs
            usort($array_method, function ($a, $b) {
                return strcmp($a["lab"], $b["lab"]);
            });
            $lab_ids = array_unique($lab_ids);
            sort($lab_ids);

            // Get sample qty for duplication
            $sample_qty = $request->sample_qty ?? 1;


            // Debug all inputs
            \Illuminate\Support\Facades\Log::info('=== MOBILE SAMPLING STORE DEBUG ===');
            \Illuminate\Support\Facades\Log::info('Sample QTY: ' . $sample_qty);
            \Illuminate\Support\Facades\Log::info('Titik Pengambilan (single): ' . ($request->titik_pengambilan ?? 'NULL'));
            \Illuminate\Support\Facades\Log::info('Note (single): ' . ($request->note ?? 'NULL'));


            // Log duplicate inputs if any
            if ($sample_qty > 1) {
                for ($i = 1; $i <= $sample_qty; $i++) {
                    \Illuminate\Support\Facades\Log::info("Titik Pengambilan #{$i}: " . ($request->input("titik_pengambilan_{$i}") ?? 'NULL'));
                    \Illuminate\Support\Facades\Log::info("Note #{$i}: " . ($request->input("note_{$i}") ?? 'NULL'));
                }
            }


            // Loop untuk setiap sample (duplicate)
            for ($sample_index = 1; $sample_index <= $sample_qty; $sample_index++) {
                // Get titik lokasi dan catatan per sample
                $titik_pengambilan = $sample_qty > 1
                    ? $request->input("titik_pengambilan_{$sample_index}")
                    : $request->titik_pengambilan;

                $note_samples = $sample_qty > 1
                    ? $request->input("note_{$sample_index}")
                    : $request->note;


                \Illuminate\Support\Facades\Log::info("Sample #{$sample_index} - Using Titik: {$titik_pengambilan}, Note: {$note_samples}");


                // Loop untuk setiap lab (seperti do-while di LaboratoriumSampleManagement)
                foreach ($lab_ids as $current_lab_id) {
                    $current_laboratorium = $laboratoriums->where('id_laboratorium', $current_lab_id)->first();


                    if (!$current_laboratorium) {
                        continue;
                    }


                    $current_lab_name = strtolower($current_laboratorium->nama_laboratorium);
                    if ($current_lab_name == 'mikrobiologi') $current_lab_name = 'mikro';


                    // Get lab code
                    $lab_code = $current_lab_name === 'kimia' ? '01' : '02';


                    // Get sample type code
                    $sample_type = SampleType::find($request->jenis_sampel);
                    $sample_type_code = $sample_type ? $sample_type->code_sample_type : 'XX';

                    $start_num = StartNum::join('ms_laboratorium', function ($join) {
                            $join->on('ms_laboratorium.kode_laboratorium', '=', 'ms_start_number.code_lab_start_number')
                                ->whereNull('ms_laboratorium.deleted_at')
                                ->whereNull('ms_start_number.deleted_at');
                        })
                        ->where('id_laboratorium', $current_lab_id)
                        ->first();

                    $current_year = (int) date('Y');
                    $current_global = GlobalLabSequence::getCurrentNumber($current_year);
                    if ($current_global == 0 && $start_num && $current_year === (int) ($start_num->year_start_number ?? $current_year)) {
                        GlobalLabSequence::raiseLastNumberToAtLeast((int) ($start_num->count_start_number ?? 0), $current_year);
                    }

                    $globalSeq = GlobalLabSequence::getNextNumber($current_year, $current_lab_id, 'lab', null);
                    $sequence_detail_new = GlobalLabSequenceDetail::where('year', $current_year)
                        ->where('sequence_number', $globalSeq)
                        ->where('lab_id', $current_lab_id)
                        ->where('lab_type', 'lab')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $code_number = str_pad((int) $globalSeq, 4, '0', STR_PAD_LEFT);
                    $code_year = (string) $current_year;
                    $code_sample = $sample_type_code . '.' . $lab_code . '/' . $code_number . '/' . $code_year;
                    $current_sample_urutan = (int) $globalSeq;
                    $lab_num_urutan = (int) $globalSeq;

                    // Calculate cost for this lab only
                    $lab_cost = 0;
                    foreach ($array_method as $method) {
                        if ($method['lab'] == $current_lab_id) {
                            $lab_cost += (int)$method['price_method'];
                        }
                    }


                    // Create sample for this lab
                    $sample = Sample::create([
                        'id_samples' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                        'permohonan_uji_id' => $id,
                        'typesample_samples' => $request->jenis_sampel,
                        'codesample_samples' => $code_sample,
                        'count_id' => $current_sample_urutan,
                        'pengambil_sampel' => $petugas_name,
                        'packet_id' => $request->packet[0],
                        'datesampling_samples' => $request->datesampling_samples,
                        'date_sending' => $request->date_sending,
                        'titik_pengambilan' => $titik_pengambilan,  // Use per-sample location
                        'cost_samples' => $lab_cost > 0 ? $lab_cost : $request->cost_samples,
                        'note_samples' => $note_samples,  // Use per-sample note
                        'program_samples' => $request->program_samples,
                        'is_sampling' => 1,  // Mark as from lab sampling
                        'cost_sampling_samples' => $request->cost_sampling_samples ?? 20000,  // Default 20000
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Store methods (SampleMethod) for this lab only
                    foreach ($array_method as $method) {
                        if ($method['lab'] == $current_lab_id) {
                            \Smt\Masterweb\Models\SampleMethod::create([
                                'sample_id' => $sample->id_samples,
                                'method_id' => $method['method'],
                                'laboratorium_id' => $method['lab'],
                                'price_method' => (int)$method['price_method'],
                            ]);
                        }
                    }


                    // Create LabNum for this lab (nomor global, sama dengan angka tengah kode sampel)
                    LabNum::create([
                        'sample_id' => $sample->id_samples,
                        'sample_type_id' => $sample->typesample_samples,
                        'lab_id' => $current_lab_id,
                        'mount_lab_num' => now()->format('m'),
                        'year_lab_num' => now()->format('Y'),
                        'permohonan_uji_id' => $sample->permohonan_uji_id,
                        'lab_number' => $lab_num_urutan,
                    ]);

                    if ($sequence_detail_new) {
                        $linkedLabNum = LabNum::where('sample_id', $sample->id_samples)
                            ->where('lab_id', $current_lab_id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        if ($linkedLabNum) {
                            $sequence_detail_new->update(['reference_id' => $linkedLabNum->id_lab_num]);
                        }
                    }

                    // Create PenerimaanSample dengan kelayakan LAYAK semua
                    \Smt\Masterweb\Models\PenerimaanSample::create([
                        'sample_id' => $sample->id_samples,
                        'penerimaan_sample_date' => $request->date_sending,
                        'kelayakan_tempat_kemasan' => 'LAYAK',
                        'kelayakan_berat_vol' => 'LAYAK',
                    ]);


                    // Create VerificationActivitySample for Pendaftaran/Registrasi
                    // Use petugas_penerima from permohonan_uji, fallback to petugas_name if null
                    $nama_petugas_pendaftaran = $permohonan_uji->petugas_penerima ?? $petugas_name;
                    if (empty($nama_petugas_pendaftaran)) {
                        $nama_petugas_pendaftaran = $request->session()->get('mobile_sampling_user_name', 'Petugas');
                    }


                    \Smt\Masterweb\Models\VerificationActivitySample::create([
                        'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                        'id_verification_activity' => 1,
                        'id_sample' => $sample->id_samples,
                        'start_date' => $permohonan_uji->date_permohonan_uji,
                        'stop_date' => $permohonan_uji->date_permohonan_uji,
                        'nama_petugas' => $nama_petugas_pendaftaran,
                        'is_done' => true,
                    ]);

                    // Create VerificationActivitySample for Pengambilan Sample
                    // Ensure petugas_name is not empty
                    $nama_petugas_pengambil = $petugas_name;
                    if (empty($nama_petugas_pengambil)) {
                        $nama_petugas_pengambil = $request->session()->get('mobile_sampling_user_name', 'Petugas');
                    }


                    \Smt\Masterweb\Models\VerificationActivitySample::create([
                        'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                        'id_verification_activity' => 6,
                        'id_sample' => $sample->id_samples,
                        'start_date' => $request->date_sending,
                        'stop_date' => $request->date_sending,
                        'nama_petugas' => $nama_petugas_pengambil,
                        'is_done' => true,
                    ]);
                } // End foreach lab_ids
            } // End for sample_index (duplicate loop)

            // Update total harga permohonan uji (including sampling costs)
            $total_cost = Sample::where('permohonan_uji_id', $id)->sum('cost_samples');
            $total_sampling_cost = Sample::where('permohonan_uji_id', $id)
                ->where('is_sampling', 1)
                ->sum('cost_sampling_samples');
            $permohonan_uji->update(['total_harga' => $total_cost + $total_sampling_cost]);

            DB::commit();

            // Calculate total samples created (sample_qty * lab_ids)
            $total_samples_created = $sample_qty * count($lab_ids);


            return redirect()->route('mobile.sampling.success', ['id' => $id])
                ->with('success', "Berhasil! {$total_samples_created} sample telah disimpan!");

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            if ($request->expectsJson() || $request->ajax()) {
                $errors = $e->errors();
                // Sanitize errors for UTF-8
                array_walk_recursive($errors, function(&$value) {
                    if (is_string($value)) {
                        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                });
                
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'pesan' => 'Validasi gagal',
                    'errors' => $errors
                ], 422, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->expectsJson() || $request->ajax()) {
                Log::error('Mobile Sampling Store Error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'request' => $request->all()
                ]);
                
                $errorMessage = mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8');
                
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'pesan' => 'Terjadi kesalahan: ' . $errorMessage
                ], 500, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Edit existing sample
     */
    public function edit(Request $request, $id, $sample_id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_auth', false)) {
            return redirect()->route('mobile.sampling.index', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu');
        }


        // Validate access rights (same as form)
        $userId = $request->session()->get('mobile_sampling_user_id');
        $user = User::where('id', $userId)->with(['getlevel', 'laboratorium'])->first();


        if ($user) {
            $userLevel = $user->getlevel->level ?? null;
            $isAdmin = in_array($userLevel, ['elits-dev','ALAB','LAB','SOLAB','ANLS', 'admin']);
            $isSOLAB = $user->level === 'd3090b8d-8951-4f5b-97e5-4dedf6935da7';


            if (!$isAdmin && !$isSOLAB) {
                return redirect()->route('mobile.sampling.index', ['id' => $id])
                    ->with('error', 'Akses ditolak! Hanya petugas pengambil sample lab atau admin yang dapat mengakses form ini.');
            }


            if ($isSOLAB) {
                $labName = strtolower($user->laboratorium->nama_laboratorium ?? '');
                if (!in_array($labName, ['kimia', 'mikrobiologi'])) {
                    return redirect()->route('mobile.sampling.index', ['id' => $id])
                        ->with('error', 'Akses ditolak! Anda harus terdaftar di laboratorium Kimia atau Mikrobiologi.');
                }
            }
        }

        // Get sample data with relationships
        $sample = Sample::where('id_samples', $sample_id)
            ->where('permohonan_uji_id', $id)
            ->with(['samplemethod', 'sampletype'])
            ->first();



        if (!$sample) {
            return redirect()->route('mobile.sampling.form', ['id' => $id])
                ->with('error', 'Data sampel tidak ditemukan');
        }


        // Get selected method IDs for pre-checking checkboxes
        $selected_methods = $sample->samplemethod->pluck('method_id')->toArray();

        // Get permohonan uji data
        $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)
            ->with(['customer', 'samples'])
            ->first();

        if (!$permohonan_uji) {
            return view('masterweb::module.mobile.sampling.error', [
                'message' => 'Data permohonan uji tidak ditemukan'
            ]);
        }

        // Get sample types (Kesmas)
        $sample_types = SampleType::orderBy('created_at')->get();

        // Get laboratories (exclude Klinik)
        $laboratoriums = Laboratorium::where('kode_laboratorium', '!=', 'KLI')->get();

        // Build data_methods structure
        $data_methods = array();


        foreach ($laboratoriums as $laboratorium) {
            array_push(
                $data_methods,
                (object) array(
                    'name' => $laboratorium->nama_laboratorium,
                    'id_lab' => $laboratorium->id_laboratorium,
                    'method' => array()
                )
            );
        }

        // Get methods for each laboratory
        $i = 0;
        foreach ($data_methods as $data_method) {
            $laboratoriummethods = LaboratoriumMethod::where('laboratorium_id', '=', $data_method->id_lab)
                ->orderBy('ms_method.created_at')
                ->join('ms_method', function ($join) {
                    $join->on('ms_method.id_method', '=', 'tb_laboratorium_method.method_id')
                        ->whereNull('ms_method.deleted_at')
                        ->whereNull('tb_laboratorium_method.deleted_at');
                })
                ->select('tb_laboratorium_method.*', 'ms_method.*')
                ->get();

            foreach ($laboratoriummethods as $laboratoriummethod) {
                $sampletypes_with_baku_mutu = BakuMutuSampletypeHelper::sampletypeIdsWithBakuMutu(
                    $laboratoriummethod->id_method,
                    $data_method->id_lab
                );

                array_push(
                    $data_methods[$i]->method,
                    (object) array(
                        'name_method' => $laboratoriummethod->params_method,
                        'id_method' => $laboratoriummethod->id_method,
                        'price_method' => $laboratoriummethod->price_total_method,
                        'baku_mutu_sampletypes' => $sampletypes_with_baku_mutu
                    )
                );
            }

            $i++;
        }

        $data_methods = MethodSampleTypePrice::attachPricesToDataMethods($data_methods);

        // Get programs
        $programs = Program::orderBy('created_at')->get();

        // Get user info from session
        $petugas_name = $request->session()->get('mobile_sampling_user_name', 'Petugas');

        // Get petugas from Petugas model based on role and lab
        $petugas_list = $this->getPetugasSampling();

        // Get user level from session
        $userLevel = $request->session()->get('mobile_sampling_user_level');
        $backUrl = $this->getBackUrl($userLevel);

        return view('masterweb::module.mobile.sampling.edit', compact(
            'permohonan_uji',
            'sample',
            'sample_types',
            'data_methods',
            'programs',
            'petugas_name',
            'petugas_list',
            'selected_methods',
            'id',
            'sample_id',
            'backUrl'
        ));
    }

    /**
     * Update existing sample
     */
    public function update(Request $request, $id, $sample_id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_auth', false)) {
            return redirect()->route('mobile.sampling.index', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu');
        }


        // Validate access rights
        $userId = $request->session()->get('mobile_sampling_user_id');
        $user = User::where('id', $userId)->with(['getlevel', 'laboratorium'])->first();


        if ($user) {
            $userLevel = $user->getlevel->level ?? null;
            $isAdmin = in_array($userLevel,['elits-dev','ALAB','LAB','SOLAB','ANLS', 'admin']);
            $isSOLAB = $user->level === 'd3090b8d-8951-4f5b-97e5-4dedf6935da7';


            if (!$isAdmin && !$isSOLAB) {
                return redirect()->route('mobile.sampling.index', ['id' => $id])
                    ->with('error', 'Akses ditolak! Hanya petugas pengambil sample lab atau admin yang dapat mengubah data.');
            }


            if ($isSOLAB) {
                $labName = strtolower($user->laboratorium->nama_laboratorium ?? '');
                if (!in_array($labName, ['kimia', 'mikrobiologi'])) {
                    return redirect()->route('mobile.sampling.index', ['id' => $id])
                        ->with('error', 'Akses ditolak! Anda harus terdaftar di laboratorium Kimia atau Mikrobiologi.');
                }
            }
        }

        try {
            DB::beginTransaction();

            // Validate request
            $validated = $request->validate([
                'jenis_sampel' => 'required',
                'datesampling_samples' => 'required|date',
                'date_sending' => 'required|date',
                'titik_pengambilan' => 'nullable|string',
                'method' => 'required|array|min:1',
                'cost_samples' => 'required|numeric',
                'cost_sampling_samples' => 'nullable|numeric|min:0',
                'note' => 'nullable|string',
                'petugas_option' => 'nullable|in:pilih,login',
                'petugas_selected' => 'nullable|string',
                'signature_pelanggan' => 'nullable|string',
                'name-ttd' => 'nullable|string',
            ]);

            // Store signature pelanggan sebagai blob data
            $signature_pelanggan_blob = null;
            if ($request->filled('signature_pelanggan')) {
                try {
                    $signature_data = $request->signature_pelanggan;

                    // Decode base64 image
                    if (preg_match('/^data:image\/(\w+);base64,/', $signature_data, $type)) {
                        $signature_data = substr($signature_data, strpos($signature_data, ',') + 1);
                        $signature_data = base64_decode($signature_data);

                        if ($signature_data === false) {
                            throw new \Exception('Base64 decode failed');
                        }

                        // Store binary data directly
                        $signature_pelanggan_blob = $signature_data;
                    }
                } catch (\Exception $e) {
                    // Continue without signature if error occurs
                }
            }

            // Get jabatan pelanggan
            $jabatan_pelanggan = $request->input('name-ttd');

            // Update signature dan jabatan pelanggan di permohonan uji
            if ($signature_pelanggan_blob || $jabatan_pelanggan) {
                $permohonan_uji = PermohonanUji::findOrFail($id);
                if ($signature_pelanggan_blob) {
                    $permohonan_uji->signature_pelanggan = $signature_pelanggan_blob;
                }
                if ($jabatan_pelanggan) {
                    $permohonan_uji->jabatan_pelanggan = $jabatan_pelanggan;
                }
                $permohonan_uji->save();
            }

            // Get sample
            $sample = Sample::where('id_samples', $sample_id)
                ->where('permohonan_uji_id', $id)
                ->firstOrFail();

            // Determine petugas name - from checkboxes (multiple selection)
            // Store as JSON array
            if ($request->filled('petugas_selected') && is_array($request->petugas_selected)) {
                $petugas_array = array_filter(array_map('trim', $request->petugas_selected));
                if (!empty($petugas_array)) {
                    $petugas_name = json_encode(array_values($petugas_array));
                } else {
                    // Fallback to existing value or session
                    $existing_petugas = $sample->pengambil_sampel ?? null;
                    if ($existing_petugas) {
                        $decoded = json_decode($existing_petugas, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $petugas_name = $existing_petugas;
                        } else {
                            $petugas_name = json_encode([$existing_petugas]);
                        }
                    } else {
                        $petugas_name = json_encode([$request->session()->get('mobile_sampling_user_name', 'Petugas')]);
                    }
                }
            } else {
                // Fallback to existing value or session
                $existing_petugas = $sample->pengambil_sampel ?? null;
                if ($existing_petugas) {
                    $decoded = json_decode($existing_petugas, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $petugas_name = $existing_petugas;
                    } else {
                        $petugas_name = json_encode([$existing_petugas]);
                    }
                } else {
                    $petugas_name = json_encode([$request->session()->get('mobile_sampling_user_name', 'Petugas')]);
                }
            }


            // Update sample
            $sample->update([
                'typesample_samples' => $request->jenis_sampel,
                'datesampling_samples' => $request->datesampling_samples,
                'date_sending' => $request->date_sending,
                'titik_pengambilan' => $request->titik_pengambilan,
                'cost_samples' => $request->cost_samples,
                'cost_sampling_samples' => $request->cost_sampling_samples ?? 20000,
                'note_samples' => $request->note,
                'pengambil_sampel' => $petugas_name,
                'is_sampling' => 1,
            ]);

            // Delete existing sample methods
            SampleMethod::where('sample_id', $sample_id)->delete();

            // Re-create sample methods from selected parameters
            $totalPrice = 0;
            foreach ($request->method as $methodData) {
                list($methodId, $labId, $price) = explode('_', $methodData);


                SampleMethod::create([
                    'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                    'sample_id' => $sample_id,
                    'method_id' => $methodId,
                    'laboratorium_id' => $labId,
                    'price' => $price,
                ]);


                $totalPrice += $price;
            }

            // Update VerificationActivitySample untuk Pengambilan Sample (id = 6)
            $verificationSample = \Smt\Masterweb\Models\VerificationActivitySample::where('id_sample', $sample_id)
                ->where('id_verification_activity', 6)
                ->first();


            if ($verificationSample) {
                $verificationSample->update([
                    'nama_petugas' => $petugas_name,
                    'start_date' => $request->date_sending,
                    'stop_date' => $request->date_sending,
                ]);
            }

            // Update total_harga di PermohonanUji (including sampling costs)
            $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)->first();
            if ($permohonan_uji) {
                $allSamplesCost = Sample::where('permohonan_uji_id', $id)
                    ->whereNull('deleted_at')
                    ->sum('cost_samples');
                $allSamplingCost = Sample::where('permohonan_uji_id', $id)
                    ->whereNull('deleted_at')
                    ->where('is_sampling', 1)
                    ->sum('cost_sampling_samples');


                $permohonan_uji->update([
                    'total_harga' => $allSamplesCost + $allSamplingCost
                ]);
            }

            DB::commit();

            return redirect()->route('mobile.sampling.form', ['id' => $id])
                ->with('success', 'Data sampel berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show signature page after finishing drafts
     */
    public function signature(Request $request, $id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_auth', false)) {
            return redirect()->route('mobile.sampling.index', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu');
        }

        // Get permohonan uji
        $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)
            ->with('customer')
            ->first();

        if (!$permohonan_uji) {
            return view('masterweb::module.mobile.sampling.error', [
                'message' => 'Data permohonan uji tidak ditemukan'
            ]);
        }

        // Get all samples for this permohonan uji (recently created from drafts)
        $samples = Sample::where('permohonan_uji_id', $id)
            ->where('is_sampling', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        // Determine petugas pengambil sampel terbanyak
        $petugas_count = [];
        foreach ($samples as $sample) {
            if ($sample->pengambil_sampel) {
                // Try to decode as JSON first
                $decoded = json_decode($sample->pengambil_sampel, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    foreach ($decoded as $petugas) {
                        $petugas = trim($petugas);
                        if (!empty($petugas)) {
                            $petugas_count[$petugas] = ($petugas_count[$petugas] ?? 0) + 1;
                        }
                    }
                } else {
                    $petugas = trim($sample->pengambil_sampel);
                    if (!empty($petugas)) {
                        $petugas_count[$petugas] = ($petugas_count[$petugas] ?? 0) + 1;
                    }
                }
            }
        }

        // Get petugas with highest count
        $petugas_terbanyak = null;
        $max_count = 0;
        foreach ($petugas_count as $petugas => $count) {
            if ($count > $max_count) {
                $max_count = $count;
                $petugas_terbanyak = $petugas;
            }
        }

        // Fallback to session name if no petugas found
        if (!$petugas_terbanyak) {
            $petugas_terbanyak = $request->session()->get('mobile_sampling_user_name', 'Petugas');
        }

        // Ambil data pelanggan dari session (jika sudah diinput di draft-list)
        $customer_data = $request->session()->get('mobile_sampling_customer_data', null);

        // Get user level from session
        $userLevel = $request->session()->get('mobile_sampling_user_level');
        $backUrl = $this->getBackUrl($userLevel);

        return view('masterweb::module.mobile.sampling.signature', compact(
            'permohonan_uji',
            'petugas_terbanyak',
            'samples',
            'customer_data',
            'backUrl'
        ));
    }

    /**
     * Save signature after finishing drafts
     */
    public function saveSignature(Request $request, $id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_auth', false)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'pesan' => 'Silakan login terlebih dahulu'
                ], 401, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
            }
            return redirect()->route('mobile.sampling.index', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu');
        }

        try {
            DB::beginTransaction();

            // Get permohonan uji
            $permohonan_uji = PermohonanUji::findOrFail($id);

            // Store signature pelanggan sebagai blob data
            $signature_pelanggan_blob = null;
            if ($request->filled('signature_pelanggan')) {
                try {
                    $signature_data = $request->signature_pelanggan;

                    // Decode base64 image
                    if (preg_match('/^data:image\/(\w+);base64,/', $signature_data, $type)) {
                        $signature_data = substr($signature_data, strpos($signature_data, ',') + 1);
                        $signature_data = base64_decode($signature_data);

                        if ($signature_data === false) {
                            throw new \Exception('Base64 decode failed');
                        }

                        // Store binary data directly
                        $signature_pelanggan_blob = $signature_data;
                    }
                } catch (\Exception $e) {
                    Log::error('Error saving signature pelanggan: ' . $e->getMessage());
                    // Continue without signature if error occurs
                }
            }

            // Store signature petugas sebagai blob data
            $signature_petugas_blob = null;
            if ($request->filled('signature_petugas')) {
                try {
                    $signature_data = $request->signature_petugas;

                    // Decode base64 image
                    if (preg_match('/^data:image\/(\w+);base64,/', $signature_data, $type)) {
                        $signature_data = substr($signature_data, strpos($signature_data, ',') + 1);
                        $signature_data = base64_decode($signature_data);

                        if ($signature_data === false) {
                            throw new \Exception('Base64 decode failed');
                        }

                        // Store binary data directly
                        $signature_petugas_blob = $signature_data;
                    }
                } catch (\Exception $e) {
                    Log::error('Error saving signature petugas: ' . $e->getMessage());
                    // Continue without signature if error occurs
                }
            }

            // Note: signature_pelanggan, jabatan_pelanggan, nama_pelanggan, nip_pelanggan tidak ada di tb_permohonan_uji
            // Data ini akan disimpan di tempat lain atau diabaikan, sesuai kebutuhan

            DB::commit();

            // Hapus data pelanggan dari session setelah signature berhasil disimpan
            $request->session()->forget('mobile_sampling_customer_data');

            return response()->json([
                'status' => true,
                'success' => true,
                'pesan' => 'Tanda tangan berhasil disimpan!',
                'redirect' => route('mobile.sampling.success', ['id' => $id])
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mobile Sampling Save Signature Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            $errorMessage = mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8');

            return response()->json([
                'status' => false,
                'success' => false,
                'pesan' => 'Terjadi kesalahan: ' . $errorMessage
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
        }
    }

    /**
     * Show success page
     */
    public function success(Request $request, $id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_sampling_auth', false)) {
            return redirect()->route('mobile.sampling.index', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu');
        }


        $permohonan_uji = PermohonanUji::where('id_permohonan_uji', $id)
            ->with(['customer', 'samples'])
            ->first();

        // Get user level from session
        $userLevel = $request->session()->get('mobile_sampling_user_level');
        $backUrl = $this->getBackUrl($userLevel);

        return view('masterweb::module.mobile.sampling.success', compact('permohonan_uji', 'backUrl'));
    }

    /**
     * Logout from mobile sampling
     */
    public function logout(Request $request, $id)
    {
        $request->session()->forget([
            'mobile_sampling_auth',
            'mobile_sampling_user_id',
            'mobile_sampling_user_name',
            'mobile_sampling_user_username'
        ]);


        // Force save session
        $request->session()->save();

        return redirect()->route('mobile.sampling.index', ['id' => $id])
            ->with('success', 'Anda telah logout');
    }

    /**
     * Get Baku Mutu (parameters) for sample type - for mobile sampling
     */
    public function getBakuMutu(Request $request, $id, $sample_type_id)
    {
        // Check session authentication
        if (!$request->session()->get('mobile_sampling_auth', false)) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $sampletype_bakumutu_details = \Smt\Masterweb\Models\BakuMutu::where('sampletype_id', $sample_type_id)
            ->join('ms_method', function ($join) {
                $join->on('ms_method.id_method', '=', 'tb_baku_mutu.method_id')
                    ->whereNull('ms_method.deleted_at')
                    ->whereNull('tb_baku_mutu.deleted_at');
            })
            ->get();

        return response()->json([
            'success' => 'Ajax request submitted successfully',
            'data' => $sampletype_bakumutu_details
        ]);
    }

    /**
     * Get detail sample type (from packet) - for mobile sampling
     */
    public function getDetailSampleType(Request $request, $id, $sample_type_id)
    {
        // Check session authentication
        if (!$request->session()->get('mobile_sampling_auth', false)) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $id_pakets = explode(",", $sample_type_id);

        $sampletype_details = \Smt\Masterweb\Models\PacketDetail::whereIn('packet_id', $id_pakets)
            ->join('ms_method', function ($join) {
                $join->on('ms_method.id_method', '=', 'ms_packet_detail.method_id')
                    ->whereNull('ms_method.deleted_at')
                    ->whereNull('ms_packet_detail.deleted_at');
            })
            ->get();

        $sampletype = \Smt\Masterweb\Models\Packet::whereIn('id_packet', $id_pakets)->get();

        $methods = array();
        foreach ($sampletype_details as $sampletype_detail) {
            array_push($methods, $sampletype_detail->id_method);
        }

        return response()->json([
            'success' => 'Ajax request submitted successfully',
            'data' => $sampletype_details,
            'price' => $sampletype->sum('price_total_packet'),
            'methods' => $methods,
        ]);
    }

    /**
     * Get petugas sampling from Petugas model based on role and lab
     * Returns array of petugas with their lab assignment
     */
    private function getPetugasSampling()
    {
        $petugas_list = [];


        // Get lab IDs for Mikro and Kimia
        $labMikro = Laboratorium::where('kode_laboratorium', 'MBI')->first();
        $labKimia = Laboratorium::where('kode_laboratorium', 'KIM')->first();


        // Get all petugas with roles
        $allPetugas = Petugas::whereNotNull('role')->get();


        foreach ($allPetugas as $petugas) {
            $roles = is_array($petugas->role) ? $petugas->role : json_decode($petugas->role, true);
            if (!is_array($roles) || empty($roles)) {
                continue;
            }


            $labIds = is_array($petugas->lab_id) ? $petugas->lab_id : json_decode($petugas->lab_id, true);
            if (!is_array($labIds)) {
                $labIds = $labIds ? [$labIds] : [];
            }


            $nama = trim($petugas->nama);
            if (empty($nama)) {
                continue;
            }


            // Check if petugas has role "1" (Register/Pendaftaran)
            if (in_array('1', $roles)) {
                // Check if already added
                $exists = false;
                foreach ($petugas_list as $existing) {
                    if ($existing['name'] === $nama && $existing['lab'] === 'Pendaftaran') {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $petugas_list[] = [
                        'name' => $nama,
                        'lab' => 'Pendaftaran'
                    ];
                }
            }


            // Check if petugas has role "6" (Pengambil Sample) or any role that includes pengambil sample
            // Role 6 = Pengambilan Sample (from verification activity id 6)
            if (in_array('6', $roles)) {
                // Check lab assignment for Mikro
                if ($labMikro && in_array($labMikro->id_laboratorium, $labIds)) {
                    $exists = false;
                    foreach ($petugas_list as $existing) {
                        if ($existing['name'] === $nama && $existing['lab'] === 'Mikrobiologi') {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        $petugas_list[] = [
                            'name' => $nama,
                            'lab' => 'Mikrobiologi'
                        ];
                    }
                }


                // Check lab assignment for Kimia
                if ($labKimia && in_array($labKimia->id_laboratorium, $labIds)) {
                    $exists = false;
                    foreach ($petugas_list as $existing) {
                        if ($existing['name'] === $nama && $existing['lab'] === 'Kimia') {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        $petugas_list[] = [
                            'name' => $nama,
                            'lab' => 'Kimia'
                        ];
                    }
                }
            }
        }


        // Also get from ms_verification_activities as fallback (for backward compatibility)
        $verificationActivity = VerificationActivity::where('id', 6)->first();
        if ($verificationActivity) {
            // Get from register column
            if (!empty($verificationActivity->register) && $verificationActivity->register !== '-' && $verificationActivity->register !== 'NULL') {
                $names = explode(', ', $verificationActivity->register);
                foreach ($names as $name) {
                    $name = trim($name);
                    if (!empty($name)) {
                        $exists = false;
                        foreach ($petugas_list as $existing) {
                            if ($existing['name'] === $name && $existing['lab'] === 'Pendaftaran') {
                                $exists = true;
                                break;
                            }
                        }
                        if (!$exists) {
                            $petugas_list[] = [
                                'name' => $name,
                                'lab' => 'Pendaftaran'
                            ];
                        }
                    }
                }
            }


            // Get from kimia column
            if (!empty($verificationActivity->kimia) && $verificationActivity->kimia !== '-' && $verificationActivity->kimia !== 'NULL') {
                $names = explode(', ', $verificationActivity->kimia);
                foreach ($names as $name) {
                    $name = trim($name);
                    if (!empty($name)) {
                        $exists = false;
                        foreach ($petugas_list as $existing) {
                            if ($existing['name'] === $name && $existing['lab'] === 'Kimia') {
                                $exists = true;
                                break;
                            }
                        }
                        if (!$exists) {
                            $petugas_list[] = [
                                'name' => $name,
                                'lab' => 'Kimia'
                            ];
                        }
                    }
                }
            }


            // Get from mikro column
            if (!empty($verificationActivity->mikro) && $verificationActivity->mikro !== '-' && $verificationActivity->mikro !== 'NULL') {
                $names = explode(', ', $verificationActivity->mikro);
                foreach ($names as $name) {
                    $name = trim($name);
                    if (!empty($name)) {
                        $exists = false;
                        foreach ($petugas_list as $existing) {
                            if ($existing['name'] === $name && $existing['lab'] === 'Mikrobiologi') {
                                $exists = true;
                                break;
                            }
                        }
                        if (!$exists) {
                            $petugas_list[] = [
                                'name' => $name,
                                'lab' => 'Mikrobiologi'
                            ];
                        }
                    }
                }
            }
        }


        // Normalize and deduplicate names
        // Function to normalize name (remove commas, extra spaces, case-insensitive)
        // Names with or without commas are considered the same, regardless of lab
        $normalizeName = function($name) {
            // Remove all commas
            $name = str_replace(',', '', $name);
            // Remove extra spaces
            $name = trim($name);
            $name = preg_replace('/\s+/', ' ', $name); // Multiple spaces to single space
            // Convert to lowercase for comparison
            return strtolower($name);
        };

        // Group by normalized name (ignore lab differences - same name = same person)
        $grouped = [];
        
        foreach ($petugas_list as $petugas) {
            $normalized = $normalizeName($petugas['name']);
            
            // If name already exists (normalized), skip duplicate regardless of lab
            if (!isset($grouped[$normalized])) {
                // Clean up display name (remove comma, normalize spaces)
                $displayName = trim($petugas['name']);
                $displayName = str_replace(',', '', $displayName); // Remove comma from display
                $displayName = preg_replace('/\s+/', ' ', $displayName); // Normalize spaces
                
                $grouped[$normalized] = [
                    'name' => $displayName,
                    'lab' => $petugas['lab'] // Keep first lab found (doesn't matter which lab)
                ];
            }
        }

        // Convert back to array
        $petugas_list = array_values($grouped);

        // Sort by name
        usort($petugas_list, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });


        return $petugas_list;
    }
}