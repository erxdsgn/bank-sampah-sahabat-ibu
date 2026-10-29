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
            $user = Auth::user();

            // 1. Validasi Role: Pastikan akun ini adalah Admin (bukan Warga)
            if ($user->role !== 'admin' && !$user->admin) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'username' => 'Akun ini tidak memiliki hak akses sebagai Admin.',
                ])->onlyInput('username');
            }

            // 2. Regenerasi ID session untuk mencegah Session Fixation
            $request->session()->regenerate();
            $currentSessionId = $request->session()->getId();

            // 3. Hapus session lama untuk user ini pada browser/device yang sama
            // Gunakan $user->id (ID dari tabel users), BUKAN id_admin
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('user_agent', $request->userAgent())
                ->where('id', '!=', $currentSessionId)
                ->delete();

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
