<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keuangan;
use App\Models\MutasiSaldo;
use App\Models\Pencairan;
use App\Models\Warga;
use App\Support\RingkasanKeuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PencairanController extends Controller
{
    public function index()
    {
        $pencairan = Pencairan::with('warga')
            ->orderByDesc('id_pencairan')
            ->get();

        $warga = Warga::orderBy('nama')->get([
            'id_warga',
            'nik',
            'nama',
            'saldo',
            'nama_bank_ewallet',
            'nomor_rekening',
            'status_rekening',
        ]);

        return view('admin.pages.pencairan', compact('pencairan', 'warga'));
    }

    /**
     * Hitung sisa kas riil Bank Sampah dari RingkasanKeuangan.
     */
    private function getSaldoKas(): float
    {
        if (class_exists(RingkasanKeuangan::class)) {
            $transaksi = RingkasanKeuangan::transaksi();

            $totalPemasukan = (float) $transaksi->filter(function ($item) {
                $jenis = is_array($item) ? ($item['jenis'] ?? '') : ($item->jenis ?? '');
                return strtolower($jenis) === 'pemasukan';
            })->sum('jumlah');

            $totalPengeluaran = (float) $transaksi->filter(function ($item) {
                $jenis = is_array($item) ? ($item['jenis'] ?? '') : ($item->jenis ?? '');
                return strtolower($jenis) === 'pengeluaran';
            })->sum('jumlah');

            return round($totalPemasukan - $totalPengeluaran, 2);
        }

        $totalPemasukan = (float) Keuangan::whereIn('jenis', ['Pemasukan', 'pemasukan'])->sum('jumlah');
        $totalPengeluaran = (float) Keuangan::whereIn('jenis', ['Pengeluaran', 'pengeluaran'])->sum('jumlah');

        return round($totalPemasukan - $totalPengeluaran, 2);
    }

    /**
     * Admin membuat permohonan pencairan baru.
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

        $jumlahPencairan = (float) $validated['jumlah'];
        $saldoWarga      = (float) ($warga->saldo ?? 0);
        $saldoKas        = $this->getSaldoKas();

        // 1. Validasi Saldo Tabungan Warga
        if ($jumlahPencairan > $saldoWarga) {
            return response()->json([
                'message' => 'Saldo tabungan warga tidak mencukupi (Saldo Warga: Rp '
                    . number_format($saldoWarga, 0, ',', '.')
                    . ', Permohonan: Rp '
                    . number_format($jumlahPencairan, 0, ',', '.') . ').',
            ], 422);
        }

        // 2. Validasi Kas Fisik Bank Sampah
        if ($jumlahPencairan > $saldoKas) {
            return response()->json([
                'message' => 'Kas fisik Bank Sampah tidak mencukupi (Sisa Kas: Rp '
                    . number_format(max(0, $saldoKas), 0, ',', '.')
                    . ', Permohonan: Rp '
                    . number_format($jumlahPencairan, 0, ',', '.') . '). Silakan lakukan penjualan sampah ke pengepul terlebih dahulu.',
            ], 422);
        }

        $pencairan = Pencairan::create([
            'id_warga' => $validated['id_warga'],
            'id_admin' => Auth::user()->id_admin ?? Auth::id() ?? 1,
            'tanggal_pencairan' => $validated['tanggal_pencairan'],
            'jumlah' => $jumlahPencairan,
            'metode_transfer' => $validated['metode_transfer'],
            'status' => 'menunggu',
        ]);

        return response()->json([
            'message' => 'Permohonan pencairan berhasil dibuat.',
            'data' => $pencairan->load('warga'),
        ]);
    }

    /**
     * Admin memproses pencairan.
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

        if ($validated['status'] === 'selesai') {
            $warga = Warga::where('id_warga', $pencairan->id_warga)->first();

            $jumlahPencairan = (float) $pencairan->jumlah;
            $saldoWarga      = (float) ($warga->saldo ?? 0);
            $saldoKas        = $this->getSaldoKas();

            // 1. Validasi Saldo Warga
            if (!$warga || $jumlahPencairan > $saldoWarga) {
                return response()->json([
                    'message' => 'Saldo tabungan warga tidak mencukupi (Saldo Warga: Rp '
                        . number_format($saldoWarga, 0, ',', '.')
                        . ', Permohonan: Rp '
                        . number_format($jumlahPencairan, 0, ',', '.') . ').',
                ], 422);
            }

            // 2. Validasi Kas Fisik Bank Sampah
            if ($jumlahPencairan > $saldoKas) {
                return response()->json([
                    'message' => 'Kas fisik Bank Sampah tidak mencukupi (Sisa Kas: Rp '
                        . number_format(max(0, $saldoKas), 0, ',', '.')
                        . ', Permohonan: Rp '
                        . number_format($jumlahPencairan, 0, ',', '.') . '). Silakan lakukan penjualan sampah ke pengepul terlebih dahulu.',
                ], 422);
            }

            DB::transaction(function () use ($pencairan, $warga, $jumlahPencairan) {
                // Potong saldo tabungan warga
                $warga->decrement('saldo', $jumlahPencairan);

                // Catat mutasi keluar pada tabungan warga
                MutasiSaldo::create([
                    'id_warga' => $pencairan->id_warga,
                    'id_setoran' => null,
                    'jenis_mutasi' => 'keluar',
                    'jumlah' => $jumlahPencairan,
                    'tanggal' => now()->toDateString(),
                    'keterangan' => 'Pencairan saldo #' . $pencairan->id_pencairan,
                ]);

                // Catat Pengeluaran Kas di modul Keuangan
                Keuangan::create([
                    'jenis'              => 'Pengeluaran',
                    'tanggal'            => now()->toDateString(),
                    'kategori_transaksi' => 'Pencairan Saldo Warga',
                    'keterangan'         => 'Pencairan saldo untuk ' . $warga->nama
                        . ' (' . $warga->nik . '), metode: ' . $pencairan->metode_transfer,
                    'jumlah'             => $jumlahPencairan,
                    'id_admin'           => Auth::id() ?? $pencairan->id_admin ?? 1,
                    'id_referensi'       => $pencairan->id_pencairan,
                    'tipe_referensi'     => 'pencairan_saldo',
                ]);

                $pencairan->update(['status' => 'selesai']);
            });
        } else {
            $pencairan->update(['status' => $validated['status']]);
        }

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
