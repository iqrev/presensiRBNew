<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckEmployeeStatus
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->status === 'nonaktif') {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'login' => 'Akun Anda telah dinonaktifkan. Hubungi HR.',
            ]);
        }

        return $next($request);
    }
}
