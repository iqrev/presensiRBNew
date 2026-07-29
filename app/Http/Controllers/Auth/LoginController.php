<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        // Rate limiting: maks 5 percobaan per menit per IP
        $this->ensureIsNotRateLimited($request);

        $login    = $request->input('login');
        $password = $request->input('password');

        // Coba login dengan email atau username
        $credentials = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? ['email' => $login, 'password' => $password]
            : ['username' => $login, 'password' => $password];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'login' => 'Email/Username atau password salah.',
            ]);
        }

        // Cek status akun
        if (Auth::user()->status === 'nonaktif') {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => 'Akun Anda telah dinonaktifkan. Hubungi HR.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();

        return redirect()->intended($this->redirectBasedOnRole(Auth::user(), returnUrl: true));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda berhasil logout.');
    }

    public function showConsentForm()
    {
        return view('auth.consent');
    }

    public function storeConsent(Request $request)
    {
        $request->validate([
            'agree' => 'required|accepted',
        ]);

        Auth::user()->update(['biometric_consent_at' => now()]);
        return redirect()->route('employee.dashboard')->with('success', 'Terima kasih atas persetujuan Anda.');
    }

    private function redirectBasedOnRole(User $user, bool $returnUrl = false): mixed
    {
        $url = match (true) {
            $user->hasRole('superadmin'), $user->hasRole('admin') => route('admin.dashboard'),
            default => route('employee.dashboard'),
        };

        return $returnUrl ? $url : redirect($url);
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower($request->input('login')) . '|' . $request->ip();
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'login' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
        ]);
    }
}
