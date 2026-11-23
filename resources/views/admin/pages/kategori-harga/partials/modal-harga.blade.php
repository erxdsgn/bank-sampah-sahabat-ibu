<!-- MODAL 2: KELOLA HARGA -->
    <div class="modal-overlay" id="modalHarga">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path
                            d="M15 8.5c-.6-.7-1.6-1.1-2.8-1.1-1.7 0-2.8.9-2.8 2.2 0 1.2 1 1.8 2.8 2.2 1.8.4 2.8 1 2.8 2.2 0 1.3-1.1 2.2-2.9 2.2-1.3 0-2.4-.5-3.1-1.3M12 5v14">
                        </path>
                    </svg>
                    <span>Kelola Harga — <span id="hargaNamaKategori">-</span></span>
                </h3>
                <button class="modal-close" type="button" aria-label="Tutup"
                    onclick="tutupModal('modalHarga')">&times;</button>
            </div>

            <div class="modal-body">
                <form id="formHarga" onsubmit="return tambahHarga(event)">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="harga_satuan">Harga per <span id="hargaSatuanLabel">satuan</span> (Rp)</label>
                            <input type="number" step="0.01" min="0" id="harga_satuan" class="form-control"
                                placeholder="Contoh: 5000" required>
                            <span class="form-error" id="err_harga_satuan"></span>
                        </div>

                        <div class="form-group">
                            <label for="tanggal_berlaku">Berlaku Mulai</label>
                            <input type="date" id="tanggal_berlaku" class="form-control" required>
                            <span class="form-error" id="err_tanggal_berlaku"></span>
                        </div>
                    </div>

                    <div class="form-actions--modal">
                        <button class="btn btn--primary" type="submit" id="hargaSubmitBtn">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Tambah Harga
                        </button>
                    </div>
                </form>

                <h4 class="modal-subtitle">Riwayat Penetapan Harga</h4>

                <div class="table-responsive">
                    <table class="data-table data-table--sm">
                        <thead>
                            <tr>
                                <th>Tanggal Berlaku</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="hargaTableBody">
                            <tr>
                                <td colspan="3" class="empty-desc text-center">Belum ada riwayat harga.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHarga')">Tutup</button>
            </div>
        </div>
    </div>
