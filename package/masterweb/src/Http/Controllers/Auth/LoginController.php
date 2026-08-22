<?php

namespace Smt\Masterweb\Http\Controllers\Auth;

use App\Rules\Captcha;
use Illuminate\Http\Request;
use Smt\Masterweb\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Smt\Masterweb\Helpers\SatuSehatHelper;

class LoginController extends Controller
{
  /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

  use AuthenticatesUsers;

  /**
   * Where to redirect users after login.
   *
   * @var string
   */
  protected $redirectTo = 'home';

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('guest')
      ->except(['logout', 'index']);
  }

  public function rules($request)
  {
    $rule = [
      'username' => 'required|string|exists:ms_users,username',
      'password' => 'required|string'
    ];

    $pesan = [
      'username.required' => 'Username tidak boleh kosong!',
      'username.exists' => 'Username tidak ditemukan!',
      'password.required' => 'Password tidak boleh kosong!'
    ];

    return Validator::make($request, $rule, $pesan);
  }

  protected function login(Request $request)
  {
    // Honeypot simple bot check
    if ($request->filled('hp_field')) {
      return redirect()->back()->with('error', 'Permintaan tidak valid.')->withInput($request->only('username'));
    }
    // CAPTCHA validation (code valid for 2 minutes)
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
    $input = $request->all();
    $validator = $this->rules($input);

    if ($validator->fails()) {
      // return redirect()->back()->with('errors', $validator->errors());

      return redirect()
        ->back()
        ->withErrors($validator->errors())
        ->withInput();
    } else {
      // ERROR Undefined index: password
      $auth = User::where('username', '=', $request->username)->first();

      if ($auth) {
        if (Hash::check($request->password, $auth->password)) {
          if ($auth->publish == '1') {
            Auth::login($auth);
            try {
              SatuSehatHelper::ensureAccessToken();
            } catch (\Throwable $e) {
              Log::warning('Satu Sehat token refresh skipped on login: ' . $e->getMessage());
            }

            return redirect()->route($this->redirectTo);
          } else {
            // return redirect()->back()->with('errors', "Akun Anda tidak aktif silahkan hubungi admin kami!");
            return redirect()
              ->back()
              ->withErrors(['username' => "Akun Anda tidak aktif silahkan hubungi admin kami!"])
              ->withInput();
          }
        } else {
          // return redirect()->back()->with('errors', "Password Anda salah silahkan coba lagi!");

          return redirect()
            ->back()
            ->withErrors(['password' => "Password Anda salah silahkan coba lagi!"])
            ->withInput();
        }
      } else {
        // return redirect()->back()->with('errors', "Username Anda salah silahkan coba lagi!");

        return redirect()
          ->back()
          ->withErrors(['username' => "Username Anda salah silahkan coba lagi!"])
          ->withInput();
      }
    }
  }

  public function index()
  {
    return view('masterweb::landing-page-2');
  }

  public function showLoginForm()
  {
    return view('masterweb::auth.login-2');
  }

  public function username()
  {
    return 'username';
  }

  /**
   * Override logout method untuk hanya menghapus session web,
   * tanpa menghapus session mobile (mobile_testing_auth, mobile_sampling_auth, sampling_auth)
   */
  public function logout(Request $request)
  {
    // Simpan session mobile sebelum logout web
    $mobileTestingAuth = $request->session()->get('mobile_testing_auth');
    $mobileTestingUserId = $request->session()->get('mobile_testing_user_id');
    $mobileTestingUserName = $request->session()->get('mobile_testing_user_name');
    $mobileTestingIdSample = $request->session()->get('mobile_testing_id_sample');
    $mobileTestingIsAdmin = $request->session()->get('mobile_testing_is_admin');
    
    $mobileSamplingAuth = $request->session()->get('mobile_sampling_auth');
    $mobileSamplingUserId = $request->session()->get('mobile_sampling_user_id');
    $mobileSamplingUserName = $request->session()->get('mobile_sampling_user_name');
    
    $samplingAuth = $request->session()->get('sampling_auth');
    $samplingUserId = $request->session()->get('sampling_user_id');
    $samplingUserName = $request->session()->get('sampling_user_name');
    $samplingUserUsername = $request->session()->get('sampling_user_username');

    // Logout web (menghapus Auth session)
    Auth::logout();

    // Hapus hanya session web, jangan invalidate semua session
    // Regenerate CSRF token tanpa invalidate session
    $request->session()->regenerateToken();

    // Restore session mobile yang sudah disimpan
    if ($mobileTestingAuth !== null) {
      $request->session()->put('mobile_testing_auth', $mobileTestingAuth);
      if ($mobileTestingUserId !== null) {
        $request->session()->put('mobile_testing_user_id', $mobileTestingUserId);
      }
      if ($mobileTestingUserName !== null) {
        $request->session()->put('mobile_testing_user_name', $mobileTestingUserName);
      }
      if ($mobileTestingIdSample !== null) {
        $request->session()->put('mobile_testing_id_sample', $mobileTestingIdSample);
      }
      if ($mobileTestingIsAdmin !== null) {
        $request->session()->put('mobile_testing_is_admin', $mobileTestingIsAdmin);
      }
    }

    if ($mobileSamplingAuth !== null) {
      $request->session()->put('mobile_sampling_auth', $mobileSamplingAuth);
      if ($mobileSamplingUserId !== null) {
        $request->session()->put('mobile_sampling_user_id', $mobileSamplingUserId);
      }
      if ($mobileSamplingUserName !== null) {
        $request->session()->put('mobile_sampling_user_name', $mobileSamplingUserName);
      }
    }

    if ($samplingAuth !== null) {
      $request->session()->put('sampling_auth', $samplingAuth);
      if ($samplingUserId !== null) {
        $request->session()->put('sampling_user_id', $samplingUserId);
      }
      if ($samplingUserName !== null) {
        $request->session()->put('sampling_user_name', $samplingUserName);
      }
      if ($samplingUserUsername !== null) {
        $request->session()->put('sampling_user_username', $samplingUserUsername);
      }
    }

    return redirect()->route('login-form')
      ->with('success', 'Anda telah logout dari web. Session mobile tetap aktif.');
  }
}