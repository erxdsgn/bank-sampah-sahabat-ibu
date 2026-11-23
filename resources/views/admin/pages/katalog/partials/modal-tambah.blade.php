<!-- =========================================================
             MODAL TAMBAH
        ========================================================== -->
        <div class="modal-overlay" id="modalTambah">
            <div class="modal-box">

                <div class="modal-head">
                    <h3 class="modal-title">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 5v14M5 12h14"></path>
                        </svg>
                        Tambah Produk Baru
                    </h3>

                    <button class="modal-close" type="button" onclick="tutupModal('modalTambah')">&times;</button>
                </div>


                <form id="formTambahProduk" onsubmit="return simpanTambah(event)">

                    <div class="modal-body">

                        <div class="form-grid">

                            <div class="form-group form-group--full">
                                <label for="tambahfoto">Foto Produk</label>
                                <input type="file" id="tambahfoto"
                                    accept="image/jpeg,image/png,image/jpg,image/webp" required>
                                <span class="field-hint">
                                    Format JPG, PNG, atau WEBP. Maksimal 2MB.
                                </span>
                            </div>

                            <div class="form-group form-group--full">
                                <label for="tambahNama">Nama Barang</label>
                                <input type="text" id="tambahNama" maxlength="80" required>
                            </div>

                            <div class="form-group form-group--full">
                                <label for="tambahDeskripsi">Deskripsi Produk</label>
                                <textarea id="tambahDeskripsi" rows="4" maxlength="500"
                                    placeholder="Contoh: Tas dari anyaman plastik bekas, tahan air, ukuran 30x25 cm."></textarea>
                                <span class="field-hint">
                                    Opsional, maksimal 500 karakter.
                                </span>
                            </div>

                            <div class="form-group">
                                <label for="tambahHarga">Harga (Rp)</label>
                                <input type="number" id="tambahHarga" min="1" max="500000" required>
                            </div>

                            <div class="form-group">
                                <label for="tambahStok">Stok</label>
                                <input type="number" id="tambahStok" min="1" max="500" required>
                            </div>

                        </div>

                    </div>


                    <div class="modal-foot">
                        <button class="btn btn--ghost" type="button" onclick="tutupModal('modalTambah')">
                            Batal
                        </button>

                        <button class="btn btn--primary" type="submit">
                            Simpan Produk
                        </button>
                    </div>

                </form>

            </div>
        </div>
