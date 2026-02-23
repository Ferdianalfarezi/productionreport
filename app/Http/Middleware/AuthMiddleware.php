<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->status === 'nonaktif') {
            Auth::logout();
            return redirect()->route('login')->withErrors(['username' => 'Akun Anda tidak aktif.']);
        }

        return $next($request);
    }
}
