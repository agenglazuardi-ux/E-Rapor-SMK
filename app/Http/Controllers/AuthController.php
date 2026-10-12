<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $key = Str::lower($data['username']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $menit = ceil(RateLimiter::availableIn($key) / 60);
            throw ValidationException::withMessages(['username' => "Terlalu banyak percobaan login. Coba lagi dalam {$menit} menit."]);
        }

        if (! Auth::attempt(['username' => $data['username'], 'password' => $data['password']], $request->boolean('remember'))) {
            RateLimiter::hit($key, 900);
            throw ValidationException::withMessages(['username' => 'Username atau password salah.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
