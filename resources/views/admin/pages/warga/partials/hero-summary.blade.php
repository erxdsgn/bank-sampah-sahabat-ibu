
    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">MASTER DATA</span>
            <h1 class="hero-title">
                Data <span class="accent">Warga</span>
            </h1>
            <p class="hero-sub">
                Kelola data masyarakat yang telah terdaftar sebagai anggota Bank Sampah.
            </p>
        </div>

        <div class="hero-actions">
            <!-- TOMBOL LEADERBOARD POIN (MEMBUKA MODAL) -->
            <button class="btn btn--ghost" type="button" onclick="bukaModalLeaderboard('bulan_ini')"
                style="display: inline-flex; align-items: center; gap: 8px;">
                <svg viewBox="0 0 24 24"
                    style="width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                    <path d="M4 22h16"></path>
                    <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                    <path d="M14 14.66V17c0 .55-.47.98-.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                </svg>
                Leaderboard Poin
            </button>

            <button class="btn btn--primary" type="button" onclick="bukaModal('modalTambah')">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"></path>
                </svg>
                Tambah Warga
            </button>
        </div>
    </section>

    <!-- STATS SUMMARY -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon--blue">
                <svg viewBox="0 0 24 24">
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"></path>
                    <circle cx="19" cy="8" r="2.3"></circle>
                    <path d="M17 21v-1a3 3 0 0 0-2-2.83"></path>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Warga</span>
                <span class="stat-value">{{ $warga->count() }} orang</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon--green">
                <svg viewBox="0 0 24 24">
                    <path d="M12 3v18"></path>
                    <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Saldo Terkumpul</span>
                <span class="stat-value">Rp {{ number_format($warga->sum('saldo'), 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon--amber">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                    <path d="M3 10h18"></path>
                    <path d="M16 15h2"></path>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Rekening Terverifikasi</span>
                <span class="stat-value">{{ $warga->where('status_rekening', 'verified')->count() }} Rekening</span>
            </div>
        </div>
    </div>

