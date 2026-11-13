<div class="modal-head">
    <h3 class="modal-title">
        <svg viewBox="0 0 24 24" style="width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 1.8;"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path><path d="M14 14.66V17c0 .55-.47.98-.97 1.21C16.15 18.75 17 20.24 17 22"></path><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path></svg>
        Leaderboard Poin Warga (Season Ini)
    </h3>
    <button class="modal-close" type="button" onclick="tutupModal('modalLeaderboard')">&times;</button>
</div>

<div class="modal-body" style="padding-top: 10px;">
    <!-- Tombol Filter Periode -->
    <div style="display: flex; justify-content: center; margin-bottom: 16px;">
        <div style="display: inline-flex; background: var(--dashboard-table-head, #f8fafc); padding: 3px; border-radius: 10px; border: 1px solid var(--dashboard-border, #e5e7eb); gap: 2px;">
            <button type="button" onclick="loadLeaderboardPopup('season_ini')" style="padding: 6px 16px; font-size: 12.5px; font-weight: 600; border-radius: 8px; border: none; cursor: pointer; transition: all 0.2s ease; background: {{ $periode == 'season_ini' ? '#10b981' : 'transparent' }}; color: {{ $periode == 'season_ini' ? '#ffffff' : 'var(--dashboard-text-secondary, #6b7280)' }}; box-shadow: {{ $periode == 'season_ini' ? '0 2px 6px rgba(16, 185, 129, 0.25)' : 'none' }};">Season Ini</button>

            <button type="button" onclick="loadLeaderboardPopup('all_time')" style="padding: 6px 16px; font-size: 12.5px; font-weight: 600; border-radius: 8px; border: none; cursor: pointer; transition: all 0.2s ease; background: {{ $periode == 'all_time' ? '#10b981' : 'transparent' }}; color: {{ $periode == 'all_time' ? '#ffffff' : 'var(--dashboard-text-secondary, #6b7280)' }}; box-shadow: {{ $periode == 'all_time' ? '0 2px 6px rgba(16, 185, 129, 0.25)' : 'none' }};">Semua Waktu</button>
        </div>
    </div>

    <!-- Tabel Peringkat -->
    <div class="table-no-scroll" style="max-height: 320px; overflow-y: auto;">
        <table class="data-table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 60px; text-align: center;">Rank</th>
                    <th>Warga</th>
                    <th style="text-align: right;">Poin</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaderboard as $index => $warga)
                    @php
                        // Logika mengambil 2 Inisial Nama (Contoh: "Basana Marpaung" -> "BM")
                        $words = explode(' ', trim($warga->nama));
                        $initials = '';
                        if (count($words) >= 2) {
                            $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                        } else {
                            $initials = strtoupper(substr($warga->nama, 0, 2));
                        }
                    @endphp
                    <tr>
                        <td style="text-align: center;">
                            @if($index == 0) <span style="font-weight: 700; color: #d97706; font-size: 16px;">🥇</span>
                            @elseif($index == 1) <span style="font-weight: 700; color: #64748b; font-size: 16px;">🥈</span>
                            @elseif($index == 2) <span style="font-weight: 700; color: #c2410c; font-size: 16px;">🥉</span>
                            @else <span style="color: var(--dashboard-text-secondary); font-weight: 600;">#{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <!-- Avatar 2 Inisial -->
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(34, 197, 94, 0.12); color: #16a34a; font-weight: 700; font-size: 11px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; letter-spacing: 0.5px;">
                                    {{ $initials }}
                                </div>
                                <strong class="user-name" style="font-size: 13px;">{{ $warga->nama }}</strong>
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <strong class="saldo-text" style="font-size: 13px;">{{ number_format($warga->total_poin ?? 0, 0, ',', '.') }} P</strong>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 30px; color: var(--dashboard-text-secondary);">
                            Belum ada data setoran yang disetujui pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-foot">
    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalLeaderboard')">Tutup</button>
</div>
