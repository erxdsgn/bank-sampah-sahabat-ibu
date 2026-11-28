<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengaduanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengaduan::with(['user', 'warga'])
            ->orderByRaw("CASE WHEN status = 'menunggu' THEN 0 WHEN status = 'diproses' THEN 1 ELSE 2 END")
            ->orderByDesc('id_pengaduan');

        if ($request->filled('status') && in_array($request->status, ['menunggu', 'diproses', 'selesai', 'ditolak'], true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kategori') && array_key_exists($request->kategori, Pengaduan::KATEGORI_LABEL)) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($sub) use ($search) {
                $sub->where('judul', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhereHas('warga', function ($w) use ($search) {
                        $w->where('nama', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $pengaduans = $query->paginate(10)->withQueryString();

        $counts = [
            'all'      => Pengaduan::count(),
            'menunggu' => Pengaduan::where('status', 'menunggu')->count(),
            'diproses' => Pengaduan::where('status', 'diproses')->count(),
            'selesai'  => Pengaduan::where('status', 'selesai')->count(),
            'ditolak'  => Pengaduan::where('status', 'ditolak')->count(),
        ];

        return view('admin.pages.pengaduan', compact('pengaduans', 'counts'));
    }

    public function summary()
    {
        $menungguCount = Pengaduan::where('status', 'menunggu')->count();

        $terbaru = Pengaduan::with(['user', 'warga'])
            ->where('status', 'menunggu')
            ->orderByDesc('id_pengaduan')
            ->limit(4)
            ->get()
            ->map(function (Pengaduan $item) {
                $nama = optional($item->warga)->nama
                    ?? optional($item->user)->username
                    ?? 'Warga';

                $parts = preg_split('/\s+/', trim((string) $nama)) ?: ['W'];
                $inisial = strtoupper(
                    substr($parts[0] ?? 'W', 0, 1) .
                    (isset($parts[1]) ? substr($parts[1], 0, 1) : '')
                );

                return [
                    'id'             => $item->id_pengaduan,
                    'inisial'        => $inisial ?: 'W',
                    'nama'           => $nama,
                    'judul'          => $item->judul,
                    'kategori_label' => $item->kategori_label,
                    'url'            => route('admin.pengaduan.index', ['q' => $item->judul]),
                ];
            });

        return response()->json([
            'menunggu_count' => $menungguCount,
            'terbaru'        => $terbaru,
        ]);
    }

    public function update(Request $request, Pengaduan $pengaduan)
    {
        $validated = $request->validate(
            [
                'status'          => ['required', 'string', 'in:menunggu,diproses,selesai,ditolak'],
                'tanggapan_admin' => ['nullable', 'string', 'max:2000'],
            ],
            [
                'status.required'     => 'Status pengaduan wajib dipilih.',
                'status.in'           => 'Status pengaduan tidak valid.',
                'tanggapan_admin.max' => 'Tanggapan maksimal 2000 karakter.',
            ]
        );

        $pengaduan->forceFill([
            'status'          => $validated['status'],
            'tanggapan_admin' => $validated['tanggapan_admin'] !== null ? trim($validated['tanggapan_admin']) : null,
            'id_admin'        => Auth::id(),
            'ditanggapi_at'   => now(),
        ])->save();

        return redirect()
            ->route('admin.pengaduan.index')
            ->with('success', 'Status dan tanggapan pengaduan berhasil diperbarui.');
    }
}
