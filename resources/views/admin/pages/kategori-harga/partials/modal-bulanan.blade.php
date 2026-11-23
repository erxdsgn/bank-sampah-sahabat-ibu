<!-- MODAL 4: REKAP HARGA PER BULAN -->
    <div class="modal-overlay" id="modalBulanan">
        <div class="modal-box modal-box--xl">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <rect x="3" y="4" width="18" height="17" rx="2"></rect>
                        <path d="M3 10h18M8 2v4M16 2v4"></path>
                    </svg>
                    <span>Update Harga per Bulan</span>
                </h3>
                <button class="modal-close" type="button" aria-label="Tutup"
                    onclick="tutupModal('modalBulanan')">&times;</button>
            </div>

            <div class="modal-body">
                <div class="bulan-bar">
                    <button class="btn btn--ghost" type="button" onclick="geserTahun(-1)"
                        aria-label="Tahun sebelumnya">‹</button>
                    <div class="combo combo--arrow" id="combo_rekapTahun">
                        <input type="text" id="cari_rekapTahun" class="combo-input" placeholder=""
                            autocomplete="off">
                        <div class="combo-list" id="list_rekapTahun"></div>
                    </div>
                    <select id="rekapTahun" class="combo-hidden" tabindex="-1" aria-hidden="true"
                        onchange="renderRekap()"></select>
                    <button class="btn btn--ghost" type="button" onclick="geserTahun(1)"
                        aria-label="Tahun berikutnya">›</button>
                    <span class="rekap-legend"><i class="dot"></i> harga berubah dari bulan sebelumnya</span>
                </div>

                <div class="rekap-wrap">
                    <table class="rekap-table">
                        <thead id="rekapHead"></thead>
                        <tbody id="rekapBody"></tbody>
                    </table>
                </div>

                <p class="muted legend-info">
                    Harga dalam Rupiah. Tanda “–” berarti belum ada harga pada bulan itu.
                    Bulan tanpa perubahan otomatis memakai harga bulan sebelumnya.
                </p>
            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalBulanan')">Tutup</button>
            </div>
        </div>
    </div>
