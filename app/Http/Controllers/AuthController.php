<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        User::create([
            'username' => $validated['username'],
            'name' => $validated['name'],
            'nim_nidn' => $validated['nim_nidn'],
            'password' => $validated['password'], // otomatis di-hash via cast 'hashed'
            'role' => 'user', // hardcode, TIDAK boleh dari input
        ]);

        return redirect()->route('login')->with('status', 'Akun berhasil dibuat, silakan login.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        $request->session()->forget('url.intended');

        return $request->user()->isAdmin()
            ? redirect()->intended(route('admin.dashboard'))
            : redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
