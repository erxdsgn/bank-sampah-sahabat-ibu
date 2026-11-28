<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalSetoran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\AndroidConfig;   // [FIX] tambah import
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class JadwalSetoranController extends Controller
{
    public function index()
    {
        $jadwalSetoran = JadwalSetoran::orderBy('tanggal_setoran', 'asc')->get();
        return view('admin.pages.penjadwalan', compact('jadwalSetoran'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_agenda' => 'required|string|max:255',
            'tanggal_setoran' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'wilayah' => 'nullable|string|max:100',
            'kuota_maksimal' => 'required|integer|min:1|max:150',
            'status' => 'required|in:draft,dipublikasi,selesai,dibatalkan',
            'keterangan' => 'nullable|string',
        ], $this->pesanValidasi());

        $jadwal = JadwalSetoran::create($validated);

        if ($jadwal->status === 'dipublikasi') {
            $this->kirimNotifikasiJadwalBaru($jadwal);
        }

        return response()->json([
            'success' => true,
            'message' => 'Jadwal setoran berhasil ditambahkan.',
            'data' => $jadwal
        ]);
    }

    public function update(Request $request, $id)
    {
        $jadwal = JadwalSetoran::where('id_jadwal', $id)->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|in:draft,dipublikasi,selesai,dibatalkan',
        ]);

        $statusLama = $jadwal->status;
        $jadwal->update($validated);

        if ($statusLama !== 'dipublikasi' && $jadwal->status === 'dipublikasi') {
            $this->kirimNotifikasiJadwalBaru($jadwal);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status jadwal berhasil diperbarui.',
            'data' => $jadwal
        ]);
    }

    public function destroy($id)
    {
        $jadwal = JadwalSetoran::where('id_jadwal', $id)->firstOrFail();
        $jadwal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal setoran berhasil dikonfirmasi dan dihapus.'
        ]);
    }

    /**
     * Fungsi untuk mengirim notifikasi jadwal baru ke semua warga
     */
    private function kirimNotifikasiJadwalBaru(JadwalSetoran $jadwal)
    {
        try {
            $messaging = Firebase::messaging();

            // 1. Query dasar: ambil user dengan token & notif aktif
            $query = User::whereNotNull('fcm_token')
                ->where('notifikasi_aktif', true);

            // 2. FILTER BERDASARKAN KELOMPOK
            if (!empty($jadwal->wilayah) && $jadwal->wilayah !== 'Semua Kelompok') {
                $query->whereHas('warga', function ($q) use ($jadwal) {
                    $q->where('kelompok', $jadwal->wilayah);
                });
            }

            $wargaList = $query->get();

            if ($wargaList->isEmpty()) {
                Log::info('Tidak ada warga yang sesuai dengan target kelompok untuk dikirimi notifikasi.');
                return;
            }

            $tanggal = Carbon::parse($jadwal->tanggal_setoran)->isoFormat('dddd, D MMMM YYYY');
            $jam = Carbon::parse($jadwal->jam_mulai)->format('H:i') . ' - ' .
                Carbon::parse($jadwal->jam_selesai)->format('H:i') . ' WIB';

            $judul = 'Jadwal Setoran Baru! 📢';

            $infoTarget = ($jadwal->wilayah && $jadwal->wilayah !== 'Semua Kelompok')
                ? "Target: {$jadwal->wilayah}\n"
                : "Untuk: Semua Warga\n";

            $pesan = "Agenda: {$jadwal->nama_agenda}\nTanggal: {$tanggal}\nJam: {$jam}\n{$infoTarget}";

            // [FIX] AndroidConfig dengan priority high + channel_id
            // Channel ID ini HARUS sama dengan yang dibuat di Flutter
            // (flutter_local_notifications -> AndroidNotificationChannel) dan
            // default_notification_channel_id di AndroidManifest.xml.
            $androidConfig = AndroidConfig::fromArray([
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'high_importance_channel',
                    'sound' => 'default',
                    'default_vibrate_timings' => true,
                ],
            ]);

            foreach ($wargaList as $user) {
                $message = CloudMessage::new()
                    ->toToken($user->fcm_token)
                    ->withNotification(Notification::create($judul, $pesan))
                    ->withAndroidConfig($androidConfig)   // [FIX]
                    ->withData([
                        'type' => 'jadwal_setoran',
                        'id_jadwal' => (string) $jadwal->id_jadwal,
                    ]);

                try {
                    $messaging->send($message);
                } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
                    $user->update(['fcm_token' => null]);
                    Log::info('Token FCM tidak valid dihapus untuk user ID: ' . $user->id);
                } catch (\Exception $e) {
                    Log::error('Gagal kirim notifikasi ke user ID ' . $user->id . ': ' . $e->getMessage());
                }
            }

            Log::info("Notifikasi jadwal baru '{$jadwal->nama_agenda}' berhasil dikirim ke " . $wargaList->count() . " warga.");
        } catch (\Exception $e) {
            Log::error('Gagal menginisialisasi Firebase Messaging: ' . $e->getMessage());
        }
    }

    private function pesanValidasi(): array
    {
        return [
            'kuota_maksimal.required' => 'Maksimal kuota wajib diisi.',
            'kuota_maksimal.integer' => 'Maksimal kuota harus berupa angka bulat.',
            'kuota_maksimal.min' => 'Maksimal kuota minimal 1 warga.',
            'kuota_maksimal.max' => 'Maksimal kuota tidak boleh lebih dari 150 warga.',
            'jam_mulai.date_format' => 'Format jam mulai harus HH:MM.',
            'jam_selesai.date_format' => 'Format jam selesai harus HH:MM.',
            'jam_selesai.after' => 'Jam selesai harus lebih akhir dari jam mulai.',
            'tanggal_setoran.after_or_equal' => 'Tanggal setoran tidak boleh di hari yang sudah lewat.',
        ];
    }
}
