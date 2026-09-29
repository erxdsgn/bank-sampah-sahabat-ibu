<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WargaController extends Controller
{
    public function index()
    {
        $warga = Warga::orderBy('id_warga', 'desc')->get();
        return view('admin.pages.warga', compact('warga'));
    }

    public function create()
    {
        return view('admin.pages.form-warga');
    }

    public function store(Request $request)
    {
        if (Warga::count() >= 100) {
            return response()->json([
                'message' => 'Gagal menambah data. Batas maksimal 100 warga telah tercapai.'
            ], 422);
        }

        $validated = $request->validate([
            'nik' => 'required|string|digits:16|unique:warga,nik',
            'nama' => 'required|string|max:50|regex:/^[a-zA-Z\s\.\'\-]+$/',
            'no_hp' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
            'jumlah_anggota_keluarga' => 'required|integer|min:1|max:15',
            'alamat' => 'required|string',
            'nama_bank_ewallet' => 'required|string|min:9|max:50|regex:/^[a-zA-Z\s\.\-]+$/',
            'nomor_rekening' => 'required|string|max:30|regex:/^[0-9]+$/',
            'nama_pemilik_rekening' => 'required|string|max:50|regex:/^[a-zA-Z\s\.\'\-]+$/',
        ], [
            'nik.digits' => 'NIK harus berisi tepat 16 digit angka.',
            'nama.regex' => 'Nama lengkap hanya boleh berisi huruf, spasi, titik, atau tanda hubung.',
            'no_hp.regex' => 'Nomor HP hanya boleh berisi angka.',
            'no_hp.max' => 'Nomor HP maksimal 15 digit.',
            'jumlah_anggota_keluarga.max' => 'Jumlah anggota keluarga maksimal 15 orang.',
            'nama_bank_ewallet.max' => 'Nama bank/e-wallet maksimal 50 karakter.',
            'nama_bank_ewallet.regex' => 'Nama bank/e-wallet tidak valid.',
            'nomor_rekening.regex' => 'Nomor rekening/e-wallet hanya boleh berisi angka.',
            'nama_pemilik_rekening.regex' => 'Nama pemilik rekening tidak valid.',
        ]);

        $warga = Warga::create([
            'nik' => $validated['nik'],
            'nama' => $validated['nama'],
            'no_hp' => $validated['no_hp'] ?? null,
            'jumlah_anggota_keluarga' => $validated['jumlah_anggota_keluarga'] ?? 0,
            'alamat' => $validated['alamat'],
            'saldo' => 0,
            'tanggal_daftar' => now(),
            'nama_bank_ewallet' => $validated['nama_bank_ewallet'] ?? null,
            'nomor_rekening' => $validated['nomor_rekening'] ?? null,
            'nama_pemilik_rekening' => $validated['nama_pemilik_rekening'] ?? null,
            'status_rekening' => 'unverified',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Data warga berhasil ditambahkan.',
                'data' => $warga,
            ], 201);
        }

        return redirect()->route('admin.warga')->with('success', 'Data warga berhasil ditambahkan.');
    }

    public function show($id)
    {
        $warga = Warga::findOrFail($id);
        return view('admin.pages.warga', compact('warga'));
    }

    public function edit($id)
    {
        $warga = Warga::findOrFail($id);
        return view('admin.pages.form-warga', compact('warga'));
    }

    public function update(Request $request, $id)
    {
        $warga = Warga::findOrFail($id);

        $validated = $request->validate([
            'nik' => [
                'required',
                'string',
                'digits:16',
                Rule::unique('warga', 'nik')->ignore($id, 'id_warga'),
            ],
            'nama' => 'required|string|max:50|regex:/^[a-zA-Z\s\.\'\-]+$/',
            'no_hp' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
            'alamat' => 'required|string',
            'jumlah_anggota_keluarga' => 'required|integer|min:0|max:15',
            'saldo' => 'required|numeric|min:0',
            'tanggal_daftar' => 'required|date',
            'nama_bank_ewallet' => 'required|string|min:9|max:50|regex:/^[a-zA-Z\s\.\-]+$/',
            'nomor_rekening' => 'required|string|max:30|regex:/^[0-9]+$/',
            'nama_pemilik_rekening' => 'required|string|max:50|regex:/^[a-zA-Z\s\.\'\-]+$/',
            'status_rekening' => 'required|in:verified,unverified',
        ]);

        $warga->update([
            'nik' => $validated['nik'],
            'nama' => $validated['nama'],
            'no_hp' => $validated['no_hp'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'jumlah_anggota_keluarga' => $validated['jumlah_anggota_keluarga'] ?? 0,
            'saldo' => $validated['saldo'] ?? $warga->saldo,
            'tanggal_daftar' => $validated['tanggal_daftar'] ?? $warga->tanggal_daftar,
            'nama_bank_ewallet' => $validated['nama_bank_ewallet'] ?? null,
            'nomor_rekening' => $validated['nomor_rekening'] ?? null,
            'nama_pemilik_rekening' => $validated['nama_pemilik_rekening'] ?? null,
            'status_rekening' => $validated['status_rekening'] ?? $warga->status_rekening,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $warga]);
        }

        return redirect()->route('admin.warga')->with('success', 'Data warga berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $warga = Warga::findOrFail($id);
        $warga->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.warga')->with('success', 'Data warga berhasil dihapus.');
    }
}
