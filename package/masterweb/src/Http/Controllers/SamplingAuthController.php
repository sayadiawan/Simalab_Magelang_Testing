<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Smt\Masterweb\Models\User;

class SamplingAuthController extends Controller
{
    /**
     * Show sampling login page
     */
    public function showLogin(Request $request)
    {
        // If already authenticated, redirect to intended URL or home
        if (auth()->check() || session()->has('sampling_auth')) {
            $intendedUrl = session('intended_url', route('elits-permohonan-uji.index'));
            return redirect($intendedUrl);
        }

        return view('masterweb::module.admin.laboratorium.sampling.login');
    }

    /**
     * Process sampling login
     */
    public function login(Request $request)
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

        // Try to find user with role sampling/petugas
        $user = User::where('username', $request->username)
            ->whereIn('role', ['sampling', 'petugas', 'admin', 'user'])
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Set sampling session
            session([
                'sampling_auth' => true,
                'sampling_user_id' => $user->id,
                'sampling_user_name' => $user->name,
                'sampling_user_username' => $user->username,
            ]);

            // Get intended URL from session
            $intendedUrl = session('intended_url');
            
            // Clear intended URL from session
            session()->forget('intended_url');

            return redirect($intendedUrl ?? route('elits-permohonan-uji.index'))
                ->with('success', 'Login berhasil! Selamat datang, ' . $user->name);
        }

        return redirect()->back()
            ->withInput($request->only('username'))
            ->with('error', 'Username atau password salah!');
    }

    /**
     * Logout from sampling session
     */
    public function logout(Request $request)
    {
        // Clear sampling session
        session()->forget(['sampling_auth', 'sampling_user_id', 'sampling_user_name', 'sampling_user_username']);
        
        return redirect()->route('sampling.login')
            ->with('success', 'Anda telah logout');
    }
}

