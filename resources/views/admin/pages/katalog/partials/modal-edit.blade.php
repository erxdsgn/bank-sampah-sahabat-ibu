<!-- =========================================================
             MODAL EDIT
        ========================================================== -->
        <div class="modal-overlay" id="modalEdit">
            <div class="modal-box">

                <div class="modal-head">
                    <h3 class="modal-title">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                        </svg>
                        Edit Produk
                    </h3>

                    <button class="modal-close" type="button" onclick="tutupModal('modalEdit')">&times;</button>
                </div>


                <form id="formEditProduk" onsubmit="return simpanEdit(event)">

                    <div class="modal-body">

                        <input type="hidden" id="editId">

                        <div class="form-grid">

                            <div class="form-group form-group--full">
                                <span class="detail-label">Foto Saat Ini</span>
                                <img id="editfotoPreview" class="preview-img" src="" alt="Preview">
                                <div id="editfotoKosong" class="catalog-img-kosong catalog-img-kosong--kecil">
                                    📸 Tidak ada foto
                                </div>
                            </div>

                            <div class="form-group form-group--full">
                                <label for="editfoto">Ganti Foto (opsional)</label>
                                <input type="file" id="editfoto"
                                    accept="image/jpeg,image/png,image/jpg,image/webp">
                                <span class="field-hint">
                                    Biarkan kosong jika tidak ingin mengubah foto. Maks 2MB.
                                </span>
                            </div>

                            <div class="form-group form-group--full">
                                <label for="editNama">Nama Barang</label>
                                <input type="text" id="editNama" maxlength="80" required>
                            </div>

                            <div class="form-group form-group--full">
                                <label for="editDeskripsi">Deskripsi Produk</label>
                                <textarea id="editDeskripsi" rows="4" maxlength="500"
                                    placeholder="Tulis deskripsi singkat produk."></textarea>
                                <span class="field-hint">
                                    Opsional, maksimal 500 karakter.
                                </span>
                            </div>

                            <div class="form-group">
                                <label for="editHarga">Harga (Rp)</label>
                                <input type="number" id="editHarga" min="1" max="500000" required>
                            </div>

                            <div class="form-group">
                                <label for="editStok">Stok</label>
                                <input type="number" id="editStok" min="1" max="500" required>
                            </div>

                        </div>

                    </div>


                    <div class="modal-foot">
                        <button class="btn btn--ghost" type="button" onclick="tutupModal('modalEdit')">
                            Batal
                        </button>

                        <button class="btn btn--primary" type="submit">
                            Simpan Perubahan
                        </button>
                    </div>

                </form>

            </div>
        </div>
