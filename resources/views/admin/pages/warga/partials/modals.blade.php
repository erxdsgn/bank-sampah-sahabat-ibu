    <!-- MODAL: DETAIL WARGA -->
    <div class="modal-overlay" id="modalDetail">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                        <circle cx="12" cy="12" r="2.5"></circle>
                    </svg>
                    Detail Warga
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalDetail')">&times;</button>
            </div>

            <div class="modal-body">
                <div class="detail-avatar-row">
                    <div class="detail-avatar" id="detailAvatar">-</div>
                    <div>
                        <div class="detail-nama" id="detailNama">-</div>
                        <div style="display: flex; gap: 8px; margin-top: 3px; align-items: center;">
                            <span class="kategori-tag" id="detailNik"
                                style="font-family: monospace; font-size: 11px;">-</span>
                        </div>
                    </div>
                </div>

                <div class="detail-grid">
                    <!-- KELOMPOK DALAM BENTUK TEKS BIASA -->
                    <div class="detail-item">
                        <span class="detail-label">Kelompok Domisili</span>
                        <span class="detail-value" id="detailKelompok">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">No. HP</span>
                        <span class="detail-value" id="detailHp">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Anggota Keluarga</span>
                        <span class="detail-value" id="detailKeluarga">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Saldo</span>
                        <span class="detail-value saldo-text" id="detailSaldo">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Tanggal Daftar</span>
                        <span class="detail-value" id="detailTanggal">-</span>
                    </div>

                    <div class="detail-item detail-item--full">
                        <span class="detail-label">Alamat</span>
                        <span class="detail-value" id="detailAlamat">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Bank / E-Wallet</span>
                        <span class="detail-value" id="detailBank">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">No. Rekening / HP E-Wallet</span>
                        <span class="detail-value mono" id="detailNoRekening">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Nama Pemilik Rekening</span>
                        <span class="detail-value" id="detailPemilikRekening">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Status Rekening</span>
                        <span class="detail-value" id="detailStatusRekening">-</span>
                    </div>
                </div>
            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalDetail')">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL: TAMBAH WARGA -->
    <div class="modal-overlay" id="modalTambah">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Warga
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalTambah')">&times;</button>
            </div>

            <form id="formTambahWarga" onsubmit="return simpanTambahWarga(event)">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="tambahNik">NIK</label>
                            <input type="text" id="tambahNik" maxlength="16" required
                                placeholder="Masukkan NIK 16 digit">
                        </div>

                        <div class="form-group">
                            <label for="tambahNama">Nama Lengkap</label>
                            <input type="text" id="tambahNama" required placeholder="Nama lengkap warga">
                        </div>

                        <div class="form-group">
                            <label for="tambahEmail">Email Login (Wajib)</label>
                            <input type="email" id="tambahEmail" required placeholder="contoh@domain.com">
                        </div>

                        <div class="form-group">
                            <label for="tambahPassword">Password Login</label>
                            <input type="password" id="tambahPassword" minlength="8" required
                                placeholder="Minimal 8 karakter">
                        </div>

                        <div class="form-group">
                            <label for="tambahHp">No. HP</label>
                            <input type="text" id="tambahHp" maxlength="14" required placeholder="08xxxxxxxxxx">
                        </div>

                        <div class="form-group">
                            <label for="tambahKelompok">Kelompok Warga</label>
                            <select id="tambahKelompok" required>
                                <option value="">Pilih Kelompok...</option>
                                <option value="A">A (Perumahan)</option>
                                <option value="B"> B Luar Perumahan</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tambahKeluarga">Anggota Keluarga</label>
                            <input type="number" id="tambahKeluarga" min="1" value="1">
                        </div>

                        <div class="form-group">
                            <label for="tambahBankInput">Bank / E-Wallet</label>
                            <div class="combo" id="comboBankTambah">
                                <input type="text" id="tambahBankInput" class="combo-input"
                                    placeholder="Ketik atau pilih bank/e-wallet..." maxlength="50" autocomplete="off">
                                <div class="combo-list" id="listBankTambah"></div>
                            </div>
                            <input type="hidden" id="tambahBank">
                        </div>

                        <div class="form-group">
                            <label for="tambahNoRekening">No. Rekening / No. HP E-Wallet</label>
                            <input type="text" id="tambahNoRekening" maxlength="30"
                                placeholder="Nomor rekening / e-wallet">
                        </div>

                        <div class="form-group form-group--full">
                            <label for="tambahPemilikRekening">Nama Pemilik Rekening</label>
                            <input type="text" id="tambahPemilikRekening"
                                placeholder="Nama pada akun rekening / e-wallet">
                        </div>

                        <div class="form-group form-group--full">
                            <label for="tambahAlamat">Alamat</label>
                            <textarea id="tambahAlamat" rows="3" required placeholder="Alamat domisili lengkap..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalTambah')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan Warga</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT WARGA -->
    <div class="modal-overlay" id="modalEdit">
        <div class="modal-box modal-box--sm">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                    </svg>
                    Status Rekening
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalEdit')">&times;</button>
            </div>

            <form id="formEditWarga" onsubmit="return simpanWarga(event)">
                <div class="modal-body">
                    <input type="hidden" id="editId">
                    <input type="hidden" id="editStatusRekening" value="unverified">

                    <div class="edit-ringkasan" id="editRingkasan"></div>

                    <div class="form-group" style="margin-top: 16px;">
                        <label>Status Rekening</label>
                        <div class="seg" id="segStatusRekening">
                            <button type="button" class="seg-btn" data-value="unverified">Belum Verifikasi</button>
                            <button type="button" class="seg-btn" data-value="verified">Terverifikasi</button>
                        </div>
                    </div>
                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalEdit')">Batal</button>
                    <button class="btn btn--primary" type="submit" id="editSubmitBtn">Simpan Status</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: KONFIRMASI HAPUS -->
    <div class="modal-overlay" id="modalHapus">
        <div class="modal-box modal-box--sm">
            <div class="modal-body modal-body--center">
                <div class="confirm-icon">
                    <svg viewBox="0 0 24 24">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                        <path d="M10 11v6M14 11v6"></path>
                    </svg>
                </div>

                <h3 class="confirm-title">Hapus Data Warga?</h3>

                <p class="confirm-text">
                    Anda akan menghapus data <strong id="hapusNama">-</strong>
                    (NIK: <span class="mono" id="hapusNik">-</span>).
                    Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="modal-foot modal-foot--center">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHapus')">Batal</button>
                <button class="btn btn--danger" type="button" onclick="konfirmasiHapus()">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <!-- MODAL: LEADERBOARD POIN (BARU) -->
    <div class="modal-overlay" id="modalLeaderboard">
        <div class="modal-box" id="modalLeaderboardContent">
            <!-- Konten dimuat via AJAX -->
        </div>
    </div>

