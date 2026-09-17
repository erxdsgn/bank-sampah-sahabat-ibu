<aside class="d-sidebar">
    <!-- Brand / Header Sidebar -->
    <div class="brand">
        <div class="brand-text">
            <div class="brand-name">Bank Sampah</div>
            <div class="brand-tag">Sahabat Ibu</div>
        </div>
    </div>

    <!-- Group: Workspace -->
    <nav class="nav-section">
        <div class="nav-label">Workspace</div>
        <a class="nav-link {{ request()->is('admin/dashboard') ? 'is-active' : '' }}" href="{{ url('/admin/dashboard') }}">
            Dashboard
        </a>
    </nav>

    <!-- Group: Kelola Data Master -->
    <nav class="nav-section">
        <div class="nav-label">Master Data</div>
        <a class="nav-link" href="#">Data Warga</a>
        <a class="nav-link" href="#">Kategori & Harga Sampah</a>
        <a class="nav-link" href="#">Katalog Produk Daur Ulang</a>
        <a class="nav-link" href="#">Artikel Edukasi</a>
    </nav>

    <!-- Group: Transaksi & Operasional -->
    <nav class="nav-section">
        <div class="nav-label">Transaksi</div>
        <a class="nav-link" href="#">Verifikasi Setoran</a>
        <a class="nav-link" href="#">Riwayat Transaksi Setoran</a>
        <a class="nav-link" href="#">Pencairan Saldo</a>
        <a class="nav-link" href="#">Penjualan ke Pengepul</a>
    </nav>

    <!-- Group: Laporan & Pengaturan -->
    <nav class="nav-section">
        <div class="nav-label">Laporan & Akun</div>
        <a class="nav-link" href="#">Laporan Keuangan</a>
        <a class="nav-link" href="#">Pengaturan Akun</a>
    </nav>
</aside>
