<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smt\Masterweb\Models\User;
use Smt\Masterweb\Models\Sample;
use Smt\Masterweb\Models\PermohonanUji;
use Smt\Masterweb\Models\VerificationActivitySample;
use Smt\Masterweb\Models\VerificationActivity;
use Smt\Masterweb\Models\PenerimaanSample;
use Smt\Masterweb\Models\Laboratorium;
use Smt\Masterweb\Models\Petugas;
use Smt\Masterweb\Models\SampleMethod;
use Smt\Masterweb\Models\SampleResultDetail;
use Smt\Masterweb\Models\SampleAnalitikProgress;
use Smt\Masterweb\Models\Unit;
use Smt\Masterweb\Models\Library;
use Smt\Masterweb\Models\Container;
use Smt\Masterweb\Models\VerifikasiHasil;
use Smt\Masterweb\Models\PengesahanHasil;
use Smt\Masterweb\Models\SampleResult;
use Smt\Masterweb\Models\Method;
use Smt\Masterweb\Models\SampleTypeDetail;
use Smt\Masterweb\Models\JenisMakanan;
use Smt\Masterweb\Models\BakuMutu;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class MobileTestingController extends Controller
{

    /**
     * Parse tanggal mobile: d/m/Y atau d/m/Y H:i (tanpa jam → jam sekarang).
     */
    private function parseMobileStageDate($dateString)
    {
        $parsed = \Smt\Masterweb\Helpers\DateHelper::parseStageDate($dateString);
        if (!$parsed) {
            throw new \InvalidArgumentException('Tanggal kosong atau tidak valid');
        }
        return $parsed;
    }


    /**
     * Mobile testing home: scan or input ID sample
     */
    public function home(Request $request)
    {
        $isAuthenticated = $request->session()->get('mobile_testing_auth', false);
        return view('masterweb::module.mobile.testing.index', [
            'is_authenticated' => $isAuthenticated
        ]);
    }

    /**
     * Handle manual input of ID sample
     */
    public function inputId(Request $request)
    {
        $request->validate([
            'id_sample' => 'required|string'
        ]);

        $id_sample = trim($request->id_sample);
        $sample = Sample::where('id_samples', $id_sample)
            ->with(['permohonanuji.customer', 'samplemethod.laboratorium', 'sampletype'])
            ->first();

        if (!$sample) {
            return redirect()->route('mobile.testing.home')
                ->with('error', 'Data sample tidak ditemukan.');
        }

        // Persist id for convenience
        $request->session()->put('mobile_testing_id_sample', $id_sample);

        if ($request->session()->get('mobile_testing_auth', false)) {
            // Get user info from session
            $isAdmin = $request->session()->get('mobile_testing_is_admin', false);
            $user_id = $request->session()->get('mobile_testing_user_id');
            $userLabCode = null;
            
            if ($user_id) {
                $user = User::with(['laboratorium'])->find($user_id);
                if ($user && $user->laboratorium) {
                    $userLabCode = $user->laboratorium->kode_laboratorium;
                }
            }

            // Get redirect URL based on current step position
            $redirectUrl = $this->getNextStepRedirect($id_sample, $isAdmin, $userLabCode);
            return redirect($redirectUrl);
        }

        return redirect()->route('mobile.testing.login', ['id' => $id_sample]);
    }

    /**
     * Process QR scan
     */
    public function scan(Request $request, $id)
    {
        $sample = Sample::where('id_samples', $id)
            ->with(['permohonanuji.customer', 'samplemethod.laboratorium', 'sampletype'])
            ->first();

        if (!$sample) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data sample tidak ditemukan'
            ]);
        }

        // Persist id for convenience
        $request->session()->put('mobile_testing_id_sample', $id);

        if ($request->session()->get('mobile_testing_auth', false)) {
            // Get user info from session
            $isAdmin = $request->session()->get('mobile_testing_is_admin', false);
            $user_id = $request->session()->get('mobile_testing_user_id');
            $userLabCode = null;
            
            if ($user_id) {
                $user = User::with(['laboratorium'])->find($user_id);
                if ($user && $user->laboratorium) {
                    $userLabCode = $user->laboratorium->kode_laboratorium;
                }
            }

            // Get redirect URL based on current step position
            $redirectUrl = $this->getNextStepRedirect($id, $isAdmin, $userLabCode);
            return redirect($redirectUrl);
        }

        return redirect()->route('mobile.testing.login', ['id' => $id]);
    }

    /**
     * Show login page
     */
    public function login(Request $request, $id)
    {
        $sample = Sample::where('id_samples', $id)
            ->with(['permohonanuji.customer', 'samplemethod.laboratorium'])
            ->first();

        if (!$sample) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data sample tidak ditemukan'
            ]);
        }

        // Check if already authenticated
        if ($request->session()->get('mobile_testing_auth', false)) {
            // Get user info from session
            $isAdmin = $request->session()->get('mobile_testing_is_admin', false);
            $user_id = $request->session()->get('mobile_testing_user_id');
            $userLabCode = null;
            
            if ($user_id) {
                $user = User::with(['laboratorium'])->find($user_id);
                if ($user && $user->laboratorium) {
                    $userLabCode = $user->laboratorium->kode_laboratorium;
                }
            }

            // Get redirect URL based on current step position
            $redirectUrl = $this->getNextStepRedirect($id, $isAdmin, $userLabCode);
            return redirect($redirectUrl);
        }

        return view('masterweb::module.mobile.testing.login', compact('sample'));
    }

    /**
     * Process login
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

        $sample = Sample::where('id_samples', $id)
            ->with(['permohonanuji.customer', 'samplemethod.laboratorium'])
            ->first();

        if (!$sample) {
            return redirect()->route('mobile.testing.home')
                ->with('error', 'Data sample tidak ditemukan.');
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect()->route('mobile.testing.login', ['id' => $id])
                ->with('error', 'Username atau password salah.')
                ->withInput($request->except('password'));
        }

        // Get user level and lab info
        $user = User::with(['getlevel', 'laboratorium'])->find($user->id);
        $userLevel = $user->getlevel->level ?? null;
        $isAdmin = in_array($userLevel, ['elits-dev', 'LAB', 'admin']);

        // Login juga ke web guard supaya endpoint web (contoh: elits-baku-mutu-*.store) tidak Unauthenticated
        Auth::login($user);
        
        // Set session
        $request->session()->put('mobile_testing_auth', true);
        $request->session()->put('mobile_testing_user_id', $user->id);
        $request->session()->put('mobile_testing_user_name', $user->name);
        $request->session()->put('mobile_testing_user_level', $userLevel);
        $request->session()->put('mobile_testing_is_admin', $isAdmin);
        $request->session()->put('mobile_testing_id_sample', $id);

        // Get redirect URL based on current step position
        $redirectUrl = $this->getNextStepRedirect($id, $isAdmin, $user->laboratorium ? $user->laboratorium->kode_laboratorium : null);
        
        return redirect($redirectUrl);
    }

    /**
     * Check if user is at correct step position, redirect if not
     * @param string $current_page - 'status', 'pemeriksaan', 'bacaHasil', 'verifikasiHasil', 'inputTanggalVerifikasi'
     */
    private function checkAndRedirectToCorrectStep($request, $sample_id, $current_page, $lab_id = null)
    {
        // Get user info from session
        $isAdmin = $request->session()->get('mobile_testing_is_admin', false);
        $user_id = $request->session()->get('mobile_testing_user_id');
        $userLabCode = null;
        
        if ($user_id) {
            $user = User::with(['laboratorium'])->find($user_id);
            if ($user && $user->laboratorium) {
                $userLabCode = $user->laboratorium->kode_laboratorium;
            }
        }

        // Sinkronkan auth web guard dengan session mobile (agar route web yang butuh auth tidak 401)
        if ($request->session()->get('mobile_testing_auth', false) && $user_id) {
            if (!Auth::check() || (string) Auth::id() !== (string) $user_id) {
                Auth::loginUsingId($user_id);
            }
        }

        // Get all verification activities for this sample
        $verification_activities = VerificationActivitySample::where('id_sample', $sample_id)
            ->get()
            ->keyBy('id_verification_activity');

       // Get sample with lab info
        $sample = Sample::where('id_samples', $sample_id)
            ->with(['samplemethod.laboratorium'])
            ->first();

        if (!$sample) {
            return null; // Let the calling method handle the error
        }

        // Check step status (these are global for the sample, not per lab)

        $penerima_sample = PenerimaanSample::where('sample_id', $sample_id)
        ->first();

        $step7_verif = $verification_activities->get(7);
        $step7_done = $step7_verif && isset($penerima_sample->disposisi_analis) && $step7_verif->is_done == 1;

        // dd($penerima_sample->disposisi_analis);


        $step2_verif = $verification_activities->get(2);
        $step2_done = $step2_verif && $step2_verif->is_done == 1;

        $step3_verif = $verification_activities->get(3);
        $step3_done = $step3_verif && $step3_verif->is_done == 1;

        $step4_verif = $verification_activities->get(4);
        $step4_done = $step4_verif && $step4_verif->is_done == 1;

        $validation_verif = $verification_activities->get(5);
        $validation_done = $validation_verif && $validation_verif->is_done == 1;

        

        // Determine lab_id if not provided
        // For admin: check all labs and find the most advanced one
        // For analis: use their specific lab
        if (!$lab_id) {
            $relevant_labs = [];
            if ($sample->samplemethod && $sample->samplemethod->count() > 0) {
                foreach ($sample->samplemethod as $sm) {
                    if ($sm->laboratorium) {
                        if ($isAdmin) {
                            // Admin: collect all KIM and MBI labs
                            if (in_array($sm->laboratorium->kode_laboratorium, ['KIM', 'MBI'])) {
                                $relevant_labs[] = $sm->laboratorium->id_laboratorium;
                            }
                        } else {
                            // Analis: only their specific lab
                            if ($userLabCode && $sm->laboratorium->kode_laboratorium == $userLabCode) {
                                $relevant_labs[] = $sm->laboratorium->id_laboratorium;
                                break;
                            }
                        }
                    }
                }
            }

            // For admin: find the most advanced lab (check VerifikasiHasil for each lab)
            if ($isAdmin && !empty($relevant_labs)) {
                // Priority: Find lab that needs inputTanggalVerifikasi > verifikasiHasil > bacaHasil
                foreach ($relevant_labs as $check_lab_id) {
                    $verifikasi_hasil_check = VerifikasiHasil::where('sample_id', $sample_id)
                        ->where('laboratorium_id', $check_lab_id)
                        ->first();

                    // Priority 1: Step 4 done but VerifikasiHasil not completed
                    if ($step4_done && !$verifikasi_hasil_check) {
                        $lab_id = $check_lab_id;
                        break;
                    }
                    // Priority 2: Step 3 done and step 4 not done
                    elseif ($step3_done && !$step4_done) {
                        $lab_id = $check_lab_id;
                        break;
                    }
                    // Priority 3: Step 2 done and step 3 not done
                    elseif ($step2_done && !$step3_done) {
                        $lab_id = $check_lab_id;
                        break;
                    }
                    // Priority 4: Default (use first lab)
                    elseif (!$lab_id) {
                        $lab_id = $check_lab_id;
                    }
                }
            } elseif (!empty($relevant_labs)) {
                // For analis: use their specific lab
                $lab_id = $relevant_labs[0];
            }
        }

        if (!$lab_id) {
            return null; // Let the calling method handle the error
        }

        // Check VerifikasiHasil for the determined lab_id
        $verifikasi_hasil = VerifikasiHasil::where('sample_id', $sample_id)
            ->where('laboratorium_id', $lab_id)
            ->first();

        $method_id = 'bfecda4a-73f2-47d6-9fc3-01f65e0f02a1'; // Default method_id


       
        // Check if user is at correct position for current page

        $pengesahan_hasil = PengesahanHasil::where('sample_id', $sample_id)
            ->where('laboratorium_id', $lab_id)
            ->first();


            
        switch ($current_page) {
            case 'status':
                if ($step7_done && $step7_verif) {
                    return route('mobile.testing.pemeriksaan', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id,
                        'method_id' => $method_id
                    ]);
                }
                if ($step2_done && !$step3_done) {
                    return route('mobile.testing.bacaHasil', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id,
                        'method_id' => $method_id
                    ]);
                }
                if ($step3_done && !$step4_done) {
                    return route('mobile.testing.verifikasiHasil', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id
                    ]);
                }
                if ($step4_done && !$verifikasi_hasil) {
                    return route('mobile.testing.inputTanggalVerifikasi', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id
                    ]);
                }
                if ($step4_done && $verifikasi_hasil && !$pengesahan_hasil) {
                    return route('mobile.testing.pengesahanHasil', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id
                    ]);
                }
                if ($step4_done && $verifikasi_hasil && $pengesahan_hasil && !$validation_done) {
                    return route('mobile.testing.inputValidasi', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id
                    ]);
                }
                if ($step4_done && $verifikasi_hasil && $pengesahan_hasil && $validation_done) {
                    return route('mobile.testing.selesai', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id
                    ]);
                }
                break;

            case 'pemeriksaan':
                if ($step2_done) {
                    if (!$step3_done) {
                        return route('mobile.testing.bacaHasil', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id,
                            'method_id' => $method_id
                        ]);
                    } elseif ($step3_done && !$step4_done) {
                        return route('mobile.testing.verifikasiHasil', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($step4_done && !$verifikasi_hasil) {
                        return route('mobile.testing.inputTanggalVerifikasi', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($step4_done && $verifikasi_hasil && !$pengesahan_hasil) {
                        return route('mobile.testing.pengesahanHasil', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($step4_done && $verifikasi_hasil && $pengesahan_hasil && !$validation_done) {
                        return route('mobile.testing.inputValidasi', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($step4_done && $verifikasi_hasil && $pengesahan_hasil && $validation_done) {
                        return route('mobile.testing.selesai', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    }
                }
                break;

            case 'bacaHasil':
                if (!$step2_done) {
                    return route('mobile.testing.status', ['id' => $sample_id]);
                }
                if ($step3_done) {
                    if (!$step4_done) {
                        return route('mobile.testing.verifikasiHasil', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($step4_done && !$verifikasi_hasil) {
                        return route('mobile.testing.inputTanggalVerifikasi', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($step4_done && $verifikasi_hasil && !$pengesahan_hasil) {
                        return route('mobile.testing.pengesahanHasil', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($step4_done && $verifikasi_hasil && $pengesahan_hasil && !$validation_done) {
                        return route('mobile.testing.inputValidasi', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($step4_done && $verifikasi_hasil && $pengesahan_hasil && $validation_done) {
                        return route('mobile.testing.selesai', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    }
                }
             
                break;

            case 'inputHasil':
                if (!$step2_done) {
                    return route('mobile.testing.status', ['id' => $sample_id]);
                }
                if ($step3_done) {
                    if (!$step4_done) {
                        return route('mobile.testing.verifikasiHasil', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($step4_done && !$verifikasi_hasil) {
                        return route('mobile.testing.inputTanggalVerifikasi', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($step4_done && $verifikasi_hasil && !$pengesahan_hasil) {
                        return route('mobile.testing.pengesahanHasil', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($step4_done && $verifikasi_hasil && $pengesahan_hasil && !$validation_done) {
                        return route('mobile.testing.inputValidasi', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($step4_done && $verifikasi_hasil && $pengesahan_hasil && $validation_done) {
                        return route('mobile.testing.selesai', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    }
                }
                break;

            case 'verifikasiHasil':
                if (!$step3_done) {
                    if ($step2_done) {
                        return route('mobile.testing.bacaHasil', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id,
                            'method_id' => $method_id
                        ]);
                    }
                    return route('mobile.testing.status', ['id' => $sample_id]);
                }
                if ($step4_done) {
                    if (!$verifikasi_hasil) {
                        return route('mobile.testing.inputTanggalVerifikasi', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($verifikasi_hasil && !$pengesahan_hasil) {
                        return route('mobile.testing.pengesahanHasil', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($verifikasi_hasil && $pengesahan_hasil && !$validation_done) {
                        return route('mobile.testing.inputValidasi', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } elseif ($verifikasi_hasil && $pengesahan_hasil && $validation_done) {
                        return route('mobile.testing.selesai', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    } else {
                        return route('mobile.testing.status', ['id' => $sample_id]);
                    }
                }
                break;

            case 'inputTanggalVerifikasi':
                if (!$step4_done) {
                    if (!$step3_done) {
                        return route('mobile.testing.verifikasiHasil', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    }
                } elseif ($verifikasi_hasil && !$pengesahan_hasil) {
                    return route('mobile.testing.pengesahanHasil', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id
                    ]);
                } elseif ($verifikasi_hasil && $pengesahan_hasil && !$validation_done) {
                    return route('mobile.testing.inputValidasi', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id
                    ]);
                } elseif ($verifikasi_hasil && $pengesahan_hasil && $validation_done) {
                    return route('mobile.testing.selesai', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id
                    ]);
                }
                break;

            case 'pengesahanHasil':
                if (!$step4_done || !$verifikasi_hasil) {
                    if (!$step3_done) {
                        return route('mobile.testing.verifikasiHasil', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    }
                    return route('mobile.testing.inputTanggalVerifikasi', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id
                    ]);
                }
                if ($pengesahan_hasil) {
                    if (!$validation_done) {
                        return route('mobile.testing.inputValidasi', [
                            'id' => $sample_id,
                            'lab_id' => $lab_id
                        ]);
                    }
                    return route('mobile.testing.selesai', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id
                    ]);
                }
                break;

            case 'inputValidasi':
                if (!$step4_done || !$verifikasi_hasil) {
                    return route('mobile.testing.inputTanggalVerifikasi', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id
                    ]);
                }
                if (!$pengesahan_hasil) {
                    return route('mobile.testing.pengesahanHasil', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id
                    ]);
                }
                if ($validation_done) {
                    return route('mobile.testing.selesai', [
                        'id' => $sample_id,
                        'lab_id' => $lab_id
                    ]);
                }
                break;
            case 'selesai':
                if (!$validation_done) {
                    return route('mobile.testing.status', ['id' => $sample_id]);
                }
                break;
        }

        // User is at correct position, no redirect needed
        return null;
    }

    /**
     * Determine next step redirect based on current sample position
     */
    private function getNextStepRedirect($sample_id, $isAdmin, $userLabCode = null)
    {
        // Get all verification activities for this sample
        $verification_activities = VerificationActivitySample::where('id_sample', $sample_id)
            ->get()
            ->keyBy('id_verification_activity');

        // Get sample with lab info
        $sample = Sample::where('id_samples', $sample_id)
            ->with(['samplemethod.laboratorium'])
            ->first();

        if (!$sample) {
            return route('mobile.testing.status', ['id' => $sample_id]);
        }

        // Check step status (these are global for the sample, not per lab)
        $step2_verif = $verification_activities->get(2);
        $step2_done = $step2_verif && $step2_verif->is_done == 1;

        $step3_verif = $verification_activities->get(3);
        $step3_done = $step3_verif && $step3_verif->is_done == 1;

        $step4_verif = $verification_activities->get(4);
        $step4_done = $step4_verif && $step4_verif->is_done == 1;

        $validation_verif = $verification_activities->get(5);
        $validation_done = $validation_verif && $validation_verif->is_done == 1;

        $method_id = 'bfecda4a-73f2-47d6-9fc3-01f65e0f02a1'; // Default method_id

        // For admin: check all labs (KIM and MBI) and find the most advanced one
        // For analis: only check their specific lab
        $relevant_labs = [];
        if ($sample->samplemethod && $sample->samplemethod->count() > 0) {
            foreach ($sample->samplemethod as $sm) {
                if ($sm->laboratorium) {
                    if ($isAdmin) {
                        // Admin: collect all KIM and MBI labs
                        if (in_array($sm->laboratorium->kode_laboratorium, ['KIM', 'MBI'])) {
                            $relevant_labs[] = [
                                'id' => $sm->laboratorium->id_laboratorium,
                                'code' => $sm->laboratorium->kode_laboratorium
                            ];
                        }
                    } else {
                        // Analis: only their specific lab
                        if ($userLabCode && $sm->laboratorium->kode_laboratorium == $userLabCode) {
                            $relevant_labs[] = [
                                'id' => $sm->laboratorium->id_laboratorium,
                                'code' => $sm->laboratorium->kode_laboratorium
                            ];
                            break;
                        }
                    }
                }
            }
        }

        // If no lab found, default to status page
        if (empty($relevant_labs)) {
            return route('mobile.testing.status', ['id' => $sample_id]);
        }

        // For each relevant lab, check VerifikasiHasil status
        // Priority: inputTanggalVerifikasi > pengesahanHasil > inputValidasi > verifikasiHasil > bacaHasil > status
        $lab_for_input_tanggal = null;
        $lab_for_pengesahan = null;
        $lab_for_validasi = null;
        $lab_for_verifikasi_hasil = null;
        $lab_for_baca_hasil = null;
        $lab_for_status = null;
        $lab_for_completion = null;

        foreach ($relevant_labs as $lab) {
            $verifikasi_hasil = VerifikasiHasil::where('sample_id', $sample_id)
                ->where('laboratorium_id', $lab['id'])
                ->first();
            $pengesahan_hasil = PengesahanHasil::where('sample_id', $sample_id)
                ->where('laboratorium_id', $lab['id'])
                ->first();

            // Priority 1: Step 4 done but VerifikasiHasil not completed
            if ($step4_done && !$verifikasi_hasil) {
                $lab_for_input_tanggal = $lab;
                break; // Found the most advanced step, use this lab
            }
            // Priority 2: Verifikasi selesai tapi pengesahan belum
            elseif ($step4_done && $verifikasi_hasil && !$pengesahan_hasil) {
                if (!$lab_for_pengesahan) {
                    $lab_for_pengesahan = $lab;
                }
            }
            // Priority 3: Pengesahan selesai tetapi validasi belum
            elseif ($step4_done && $verifikasi_hasil && $pengesahan_hasil && !$validation_done) {
                if (!$lab_for_validasi) {
                    $lab_for_validasi = $lab;
                }
            }
            // Priority 4: Semua tahapan selesai
            elseif ($step4_done && $verifikasi_hasil && $pengesahan_hasil && $validation_done) {
                if (!$lab_for_completion) {
                    $lab_for_completion = $lab;
                }
            }
            // Priority 2: Step 3 done and step 4 not done
            elseif ($step3_done && !$step4_done) {
                if (!$lab_for_verifikasi_hasil) {
                    $lab_for_verifikasi_hasil = $lab;
                }
            }
            // Priority 3: Step 2 done and step 3 not done
            elseif ($step2_done && !$step3_done) {
                if (!$lab_for_baca_hasil) {
                    $lab_for_baca_hasil = $lab;
                }
            }
            // Priority 4: Default (step 1 or all completed)
            elseif (!$lab_for_status) {
                $lab_for_status = $lab;
            }
        }

        // Determine redirect based on priority (highest priority first)
        // 1. If step 4 done but VerifikasiHasil not completed -> redirect to inputTanggalVerifikasi
        if ($lab_for_input_tanggal) {
            return route('mobile.testing.inputTanggalVerifikasi', [
                'id' => $sample_id,
                'lab_id' => $lab_for_input_tanggal['id']
            ]);
        }

        // 2. If verifikasi selesai tapi pengesahan belum -> pengesahan
        if ($lab_for_pengesahan) {
            return route('mobile.testing.pengesahanHasil', [
                'id' => $sample_id,
                'lab_id' => $lab_for_pengesahan['id']
            ]);
        }

        // 3. If pengesahan selesai tapi validasi belum -> input validasi
        if ($lab_for_validasi) {
            return route('mobile.testing.inputValidasi', [
                'id' => $sample_id,
                'lab_id' => $lab_for_validasi['id']
            ]);
        }

    // 4. Semua tahapan selesai -> halaman selesai
    if ($lab_for_completion) {
        return route('mobile.testing.selesai', [
            'id' => $sample_id,
            'lab_id' => $lab_for_completion['id']
        ]);
    }

    // 5. If step 3 done and step 4 not done -> redirect to verifikasiHasil
        if ($lab_for_verifikasi_hasil) {
            return route('mobile.testing.verifikasiHasil', [
                'id' => $sample_id,
                'lab_id' => $lab_for_verifikasi_hasil['id']
            ]);
        }

        // 5. If step 2 done and step 3 not done -> redirect to bacaHasil
        if ($lab_for_baca_hasil) {
            return route('mobile.testing.bacaHasil', [
                'id' => $sample_id,
                'lab_id' => $lab_for_baca_hasil['id'],
                'method_id' => $method_id
            ]);
        }

        // 6. Default: redirect to status (Step 1: Penerimaan Sample or all steps completed)
        return route('mobile.testing.status', ['id' => $sample_id]);
    }

    /**
     * Check status and show appropriate form
     */
    public function status(Request $request, $id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_testing_auth', false)) {
            return redirect()->route('mobile.testing.login', ['id' => $id]);
        }

        // Check and redirect to correct step if user is at wrong position
        $redirectUrl = $this->checkAndRedirectToCorrectStep($request, $id, 'status');
        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        $sample = Sample::where('id_samples', $id)
            ->with(['permohonanuji.customer', 'samplemethod.method', 'samplemethod.laboratorium', 'sampletype'])
            ->first();

        if (!$sample) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data sample tidak ditemukan'
            ]);
        }

        // Get laboratorium from first samplemethod
        $laboratorium = null;
        if ($sample->samplemethod && $sample->samplemethod->count() > 0) {
            $laboratorium = $sample->samplemethod->first()->laboratorium;
        }
        
        if (!$laboratorium) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data laboratorium tidak ditemukan'
            ]);
        }
        
        // Get user info from session first
        $user_id = $request->session()->get('mobile_testing_user_id');
        $isAdmin = $request->session()->get('mobile_testing_is_admin', false);
        $user = null;
        $userLabCode = null;
        
        if ($user_id) {
            $user = User::with(['getlevel', 'laboratorium'])->find($user_id);
            if ($user && $user->laboratorium) {
                $userLabCode = $user->laboratorium->kode_laboratorium;
            }
        }

        
        // Jika analis, pastikan hanya lab kimia atau mikro yang bisa diakses
        $isAnalis = !$isAdmin && $userLabCode && in_array($userLabCode, ['KIM', 'MBI']);

        // Get all samples in the same permohonan uji with kimia or mikro lab
        // Group by permohonan_uji_id and filter by lab kimia (KIM) or mikrobiologi (MBI)
        $all_samples = Sample::where('permohonan_uji_id', $sample->permohonan_uji_id)
            ->whereHas('samplemethod.laboratorium', function($query) {
                $query->whereIn('kode_laboratorium', ['KIM', 'MBI']);
            })
            ->with(['samplemethod.method', 'samplemethod.laboratorium', 'sampletype', 'permohonanuji'])
            ->get();
        
        // Alias for group_samples_all (used in view)
        $group_samples_all = $all_samples;
        
        // Validasi: Jika analis, pastikan sample memiliki lab yang sesuai dengan lab analis
        if ($isAnalis && $userLabCode) {
            $sample_has_user_lab = false;
            if ($sample->samplemethod && $sample->samplemethod->count() > 0) {
                foreach ($sample->samplemethod as $sm) {
                    if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == $userLabCode) {
                        $sample_has_user_lab = true;
                        break;
                    }
                }
            }
            
            // Cek juga apakah ada sample di permohonan uji ini yang sesuai dengan lab analis
            $samples_with_user_lab = $all_samples->filter(function($s) use ($userLabCode) {
                if ($s->samplemethod && $s->samplemethod->count() > 0) {
                    foreach ($s->samplemethod as $sm) {
                        if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == $userLabCode) {
                            return true;
                        }
                    }
                }
                return false;
            });
            
            if (!$sample_has_user_lab || $samples_with_user_lab->isEmpty()) {
                $lab_name = $userLabCode == 'KIM' ? 'Kimia' : 'Mikrobiologi';
                return view('masterweb::module.mobile.testing.error', [
                    'message' => 'Bukan sample dari lab ' . $lab_name . '. Anda hanya dapat mengakses sample dari lab ' . $lab_name . '.'
                ]);
            }
        }

        
        // Separate samples by lab for admin, or filter by user lab for analis
        $group_samples_kim = collect();
        $group_samples_mbi = collect();
        
        foreach ($all_samples as $s) {
            $has_kim = false;
            $has_mbi = false;

         
            
            if ($s->samplemethod && $s->samplemethod->count() > 0) {
                foreach ($s->samplemethod as $sm) {
                    if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'KIM') {
                        $has_kim = true;
                    }
                    if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'MBI') {
                        $has_mbi = true;
                    }
                }
            }
            
            // For admin, add to both groups if sample has both labs
            // For analis, only add to matching lab
            if ($isAdmin) {
                if ($has_kim) {
                    $group_samples_kim->push($s);
                }
                if ($has_mbi) {
                    $group_samples_mbi->push($s);
                }
            } else {
                // Analis: only add to matching lab
                if ($isAnalis) {
                    if ($userLabCode == 'KIM' && $has_kim) {
                        $group_samples_kim->push($s);
                    }
                    if ($userLabCode == 'MBI' && $has_mbi) {
                        $group_samples_mbi->push($s);
                    }
                }
            }
        }

        
        // For backward compatibility, keep group_samples as all samples for admin
        // For analis, filter by their lab
        if ($isAdmin) {
            $group_samples = $all_samples;
        } else {
            // Analis: only samples from their lab
            if ($isAnalis && $userLabCode && in_array($userLabCode, ['KIM', 'MBI'])) {
                $group_samples = $all_samples->filter(function($s) use ($userLabCode) {
                    if ($s->samplemethod && $s->samplemethod->count() > 0) {
                        foreach ($s->samplemethod as $sm) {
                            if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == $userLabCode) {
                                return true;
                            }
                        }
                    }
                    return false;
                });
            } else {
                $group_samples = $all_samples;
            }
        }

        // Get verification activities for all samples in group
        $verification_activities = VerificationActivitySample::whereIn('id_sample', $group_samples->pluck('id_samples'))
            ->get()
            ->groupBy('id_sample');

        // Check if step 2 (Pemeriksaan / Analitik) is done and step 3 (Input / Output Hasil Px) is not done
        // If so, redirect directly to baca-hasil
        $verif_sample = $verification_activities->get($sample->id_samples);
        if ($verif_sample) {
            $step2_verif = $verif_sample->where('id_verification_activity', 2)->first();
            $step3_verif = $verif_sample->where('id_verification_activity', 3)->first();
            
            // Check if step 2 is done (is_done = 1) and step 3 is not done (is_done = 0 or null)
            $step2_done = $step2_verif && $step2_verif->is_done == 1;
            $step3_not_done = !$step3_verif || $step3_verif->is_done == 0 || $step3_verif->is_done === null;
            
            if ($step2_done && $step3_not_done) {
                // Determine lab_id for redirect
                $lab_id_for_redirect = null;
                $method_id = 'bfecda4a-73f2-47d6-9fc3-01f65e0f02a1'; // Default method_id
                
                if ($isAnalis && $userLabCode) {
                    // For analis, use their lab
                    if ($userLabCode == 'KIM' && $group_samples_kim->isNotEmpty()) {
                        $first_kim_sample = $group_samples_kim->first();
                        if ($first_kim_sample && $first_kim_sample->samplemethod) {
                            foreach ($first_kim_sample->samplemethod as $sm) {
                                if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'KIM') {
                                    $lab_id_for_redirect = $sm->laboratorium->id_laboratorium;
                                    break;
                                }
                            }
                        }
                    } elseif ($userLabCode == 'MBI' && $group_samples_mbi->isNotEmpty()) {
                        $first_mbi_sample = $group_samples_mbi->first();
                        if ($first_mbi_sample && $first_mbi_sample->samplemethod) {
                            foreach ($first_mbi_sample->samplemethod as $sm) {
                                if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'MBI') {
                                    $lab_id_for_redirect = $sm->laboratorium->id_laboratorium;
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    // For admin, use first lab found from sample
                    if ($sample->samplemethod && $sample->samplemethod->count() > 0) {
                        foreach ($sample->samplemethod as $sm) {
                            if ($sm->laboratorium && in_array($sm->laboratorium->kode_laboratorium, ['KIM', 'MBI'])) {
                                $lab_id_for_redirect = $sm->laboratorium->id_laboratorium;
                                break;
                            }
                        }
                    }
                }
                
                if ($lab_id_for_redirect) {
                    return redirect()->route('mobile.testing.bacaHasil', [
                        'id' => $sample->id_samples,
                        'lab_id' => $lab_id_for_redirect,
                        'method_id' => $method_id
                    ]);
                }
            }
        }

        // Determine current status
        // Check if penerimaan sample (step 7) is done for all samples
        $penerimaan_done = true;
        $penerimaan_samples = [];
        
        foreach ($group_samples as $s) {
            $verif = $verification_activities->get($s->id_samples);
            $penerimaan_verif = $verif ? $verif->where('id_verification_activity', 7)->first() : null;
            
            if (!$penerimaan_verif || !$penerimaan_verif->is_done) {
                $penerimaan_done = false;
            }
            
            $penerimaan_samples[] = [
                'sample' => $s,
                'verification' => $penerimaan_verif,
                'penerimaan_data' => PenerimaanSample::where('sample_id', $s->id_samples)->first()
            ];
        }


        // Get laboratorium from first samplemethod
        $laboratorium = null;
        if ($sample->samplemethod && $sample->samplemethod->count() > 0) {
            $laboratorium = $sample->samplemethod->first()->laboratorium;
        }
        
        if (!$laboratorium) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data laboratorium tidak ditemukan'
            ]);
        }

        // Get lists for form
        $verificationActivity = VerificationActivity::all()->keyBy('id')->toArray();
        
        // Penerima sampel list
        $penerima_sampel_list = [];
        if (isset($verificationActivity[7])) {
            $activity7 = (object) $verificationActivity[7];
            if ($sample->permohonanuji->is_sampling == 1) {
                // Jika admin, ambil semua lab. Jika analis, hanya KIM atau MBI
                if ($isAdmin) {
                    // Admin: ambil semua
                    $penerima_sampel_list = array_merge(
                        array_filter(explode(', ', $activity7->mikro ?? '')),
                        array_filter(explode(', ', $activity7->kimia ?? '')),
                        array_filter(explode(', ', $activity7->klnik ?? ''))
                    );
                    $penerima_sampel_list = array_unique($penerima_sampel_list);
                } elseif ($isAnalis) {
                    // Analis: hanya KIM atau MBI sesuai lab user
                    if ($userLabCode == 'MBI') {
                        $penerima_sampel_list = array_filter(explode(', ', $activity7->mikro ?? ''));
                    } elseif ($userLabCode == 'KIM') {
                        $penerima_sampel_list = array_filter(explode(', ', $activity7->kimia ?? ''));
                    }
                } else {
                    // Default: berdasarkan lab sample
                    if ($laboratorium->kode_laboratorium == 'MBI') {
                        $penerima_sampel_list = array_filter(explode(', ', $activity7->mikro ?? ''));
                    } elseif ($laboratorium->kode_laboratorium == 'KIM') {
                        $penerima_sampel_list = array_filter(explode(', ', $activity7->kimia ?? ''));
                    } else {
                        $penerima_sampel_list = array_filter(explode(', ', $activity7->klnik ?? ''));
                    }
                }
            } else {
                $activity1 = (object) $verificationActivity[1];
                $penerima_sampel_list = array_filter(explode(', ', $activity1->register ?? ''));
                $penerima_sampel_list = array_merge($penerima_sampel_list, array_filter(explode(', ', $activity1->klinik ?? '')));
            }
        }

        // Get lab IDs for KIM and MBI (needed for both analis and koordinator)
        $labKim = Laboratorium::where('kode_laboratorium', 'KIM')->first();
        $labMbi = Laboratorium::where('kode_laboratorium', 'MBI')->first();
        $labKimId = $labKim ? $labKim->id_laboratorium : null;
        $labMbiId = $labMbi ? $labMbi->id_laboratorium : null;

        // Function to normalize name (remove accents, commas, extra spaces)
        $normalizeName = function($name) {
            // Remove commas
            $name = str_replace(',', '', $name);
            // Remove accents/diacritics
            $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
            // Remove extra spaces
            $name = trim($name);
            $name = preg_replace('/\s+/', ' ', $name);
            return $name;
        };
        
        // Get analis from Petugas with role 2 or 3 and matching lab_id
        // Also use VerificationActivity as reference/fallback
        $analis_list = [];
        $analis_list_kim = [];
        $analis_list_mbi = [];
        
        // First, get from Petugas with role 2 or 3
        $allPetugasAnalis = Petugas::whereNotNull('role')->get();
        
        foreach ($allPetugasAnalis as $petugas) {
            $roles = is_array($petugas->role) ? $petugas->role : json_decode($petugas->role, true);
            if (!is_array($roles) || (!in_array('2', $roles) && !in_array('3', $roles))) {
                continue; // Skip if doesn't have role 2 or 3
            }
            
            $labIds = is_array($petugas->lab_id) ? $petugas->lab_id : json_decode($petugas->lab_id, true);
            if (!is_array($labIds)) {
                $labIds = $labIds ? [$labIds] : [];
            }
            
            $nama = trim($petugas->nama);
            if (empty($nama)) {
                continue;
            }
            
            // Normalize name (remove accents and commas)
            $nama_normalized = $normalizeName($nama);
            
            // Check if petugas has lab KIM
            if ($labKimId && in_array($labKimId, $labIds)) {
                if (!in_array($nama_normalized, $analis_list_kim)) {
                    $analis_list_kim[] = $nama_normalized;
                }
            }
            
            // Check if petugas has lab MBI
            if ($labMbiId && in_array($labMbiId, $labIds)) {
                if (!in_array($nama_normalized, $analis_list_mbi)) {
                    $analis_list_mbi[] = $nama_normalized;
                }
            }
        }
        
        // Also get from VerificationActivity as reference/fallback
        // Activity id 2 = Analis (Input Hasil/Analitik)
        // Activity id 3 = Input/Output Hasil
        if (isset($verificationActivity[2])) {
            $activity2 = (object) $verificationActivity[2];
            // Get kimia analis
            $analis_kimia_from_activity = array_filter(explode(', ', $activity2->kimia ?? ''));
            foreach ($analis_kimia_from_activity as $nama) {
                $nama_normalized = $normalizeName(trim($nama));
                if (!empty($nama_normalized) && !in_array($nama_normalized, $analis_list_kim)) {
                    $analis_list_kim[] = $nama_normalized;
                }
            }
            // Get mikro analis
            $analis_mikro_from_activity = array_filter(explode(', ', $activity2->mikro ?? ''));
            foreach ($analis_mikro_from_activity as $nama) {
                $nama_normalized = $normalizeName(trim($nama));
                if (!empty($nama_normalized) && !in_array($nama_normalized, $analis_list_mbi)) {
                    $analis_list_mbi[] = $nama_normalized;
                }
            }
        }
        
        if (isset($verificationActivity[3])) {
            $activity3 = (object) $verificationActivity[3];
            // Get kimia analis from activity 3
            $analis_kimia_from_activity3 = array_filter(explode(', ', $activity3->kimia ?? ''));
            foreach ($analis_kimia_from_activity3 as $nama) {
                $nama_normalized = $normalizeName(trim($nama));
                if (!empty($nama_normalized) && !in_array($nama_normalized, $analis_list_kim)) {
                    $analis_list_kim[] = $nama_normalized;
                }
            }
            // Get mikro analis from activity 3
            $analis_mikro_from_activity3 = array_filter(explode(', ', $activity3->mikro ?? ''));
            foreach ($analis_mikro_from_activity3 as $nama) {
                $nama_normalized = $normalizeName(trim($nama));
                if (!empty($nama_normalized) && !in_array($nama_normalized, $analis_list_mbi)) {
                    $analis_list_mbi[] = $nama_normalized;
                }
            }
        }
        
        // Sort lists
        sort($analis_list_kim);
        sort($analis_list_mbi);
        
        // Set main list based on user role
        if ($isAdmin) {
            // Admin: combine both lists
            $analis_list = array_unique(array_merge($analis_list_kim, $analis_list_mbi));
            sort($analis_list);
        } elseif ($isAnalis) {
            // Analis: only matching lab
            if ($userLabCode == 'KIM') {
                $analis_list = $analis_list_kim;
            } elseif ($userLabCode == 'MBI') {
                $analis_list = $analis_list_mbi;
            }
        } else {
            // Default: based on sample lab
            if ($laboratorium->kode_laboratorium == 'KIM') {
                $analis_list = $analis_list_kim;
            } elseif ($laboratorium->kode_laboratorium == 'MBI') {
                $analis_list = $analis_list_mbi;
            }
        }

        // Get koordinator kesmas from Petugas with role 8 and matching lab_id
        $koordinator_kesmas_list = [];
        $koordinator_kesmas_list_kim = [];
        $koordinator_kesmas_list_mbi = [];
        
        // Get all petugas with role 8
        $allPetugas = Petugas::whereNotNull('role')->get();
        
        foreach ($allPetugas as $petugas) {
            $roles = is_array($petugas->role) ? $petugas->role : json_decode($petugas->role, true);
            if (!is_array($roles) || !in_array('8', $roles)) {
                continue; // Skip if doesn't have role 8
            }
            
            $labIds = is_array($petugas->lab_id) ? $petugas->lab_id : json_decode($petugas->lab_id, true);
            if (!is_array($labIds)) {
                $labIds = $labIds ? [$labIds] : [];
            }
            
            $nama = trim($petugas->nama);
            if (empty($nama)) {
                continue;
            }
            
            // Normalize name (remove accents and commas)
            $nama_normalized = $normalizeName($nama);
            
            // Check if petugas has lab KIM
            if ($labKimId && in_array($labKimId, $labIds)) {
                if (!in_array($nama_normalized, $koordinator_kesmas_list_kim)) {
                    $koordinator_kesmas_list_kim[] = $nama_normalized;
                }
            }
            
            // Check if petugas has lab MBI
            if ($labMbiId && in_array($labMbiId, $labIds)) {
                if (!in_array($nama_normalized, $koordinator_kesmas_list_mbi)) {
                    $koordinator_kesmas_list_mbi[] = $nama_normalized;
                }
            }
        }
        
        // Sort lists
        sort($koordinator_kesmas_list_kim);
        sort($koordinator_kesmas_list_mbi);
        
        // Set main list based on user role
        if ($isAdmin) {
            // Admin: combine both lists
            $koordinator_kesmas_list = array_unique(array_merge($koordinator_kesmas_list_kim, $koordinator_kesmas_list_mbi));
            sort($koordinator_kesmas_list);
        } elseif ($isAnalis) {
            // Analis: only matching lab
            if ($userLabCode == 'KIM') {
                $koordinator_kesmas_list = $koordinator_kesmas_list_kim;
            } elseif ($userLabCode == 'MBI') {
                $koordinator_kesmas_list = $koordinator_kesmas_list_mbi;
            }
        } else {
            // Default: based on sample lab
            if ($laboratorium->kode_laboratorium == 'KIM') {
                $koordinator_kesmas_list = $koordinator_kesmas_list_kim;
            } elseif ($laboratorium->kode_laboratorium == 'MBI') {
                $koordinator_kesmas_list = $koordinator_kesmas_list_mbi;
            }
        }

        // Check TTE
        $use_tte = config('app.bsre_use', false);

        // Get existing penerimaan data - check for each sample with its own lab
        $existing_penerimaan = [];
        foreach ($group_samples as $s) {
            // Get laboratorium from sample's samplemethod (kimia or mikro)
            $s_lab = null;
            if ($s->samplemethod && $s->samplemethod->count() > 0) {
                foreach ($s->samplemethod as $sm) {
                    if ($sm->laboratorium) {
                        if ($isAdmin) {
                            // Admin: accept any KIM or MBI lab
                            if (in_array($sm->laboratorium->kode_laboratorium, ['KIM', 'MBI'])) {
                                $s_lab = $sm->laboratorium;
                                break;
                            }
                        } else {
                            // Analis: only accept their specific lab
                            if ($isAnalis && $userLabCode && $sm->laboratorium->kode_laboratorium == $userLabCode) {
                                $s_lab = $sm->laboratorium;
                                break;
                            }
                        }
                    }
                }
            }
            
            if ($s_lab) {
                $penerimaan = PenerimaanSample::where('sample_id', $s->id_samples)
                    ->where('laboratorium_id', $s_lab->id_laboratorium)
                    ->first();
                if ($penerimaan) {
                    $existing_penerimaan[$s->id_samples] = $penerimaan;
                }
            }
        }

        // Determine steps status
        $step_penerima_done = false;
        $step_penerima_done_kim = false;
        $step_penerima_done_mbi = false;
        $step_koordinator_done = false;
        $step_analis_done = false;

        // Check step 1: Penerima Sampel - check separately for each lab if admin has both labs
        $has_kim = $group_samples_kim->count() > 0;
        $has_mbi = $group_samples_mbi->count() > 0;
        $has_both_labs = $has_kim && $has_mbi && $isAdmin;

        
        if ($has_both_labs) {
            // Check KIM samples
            $samples_with_penerima_kim = 0;
            $samples_with_pengawetan_kim = 0;
            foreach ($group_samples_kim as $s) {
                $s_lab = null;
                if ($s->samplemethod && $s->samplemethod->count() > 0) {
                    foreach ($s->samplemethod as $sm) {
                        if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'KIM') {
                            $s_lab = $sm->laboratorium;
                            break;
                        }
                    }
                }
                
                if ($s_lab) {
                    $penerimaan = PenerimaanSample::where('sample_id', $s->id_samples)
                        ->where('laboratorium_id', $s_lab->id_laboratorium)
                        ->first();
                    
                    if ($penerimaan && !empty($penerimaan->penerima_sampel) && !empty($penerimaan->penerima_tanggal)) {
                        $samples_with_penerima_kim++;
                    }
                    
                    if ($penerimaan && (!empty($penerimaan->pengawetan_oleh) || !empty($penerimaan->pengawetan_dengan))) {
                        $samples_with_pengawetan_kim++;
                    }
                }
            }
            
            // Check MBI samples
            $samples_with_penerima_mbi = 0;
            $samples_with_pengawetan_mbi = 0;
            foreach ($group_samples_mbi as $s) {
                $s_lab = null;
                if ($s->samplemethod && $s->samplemethod->count() > 0) {
                    foreach ($s->samplemethod as $sm) {
                        if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'MBI') {
                            $s_lab = $sm->laboratorium;
                            break;
                        }
                    }
                }
                
                if ($s_lab) {
                    $penerimaan = PenerimaanSample::where('sample_id', $s->id_samples)
                        ->where('laboratorium_id', $s_lab->id_laboratorium)
                        ->first();
                    
                    if ($penerimaan && !empty($penerimaan->penerima_sampel) && !empty($penerimaan->penerima_tanggal)) {
                        $samples_with_penerima_mbi++;
                    }
                    
                    if ($penerimaan && (!empty($penerimaan->pengawetan_oleh) || !empty($penerimaan->pengawetan_dengan))) {
                        $samples_with_pengawetan_mbi++;
                    }
                }
            }
            
            // Step done only if both labs are complete
            $step_penerima_done_kim = ($samples_with_penerima_kim == $group_samples_kim->count() && 
                                      $samples_with_pengawetan_kim == $group_samples_kim->count() && 
                                      $group_samples_kim->count() > 0);
            $step_penerima_done_mbi = ($samples_with_penerima_mbi == $group_samples_mbi->count() && 
                                      $samples_with_pengawetan_mbi == $group_samples_mbi->count() && 
                                      $group_samples_mbi->count() > 0);
            $step_penerima_done = ($step_penerima_done_kim && $step_penerima_done_mbi);
        } else {
            // Single lab or analis: check all samples
            $samples_with_penerima = 0;
            $samples_with_pengawetan = 0;
            $total_samples = count($group_samples);
            $total_samples_kim_check = 0;
            $total_samples_mbi_check = 0;
            $samples_with_penerima_kim = 0;
            $samples_with_pengawetan_kim = 0;
            $samples_with_penerima_mbi = 0;
            $samples_with_pengawetan_mbi = 0;


            foreach ($group_samples as $s) {
                // Check which lab this sample belongs to
                $is_kim = false;
                $is_mbi = false;
                $s_lab_kim = null;
                $s_lab_mbi = null;
                if ($s->samplemethod && $s->samplemethod->count() > 0) {
                    foreach ($s->samplemethod as $sm) {
                        if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'KIM') {
                            $is_kim = true;
                            if (!$s_lab_kim) { // Only set once
                                $s_lab_kim = $sm->laboratorium;
                                $total_samples_kim_check++;
                            }
                        } elseif ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'MBI') {
                            $is_mbi = true;
                            if (!$s_lab_mbi) { // Only set once
                                $s_lab_mbi = $sm->laboratorium;
                                $total_samples_mbi_check++;
                            }
                        }
                    }
                }
                
                // Get penerimaan for KIM lab if exists
                $penerimaan_kim = null;
                if ($is_kim && $s_lab_kim) {
                    $penerimaan_kim = PenerimaanSample::where('sample_id', $s->id_samples)
                        ->where('laboratorium_id', $s_lab_kim->id_laboratorium)
                        ->first();
                }
                
                // Get penerimaan for MBI lab if exists
                $penerimaan_mbi = null;
                if ($is_mbi && $s_lab_mbi) {
                    $penerimaan_mbi = PenerimaanSample::where('sample_id', $s->id_samples)
                        ->where('laboratorium_id', $s_lab_mbi->id_laboratorium)
                        ->first();
                }
                
                // Use existing_penerimaan as fallback for general check
                $penerimaan = isset($existing_penerimaan[$s->id_samples]) ? $existing_penerimaan[$s->id_samples] : null;
                
                if ($penerimaan && !empty($penerimaan->penerima_sampel) && !empty($penerimaan->penerima_tanggal)) {
                    $samples_with_penerima++;
                }
                
                if ($penerimaan && (!empty($penerimaan->pengawetan_oleh) || !empty($penerimaan->pengawetan_dengan))) {
                    $samples_with_pengawetan++;
                }
                
                // Check KIM specifically
                if ($penerimaan_kim && !empty($penerimaan_kim->penerima_sampel) && !empty($penerimaan_kim->penerima_tanggal)) {
                    $samples_with_penerima_kim++;
                }
                
                // Check pengawetan KIM: harus ada pengawetan_oleh ATAU pengawetan_dengan yang tidak kosong
                if ($penerimaan_kim) {
                    $has_pengawetan_kim = false;
                    if (!empty($penerimaan_kim->pengawetan_oleh)) {
                        $has_pengawetan_kim = true;
                    } elseif (!empty($penerimaan_kim->pengawetan_dengan) && trim($penerimaan_kim->pengawetan_dengan) !== '') {
                        $has_pengawetan_kim = true;
                    }
                    if ($has_pengawetan_kim) {
                        $samples_with_pengawetan_kim++;
                    }
                }
                
                // Check MBI specifically
                if ($penerimaan_mbi && !empty($penerimaan_mbi->penerima_sampel) && !empty($penerimaan_mbi->penerima_tanggal)) {
                    $samples_with_penerima_mbi++;
                }
                
                // Check pengawetan MBI: harus ada pengawetan_oleh ATAU pengawetan_dengan yang tidak kosong
                if ($penerimaan_mbi) {
                    $has_pengawetan_mbi = false;
                    if (!empty($penerimaan_mbi->pengawetan_oleh)) {
                        $has_pengawetan_mbi = true;
                    } elseif (!empty($penerimaan_mbi->pengawetan_dengan) && trim($penerimaan_mbi->pengawetan_dengan) !== '') {
                        $has_pengawetan_mbi = true;
                    }
                    if ($has_pengawetan_mbi) {
                        $samples_with_pengawetan_mbi++;
                    }
                }
                
                // Debug log untuk setiap sample
                if ($is_kim || $is_mbi) {
                    Log::debug('MobileTesting: Sample check', [
                        'sample_id' => $s->id_samples,
                        'is_kim' => $is_kim,
                        'is_mbi' => $is_mbi,
                        'has_penerimaan_kim' => $penerimaan_kim ? 'yes' : 'no',
                        'has_penerimaan_mbi' => $penerimaan_mbi ? 'yes' : 'no',
                        'penerimaan_kim_penerima_sampel' => $penerimaan_kim ? ($penerimaan_kim->penerima_sampel ?? 'null') : 'N/A',
                        'penerimaan_kim_penerima_tanggal' => $penerimaan_kim ? ($penerimaan_kim->penerima_tanggal ?? 'null') : 'N/A',
                        'penerimaan_kim_pengawetan_oleh' => $penerimaan_kim ? ($penerimaan_kim->pengawetan_oleh ?? 'null') : 'N/A',
                        'penerimaan_kim_pengawetan_dengan' => $penerimaan_kim ? ($penerimaan_kim->pengawetan_dengan ?? 'null') : 'N/A',
                        'penerimaan_mbi_penerima_sampel' => $penerimaan_mbi ? ($penerimaan_mbi->penerima_sampel ?? 'null') : 'N/A',
                        'penerimaan_mbi_penerima_tanggal' => $penerimaan_mbi ? ($penerimaan_mbi->penerima_tanggal ?? 'null') : 'N/A',
                        'penerimaan_mbi_pengawetan_oleh' => $penerimaan_mbi ? ($penerimaan_mbi->pengawetan_oleh ?? 'null') : 'N/A',
                        'penerimaan_mbi_pengawetan_dengan' => $penerimaan_mbi ? ($penerimaan_mbi->pengawetan_dengan ?? 'null') : 'N/A',
                    ]);
                }
            }

            $step_penerima_done = ($samples_with_penerima == $total_samples && 
                                  $samples_with_pengawetan == $total_samples && 
                                  $total_samples > 0);

            
            // For single lab or analis, calculate per lab
            if ($total_samples_kim_check > 0) {
                $step_penerima_done_kim = ($samples_with_penerima_kim == $total_samples_kim_check && 
                                          $samples_with_pengawetan_kim == $total_samples_kim_check);
                Log::debug('MobileTesting: Step 1 KIM status for analis/single lab', [
                    'total_samples_kim_check' => $total_samples_kim_check,
                    'samples_with_penerima_kim' => $samples_with_penerima_kim,
                    'samples_with_pengawetan_kim' => $samples_with_pengawetan_kim,
                    'step_penerima_done_kim' => $step_penerima_done_kim,
                    'isAnalis' => $isAnalis,
                    'userLabCode' => $userLabCode,
                    'condition_penerima' => $samples_with_penerima_kim == $total_samples_kim_check,
                    'condition_pengawetan' => $samples_with_pengawetan_kim == $total_samples_kim_check
                ]);
            } else {
                $step_penerima_done_kim = false;
            }
            
            if ($total_samples_mbi_check > 0) {
                $step_penerima_done_mbi = ($samples_with_penerima_mbi == $total_samples_mbi_check && 
                                          $samples_with_pengawetan_mbi == $total_samples_mbi_check);
                Log::debug('MobileTesting: Step 1 MBI status for analis/single lab', [
                    'total_samples_mbi_check' => $total_samples_mbi_check,
                    'samples_with_penerima_mbi' => $samples_with_penerima_mbi,
                    'samples_with_pengawetan_mbi' => $samples_with_pengawetan_mbi,
                    'step_penerima_done_mbi' => $step_penerima_done_mbi,
                    'isAnalis' => $isAnalis,
                    'userLabCode' => $userLabCode,
                    'condition_penerima' => $samples_with_penerima_mbi == $total_samples_mbi_check,
                    'condition_pengawetan' => $samples_with_pengawetan_mbi == $total_samples_mbi_check
                ]);
            } else {
                $step_penerima_done_mbi = false;
            }
        }

        // Check step 2: Koordinator Kesmas - check if ALL samples have disposisi data for their respective labs
        // If admin and has both KIM and MBI labs, check separately for each lab
        
        if ($has_both_labs) {
            // Check KIM samples
            $samples_with_disposisi_koordinator_kim = 0;
            foreach ($group_samples_kim as $s) {
                $s_lab = null;
                if ($s->samplemethod && $s->samplemethod->count() > 0) {
                    foreach ($s->samplemethod as $sm) {
                        if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'KIM') {
                            $s_lab = $sm->laboratorium;
                            break;
                        }
                    }
                }
                
                if ($s_lab) {
                    $penerimaan = PenerimaanSample::where('sample_id', $s->id_samples)
                        ->where('laboratorium_id', $s_lab->id_laboratorium)
                        ->first();
                    
                    if ($penerimaan && !empty($penerimaan->disposisi_koordinator_kesmas) && !empty($penerimaan->disposisi_tanggal)) {
                        $samples_with_disposisi_koordinator_kim++;
                    }
                }
            }
            
            // Check MBI samples
            $samples_with_disposisi_koordinator_mbi = 0;
            foreach ($group_samples_mbi as $s) {
                $s_lab = null;
                if ($s->samplemethod && $s->samplemethod->count() > 0) {
                    foreach ($s->samplemethod as $sm) {
                        if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'MBI') {
                            $s_lab = $sm->laboratorium;
                            break;
                        }
                    }
                }
                
                if ($s_lab) {
                    $penerimaan = PenerimaanSample::where('sample_id', $s->id_samples)
                        ->where('laboratorium_id', $s_lab->id_laboratorium)
                        ->first();
                    
                    if ($penerimaan && !empty($penerimaan->disposisi_koordinator_kesmas) && !empty($penerimaan->disposisi_tanggal)) {
                        $samples_with_disposisi_koordinator_mbi++;
                    }
                }
            }
            
            // Step done only if step 1 for that lab is complete AND disposisi is done
            $step_koordinator_done_kim = ($step_penerima_done_kim && 
                $samples_with_disposisi_koordinator_kim == $group_samples_kim->count() && 
                $group_samples_kim->count() > 0);
            $step_koordinator_done_mbi = ($step_penerima_done_mbi && 
                $samples_with_disposisi_koordinator_mbi == $group_samples_mbi->count() && 
                $group_samples_mbi->count() > 0);
            $step_koordinator_done = ($step_koordinator_done_kim && $step_koordinator_done_mbi);
        } else {
            // Single lab or analis: check all samples
            $samples_with_disposisi_koordinator = 0;
            $samples_with_disposisi_koordinator_kim = 0;
            $samples_with_disposisi_koordinator_mbi = 0;
            $total_samples_kim_check = 0;
            $total_samples_mbi_check = 0;
            
            foreach ($group_samples as $s) {
                // Get laboratorium from sample's samplemethod
                $s_lab = null;
                $is_kim = false;
                $is_mbi = false;
                
                if ($s->samplemethod && $s->samplemethod->count() > 0) {
                    foreach ($s->samplemethod as $sm) {
                        if ($sm->laboratorium && in_array($sm->laboratorium->kode_laboratorium, ['KIM', 'MBI'])) {
                            $s_lab = $sm->laboratorium;
                            if ($sm->laboratorium->kode_laboratorium == 'KIM') {
                                $is_kim = true;
                                $total_samples_kim_check++;
                            } elseif ($sm->laboratorium->kode_laboratorium == 'MBI') {
                                $is_mbi = true;
                                $total_samples_mbi_check++;
                            }
                            break;
                        }
                    }
                }
                
                if ($s_lab) {
                    $penerimaan = PenerimaanSample::where('sample_id', $s->id_samples)
                        ->where('laboratorium_id', $s_lab->id_laboratorium)
                        ->first();
                    
                    if ($penerimaan && !empty($penerimaan->disposisi_koordinator_kesmas) && !empty($penerimaan->disposisi_tanggal)) {
                        $samples_with_disposisi_koordinator++;
                        if ($is_kim) {
                            $samples_with_disposisi_koordinator_kim++;
                        }
                        if ($is_mbi) {
                            $samples_with_disposisi_koordinator_mbi++;
                        }
                    }
                }
            }
            $step_koordinator_done = ($step_penerima_done && $samples_with_disposisi_koordinator == $total_samples && $total_samples > 0);
            
            // For single lab or analis, calculate per lab - use step_penerima_done_kim/mbi instead of step_penerima_done
            if ($total_samples_kim_check > 0) {
                $step_koordinator_done_kim = ($step_penerima_done_kim && 
                    $samples_with_disposisi_koordinator_kim == $total_samples_kim_check);
            } else {
                $step_koordinator_done_kim = false;
            }
            
            if ($total_samples_mbi_check > 0) {
                $step_koordinator_done_mbi = ($step_penerima_done_mbi && 
                    $samples_with_disposisi_koordinator_mbi == $total_samples_mbi_check);
            } else {
                $step_koordinator_done_mbi = false;
            }
        }

        // Check step 3: Analis - check per lab (kimia and mikro separately)
        // Use all_samples for admin, group_samples for analis (already filtered)
        $samples_to_check_analis = $isAdmin ? $all_samples : $group_samples;
        
        $samples_with_disposisi_analis = 0;
        $samples_with_disposisi_analis_kim = 0;
        $samples_with_disposisi_analis_mbi = 0;
        $total_samples_kim = 0;
        $total_samples_mbi = 0;
        $total_samples = count($samples_to_check_analis);
        
        foreach ($samples_to_check_analis as $s) {
            // Get laboratorium from sample's samplemethod
            $s_lab = null;
            $has_kim_lab = false;
            $has_mbi_lab = false;
            
            if ($s->samplemethod && $s->samplemethod->count() > 0) {
                foreach ($s->samplemethod as $sm) {
                    if ($sm->laboratorium && in_array($sm->laboratorium->kode_laboratorium, ['KIM', 'MBI'])) {
                        if ($sm->laboratorium->kode_laboratorium == 'KIM') {
                            $has_kim_lab = true;
                        } elseif ($sm->laboratorium->kode_laboratorium == 'MBI') {
                            $has_mbi_lab = true;
                        }
                    }
                }
            }
            
            // Check kimia lab
            if ($has_kim_lab) {
                $total_samples_kim++;
                $labKim = Laboratorium::where('kode_laboratorium', 'KIM')->first();
                if ($labKim) {
                    $penerimaan = PenerimaanSample::where('sample_id', $s->id_samples)
                        ->where('laboratorium_id', $labKim->id_laboratorium)
                        ->first();
                    
                    if ($penerimaan && !empty($penerimaan->disposisi_analis) && !empty($penerimaan->disposisi_analis_tanggal)) {
                        $samples_with_disposisi_analis_kim++;
                    }
                }
            }
            
            // Check mikro lab
            if ($has_mbi_lab) {
                $total_samples_mbi++;
                $labMbi = Laboratorium::where('kode_laboratorium', 'MBI')->first();
                if ($labMbi) {
                    $penerimaan = PenerimaanSample::where('sample_id', $s->id_samples)
                        ->where('laboratorium_id', $labMbi->id_laboratorium)
                        ->first();
                    
                    if ($penerimaan && !empty($penerimaan->disposisi_analis) && !empty($penerimaan->disposisi_analis_tanggal)) {
                        $samples_with_disposisi_analis_mbi++;
                    }
                }
            }
            
            // Also check for overall count (for single form)
            if ($s->samplemethod && $s->samplemethod->count() > 0) {
                foreach ($s->samplemethod as $sm) {
                    if ($sm->laboratorium && in_array($sm->laboratorium->kode_laboratorium, ['KIM', 'MBI'])) {
                        $s_lab = $sm->laboratorium;
                        break;
                    }
                }
            }
            
            if ($s_lab) {
                $penerimaan = PenerimaanSample::where('sample_id', $s->id_samples)
                    ->where('laboratorium_id', $s_lab->id_laboratorium)
                    ->first();
                
                if ($penerimaan && !empty($penerimaan->disposisi_analis) && !empty($penerimaan->disposisi_analis_tanggal)) {
                    $samples_with_disposisi_analis++;
                }
            }
        }
        
        // Step 3 done per lab
        $step_analis_done_kim = ($step_koordinator_done_kim && $samples_with_disposisi_analis_kim == $total_samples_kim && $total_samples_kim > 0);
        $step_analis_done_mbi = ($step_koordinator_done_mbi && $samples_with_disposisi_analis_mbi == $total_samples_mbi && $total_samples_mbi > 0);
        
        // Overall step 3 done (for single form)
        $step_analis_done = ($step_koordinator_done && $samples_with_disposisi_analis == $total_samples && $total_samples > 0);
        
        // For single lab (when not showing separate forms), set both to same value
        // Note: $show_separate is determined in the view, so we check if both labs exist
        if ($has_kim && $has_mbi) {
            // Both labs exist - keep separate tracking
        } else {
            // Single lab - use overall status
            if ($has_kim) {
                $step_analis_done_kim = $step_analis_done;
            } else {
                $step_analis_done_kim = false;
            }
            if ($has_mbi) {
                $step_analis_done_mbi = $step_analis_done;
            } else {
                $step_analis_done_mbi = false;
            }
        }

        // Determine readiness per user role
        $step_koordinator_ready_for_user = $step_koordinator_done;
        $step_analis_ready_for_user = $step_analis_done;

        if ($isAnalis && $userLabCode) {
            if ($userLabCode == 'KIM') {
                $step_koordinator_ready_for_user = $step_koordinator_done_kim;
                $step_analis_ready_for_user = $step_analis_done_kim;
            } elseif ($userLabCode == 'MBI') {
                $step_koordinator_ready_for_user = $step_koordinator_done_mbi;
                $step_analis_ready_for_user = $step_analis_done_mbi;
            }
        }

        // Check if all steps are completed
        $all_steps_completed = false;
        if ($isAdmin) {
            // For admin: all labs must be completed
            $all_steps_completed = ($step_analis_done_kim || !$has_kim) && ($step_analis_done_mbi || !$has_mbi);
        } else {
            // For analis: only their lab must be completed
            if ($isAnalis && $userLabCode) {
                if ($userLabCode == 'KIM') {
                    $all_steps_completed = $step_analis_done_kim;
                } elseif ($userLabCode == 'MBI') {
                    $all_steps_completed = $step_analis_done_mbi;
                }
            } else {
                $all_steps_completed = $step_analis_done;
            }
        }

        // If all steps completed, redirect to verification page
        if ($all_steps_completed) {
            // Get lab_id for redirect
            $lab_id = null;
            if ($isAnalis && $userLabCode) {
                // For analis, get their lab id
                if ($userLabCode == 'KIM' && $has_kim) {
                    $first_kim_sample = $group_samples_kim->first();
                    if ($first_kim_sample && $first_kim_sample->samplemethod) {
                        foreach ($first_kim_sample->samplemethod as $sm) {
                            if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'KIM') {
                                $lab_id = $sm->laboratorium->id_laboratorium;
                                break;
                            }
                        }
                    }
                } elseif ($userLabCode == 'MBI' && $has_mbi) {
                    $first_mbi_sample = $group_samples_mbi->first();
                    if ($first_mbi_sample && $first_mbi_sample->samplemethod) {
                        foreach ($first_mbi_sample->samplemethod as $sm) {
                            if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'MBI') {
                                $lab_id = $sm->laboratorium->id_laboratorium;
                                break;
                            }
                        }
                    }
                }
            } else {
                // For admin, use first lab found
                if ($laboratorium) {
                    $lab_id = $laboratorium->id_laboratorium;
                }
            }

            if ($lab_id) {
                return redirect()->route('mobile.testing.pemeriksaan', [$sample->id_samples, $lab_id]);
            }
        }

        // Get default dates for form inputs (format d/m/Y H:i)
        $penerima_tanggal_default = '';
        $disposisi_tanggal_default = '';
        $disposisi_analis_tanggal_default = '';
        
        // Get first sample's penerimaan data for default values
        if (count($existing_penerimaan) > 0) {
            $first_penerimaan = reset($existing_penerimaan);
            
            if ($first_penerimaan && $first_penerimaan->penerima_tanggal) {
                try {
                    $penerima_tanggal_default = Carbon::parse($first_penerimaan->penerima_tanggal)->format('d/m/Y H:i');
                } catch (\Exception $e) {
                    // Ignore error
                }
            }
            
            if ($first_penerimaan && $first_penerimaan->disposisi_tanggal) {
                try {
                    $disposisi_tanggal_default = Carbon::parse($first_penerimaan->disposisi_tanggal)->format('d/m/Y H:i');
                } catch (\Exception $e) {
                    // Ignore error
                }
            }
            
            if ($first_penerimaan && $first_penerimaan->disposisi_analis_tanggal) {
                try {
                    $disposisi_analis_tanggal_default = Carbon::parse($first_penerimaan->disposisi_analis_tanggal)->format('d/m/Y H:i');
                } catch (\Exception $e) {
                    // Ignore error
                }
            }
        }



        return view('masterweb::module.mobile.testing.status', compact(
            'sample',
            'group_samples',
            'group_samples_all',
            'group_samples_kim',
            'group_samples_mbi',
            'laboratorium',
            'penerimaan_done',
            'penerimaan_samples',
            'penerima_sampel_list',
            'analis_list',
            'analis_list_kim',
            'analis_list_mbi',
            'koordinator_kesmas_list',
            'koordinator_kesmas_list_kim',
            'koordinator_kesmas_list_mbi',
            'use_tte',
            'existing_penerimaan',
            'step_penerima_done',
            'step_penerima_done_kim',
            'step_penerima_done_mbi',
            'step_koordinator_done',
            'step_koordinator_done_kim',
            'step_koordinator_done_mbi',
            'step_analis_done',
            'step_analis_done_kim',
            'step_analis_done_mbi',
            'step_koordinator_ready_for_user',
            'step_analis_ready_for_user',
            'isAdmin',
            'isAnalis',
            'userLabCode',
            'penerima_tanggal_default',
            'disposisi_tanggal_default',
            'disposisi_analis_tanggal_default'
        ));
    }

    /**
     * Store penerimaan sample massal
     */
    public function storePenerimaan(Request $request, $id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_testing_auth', false)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get user info from session
        $user_id = $request->session()->get('mobile_testing_user_id');
        $isAdmin = $request->session()->get('mobile_testing_is_admin', false);
        $user = null;
        $userLabCode = null;
        $isAnalis = false;
        
        if ($user_id) {
            $user = User::with(['getlevel', 'laboratorium'])->find($user_id);
            if ($user && $user->laboratorium) {
                $userLabCode = $user->laboratorium->kode_laboratorium;
            }
        }
        
        // Determine if user is analis
        $isAnalis = !$isAdmin && $userLabCode && in_array($userLabCode, ['KIM', 'MBI']);
        
        // Check if user is admin/elits/elits-dev/LAB
        $userLevel = $request->session()->get('mobile_testing_user_level');
        $isAdminOrElits = $isAdmin || in_array($userLevel, ['elits', 'elits-dev', 'admin', 'LAB']);

        $sample = Sample::where('id_samples', $id)
            ->with(['permohonanuji', 'samplemethod.laboratorium', 'sampletype'])
            ->first();

        if (!$sample) {
            return response()->json(['error' => 'Sample tidak ditemukan'], 404);
        }

        // Ambil semua sample (kimia & mikro) dalam permohonan uji ini
        $group_samples_all = Sample::where('permohonan_uji_id', $sample->permohonan_uji_id)
            ->whereHas('samplemethod.laboratorium', function($query) {
                $query->whereIn('kode_laboratorium', ['KIM', 'MBI']);
            })
            ->with(['permohonanuji', 'samplemethod.laboratorium'])
            ->get();

        if ($group_samples_all->isEmpty()) {
            return response()->json(['error' => 'Sample untuk permohonan ini tidak ditemukan'], 404);
        }

        // Pastikan relasi laboratorium sudah ter-load
        $group_samples_all->loadMissing(['samplemethod.laboratorium']);

        if ($isAdminOrElits) {
            // Admin: gunakan semua sample untuk semua step
            $group_samples = $group_samples_all;
        } else {
            // Analis: filter sample sesuai lab user (untuk step 2 & 3), tapi tetap simpan semua untuk step 1
            if ($userLabCode && in_array($userLabCode, ['KIM', 'MBI'])) {
                $group_samples = $group_samples_all->filter(function($s) use ($userLabCode) {
                    if ($s->samplemethod && $s->samplemethod->count() > 0) {
                        foreach ($s->samplemethod as $sm) {
                            if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == $userLabCode) {
                                return true;
                            }
                        }
                    }
                    return false;
                })->values();

                if ($group_samples->isEmpty()) {
                    return response()->json(['error' => 'Tidak ada sample untuk lab Anda'], 403);
                }
            } else {
                return response()->json(['error' => 'Anda tidak memiliki akses untuk lab ini'], 403);
            }
        }

        DB::beginTransaction();
        try {
            // Determine current step
            $current_step = $request->input('current_step', 1);

            // STEP 1: Penerima Sampel
            if ($current_step == 1) {
                $request->validate([
                    'penerima_sampel' => 'required|string',
                    'penerima_tanggal' => 'required|string',
                ]);

                $penerima_sampel_new = $request->input('penerima_sampel');
                $penerima_tanggal_str = trim($request->input('penerima_tanggal'));
                
                // Parse tanggal dengan error handling
                // Format yang diterima: d/m/Y H:i (contoh: 16/01/2026 14:30)
                // Pastikan format yang diterima sudah benar dengan memeriksa apakah ada waktu
                $penerima_tanggal_new = null;
                
                // Debug: log input
                Log::debug('MobileTesting: Parsing penerima_tanggal', [
                    'input' => $penerima_tanggal_str,
                    'length' => strlen($penerima_tanggal_str),
                    'has_space' => strpos($penerima_tanggal_str, ' ') !== false
                ]);
                
                try {
                    // Coba format d/m/Y H:i
                    $penerima_tanggal_new = Carbon::createFromFormat('d/m/Y H:i', $penerima_tanggal_str);
                    Log::debug('MobileTesting: Successfully parsed with d/m/Y H:i', ['parsed' => $penerima_tanggal_new->format('Y-m-d H:i:s')]);
                } catch (\Exception $e) {
                    try {
                        // Coba format d/m/Y H:i:s
                        $penerima_tanggal_new = Carbon::createFromFormat('d/m/Y H:i:s', $penerima_tanggal_str);
                        Log::debug('MobileTesting: Successfully parsed with d/m/Y H:i:s', ['parsed' => $penerima_tanggal_new->format('Y-m-d H:i:s')]);
                    } catch (\Exception $e2) {
                        try {
                            // Coba format d-m-Y H:i
                            $penerima_tanggal_new = Carbon::createFromFormat('d-m-Y H:i', $penerima_tanggal_str);
                            Log::debug('MobileTesting: Successfully parsed with d-m-Y H:i', ['parsed' => $penerima_tanggal_new->format('Y-m-d H:i:s')]);
                        } catch (\Exception $e3) {
                            try {
                                // Coba format Y-m-d H:i:s (jika sudah dalam format database)
                                $penerima_tanggal_new = Carbon::parse($penerima_tanggal_str);
                                Log::debug('MobileTesting: Successfully parsed with Carbon::parse', ['parsed' => $penerima_tanggal_new->format('Y-m-d H:i:s')]);
                            } catch (\Exception $e4) {
                                Log::error('MobileTesting: Failed to parse penerima_tanggal', [
                                    'input' => $penerima_tanggal_str,
                                    'error' => $e4->getMessage()
                                ]);
                                return response()->json([
                                    'error' => 'Format tanggal tidak valid: ' . $penerima_tanggal_str . '. Format yang diharapkan: dd/mm/yyyy HH:mm. Pastikan waktu (jam:menit) sudah diisi.'
                                ], 400);
                            }
                        }
                    }
                }
                
                // Format ke database format
                $penerima_tanggal_new = $penerima_tanggal_new->format('Y-m-d H:i:s');
                Log::debug('MobileTesting: Final penerima_tanggal', ['final' => $penerima_tanggal_new]);

                // Determine which samples to process for Step 1
                // Untuk Step 1: SEMUA user (admin dan analis) menyimpan semua sample (kimia & mikro)
                // Untuk Step 2 & 3: analis hanya menyimpan sample sesuai lab mereka
                $samples_to_process = $group_samples_all;

                // Update or create PenerimaanSample
                // Untuk Step 1: SEMUA user (admin dan analis) menyimpan untuk SEMUA lab (KIM dan MBI) yang ada di setiap sample
                foreach ($samples_to_process as $s) {
                    // Get all laboratorium from sample's samplemethod (kimia and/or mikro)
                    $s_labs = [];
                    if ($s->samplemethod && $s->samplemethod->count() > 0) {
                        // Untuk Step 1, ambil semua lab KIM atau MBI yang ada di sample
                        foreach ($s->samplemethod as $sm) {
                            if ($sm->laboratorium && in_array($sm->laboratorium->kode_laboratorium, ['KIM', 'MBI'])) {
                                $s_labs[] = $sm->laboratorium;
                            }
                        }
                    }
                    
                    if (empty($s_labs)) {
                        continue; // Skip if no valid lab found
                    }
                    
                    // Handle pengawetan data per sample (sama untuk semua lab)
                    $sample_key = $s->id_samples;
                    $pengawetan_oleh = null;
                    if ($request->has("samples.{$sample_key}.pengawetan_oleh")) {
                        $pengawetan_oleh = $request->input("samples.{$sample_key}.pengawetan_oleh");
                    }

                    $pengawetan_dengan = [];
                    if ($request->has("samples.{$sample_key}.pengawetan_pendinginan")) {
                        $pengawetan_dengan[] = 'Pendinginan';
                    }
                    if ($request->has("samples.{$sample_key}.pengawetan_hno3")) {
                        $pengawetan_dengan[] = 'HNO3';
                    }
                    if ($request->has("samples.{$sample_key}.pengawetan_h2so4")) {
                        $pengawetan_dengan[] = 'H2SO4';
                    }
                    if ($request->has("samples.{$sample_key}.pengawetan_naoh")) {
                        $pengawetan_dengan[] = 'NaOH';
                    }
                    if ($request->has("samples.{$sample_key}.pengawetan_lainnya") && $request->has("samples.{$sample_key}.pengawetan_lainnya_text")) {
                        $pengawetan_dengan[] = 'lainnya: ' . $request->input("samples.{$sample_key}.pengawetan_lainnya_text");
                    }

                    // Handle kondisi sample (sama untuk semua lab)
                    $kondisi_sample = [];
                    if ($request->has("samples.{$sample_key}.kondisi_tidak_diawetkan")) {
                        $kondisi_sample[] = 'tidak diawetkan di lapangan';
                    }
                    if ($request->has("samples.{$sample_key}.kondisi_wadah_tidak_sesuai")) {
                        $kondisi_sample[] = 'wadah sampel tidak sesuai';
                    }
                    if ($request->has("samples.{$sample_key}.kondisi_kadaluarsa")) {
                        $kondisi_sample[] = 'sampel kadaluarsa';
                    }
                    if ($request->has("samples.{$sample_key}.kondisi_lainnya") && $request->has("samples.{$sample_key}.kondisi_lainnya_text")) {
                        $kondisi_sample[] = 'lainnya: ' . $request->input("samples.{$sample_key}.kondisi_lainnya_text");
                    }

                    // Handle kelayakan (sama untuk semua lab)
                    $kelayakan = null;
                    if ($request->has("samples.{$sample_key}.kelayakan")) {
                        $kelayakan = $request->input("samples.{$sample_key}.kelayakan");
                    }

                    // Simpan untuk SETIAP lab (KIM dan/atau MBI)
                    foreach ($s_labs as $s_lab) {
                        $s_idlabs = $s_lab->id_laboratorium;
                        
                        $penerimaan = PenerimaanSample::where('sample_id', $s->id_samples)
                            ->where('laboratorium_id', $s_idlabs)
                            ->first();

                        if (!$penerimaan) {
                            $penerimaan = new PenerimaanSample();
                            $penerimaan->id_sample_penerimaan = Uuid::uuid4()->toString();
                            $penerimaan->sample_id = $s->id_samples;
                            $penerimaan->laboratorium_id = $s_idlabs;
                        }

                        $penerimaan->penerima_sampel = $penerima_sampel_new;
                        $penerimaan->penerima_tanggal = $penerima_tanggal_new;

                        // Handle signature
                        if ($request->has('penerima_signature') && !empty($request->input('penerima_signature'))) {
                            $penerimaan->penerima_signature = $request->input('penerima_signature');
                            $penerimaan->penerima_signature_type = $request->input('penerima_signature_type', 'canvas');
                        }

                        // Set pengawetan data
                        if ($pengawetan_oleh) {
                            $penerimaan->pengawetan_oleh = $pengawetan_oleh;
                        }
                        if (!empty($pengawetan_dengan)) {
                            $penerimaan->pengawetan_dengan = implode('; ', $pengawetan_dengan);
                        } else {
                            // Set to null if empty to avoid empty string
                            $penerimaan->pengawetan_dengan = null;
                        }

                        // Set kondisi sample
                        if (!empty($kondisi_sample)) {
                            $penerimaan->kondisi_sample = implode('; ', $kondisi_sample);
                        }

                        // Set kelayakan
                        if ($kelayakan) {
                            $penerimaan->kelayakan_tempat_kemasan = $kelayakan == '1' ? 'layak' : 'tidak layak';
                        }

                        $penerimaan->save();
                        
                        Log::debug('MobileTesting: Step 1 - Saved penerimaan', [
                            'sample_id' => $s->id_samples,
                            'laboratorium_id' => $s_idlabs,
                            'lab_code' => $s_lab->kode_laboratorium
                        ]);
                    }
                    
                    // Update VerificationActivitySample for step 7 (Penerimaan Sample) - hanya sekali per sample
                    $verif = VerificationActivitySample::where('id_sample', $s->id_samples)
                        ->where('id_verification_activity', 7)
                        ->first();

                    if (!$verif) {
                        $verif = new VerificationActivitySample();
                        $verif->id = Uuid::uuid4()->toString();
                        $verif->id_verification_activity = 7;
                        $verif->id_sample = $s->id_samples;
                        $verif->start_date = $penerima_tanggal_new;
                        $verif->stop_date = Carbon::parse($penerima_tanggal_new)->addMinutes(5)->format('Y-m-d H:i:s');
                        $verif->nama_petugas = $penerima_sampel_new;
                        $verif->is_done = true;
                        $verif->save();
                    } else {
                        $verif->is_done = true;
                        $verif->nama_petugas = $penerima_sampel_new;
                        $verif->start_date = $penerima_tanggal_new;
                        $verif->stop_date = Carbon::parse($penerima_tanggal_new)->addMinutes(5)->format('Y-m-d H:i:s');
                        $verif->save();
                    }

                    // Update VerificationActivitySample for step 7 (Penerimaan Sample)
                    $verif = VerificationActivitySample::where('id_sample', $s->id_samples)
                        ->where('id_verification_activity', 7)
                        ->first();

                    if (!$verif) {
                        $verif = new VerificationActivitySample();
                        $verif->id = Uuid::uuid4()->toString();
                        $verif->id_verification_activity = 7;
                        $verif->id_sample = $s->id_samples;
                        $verif->start_date = $penerima_tanggal_new;
                        $verif->stop_date = Carbon::parse($penerima_tanggal_new)->addMinutes(5)->format('Y-m-d H:i:s');
                        $verif->nama_petugas = $penerima_sampel_new;
                        $verif->is_done = true;
                        $verif->save();
                    } else {
                        $verif->is_done = true;
                        $verif->nama_petugas = $penerima_sampel_new;
                        $verif->start_date = $penerima_tanggal_new;
                        $verif->stop_date = Carbon::parse($penerima_tanggal_new)->addMinutes(5)->format('Y-m-d H:i:s');
                        $verif->save();
                    }
                }

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Data penerimaan sample berhasil disimpan',
                    'next_step' => 2,
                    'redirect' => route('mobile.testing.status', ['id' => $id])
                ]);
            }
            // STEP 2: Disposisi Koordinator Kesmas
            elseif ($current_step == 2) {
                $request->validate([
                    'disposisi_koordinator_kesmas' => 'required|string',
                    'disposisi_tanggal' => 'required|string',
                ]);

                $disposisi_koordinator = $request->input('disposisi_koordinator_kesmas');
                $disposisi_tanggal_str = $request->input('disposisi_tanggal');
                $lab_type = $request->input('lab_type'); // KIM or MBI or null
                
                // Parse tanggal dengan error handling
                // Format yang diterima: d/m/Y H:i (contoh: 16/01/2026 14:30)
                try {
                    $disposisi_tanggal = Carbon::createFromFormat('d/m/Y H:i', trim($disposisi_tanggal_str))->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    try {
                        $disposisi_tanggal = Carbon::createFromFormat('d/m/Y H:i:s', trim($disposisi_tanggal_str))->format('Y-m-d H:i:s');
                    } catch (\Exception $e2) {
                        try {
                            // Coba format d-m-Y H:i
                            $disposisi_tanggal = Carbon::createFromFormat('d-m-Y H:i', trim($disposisi_tanggal_str))->format('Y-m-d H:i:s');
                        } catch (\Exception $e3) {
                            try {
                                $disposisi_tanggal = Carbon::parse(trim($disposisi_tanggal_str))->format('Y-m-d H:i:s');
                            } catch (\Exception $e4) {
                                return response()->json([
                                    'error' => 'Format tanggal tidak valid: ' . $disposisi_tanggal_str . '. Format yang diharapkan: dd/mm/yyyy HH:mm'
                                ], 400);
                            }
                        }
                    }
                }

                // Determine which samples to update based on lab_type
                // Untuk analis: gunakan $group_samples yang sudah difilter sesuai lab mereka
                // Untuk admin: gunakan $group_samples_all jika lab_type tidak ditentukan, atau filter sesuai lab_type
                if ($isAdminOrElits) {
                    $samples_to_update = $group_samples_all;
                    if ($lab_type && in_array($lab_type, ['KIM', 'MBI'])) {
                        // Filter samples by specific lab
                        $samples_to_update = $group_samples_all->filter(function($s) use ($lab_type) {
                            if ($s->samplemethod && $s->samplemethod->count() > 0) {
                                foreach ($s->samplemethod as $sm) {
                                    if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == $lab_type) {
                                        return true;
                                    }
                                }
                            }
                            return false;
                        });
                    }
                } else {
                    // Analis: gunakan $group_samples yang sudah difilter sesuai lab mereka
                    $samples_to_update = $group_samples;
                }

                Log::debug('MobileTesting: Step 2 samples to update', [
                    'isAdminOrElits' => $isAdminOrElits,
                    'userLabCode' => $userLabCode,
                    'lab_type' => $lab_type,
                    'samples_count' => $samples_to_update->count(),
                    'sample_ids' => $samples_to_update->pluck('id_samples')->toArray()
                ]);

                // Update samples
                foreach ($samples_to_update as $s) {
                    // Get laboratorium from sample's samplemethod (kimia or mikro)
                    $s_lab = null;
                    if ($s->samplemethod && $s->samplemethod->count() > 0) {
                        foreach ($s->samplemethod as $sm) {
                            if ($sm->laboratorium && in_array($sm->laboratorium->kode_laboratorium, ['KIM', 'MBI'])) {
                                // If lab_type specified, only update matching lab
                                if ($lab_type && $sm->laboratorium->kode_laboratorium != $lab_type) {
                                    continue;
                                }
                                // If analis, only update samples with matching lab
                                if (!$isAdminOrElits && $userLabCode && $sm->laboratorium->kode_laboratorium != $userLabCode) {
                                    continue;
                                }
                                $s_lab = $sm->laboratorium;
                                break;
                            }
                        }
                    }
                    
                    if (!$s_lab) {
                        Log::debug('MobileTesting: Step 2 - No lab found for sample', [
                            'sample_id' => $s->id_samples,
                            'lab_type' => $lab_type,
                            'userLabCode' => $userLabCode
                        ]);
                        continue;
                    }
                    
                    $s_idlabs = $s_lab->id_laboratorium;
                    
                    Log::debug('MobileTesting: Step 2 - Finding penerimaan', [
                        'sample_id' => $s->id_samples,
                        'laboratorium_id' => $s_idlabs,
                        'lab_code' => $s_lab->kode_laboratorium
                    ]);
                    
                    $penerimaan = PenerimaanSample::where('sample_id', $s->id_samples)
                        ->where('laboratorium_id', $s_idlabs)
                        ->first();

                    if (!$penerimaan) {
                        Log::warning('MobileTesting: Step 2 - Penerimaan not found, creating new', [
                            'sample_id' => $s->id_samples,
                            'laboratorium_id' => $s_idlabs,
                            'lab_code' => $s_lab->kode_laboratorium
                        ]);
                        $penerimaan = new PenerimaanSample();
                        $penerimaan->id_sample_penerimaan = Uuid::uuid4()->toString();
                        $penerimaan->sample_id = $s->id_samples;
                        $penerimaan->laboratorium_id = $s_idlabs;
                    } else {
                        Log::debug('MobileTesting: Step 2 - Penerimaan found', [
                            'sample_id' => $s->id_samples,
                            'laboratorium_id' => $s_idlabs,
                            'penerimaan_id' => $penerimaan->id_sample_penerimaan
                        ]);
                    }

                    // Normalize disposisi_koordinator_kesmas (remove accents and commas)
                    $normalizeName = function($name) {
                        // Remove commas
                        $name = str_replace(',', '', $name);
                        // Remove accents/diacritics
                        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
                        // Remove extra spaces
                        $name = trim($name);
                        $name = preg_replace('/\s+/', ' ', $name);
                        return $name;
                    };
                    
                    $penerimaan->disposisi_koordinator_kesmas = $normalizeName($disposisi_koordinator);
                    $penerimaan->disposisi_tanggal = $disposisi_tanggal;

                    // Log signature input
                    Log::debug('MobileTesting: Step 2 - Signature input check', [
                        'sample_id' => $s->id_samples,
                        'has_disposisi_signature' => $request->has('disposisi_signature'),
                        'disposisi_signature_empty' => empty($request->input('disposisi_signature')),
                        'disposisi_signature_length' => $request->has('disposisi_signature') ? strlen($request->input('disposisi_signature')) : 0,
                        'disposisi_signature_type' => $request->input('disposisi_signature_type', 'not set')
                    ]);

                    // dd($request->input('disposisi_signature'));

               
                    if ($request->has('disposisi_signature') && !empty($request->input('disposisi_signature'))) {
                        $penerimaan->disposisi_signature = $request->input('disposisi_signature');
                        $penerimaan->disposisi_signature_type = $request->input('disposisi_signature_type', 'canvas');
                        Log::debug('MobileTesting: Step 2 - Signature saved', [
                            'sample_id' => $s->id_samples,
                            'signature_type' => $penerimaan->disposisi_signature_type
                        ]);

                    } else {
                        Log::warning('MobileTesting: Step 2 - Signature not saved', [
                            'sample_id' => $s->id_samples,
                            'has_disposisi_signature' => $request->has('disposisi_signature'),
                            'disposisi_signature_value' => $request->input('disposisi_signature', 'not set')
                        ]);
                    }

                    $penerimaan->save();
                    
                    Log::debug('MobileTesting: Step 2 - Saved disposisi', [
                        'sample_id' => $s->id_samples,
                        'laboratorium_id' => $s_idlabs,
                        'disposisi_koordinator_kesmas' => $penerimaan->disposisi_koordinator_kesmas,
                        'disposisi_tanggal' => $penerimaan->disposisi_tanggal,
                        'has_disposisi_signature' => !empty($penerimaan->disposisi_signature)
                    ]);
                }

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Data disposisi koordinator berhasil disimpan',
                    'next_step' => 3
                ]);
            }
            // STEP 3: Disposisi Analis
            elseif ($current_step == 3) {
                $request->validate([
                    'disposisi_analis' => 'required|string',
                    'disposisi_analis_tanggal' => 'required|string',
                ]);

                $disposisi_analis = $request->input('disposisi_analis');
                $disposisi_analis_tanggal_str = $request->input('disposisi_analis_tanggal');
                $lab_type = $request->input('lab_type'); // KIM or MBI or null
                
                Log::info('Step 3 - Disposisi Analis', [
                    'disposisi_analis' => $disposisi_analis,
                    'disposisi_analis_tanggal_str' => $disposisi_analis_tanggal_str,
                    'lab_type' => $lab_type,
                    'group_samples_count' => $group_samples->count()
                ]);
                
                // Parse tanggal dengan error handling
                // Format yang diterima: d/m/Y H:i (contoh: 16/01/2026 14:30)
                try {
                    $disposisi_analis_tanggal = Carbon::createFromFormat('d/m/Y H:i', trim($disposisi_analis_tanggal_str))->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    try {
                        $disposisi_analis_tanggal = Carbon::createFromFormat('d/m/Y H:i:s', trim($disposisi_analis_tanggal_str))->format('Y-m-d H:i:s');
                    } catch (\Exception $e2) {
                        try {
                            // Coba format d-m-Y H:i
                            $disposisi_analis_tanggal = Carbon::createFromFormat('d-m-Y H:i', trim($disposisi_analis_tanggal_str))->format('Y-m-d H:i:s');
                        } catch (\Exception $e3) {
                            try {
                                $disposisi_analis_tanggal = Carbon::parse(trim($disposisi_analis_tanggal_str))->format('Y-m-d H:i:s');
                            } catch (\Exception $e4) {
                                return response()->json([
                                    'error' => 'Format tanggal tidak valid: ' . $disposisi_analis_tanggal_str . '. Format yang diharapkan: dd/mm/yyyy HH:mm'
                                ], 400);
                            }
                        }
                    }
                }

                // Determine which samples to update based on lab_type
                $samples_to_update = $group_samples;
                if ($lab_type && in_array($lab_type, ['KIM', 'MBI'])) {
                    // Filter samples by specific lab
                    $samples_to_update = $group_samples->filter(function($s) use ($lab_type) {
                        if ($s->samplemethod && $s->samplemethod->count() > 0) {
                            foreach ($s->samplemethod as $sm) {
                                if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == $lab_type) {
                                    return true;
                                }
                            }
                        }
                        return false;
                    });
                }

                Log::info('Step 3 - Samples to update', [
                    'samples_to_update_count' => $samples_to_update->count(),
                    'lab_type' => $lab_type,
                    'group_samples_count' => $group_samples->count(),
                    'isAdminOrElits' => $isAdminOrElits,
                    'sample_ids' => $samples_to_update->pluck('id_samples')->toArray()
                ]);

                // Update samples
                $saved_count = 0;
                foreach ($samples_to_update as $s) {
                    // Get laboratorium from sample's samplemethod (kimia or mikro)
                    $s_lab = null;
                    $s_idlabs = null;
                    
                    if ($s->samplemethod && $s->samplemethod->count() > 0) {
                        foreach ($s->samplemethod as $sm) {
                            if (!$sm->laboratorium) {
                                continue;
                            }
                            
                            $lab_kode = $sm->laboratorium->kode_laboratorium;
                            
                            // Skip if not KIM or MBI
                            if (!in_array($lab_kode, ['KIM', 'MBI'])) {
                                continue;
                            }
                            
                            // If lab_type specified, only use matching lab
                            if ($lab_type && $lab_kode != $lab_type) {
                                continue;
                            }
                            
                            // If analis (not admin), only use matching lab
                            if (!$isAdminOrElits && $userLabCode && $lab_kode != $userLabCode) {
                                continue;
                            }
                            
                            // Found matching lab
                            $s_lab = $sm->laboratorium;
                            $s_idlabs = $s_lab->id_laboratorium;
                            break;
                        }
                    }
                    
                    if (!$s_lab || !$s_idlabs) {
                        Log::info('Step 3 - Skipping sample (no matching lab)', [
                            'sample_id' => $s->id_samples,
                            'lab_type' => $lab_type,
                            'userLabCode' => $userLabCode,
                            'isAdminOrElits' => $isAdminOrElits,
                            'samplemethod_count' => $s->samplemethod ? $s->samplemethod->count() : 0,
                            'samplemethod_labs' => $s->samplemethod ? $s->samplemethod->map(function($sm) {
                                return $sm->laboratorium ? $sm->laboratorium->kode_laboratorium : null;
                            })->toArray() : []
                        ]);
                        continue;
                    }
                    
                    $penerimaan = PenerimaanSample::where('sample_id', $s->id_samples)
                        ->where('laboratorium_id', $s_idlabs)
                        ->first();

                    if (!$penerimaan) {
                        $penerimaan = new PenerimaanSample();
                        $penerimaan->id_sample_penerimaan = Uuid::uuid4()->toString();
                        $penerimaan->sample_id = $s->id_samples;
                        $penerimaan->laboratorium_id = $s_idlabs;
                    }

                    // Normalize disposisi_analis (remove accents and commas) - use the same normalizeName function
                    // Note: $normalizeName is defined earlier in the method, but we need to recreate it here
                    $normalizeNameAnalis = function($name) {
                        // Remove commas
                        $name = str_replace(',', '', $name);
                        // Remove accents/diacritics
                        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
                        // Remove extra spaces
                        $name = trim($name);
                        $name = preg_replace('/\s+/', ' ', $name);
                        return $name;
                    };
                    
                    $penerimaan->disposisi_analis = $normalizeNameAnalis($disposisi_analis);
                    $penerimaan->disposisi_analis_tanggal = $disposisi_analis_tanggal;

                    // Log signature input
                    Log::debug('MobileTesting: Step 3 - Signature input check', [
                        'sample_id' => $s->id_samples,
                        'has_disposisi_analis_signature' => $request->has('disposisi_analis_signature'),
                        'disposisi_analis_signature_empty' => empty($request->input('disposisi_analis_signature')),
                        'disposisi_analis_signature_length' => $request->has('disposisi_analis_signature') ? strlen($request->input('disposisi_analis_signature')) : 0,
                        'disposisi_analis_signature_type' => $request->input('disposisi_analis_signature_type', 'not set')
                    ]);

                    if ($request->has('disposisi_analis_signature') && !empty($request->input('disposisi_analis_signature'))) {
                        $penerimaan->disposisi_analis_signature = $request->input('disposisi_analis_signature');
                        $penerimaan->disposisi_analis_signature_type = $request->input('disposisi_analis_signature_type', 'canvas');
                        Log::debug('MobileTesting: Step 3 - Signature saved', [
                            'sample_id' => $s->id_samples,
                            'signature_type' => $penerimaan->disposisi_analis_signature_type
                        ]);
                    } else {
                        Log::warning('MobileTesting: Step 3 - Signature not saved', [
                            'sample_id' => $s->id_samples,
                            'has_disposisi_analis_signature' => $request->has('disposisi_analis_signature'),
                            'disposisi_analis_signature_value' => $request->input('disposisi_analis_signature', 'not set')
                        ]);
                    }

                    try {
                        $penerimaan->save();
                        $saved_count++;
                        
                        Log::debug('MobileTesting: Step 3 - Saved disposisi analis', [
                            'sample_id' => $s->id_samples,
                            'laboratorium_id' => $s_idlabs,
                            'disposisi_analis' => $penerimaan->disposisi_analis,
                            'disposisi_analis_tanggal' => $penerimaan->disposisi_analis_tanggal,
                            'has_disposisi_analis_signature' => !empty($penerimaan->disposisi_analis_signature)
                        ]);
                        
                        Log::info('Step 3 - Saved penerimaan', [
                            'sample_id' => $s->id_samples,
                            'laboratorium_id' => $s_idlabs,
                            'laboratorium_kode' => $s_lab->kode_laboratorium,
                            'disposisi_analis' => $penerimaan->disposisi_analis,
                            'disposisi_analis_tanggal' => $penerimaan->disposisi_analis_tanggal
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Step 3 - Error saving penerimaan', [
                            'sample_id' => $s->id_samples,
                            'laboratorium_id' => $s_idlabs,
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }
                }

                if ($saved_count == 0) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Tidak ada sample yang dapat disimpan. Pastikan lab_type sesuai dengan sample yang ada.'
                    ], 400);
                }

                DB::commit();
                
                // Check if all steps are completed
                $all_steps_completed = false;
                $completed_lab = null;
                
                // Re-check completion status
                $has_kim = false;
                $has_mbi = false;
                $step_analis_done_kim = false;
                $step_analis_done_mbi = false;
                
                foreach ($group_samples_all as $s) {
                    if ($s->samplemethod && $s->samplemethod->count() > 0) {
                        foreach ($s->samplemethod as $sm) {
                            if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'KIM') {
                                $has_kim = true;
                            }
                            if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'MBI') {
                                $has_mbi = true;
                            }
                        }
                    }
                }
                
                // Check completion for each lab
                if ($has_kim) {
                    $samples_kim = $group_samples_all->filter(function($s) {
                        if ($s->samplemethod && $s->samplemethod->count() > 0) {
                            foreach ($s->samplemethod as $sm) {
                                if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'KIM') {
                                    return true;
                                }
                            }
                        }
                        return false;
                    });
                    
                    $samples_with_disposisi_analis_kim = 0;
                    foreach ($samples_kim as $s) {
                        $s_lab = null;
                        if ($s->samplemethod && $s->samplemethod->count() > 0) {
                            foreach ($s->samplemethod as $sm) {
                                if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'KIM') {
                                    $s_lab = $sm->laboratorium;
                                    break;
                                }
                            }
                        }
                        if ($s_lab) {
                            $penerimaan = PenerimaanSample::where('sample_id', $s->id_samples)
                                ->where('laboratorium_id', $s_lab->id_laboratorium)
                                ->first();
                            if ($penerimaan && !empty($penerimaan->disposisi_analis) && !empty($penerimaan->disposisi_analis_tanggal)) {
                                $samples_with_disposisi_analis_kim++;
                            }
                        }
                    }
                    $step_analis_done_kim = ($samples_with_disposisi_analis_kim == $samples_kim->count() && $samples_kim->count() > 0);
                }
                
                if ($has_mbi) {
                    $samples_mbi = $group_samples_all->filter(function($s) {
                        if ($s->samplemethod && $s->samplemethod->count() > 0) {
                            foreach ($s->samplemethod as $sm) {
                                if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'MBI') {
                                    return true;
                                }
                            }
                        }
                        return false;
                    });
                    
                    $samples_with_disposisi_analis_mbi = 0;
                    foreach ($samples_mbi as $s) {
                        $s_lab = null;
                        if ($s->samplemethod && $s->samplemethod->count() > 0) {
                            foreach ($s->samplemethod as $sm) {
                                if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == 'MBI') {
                                    $s_lab = $sm->laboratorium;
                                    break;
                                }
                            }
                        }
                        if ($s_lab) {
                            $penerimaan = PenerimaanSample::where('sample_id', $s->id_samples)
                                ->where('laboratorium_id', $s_lab->id_laboratorium)
                                ->first();
                            if ($penerimaan && !empty($penerimaan->disposisi_analis) && !empty($penerimaan->disposisi_analis_tanggal)) {
                                $samples_with_disposisi_analis_mbi++;
                            }
                        }
                    }
                    $step_analis_done_mbi = ($samples_with_disposisi_analis_mbi == $samples_mbi->count() && $samples_mbi->count() > 0);
                }
                
                if ($isAdmin) {
                    $all_steps_completed = ($step_analis_done_kim || !$has_kim) && ($step_analis_done_mbi || !$has_mbi);
                } else {
                    if ($isAnalis && $userLabCode) {
                        if ($userLabCode == 'KIM') {
                            $all_steps_completed = $step_analis_done_kim;
                            if ($all_steps_completed) {
                                $completed_lab = 'KIM';
                            }
                        } elseif ($userLabCode == 'MBI') {
                            $all_steps_completed = $step_analis_done_mbi;
                            if ($all_steps_completed) {
                                $completed_lab = 'MBI';
                            }
                        }
                    } else {
                        $all_steps_completed = ($step_analis_done_kim || !$has_kim) && ($step_analis_done_mbi || !$has_mbi);
                    }
                }
                
                // Get lab_id for redirect if completed
                $lab_id_for_redirect = null;
                if ($all_steps_completed) {
                    if ($isAnalis && $userLabCode && $completed_lab) {
                        // Get lab id for completed lab
                        $first_sample = $group_samples_all->first();
                        if ($first_sample && $first_sample->samplemethod) {
                            foreach ($first_sample->samplemethod as $sm) {
                                if ($sm->laboratorium && $sm->laboratorium->kode_laboratorium == $completed_lab) {
                                    $lab_id_for_redirect = $sm->laboratorium->id_laboratorium;
                                    break;
                                }
                            }
                        }
                    } else {
                        // For admin, use first lab found
                        $first_sample = $group_samples_all->first();
                        if ($first_sample && $first_sample->samplemethod) {
                            $first_lab = $first_sample->samplemethod->first()->laboratorium;
                            if ($first_lab) {
                                $lab_id_for_redirect = $first_lab->id_laboratorium;
                            }
                        }
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Data disposisi analis berhasil disimpan (' . $saved_count . ' sample)',
                    'next_step' => null,
                    'all_steps_completed' => $all_steps_completed,
                    'completed_lab' => $completed_lab,
                    'lab_id' => $lab_id_for_redirect,
                    'is_admin' => $isAdmin,
                    'is_analis' => $isAnalis
                ]);
            }

            DB::rollBack();
            return response()->json(['error' => 'Invalid step'], 400);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mobile Testing Store Error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        // Logout dari web guard juga (biar session bersih)
        try {
            Auth::logout();
        } catch (\Exception $e) {
            // ignore
        }

        $request->session()->forget('mobile_testing_auth');
        $request->session()->forget('mobile_testing_user_id');
        $request->session()->forget('mobile_testing_user_name');
        $request->session()->forget('mobile_testing_id_sample');

        return redirect()->route('mobile.testing.home')
            ->with('success', 'Anda telah logout.');
    }

    /**
     * Show pemeriksaan form (Pemeriksaan / Analitik)
     */
    public function pemeriksaan(Request $request, $id, $lab_id)
    {
        // Check authentication
        $isAuthenticated = $request->session()->get('mobile_testing_auth', false);
        if (!$isAuthenticated) {
            return redirect()->route('mobile.testing.login', $id)
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Check and redirect to correct step if user is at wrong position
        $redirectUrl = $this->checkAndRedirectToCorrectStep($request, $id, 'pemeriksaan', $lab_id);
        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        // Get sample
        $sample = Sample::where('id_samples', $id)
            ->with(['permohonanuji', 'samplemethod.laboratorium', 'sampletype'])
            ->first();

        if (!$sample) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data sample tidak ditemukan.'
            ]);
        }

        // Get laboratorium
        $laboratorium = Laboratorium::find($lab_id);
        if (!$laboratorium) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data laboratorium tidak ditemukan.'
            ]);
        }

        // Get user info
        $user_id = $request->session()->get('mobile_testing_user_id');
        $isAdmin = $request->session()->get('mobile_testing_is_admin', false);
        $user = null;
        $userLabCode = null;
        
        if ($user_id) {
            $user = User::with(['getlevel', 'laboratorium'])->find($user_id);
            if ($user && $user->laboratorium) {
                $userLabCode = $user->laboratorium->kode_laboratorium;
            }
        }

        $isAnalis = !$isAdmin && $userLabCode && in_array($userLabCode, ['KIM', 'MBI']);

        // Get verification activity (id = 2 for Pemeriksaan / Analitik)
        $verificationActivity = VerificationActivity::where('id', 2)->first();
        $list_name_petugas = [];
        
        if ($verificationActivity) {
            if ($laboratorium->kode_laboratorium == 'KIM') {
                $list_name_petugas = explode(', ', $verificationActivity->kimia ?? '');
            } elseif ($laboratorium->kode_laboratorium == 'MBI') {
                $list_name_petugas = explode(', ', $verificationActivity->mikro ?? '');
            }
            // Remove empty strings
            $list_name_petugas = array_filter(array_map('trim', $list_name_petugas), function($value) {
                return !empty($value);
            });
        }

        // Get existing verification data
        $existing_verification = VerificationActivitySample::where('id_sample', $id)
            ->where('id_verification_activity', 2)
            ->first();

        // Get default analis from penerimaan sample
        $default_analis = null;
        $penerimaan = PenerimaanSample::where('sample_id', $id)
            ->where('laboratorium_id', $lab_id)
            ->first();
        if ($penerimaan && $penerimaan->disposisi_analis) {
            $default_analis = $penerimaan->disposisi_analis;
        }

        // Get data for default dates (same logic as verification-2.blade.php)
        // Get Pendaftaran (step 1) - this is used as base for Pemeriksaan Start
        $pendaftaran_verif = VerificationActivitySample::where('id_sample', $id)
            ->where('id_verification_activity', 1)
            ->first();
        
        // Determine default start date
        // Logic: Pemeriksaan Start = Pendaftaran Stop (or current date if not available)
        $default_start_date = null;
        if ($pendaftaran_verif && $pendaftaran_verif->stop_date) {
            $default_start_date = Carbon::parse($pendaftaran_verif->stop_date);
        } else {
            $default_start_date = Carbon::now();
        }
        
        // Adjust to work hours (8:00 - 15:00) - same as adjustToWorkHours function in JS
        if ($default_start_date->hour < 8) {
            $default_start_date->setTime(8, 0, 0);
        } elseif ($default_start_date->hour >= 15) {
            $default_start_date->addDay()->setTime(8, 0, 0);
        }
        
        // Default stop date = start date + 2 days (same as verification-2.blade.php)
        $default_stop_date = $default_start_date->copy()->addDays(2);
        
        // Adjust stop date to work hours
        if ($default_stop_date->hour < 8) {
            $default_stop_date->setTime(8, 0, 0);
        } elseif ($default_stop_date->hour >= 15) {
            $default_stop_date->addDay()->setTime(8, 0, 0);
        }
        
        // Format for view (format: d/m/Y H:i)
        $default_start_date_str = $default_start_date->format('d/m/Y');
        $default_stop_date_str = $default_stop_date->format('d/m/Y');

        return view('masterweb::module.mobile.testing.pemeriksaan', compact(
            'sample',
            'laboratorium',
            'list_name_petugas',
            'existing_verification',
            'default_analis',
            'isAdmin',
            'isAnalis',
            'userLabCode',
            'default_start_date_str',
            'default_stop_date_str',
            'pendaftaran_verif'
        ));
    }

    /**
     * Store pemeriksaan data (Pemeriksaan / Analitik)
     */
    public function storePemeriksaan(Request $request, $id)
    {
        // Check authentication
        $isAuthenticated = $request->session()->get('mobile_testing_auth', false);
        if (!$isAuthenticated) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }

        $request->validate([
            'verification_step' => 'required|numeric',
            'start_date' => 'required|string',
            'stop_date' => 'required|string',
            'nama_petugas' => 'required|string',
            'lab_id' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // Parse dates
            $start_date = $this->parseMobileStageDate($request->input('start_date'))->format('Y-m-d H:i:s');
            $stop_date = $this->parseMobileStageDate($request->input('stop_date'))->format('Y-m-d H:i:s');

            // Check if verification already exists
            $verificationActivitySample = VerificationActivitySample::where('id_sample', $id)
                ->where('id_verification_activity', $request->input('verification_step'))
                ->first();

            if ($verificationActivitySample) {
                // Update existing
                $verificationActivitySample->start_date = $start_date;
                $verificationActivitySample->stop_date = $stop_date;
                $verificationActivitySample->nama_petugas = $request->input('nama_petugas');
                $verificationActivitySample->is_done = 1;
                $verificationActivitySample->save();
            } else {
                // Create new
                $verificationActivitySample = new VerificationActivitySample();
                $verificationActivitySample->id = Uuid::uuid4()->toString();
                $verificationActivitySample->id_sample = $id;
                $verificationActivitySample->id_verification_activity = $request->input('verification_step');
                $verificationActivitySample->start_date = $start_date;
                $verificationActivitySample->stop_date = $stop_date;
                $verificationActivitySample->nama_petugas = $request->input('nama_petugas');
                $verificationActivitySample->is_done = 1;
                $verificationActivitySample->save();
            }

            DB::commit();

            // If step 2 (Pemeriksaan / Analitik) is completed, redirect to baca-hasil
            if ($request->input('verification_step') == 2) {
                $lab_id = $request->input('lab_id');
                // Get lab_id from laboratorium table
                $laboratorium = Laboratorium::find($lab_id);
                $lab_uuid = $laboratorium ? $laboratorium->id_laboratorium : $lab_id;
                
                return response()->json([
                    'success' => true,
                    'message' => 'Data verifikasi berhasil disimpan.',
                    'next_step' => 'baca_hasil',
                    'redirect_url' => route('mobile.testing.bacaHasil', [
                        'id' => $id,
                        'lab_id' => $lab_uuid,
                        'method_id' => 'bfecda4a-73f2-47d6-9fc3-01f65e0f02a1' // Default method_id, same as web version
                    ])
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data verifikasi berhasil disimpan.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MobileTesting: Error storing verification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Input / Output Hasil Px form (step 3)
     */
    public function inputHasil(Request $request, $id, $lab_id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_testing_auth', false)) {
            return redirect()->route('mobile.testing.login', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Check and redirect to correct step if user is at wrong position
        // InputHasil is step 3, so should check if step 2 done and step 3 not done
        $redirectUrl = $this->checkAndRedirectToCorrectStep($request, $id, 'inputHasil', $lab_id);
        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        $sample = Sample::where('id_samples', $id)
            ->with(['permohonanuji', 'samplemethod.laboratorium', 'sampletype'])
            ->first();

        if (!$sample) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data sample tidak ditemukan.'
            ]);
        }

        // Get laboratorium
        $laboratorium = Laboratorium::find($lab_id);
        if (!$laboratorium) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data laboratorium tidak ditemukan.'
            ]);
        }

        // Get user info
        $user_id = $request->session()->get('mobile_testing_user_id');
        $isAdmin = $request->session()->get('mobile_testing_is_admin', false);
        $user = null;
        $userLabCode = null;
        
        if ($user_id) {
            $user = User::with(['getlevel', 'laboratorium'])->find($user_id);
            if ($user && $user->laboratorium) {
                $userLabCode = $user->laboratorium->kode_laboratorium;
            }
        }

        $isAnalis = !$isAdmin && $userLabCode && in_array($userLabCode, ['KIM', 'MBI']);

        // Get verification activity for Input / Output Hasil Px
        // Note: verificationActivity[2] means array index 2, which corresponds to id = 3
        $verificationActivityAll = VerificationActivity::all();
        $verificationActivity = $verificationActivityAll->where('id', 3)->first();
        $list_name_petugas = [];
        
        if ($verificationActivity) {
            if ($laboratorium->kode_laboratorium == 'KIM') {
                $list_name_petugas = explode(', ', $verificationActivity->kimia ?? '');
            } elseif ($laboratorium->kode_laboratorium == 'MBI') {
                $list_name_petugas = explode(', ', $verificationActivity->mikro ?? '');
            }
            // Remove empty strings
            $list_name_petugas = array_filter(array_map('trim', $list_name_petugas), function($value) {
                return !empty($value);
            });
        }

        // Get existing verification data (step 3)
        $existing_verification = VerificationActivitySample::where('id_sample', $id)
            ->where('id_verification_activity', 3)
            ->first();

        // Get default analis from step 2 (Pemeriksaan / Analitik)
        $default_analis = null;
        $pemeriksaan_verif = VerificationActivitySample::where('id_sample', $id)
            ->where('id_verification_activity', 2)
            ->first();
        if ($pemeriksaan_verif && $pemeriksaan_verif->nama_petugas) {
            $default_analis = $pemeriksaan_verif->nama_petugas;
        }

        // Get data for default dates (same logic as verification-2.blade.php)
        // Input Start = Pemeriksaan Stop
        $pemeriksaan_verif = VerificationActivitySample::where('id_sample', $id)
            ->where('id_verification_activity', 2)
            ->first();
        
        // Determine default start date
        $default_start_date = null;
        if ($pemeriksaan_verif && $pemeriksaan_verif->stop_date) {
            $default_start_date = Carbon::parse($pemeriksaan_verif->stop_date);
        } else {
            $default_start_date = Carbon::now();
        }
        
        // Adjust to work hours (8:00 - 15:00)
        if ($default_start_date->hour < 8) {
            $default_start_date->setTime(8, 0, 0);
        } elseif ($default_start_date->hour >= 15) {
            $default_start_date->addDay()->setTime(8, 0, 0);
        }
        
        // Default stop date = start date + 10 minutes
        $default_stop_date = $default_start_date->copy()->addMinutes(10);
        
        // Adjust stop date to work hours
        if ($default_stop_date->hour < 8) {
            $default_stop_date->setTime(8, 0, 0);
        } elseif ($default_stop_date->hour >= 15) {
            $default_stop_date->addDay()->setTime(8, 0, 0);
        }
        
        // Format for view
        $default_start_date_str = $default_start_date->format('d/m/Y');
        $default_stop_date_str = $default_stop_date->format('d/m/Y');

        return view('masterweb::module.mobile.testing.input-hasil', compact(
            'sample',
            'laboratorium',
            'list_name_petugas',
            'existing_verification',
            'default_analis',
            'isAdmin',
            'isAnalis',
            'userLabCode',
            'default_start_date_str',
            'default_stop_date_str',
            'pemeriksaan_verif'
        ));
    }

    /**
     * Store Input / Output Hasil Px data (step 3)
     */
    public function storeInputHasil(Request $request, $id)
    {
        // Check authentication
        $isAuthenticated = $request->session()->get('mobile_testing_auth', false);
        if (!$isAuthenticated) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }

        $request->validate([
            'verification_step' => 'required|numeric',
            'start_date' => 'required|string',
            'stop_date' => 'required|string',
            'nama_petugas' => 'required|string',
            'lab_id' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // Parse dates
            $start_date = $this->parseMobileStageDate($request->input('start_date'))->format('Y-m-d H:i:s');
            $stop_date = $this->parseMobileStageDate($request->input('stop_date'))->format('Y-m-d H:i:s');

            // Check if verification already exists
            $verificationActivitySample = VerificationActivitySample::where('id_sample', $id)
                ->where('id_verification_activity', $request->input('verification_step'))
                ->first();

            if ($verificationActivitySample) {
                // Update existing
                $verificationActivitySample->start_date = $start_date;
                $verificationActivitySample->stop_date = $stop_date;
                $verificationActivitySample->nama_petugas = $request->input('nama_petugas');
                $verificationActivitySample->is_done = 1;
                $verificationActivitySample->save();
            } else {
                // Create new
                $verificationActivitySample = new VerificationActivitySample();
                $verificationActivitySample->id = Uuid::uuid4()->toString();
                $verificationActivitySample->id_sample = $id;
                $verificationActivitySample->id_verification_activity = $request->input('verification_step');
                $verificationActivitySample->start_date = $start_date;
                $verificationActivitySample->stop_date = $stop_date;
                $verificationActivitySample->nama_petugas = $request->input('nama_petugas');
                $verificationActivitySample->is_done = 1;
                $verificationActivitySample->save();
            }

            DB::commit();

            // Step 3 (Input / Output Hasil Px) completed, redirect to verifikasi hasil
            $lab_id = $request->input('lab_id');
            // Get lab_id from laboratorium table
            $laboratorium = Laboratorium::find($lab_id);
            $lab_uuid = $laboratorium ? $laboratorium->id_laboratorium : $lab_id;
            
            return response()->json([
                'success' => true,
                'message' => 'Data verifikasi berhasil disimpan.',
                'next_step' => 'verifikasi_hasil',
                'redirect_url' => route('mobile.testing.verifikasiHasil', [
                    'id' => $id,
                    'lab_id' => $lab_uuid
                ])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MobileTesting: Error storing input hasil', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Baca Hasil form (mobile version)
     */
    public function bacaHasil(Request $request, $id, $lab_id, $method_id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_testing_auth', false)) {
            return redirect()->route('mobile.testing.login', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Check and redirect to correct step if user is at wrong position
        $redirectUrl = $this->checkAndRedirectToCorrectStep($request, $id, 'bacaHasil', $lab_id);
        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        // Use the same logic as web version but with mobile view
        Carbon::setLocale('id');
        $sample = Sample::where('tb_samples.id_samples', '=', $id)
            ->where('ms_laboratorium.id_laboratorium', '=', $lab_id)
            ->with(['permohonanuji', 'permohonanuji.customer', 'sampletype'])
            ->join('tb_sample_method', function ($join) {
                $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                    ->whereNull('tb_sample_method.deleted_at')
                    ->whereNull('tb_samples.deleted_at')
                    ->join('ms_laboratorium', function ($join) {
                        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
                            ->whereNull('ms_laboratorium.deleted_at')
                            ->whereNull('tb_sample_method.deleted_at');
                    });
            })
            ->join('ms_sample_type', function ($join) {
                $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                    ->whereNull('ms_sample_type.deleted_at')
                    ->whereNull('tb_samples.deleted_at');
            })
            ->first();

        if (!$sample) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data sample tidak ditemukan.'
            ]);
        }

        $lab = Laboratorium::find($lab_id);
        $sampletype_id = $sample->id_sample_type;
        // Untuk MBI makanan/minuman/lainnya:
        // - Jenis makanan menentukan baku mutu, jadi pilih jenis_makanan_id dulu (server-side),
        //   lalu query baku mutu berdasarkan jenis_makanan_id (bukan load semua via JS).
        $jenisMakananAll = collect();
        $autoJenisSarana = null;
        $jenis_makanan_id = null;

        $isMbiMakanan = ($lab && $lab->kode_laboratorium === 'MBI' && $sample->name_sample_type === 'Makanan/Minuman/Lainnya');
        if ($isMbiMakanan) {
            $jenis_makanan_id = $request->input('jenis_makanan_id') ?: ($sample->jenis_makanan_id ?? null);

            // Ambil method IDs dari sample ini (tanpa bergantung join baku mutu)
            $methodIds = SampleMethod::where('laboratorium_id', $lab_id)
                ->where('sample_id', $id)
                ->whereNull('deleted_at')
                ->pluck('method_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($methodIds)) {
                $jenisIds = BakuMutu::whereIn('method_id', $methodIds)
                    ->where('sampletype_id', $sampletype_id)
                    ->whereNull('deleted_at')
                    ->distinct()
                    ->pluck('jenis_makanan_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (!empty($jenisIds)) {
                    $jenisMakananAll = JenisMakanan::whereIn('id_jenis_makanan', $jenisIds)
                        ->orderBy('name_jenis_makanan')
                        ->get();
                }
            }

            // Fallback: jika tidak ketemu, tetap tampilkan semua jenis makanan
            if ($jenisMakananAll->isEmpty()) {
                $jenisMakananAll = JenisMakanan::orderBy('name_jenis_makanan')->get();
            }

            // Jika belum ada pilihan, default ke item pertama (seperti versi web)
            if (!$jenis_makanan_id && $jenisMakananAll->count() > 0) {
                $jenis_makanan_id = $jenisMakananAll->first()->id_jenis_makanan;
            }

            if ($jenis_makanan_id) {
                $jmSelected = $jenisMakananAll->firstWhere('id_jenis_makanan', $jenis_makanan_id);
                $autoJenisSarana = $jmSelected ? $jmSelected->name_jenis_makanan : null;
            }
        } else {
            // Non-MBI makanan: pertahankan perilaku lama
            $jenis_makanan_id = $request->input('jenis_makanan_id', $sample->jenis_makanan_id);
        }

        // Get laboratorium methods (filter baku mutu sesuai jenis makanan untuk MBI makanan)
        $useJenisMakanan = ($isMbiMakanan && !empty($jenis_makanan_id));
        if ($useJenisMakanan) {
            $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $lab_id)
                ->where('tb_sample_method.sample_id', '=', $id)
                ->join('ms_method', function ($join) use ($sampletype_id, $jenis_makanan_id, $lab_id, $id) {
                    $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                        ->whereNull('tb_sample_method.deleted_at')
                        ->whereNull('ms_method.deleted_at')
                        ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id) {
                            $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                                ->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id)
                                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                                ->whereNull('tb_baku_mutu.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        })
                        ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                            $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                            $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                                ->whereNull('unit_baku_mutu.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('ms_parameter_satuan_klinik', function ($join) {
                            $join->on('ms_parameter_satuan_klinik.id_parameter_satuan_klinik', '=', 'tb_baku_mutu.parameter_satuan_klinik_id')
                                ->whereNull('ms_parameter_satuan_klinik.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('tb_sample_result', function ($join) use ($id, $lab_id) {
                            $join->where('tb_sample_result.laboratorium_id', '=', $lab_id)
                                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                                ->where('tb_sample_result.sample_id', '=', $id)
                                ->whereNull('tb_sample_result.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        });
                })
                ->select(
                    'tb_baku_mutu.*',
                    'ms_method.*',
                    'tb_sample_method.*',
                    'unit_baku_mutu.*',
                    'ms_parameter_satuan_klinik.is_option as parameter_satuan_klinik_is_option',
                    'ms_parameter_satuan_klinik.option as parameter_satuan_klinik_option',
                    'ms_method.is_option as method_is_option',
                    'ms_method.option as method_option',
                    'tb_sample_result.hasil',
                    'tb_sample_result.keterangan',
                    'tb_sample_result.metode',
                    'tb_sample_result.offset_baku_mutu'
                )
                ->distinct('ms_method.id_method')
                ->get();
        } else {
            $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $lab_id)
                ->where('tb_sample_method.sample_id', '=', $id)
                ->orderBy('ms_method.jenis_parameter_kimia')
                ->join('ms_method', function ($join) use ($sampletype_id, $lab_id, $id) {
                    $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                        ->whereNull('tb_sample_method.deleted_at')
                        ->whereNull('ms_method.deleted_at')
                        ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $lab_id) {
                            $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                                ->where('tb_baku_mutu.lab_id', '=', $lab_id)
                                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                                ->whereNull('tb_baku_mutu.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        })
                        ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                            $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                            $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                                ->whereNull('unit_baku_mutu.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('ms_parameter_satuan_klinik', function ($join) {
                            $join->on('ms_parameter_satuan_klinik.id_parameter_satuan_klinik', '=', 'tb_baku_mutu.parameter_satuan_klinik_id')
                                ->whereNull('ms_parameter_satuan_klinik.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('tb_sample_result', function ($join) use ($id, $lab_id) {
                            $join->where('tb_sample_result.laboratorium_id', '=', $lab_id)
                                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                                ->where('tb_sample_result.sample_id', '=', $id)
                                ->whereNull('tb_sample_result.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        });
                })
                ->select(
                    'tb_baku_mutu.*',
                    'ms_method.*',
                    'tb_sample_method.*',
                    'unit_baku_mutu.*',
                    'ms_parameter_satuan_klinik.is_option as parameter_satuan_klinik_is_option',
                    'ms_parameter_satuan_klinik.option as parameter_satuan_klinik_option',
                    'ms_method.is_option as method_is_option',
                    'ms_method.option as method_option',
                    'tb_sample_result.hasil',
                    'tb_sample_result.keterangan',
                    'tb_sample_result.metode',
                    'tb_sample_result.offset_baku_mutu'
                )
                ->distinct('ms_method.id_method')
                ->get();
        }

        // catatan: untuk MBI makanan, jenisMakananAll/jenis_makanan_id/autoJenisSarana sudah ditentukan sebelum query di atas

        // Order by sample_type_details orderlist
        $sample_type_details = \Smt\Masterweb\Models\SampleTypeDetail::where('sample_type_id', $sampletype_id)
            ->orderBy('orderlist_sample_type_detail')
            ->get();

        $method_all_temp = [];
        foreach ($sample_type_details as $sample_type_detail) {
            foreach ($laboratoriummethods as $method) {
                if ($method->id_method == $sample_type_detail->method_id) {
                    $method_all_temp[] = $method;
                }
            }
        }
        if ($method_all_temp != [] && count($laboratoriummethods) == count($method_all_temp)) {
            $laboratoriummethods = $method_all_temp;
        }

        // Add detail for each method
        foreach ($laboratoriummethods as $key => $laboratoriummethod) {
            $laboratoriummethods[$key]->detail = SampleResultDetail::where('method_id', '=', $laboratoriummethod->id_method)
                ->where('sampletype_id', '=', $sampletype_id)
                ->where('sample_id', '=', $id)
                ->get();
        }

          //pengurutan order ist
        $sample_type_details = SampleTypeDetail::where('sample_type_id', $sampletype_id)->orderBy('orderlist_sample_type_detail')->get();



        $method_all_temp = [];


        foreach ($sample_type_details as $sample_type_detail) {
            # code...
            foreach ($laboratoriummethods as $method) {
                # code...


                // print("& ".$method->id_method." ".$sample_type_detail->method_id);
                if ($method->id_method == $sample_type_detail->method_id) {
                $method_all_temp[] = $method;
                }
            }
        }
        if ($method_all_temp != [] and count($laboratoriummethods) == count($method_all_temp)) {
        # code...
        $laboratoriummethods = $method_all_temp;
        }

        

        $lab = Laboratorium::where('id_laboratorium', '=', $lab_id)->first();
        
        // Get jenis sarana options (simplified)
        $jenis_sarana_options = [];
        if ($lab && $lab->kode_laboratorium == 'MBI') {
            $jenis_sarana_options = [
                ['value' => 'Rumah Sakit'],
                ['value' => 'Puskesmas'],
                ['value' => 'Klinik'],
                ['value' => 'Apotek'],
                ['value' => 'Laboratorium'],
                ['value' => 'Lainnya']
            ];
        }

        $units = Unit::all();
        $libraries = Library::all();
        $containers = Container::where('id_container', '!=', '0')->get();
        
        $sampleanalitikprogress = SampleAnalitikProgress::where("laboratorium_progress_id", $method_id)
            ->where('laboratorium_id', $lab_id)
            ->where('sample_id', $id)
            ->first();

        return view('masterweb::module.mobile.testing.baca-hasil', compact(
            'sampleanalitikprogress',
            'sample',
            'laboratoriummethods',
            'containers',
            'units',
            'libraries',
            'lab',
            'jenis_sarana_options',
            'lab_id',
            'method_id',
            'jenis_makanan_id',
            'jenisMakananAll',
            'autoJenisSarana'
        ));
    }

    /**
     * Store Baca Hasil data (mobile version)
     */
    public function storeBacaHasil(Request $request, $id, $lab_id, $method_id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_testing_auth', false)) {
            return response()->json([
                'status' => false,
                'pesan' => 'Silakan login terlebih dahulu.'
            ], 401);
        }

        // Validate
        $request->validate([
            'baca_hasil' => 'required'
        ]);

        try {
            DB::beginTransaction();

            $simpan_baca_hasil = false;
            $data = $request->all();

            $sampleanalitikprogress = SampleAnalitikProgress::where("laboratorium_progress_id", $method_id)
                ->where('laboratorium_id', $lab_id)
                ->where('sample_id', $id)
                ->first();

            if (isset($sampleanalitikprogress)) {
                $sample = Sample::where('tb_samples.id_samples', '=', $id)
                    ->where('ms_laboratorium.id_laboratorium', '=', $lab_id)
                    ->with(['permohonanuji', 'permohonanuji.customer'])
                    ->join('tb_sample_method', function ($join) {
                        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                            ->whereNull('tb_sample_method.deleted_at')
                            ->whereNull('tb_samples.deleted_at')
                            ->join('ms_laboratorium', function ($join) {
                                $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
                                    ->whereNull('ms_laboratorium.deleted_at')
                                    ->whereNull('tb_sample_method.deleted_at');
                            });
                    })
                    ->join('ms_sample_type', function ($join) {
                        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                            ->whereNull('ms_sample_type.deleted_at')
                            ->whereNull('tb_samples.deleted_at');
                    })
                    ->first();

                $sampletype_id = $sample->id_sample_type;

                // Ambil jenis_makanan_id dari request jika ada (mis. dari dropdown pada baca-hasil), jika tidak pakai yang tersimpan di sample
                $jenis_makanan_id = $request->input('jenis_makanan_id', $sample->jenis_makanan_id);

                if (isset($jenis_makanan_id)) {
                    $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $lab_id)
                        ->where('tb_sample_method.sample_id', '=', $id)
                        ->orderBy('ms_method.jenis_parameter_kimia')
                        ->join('ms_method', function ($join) use ($sampletype_id, $jenis_makanan_id, $lab_id, $id) {
                            $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                                ->whereNull('tb_sample_method.deleted_at')
                                ->whereNull('ms_method.deleted_at')
                                ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id) {
                                    $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                                        ->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id)
                                        ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                                        ->whereNull('tb_baku_mutu.deleted_at')
                                        ->whereNull('ms_method.deleted_at');
                                })
                                ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                                    $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                                        ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                                        ->whereNull('tb_baku_mutu.deleted_at');
                                })
                                ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                                    $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                                        ->whereNull('unit_baku_mutu.deleted_at')
                                        ->whereNull('tb_baku_mutu.deleted_at');
                                })
                                ->leftjoin('tb_sample_result', function ($join) use ($id, $lab_id) {
                                    $join->where('tb_sample_result.laboratorium_id', '=', $lab_id)
                                        ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                                        ->where('tb_sample_result.sample_id', '=', $id)
                                        ->whereNull('tb_sample_result.deleted_at')
                                        ->whereNull('ms_method.deleted_at');
                                });
                        })
                        ->select(
                            'tb_baku_mutu.*',
                            'ms_method.*',
                            'ms_method.id_method',
                            'tb_sample_method.*',
                            'unit_baku_mutu.*',
                            'tb_sample_result.hasil',
                            'tb_sample_result.offset_baku_mutu'
                        )
                        ->distinct('ms_method.id_method')
                        ->get();
                } else {
                    $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $lab_id)
                        ->where('tb_sample_method.sample_id', '=', $id)
                        ->orderBy('ms_method.jenis_parameter_kimia')
                        ->join('ms_method', function ($join) use ($sampletype_id, $lab_id, $id) {
                            $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                                ->whereNull('tb_sample_method.deleted_at')
                                ->whereNull('ms_method.deleted_at')
                                ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id) {
                                    $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                                        ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                                        ->whereNull('tb_baku_mutu.deleted_at')
                                        ->whereNull('ms_method.deleted_at');
                                })
                                ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                                    $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                                        ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                                        ->whereNull('tb_baku_mutu.deleted_at');
                                })
                                ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                                    $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                                        ->whereNull('unit_baku_mutu.deleted_at')
                                        ->whereNull('tb_baku_mutu.deleted_at');
                                })
                                ->leftjoin('tb_sample_result', function ($join) use ($id, $lab_id) {
                                    $join->where('tb_sample_result.laboratorium_id', '=', $lab_id)
                                        ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                                        ->where('tb_sample_result.sample_id', '=', $id)
                                        ->whereNull('tb_sample_result.deleted_at')
                                        ->whereNull('ms_method.deleted_at');
                                });
                        })
                        ->select(
                            'tb_baku_mutu.*',
                            'ms_method.*',
                            'ms_method.id_method',
                            'tb_sample_method.*',
                            'unit_baku_mutu.*',
                            'tb_sample_result.hasil',
                            'tb_sample_result.offset_baku_mutu'
                        )
                        ->get();
                }

                // Ambil selected_ruangan dari request untuk disimpan ke setiap SampleResult
                $selectedRuangan = $request->input('selected_ruangan');

                SampleResult::where("sample_id", $id)
                    ->where("laboratorium_id", $lab_id)->delete();

                foreach ($laboratoriummethods as $laboratoriummethod) {
                    $sampleresult = new SampleResult;
                    $uuid4 = Uuid::uuid4();
                    $sampleresult->method_id = $laboratoriummethod->method_id;
                    $sampleresult->sample_id = $id;
                    $sampleresult->laboratorium_id = $lab_id;

                    if (isset($data["status_" . $laboratoriummethod->method_id])) {
                        $sampleresult->offset_baku_mutu = $data["offset_baku_mutu_" . $laboratoriummethod->method_id];
                        $sampleresult->hasil = rubahNilaikeHtml(str_replace(",", ".", $data["result_method_" . $laboratoriummethod->method_id]));
                        $sampleresult->metode = $data["metode_" . $laboratoriummethod->method_id] ?? '';
                        $sampleresult->keterangan = $data["keterangan_" . $laboratoriummethod->method_id] ?? '';
                    } else {
                        $sampleresult->keterangan = $data["keterangan_" . $laboratoriummethod->method_id] ?? '';
                        $sampleresult->hasil = "-";
                    }

                    // Simpan lokasi_selected untuk Kualitas Udara
                    if ($selectedRuangan) {
                        $sampleresult->lokasi_selected = $selectedRuangan;
                    }

                    $simpan_baca_hasil = $sampleresult->save();

                    $sampleresultdetails = SampleResultDetail::where('method_id', '=', $laboratoriummethod->id_method)
                        ->where('sampletype_id', '=', $sampletype_id)
                        ->where('sample_id', '=', $id)->get();

                    foreach ($sampleresultdetails as $key => $sampleresultdetail) {
                        $sampleresultdetail_edit = SampleResultDetail::findOrFail($sampleresultdetail->id_sample_result_detail);
                        if (isset($data["status_" . $sampleresultdetail->id_sample_result_detail])) {
                            $sampleresultdetail_edit->hasil = rubahNilaikeHtml(str_replace(",", ".", $data["result_method_" . $sampleresultdetail->id_sample_result_detail]));
                        } else {
                            $sampleresultdetail_edit->hasil = "-";
                        }
                        $sampleresultdetail_edit->offset_baku_mutu = $data["offset_baku_mutu_" . $sampleresultdetail->id_sample_result_detail] ?? 'default';
                        $sampleresultdetail_edit->save();
                    }
                }

                // Simpan jenis_makanan_id dan nama_jenis_makanan untuk sampel makanan/minuman/lainnya
                $namaJenisInputStore = $request->input('nama_jenis_makanan');
                if ($request->has('jenis_makanan_id') && $request->get('jenis_makanan_id') !== '') {
                    $sample->jenis_makanan_id = $request->get('jenis_makanan_id');
                    // Jika user belum mengisi nama manual, isi default dari master JenisMakanan
                    if ($namaJenisInputStore === null || $namaJenisInputStore === '') {
                        $jmStore = JenisMakanan::find($sample->jenis_makanan_id);
                        if ($jmStore) {
                            $namaJenisInputStore = $jmStore->name_jenis_makanan;
                        }
                    }
                }
                if ($namaJenisInputStore !== null && $namaJenisInputStore !== '') {
                    $sample->nama_jenis_makanan = $namaJenisInputStore;
                }
                $sample->save();

                if (isset($request->perlakuan_usap_tangan_sample_analitik_progress) || $request->perlakuan_usap_tangan_sample_analitik_progress != null) {
                    $sampleanalitikprogress->perlakuan_usap_tangan_sample_analitik_progress = $request->perlakuan_usap_tangan_sample_analitik_progress;
                }
                $sampleanalitikprogress->save();

            } else {
                $sample = Sample::where('tb_samples.id_samples', '=', $id)
                    ->where('ms_laboratorium.id_laboratorium', '=', $lab_id)
                    ->join('tb_sample_method', function ($join) {
                        $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                            ->whereNull('tb_sample_method.deleted_at')
                            ->whereNull('tb_samples.deleted_at')
                            ->join('ms_laboratorium', function ($join) {
                                $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
                                    ->whereNull('ms_laboratorium.deleted_at')
                                    ->whereNull('tb_sample_method.deleted_at');
                            });
                    })
                    ->join('ms_sample_type', function ($join) {
                        $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                            ->whereNull('ms_sample_type.deleted_at')
                            ->whereNull('tb_samples.deleted_at');
                    })
                    ->first();

                $sampletype_id = $sample->id_sample_type;

                $laboratoriummethods = SampleMethod::where('laboratorium_id', '=', $lab_id)
                    ->where('sample_id', '=', $id)
                    ->orderBy('ms_method.created_at')
                    ->join('ms_method', function ($join) {
                        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                            ->whereNull('tb_sample_method.deleted_at')
                            ->whereNull('ms_method.deleted_at');
                    })
                    ->join('tb_baku_mutu', function ($join) use ($sampletype_id) {
                        $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                            ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                            ->whereNull('tb_baku_mutu.deleted_at')
                            ->whereNull('ms_method.deleted_at');
                    })
                    ->join('ms_unit as unit_baku_mutu', function ($join) {
                        $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                            ->whereNull('unit_baku_mutu.deleted_at')
                            ->whereNull('tb_baku_mutu.deleted_at');
                    })
                    ->select('tb_baku_mutu.*', 'ms_method.*', 'ms_method.id_method', 'tb_sample_method.*', 'unit_baku_mutu.*')
                    ->distinct('ms_method.id_method')
                    ->get();

                // Ambil selected_ruangan dari request untuk disimpan ke setiap SampleResult
                $selectedRuangan = $request->input('selected_ruangan');

                SampleResult::where("sample_id", $id)
                    ->where("laboratorium_id", $lab_id)->delete();

                foreach ($laboratoriummethods as $laboratoriummethod) {
                    $sampleresult = new SampleResult;
                    $uuid4 = Uuid::uuid4();
                    $sampleresult->method_id = $laboratoriummethod->method_id;
                    $sampleresult->sample_id = $id;
                    $sampleresult->laboratorium_id = $lab_id;
                    if (isset($data["status_" . $laboratoriummethod->method_id])) {
                        $sampleresult->offset_baku_mutu = $data["offset_baku_mutu_" . $laboratoriummethod->method_id];
                        $sampleresult->hasil = rubahNilaikeHtml(str_replace(",", ".", $data["result_method_" . $laboratoriummethod->method_id]));
                        $sampleresult->metode = $data["metode_" . $laboratoriummethod->method_id] ?? '';
                        $sampleresult->keterangan = $data["keterangan_" . $laboratoriummethod->method_id] ?? '';
                    } else {
                        $sampleresult->keterangan = $data["keterangan_" . $laboratoriummethod->method_id] ?? '';
                        $sampleresult->hasil = "-";
                    }

                    // Simpan lokasi_selected untuk Kualitas Udara
                    if ($selectedRuangan) {
                        $sampleresult->lokasi_selected = $selectedRuangan;
                    }

                    $simpan_baca_hasil = $sampleresult->save();

                    $sampleresultdetails = SampleResultDetail::where('method_id', '=', $laboratoriummethod->id_method)
                        ->where('sampletype_id', '=', $sampletype_id)
                        ->where('sample_id', '=', $id)->get();

                    foreach ($sampleresultdetails as $key => $sampleresultdetail) {
                        $sampleresultdetail_edit = SampleResultDetail::findOrFail($sampleresultdetail->id_sample_result_detail);
                        if (isset($data["status_" . $sampleresultdetail->id_sample_result_detail])) {
                            $sampleresultdetail_edit->hasil = rubahNilaikeHtml(str_replace(",", ".", $data["result_method_" . $sampleresultdetail->id_sample_result_detail]));
                        } else {
                            $sampleresultdetail_edit->hasil = "-";
                        }
                        $sampleresultdetail_edit->offset_baku_mutu = $data["offset_baku_mutu_" . $sampleresultdetail->id_sample_result_detail] ?? 'default';
                        $sampleresultdetail_edit->save();
                    }
                }

                // Simpan jenis_makanan_id dan nama_jenis_makanan untuk sampel makanan/minuman/lainnya
                $namaJenisInputStore = $request->input('nama_jenis_makanan');
                if ($request->has('jenis_makanan_id') && $request->get('jenis_makanan_id') !== '') {
                    $sample->jenis_makanan_id = $request->get('jenis_makanan_id');
                    // Jika user belum mengisi nama manual, isi default dari master JenisMakanan
                    if ($namaJenisInputStore === null || $namaJenisInputStore === '') {
                        $jmStore = JenisMakanan::find($sample->jenis_makanan_id);
                        if ($jmStore) {
                            $namaJenisInputStore = $jmStore->name_jenis_makanan;
                        }
                    }
                }
                if ($namaJenisInputStore !== null && $namaJenisInputStore !== '') {
                    $sample->nama_jenis_makanan = $namaJenisInputStore;
                }
                $sample->save();

                $sampleanalitikprogress = new SampleAnalitikProgress;
                $uuid4 = Uuid::uuid4();
                $sampleanalitikprogress->laboratorium_progress_id = $method_id;
                $sampleanalitikprogress->laboratorium_id = $lab_id;
                $sampleanalitikprogress->sample_id = $id;

                if (isset($request->perlakuan_usap_tangan_sample_analitik_progress) || $request->perlakuan_usap_tangan_sample_analitik_progress != null) {
                    $sampleanalitikprogress->perlakuan_usap_tangan_sample_analitik_progress = $request->perlakuan_usap_tangan_sample_analitik_progress;
                }

                $sampleanalitikprogress->save();
            }

            // Update sample data
            $sample = Sample::where('id_samples', $id)->first();
            if ($sample) {
                $sample->refresh();

                // Simpan Asal & Titik Sampel tergantung jenis laboratorium / jenis sampel
                $labStore = Laboratorium::find($lab_id);
                $titikStore = $request->get('titik_pengambilan');

                if ($labStore && $labStore->kode_laboratorium === 'MBI') {
                    // Asal Sampel selalu disimpan di permohonan_uji.detail_alamat_sampling
                    if ($request->filled('lokasi_pengambilan')) {
                        if (!$sample->relationLoaded('permohonanuji')) {
                            $sample->load('permohonanuji');
                        }
                        if ($sample->permohonanuji) {
                            $sample->permohonanuji->detail_alamat_sampling = $request->get('lokasi_pengambilan');
                            $sample->permohonanuji->save();
                        }
                    }

                    // Titik Sampel (Air Minum, Air Higiene — nama lama: Air Bersih, Uji Usap, Air Kolam Renang) → location_samples
                    $tipeStore = $sample->name_sample_type ?? null;
                    $jenisTitikSpesifikStore = ['Air Minum', 'Air Higiene', 'Air Bersih', 'Uji Usap', 'Air Kolam Renang'];
                    if ($tipeStore && in_array($tipeStore, $jenisTitikSpesifikStore)) {
                        $sample->location_samples = $titikStore;
                    } else {
                        // Untuk jenis lain, pertahankan perilaku lama (lokasi_pengambilan ke location_samples)
                        $sample->location_samples = $request->get('lokasi_pengambilan');
                    }
                    $sample->titik_pengambilan = $titikStore;
                } else {
                    // Non-mikro: tetap seperti sebelumnya
                    $sample->location_samples = $request->get('lokasi_pengambilan');
                    $sample->titik_pengambilan = $titikStore;
                }

                $sample->jenis_sarana_names = $request->get('jenis_sarana');

                // Update nama pengambil di permohonan uji
                if ($request->has('nama_pengambil')) {
                    $sample->syncNamaPengambil($request->get('nama_pengambil'));
                }

                $sample->save();
            }

            DB::commit();

            if ($simpan_baca_hasil == true) {
                return response()->json([
                    'status' => true,
                    'pesan' => "Data baca hasil berhasil disimpan!",
                    'url_redirect' => route('mobile.testing.inputHasil', ['id' => $id, 'lab_id' => $lab_id])
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'pesan' => "Data baca hasil tidak berhasil disimpan!"
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MobileTesting: Error storing baca hasil', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'pesan' => $e->getMessage()
            ], 200);
        }
    }

    /**
     * Show Verifikasi Hasil form (mobile version)
     */
    public function verifikasiHasil(Request $request, $id, $lab_id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_testing_auth', false)) {
            return redirect()->route('mobile.testing.login', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Check and redirect to correct step if user is at wrong position
        $redirectUrl = $this->checkAndRedirectToCorrectStep($request, $id, 'verifikasiHasil', $lab_id);
        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        // Get sample
        $sample = Sample::where('tb_samples.id_samples', '=', $id)
            ->where('ms_laboratorium.id_laboratorium', '=', $lab_id)
            ->with(['permohonanuji', 'permohonanuji.customer'])
            ->join('tb_sample_method', function ($join) {
                $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                    ->whereNull('tb_sample_method.deleted_at')
                    ->whereNull('tb_samples.deleted_at')
                    ->join('ms_laboratorium', function ($join) {
                        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
                            ->whereNull('ms_laboratorium.deleted_at')
                            ->whereNull('tb_sample_method.deleted_at');
                    });
            })
            ->join('ms_sample_type', function ($join) {
                $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                    ->whereNull('ms_sample_type.deleted_at')
                    ->whereNull('tb_samples.deleted_at');
            })
            ->first();

        if (!$sample) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data sample tidak ditemukan.'
            ]);
        }

        $lab = Laboratorium::find($lab_id);
        $sampletype_id = $sample->id_sample_type;
        // Untuk MBI makanan/minuman/lainnya:
        // - Jenis makanan menentukan baku mutu, jadi pilih jenis_makanan_id dulu (server-side),
        //   lalu query baku mutu berdasarkan jenis_makanan_id (bukan load semua via JS).
        $jenisMakananAll = collect();
        $autoJenisSarana = null;
        $jenis_makanan_id = null;

        $isMbiMakanan = ($lab && $lab->kode_laboratorium === 'MBI' && $sample->name_sample_type === 'Makanan/Minuman/Lainnya');
        if ($isMbiMakanan) {
            $jenis_makanan_id = $request->input('jenis_makanan_id') ?: ($sample->jenis_makanan_id ?? null);

            // Ambil method IDs dari sample ini (tanpa bergantung join baku mutu)
            $methodIds = SampleMethod::where('laboratorium_id', $lab_id)
                ->where('sample_id', $id)
                ->whereNull('deleted_at')
                ->pluck('method_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($methodIds)) {
                $jenisIds = BakuMutu::whereIn('method_id', $methodIds)
                    ->where('sampletype_id', $sampletype_id)
                    ->whereNull('deleted_at')
                    ->distinct()
                    ->pluck('jenis_makanan_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (!empty($jenisIds)) {
                    $jenisMakananAll = JenisMakanan::whereIn('id_jenis_makanan', $jenisIds)
                        ->orderBy('name_jenis_makanan')
                        ->get();
                }
            }

            // Fallback: jika tidak ketemu sama sekali, tetap tampilkan semua jenis makanan
            if ($jenisMakananAll->isEmpty()) {
                $jenisMakananAll = JenisMakanan::orderBy('name_jenis_makanan')->get();
            }

            // Default: kalau kosong, pilih jenis makanan paling atas (sesuai versi web)
            if (!$jenis_makanan_id && $jenisMakananAll->count() > 0) {
                $jenis_makanan_id = $jenisMakananAll->first()->id_jenis_makanan;
            }

            if ($jenis_makanan_id) {
                $jmSelected = $jenisMakananAll->firstWhere('id_jenis_makanan', $jenis_makanan_id);
                $autoJenisSarana = $jmSelected ? $jmSelected->name_jenis_makanan : null;
            }
        } else {
            // Non-MBI makanan: pertahankan perilaku lama
            $jenis_makanan_id = $request->input('jenis_makanan_id', $sample->jenis_makanan_id);
        }

        // Get laboratorium methods with results (same logic as web version)
        $useJenisMakanan = ($isMbiMakanan && !empty($jenis_makanan_id));
        if ($useJenisMakanan) {
            $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $lab_id)
                ->where('tb_sample_method.sample_id', '=', $id)
                ->orderBy('ms_method.jenis_parameter_kimia')
                ->join('ms_method', function ($join) use ($sampletype_id, $jenis_makanan_id, $lab_id, $id) {
                    $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                        ->whereNull('tb_sample_method.deleted_at')
                        ->whereNull('ms_method.deleted_at')
                        ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id) {
                            $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                                ->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id)
                                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                                ->whereNull('tb_baku_mutu.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        })
                        ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                            $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                            $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                                ->whereNull('unit_baku_mutu.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('tb_sample_result', function ($join) use ($id, $lab_id) {
                            $join->where('tb_sample_result.laboratorium_id', '=', $lab_id)
                                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                                ->where('tb_sample_result.sample_id', '=', $id)
                                ->whereNull('tb_sample_result.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        });
                })
                ->select(
                    'tb_baku_mutu.*',
                    'ms_method.*',
                    'ms_method.id_method',
                    'tb_sample_method.*',
                    'unit_baku_mutu.*',
                    'ms_method.is_option as method_is_option',
                    'ms_method.option as method_option',
                    'tb_sample_result.hasil',
                    'tb_sample_result.metode',
                    'tb_sample_result.keterangan',
                    'tb_sample_result.offset_baku_mutu'
                )
                ->distinct('ms_method.id_method')
                ->get();
        } else {
            $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $lab_id)
                ->where('tb_sample_method.sample_id', '=', $id)
                ->orderBy('ms_method.jenis_parameter_kimia')
                ->join('ms_method', function ($join) use ($sampletype_id, $lab_id, $id) {
                    $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                        ->whereNull('tb_sample_method.deleted_at')
                        ->whereNull('ms_method.deleted_at')
                        ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $lab_id) {
                            $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                                ->where('tb_baku_mutu.lab_id', '=', $lab_id)
                                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                                ->whereNull('tb_baku_mutu.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        })
                        ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                            $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                            $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                                ->whereNull('unit_baku_mutu.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('tb_sample_result', function ($join) use ($id, $lab_id) {
                            $join->where('tb_sample_result.laboratorium_id', '=', $lab_id)
                                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                                ->where('tb_sample_result.sample_id', '=', $id)
                                ->whereNull('tb_sample_result.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        });
                })
                ->select(
                    'tb_baku_mutu.*',
                    'ms_method.*',
                    'ms_method.id_method',
                    'tb_sample_method.*',
                    'unit_baku_mutu.*',
                    'ms_method.is_option as method_is_option',
                    'ms_method.option as method_option',
                    'tb_sample_result.hasil',
                    'tb_sample_result.metode',
                    'tb_sample_result.keterangan',
                    'tb_sample_result.offset_baku_mutu'
                )
                ->distinct('ms_method.id_method')
                ->get();
        }

        // Add detail to each method (same logic as web version)
        foreach ($laboratoriummethods as $key => $laboratoriummethod) {
            $method_id = $laboratoriummethod->id_method ?? $laboratoriummethod->method_id;
            $laboratoriummethods[$key]->detail = array();
            $laboratoriummethods[$key]->detail = SampleResultDetail::where('method_id', '=', $method_id)
                ->where('sampletype_id', '=', $sampletype_id)
                ->where('sample_id', '=', $id)
                ->get();
            
            // For each detail, get hasil from SampleResultDetail if exists
            foreach ($laboratoriummethods[$key]->detail as $detail_key => $detail) {
                // Get hasil from SampleResultDetail table (where hasil is stored per detail)
                $sample_result_detail = SampleResultDetail::where('id_sample_result_detail', $detail->id_sample_result_detail)
                    ->where('sample_id', $id)
                    ->first();
                
                if ($sample_result_detail) {
                    $laboratoriummethods[$key]->detail[$detail_key]->hasil = $sample_result_detail->hasil ?? null;
                    $laboratoriummethods[$key]->detail[$detail_key]->offset_baku_mutu = $sample_result_detail->offset_baku_mutu ?? 'default';
                }
            }
            
            // Ensure method_id is available
            if (!isset($laboratoriummethods[$key]->method_id)) {
                $laboratoriummethods[$key]->method_id = $method_id;
            }
        }

          //pengurutan order ist
          $sample_type_details = SampleTypeDetail::where('sample_type_id', $sampletype_id)->orderBy('orderlist_sample_type_detail')->get();



          $method_all_temp = [];
  
  
          foreach ($sample_type_details as $sample_type_detail) {
              # code...
              foreach ($laboratoriummethods as $method) {
                  # code...
  
  
                  // print("& ".$method->id_method." ".$sample_type_detail->method_id);
                  if ($method->id_method == $sample_type_detail->method_id) {
                  $method_all_temp[] = $method;
                  }
              }
          }
          if ($method_all_temp != [] and count($laboratoriummethods) == count($method_all_temp)) {
          # code...
          $laboratoriummethods = $method_all_temp;
          }
  

        // Get jenis sarana options for MBI (disembunyikan di UI, tapi tetap disediakan jika dibutuhkan)
        $jenis_sarana_options = [];
        if ($sample->kode_laboratorium == 'MBI') {
            $jenis_sarana_options = [
                ['value' => 'Air Minum'],
                ['value' => 'Air Higiene'],
                ['value' => 'Lainnya'],
            ];
        }
        // catatan: untuk MBI makanan, jenisMakananAll/jenis_makanan_id/autoJenisSarana sudah ditentukan sebelum query di atas

        return view('masterweb::module.mobile.testing.verifikasi-hasil', compact(
            'sample',
            'laboratoriummethods',
            'lab',
            'lab_id',
            'jenis_sarana_options',
            'jenis_makanan_id',
            'jenisMakananAll',
            'autoJenisSarana'
        ));
    }

    /**
     * Store Verifikasi Hasil data
     */
    public function storeVerifikasiHasil(Request $request, $id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_testing_auth', false)) {
            return response()->json([
                'status' => false,
                'pesan' => 'Silakan login terlebih dahulu.'
            ], 401);
        }

        $request->validate([
            'baca_hasil' => 'required',
            'lab_id' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();
            $lab_id = $request->input('lab_id');

            // Get sample
            $sample = Sample::where('id_samples', $id)->first();
            if (!$sample) {
                return response()->json([
                    'status' => false,
                    'pesan' => 'Data sample tidak ditemukan.'
                ], 404);
            }

            $sampletype_id = $sample->id_sample_type;
            // Ambil jenis_makanan_id dari request jika ada (mis. dari dropdown pada verifikasi-hasil), jika tidak pakai yang tersimpan di sample
            $jenis_makanan_id = $request->input('jenis_makanan_id', $sample->jenis_makanan_id);

            // Get laboratorium methods (same logic as LaboratoriumVerifikasiHasilManagement@store)
            if (isset($jenis_makanan_id)) {
                $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $lab_id)
                    ->where('tb_sample_method.sample_id', '=', $id)
                    ->orderBy('ms_method.jenis_parameter_kimia')
                    ->join('ms_method', function ($join) use ($sampletype_id, $jenis_makanan_id, $lab_id, $id) {
                        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                            ->whereNull('tb_sample_method.deleted_at')
                            ->whereNull('ms_method.deleted_at')
                            ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id) {
                                $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                                    ->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id)
                                    ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                                    ->whereNull('tb_baku_mutu.deleted_at')
                                    ->whereNull('ms_method.deleted_at');
                            })
                            ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                                $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                                    ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                                    ->whereNull('tb_baku_mutu.deleted_at');
                            })
                            ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                                $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                                    ->whereNull('unit_baku_mutu.deleted_at')
                                    ->whereNull('tb_baku_mutu.deleted_at');
                            })
                            ->leftjoin('tb_sample_result', function ($join) use ($id, $lab_id) {
                                $join->where('tb_sample_result.laboratorium_id', '=', $lab_id)
                                    ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                                    ->where('tb_sample_result.sample_id', '=', $id)
                                    ->whereNull('tb_sample_result.deleted_at')
                                    ->whereNull('ms_method.deleted_at');
                            });
                    })
                    ->select(
                        'tb_baku_mutu.*',
                        'ms_method.*',
                        'tb_sample_method.*',
                        'unit_baku_mutu.*',
                        'tb_sample_result.hasil',
                        'tb_sample_result.offset_baku_mutu'
                    )
                    ->distinct('ms_method.id_method')
                    ->get();
            } else {
                $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $lab_id)
                    ->where('tb_sample_method.sample_id', '=', $id)
                    ->orderBy('ms_method.jenis_parameter_kimia')
                    ->join('ms_method', function ($join) use ($sampletype_id, $lab_id, $id) {
                        $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                            ->whereNull('tb_sample_method.deleted_at')
                            ->whereNull('ms_method.deleted_at')
                            ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $lab_id) {
                                $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                                    ->where('tb_baku_mutu.lab_id', '=', $lab_id)
                                    ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                                    ->whereNull('tb_baku_mutu.deleted_at')
                                    ->whereNull('ms_method.deleted_at');
                            })
                            ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                                $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                                    ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                                    ->whereNull('tb_baku_mutu.deleted_at');
                            })
                            ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                                $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                                    ->whereNull('unit_baku_mutu.deleted_at')
                                    ->whereNull('tb_baku_mutu.deleted_at');
                            })
                            ->leftjoin('tb_sample_result', function ($join) use ($id, $lab_id) {
                                $join->where('tb_sample_result.laboratorium_id', '=', $lab_id)
                                    ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                                    ->where('tb_sample_result.sample_id', '=', $id)
                                    ->whereNull('tb_sample_result.deleted_at')
                                    ->whereNull('ms_method.deleted_at');
                            });
                    })
                    ->select(
                        'tb_baku_mutu.*',
                        'ms_method.*',
                        'tb_sample_method.*',
                        'unit_baku_mutu.*',
                        'tb_sample_result.hasil',
                        'tb_sample_result.offset_baku_mutu'
                    )
                    ->distinct('ms_method.id_method')
                    ->get();
            }

            // Delete existing results and save new ones (same logic as web version)
            SampleResult::where("sample_id", $id)
                ->where("laboratorium_id", $lab_id)
                ->delete();

            foreach ($laboratoriummethods as $laboratoriummethod) {
                // Use method_id from tb_sample_method (same as web version)
                // In web version, they use $laboratoriummethod->method_id which comes from tb_sample_method.method_id
                $method_id = $laboratoriummethod->method_id ?? $laboratoriummethod->id_method;
                
                $sampleresult = new SampleResult;
                $uuid4 = Uuid::uuid4();
                $sampleresult->id_sample_result = $uuid4->toString();
                $sampleresult->method_id = $method_id;
                $sampleresult->sample_id = $id;
                $sampleresult->laboratorium_id = $lab_id;
                
                if (isset($data["status_" . $method_id])) {
                    $sampleresult->offset_baku_mutu = $data["offset_baku_mutu_" . $method_id] ?? 'default';
                    $sampleresult->hasil = isset($data["result_method_" . $method_id]) 
                        ? rubahNilaikeHtml(str_replace(",", ".", $data["result_method_" . $method_id]))
                        : "-";
                    $sampleresult->metode = $data["metode_" . $method_id] ?? '';
                    $sampleresult->keterangan = $data["keterangan_" . $method_id] ?? '';
                } else {
                    $sampleresult->keterangan = $data["keterangan_" . $method_id] ?? '';
                    $sampleresult->hasil = "-";
                }
                $sampleresult->save();

                // Handle SampleResultDetail (same as web version)
                // Use id_method from ms_method for SampleResultDetail query
                $id_method = $laboratoriummethod->id_method ?? $method_id;
                $sampleresultdetails = SampleResultDetail::where('method_id', '=', $id_method)
                    ->where('sampletype_id', '=', $sampletype_id)
                    ->where('sample_id', '=', $id)
                    ->get();

                foreach ($sampleresultdetails as $key => $sampleresultdetail) {
                    $sampleresultdetail_edit = SampleResultDetail::findOrFail($sampleresultdetail->id_sample_result_detail);
                    if (isset($data["status_" . $sampleresultdetail->id_sample_result_detail])) {
                        $sampleresultdetail_edit->hasil = rubahNilaikeHtml(str_replace(",", ".", $data["result_method_" . $sampleresultdetail->id_sample_result_detail]));
                    } else {
                        $sampleresultdetail_edit->hasil = "-";
                    }
                    $sampleresultdetail_edit->offset_baku_mutu = $data["offset_baku_mutu_" . $sampleresultdetail->id_sample_result_detail] ?? 'default';
                    $sampleresultdetail_edit->save();
                }
            }

            // Update sample location and jenis sarana
            if ($request->has('lokasi_pengambilan')) {
                $sample->location_samples = $request->get('lokasi_pengambilan');
            }
            if ($request->has('jenis_sarana')) {
                $sample->jenis_sarana_names = $request->get('jenis_sarana');
            }
            if ($request->has('titik_pengambilan')) {
                $sample->titik_pengambilan = $request->get('titik_pengambilan');
            }
            if ($request->has('nama_pengambil')) {
                $sample->syncNamaPengambil($request->get('nama_pengambil'));
            }

            // Simpan jenis_makanan_id dan nama_jenis_makanan untuk sampel makanan/minuman/lainnya (seperti versi web)
            $namaJenisInputStore = $request->input('nama_jenis_makanan');
            if ($request->has('jenis_makanan_id') && $request->get('jenis_makanan_id') !== '') {
                $sample->jenis_makanan_id = $request->get('jenis_makanan_id');
                // Jika user belum mengisi nama manual, isi default dari master JenisMakanan
                if ($namaJenisInputStore === null || $namaJenisInputStore === '') {
                    $jmStore = JenisMakanan::find($sample->jenis_makanan_id);
                    if ($jmStore) {
                        $namaJenisInputStore = $jmStore->name_jenis_makanan;
                    }
                }
            }
            if ($namaJenisInputStore !== null && $namaJenisInputStore !== '') {
                $sample->nama_jenis_makanan = $namaJenisInputStore;
            }

            $sample->save();

            // Save or update VerifikasiHasil (same as web version)
            $verifikasi_hasil = VerifikasiHasil::where('sample_id', $id)
                ->where('laboratorium_id', $lab_id)
                ->first();

            if ($verifikasi_hasil) {
                // Update existing - tanggal akan diupdate di step inputTanggalVerifikasi
                // Keep existing verifikasi_hasil_date if exists
            } else {
                // Create new VerifikasiHasil (tanggal akan diisi di step inputTanggalVerifikasi)
                $verifikasi_hasil = new VerifikasiHasil();
                $uuid4 = Uuid::uuid4();
                $verifikasi_hasil->id_verifikasi_hasil = $uuid4->toString();
                $verifikasi_hasil->sample_id = $id;
                $verifikasi_hasil->laboratorium_id = $lab_id;
                // verifikasi_hasil_date akan diisi di step inputTanggalVerifikasi
                $verifikasi_hasil->verifikasi_hasil_date = Carbon::now()->format('Y-m-d H:i:s');
                $verifikasi_hasil->save();
            }

            // Save VerificationActivitySample for step 4 (Verifikasi Hasil) with is_done = 0
            // $verificationActivitySample = VerificationActivitySample::where('id_sample', $id)
            //     ->where('id_verification_activity', 4)
            //     ->first();

            // if ($verificationActivitySample) {
            //     // Update existing - keep is_done = 0
            //     $verificationActivitySample->is_done = 0;
            //     $verificationActivitySample->save();
            // } else {
            //     // Create new
            //     $verificationActivitySample = new VerificationActivitySample();
            //     $verificationActivitySample->id = Uuid::uuid4()->toString();
            //     $verificationActivitySample->id_sample = $id;
            //     $verificationActivitySample->id_verification_activity = 4;
            //     $verificationActivitySample->is_done = 0;
            //     $verificationActivitySample->save();
            // }

            DB::commit();

            $redirectUrl = route('mobile.testing.inputValidasi', ['id' => $id, 'lab_id' => $lab_id]);

            // Redirect to input tanggal verifikasi (support ajax + normal submit)
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => true,
                    'pesan' => "Data verifikasi hasil berhasil disimpan!",
                    'url_redirect' => $redirectUrl
                ], 200);
            }

            return redirect($redirectUrl)->with('success', 'Data verifikasi hasil berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MobileTesting: Error storing verifikasi hasil', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'pesan' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Pengesahan Hasil (validation) page for mobile
     */
    public function pengesahanHasil(Request $request, $id, $lab_id)
    {
        if (!$request->session()->get('mobile_testing_auth', false)) {
            return redirect()->route('mobile.testing.login', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $redirectUrl = $this->checkAndRedirectToCorrectStep($request, $id, 'pengesahanHasil', $lab_id);
        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        Carbon::setLocale('id');
        $sample = Sample::where('tb_samples.id_samples', '=', $id)
            ->where('ms_laboratorium.id_laboratorium', '=', $lab_id)
            ->with(['permohonanuji', 'permohonanuji.customer'])
            ->join('tb_sample_method', function ($join) {
                $join->on('tb_sample_method.sample_id', '=', 'tb_samples.id_samples')
                    ->whereNull('tb_sample_method.deleted_at')
                    ->whereNull('tb_samples.deleted_at')
                    ->join('ms_laboratorium', function ($join) {
                        $join->on('ms_laboratorium.id_laboratorium', '=', 'tb_sample_method.laboratorium_id')
                            ->whereNull('ms_laboratorium.deleted_at')
                            ->whereNull('tb_sample_method.deleted_at');
                    });
            })
            ->join('ms_sample_type', function ($join) {
                $join->on('ms_sample_type.id_sample_type', '=', 'tb_samples.typesample_samples')
                    ->whereNull('ms_sample_type.deleted_at')
                    ->whereNull('tb_samples.deleted_at');
            })
            ->first();

        if (!$sample) {
            return redirect()->route('mobile.testing.status', ['id' => $id])
                ->with('error', 'Data sample tidak ditemukan.');
        }

        $lab = Laboratorium::find($lab_id);
        $sampletype_id = $sample->id_sample_type;
        // Untuk MBI makanan/minuman/lainnya:
        // - Gunakan jenis_makanan_id yang SUDAH TERSIMPAN di tb_samples (tanpa dropdown di pengesahan).
        // - Baku mutu harus mengikuti jenis makanan tsb (seperti versi web).
        $jenis_makanan_id = $sample->jenis_makanan_id;
        $isMbiMakanan = ($lab && $lab->kode_laboratorium === 'MBI' && $sample->name_sample_type === 'Makanan/Minuman/Lainnya');

        $useJenisMakanan = ($isMbiMakanan && !empty($jenis_makanan_id));
        if ($useJenisMakanan) {
            $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $lab_id)
                ->where('tb_sample_method.sample_id', '=', $id)
                ->orderBy('ms_method.jenis_parameter_kimia')
                ->join('ms_method', function ($join) use ($sampletype_id, $jenis_makanan_id, $lab_id, $id) {
                    $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                        ->whereNull('tb_sample_method.deleted_at')
                        ->whereNull('ms_method.deleted_at')
                        ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id) {
                            $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                                ->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id)
                                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                                ->whereNull('tb_baku_mutu.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        })
                        ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                            $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                            $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                                ->whereNull('unit_baku_mutu.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('tb_sample_result', function ($join) use ($id, $lab_id) {
                            $join->where('tb_sample_result.laboratorium_id', '=', $lab_id)
                                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                                ->where('tb_sample_result.sample_id', '=', $id)
                                ->whereNull('tb_sample_result.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        });
                })
                ->select(
                    'tb_baku_mutu.*',
                    'ms_method.*',
                    'tb_sample_method.*',
                    'unit_baku_mutu.*',
                    'tb_sample_result.hasil',
                    'tb_sample_result.metode',
                    'tb_sample_result.keterangan',
                    'tb_sample_result.offset_baku_mutu'
                )
                ->distinct('ms_method.id_method')
                ->get();
        } else {
            $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $lab_id)
                ->where('tb_sample_method.sample_id', '=', $id)
                ->orderBy('ms_method.jenis_parameter_kimia')
                ->join('ms_method', function ($join) use ($sampletype_id, $lab_id, $id) {
                    $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                        ->whereNull('tb_sample_method.deleted_at')
                        ->whereNull('ms_method.deleted_at')
                        ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $lab_id) {
                            $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                                ->where('tb_baku_mutu.lab_id', '=', $lab_id)
                                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                                ->whereNull('tb_baku_mutu.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        })
                        ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                            $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                            $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                                ->whereNull('unit_baku_mutu.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('tb_sample_result', function ($join) use ($id, $lab_id) {
                            $join->where('tb_sample_result.laboratorium_id', '=', $lab_id)
                                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                                ->where('tb_sample_result.sample_id', '=', $id)
                                ->whereNull('tb_sample_result.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        });
                })
                ->select(
                    'tb_baku_mutu.*',
                    'ms_method.*',
                    'tb_sample_method.*',
                    'unit_baku_mutu.*',
                    'tb_sample_result.hasil',
                    'tb_sample_result.metode',
                    'tb_sample_result.keterangan',
                    'tb_sample_result.offset_baku_mutu'
                )
                ->distinct('ms_method.id_method')
                ->get();
        }

        // Pastikan benar-benar unik per method (join detail/non-klinik bisa menggandakan row walaupun sudah DISTINCT)
        $laboratoriummethods = collect($laboratoriummethods)
            ->unique(function ($m) {
                return $m->id_method ?? $m->method_id ?? null;
            })
            ->values();

        foreach ($laboratoriummethods as $key => $laboratoriummethod) {
            $method_id = $laboratoriummethod->id_method ?? $laboratoriummethod->method_id;
            $laboratoriummethods[$key]->detail = SampleResultDetail::where('method_id', '=', $method_id)
                ->where('sampletype_id', '=', $sampletype_id)
                ->where('sample_id', '=', $id)
                ->get();

            foreach ($laboratoriummethods[$key]->detail as $detail_key => $detail) {
                $sample_result_detail = SampleResultDetail::where('id_sample_result_detail', $detail->id_sample_result_detail)
                    ->where('sample_id', $id)
                    ->first();

                if ($sample_result_detail) {
                    $laboratoriummethods[$key]->detail[$detail_key]->hasil = $sample_result_detail->hasil ?? null;
                    $laboratoriummethods[$key]->detail[$detail_key]->offset_baku_mutu = $sample_result_detail->offset_baku_mutu ?? 'default';
                }
            }

            if (!isset($laboratoriummethods[$key]->method_id)) {
                $laboratoriummethods[$key]->method_id = $method_id;
            }
        }

          //pengurutan order ist
          $sample_type_details = SampleTypeDetail::where('sample_type_id', $sampletype_id)->orderBy('orderlist_sample_type_detail')->get();



          $method_all_temp = [];
  
  
          foreach ($sample_type_details as $sample_type_detail) {
              # code...
              foreach ($laboratoriummethods as $method) {
                  # code...
  
  
                  // print("& ".$method->id_method." ".$sample_type_detail->method_id);
                  if ($method->id_method == $sample_type_detail->method_id) {
                  $method_all_temp[] = $method;
                  }
              }
          }
          if ($method_all_temp != [] and count($laboratoriummethods) == count($method_all_temp)) {
          # code...
          $laboratoriummethods = $method_all_temp;
          }
  

        // catatan: $lab sudah diambil di atas
        $pengesahan_hasil = PengesahanHasil::where('sample_id', $id)
            ->where('laboratorium_id', $lab_id)
            ->first();
        $default_pengesahan_date = $pengesahan_hasil
            ? Carbon::parse($pengesahan_hasil->pengesahan_hasil_date)->format('d/m/Y')
            : Carbon::now()->format('d/m/Y');

        return view('masterweb::module.mobile.testing.pengesahan-hasil', compact(
            'sample',
            'laboratoriummethods',
            'lab',
            'lab_id',
            'pengesahan_hasil',
            'default_pengesahan_date'
        ));
    }

    /**
     * Store Pengesahan Hasil (mobile)
     */
    public function storePengesahanHasil(Request $request, $id)
    {
        if (!$request->session()->get('mobile_testing_auth', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }

        $request->validate([
            'lab_id' => 'required|string',
            'pengesahan_hasil' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $lab_id = $request->input('lab_id');
            $date = Carbon::createFromFormat('d/m/Y', trim($request->input('pengesahan_hasil')))
                ->format('Y-m-d H:i:s');

            $pengesahan = PengesahanHasil::where('sample_id', $id)
                ->where('laboratorium_id', $lab_id)
                ->first();

            if ($pengesahan) {
                $pengesahan->pengesahan_hasil_date = $date;
                $pengesahan->save();
            } else {
                $pengesahan = new PengesahanHasil();
                $pengesahan->id_pengesahan_hasil = Uuid::uuid4()->toString();
                $pengesahan->sample_id = $id;
                $pengesahan->laboratorium_id = $lab_id;
                $pengesahan->pengesahan_hasil_date = $date;
                $pengesahan->save();
            }

            DB::commit();

            $redirectUrl = route('mobile.testing.status', ['id' => $id]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pengesahan hasil berhasil disimpan!',
                    'url_redirect' => $redirectUrl
                ], 200);
            }

            return redirect($redirectUrl)->with('success', 'Pengesahan hasil berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MobileTesting: Error storing pengesahan hasil', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $message = 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 500);
            }

            return redirect()->back()->with('error', $message);
        }
    }

    /**
     * Show Input Tanggal Validasi form
     */
    public function inputValidasi(Request $request, $id, $lab_id)
    {
        if (!$request->session()->get('mobile_testing_auth', false)) {
            return redirect()->route('mobile.testing.login', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $redirectUrl = $this->checkAndRedirectToCorrectStep($request, $id, 'inputValidasi', $lab_id);
        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        $sample = Sample::where('id_samples', $id)
            ->with(['permohonanuji', 'samplemethod.laboratorium', 'sampletype'])
            ->first();

        if (!$sample) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data sample tidak ditemukan.'
            ]);
        }

        $laboratorium = Laboratorium::find($lab_id);
        if (!$laboratorium) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data laboratorium tidak ditemukan.'
            ]);
        }

        $existing_validation = VerificationActivitySample::where('id_sample', $id)
            ->where('id_verification_activity', 5)
            ->first();

        $verificationActivity = VerificationActivity::all()->keyBy('id');
        $list_name_petugas = [];
        if ($laboratorium->kode_laboratorium == 'MBI') {
            $list_name_petugas = explode(', ', $verificationActivity[5]->mikro ?? '');
        } elseif ($laboratorium->kode_laboratorium == 'KIM') {
            $list_name_petugas = explode(', ', $verificationActivity[5]->kimia ?? '');
        } else {
            $list_name_petugas = explode(', ', $verificationActivity[5]->klinik ?? '');
        }
        $list_name_petugas = array_values(array_filter(array_map('trim', $list_name_petugas), function ($value) {
            return $value !== '';
        }));

        $default_validator = $existing_validation->nama_petugas ?? ($list_name_petugas[0] ?? null);

        $pengesahan_hasil = PengesahanHasil::where('sample_id', $id)
            ->where('laboratorium_id', $lab_id)
            ->first();

        $verifikasi_step = VerificationActivitySample::where('id_sample', $id)
            ->where('id_verification_activity', 4)
            ->first();

        $default_start_date = null;
        $default_stop_date = null;

        if ($existing_validation) {
            $default_start_date = Carbon::parse($existing_validation->start_date)->format('d/m/Y');
            $default_stop_date = Carbon::parse($existing_validation->stop_date)->format('d/m/Y');
        } elseif ($verifikasi_step && $verifikasi_step->stop_date) {
            $validasiStart = Carbon::parse($verifikasi_step->stop_date);
            if ($validasiStart->hour < 8) {
                $validasiStart->setTime(8, 0, 0);
            } elseif ($validasiStart->hour >= 15) {
                $validasiStart->addDay()->setTime(8, 0, 0);
            }
            $validasiStop = $validasiStart->copy()->addHour();
            if ($validasiStop->hour >= 15) {
                $validasiStop->addDay()->setTime(8, 0, 0);
            }
            $default_start_date = $validasiStart->format('d/m/Y');
            $default_stop_date = $validasiStop->format('d/m/Y');
        } elseif ($pengesahan_hasil && $pengesahan_hasil->pengesahan_hasil_date) {
            $validasiStart = Carbon::parse($pengesahan_hasil->pengesahan_hasil_date);
            if ($validasiStart->hour < 8) {
                $validasiStart->setTime(8, 0, 0);
            } elseif ($validasiStart->hour >= 15) {
                $validasiStart->addDay()->setTime(8, 0, 0);
            }
            $validasiStop = $validasiStart->copy()->addHour();
            if ($validasiStop->hour >= 15) {
                $validasiStop->addDay()->setTime(8, 0, 0);
            }
            $default_start_date = $validasiStart->format('d/m/Y');
            $default_stop_date = $validasiStop->format('d/m/Y');
        } else {
            $now = Carbon::now();
            if ($now->hour < 8) {
                $now->setTime(8, 0, 0);
            } elseif ($now->hour >= 15) {
                $now->addDay()->setTime(8, 0, 0);
            }
            $default_start_date = $now->format('d/m/Y');
            $default_stop_date = $now->copy()->addHour()->format('d/m/Y');
        }

        return view('masterweb::module.mobile.testing.input-tanggal-validasi', compact(
            'sample',
            'laboratorium',
            'existing_validation',
            'lab_id',
            'list_name_petugas',
            'default_validator',
            'default_start_date',
            'default_stop_date'
        ));
    }

    /**
     * Store Tanggal Validasi data
     */
    public function storeValidasi(Request $request, $id)
    {
        if (!$request->session()->get('mobile_testing_auth', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }

        $request->validate([
            'verification_step' => 'required',
            'start_date' => 'required',
            'stop_date' => 'required',
            'nama_petugas' => 'required',
            'lab_id' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $step = (int) $request->get('verification_step', 5);
            if ($step !== 5) {
                $step = 5;
            }

            $validationRecord = VerificationActivitySample::where('id_sample', $id)
                ->where('id_verification_activity', $step)
                ->first();

            $start_date = $this->parseMobileStageDate($request->get('start_date'))->format('Y-m-d H:i:s');
            $stop_date = $this->parseMobileStageDate($request->get('stop_date'))->format('Y-m-d H:i:s');

            if ($validationRecord) {
                $validationRecord->start_date = $start_date;
                $validationRecord->stop_date = $stop_date;
                $validationRecord->nama_petugas = $request->get('nama_petugas');
                $validationRecord->is_done = 1;
                $validationRecord->save();
            } else {
                $validationRecord = new VerificationActivitySample();
                $validationRecord->id = Uuid::uuid4()->toString();
                $validationRecord->id_sample = $id;
                $validationRecord->id_verification_activity = $step;
                $validationRecord->start_date = $start_date;
                $validationRecord->stop_date = $stop_date;
                $validationRecord->nama_petugas = $request->get('nama_petugas');
                $validationRecord->is_done = 1;
                $validationRecord->save();
            }

            DB::commit();

            $lab_id = $request->get('lab_id');

            $redirectUrl = route('mobile.testing.selesai', ['id' => $id, 'lab_id' => $lab_id]);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data validasi berhasil disimpan!',
                    'url_redirect' => $redirectUrl
                ], 200);
            }

            return redirect($redirectUrl)->with('success', 'Data validasi berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MobileTesting: Error storing tanggal validasi', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $message = 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 500);
            }

            return redirect()->back()->with('error', $message);
        }
    }

    /**
     * Show completion page after all steps done
     */
    public function selesai(Request $request, $id, $lab_id)
    {
        if (!$request->session()->get('mobile_testing_auth', false)) {
            return redirect()->route('mobile.testing.login', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $redirectUrl = $this->checkAndRedirectToCorrectStep($request, $id, 'selesai', $lab_id);
        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        $sample = Sample::where('id_samples', $id)
            ->with(['permohonanuji', 'permohonanuji.customer', 'samplemethod.method', 'samplemethod.laboratorium', 'sampletype'])
            ->first();

        if (!$sample) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data sample tidak ditemukan.'
            ]);
        }

        $lab = Laboratorium::find($lab_id);
        if (!$lab) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data laboratorium tidak ditemukan.'
            ]);
        }

        // sampletype_id di tb_baku_mutu mengacu ke id_sample_type (di tb_samples biasanya tersimpan di typesample_samples)
        $sampletype_id = $sample->id_sample_type ?? $sample->typesample_samples ?? ($sample->sampletype->id_sample_type ?? null);
        $jenis_makanan_id = $sample->jenis_makanan_id ?? null;

        // Khusus MBI + Makanan/Minuman/Lainnya: baku mutu mengikuti jenis_makanan_id (distinct by method)
        $sampleTypeName = $sample->name_sample_type ?? ($sample->sampletype->name_sample_type ?? null);
        $isMbiMakanan = ($lab && $lab->kode_laboratorium === 'MBI' && $sampleTypeName === 'Makanan/Minuman/Lainnya');
        $useJenisMakanan = ($isMbiMakanan && !empty($jenis_makanan_id));

        if ($useJenisMakanan) {
            $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $lab_id)
                ->where('tb_sample_method.sample_id', '=', $id)
                ->orderBy('ms_method.jenis_parameter_kimia')
                ->join('ms_method', function ($join) use ($sampletype_id, $jenis_makanan_id, $lab_id, $id) {
                    $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                        ->whereNull('tb_sample_method.deleted_at')
                        ->whereNull('ms_method.deleted_at')
                        ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $jenis_makanan_id) {
                            $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                                ->where('tb_baku_mutu.jenis_makanan_id', '=', $jenis_makanan_id)
                                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                                ->whereNull('tb_baku_mutu.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        })
                        ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                            $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                            $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                                ->whereNull('unit_baku_mutu.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('tb_sample_result', function ($join) use ($id, $lab_id) {
                            $join->where('tb_sample_result.laboratorium_id', '=', $lab_id)
                                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                                ->where('tb_sample_result.sample_id', '=', $id)
                                ->whereNull('tb_sample_result.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        });
                })
                ->select(
                    'tb_baku_mutu.*',
                    'ms_method.*',
                    'tb_sample_method.*',
                    'unit_baku_mutu.*',
                    'tb_sample_result.hasil',
                    'tb_sample_result.metode',
                    'tb_sample_result.keterangan',
                    'tb_sample_result.offset_baku_mutu'
                )
                ->distinct('ms_method.id_method')
                ->get();
        } else {
            $laboratoriummethods = SampleMethod::where('tb_sample_method.laboratorium_id', '=', $lab_id)
                ->where('tb_sample_method.sample_id', '=', $id)
                ->orderBy('ms_method.jenis_parameter_kimia')
                ->join('ms_method', function ($join) use ($sampletype_id, $lab_id, $id) {
                    $join->on('ms_method.id_method', '=', 'tb_sample_method.method_id')
                        ->whereNull('tb_sample_method.deleted_at')
                        ->whereNull('ms_method.deleted_at')
                        ->leftjoin('tb_baku_mutu', function ($join) use ($sampletype_id, $lab_id) {
                            $join->on('tb_baku_mutu.method_id', '=', 'ms_method.id_method')
                                ->where('tb_baku_mutu.lab_id', '=', $lab_id)
                                ->where('tb_baku_mutu.sampletype_id', '=', $sampletype_id)
                                ->whereNull('tb_baku_mutu.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        })
                        ->leftjoin('tb_baku_mutu_detail_parameter_non_klinik', function ($join) {
                            $join->on('tb_baku_mutu_detail_parameter_non_klinik.baku_mutu_id', '=', 'tb_baku_mutu.id_baku_mutu')
                                ->whereNull('tb_baku_mutu_detail_parameter_non_klinik.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('ms_unit as unit_baku_mutu', function ($join) {
                            $join->on('unit_baku_mutu.id_unit', '=', 'tb_baku_mutu.unit_id')
                                ->whereNull('unit_baku_mutu.deleted_at')
                                ->whereNull('tb_baku_mutu.deleted_at');
                        })
                        ->leftjoin('tb_sample_result', function ($join) use ($id, $lab_id) {
                            $join->where('tb_sample_result.laboratorium_id', '=', $lab_id)
                                ->on('tb_sample_result.method_id', '=', 'ms_method.id_method')
                                ->where('tb_sample_result.sample_id', '=', $id)
                                ->whereNull('tb_sample_result.deleted_at')
                                ->whereNull('ms_method.deleted_at');
                        });
                })
                ->select(
                    'tb_baku_mutu.*',
                    'ms_method.*',
                    'tb_sample_method.*',
                    'unit_baku_mutu.*',
                    'tb_sample_result.hasil',
                    'tb_sample_result.metode',
                    'tb_sample_result.keterangan',
                    'tb_sample_result.offset_baku_mutu'
                )
                ->distinct('ms_method.id_method')
                ->get();
        }

        // Pastikan benar-benar unik per method (join detail/non-klinik bisa menggandakan row walaupun sudah DISTINCT)
        $laboratoriummethods = collect($laboratoriummethods)
            ->unique(function ($m) {
                return $m->id_method ?? $m->method_id ?? null;
            })
            ->values();


        foreach ($laboratoriummethods as $key => $laboratoriummethod) {
            $method_id = $laboratoriummethod->id_method ?? $laboratoriummethod->method_id;
            $laboratoriummethods[$key]->detail = SampleResultDetail::where('method_id', '=', $method_id)
                ->where('sampletype_id', '=', $sampletype_id)
                ->where('sample_id', '=', $id)
                ->get();

            foreach ($laboratoriummethods[$key]->detail as $detail_key => $detail) {
                $sample_result_detail = SampleResultDetail::where('id_sample_result_detail', $detail->id_sample_result_detail)
                    ->where('sample_id', $id)
                    ->first();

                if ($sample_result_detail) {
                    $laboratoriummethods[$key]->detail[$detail_key]->hasil = $sample_result_detail->hasil ?? null;
                    $laboratoriummethods[$key]->detail[$detail_key]->offset_baku_mutu = $sample_result_detail->offset_baku_mutu ?? 'default';
                }
            }

            if (!isset($laboratoriummethods[$key]->method_id)) {
                $laboratoriummethods[$key]->method_id = $method_id;
            }
        }

        //pengurutan order ist
        $sample_type_details = SampleTypeDetail::where('sample_type_id', $sampletype_id)->orderBy('orderlist_sample_type_detail')->get();



        $method_all_temp = [];


        foreach ($sample_type_details as $sample_type_detail) {
            # code...
            foreach ($laboratoriummethods as $method) {
                # code...


                // print("& ".$method->id_method." ".$sample_type_detail->method_id);
                if ($method->id_method == $sample_type_detail->method_id) {
                $method_all_temp[] = $method;
                }
            }
        }
        if ($method_all_temp != [] and count($laboratoriummethods) == count($method_all_temp)) {
        # code...
        $laboratoriummethods = $method_all_temp;
        }




        $validationRecord = VerificationActivitySample::where('id_sample', $id)
            ->where('id_verification_activity', 5)
            ->first();

        $web_link = route('elits-samples.verification-2', [$id, $lab_id]);

        return view('masterweb::module.mobile.testing.selesai', compact(
            'sample',
            'lab',
            'lab_id',
            'laboratoriummethods',
            'validationRecord',
            'web_link'
        ));
    }

    /**
     * Show Input Tanggal Verifikasi form
     */
    public function inputTanggalVerifikasi(Request $request, $id, $lab_id)
    {

        // Check authentication
        if (!$request->session()->get('mobile_testing_auth', false)) {
            return redirect()->route('mobile.testing.login', ['id' => $id])
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Check and redirect to correct step if user is at wrong position
        $redirectUrl = $this->checkAndRedirectToCorrectStep($request, $id, 'inputTanggalVerifikasi', $lab_id);
        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        // Get sample
        $sample = Sample::where('id_samples', $id)
            ->with(['permohonanuji', 'samplemethod.laboratorium', 'sampletype'])
            ->first();

        if (!$sample) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data sample tidak ditemukan.'
            ]);
        }

        // Get laboratorium
        $laboratorium = Laboratorium::find($lab_id);
        if (!$laboratorium) {
            return view('masterweb::module.mobile.testing.error', [
                'message' => 'Data laboratorium tidak ditemukan.'
            ]);
        }

        // Get existing verification activity for step 4 (Verifikasi)
        $existing_verification = VerificationActivitySample::where('id_sample', $id)
            ->where('id_verification_activity', 4)
            ->first();

        // Get verificationActivity for step 4 (Verifikasi) - same logic as web version
        // NOTE: gunakan ms_verification_activities.id = 4 (name: Verifikasi)
        $verificationActivity = VerificationActivity::query()->where('id', 4)->first();

        // Get list_name_petugas for step 4 (Verifikasi) based on lab.
        // Data dipisahkan dengan koma, bisa ada/tidak ada spasi.
        $petugas_raw = '';
        if ($verificationActivity) {
            if ($laboratorium->kode_laboratorium == 'MBI') {
                $petugas_raw = (string) ($verificationActivity->mikro ?? '');
            } elseif ($laboratorium->kode_laboratorium == 'KIM') {
                $petugas_raw = (string) ($verificationActivity->kimia ?? '');
            } else {
                $petugas_raw = (string) ($verificationActivity->klinik ?? '');
            }
        }

        // Split by comma, tolerate spaces
        $list_name_petugas = preg_split('/\s*,\s*/', trim($petugas_raw)) ?: [];
        $list_name_petugas = array_values(array_filter(array_map('trim', $list_name_petugas), function ($v) {
            return $v !== '';
        }));

        // Get default koordinator kesmas from PenerimaanSample (same logic as web version)
        // NOTE: beberapa data lama bisa tidak konsisten per-lab, jadi pakai fallback by sample_id saja.
        $penerimaan_sample = PenerimaanSample::where('laboratorium_id', $lab_id)
            ->where('sample_id', $id)
            ->first();
        if (!$penerimaan_sample) {
            $penerimaan_sample = PenerimaanSample::where('sample_id', $id)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        $default_koordinator_kesmas = null;
        if ($penerimaan_sample) {
            $default_koordinator_kesmas = $penerimaan_sample->disposisi_koordinator_kesmas;
        }

        // Samakan cara matching default dengan opsi dropdown (robust terhadap spasi/aksen/koma/titik/gelar)
        // Karena data disposisi di mobile bisa dinormalisasi saat disimpan (Step 2),
        // sementara daftar petugas berasal dari master VerificationActivity.
        $normalizeName = function ($name) {
            $name = (string) $name;
            // Normalize NBSP
            $name = str_replace("\xc2\xa0", ' ', $name);
            // Remove accents/diacritics safely
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
            if ($converted !== false) {
                $name = $converted;
            }
            $name = trim($name);
            $name = preg_replace('/\s+/', ' ', $name);
            $name = mb_strtolower($name, 'UTF-8');
            // Remove punctuation so "S.Si" vs "S Si" still matches
            $name = preg_replace('/[^a-z0-9 ]+/i', '', $name);
            $name = trim(preg_replace('/\s+/', ' ', $name));
            return $name;
        };

        $matched = false;
        if (!empty($default_koordinator_kesmas) && !empty($list_name_petugas)) {
            $defaultNorm = $normalizeName($default_koordinator_kesmas);
            // 1) exact normalized match
            foreach ($list_name_petugas as $opt) {
                if ($normalizeName($opt) === $defaultNorm) {
                    $default_koordinator_kesmas = $opt; // match persis ke opsi
                    $matched = true;
                    break;
                }
            }
            // 2) contains match (fallback) - mengatasi variasi gelar/format
            if (!$matched && $defaultNorm !== '') {
                foreach ($list_name_petugas as $opt) {
                    $optNorm = $normalizeName($opt);
                    if ($optNorm !== '' && (str_contains($optNorm, $defaultNorm) || str_contains($defaultNorm, $optNorm))) {
                        $default_koordinator_kesmas = $opt;
                        $matched = true;
                        break;
                    }
                }
            }
        }

        // Fallback: kalau tidak match (atau kosong) dan ada list, gunakan petugas pertama
        if ((!$matched || empty($default_koordinator_kesmas)) && !empty($list_name_petugas)) {
            $first = reset($list_name_petugas);
            if (!empty($first)) {
                $default_koordinator_kesmas = $first;
            }
        }

        // Get default dates (same logic as verification-2.blade.php - table-verification-kimia.blade.php)
        // Step 4 Verifikasi: Start from Input Stop (step 3), Stop = Start + 1 jam
        $input_hasil_verif = VerificationActivitySample::where('id_sample', $id)
            ->where('id_verification_activity', 3)
            ->first();

        $default_start_date = null;
        $default_stop_date = null;

        if ($existing_verification) {
            // Use existing data if available
            $default_start_date = Carbon::parse($existing_verification->start_date)->format('d/m/Y');
            $default_stop_date = Carbon::parse($existing_verification->stop_date)->format('d/m/Y');
        } elseif ($input_hasil_verif && $input_hasil_verif->stop_date) {
            // Default: Verifikasi Start = Input Stop (step 3)
            $verifikasiStart = Carbon::parse($input_hasil_verif->stop_date);
            
            // Adjust to working hours (8:00 AM to 3:00 PM) - same as web version
            if ($verifikasiStart->hour < 8) {
                $verifikasiStart->setTime(8, 0, 0);
            } elseif ($verifikasiStart->hour >= 15) {
                $verifikasiStart->addDay()->setTime(8, 0, 0);
            }

            // Verifikasi Stop = Verifikasi Start + 1 jam (same as web version)
            $verifikasiStop = $verifikasiStart->copy()->addHour();
            // Adjust stop time to working hours if needed
            if ($verifikasiStop->hour >= 15) {
                $verifikasiStop->addDay()->setTime(8, 0, 0);
            }

            $default_start_date = $verifikasiStart->format('d/m/Y');
            $default_stop_date = $verifikasiStop->format('d/m/Y');
        } else {
            // Fallback to current date/time
            $now = Carbon::now();
            if ($now->hour < 8) {
                $now->setTime(8, 0, 0);
            } elseif ($now->hour >= 15) {
                $now->addDay()->setTime(8, 0, 0);
            }
            $default_start_date = $now->format('d/m/Y');
            $default_stop_date = $now->copy()->addHour()->format('d/m/Y');
        }

        return view('masterweb::module.mobile.testing.input-tanggal-verifikasi', compact(
            'sample',
            'laboratorium',
            'existing_verification',
            'lab_id',
            'list_name_petugas',
            'default_koordinator_kesmas',
            'default_start_date',
            'default_stop_date'
        ));
    }

    /**
     * Store Tanggal Verifikasi data
     */
    public function storeTanggalVerifikasi(Request $request, $id)
    {
        // Check authentication
        if (!$request->session()->get('mobile_testing_auth', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }

        $request->validate([
            'verification_step' => 'required',
            'start_date' => 'required',
            'stop_date' => 'required',
            'nama_petugas' => 'required',
            'lab_id' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // Updated if exist (same logic as verificationAnalytic)
            $verificationActivitySampleUpdated = VerificationActivitySample::where('id_sample', $id)
                ->where('id_verification_activity', $request->get('verification_step'))
                ->first();

            if (isset($verificationActivitySampleUpdated)) {
                $start_date = $this->parseMobileStageDate($request->get('start_date'))->format('Y-m-d H:i:s');
                $stop_date = $this->parseMobileStageDate($request->get('stop_date'))->format('Y-m-d H:i:s');
                $verificationActivitySampleUpdated->start_date = $start_date;
                $verificationActivitySampleUpdated->stop_date = $stop_date;
                $verificationActivitySampleUpdated->nama_petugas = $request->get('nama_petugas');
                $verificationActivitySampleUpdated->is_done = 1;
                $verificationActivitySampleUpdated->save();
            } else {
                // Create new
                $verificationActivitySample = new VerificationActivitySample();
                $verificationActivitySample->id = Uuid::uuid4()->toString();
                $verificationActivitySample->id_sample = $id;
                $verificationActivitySample->id_verification_activity = $request->get('verification_step');

                $start_date = $this->parseMobileStageDate($request->get('start_date'))->format('Y-m-d H:i:s');
                $stop_date = $this->parseMobileStageDate($request->get('stop_date'))->format('Y-m-d H:i:s');

                $verificationActivitySample->is_done = 1;
                $verificationActivitySample->start_date = $start_date;
                $verificationActivitySample->stop_date = $stop_date;
                $verificationActivitySample->nama_petugas = $request->get('nama_petugas');
                $verificationActivitySample->save();
            }

            // Update VerifikasiHasil with verifikasi_hasil_date (same as web version)
            $lab_id = $request->input('lab_id');
            $verifikasi_hasil = VerifikasiHasil::where('sample_id', $id)
                ->where('laboratorium_id', $lab_id)
                ->first();

            if ($verifikasi_hasil) {
                // Update existing - use stop_date as verifikasi_hasil_date
                $verifikasi_hasil->verifikasi_hasil_date = $this->parseMobileStageDate($request->get('stop_date'))->format('Y-m-d H:i:s');
                $verifikasi_hasil->save();
            } else {
                // Create new VerifikasiHasil
                $verifikasi_hasil = new VerifikasiHasil();
                $uuid4 = Uuid::uuid4();
                $verifikasi_hasil->id_verifikasi_hasil = $uuid4->toString();
                $verifikasi_hasil->sample_id = $id;
                $verifikasi_hasil->laboratorium_id = $lab_id;
                $verifikasi_hasil->verifikasi_hasil_date = $this->parseMobileStageDate($request->get('stop_date'))->format('Y-m-d H:i:s');
                $verifikasi_hasil->save();
            }

            DB::commit();

            $redirectUrl = route('mobile.testing.pengesahanHasil', ['id' => $id, 'lab_id' => $lab_id]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data tanggal verifikasi berhasil disimpan!',
                    'url_redirect' => $redirectUrl
                ], 200);
            }

            return redirect($redirectUrl)->with('success', 'Data tanggal verifikasi berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MobileTesting: Error storing tanggal verifikasi', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }
}