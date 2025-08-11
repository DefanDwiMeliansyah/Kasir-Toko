<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validasi dasar tanpa rule 'exists'
        $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // Ambil user berdasarkan username
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            // Username tidak ditemukan
            return back()->withErrors([
                'username' => 'Username tidak ditemukan.',
            ])->onlyInput('username');
        }

        // Coba login
        if (!Auth::attempt([
            'username' => $request->username,
            'password' => $request->password
        ], $request->boolean('remember'))) {
            // Password salah
            return back()->withErrors([
                'password' => 'Password yang Anda masukkan salah.',
            ])->onlyInput('username');
        }

        // Sukses login
        $request->session()->regenerate();
        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
