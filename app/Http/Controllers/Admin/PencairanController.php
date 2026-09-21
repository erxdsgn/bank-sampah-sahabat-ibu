<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keuangan;
use App\Models\MutasiSaldo;
use App\Models\Pencairan;
use App\Models\Warga;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PencairanController extends Controller
{
    public function index()
    {
        $pencairan = Pencairan::with('warga')
            ->orderByDesc('id_pencairan')
            ->get();

        $warga = Warga::orderBy('nama')->get(['id_warga', 'nik', 'nama', 'saldo']);

        return view('admin.pages.pencairan', compact('pencairan', 'warga'));
    }

    /**
     * Admin membuat permohonan pencairan baru dengan memilih warga.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_warga' => 'required|exists:warga,id_warga',
            'jumlah' => 'required|numeric|min:1',
            'metode_transfer' => 'required|string|max:50',
            'tanggal_pencairan' => 'required|date',
        ]);

        $warga = Warga::findOrFail($validated['id_warga']);

        if ($validated['jumlah'] > ($warga->saldo ?? 0)) {
            return response()->json([
                'message' => 'Saldo warga tidak mencukupi untuk jumlah pencairan ini.',
            ], 422);
        }

        $pencairan = Pencairan::create([
            'id_warga' => $validated['id_warga'],
            'id_admin' => Auth::user()->id_admin ?? Auth::id(),
            'tanggal_pencairan' => $validated['tanggal_pencairan'],
            'jumlah' => $validated['jumlah'],
            'metode_transfer' => $validated['metode_transfer'],
            'status' => 'menunggu',
        ]);

        return response()->json([
            'message' => 'Permohonan pencairan berhasil dibuat.',
            'data' => $pencairan->load('warga'),
        ]);
    }

    /**
     * Admin memproses (ubah status) pencairan.
     * Saldo warga hanya dikurangi saat status berubah menjadi "selesai",
     * dan pada saat itu juga dicatat otomatis sebagai pengeluaran di kas.
     */
    public function update(Request $request, Pencairan $pencairan)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['menunggu', 'selesai', 'ditolak'])],
        ]);

        if ($pencairan->status === 'selesai') {
            return response()->json([
                'message' => 'Pencairan ini sudah selesai diproses dan tidak dapat diubah lagi.',
            ], 422);
        }

        DB::transaction(function () use ($pencairan, $validated) {
            if ($validated['status'] === 'selesai') {
                $warga = Warga::where('id_warga', $pencairan->id_warga)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($pencairan->jumlah > ($warga->saldo ?? 0)) {
                    abort(response()->json([
                        'message' => 'Saldo warga tidak mencukupi, pencairan tidak dapat diselesaikan.',
                    ], 422));
                }

                $warga->decrement('saldo', (float) $pencairan->jumlah);

                MutasiSaldo::create([
                    'id_warga' => $pencairan->id_warga,
                    'id_setoran' => null,
                    'jenis_mutasi' => 'keluar',
                    'jumlah' => $pencairan->jumlah,
                    'tanggal' => now()->toDateString(),
                    'keterangan' => 'Pencairan saldo #' . $pencairan->id_pencairan,
                ]);

                // Catat otomatis ke kas sebagai pengeluaran
                Keuangan::create([
                    'jenis'              => 'pengeluaran',
                    'tanggal'            => now()->toDateString(),
                    'kategori_transaksi' => 'Pencairan Saldo Warga',
                    'keterangan'         => 'Pencairan saldo untuk ' . $warga->nama
                        . ' (' . $warga->nik . '), metode: ' . $pencairan->metode_transfer,
                    'jumlah'             => $pencairan->jumlah,
                    'id_admin'           => $pencairan->id_admin,
                    'id_referensi'       => $pencairan->id_pencairan,
                    'tipe_referensi'     => 'pencairan_saldo',
                ]);
            }

            $pencairan->update(['status' => $validated['status']]);
        });

        return response()->json([
            'message' => 'Status pencairan berhasil diperbarui.',
            'data' => $pencairan->fresh('warga'),
        ]);
    }

    public function destroy(Pencairan $pencairan)
    {
        if ($pencairan->status === 'selesai') {
            return response()->json([
                'message' => 'Pencairan yang sudah selesai tidak dapat dihapus.',
            ], 422);
        }

        $pencairan->delete();

        return response()->json(['message' => 'Data pencairan berhasil dihapus.']);
    }
}
