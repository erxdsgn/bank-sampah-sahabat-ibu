<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->query('periode', 'bulan_ini');

        $query = Warga::select('warga.id_warga', 'warga.nama')
            ->join('penyetoran', 'warga.id_warga', '=', 'penyetoran.id_warga')
            ->join('detail_setoran', 'penyetoran.id_setoran', '=', 'detail_setoran.id_setoran')
            ->where('penyetoran.status', 'approved');

        if ($periode === 'bulan_ini') {
            $query->whereMonth('penyetoran.tanggal_setoran', Carbon::now()->month)
                  ->whereYear('penyetoran.tanggal_setoran', Carbon::now()->year);
        }

        $leaderboard = $query->selectRaw('SUM(detail_setoran.poin) as total_poin')
            ->groupBy('warga.id_warga', 'warga.nama')
            ->orderByDesc('total_poin')
            ->take(10)
            ->get();

        return view('admin.pages.leaderboard', compact('leaderboard', 'periode'));
    }
}
