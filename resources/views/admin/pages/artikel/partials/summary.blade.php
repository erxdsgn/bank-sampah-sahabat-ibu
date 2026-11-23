{{-- SUMMARY --}}
    <section class="summary-grid" id="wrapperSummary">

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Total Konten</div>
                    <div class="summary-value">{{ $totalKonten }}</div>
                </div>

                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                        <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z"></path>
                        <path d="M9 13h6M9 17h6"></path>
                    </svg>
                </div>
            </div>

            <div class="summary-info">Seluruh artikel & edukasi</div>
        </div>


        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Dipublikasikan</div>
                    <div class="summary-value">{{ $totalPublikasi }}</div>
                </div>

                <div class="summary-icon summary-icon--income">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 6 9 17l-5-5"></path>
                    </svg>
                </div>
            </div>

            <div class="summary-info">Sudah tampil di aplikasi warga</div>
        </div>


        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Draft</div>
                    <div class="summary-value">{{ $totalDraft }}</div>
                </div>

                <div class="summary-icon summary-icon--expense">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                    </svg>
                </div>
            </div>

            <div class="summary-info">Belum ditayangkan</div>
        </div>


        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Acara Mendatang</div>
                    <div class="summary-value">{{ $totalAcara }}</div>
                </div>

                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                        <path d="M3 10h18"></path>
                        <path d="M8 3v4M16 3v4"></path>
                    </svg>
                </div>
            </div>

            <div class="summary-info">Segera hadir</div>
        </div>

    </section>
