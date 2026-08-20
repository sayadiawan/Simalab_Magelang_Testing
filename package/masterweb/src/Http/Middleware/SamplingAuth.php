<?php

namespace Smt\Masterweb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SamplingAuth
{
    /**
     * Handle an incoming request for sampling authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated OR has sampling session
        if (auth()->check()) {
            return $next($request);
        }
        
        // Check if has sampling session (simple auth for field workers)
        if (session()->has('sampling_auth')) {
            return $next($request);
        }

        // Redirect to sampling login page
        return redirect()->route('sampling.login')
            ->with('error', 'Silakan login terlebih dahulu untuk melakukan pengambilan sampel')
            ->with('intended_url', $request->fullUrl());
    }
}

