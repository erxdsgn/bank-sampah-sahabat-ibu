{{-- VIEW KEDUA: FORM FULL PAGE --}}
    <div class="card form-full-view" id="viewForm" style="display: none;">
        <div class="form-full-header">
            <div>
                <h2 class="card-title" id="formTitleText">Formulir Konten Artikel & Edukasi</h2>
                <p class="card-sub">Tulis materi secara profesional dan pilih gaya tampilan header untuk aplikasi warga.</p>
            </div>
            <button class="btn btn--ghost" type="button" onclick="tutupFormFull()">
                ← Kembali ke Daftar
            </button>
        </div>

        <form id="formArtikel" onsubmit="return false;" class="form-full-body">
            <input type="hidden" id="artikelId">
            <input type="hidden" id="modeMedia" name="mode_media" value="gambar">

            <div class="form-grid-columns">
                <div class="form-group form-group--full">
                    <label for="artikelJudul">Judul Konten <span class="text-danger">*</span></label>
                    <input type="text" id="artikelJudul" class="form-control" placeholder="Contoh: Cara Memilah Sampah dari Rumah" oninput="updateFormMediaPreview()" required>
                    <span class="form-error" id="err_judul"></span>
                </div>

                <div class="form-group">
                    <label for="artikelJenis">Jenis / Kategori Konten <span class="text-danger">*</span></label>
                    <select id="artikelJenis" class="form-control" required>
                        <option value="">-- Pilih Jenis Konten --</option>
                        <option value="edukasi">Edukasi Sampah</option>
                        <option value="artikel">Artikel</option>
                        <option value="event">Event</option>
                    </select>
                    <span class="form-error" id="err_jenis"></span>
                </div>

                <div class="form-group">
                    <label for="artikelTanggal">Tanggal Publikasi</label>
                    <input type="date" id="artikelTanggal" class="form-control">
                    <span class="field-hint">Kosongkan jika ingin menjadikannya sebagai draft.</span>
                    <span class="form-error" id="err_tanggal"></span>
                </div>

                {{-- PILIHAN MODE TAMPILAN MEDIA --}}
                <div class="form-group form-group--full">
                    <label>Gaya Tampilan Header / Media <span class="text-danger">*</span></label>

                    <div class="media-mode-grid" id="mediaModeGrid">
                        <button type="button" class="media-mode-card active" data-mode="gambar" onclick="pilihModeMedia('gambar')">
                            <div class="media-mode-icon media-mode-icon--img">🖼️</div>
                            <div class="media-mode-info">
                                <strong>Upload / URL Gambar</strong>
                                <span>Gunakan foto atau poster</span>
                            </div>
                        </button>

                        <button type="button" class="media-mode-card" data-mode="banner_edukasi" onclick="pilihModeMedia('banner_edukasi')">
                            <div class="media-mode-icon banner-grad--edukasi">🌱</div>
                            <div class="media-mode-info">
                                <strong>Banner Warna Edukasi</strong>
                                <span>Latar hijau alami</span>
                            </div>
                        </button>

                        <button type="button" class="media-mode-card" data-mode="banner_event" onclick="pilihModeMedia('banner_event')">
                            <div class="media-mode-icon banner-grad--event">📅</div>
                            <div class="media-mode-info">
                                <strong>Banner Warna Event</strong>
                                <span>Latar amber hangat</span>
                            </div>
                        </button>

                        <button type="button" class="media-mode-card" data-mode="banner_artikel" onclick="pilihModeMedia('banner_artikel')">
                            <div class="media-mode-icon banner-grad--artikel">📰</div>
                            <div class="media-mode-info">
                                <strong>Banner Warna Artikel</strong>
                                <span>Latar teal sejuk</span>
                            </div>
                        </button>

                        <button type="button" class="media-mode-card" data-mode="tanpa_gambar" onclick="pilihModeMedia('tanpa_gambar')">
                            <div class="media-mode-icon media-mode-icon--text">📝</div>
                            <div class="media-mode-info">
                                <strong>Teks Saja (Tanpa Gambar)</strong>
                                <span>Fokus teks, tanpa banner</span>
                            </div>
                        </button>
                    </div>

                    {{-- PANEL 1: UPLOAD GAMBAR (Muncul jika mode = gambar) --}}
                    <div id="panelUploadGambar" class="media-option-panel">
                        <input type="file" id="artikelGambar" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewGambar(this)">
                        <input type="hidden" id="artikelGambarUrl" name="gambar_url">

                        <div class="field-hint" style="margin-top: 8px; line-height: 1.5;">
                            <span style="display: inline-block; font-weight: 600; color: #1E9E66; margin-bottom: 2px;">📐 Panduan Ukuran Gambar:</span><br>
                            • Format: <strong>JPG, PNG, atau WEBP</strong><br>
                            • Rekomendasi Resolusi: <strong>1200 x 630 piksel</strong> (Rasio 16:9 atau Landscape)<br>
                            • Ukuran Maksimal File: <strong>2 MB</strong>
                        </div>

                        <span class="form-error" id="err_gambar"></span>

                        <div class="gambar-preview" id="gambarPreviewWrap">
                            <img id="gambarPreviewImg" src="" alt="Preview">
                        </div>
                        <div id="gambarLamaInfo"></div>
                    </div>

                    {{-- PANEL 2: PRATINJAU BANNER WARNA / TEKS SAJA (Muncul jika bukan mode gambar) --}}
                    <div id="panelBannerPreview" class="media-option-panel" style="display: none;">
                        <div class="form-live-card-preview" id="formLiveCardPreview"></div>
                    </div>
                </div>

                {{-- CKEDITOR CONTAINER --}}
                <div class="form-group form-group--full">
                    <div class="editor-label-wrap">
                        <label for="editorKonten">Isi Konten / Materi <span class="text-danger">*</span></label>
                    </div>
                    <textarea id="editorKonten" class="form-control"></textarea>
                    <span class="form-error" id="err_konten"></span>
                </div>
            </div>

            <div class="form-full-footer">
                <button class="btn btn--ghost" type="button" onclick="tutupFormFull()">Batal</button>
                <button type="button" class="btn btn--outline" onclick="simpanArtikel('draft')">Simpan sebagai Draft</button>
                <button class="btn btn--primary" type="button" onclick="simpanArtikel('publish')">Publikasikan Konten</button>
            </div>
        </form>
    </div>
