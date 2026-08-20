<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AllowIframeForMobile Middleware
 * 
 * Middleware ini memastikan route mobile dan PDF preview MENGIZINKAN iframe.
 * 
 * PENTING: Middleware ini dijalankan di akhir (setelah SecurityHeaders) untuk memastikan
 * header benar-benar di-override dan tidak ada header DENY yang tersisa.
 * 
 * Route yang diizinkan untuk iframe:
 * - mobile/testing/*
 * - mobile/dokter/*
 * - preview-pdf-hasil-klinik-2/*
 * - print-permohonan-uji-klinik-hasil-2?mode=preview
 * - elits-release/print-*
 * 
 * Header yang di-set:
 * - X-Frame-Options: SAMEORIGIN (mengizinkan iframe dari same origin)
 * - Content-Security-Policy: frame-ancestors 'self' (mengizinkan iframe dari same origin)
 * 
 * Ini berbeda dengan server eksternal yang mungkin mengirim:
 * - X-Frame-Options: DENY (memblokir semua iframe)
 * - Content-Security-Policy: frame-ancestors 'none' (memblokir semua iframe)
 */
class AllowIframeForMobile
{
    /**
     * Handle an incoming request.
     * Middleware ini dijalankan di akhir untuk memastikan header benar-benar di-override
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $routeUri = $request->path();
        $requestUrl = $request->fullUrl();
        
        // Deteksi route yang perlu mengizinkan iframe
        $isMobileTesting = strpos($routeUri, 'mobile/testing') !== false;
        $isMobileDokter = strpos($routeUri, 'mobile/dokter') !== false;
        $isPrintMikro = strpos($routeUri, 'elits-release/print-mikro') !== false || strpos($requestUrl, 'elits-release/print-mikro') !== false;
        $isPrintLHU = strpos($routeUri, 'elits-release/printLHU') !== false || strpos($requestUrl, 'elits-release/printLHU') !== false;
        $isPrintKimia = strpos($routeUri, 'elits-release/print-kimia') !== false || strpos($requestUrl, 'elits-release/print-kimia') !== false;
        $isPrintVerifikasi = strpos($routeUri, 'elits-release/print_verifikasi') !== false || strpos($requestUrl, 'elits-release/print_verifikasi') !== false;
        $isPreviewPdfKlinik = strpos($routeUri, 'preview-pdf-hasil-klinik-2') !== false || strpos($requestUrl, 'preview-pdf-hasil-klinik-2') !== false;
        $isPrintKlinikHasilPreview = strpos($routeUri, 'print-permohonan-uji-klinik-hasil-2') !== false
            && $request->query('mode') === 'preview';
        $isPrintVerifikasi = strpos($routeUri, 'elits-release/print_verifikasi') !== false || strpos($requestUrl, 'elits-release/print_verifikasi') !== false;
        
        if ($isMobileTesting || $isMobileDokter || $isPrintMikro || $isPrintLHU || $isPrintKimia || $isPreviewPdfKlinik || $isPrintVerifikasi || $isPrintKlinikHasilPreview) {
            // Hapus SEMUA header yang memblokir iframe (dengan first=false untuk hapus semua instance)
            $response->headers->remove('X-Frame-Options', false);
            $response->headers->remove('Content-Security-Policy', false);
            $response->headers->remove('Content-Security-Policy-Report-Only', false);
            
            // Set X-Frame-Options ke SAMEORIGIN (replace=true untuk override semua)
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN', true);
            
            // Set CSP yang mengizinkan iframe
            $csp = "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com; " .
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
                "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; " .
                "img-src 'self' data: blob: https:; " .
                "object-src 'self' blob:; " .
                "frame-src 'self' blob: data:; " .
                "connect-src 'self'; " .
                "frame-ancestors 'self';";
            $response->headers->set('Content-Security-Policy', $csp, true);
            
            // Set CSP Report-Only dengan frame-ancestors 'self' juga
            $cspReportOnly = "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com; " .
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
                "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; " .
                "img-src 'self' data: blob: https:; " .
                "object-src 'self' blob:; " .
                "frame-src 'self' blob: data:; " .
                "connect-src 'self'; " .
                "frame-ancestors 'self';";
            $response->headers->set('Content-Security-Policy-Report-Only', $cspReportOnly, true);
        }

        return $response;
    }
    
    /**
     * Terminable middleware - dijalankan setelah response dikirim
     * Ini memastikan header benar-benar di-override bahkan setelah semua middleware selesai
     */
    public function terminate($request, $response)
    {
        $routeUri = $request->path();
        $requestUrl = $request->fullUrl();
        
        $isMobileTesting = strpos($routeUri, 'mobile/testing') !== false;
        $isMobileDokter = strpos($routeUri, 'mobile/dokter') !== false;
        $isPrintMikro = strpos($routeUri, 'elits-release/print-mikro') !== false || strpos($requestUrl, 'elits-release/print-mikro') !== false;
        $isPrintLHU = strpos($routeUri, 'elits-release/printLHU') !== false || strpos($requestUrl, 'elits-release/printLHU') !== false;
        $isPrintKimia = strpos($routeUri, 'elits-release/print-kimia') !== false || strpos($requestUrl, 'elits-release/print-kimia') !== false;
        $isPrintVerifikasi = strpos($routeUri, 'elits-release/print_verifikasi') !== false || strpos($requestUrl, 'elits-release/print_verifikasi') !== false;
        $isPreviewPdfKlinik = strpos($routeUri, 'preview-pdf-hasil-klinik-2') !== false || strpos($requestUrl, 'preview-pdf-hasil-klinik-2') !== false;
        $isPrintKlinikHasilPreview = strpos($routeUri, 'print-permohonan-uji-klinik-hasil-2') !== false
            && $request->query('mode') === 'preview';
        $isPrintVerifikasi = strpos($routeUri, 'elits-release/print_verifikasi') !== false || strpos($requestUrl, 'elits-release/print_verifikasi') !== false;
        
        if ($isMobileTesting || $isMobileDokter || $isPrintMikro || $isPrintLHU || $isPrintKimia || $isPreviewPdfKlinik || $isPrintVerifikasi || $isPrintKlinikHasilPreview) {
            // Set header menggunakan PHP native untuk memastikan override
            if (!headers_sent()) {
                header('X-Frame-Options: SAMEORIGIN', true);
                
                $csp = "default-src 'self'; " .
                    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com; " .
                    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
                    "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; " .
                    "img-src 'self' data: blob: https:; " .
                    "object-src 'self' blob:; " .
                    "frame-src 'self' blob: data:; " .
                    "connect-src 'self'; " .
                    "frame-ancestors 'self';";
                header('Content-Security-Policy: ' . $csp, true);
                
                // Set CSP Report-Only juga
                $cspReportOnly = "default-src 'self'; " .
                    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com; " .
                    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
                    "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; " .
                    "img-src 'self' data: blob: https:; " .
                    "object-src 'self' blob:; " .
                    "frame-src 'self' blob: data:; " .
                    "connect-src 'self'; " .
                    "frame-ancestors 'self';";
                header('Content-Security-Policy-Report-Only: ' . $cspReportOnly, true);
            }
        }
    }
}