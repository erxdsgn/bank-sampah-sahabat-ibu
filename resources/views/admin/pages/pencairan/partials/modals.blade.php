    <!-- TOAST -->
    <div class="toast-wrap" id="toastWrap"></div>

    <!-- MODAL INVOICE CARD -->
    <div class="modal-overlay" id="modalInvoice">
        <div class="modal-box invoice-modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    Nota Pencairan Saldo
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalInvoice')">&times;</button>
            </div>

            <div class="modal-body">
                <div class="invoice-paper">
                    <div class="inv-top">
                        <div>
                            <span class="inv-company-name">Bank Sampah Sahabat Ibu</span>
                            <span class="inv-company-addr">Tegal Besar, Kaliwates, Jember</span>
                        </div>
                        <div class="inv-title-group">
                            <span class="mono inv-number" id="invId">#INV-000</span>
                            <span class="mono inv-text-muted" id="invTanggal">-</span>
                        </div>
                    </div>

                    <div class="inv-divider"></div>

                    <div class="inv-grid-compact">
                        <div>
                            <span class="inv-label">Warga Penerima</span>
                            <strong class="inv-text-dark" id="invNama" style="display: block;">-</strong>
                            <span class="mono inv-text-muted" id="invNik"
                                style="display: block; margin-top: 2px;">-</span>
                        </div>
                        <div style="text-align: right;">
                            <span class="inv-label">Status Transaksi</span>
                            <div id="invBadgeStatus" style="margin-top: 2px;">-</div>
                        </div>
                    </div>

                    <div class="inv-compact-box">
                        <div class="inv-compact-col">
                            <span class="inv-label">Tujuan Pencairan</span>
                            <div class="inv-text-dark" id="invBankInfo">-</div>
                            <div class="mono inv-text-muted" id="invRekening">-</div>
                        </div>
                        <div class="inv-compact-col inv-compact-right">
                            <span class="inv-label">Jumlah Dicairkan</span>
                            <div class="inv-total-amount" id="invNominal">Rp 0</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-foot">
                <!-- Tombol Kirim WhatsApp Ditambahkan Di Sini -->
                <button class="btn btn--whatsapp" id="btnKirimWa" type="button" onclick="kirimInvoiceWhatsApp()">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path
                            d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z">
                        </path>
                    </svg>
                    Kirim WA
                </button>
                <button class="btn btn--primary" type="button" onclick="tutupModal('modalInvoice')">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH PENCAIRAN -->
    <div class="modal-overlay" id="modalTambah">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Permohonan Pencairan
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalTambah')">&times;</button>
            </div>

            <form id="formTambahPencairan" onsubmit="return simpanTambah(event)">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group form-group--full">
                            <label for="cariWarga">Nama Warga</label>
                            <div class="combo" id="comboWarga">
                                <input type="text" id="cariWarga" class="combo-input"
                                    placeholder="Ketik nama atau NIK warga..." autocomplete="off">
                                <div class="combo-list" id="listWarga"></div>
                            </div>

                            <select id="tambahWarga" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="">-- Pilih Warga --</option>
                                @foreach ($warga as $w)
                                    <option value="{{ $w->id_warga }}" data-saldo="{{ $w->saldo ?? 0 }}"
                                        data-bank="{{ $w->nama_bank_ewallet ?? '' }}"
                                        data-rekening="{{ $w->nomor_rekening ?? '' }}"
                                        data-status-rekening="{{ $w->status_rekening ?? '' }}">
                                        {{ $w->nama }} (NIK: {{ $w->nik }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <span class="detail-label">Saldo Tersedia</span>
                            <span class="detail-value saldo" id="tambahSaldoInfo">-</span>
                        </div>

                        <div class="form-group">
                            <span class="detail-label">Rekening / E-Wallet</span>
                            <span class="detail-value" id="tambahRekeningInfo">-</span>
                        </div>

                        <div class="form-group">
                            <label for="tambahJumlah">Jumlah Penarikan (Rp)</label>
                            <input type="number" id="tambahJumlah" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="cari_tambahMetode">Metode Pencairan</label>
                            <div class="combo combo--arrow" id="combo_tambahMetode">
                                <input type="text" id="cari_tambahMetode" class="combo-input" autocomplete="off">
                                <div class="combo-list" id="list_tambahMetode"></div>
                            </div>
                            <select id="tambahMetode" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="">-- Pilih Metode --</option>
                                <option value="tunai">Tunai</option>
                                <option value="transfer">Transfer Bank</option>
                            </select>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="tambahTanggal">Tanggal Pencairan</label>
                            <input type="date" id="tambahTanggal" readonly required>
                        </div>
                    </div>
                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalTambah')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan Permohonan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL PROSES PENCAIRAN -->
    <div class="modal-overlay" id="modalProses">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                    </svg>
                    Proses Pencairan Saldo
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalProses')">&times;</button>
            </div>

            <form id="formProses" onsubmit="return simpanProses(event)">
                <div class="modal-body">
                    <input type="hidden" id="editId">
                    <div class="form-grid">
                        <div class="form-group form-group--full">
                            <label>Nama Warga</label>
                            <input type="text" id="editNama" disabled>
                        </div>

                        <div class="form-group form-group--full">
                            <label>Nominal Penarikan</label>
                            <input type="text" id="editNominal" disabled>
                        </div>

                        <div class="form-group">
                            <label>Bank / E-Wallet Tujuan</label>
                            <input type="text" id="editBank" disabled>
                        </div>

                        <div class="form-group">
                            <label>No. Rekening / No. HP</label>
                            <input type="text" id="editNoRekening" disabled>
                        </div>

                        <div class="form-group form-group--full">
                            <label>Status Rekening</label>
                            <input type="text" id="editStatusRekening" disabled>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="cari_editStatus">Status Pencairan</label>
                            <div class="combo combo--arrow" id="combo_editStatus">
                                <input type="text" id="cari_editStatus" class="combo-input" autocomplete="off">
                                <div class="combo-list" id="list_editStatus"></div>
                            </div>
                            <select id="editStatus" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="menunggu">Menunggu</option>
                                <option value="selesai">Selesai (Uang Diserahkan)</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalProses')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

