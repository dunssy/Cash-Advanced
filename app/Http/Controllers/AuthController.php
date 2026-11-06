<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $demoUsers = User::orderByRaw("
            CASE role 
                WHEN 'karyawan' THEN 1 
                WHEN 'atasan' THEN 2 
                WHEN 'finance' THEN 3 
                WHEN 'direktur' THEN 4 
                WHEN 'super_admin' THEN 5 
                ELSE 6 
            END
        ")->get();

        return view('auth.login', compact('demoUsers'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            AuditLog::record('LOGIN', $user, "User {$user->name} ({$user->role}) berhasil login.");

            return redirect()->intended(route('dashboard'))
                ->with('success', "Selamat datang kembali, {$user->name}!");
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function quickLogin(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($request->user_id);
        Auth::login($user);
        $request->session()->regenerate();

        AuditLog::record('QUICK_LOGIN', $user, "Quick switch login sebagai {$user->name} ({$user->role}).");

        return redirect()->route('dashboard')
            ->with('success', "Berhasil masuk sebagai {$user->name} ({$user->role}).");
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::record('LOGOUT', $user, "User {$user->name} telah logout.");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil logout.');
    }
}
