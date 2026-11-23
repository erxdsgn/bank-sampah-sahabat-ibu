{{-- =========================================================
         SUMMARY CARDS
    ========================================================== --}}
    <section class="summary-grid">
        {{-- Total Pemasukan --}}
        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Total Pemasukan</div>
                    <div class="summary-value">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</div>
                </div>
                <div class="summary-icon summary-icon--income">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3v18"></path>
                        <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Akumulasi seluruh transaksi pemasukan</div>
        </div>

        {{-- Total Pengeluaran --}}
        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Total Pengeluaran</div>
                    <div class="summary-value">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
                </div>
                <div class="summary-icon summary-icon--expense">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3v18"></path>
                        <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Akumulasi seluruh transaksi pengeluaran</div>
        </div>

        {{-- Saldo Kas --}}
        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Saldo Kas</div>
                    <div class="summary-value">Rp {{ number_format($saldoKas, 0, ',', '.') }}</div>
                </div>
                <div class="summary-icon summary-icon--cash">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                        <path d="M3 10h18"></path>
                        <path d="M16 15h2"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Pemasukan dikurangi pengeluaran</div>
        </div>

        {{-- Jumlah Transaksi --}}
        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Jumlah Transaksi</div>
                    <div class="summary-value">{{ $jumlahTransaksi }}</div>
                </div>
                <div class="summary-icon summary-icon--transaction">
                    <svg viewBox="0 0 24 24">
                        <path d="M8 6h13"></path>
                        <path d="M8 12h13"></path>
                        <path d="M8 18h13"></path>
                        <path d="M3 6h.01"></path>
                        <path d="M3 12h.01"></path>
                        <path d="M3 18h.01"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Total transaksi yang tercatat</div>
        </div>
    </section>
