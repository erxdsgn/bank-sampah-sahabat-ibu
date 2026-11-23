<!-- MODAL 1: TAMBAH / EDIT KATEGORI -->
    <div class="modal-overlay" id="modalKategori">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title" id="modalKategoriTitle">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span id="modalKategoriTitleText">Tambah Kategori</span>
                </h3>
                <button class="modal-close" type="button" aria-label="Tutup"
                    onclick="tutupModal('modalKategori')">&times;</button>
            </div>

            <form id="formKategori" class="modal-form" onsubmit="return simpanKategori(event)">
                <div class="modal-body">
                    <input type="hidden" id="kategoriId">
                    <div class="form-grid">
                        <div class="form-group form-group--full">
                            {{-- <label for="cari_id_induk">Kategori Induk (opsional)</label>
                            <div class="combo" id="combo_id_induk">
                                <input type="text" id="cari_id_induk" class="combo-input"
                                    placeholder="Pilih / ketik kategori induk..." autocomplete="off">
                                <div class="combo-list" id="list_id_induk"></div>
                            </div> --}}
                            <select id="id_induk" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="">— Tidak ada (kategori utama) —</option>
                                @foreach ($kategoriInduk as $induk)
                                    <option value="{{ $induk->id_kategori }}">{{ $induk->nama_kategori }}</option>
                                @endforeach
                            </select>
                            <span class="form-error" id="err_id_induk"></span>
                        </div>

                        <div class="form-group">
                            <label for="nama_kategori">Nama Kategori</label>
                            <input type="text" id="nama_kategori" class="form-control"
                                placeholder="Contoh: Botol Plastik" required autocomplete="off">
                            <span class="form-error" id="err_nama_kategori"></span>
                        </div>

                        <div class="form-group">
                            <label for="cari_satuan">Satuan Jual</label>
                            <div class="combo combo-satuan" id="combo_satuan">
                                <input type="text" id="cari_satuan" class="combo-input" placeholder="Pilih satuan..."
                                    autocomplete="off">
                                <div class="combo-list" id="list_satuan"></div>
                            </div>
                            <select id="satuan" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="kg">Kilogram (kg)</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="gram">Gram (gram)</option>
                                <option value="liter">Liter (L)</option>
                                <option value="set">Set (set)</option>
                                <option value="unit">Unit (unit)</option>
                            </select>
                            <span class="form-error" id="err_satuan"></span>
                        </div>

                        <div class="form-group" id="blokHargaAwal">
                            <label for="harga_awal">Harga Satuan (Rp)</label>
                            <input type="number" step="0.01" min="0" id="harga_awal" class="form-control"
                                placeholder="Contoh: 5000">
                            <span class="form-error" id="err_harga_awal"></span>
                        </div>

                        <div class="form-group" id="blokTanggalAwal">
                            <label for="tanggal_awal">Berlaku Mulai</label>
                            <input type="date" id="tanggal_awal" class="form-control">
                            <span class="form-error" id="err_tanggal_awal"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalKategori')">Batal</button>
                    <button class="btn btn--primary" type="submit" id="kategoriSubmitBtn">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
