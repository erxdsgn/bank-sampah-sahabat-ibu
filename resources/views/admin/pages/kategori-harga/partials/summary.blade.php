<!-- SUMMARY CARDS -->
    <section class="summary-grid">
        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Kategori Induk</div>
                    <div class="summary-value">{{ $totalInduk }}</div>
                </div>
                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                        <path d="M3 10h18"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Jumlah kategori utama</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Sub Kategori</div>
                    <div class="summary-value">{{ $totalSub }}</div>
                </div>
                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M6 3v12a3 3 0 0 0 3 3h9"></path>
                        <circle cx="18" cy="6" r="2"></circle>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Jumlah kategori turunan</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Sudah Ada Harga</div>
                    <div class="summary-value">{{ $totalHarga }}</div>
                </div>
                <div class="summary-icon summary-icon--income">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 6 9 17l-5-5"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Kategori dengan harga aktif</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Belum Ada Harga</div>
                    <div class="summary-value">{{ $totalBelum }}</div>
                </div>
                <div class="summary-icon summary-icon--expense">
                    <svg viewBox="0 0 24 24">
                        <path d="M18 6 6 18M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Kategori yang perlu ditetapkan harganya</div>
        </div>
    </section>
