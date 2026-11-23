{{-- =========================================================
         MODAL DETAIL TRANSAKSI
    ========================================================== --}}
    <div class="modal-overlay" id="modalDetail">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                        <circle cx="12" cy="12" r="2.5"></circle>
                    </svg>
                    Detail Transaksi
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalDetail')">&times;</button>
            </div>

            <div class="modal-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Jenis</span>
                        <span id="detailJenis">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tanggal</span>
                        <span class="detail-value" id="detailTanggal">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Kategori</span>
                        <span class="detail-value" id="detailKategori">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Sumber</span>
                        <span class="detail-value" id="detailSumber">-</span>
                    </div>
                    <div class="detail-item detail-item--full">
                        <span class="detail-label">Jumlah</span>
                        <span class="detail-value saldo" id="detailJumlah">-</span>
                    </div>
                    <div class="detail-item detail-item--full">
                        <span class="detail-label">Keterangan</span>
                        <span class="detail-value" id="detailKeterangan">-</span>
                    </div>
                </div>
            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalDetail')">Tutup</button>
            </div>
        </div>
    </div>
