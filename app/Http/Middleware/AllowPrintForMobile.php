<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowPrintForMobile
{
    /**
     * Handle an incoming request.
     * Middleware ini mengizinkan akses ke route print jika ada session mobile,
     * meskipun tidak ada session web (setelah logout web)
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah ini route print
        $routeUri = $request->path();
        $isPrintRoute = strpos($routeUri, 'elits-release/print') !== false;
        
        if ($isPrintRoute) {
            // Cek apakah ada session mobile (testing, sampling, atau sampling auth)
            $hasMobileTesting = $request->session()->has('mobile_testing_auth');
            $hasMobileSampling = $request->session()->has('mobile_sampling_auth');
            $hasSamplingAuth = $request->session()->has('sampling_auth');
            
            // Jika ada session mobile, izinkan akses meskipun tidak ada auth web
            if ($hasMobileTesting || $hasMobileSampling || $hasSamplingAuth) {
                // Set user untuk kebutuhan controller (jika diperlukan)
                // Tapi jangan set Auth::login karena itu akan membuat session web
                // Controller bisa menggunakan session mobile langsung
                return $next($request);
            }
        }
        
        // Untuk route lainnya, lanjutkan normal (akan dicek oleh middleware auth jika diperlukan)
        return $next($request);
    }
}

