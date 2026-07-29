<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureBiometricConsent
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && !$user->hasRole(['admin', 'superadmin']) && !$user->hasGivenBiometricConsent()) {
            return redirect()->route('consent.show');
        }

        return $next($request);
    }
}
