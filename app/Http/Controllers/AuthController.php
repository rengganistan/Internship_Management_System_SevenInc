<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Show registration form
    public function showRegisterForm()
    {
        if (auth()->check()) {
            $role = auth()->user()->role;
            if ($role === 'admin') {
                return redirect()->route('admin.dashboard.index');
            }
            return redirect()->route('pemagang.dashboard');
        }
        return view('auth.admin-register');
    }

    // Handle registration
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        return redirect()->route('user.login')
            ->with('success', 'Registrasi berhasil! Silakan login untuk melanjutkan.');
    }

    /**
     * Tampilkan form login admin
     */
    public function showLoginForm()
    {
        if (auth()->check()) {
            $role = auth()->user()->role;
            if ($role === 'admin') {
                return redirect()->route('admin.dashboard.index');
            }
            return redirect()->route('pemagang.dashboard');
        }
        return view('auth.admin-login');
    }


    /**
     * Proses login admin
     */
    public function login(Request $request)
    {
        // Validasi kredensial login
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Proses login menggunakan kredensial
        if (auth()->attempt($credentials, $request->boolean('remember'))) {
            // Regenerasi session untuk keamanan
            $request->session()->regenerate();

            $user = auth()->user();

            // Admin → admin dashboard
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard.index');
            }

            // Semua role selain admin (user, pemagang) → pemagang dashboard baru
            return redirect()->route('pemagang.dashboard');
        }

        // Jika login gagal
        return back()->with('error', 'Email atau password salah!')->onlyInput('email');
    }





    /**
     * Logout admin
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidate session to prevent session hijacking
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect back to login page after logout
        return redirect()->route('user.login')->with('success', 'Berhasil logout.');
    }
}
