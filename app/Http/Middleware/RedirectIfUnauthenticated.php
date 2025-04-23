<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfUnauthenticated
{
    public function handle($request, Closure $next, $guard = null)
    {
        \Log::info('RedirectIfUnauthenticated middleware - Checking authentication', [
            'path' => $request->path(),
            'guard' => $guard,
            'authenticated' => Auth::guard($guard)->check()
        ]);

        if (Auth::guard($guard)->check()) {
            return $next($request);
        }

        \Log::info('RedirectIfUnauthenticated middleware - Redirecting to login', [
            'path' => $request->path()
        ]);

        return redirect()->route('login');
    }
} 