<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB; // Pastikan DB facade di-import

class LeaderboardApiController extends Controller
{
    public function index(Request $request)
    {
        $sekarang = Carbon::now();
        // Parameter filter: 'season_ini' atau 'all_time' (default: season_ini)
        $periode = $request->query('periode', 'season_ini');

        $query = Warga::select('warga.id_warga', 'warga.nama')
            ->join('penyetoran', 'warga.id_warga', '=', 'penyetoran.id_warga')
            ->join('detail_setoran', 'penyetoran.id_setoran', '=', 'detail_setoran.id_setoran')
            ->where('penyetoran.status', 'approved');

        // Filter berdasarkan triwulan berjalan (Season Ini)
        if ($periode === 'season_ini') {
            $query->whereRaw('QUARTER(penyetoran.tanggal_setoran) = ?', [$sekarang->quarter])
                  ->whereYear('penyetoran.tanggal_setoran', $sekarang->year);
        }

        // Tambahkan COUNT(DISTINCT penyetoran.id_setoran) untuk menghitung total frekuensi setor
        $leaderboard = $query->selectRaw('SUM(detail_setoran.poin) as total_poin')
            ->selectRaw('COUNT(DISTINCT penyetoran.id_setoran) as total_setor')
            ->groupBy('warga.id_warga', 'warga.nama')
            ->orderByDesc('total_poin')
            ->orderByDesc('total_setor')
            ->take(10) 
            ->get()
            ->map(function ($item, $index) {
                // Logika helper 2 inisial huruf untuk avatar[cite: 9]
                $words = explode(' ', trim($item->nama));
                $initials = count($words) >= 2
                    ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                    : strtoupper(substr($item->nama, 0, 2));

                return [
                    'id_warga' => (string) $item->id_warga, // WAJIB ADA agar Flutter bisa cek user aktif
                    'rank' => $index + 1,
                    'nama' => $item->nama,
                    'initials' => $initials,
                    'total_poin' => (int) ($item->total_poin ?? 0),
                    'total_setor' => (int) ($item->total_setor ?? 0), // WAJIB ADA untuk teks "x setor" di Flutter
                ];
            });

        return response()->json([
            'status' => 'success',
            'periode' => $periode,
            'data' => $leaderboard
        ]);
    }
}
