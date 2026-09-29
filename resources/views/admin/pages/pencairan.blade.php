@extends('admin.layouts.app')

@section('title', 'Pencairan Saldo')
@section('active', 'pencairan')
@section('crumbs', 'Transaksi Sampah | Pencairan Saldo')

@section('content')

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">TRANSAKSI SAMPAH</span>
            <h1 class="hero-title">Pencairan <span class="accent">Saldo</span></h1>
            <p class="hero-sub">Kelola permohonan penarikan saldo tabungan warga.</p>
        </div>

        <div class="hero-actions">
            <button class="btn btn--primary" type="button" onclick="bukaModal('modalTambah')">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"></path>
                </svg>
                Tambah Pencairan
            </button>
        </div>
    </section>

    <section class="card">
        <!-- SEARCH -->
        <div class="table-toolbar">
            <div class="table-search">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchPencairan" placeholder="Cari ID, NIK, nama, nomor HP, atau alamat..."
                    autocomplete="off">
            </div>

            <button class="btn btn--ghost" type="button" onclick="window.location.reload()">
                <svg viewBox="0 0 24 24">
                    <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                    <path d="M21 3v5h-5"></path>
                </svg>
                Refresh
            </button>
        </div>

        <!-- TABLE -->
        <div class="table-responsive">
            <table class="data-table" id="pencairanTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Warga</th>
                        <th>Kontak</th>
                        <th>Rekening / Metode</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pencairan as $item)
                        <tr data-row-pencairan>
                            <td><span class="mono">#{{ $item->id_pencairan }}</span></td>
                            <td>
                                <span class="mono">
                                    {{ $item->tanggal_pencairan ? \Carbon\Carbon::parse($item->tanggal_pencairan)->format('d/m/Y') : '-' }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $item->warga->nama ?? 'Anonim' }}</strong>
                                <div class="mono">NIK: {{ $item->warga->nik ?? '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $item->warga->no_hp ?? '-' }}</div>
                                <div class="muted-text" style="font-size: 11px;" title="{{ $item->warga->alamat ?? '-' }}">
                                    {{ \Illuminate\Support\Str::limit($item->warga->alamat ?? '-', 22) }}
                                </div>
                            </td>
                            <td>
                                @if ($item->warga && $item->warga->nama_bank_ewallet)
                                    <div class="bank-info">
                                        <div class="bank-main">
                                            <span
                                                class="status-badge status-badge--icon {{ $item->warga->status_rekening === 'verified' ? 'status-badge--approved' : 'status-badge--pending' }}"
                                                title="{{ $item->warga->status_rekening === 'verified' ? 'Terverifikasi' : 'Belum Verifikasi' }}"
                                                aria-label="{{ $item->warga->status_rekening === 'verified' ? 'Terverifikasi' : 'Belum Verifikasi' }}">
                                                @if ($item->warga->status_rekening === 'verified')
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M20 6 9 17l-5-5"></path>
                                                    </svg>
                                                @else
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <circle cx="12" cy="12" r="9"></circle>
                                                        <path d="M12 7v5l3 2"></path>
                                                    </svg>
                                                @endif
                                            </span>
                                            <strong title="{{ $item->warga->nama_bank_ewallet }}">{{ $item->warga->nama_bank_ewallet }}</strong>
                                        </div>
                                        <div class="mono rekening-number" title="{{ $item->warga->nomor_rekening ?? '-' }}">{{ $item->warga->nomor_rekening ?? '-' }}</div>
                                    </div>
                                @else
                                    <span class="muted-text">Tunai / Lainnya</span>
                                @endif
                            </td>
                            <td>
                                <strong class="saldo">
                                    Rp {{ number_format($item->jumlah ?? 0, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>
                                @if ($item->status == 'menunggu')
                                    <span class="badge badge--warning">Menunggu</span>
                                @elseif($item->status == 'selesai')
                                    <span class="badge badge--success">Selesai</span>
                                @else
                                    <span class="badge badge--danger">Ditolak</span>
                                @endif
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="icon-btn" title="Lihat Invoice Ringkas" type="button"
                                        onclick='bukaInvoiceCard(@json($item))'>
                                        <svg viewBox="0 0 24 24">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>

                                    <button class="icon-btn"
                                        title="{{ $item->status === 'menunggu' ? 'Ubah Status Pencairan' : 'Status sudah dikunci' }}"
                                        type="button" onclick='prosesPencairan(@json($item))'
                                        {{ $item->status !== 'menunggu' ? 'disabled' : '' }}>
                                        <svg viewBox="0 0 24 24">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-table">
                                <div class="empty-icon">📋</div>
                                <strong>Belum Ada Permohonan Pencairan</strong>
                                <p>Belum ada warga yang mengajukan pencairan saldo.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="empty-table" id="pencairanKosongCari" style="display: none;">
                <div class="empty-icon">🔍</div>
                <strong>Data Tidak Ditemukan</strong>
                <p>Tidak ada permohonan yang cocok dengan pencarian ini.</p>
            </div>
        </div>

        <div class="table-footer">
            <div class="table-info" id="tableInfoPagination">Menampilkan data...</div>

            <div class="table-pagination-controls">
                <div class="per-page">
                    <label for="cari_perPageSelect">Permohonan per halaman:</label>
                    <div class="combo combo--up combo--arrow" id="combo_perPageSelect">
                        <input type="text" id="cari_perPageSelect" class="combo-input" placeholder=""
                            autocomplete="off">
                        <div class="combo-list" id="list_perPageSelect"></div>
                    </div>
                    <select id="perPageSelect" class="combo-hidden" tabindex="-1" aria-hidden="true">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="pagination-buttons" id="paginationButtons"></div>
            </div>
        </div>
    </section>

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
                            <input type="date" id="tambahTanggal" required>
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

    <style>
        :root {
            --pencairan-page: #f5f7fb;
            --pencairan-surface: #ffffff;
            --pencairan-surface-secondary: #f8fafc;
            --pencairan-text: #1f2937;
            --pencairan-text-secondary: #6b7280;
            --pencairan-text-muted: #9ca3af;
            --pencairan-border: #e5e7eb;
            --pencairan-table-head: #f8fafc;
            --pencairan-table-hover: #fafafa;
            --pencairan-input: #ffffff;
            --pencairan-shadow: 0 8px 25px rgba(15, 23, 42, .06);
        }

        [data-theme="dark"] {
            --pencairan-page: #0b1220;
            --pencairan-surface: #151d2f;
            --pencairan-surface-secondary: #1b2438;
            --pencairan-text: #f1f5f9;
            --pencairan-text-secondary: #aab6c8;
            --pencairan-text-muted: #748198;
            --pencairan-border: #29364d;
            --pencairan-table-head: #111a2c;
            --pencairan-table-hover: #202b40;
            --pencairan-input: #111a2c;
            --pencairan-shadow: 0 8px 25px rgba(0, 0, 0, .25);
        }

        .card {
            background: var(--pencairan-surface);
            border: 1px solid var(--pencairan-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--pencairan-shadow);
        }

        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 16px 24px;
        }

        .table-search {
            width: 400px;
            max-width: 100%;
            position: relative;
            color: var(--pencairan-text-muted);
        }

        .table-search svg {
            position: absolute;
            width: 18px;
            height: 18px;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            pointer-events: none;
        }

        .table-search input {
            width: 100%;
            height: 40px;
            padding: 0 14px 0 40px;
            border: 1px solid var(--pencairan-border);
            border-radius: 8px;
            outline: none;
            box-sizing: border-box;
            background: var(--pencairan-input);
            color: var(--pencairan-text);
            font-family: inherit;
            font-size: 13px;
            transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
        }

        .table-search input::placeholder {
            color: var(--pencairan-text-muted);
        }

        .table-search input:focus {
            border-color: var(--primary, #16a34a);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        .data-table th {
            background: var(--pencairan-table-head);
            color: var(--pencairan-text-secondary);
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 12px 12px;
            text-align: left;
            border-top: 1px solid var(--pencairan-border);
            border-bottom: 1px solid var(--pencairan-border);
            white-space: nowrap;
        }

        .data-table td {
            padding: 12px 12px;
            border-bottom: 1px solid var(--pencairan-border);
            font-size: 12.5px;
            color: var(--pencairan-text);
            vertical-align: middle;
            background: var(--pencairan-surface);
            transition: background .15s ease, color .15s ease;
        }

        .data-table tbody tr:hover td {
            background: var(--pencairan-table-hover);
        }

        .data-table strong {
            color: var(--pencairan-text);
        }

        .mono {
            font-family: monospace;
            font-size: 11.5px;
            color: var(--pencairan-text-secondary);
        }

        /* Perbaikan Layout Kolom Bank & Nomor Rekening agar Sejajar Rapi */
        .bank-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 3px;
            line-height: 1.35;
            min-width: 0;
        }

        .bank-main {
            display: flex;
            align-items: center;
            gap: 7px;
            min-width: 0;
            max-width: 100%;
        }

        .bank-main > strong {
            display: block;
            max-width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .bank-info .rekening-number {
            display: block;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding-left: 27px; /* Menjajarkan nomor rekening/HP agar rapi di bawah teks nama bank */
        }

        .muted-text {
            color: var(--pencairan-text-muted);
        }

        .saldo {
            color: #22c55e !important;
            white-space: nowrap;
        }

        /* Styles untuk Icon Badge Status Rekening */
        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            flex-shrink: 0;
        }

        .status-badge--icon {
            width: 20px;
            height: 20px;
        }

        .status-badge--icon svg {
            width: 12px;
            height: 12px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .status-badge--approved {
            background: rgba(34, 197, 94, .13);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, .25);
        }

        .status-badge--pending {
            background: rgba(245, 158, 11, .13);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, .25);
        }

        .table-actions {
            display: flex;
            gap: 6px;
        }

        .icon-btn {
            width: 32px;
            height: 32px;
            border: 1px solid var(--pencairan-border);
            background: var(--pencairan-surface-secondary);
            color: var(--pencairan-text-secondary);
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .2s ease, border-color .2s ease, color .2s ease, transform .15s ease;
        }

        .icon-btn svg {
            width: 15px;
            height: 15px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .icon-btn:hover:not(:disabled) {
            background: rgba(34, 197, 94, .10);
            border-color: rgba(34, 197, 94, .30);
            color: #22c55e;
            transform: translateY(-1px);
        }

        .icon-btn:disabled {
            opacity: .35;
            cursor: not-allowed;
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            flex-wrap: wrap;
            background: var(--pencairan-surface);
        }

        .table-pagination-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .per-page {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .per-page label {
            font-size: 13px;
            color: var(--pencairan-text-secondary);
        }

        .per-page .combo {
            width: 84px;
        }

        .per-page .combo-input {
            padding-top: 6px;
            padding-bottom: 6px;
        }

        .pagination-buttons {
            display: flex;
            gap: 4px;
            align-items: center;
        }

        .pagination-buttons .btn {
            padding: 6px 12px;
            min-width: 32px;
        }

        .pagination-buttons .btn:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .pagination-dots {
            padding: 0 4px;
            color: var(--pencairan-text-secondary);
        }

        .table-info {
            font-size: 13px;
            color: var(--pencairan-text-secondary);
        }

        .table-info strong {
            color: var(--pencairan-text);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge--warning {
            background: rgba(245, 158, 11, .13);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, .20);
        }

        .badge--success {
            background: rgba(34, 197, 94, .13);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, .20);
        }

        .badge--danger {
            background: rgba(239, 68, 68, .13);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, .20);
        }

        .empty-table {
            text-align: center;
            padding: 50px 40px !important;
            background: var(--pencairan-surface) !important;
        }

        .empty-icon {
            font-size: 30px;
            margin-bottom: 10px;
            opacity: .8;
        }

        .empty-table strong {
            display: block;
            color: var(--pencairan-text);
            font-size: 14px;
        }

        .empty-table p {
            margin: 5px 0 0;
            color: var(--pencairan-text-secondary);
            font-size: 13px;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, .68);
            backdrop-filter: blur(3px);
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 1000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            background: var(--pencairan-surface);
            border: 1px solid var(--pencairan-border);
            border-radius: 14px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, .30);
            color: var(--pencairan-text);
        }

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid var(--pencairan-border);
            background: var(--pencairan-surface);
        }

        .modal-title {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: var(--pencairan-text);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-title svg {
            width: 17px;
            height: 17px;
            fill: none;
            stroke: #22c55e;
            stroke-width: 1.8;
        }

        .modal-close {
            width: 28px;
            height: 28px;
            border: none;
            background: transparent;
            border-radius: 6px;
            font-size: 18px;
            line-height: 1;
            color: var(--pencairan-text-muted);
            cursor: pointer;
            transition: background .2s ease, color .2s ease;
        }

        .modal-close:hover {
            background: var(--pencairan-table-hover);
            color: var(--pencairan-text);
        }

        .modal-body {
            padding: 18px 22px;
            background: var(--pencairan-surface);
        }

        .modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 22px;
            border-top: 1px solid var(--pencairan-border);
            background: var(--pencairan-surface);
        }

        .invoice-modal-box {
            max-width: 480px;
        }

        .invoice-paper {
            background: var(--pencairan-surface-secondary);
            border: 1px solid var(--pencairan-border);
            border-radius: 10px;
            padding: 16px;
            box-sizing: border-box;
        }

        .inv-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .inv-company-name {
            font-weight: 700;
            font-size: 13px;
            color: var(--pencairan-text);
            display: block;
        }

        .inv-company-addr {
            font-size: 11px;
            color: var(--pencairan-text-muted);
            display: block;
        }

        .inv-title-group {
            text-align: right;
        }

        .inv-number {
            font-weight: 700;
            color: #22c55e;
            font-size: 12.5px;
            display: block;
        }

        .inv-divider {
            height: 1px;
            background: var(--pencairan-border);
            margin: 12px 0;
        }

        .inv-grid-compact {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: center;
        }

        .inv-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--pencairan-text-muted);
            margin-bottom: 2px;
            display: block;
        }

        .inv-text-dark {
            color: var(--pencairan-text);
            font-size: 12.5px;
            font-weight: 600;
        }

        .inv-compact-box {
            margin-top: 12px;
            background: var(--pencairan-surface);
            border: 1px solid var(--pencairan-border);
            border-radius: 8px;
            padding: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .inv-compact-col {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .inv-compact-right {
            text-align: right;
        }

        .inv-total-amount {
            font-size: 15px;
            font-weight: 800;
            color: #22c55e;
        }

        /* Styling tambahan untuk tombol WhatsApp */
        .btn--whatsapp {
            background-color: #25d366;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .btn--whatsapp:hover {
            background-color: #22bf5b;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group--full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 700;
            color: var(--pencairan-text-secondary);
        }

        .form-group input,
        .form-group select {
            border: 1px solid var(--pencairan-border);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            width: 100%;
            background: var(--pencairan-input);
            color: var(--pencairan-text);
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .form-group input::placeholder {
            color: var(--pencairan-text-muted);
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
        }

        .form-group input:disabled {
            background: var(--pencairan-surface-secondary);
            color: var(--pencairan-text-secondary);
            cursor: not-allowed;
        }

        .form-group select option {
            background: var(--pencairan-surface);
            color: var(--pencairan-text);
        }

        .detail-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--pencairan-text-muted);
            display: block;
        }

        .detail-value {
            font-size: 13.5px;
            color: var(--pencairan-text);
            font-weight: 600;
        }

        .combo {
            position: relative;
        }

        .combo-hidden {
            display: none !important;
        }

        .combo-input {
            width: 100%;
            border: 1px solid var(--pencairan-border);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            background: var(--pencairan-input);
            color: var(--pencairan-text);
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .combo-input::placeholder {
            color: var(--pencairan-text-muted);
        }

        .combo-input:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
        }

        .combo-list {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            max-height: 200px;
            overflow-y: auto;
            background: var(--pencairan-surface);
            border: 1px solid var(--pencairan-border);
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .22);
            z-index: 20;
        }

        .combo-list.show {
            display: block;
        }

        .combo-item {
            padding: 8px 12px;
            font-size: 13px;
            color: var(--pencairan-text);
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
        }

        .combo-item:hover,
        .combo-item.is-active {
            background: var(--pencairan-table-hover);
            color: #22c55e;
        }

        .combo-empty {
            padding: 10px;
            font-size: 12px;
            color: var(--pencairan-text-muted);
            text-align: center;
        }

        .toast-wrap {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .toast {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 300px;
            max-width: 380px;
            background: var(--pencairan-surface);
            border: 1px solid var(--pencairan-border);
            border-left: 4px solid #22c55e;
            border-radius: 10px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .25);
            padding: 14px 16px;
            pointer-events: auto;
        }

        .toast.toast--error {
            border-left-color: #ef4444;
        }

        .toast-icon {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: rgba(34, 197, 94, .13);
            color: #22c55e;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toast.toast--error .toast-icon {
            background: rgba(239, 68, 68, .13);
            color: #ef4444;
        }

        .toast-icon svg {
            width: 13px;
            height: 13px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.4;
        }

        .toast-body {
            flex: 1;
        }

        .toast-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--pencairan-text);
            margin: 0 0 2px;
        }

        .toast-text {
            font-size: 12.5px;
            color: var(--pencairan-text-secondary);
            margin: 0;
            line-height: 1.5;
        }

        .toast-close {
            border: none;
            background: transparent;
            color: var(--pencairan-text-muted);
            font-size: 16px;
            line-height: 1;
            cursor: pointer;
            padding: 0;
            flex-shrink: 0;
        }

        .toast-close:hover {
            color: var(--pencairan-text);
        }

        @media (max-width: 760px) {
            .table-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .table-search {
                width: 100%;
            }

            .table-toolbar .btn {
                width: 100%;
                justify-content: center;
            }

            .toast {
                min-width: 0;
                width: calc(100vw - 40px);
            }

            .toast-wrap {
                left: 20px;
                right: 20px;
            }
        }

        /* =========================================================
           MODAL & COMBOBOX — konsisten dengan halaman lain
           (header/footer diam, hanya isi form yang scroll)
           ========================================================= */
        .modal-box {
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-box > form {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }

        .modal-head,
        .modal-foot {
            flex-shrink: 0;
        }

        .modal-body {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--pencairan-border) transparent;
        }

        .modal-body::-webkit-scrollbar { width: 6px; }
        .modal-body::-webkit-scrollbar-track { background: transparent; }
        .modal-body::-webkit-scrollbar-thumb { background: var(--pencairan-border); border-radius: 4px; }
        .modal-body::-webkit-scrollbar-thumb:hover { background: var(--pencairan-text-muted); }

        .combo--arrow .combo-input {
            cursor: pointer;
            padding-right: 30px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 9px center;
            background-size: 14px;
        }

        .combo-list {
            z-index: 60;
            scrollbar-width: thin;
            scrollbar-color: var(--pencairan-border) transparent;
        }

        .combo-list::-webkit-scrollbar { width: 6px; }
        .combo-list::-webkit-scrollbar-track { background: transparent; }
        .combo-list::-webkit-scrollbar-thumb { background: var(--pencairan-border); border-radius: 4px; }
        .combo-list::-webkit-scrollbar-thumb:hover { background: var(--pencairan-text-muted); }

        .combo-item { padding: 10px 14px; }
        .combo-item.is-selected { font-weight: 700; }

        .combo-list { min-width: 84px; }

        .combo--up .combo-list {
            top: auto;
            bottom: calc(100% + 4px);
        }
    </style>

    <script>
        // Variabel penampung data item saat modal invoice dibuka
        let activeInvoiceItem = null;

        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif

            const searchInput = document.getElementById('searchPencairan');
            const table = document.getElementById('pencairanTable');
            const perPageSelect = document.getElementById('perPageSelect');
            const paginationContainer = document.getElementById('paginationButtons');
            const tableInfo = document.getElementById('tableInfoPagination');
            const kosongCari = document.getElementById('pencairanKosongCari');

            let currentPage = 1;

            const rows = table ? Array.from(table.querySelectorAll('tbody tr[data-row-pencairan]')) : [];

            function render() {
                if (!table) return;

                const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const filtered = rows.filter(r => r.textContent.toLowerCase().includes(keyword));

                if (kosongCari) {
                    kosongCari.style.display = (rows.length > 0 && filtered.length === 0) ? '' : 'none';
                }

                const perPage = parseInt(perPageSelect ? perPageSelect.value : 10, 10);
                const totalPages = Math.ceil(filtered.length / perPage) || 1;
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);

                rows.forEach(r => r.style.display = 'none');

                const start = (currentPage - 1) * perPage;
                const end = start + perPage;
                filtered.slice(start, end).forEach(r => r.style.display = '');

                if (tableInfo) {
                    tableInfo.innerHTML = filtered.length === 0 ?
                        'Tidak ada data yang ditampilkan' :
                        `Menampilkan permohonan <strong>${start + 1}</strong> - <strong>${Math.min(end, filtered.length)}</strong> dari <strong>${filtered.length}</strong>`;
                }

                if (!paginationContainer) return;

                let html =
                    `<button class="btn btn--ghost" type="button" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>‹</button>`;

                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                        html +=
                            `<button class="btn ${i === currentPage ? 'btn--primary' : 'btn--ghost'}" type="button" data-page="${i}">${i}</button>`;
                    } else if (i === currentPage - 2 || i === currentPage + 2) {
                        html += '<span class="pagination-dots">…</span>';
                    }
                }

                html +=
                    `<button class="btn btn--ghost" type="button" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>›</button>`;
                paginationContainer.innerHTML = html;
            }

            if (paginationContainer) {
                paginationContainer.addEventListener('click', function(e) {
                    const btn = e.target.closest('button[data-page]');
                    if (!btn || btn.disabled) return;
                    currentPage = parseInt(btn.dataset.page, 10);
                    render();
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    currentPage = 1;
                    render();
                });
            }

            document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) overlay.classList.remove('active');
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay.active').forEach(function(overlay) {
                        overlay.classList.remove('active');
                    });
                }
            });

            initComboWarga();
            initComboSelect('tambahMetode');
            initComboSelect('editStatus');
            initComboSelect('perPageSelect', function() {
                currentPage = 1;
                render();
            });

            render();
        });

        /* Combobox untuk <select> tersembunyi (pilihan tetap, tanpa teks bebas) */
        const comboSelectRegistry = {};

        function syncComboSelect(selectId) {
            if (comboSelectRegistry[selectId]) comboSelectRegistry[selectId]();
        }

        function initComboSelect(selectId, onChange) {
            const select = document.getElementById(selectId);
            const input = document.getElementById('cari_' + selectId);
            const list = document.getElementById('list_' + selectId);
            if (!select || !input || !list) return;

            input.readOnly = true;
            let aktif = -1;

            const teks = o => o.textContent.replace(/\s+/g, ' ').trim();
            const esc = v => String(v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

            const kosong = Array.from(select.options).find(o => o.value === '');
            if (kosong) input.placeholder = teks(kosong);

            function sync() {
                const o = select.options[select.selectedIndex];
                input.value = (o && o.value !== '') ? teks(o) : '';
            }

            function buka() {
                list.innerHTML = Array.from(select.options).filter(o => o.value !== '').map(o =>
                    `<div class="combo-item${o.value === select.value ? ' is-selected' : ''}" data-value="${esc(o.value)}">${esc(teks(o))}</div>`
                ).join('');
                aktif = -1;
                list.classList.add('show');
            }

            function tutup() { list.classList.remove('show'); }

            function pilih(value) {
                const berubah = select.value !== String(value);
                select.value = value;
                sync();
                tutup();
                if (berubah && typeof onChange === 'function') onChange();
            }

            function sorot(arah) {
                const items = list.querySelectorAll('.combo-item');
                if (!items.length) return;
                aktif = (aktif + arah + items.length) % items.length;
                items.forEach((el, i) => el.classList.toggle('is-active', i === aktif));
            }

            input.addEventListener('focus', buka);
            input.addEventListener('click', () => { if (!list.classList.contains('show')) buka(); });
            input.addEventListener('blur', tutup);

            input.addEventListener('keydown', function(e) {
                const items = list.querySelectorAll('.combo-item');
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!list.classList.contains('show')) buka();
                    sorot(1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    sorot(-1);
                } else if (e.key === 'Enter') {
                    if (list.classList.contains('show') && aktif > -1 && items[aktif]) {
                        e.preventDefault();
                        pilih(items[aktif].dataset.value);
                    }
                } else if (e.key === 'Escape' && list.classList.contains('show')) {
                    e.stopPropagation(); // jangan ikut menutup modal
                    tutup();
                }
            });

            list.addEventListener('mouseover', function(e) {
                const item = e.target.closest('.combo-item');
                if (!item) return;
                list.querySelectorAll('.combo-item').forEach((el, i) => {
                    el.classList.toggle('is-active', el === item);
                    if (el === item) aktif = i;
                });
            });

            list.addEventListener('mousedown', function(e) {
                e.preventDefault();
                const item = e.target.closest('.combo-item');
                if (item) pilih(item.dataset.value);
            });

            comboSelectRegistry[selectId] = sync;
            sync();
        }

        function formatStatusRekening(status) {
            return status === 'verified' ? 'Terverifikasi' : 'Belum Verifikasi';
        }

        function initComboWarga() {
            const select = document.getElementById('tambahWarga');
            const input = document.getElementById('cariWarga');
            const list = document.getElementById('listWarga');
            const info = document.getElementById('tambahSaldoInfo');
            const rekeningInfo = document.getElementById('tambahRekeningInfo');

            if (!select || !input || !list) return;

            const data = Array.from(select.options)
                .filter(o => o.value !== '')
                .map(o => ({
                    value: o.value,
                    label: o.textContent.replace(/\s+/g, ' ').trim(),
                    saldo: Number(o.dataset.saldo || 0),
                    bank: o.dataset.bank || '',
                    rekening: o.dataset.rekening || '',
                    statusRekening: o.dataset.statusRekening || ''
                }));

            let aktif = -1;

            function escapeHtml(s) {
                return String(s).replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));
            }

            function render(keyword) {
                const k = (keyword || '').toLowerCase().trim();
                const hasil = data.filter(d => d.label.toLowerCase().includes(k));
                aktif = -1;

                list.innerHTML = hasil.length ?
                    hasil.map(d => `<div class="combo-item" data-value="${d.value}">${escapeHtml(d.label)}</div>`).join(
                        '') :
                    `<div class="combo-empty">Warga tidak ditemukan</div>`;
            }

            function pilih(value) {
                const d = data.find(x => x.value === String(value));
                if (!d) return;

                select.value = d.value;
                input.value = d.label;
                info.textContent = formatRupiah(d.saldo);

                if (rekeningInfo) {
                    rekeningInfo.textContent = d.bank ?
                        `${d.bank} - ${d.rekening || '-'}` :
                        'Tunai / Lainnya';
                }
                list.classList.remove('show');
            }

            function sorot(arah) {
                const items = list.querySelectorAll('.combo-item');
                if (!items.length) return;
                aktif = (aktif + arah + items.length) % items.length;
                items.forEach((el, i) => el.classList.toggle('is-active', i === aktif));
                items[aktif].scrollIntoView({
                    block: 'nearest'
                });
            }

            // Kembalikan teks input ke warga yang terpilih (kosong jika belum memilih)
            function tutup() {
                list.classList.remove('show');
                const d = data.find(x => x.value === String(select.value));
                input.value = d ? d.label : '';
            }

            input.addEventListener('focus', function() {
                this.select();
                render('');
                list.classList.add('show');
            });

            // Mengetik hanya menyaring daftar; warga baru tersimpan setelah dipilih
            input.addEventListener('input', function() {
                render(this.value);
                list.classList.add('show');
            });

            input.addEventListener('blur', tutup);

            input.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!list.classList.contains('show')) {
                        render(this.value);
                        list.classList.add('show');
                    }
                    sorot(1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    sorot(-1);
                } else if (e.key === 'Enter') {
                    const items = list.querySelectorAll('.combo-item');
                    if (list.classList.contains('show') && aktif > -1 && items[aktif]) {
                        e.preventDefault();
                        pilih(items[aktif].dataset.value);
                    }
                } else if (e.key === 'Escape' && list.classList.contains('show')) {
                    e.stopPropagation(); // jangan ikut menutup modal
                    tutup();
                }
            });

            list.addEventListener('mouseover', function(e) {
                const item = e.target.closest('.combo-item');
                if (!item) return;
                list.querySelectorAll('.combo-item').forEach((el, i) => {
                    el.classList.toggle('is-active', el === item);
                    if (el === item) aktif = i;
                });
            });

            list.addEventListener('mousedown', function(e) {
                e.preventDefault(); // jaga fokus agar list tidak tertutup saat scroll/klik
                const item = e.target.closest('.combo-item');
                if (item) pilih(item.dataset.value);
            });
        }

        function resetComboWarga() {
            const select = document.getElementById('tambahWarga');
            const input = document.getElementById('cariWarga');
            const list = document.getElementById('listWarga');
            const info = document.getElementById('tambahSaldoInfo');
            const rekeningInfo = document.getElementById('tambahRekeningInfo');

            if (select) select.value = '';
            if (input) input.value = '';
            if (list) list.classList.remove('show');
            if (info) info.textContent = '-';
            if (rekeningInfo) rekeningInfo.textContent = '-';
        }

        function bukaModal(id) {
            if (id === 'modalTambah') {
                const form = document.getElementById('formTambahPencairan');
                if (form) form.reset();
                resetComboWarga();
                syncComboSelect('tambahMetode');
            }
            document.getElementById(id).classList.add('active');
        }

        function tutupModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function formatRupiah(angka) {
            return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
        }

        function showToast(title, text, type = 'success') {
            const wrap = document.getElementById('toastWrap');
            const toast = document.createElement('div');
            toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');

            const iconPath = type === 'error' ?
                '<path d="M18 6 6 18M6 6l12 12"></path>' :
                '<path d="M20 6 9 17l-5-5"></path>';

            toast.innerHTML = `
                <div class="toast-icon"><svg viewBox="0 0 24 24">${iconPath}</svg></div>
                <div class="toast-body">
                    <p class="toast-title">${title}</p>
                    <p class="toast-text">${text}</p>
                </div>
                <button class="toast-close" type="button" aria-label="Tutup">&times;</button>
            `;

            toast.querySelector('.toast-close').addEventListener('click', () => toast.remove());
            wrap.appendChild(toast);
            setTimeout(() => toast.remove(), 3500);
        }

        function simpanTambah(event) {
            event.preventDefault();
            const selectWarga = document.getElementById('tambahWarga');
            const idWarga = selectWarga.value;

            if (!idWarga) {
                showToast('Gagal menyimpan', 'Silakan pilih warga terlebih dahulu.', 'error');
                document.getElementById('cariWarga').focus();
                return false;
            }

            const selectedOption = selectWarga.options[selectWarga.selectedIndex];
            const saldoTersedia = Number(selectedOption.dataset.saldo || 0);
            const jumlahDitarik = Number(document.getElementById('tambahJumlah').value);

            if (jumlahDitarik > saldoTersedia) {
                showToast('Gagal menyimpan', 'Jumlah penarikan melebihi saldo tersedia warga!', 'error');
                document.getElementById('tambahJumlah').focus();
                return false;
            }

            if (!document.getElementById('tambahMetode').value) {
                showToast('Gagal menyimpan', 'Silakan pilih metode pencairan.', 'error');
                document.getElementById('cari_tambahMetode')?.focus();
                return false;
            }

            const payload = {
                id_warga: idWarga,
                jumlah: jumlahDitarik,
                metode_transfer: document.getElementById('tambahMetode').value,
                tanggal_pencairan: document.getElementById('tambahTanggal').value,
            };

            fetch(`{{ route('admin.pencairan.store') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Gagal menyimpan permohonan.');
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalTambah');
                    showToast('Berhasil', 'Permohonan pencairan telah dibuat.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => showToast('Gagal menyimpan', err.message, 'error'));

            return false;
        }

        function bukaInvoiceCard(item) {
            activeInvoiceItem = item; // Simpan data item saat ini untuk kebutuhan WA

            document.getElementById('invId').textContent = `#INV-${item.id_pencairan}`;
            document.getElementById('invTanggal').textContent = item.tanggal_pencairan ? new Date(item.tanggal_pencairan)
                .toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                }) : '-';

            const namaWarga = item.warga?.nama || 'Anonim';
            document.getElementById('invNama').textContent = namaWarga;
            document.getElementById('invNik').textContent = `(NIK: ${item.warga?.nik || '-'})`;

            const bankInfo = document.getElementById('invBankInfo');
            const rekInfo = document.getElementById('invRekening');

            if (item.warga && item.warga.nama_bank_ewallet) {
                bankInfo.textContent = item.warga.nama_bank_ewallet;
                rekInfo.textContent = item.warga.nomor_rekening || '-';
            } else {
                bankInfo.textContent = 'Tunai / Langsung';
                rekInfo.textContent = '-';
            }

            document.getElementById('invNominal').textContent = formatRupiah(item.jumlah);

            const badgeStatus = document.getElementById('invBadgeStatus');
            if (item.status === 'menunggu') {
                badgeStatus.innerHTML = '<span class="badge badge--warning">Menunggu</span>';
            } else if (item.status === 'selesai') {
                badgeStatus.innerHTML = '<span class="badge badge--success">Selesai</span>';
            } else {
                badgeStatus.innerHTML = '<span class="badge badge--danger">Ditolak</span>';
            }

            bukaModal('modalInvoice');
        }

        // Fungsi untuk mengirim pesan WhatsApp berisi detail invoice pencairan
        function kirimInvoiceWhatsApp() {
            if (!activeInvoiceItem) {
                showToast('Gagal', 'Data invoice tidak ditemukan.', 'error');
                return;
            }

            const noHp = activeInvoiceItem.warga?.no_hp;
            if (!noHp) {
                showToast('Gagal', 'Nomor HP warga tidak tersedia.', 'error');
                return;
            }

            let formattedPhone = noHp.replace(/\D/g, '');
            if (formattedPhone.startsWith('0')) {
                formattedPhone = '62' + formattedPhone.slice(1);
            }

            const namaWarga = activeInvoiceItem.warga?.nama || 'Warga';
            const idInv = `#INV-${activeInvoiceItem.id_pencairan}`;
            const nominal = formatRupiah(activeInvoiceItem.jumlah);
            const status = activeInvoiceItem.status.toUpperCase();
            const metodePencairan = activeInvoiceItem.warga?.nama_bank_ewallet ? activeInvoiceItem.warga.nama_bank_ewallet :
                'Tunai';

            const tanggal = activeInvoiceItem.tanggal_pencairan ? new Date(activeInvoiceItem.tanggal_pencairan)
                .toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                }) : '-';

            // Menggunakan Unicode Escape Sequence untuk mencegah error encoding emoji
            const emojiPin = '\u{1F4CC}';
            const emojiCalendar = '\u{1F4C5}';
            const emojiMoneyBag = '\u{1F4B0}';
            const emojiCash = '\u{1F4B5}';
            const emojiSparkles = '\u{2728}';
            const emojiSeedling = '\u{1F331}';

            const pesan = `*NOTIFIKASI PENCAIRAN SALDO*
*BANK SAMPAH SAHABAT IBU*
---------------------------------------
Halo *${namaWarga}*,

Permohonan pencairan saldo tabungan sampah Anda telah kami proses dengan rincian berikut:

${emojiPin} *No. Invoice* : ${idInv}
${emojiCalendar} *Tanggal*    : ${tanggal}
${emojiCash} *Metode*     : ${metodePencairan}
${emojiMoneyBag} *Jumlah*     : *${nominal}*
${emojiSparkles} *Status*     : *${status}*
---------------------------------------
Terima kasih telah aktif menabung dan menjaga kebersihan lingkungan bersama kami! ${emojiSeedling}


_Pesan otomatis oleh Sistem Bank Sampah Sahabat Ibu_`;
            const urlWa = `https://wa.me/${formattedPhone}?text=${encodeURIComponent(pesan)}`;
            window.open(urlWa, '_blank');
        }

        function prosesPencairan(item) {
            document.getElementById('editId').value = item.id_pencairan;
            document.getElementById('editNama').value = `${item.warga?.nama || 'Anonim'} (NIK: ${item.warga?.nik || '-'})`;
            document.getElementById('editNominal').value = formatRupiah(item.jumlah);
            document.getElementById('editBank').value = item.warga?.nama_bank_ewallet || '-';
            document.getElementById('editNoRekening').value = item.warga?.nomor_rekening || '-';
            document.getElementById('editStatusRekening').value = item.warga?.nama_bank_ewallet ? formatStatusRekening(item
                .warga?.status_rekening) : '-';
            document.getElementById('editStatus').value = item.status;
            syncComboSelect('editStatus');

            bukaModal('modalProses');
        }

        function simpanProses(event) {
            event.preventDefault();
            const id = document.getElementById('editId').value;
            const status = document.getElementById('editStatus').value;
            const baseUrl = "{{ route('admin.pencairan.index') }}";

            fetch(`${baseUrl}/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status
                    })
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Gagal memproses pencairan.');
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalProses');
                    showToast('Berhasil disimpan', 'Status pencairan berhasil diperbarui.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => showToast('Gagal menyimpan', err.message, 'error'));

            return false;
        }
    </script>

@endsection
