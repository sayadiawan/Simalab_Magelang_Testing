<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * HTTP Basic Auth untuk API konsumsi order oleh komputer alat TMS.
 */
class TmsInstrumentAuth
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = (string) config('tms.api_username', env('TMS_API_USERNAME', 'elits'));
        $pass = (string) config('tms.api_password', env('TMS_API_PASSWORD', 'labkeskabmagelang'));

        $givenUser = (string) $request->getUser();
        $givenPass = (string) $request->getPassword();

        if ($givenUser === '' && $givenPass === '') {
            // Fallback: Authorization header manual / query (beberapa client alat)
            $authHeader = (string) $request->header('Authorization', '');
            if (stripos($authHeader, 'Basic ') === 0) {
                $decoded = base64_decode(substr($authHeader, 6), true);
                if ($decoded !== false && strpos($decoded, ':') !== false) {
                    list($givenUser, $givenPass) = explode(':', $decoded, 2);
                }
            } else {
                $givenUser = (string) $request->input('username', '');
                $givenPass = (string) $request->input('password', '');
            }
        }

        if (!hash_equals($user, (string) $givenUser) || !hash_equals($pass, (string) $givenPass)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Gunakan username/password TMS API.',
            ], 401, [
                'WWW-Authenticate' => 'Basic realm="TMS API"',
            ]);
        }

        return $next($request);
    }
}
