<!-- =========================================================
             MODAL DETAIL
        ========================================================== -->
        <div class="modal-overlay" id="modalDetail">
            <div class="modal-box">

                <div class="modal-head">
                    <h3 class="modal-title">
                        <svg viewBox="0 0 24 24">
                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                            <circle cx="12" cy="12" r="2.5"></circle>
                        </svg>
                        Detail Produk
                    </h3>

                    <button class="modal-close" type="button" onclick="tutupModal('modalDetail')">&times;</button>
                </div>


                <div class="modal-body">

                    <img id="detailfoto" class="preview-img preview-img--besar" src="" alt="Preview">

                    <div id="detailfotoKosong" class="catalog-img-kosong catalog-img-kosong--besar">
                        📸 Tidak ada foto
                    </div>


                    <div class="detail-grid">

                        <div class="detail-item detail-item--full">
                            <span class="detail-label">Nama Barang</span>
                            <span class="detail-nama" id="detailNama">-</span>
                        </div>

                        <div class="detail-item detail-item--full">
                            <span class="detail-label">Deskripsi</span>
                            <span class="detail-value detail-deskripsi" id="detailDeskripsi">-</span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Harga</span>
                            <span class="detail-value saldo" id="detailHarga">-</span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Stok Tersedia</span>
                            <span class="detail-value" id="detailStok">-</span>
                        </div>

                    </div>

                </div>


                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalDetail')">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
