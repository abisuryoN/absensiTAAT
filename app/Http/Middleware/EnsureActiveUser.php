<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->is_active) {
            Auth::logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Administrator sekolah.',
            ]);
        }

        return $next($request);
    }
}
