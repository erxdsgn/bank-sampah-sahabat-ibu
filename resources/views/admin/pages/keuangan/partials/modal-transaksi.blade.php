{{-- =========================================================
         MODAL TAMBAH TRANSAKSI
    ========================================================== --}}
    <div class="modal-overlay" id="modalTransaksi">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Transaksi
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalTransaksi')">&times;</button>
            </div>

            <form id="formTransaksi" onsubmit="return simpanTransaksi(event)">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="cari_transaksiJenis">Jenis Transaksi</label>
                            <div class="combo combo--arrow" id="combo_transaksiJenis">
                                <input type="text" id="cari_transaksiJenis" class="combo-input" autocomplete="off">
                                <div class="combo-list" id="list_transaksiJenis"></div>
                            </div>
                            <select id="transaksiJenis" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="">Pilih jenis transaksi</option>
                                <option value="Pemasukan">Pemasukan</option>
                                <option value="Pengeluaran">Pengeluaran</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="transaksiTanggal">Tanggal</label>
                            <input type="date" id="transaksiTanggal" required>
                        </div>

                        <div class="form-group">
                            <label for="transaksiKategori">Kategori</label>
                            <input type="text" id="transaksiKategori" placeholder="Contoh: Operasional" required>
                        </div>

                        <div class="form-group">
                            <label for="transaksiJumlah">Jumlah (Rp)</label>
                            <input type="number" id="transaksiJumlah" min="0" placeholder="0" required>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="transaksiKeterangan">Keterangan</label>
                            <textarea id="transaksiKeterangan" rows="3" placeholder="Tambahkan keterangan transaksi..."></textarea>
                        </div>
                    </div>

                    <p class="form-note">
                        Catatan: transaksi yang sudah disimpan tidak dapat diedit, hanya dapat dilihat atau dihapus.
                    </p>
                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalTransaksi')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
