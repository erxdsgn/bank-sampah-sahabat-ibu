@extends('admin.layouts.app')

@section('title', 'Leaderboard Poin Warga')
@section('active', 'leaderboard')
@section('crumbs', 'Konten & Laporan | Leaderboard Poin')

@section('content')

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">PRESTASI WARGA</span>
            <h1 class="hero-title">
                Leaderboard <span class="accent">Poin</span>
            </h1>
            <p class="hero-sub">
                Peringkat perolehan poin setoran sampah warga Bank Sampah Sahabat Ibu.
            </p>
        </div>

        <div class="hero-actions">
            <div class="btn-group" role="group" style="display: inline-flex; gap: 4px;">
                <a href="{{ route('admin.leaderboard', ['periode' => 'bulan_ini']) }}"
                   class="btn {{ $periode == 'bulan_ini' ? 'btn--primary' : 'btn--ghost' }}">
                    Bulan Ini
                </a>
                <a href="{{ route('admin.leaderboard', ['periode' => 'all_time']) }}"
                   class="btn {{ $periode == 'all_time' ? 'btn--primary' : 'btn--ghost' }}">
                    Semua Waktu
                </a>
            </div>
        </div>
    </section>

    <!-- MAIN DATA TABLE CARD -->
    <section class="card">
        <div class="table-no-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">Peringkat</th>
                        <th>Warga</th>
                        <th style="text-align: right;">Total Poin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaderboard as $index => $warga)
                        <tr>
                            <!-- PERINGKAT & MEDALI -->
                            <td style="text-align: center;">
                                @if($index == 0)
                                    <span class="badge-rank badge-gold">🥇 #1</span>
                                @elseif($index == 1)
                                    <span class="badge-rank badge-silver">🥈 #2</span>
                                @elseif($index == 2)
                                    <span class="badge-rank badge-bronze">🥉 #3</span>
                                @else
                                    <span style="font-weight: 700; color: var(--dashboard-text-secondary);">#{{ $index + 1 }}</span>
                                @endif
                            </td>

                            <!-- NAMA WARGA DENGAN INISIAL -->
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="table-avatar">
                                        {{ strtoupper(substr($warga->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong class="user-name">{{ $warga->nama }}</strong>
                                        <div class="mono text-subtle" style="font-size: 11px; margin-top: 2px;">Anggota Bank Sampah</div>
                                    </div>
                                </div>
                            </td>

                            <!-- TOTAL POIN -->
                            <td style="text-align: right;">
                                <strong class="saldo-text" style="font-size: 15px;">
                                    {{ number_format($warga->total_poin, 0, ',', '.') }} Poin
                                </strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 40px;">
                                <div style="font-size: 30px; margin-bottom: 10px;">🏆</div>
                                <strong>Belum Ada Data Peringkat</strong>
                                <p class="text-subtle" style="margin: 5px 0 0;">Belum ada setoran sampah yang disetujui pada periode ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <style>
        .badge-rank {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-gold { background: #fef3c0; color: #b45309; border: 1px solid #fde047; }
        .badge-silver { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .badge-bronze { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }

        .table-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(34, 197, 94, 0.12);
            color: #16a34a;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }
    </style>

@endsection
