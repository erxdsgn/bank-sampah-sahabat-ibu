<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;
use Carbon\Carbon;

class DeviceManagementController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil semua sesi milik user yang sedang login
        $sessions = DB::table('sessions')
            ->where('user_id', Auth::id())
            ->orderBy('last_activity', 'desc')
            ->get();

        // 2. Format data agar siap ditampilkan di view
        $devices = $sessions->map(function ($session) use ($request) {
            $agent = new Agent();
            $agent->setUserAgent($session->user_agent);

            return (object) [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'platform' => $agent->platform(),    // Windows, iOS, Android, Linux, macOS
                'browser' => $agent->browser(),      // Chrome, Safari, Firefox, Edge
                'device' => $agent->device(),        // iPhone, Samsung, atau Desktop
                'is_desktop' => $agent->isDesktop(),
                'last_activity' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                'is_current_device' => $session->id === $request->session()->getId(),
            ];
        });

        return view('admin.devices', compact('devices'));
    }

    public function logoutDevice(Request $request, $sessionId)
    {
        // Hapus sesi spesifik berdasarkan ID
        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', Auth::id())
            ->delete();

        return back()->with('success', 'Perangkat berhasil dikeluarkan.');
    }
}
