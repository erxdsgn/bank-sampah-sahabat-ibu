<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Proses autentikasi login admin.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            $admin = Auth::user();

            /*
         * Hapus session lama dari perangkat/browser yang sama,
         * TAPI jangan hapus session yang baru saja dibuat.
         */
            $currentSessionId = $request->session()->getId();

            DB::table('sessions')
                ->where('user_id', $admin->id_admin)
                ->where('user_agent', $request->userAgent())
                ->where('id', '!=', $currentSessionId) // ← KUNCI
                ->delete();

            $request->session()->regenerate();

            return redirect()
                ->intended(route('admin.dashboard'))
                ->with('success', 'Selamat datang kembali!');
        }

        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    /**
     * Proses logout admin.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda telah keluar dari sistem.');
    }
}
