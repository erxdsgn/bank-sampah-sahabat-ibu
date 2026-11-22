<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    // 1. Kirim Kode OTP ke Email
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $otp = (string) rand(100000, 999999);

        // Hapus OTP lama milik email ini jika ada
        DB::table('password_otps')->where('email', $request->email)->delete();

        // Simpan OTP baru (berlaku 5 menit)
        DB::table('password_otps')->insert([
            'email' => $request->email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Kirim Email
        Mail::to($request->email)->send(new OtpMail($otp));

        return response()->json([
            'message' => 'Kode OTP telah dikirim ke email Anda.'
        ]);
    }

    // 2. Verifikasi Kode OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $record = DB::table('password_otps')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$record || now()->isAfter($record->expires_at)) {
            return response()->json(['message' => 'Kode OTP salah atau sudah kedaluwarsa.'], 422);
        }

        return response()->json(['message' => 'Kode OTP valid.']);
    }

    // 3. Simpan Password Baru
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_otps')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$record || now()->isAfter($record->expires_at)) {
            return response()->json(['message' => 'Kode OTP tidak valid.'], 422);
        }

        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password),
        ]);

        // Hapus OTP agar tidak bisa dipakai ulang
        DB::table('password_otps')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password berhasil diperbarui.']);
    }
}
