<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeaders Middleware
 * 
 * Middleware ini mengatur security headers untuk semua request.
 * 
 * PENTING: Untuk route mobile (mobile/testing, mobile/dokter) dan route PDF preview,
 * middleware ini MENGIZINKAN iframe dengan mengatur:
 * - X-Frame-Options: SAMEORIGIN (bukan DENY)
 * - Content-Security-Policy: frame-ancestors 'self' (bukan 'none')
 * 
 * Ini berbeda dengan server eksternal yang mengirim DENY atau 'none',
 * yang akan memblokir iframe secara total.
 */
class SecurityHeaders
{
	/**
	 * Handle an incoming request.
	 */
	public function handle(Request $request, Closure $next): Response
	{


		/** @var \Symfony\Component\HttpFoundation\Response $response */
		$response = $next($request);

		// --- Early bypass for mobile testing + print routes (but still allow iframe) ---
		// Route lokal ini DIIZINKAN untuk iframe (SAMEORIGIN), berbeda dengan server eksternal
		// yang mungkin mengirim DENY atau frame-ancestors 'none'
		$routeUri = $request->path();
		$requestUrl = $request->fullUrl();
		$routeName = $request->route() ? $request->route()->getName() : null;
		
		// Deteksi route yang perlu mengizinkan iframe
		$isMobileTesting = strpos($routeUri, 'mobile/testing') !== false;
		$isMobileDokter = strpos($routeUri, 'mobile/dokter/validasi') !== false || ($routeName && strpos($routeName, 'mobile.dokter.validasi') !== false);
		$isPrintMikro = strpos($routeUri, 'elits-release/print-mikro') !== false || strpos($requestUrl, 'elits-release/print-mikro') !== false;
		$isPrintLHU = strpos($routeUri, 'elits-release/printLHU') !== false || strpos($requestUrl, 'elits-release/printLHU') !== false;
		$isPrintKimia = strpos($routeUri, 'elits-release/print-kimia') !== false || strpos($requestUrl, 'elits-release/print-kimia') !== false;
		$isPrintVerifikasi = strpos($routeUri, 'elits-release/print_verifikasi') !== false || strpos($requestUrl, 'elits-release/print_verifikasi') !== false;
		$isPreviewPdfKlinik = strpos($routeUri, 'preview-pdf-hasil-klinik-2') !== false || strpos($requestUrl, 'preview-pdf-hasil-klinik-2') !== false;
		$isPrintKlinikHasilPreview = strpos($routeUri, 'print-permohonan-uji-klinik-hasil-2') !== false
			&& $request->query('mode') === 'preview';
		
		// Cek juga berdasarkan route name untuk PDF preview
		$isPreviewPdfKlinikRoute = ($routeName && strpos($routeName, 'preview-pdf-hasil') !== false);
		
		if ($isMobileTesting || $isMobileDokter || $isPrintMikro || $isPrintLHU || $isPrintKimia || $isPreviewPdfKlinik || $isPrintVerifikasi || $isPreviewPdfKlinikRoute || $isPrintKlinikHasilPreview) {
			// Hapus semua header yang memblokir iframe (hapus semua instance)
			$response->headers->remove('X-Frame-Options', true);
			$response->headers->remove('Content-Security-Policy', true);
			$response->headers->remove('Content-Security-Policy-Report-Only', true);
			
			// Set X-Frame-Options ke SAMEORIGIN (replace semua instance sebelumnya)
			$response->headers->set('X-Frame-Options', 'SAMEORIGIN', true);
			
			// Set CSP yang mengizinkan iframe (frame-ancestors 'self')
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
			$response->headers->set('Content-Security-Policy-Report-Only', $cspReportOnly, true);

			// Tetap set header keamanan dasar lainnya
			$response->headers->set('X-Content-Type-Options', 'nosniff', true);
			$response->headers->set('Referrer-Policy', $this->resolveReferrerPolicy($request), true);
			$response->headers->set('Permissions-Policy', "geolocation=(), microphone=(), camera=*, payment=(), usb=()", true);
			$response->headers->set('X-XSS-Protection', '0', true);
			$response->headers->set('Cross-Origin-Opener-Policy', 'same-origin', true);
			$response->headers->set('Cross-Origin-Resource-Policy', 'same-origin', true);
			
			$response->headers->set('X-Frame-Options', 'SAMEORIGIN', true);
			return $response;
		}

		// Routes yang perlu di-embed dalam iframe (mengizinkan SAMEORIGIN)
		$iframeAllowedRouteNames = [
			'elits-permohonan-uji-klinik-2.preview-pdf-hasil',
			'elits-permohonan-uji-klinik-2.disabled-permohonan-uji-analis2',
			'elits-release.print-mikro',
			'elits-release.print-mikro-gabungan',
			'elits-release.print_verifikasi',
			'elits-release.printLHU',
			'elits-release.print-kimia',
			'elits-release.print-kimia-2',
			'elits-release.print_verifikasi',
			'mobile.dokter.validasi',
			'mobile.dokter',
		];
		
		$iframeAllowedPaths = [
			'preview-pdf-hasil-klinik-2',
			'preview-pdf-hasil',
			'print-permohonan-uji-klinik-hasil-2',
			'disabled-permohonan-uji-analis2',
			'elits-release/print_verifikasi',
			'elits-release/print-mikro',
			'elits-release/print-mikro-air-bersih-air-minum',
			'elits-release/printLHU',
			'elits-release/print-kimia',
			'elits-release/print-kimia-2',
			'elits-release/print_verifikasi',
			'mobile/testing', // Route mobile testing untuk mengizinkan iframe PDF
			'mobile/dokter', // Route mobile dokter untuk mengizinkan iframe PDF
		];
		
		// Cek apakah route saat ini perlu di-embed dalam iframe
		$routeName = $request->route() ? $request->route()->getName() : null;
		$routeUri = $request->path();
		$isIframeAllowed = false;
		
		// Cek berdasarkan route name
		if ($routeName) {
			foreach ($iframeAllowedRouteNames as $allowedRoute) {
				if (strpos($routeName, $allowedRoute) !== false) {
					$isIframeAllowed = true;
					break;
				}
			}
		}
		
		// Cek berdasarkan URI path
		if (!$isIframeAllowed) {
			foreach ($iframeAllowedPaths as $allowedPath) {
				if (strpos($routeUri, $allowedPath) !== false) {
					// print-permohonan-uji-klinik-hasil-2 hanya untuk mode=preview (iframe popup)
					if (strpos($allowedPath, 'print-permohonan-uji-klinik-hasil-2') !== false
						&& $request->query('mode') !== 'preview') {
						continue;
					}
					$isIframeAllowed = true;
					break;
				}
			}
		}
		
		// Cek khusus untuk route mobile testing dan mobile dokter - perlu mengizinkan iframe di dalamnya
		$isMobileTesting = strpos($routeUri, 'mobile/testing') !== false;
		$isMobileDokter = strpos($routeUri, 'mobile/dokter') !== false;
		if ($isMobileTesting || $isMobileDokter) {
			$isIframeAllowed = true;
		}
		
		// Cek khusus untuk PDF preview - pastikan selalu di-allow
		if (!$isIframeAllowed && ($routeName && strpos($routeName, 'preview-pdf-hasil') !== false)) {
			$isIframeAllowed = true;
		}
		if (!$isIframeAllowed && (strpos($routeUri, 'preview-pdf-hasil') !== false)) {
			$isIframeAllowed = true;
		}


		if (!$isIframeAllowed && (strpos($routeUri, 'mobile.dokter.validasi') !== false)) {
			$isIframeAllowed = true;
		}
		
		// Cek juga berdasarkan full URL untuk route print yang mungkin diakses dari iframe
		// Ini penting karena route print-mikro mungkin diakses dengan query string panjang
		$requestUrl = $request->fullUrl();
		$requestPath = $request->path();
		if (!$isIframeAllowed) {
			// Cek path langsung
			if (strpos($requestPath, 'elits-release/print-mikro') !== false || strpos($requestPath, 'elits-release/print_verifikasi') !== false) {
				$isIframeAllowed = true;
			}
			// Cek full URL sebagai fallback
			elseif (strpos($requestUrl, 'elits-release/print-mikro') !== false || strpos($requestUrl, 'elits-release/print_verifikasi') !== false) {
				$isIframeAllowed = true;
			}
			// Cek juga untuk printLHU dan print-kimia
			elseif (strpos($requestPath, 'elits-release/printLHU') !== false || 
					strpos($requestPath, 'elits-release/print-kimia') !== false) {
				$isIframeAllowed = true;
			}
			// Cek untuk PDF preview klinik
			elseif (strpos($requestPath, 'preview-pdf-hasil-klinik-2') !== false || 
					strpos($requestUrl, 'preview-pdf-hasil-klinik-2') !== false ||
					strpos($requestPath, 'preview-pdf-hasil') !== false) {
				$isIframeAllowed = true;
			}
		}

		// Cek apakah ini route print (yang perlu di-embed dalam iframe)
		$isPrintRoute = strpos($requestPath, 'elits-release/print') !== false;
		
		// Add common hardening headers
		if ($isIframeAllowed) {
			// Untuk route yang perlu di-embed, set SAMEORIGIN
			// Hapus header yang mungkin memblokir iframe
			$response->headers->remove('X-Frame-Options');
			$response->headers->set('X-Frame-Options', 'SAMEORIGIN', true);
			
			// Hapus CSP yang mungkin memblokir
			$response->headers->remove('Content-Security-Policy');
			$response->headers->remove('Content-Security-Policy-Report-Only');
			
			// Untuk route mobile testing dan mobile dokter, izinkan iframe lebih luas
			if ($isMobileTesting || $isMobileDokter) {
				$cspReportOnly = "default-src 'self'; " .
					"script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
					"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
					"font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; " .
					"img-src 'self' data: blob: https:; " .
					"object-src 'self' blob:; " .
					"frame-src 'self' blob: data:; " .
					"connect-src 'self'; " .
					"frame-ancestors 'self';";
				$response->headers->set('Content-Security-Policy-Report-Only', $cspReportOnly);
				
				$csp = "default-src 'self'; " .
					"script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
					"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
					"font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; " .
					"img-src 'self' data: blob: https:; " .
					"object-src 'self' blob:; " .
					"frame-src 'self' blob: data:; " .
					"connect-src 'self'; " .
					"frame-ancestors 'self';";
				$response->headers->set('Content-Security-Policy', $csp);
			} 
			// Untuk route print (yang akan di-embed di iframe), pastikan frame-ancestors mengizinkan
			elseif ($isPrintRoute) {
				$cspReportOnly = "default-src 'self'; " .
					"script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
					"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
					"font-src 'self' https://fonts.gstatic.com data:; " .
					"img-src 'self' data: blob: https:; " .
					"object-src 'self' blob:; " .
					"frame-src 'self' blob:; " .
					"connect-src 'self'; " .
					"frame-ancestors 'self';";
				$response->headers->set('Content-Security-Policy-Report-Only', $cspReportOnly);
				
				// Set CSP yang sebenarnya untuk route print - izinkan di-embed dari same origin
				$csp = "default-src 'self'; " .
					"script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
					"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
					"font-src 'self' https://fonts.gstatic.com data:; " .
					"img-src 'self' data: blob: https:; " .
					"object-src 'self' blob:; " .
					"frame-src 'self' blob:; " .
					"connect-src 'self'; " .
					"frame-ancestors 'self';";
				$response->headers->set('Content-Security-Policy', $csp);
			} 
			// Untuk route lainnya yang diizinkan
			else {
				$cspReportOnly = "default-src 'self'; " .
					"script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
					"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
					"font-src 'self' https://fonts.gstatic.com data:; " .
					"img-src 'self' data: blob: https:; " .
					"object-src 'self' blob:; " .
					"frame-src 'self' blob:; " .
					"connect-src 'self'; " .
					"frame-ancestors 'self';";
				$response->headers->set('Content-Security-Policy-Report-Only', $cspReportOnly);
				
				$csp = "default-src 'self'; " .
					"script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
					"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
					"font-src 'self' https://fonts.gstatic.com data:; " .
					"img-src 'self' data: blob: https:; " .
					"object-src 'self' blob:; " .
					"frame-src 'self' blob:; " .
					"connect-src 'self'; " .
					"frame-ancestors 'self';";
				$response->headers->set('Content-Security-Policy', $csp);
			}
		} else {
			// Untuk route lainnya, set DENY
			$response->headers->set('X-Frame-Options', 'DENY');
			$cspReportOnly = "default-src 'self'; " .
				"script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
				"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
				"font-src 'self' https://fonts.gstatic.com data:; " .
				"img-src 'self' data: https:; " .
				"connect-src 'self'; " .
				"frame-ancestors 'none';";
			$response->headers->set('Content-Security-Policy-Report-Only', $cspReportOnly);
		}
		
		$response->headers->set('X-Content-Type-Options', 'nosniff'); // prevent MIME sniffing
		$response->headers->set('Referrer-Policy', $this->resolveReferrerPolicy($request)); // minimize leaked referrer data (kecuali halaman peta OSM)
		$response->headers->set('Permissions-Policy', "geolocation=(), microphone=(), camera=*, payment=(), usb=()"); // lock down powerful APIs, allow camera
		// Modern browsers ignore X-XSS-Protection; prefer CSP. Set to 0 to avoid legacy behavior.
		$response->headers->set('X-XSS-Protection', '0');

		// Ensure cookies marked Secure/SameSite/Lax are respected via headers in some clients
		// Untuk route print yang akan di-embed di iframe, set CORP ke cross-origin atau same-origin
		if ($isIframeAllowed && $isPrintRoute) {
			// Untuk route print yang di-embed, izinkan cross-origin resource sharing dalam same origin
			$response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
			$response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
		} else {
			$response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
			$response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
		}

		return $response;
	}

	/**
	 * Halaman peta Leaflet + tile OpenStreetMap membutuhkan Referer.
	 * no-referrer membuat OSM mengembalikan 403 "Referer is required".
	 */
	private function resolveReferrerPolicy(Request $request): string
	{
		$routeName = $request->route() ? $request->route()->getName() : null;
		$path = $request->path();

		$needsOsmReferrer = $routeName === 'klinik.analisis-hasil-wilayah'
			|| strpos($path, 'klinik/analisis-hasil-wilayah') !== false
			|| strpos($path, 'dokter/dashboard') !== false;

		if ($needsOsmReferrer) {
			return 'strict-origin-when-cross-origin';
		}

		return 'no-referrer';
	}
}