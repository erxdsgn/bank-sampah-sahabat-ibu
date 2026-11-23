<select id="masterKategori" class="combo-hidden" tabindex="-1" aria-hidden="true">
    <option value="" data-harga="0" data-satuan="" disabled selected>
        -- Pilih Kategori Sampah --
    </option>

    @foreach ($kategoriSampah as $kategori)
        @php
            $harga = $kategori->hargaTerbaru->harga_satuan ?? null;
            $satuan = $normalisasiSatuan($kategori->satuan ?? 'gram');
            $namaKat = $kategori->nama_kategori ?? $kategori->nama_lengkap ?? $kategori->nama ?? '-';
            $idKat = $kategori->id_kategori ?? $kategori->id;
        @endphp

        <option value="{{ $idKat }}" data-harga="{{ $harga ?? 0 }}"
            data-satuan="{{ $satuan }}">
            {{ $namaKat }} —
            @if ($harga !== null)
                Rp {{ $fmtHarga($harga) }} / {{ $simbolSatuan($satuan) }}
            @else
                belum ada harga
            @endif
        </option>
    @endforeach
</select>


<!-- ========================================================= -->
<!-- MODAL VERIFIKASI -->
<!-- ========================================================= -->
<div class="modal-overlay" id="modalVerifikasi">
    <div class="modal-box modal-box--wide">
        <div class="modal-head">
            <h3 class="modal-title">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span id="verifJudul">Verifikasi Setoran</span>
            </h3>
            <button class="modal-close" type="button" onclick="tutupModal('modalVerifikasi')">
                &times;
            </button>
        </div>

        <form id="formVerifikasi" onsubmit="return prosesVerifikasi(event)">
            <div class="modal-body">
                <input type="hidden" id="verifId">

                <!-- WARGA -->
                <div class="detail-avatar-row">
                    <div class="detail-avatar" id="verifAvatar">-</div>
                    <div>
                        <div class="detail-nama" id="verifNama">-</div>
                        <div class="detail-nik mono" id="verifNik">-</div>
                        <div class="detail-nik mono" id="verifKode">-</div>
                        <span class="status-badge" id="verifStatus" style="margin-top:6px;"></span>
                    </div>
                </div>

                <!-- FOTO -->
                <div class="form-group form-group--full" id="verifFotoBlock">
                    <label>Foto Bukti dari Warga</label>
                    <div class="photo-frame" id="verifFotoWrap">
                        <span id="verifFotoKosong">Tidak ada foto dilampirkan</span>
                        <img id="verifFoto" src="" alt="Foto bukti setoran" style="display:none;">
                    </div>
                </div>

                <!-- RINCIAN SAMPAH -->
                <div class="items-section">
                    <div class="items-head">
                        <div class="items-title">
                            Rincian Sampah
                            <span class="items-count" id="verifItemCount">0 jenis</span>
                        </div>
                        <button type="button" class="items-add" id="verifAddItem">+ Tambah Jenis</button>
                    </div>

                    <div class="item-list" id="verifItemList"></div>

                    <div class="estimate-box">
                        <span class="lab">Estimasi total nilai setoran</span>
                        <span class="val" id="verifEstimasi">Rp 0</span>
                    </div>

                    <span class="field-sub">Sesuaikan jenis dan jumlah dengan hasil pengecekan ulang jika berbeda.</span>
                </div>

                <div class="form-grid">
                    <!-- ALASAN PENOLAKAN -->
                    <div class="form-group form-group--full reject-reason" id="verifAlasanBlock">
                        <label for="verifAlasan">Catatan Penolakan</label>
                        <textarea id="verifAlasan" rows="3" placeholder="Contoh: foto tidak jelas, jumlah tidak sesuai foto, kategori tidak cocok..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-foot" id="verifFootActions">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalVerifikasi')">Tutup</button>
                <button class="btn btn--danger" type="button" id="btnTolakSetoran" onclick="tolakSetoran()">Tolak</button>
                <button class="btn btn--primary" type="submit" id="btnSetujuiSetoran">Setujui &amp; Proses Saldo</button>
            </div>
        </form>
    </div>
</div>


<!-- ========================================================= -->
<!-- MODAL TAMBAH SETORAN -->
<!-- ========================================================= -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal-box modal-box--wide">
        <div class="modal-head">
            <h3 class="modal-title">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Setoran
            </h3>
            <button class="modal-close" type="button" onclick="tutupModal('modalTambah')">
                &times;
            </button>
        </div>

        <form id="formTambahSetoran" onsubmit="return simpanSetoranBaru(event)">
            <div class="modal-body">
                <div class="form-grid">
                    <!-- WARGA -->
                    <div class="form-group form-group--full">
                        <label for="cariWarga">Nama Warga</label>
                        <div class="combo" id="comboWarga">
                            <input type="text" id="cariWarga" class="combo-input" placeholder="Ketik nama atau NIK warga..." autocomplete="off">
                            <div class="combo-list" id="listWarga"></div>
                        </div>

                        <select id="tambahWarga" class="combo-hidden" tabindex="-1" aria-hidden="true">
                            <option value="">-- Pilih Warga --</option>
                            @foreach ($wargaList as $w)
                                <option value="{{ $w->id_warga }}">
                                    {{ $w->nama }} (NIK: {{ $w->nik }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- TANGGAL -->
                    <div class="form-group form-group--full">
                        <label for="tambahTanggal">Tanggal Setoran</label>
                        <input type="date" id="tambahTanggal" required disabled>
                    </div>

                    <!-- RINCIAN SAMPAH -->
                    <div class="form-group form-group--full">
                        <div class="items-section">
                            <div class="items-head">
                                <div class="items-title">
                                    Rincian Sampah
                                    <span class="items-count" id="tambahItemCount">0 jenis</span>
                                </div>
                                <button type="button" class="items-add" id="tambahAddItem">+ Tambah Jenis</button>
                            </div>

                            <div class="item-list" id="tambahItemList"></div>

                            <div class="estimate-box">
                                <span class="lab">Estimasi total nilai</span>
                                <span class="val" id="tambahEstimasi">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- CATATAN -->
                    <div class="form-group form-group--full">
                        <label for="tambahCatatan">Catatan (opsional)</label>
                        <textarea id="tambahCatatan" rows="2" placeholder="Contoh: setoran langsung di titik kumpul"></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalTambah')">Batal</button>
                <button class="btn btn--primary" type="submit">Simpan &amp; Proses Saldo</button>
            </div>
        </form>
    </div>
</div>
