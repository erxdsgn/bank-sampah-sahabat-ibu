<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalSetoran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class JadwalSetoranApiController extends Controller
{
    /**
     * Mengambil seluruh daftar jadwal setoran untuk Flutter.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Ambil user yang sedang login
            $user = $request->user();
            $kelompokUser = $user->warga->kelompok ?? null;

            $query = JadwalSetoran::whereIn('status', ['dipublikasi', 'selesai']);

            // FILTER BERDASARKAN KELOMPOK WARGA
            if ($kelompokUser) {
                $query->where(function($q) use ($kelompokUser) {
                    $q->where('wilayah', $kelompokUser)
                      ->orWhereNull('wilayah')
                      ->orWhere('wilayah', 'Semua Kelompok')
                      ->orWhere('wilayah', '');
                });
            }

            if ($request->has('month') && $request->has('year')) {
                $query->whereMonth('tanggal_setoran', $request->month)
                      ->whereYear('tanggal_setoran', $request->year);
            }

            $schedules = $query->orderBy('tanggal_setoran', 'desc')->get();

            $formattedData = $schedules->map(function ($item) {
                return $this->formatForFlutter($item);
            });

            return response()->json([
                'success' => true,
                'message' => 'Daftar jadwal setoran berhasil dimuat.',
                'total'   => $formattedData->count(),
                'data'    => $formattedData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat jadwal setoran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil detail 1 jadwal setoran berdasarkan ID.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $kelompokUser = $user->warga->kelompok ?? null;

            $jadwal = JadwalSetoran::where('id_jadwal', $id)->first();

            if (!$jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal setoran tidak ditemukan.'
                ], 404);
            }

            // Validasi: Apakah jadwal ini diperuntukkan bagi kelompok user?
            if ($kelompokUser && $jadwal->wilayah && $jadwal->wilayah !== 'Semua Kelompok' && $jadwal->wilayah !== $kelompokUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke jadwal ini.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail jadwal setoran berhasil ditemukan.',
                'data'    => $this->formatForFlutter($jadwal)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil jadwal setoran aktif terdekat.
     */
    public function getActiveSession(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $kelompokUser = $user->warga->kelompok ?? null;

            $query = JadwalSetoran::where('status', 'dipublikasi')
                ->where('tanggal_setoran', '>=', Carbon::today());

            // FILTER KELOMPOK
            if ($kelompokUser) {
                $query->where(function($q) use ($kelompokUser) {
                    $q->where('wilayah', $kelompokUser)
                      ->orWhereNull('wilayah')
                      ->orWhere('wilayah', 'Semua Kelompok')
                      ->orWhere('wilayah', '');
                });
            }

            $activeJadwal = $query->orderBy('tanggal_setoran', 'asc')->first();

            if (!$activeJadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada jadwal setoran mendatang.',
                    'data'    => null
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Jadwal setoran terdekat berhasil dimuat.',
                'data'    => $this->formatForFlutter($activeJadwal)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat jadwal terdekat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper Format Data -> JSON Flutter
     */
    private function formatForFlutter($item): array
    {
        Carbon::setLocale('id');
        $date = Carbon::parse($item->tanggal_setoran);
        $today = Carbon::today();

        $isPast = $date->lt($today) || $item->status === 'selesai';
        $daysDiff = $today->diffInDays($date, false);

        if ($isPast) {
            $countdownText = 'Selesai';
        } elseif ($daysDiff == 0) {
            $countdownText = 'Hari Ini';
        } else {
            $countdownText = $daysDiff . ' Hari Lagi';
        }

        $timeRange = sprintf(
            '%s - %s WIB',
            Carbon::parse($item->jam_mulai)->format('H.i'),
            Carbon::parse($item->jam_selesai)->format('H.i')
        );

        return [
            'id'            => $item->id_jadwal,
            'sessionTitle'  => strtoupper($item->nama_agenda),
            'dayName'       => strtoupper($date->isoFormat('dddd')),
            'dayNumber'     => (int) $date->format('d'),
            'monthYear'     => $date->isoFormat('MMMM YYYY'),
            'fullDate'      => $date->format('Y-m-d'),
            'timeRange'     => $timeRange,
            'countdownText' => $countdownText,
            'isCompleted'   => $isPast,
            'wilayah'       => $item->wilayah ?? 'Semua Kelompok',
            'kuotaMaksimal' => (int) $item->kuota_maksimal,
            'keterangan'    => $item->keterangan,
            'status'        => $item->status,
        ];
    }
}
