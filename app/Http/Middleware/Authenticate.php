<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     * Override untuk mengizinkan akses ke route print jika ada session mobile
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Cek apakah ini route print dan ada session mobile
        $routeUri = $request->path();
        $isPrintRoute = strpos($routeUri, 'elits-release/print') !== false;
        $isPrintRoute2 = strpos($routeUri, 'preview-pdf-hasil-klinik-2') !== false;
     
        // Cek apakah ini route mobile sampling/testing/dokter
        $isMobileRoute = strpos($routeUri, '/mobile/sampling/') !== false ||
                         strpos($routeUri, '/mobile/testing/') !== false ||
                         strpos($routeUri, '/mobile/dokter/') !== false ||
                         strpos($routeUri, '/sampling/') !== false;
        
        // Untuk route mobile, SELALU izinkan (biarkan controller yang handle auth)
        // JANGAN panggil parent::handle karena itu akan redirect ke login dan menghapus session
        if ($isMobileRoute) {
            // Langsung izinkan tanpa cek auth web
            // Controller akan handle pengecekan session mobile sendiri
            return $next($request);
        }
        
        if ($isPrintRoute || $isPrintRoute2) {
            // Cek apakah ada session mobile (testing, sampling, atau sampling auth)
            $hasMobileTesting = $request->session()->has('mobile_testing_auth');
            $hasMobileSampling = $request->session()->has('mobile_sampling_auth');
            $hasMobileDokter = $request->session()->has('mobile_dokter_auth');
            $hasSamplingAuth = $request->session()->has('sampling_auth');
            
            // Jika ada session mobile, izinkan akses tanpa auth web
            if ($hasMobileTesting || $hasMobileSampling || $hasSamplingAuth || $hasMobileDokter) {
                return $next($request);
            }
        }
        
        // Untuk route lainnya, gunakan pengecekan auth normal
        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
