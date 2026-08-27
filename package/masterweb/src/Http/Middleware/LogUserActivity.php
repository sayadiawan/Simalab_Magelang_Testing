<?php

namespace Smt\Masterweb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Smt\Masterweb\Helpers\ActivityLogger;

class LogUserActivity
{
    /**
     * Catat aktivitas pengguna setelah response diproses agar tidak menambah latency.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!ActivityLogger::shouldLogRequest($request)) {
            return $response;
        }

        $action = ActivityLogger::detectAction($request);
        ActivityLogger::fromRequest($request, $action);

        return $response;
    }
}
