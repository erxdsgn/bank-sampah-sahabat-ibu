<section class="hero">
    <div class="hero-text">
        <span class="eyebrow">TRANSAKSI SAMPAH</span>

        <h1 class="hero-title">
            Verifikasi <span class="accent">Setoran</span>
        </h1>

        <p class="hero-sub">
            Verifikasi setoran sampah warga & Tambahkan setoran baru secara langsung.
        </p>
    </div>

    <div class="hero-actions">

        <div class="stat-chip">
            <span class="stat-dot stat-dot--amber"></span>
            <div>
                <div class="stat-num">{{ $setoran->where('status', 'pending')->count() }}</div>
                <div class="stat-label">Menunggu</div>
            </div>
        </div>

        <div class="stat-chip">
            <span class="stat-dot stat-dot--green"></span>
            <div>
                <div class="stat-num">{{ $setoran->where('status', 'approved')->count() }}</div>
                <div class="stat-label">Disetujui</div>
            </div>
        </div>

        <div class="stat-chip">
            <span class="stat-dot stat-dot--red"></span>
            <div>
                <div class="stat-num">{{ $setoran->where('status', 'rejected')->count() }}</div>
                <div class="stat-label">Ditolak</div>
            </div>
        </div>

        <button class="btn btn--primary" type="button" onclick="bukaTambahSetoran()">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Tambah Setoran
        </button>

    </div>
</section>
