@extends('admin.layouts.app')

@section('title', 'Data Warga')
@section('active', 'warga')
@section('crumbs', 'Master Data | Data Warga')

@section('content')

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">MASTER DATA</span>
            <h1 class="hero-title">
                Data <span class="accent">Warga</span>
            </h1>
            <p class="hero-sub">
                Kelola data masyarakat yang telah terdaftar sebagai anggota Bank Sampah.
            </p>
        </div>

        <div class="hero-actions">
            <button class="btn btn--primary" type="button" onclick="bukaModal('modalTambah')">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"></path>
                </svg>
                Tambah Warga
            </button>
        </div>
    </section>

    <!-- STATS SUMMARY -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon--blue">
                <svg viewBox="0 0 24 24">
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"></path>
                    <circle cx="19" cy="8" r="2.3"></circle>
                    <path d="M17 21v-1a3 3 0 0 0-2-2.83"></path>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Warga</span>
                <span class="stat-value">{{ $warga->count() }} orang</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon--green">
                <svg viewBox="0 0 24 24">
                    <path d="M12 3v18"></path>
                    <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Saldo Terkumpul</span>
                <span class="stat-value">Rp {{ number_format($warga->sum('saldo'), 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon--amber">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                    <path d="M3 10h18"></path>
                    <path d="M16 15h2"></path>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-label">Rekening Terverifikasi</span>
                <span class="stat-value">{{ $warga->where('status_rekening', 'verified')->count() }} Rekening</span>
            </div>
        </div>
    </div>

    <!-- MAIN DATA TABLE CARD -->
    <section class="card">
        <!-- TOOLBAR / SEARCH + FILTER -->
        <div class="table-toolbar">
            <div class="table-search">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchWarga" placeholder="Cari NIK, nama, nomor HP, atau alamat..."
                    autocomplete="off">
            </div>

            <div class="filter-pills" id="filterPills">
                <button class="fpill active" type="button" data-filter="all">Semua</button>
                <button class="fpill" type="button" data-filter="verified">Terverifikasi</button>
                <button class="fpill" type="button" data-filter="unverified">Belum Verifikasi</button>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-responsive">
            <table class="data-table" id="wargaTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Warga</th>
                        <th>No. HP</th>
                        <th>Alamat</th>
                        <th>Anggota</th>
                        <th>Saldo</th>
                        <th>Rekening / E-Wallet</th>
                        <th>Tgl Daftar</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warga as $index => $item)
                        <tr data-status="{{ $item->nama_bank_ewallet ? $item->status_rekening : 'none' }}">
                            <!-- NO -->
                            <td class="text-subtle">{{ $index + 1 }}</td>

                            <!-- NAMA WARGA & NIK -->
                            <td>
                                <strong class="user-name">{{ $item->nama }}</strong>
                                <div class="mono text-subtle" style="margin-top: 2px;">{{ $item->nik }}</div>
                            </td>

                            <!-- NO HP -->
                            <td>{{ $item->no_hp ?? '-' }}</td>

                            <!-- ALAMAT -->
                            <td>
                                <div class="truncate-text" title="{{ $item->alamat }}">
                                    {{ Str::limit($item->alamat, 35, '...') }}
                                </div>
                            </td>

                            <!-- JUMLAH ANGGOTA KELUARGA -->
                            <td>
                                <span class="kategori-tag">
                                    {{ $item->jumlah_anggota_keluarga ?? 0 }} org
                                </span>
                            </td>

                            <!-- SALDO -->
                            <td>
                                <strong class="saldo-text">
                                    Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}
                                </strong>
                            </td>

                            <!-- REKENING / E-WALLET -->
                            <td>
                                @if ($item->nama_bank_ewallet)
                                    <div class="bank-info">
                                        <div class="bank-main">
                                            <span
                                                class="status-badge status-badge--icon {{ $item->status_rekening === 'verified' ? 'status-badge--approved' : 'status-badge--pending' }}"
                                                title="{{ $item->status_rekening === 'verified' ? 'Terverifikasi' : 'Belum Verifikasi' }}"
                                                aria-label="{{ $item->status_rekening === 'verified' ? 'Terverifikasi' : 'Belum Verifikasi' }}">
                                                @if ($item->status_rekening === 'verified')
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
                                            <strong
                                                title="{{ $item->nama_bank_ewallet }}">{{ $item->nama_bank_ewallet }}</strong>
                                        </div>
                                        <span class="mono text-subtle bank-number"
                                            title="{{ $item->nomor_rekening ?? '-' }}">
                                            {{ $item->nomor_rekening ?? '-' }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-subtle">-</span>
                                @endif
                            </td>

                            <!-- TANGGAL DAFTAR -->
                            <td class="text-subtle">
                                @if ($item->tanggal_daftar)
                                    {{ \Carbon\Carbon::parse($item->tanggal_daftar)->translatedFormat('d/m/y') }}
                                @else
                                    -
                                @endif
                            </td>

                            <!-- AKSI -->
                            <td>
                                <div class="table-actions" style="justify-content: center;">
                                    <button class="icon-btn" title="Lihat Detail" type="button"
                                        onclick='lihatWarga(@json($item))'>
                                        <svg viewBox="0 0 24 24">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                            <circle cx="12" cy="12" r="2.5"></circle>
                                        </svg>
                                    </button>

                                    <button class="icon-btn icon-btn--primary" title="Edit Data" type="button"
                                        onclick='editWarga(@json($item))'>
                                        <svg viewBox="0 0 24 24">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                        </svg>
                                    </button>

                                    <button class="icon-btn icon-btn--danger" title="Hapus Data" type="button"
                                        onclick='hapusWarga(@json($item))'>
                                        <svg viewBox="0 0 24 24">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                            <path d="M10 11v6M14 11v6"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px;">
                                <div style="font-size: 30px; margin-bottom: 10px;">📋</div>
                                <strong>Belum Ada Data Warga</strong>
                                <p class="empty-desc" style="margin: 5px 0 0;">
                                    Belum ada masyarakat yang terdaftar di Bank Sampah.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- FOOTER -->
        <div class="table-footer">
            <div class="table-info" id="tableInfoPagination">Menampilkan data...</div>

            <div class="table-pagination-controls">
                <div class="per-page">
                    <label for="cari_perPageSelect">Warga per halaman:</label>
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

    <!-- TOAST NOTIFIKASI -->
    <div class="toast-wrap" id="toastWrap"></div>

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
                        <div class="detail-nik mono" id="detailNik">-</div>
                    </div>
                </div>

                <div class="detail-grid">
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
                            <label for="tambahHp">No. HP</label>
                            <input type="text" id="tambahHp" maxlength="14" required placeholder="08xxxxxxxxxx">
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
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                    </svg>
                    Edit Data Warga
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalEdit')">&times;</button>
            </div>

            <form id="formEditWarga" onsubmit="return simpanWarga(event)">
                <div class="modal-body">
                    <input type="hidden" id="editId">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="editNik">NIK</label>
                            <input type="text" id="editNik" maxlength="16" required>
                        </div>

                        <div class="form-group">
                            <label for="editNama">Nama Lengkap</label>
                            <input type="text" id="editNama" required>
                        </div>

                        <div class="form-group">
                            <label for="editHp">No. HP</label>
                            <input type="text" id="editHp">
                        </div>

                        <div class="form-group">
                            <label for="editKeluarga">Anggota Keluarga</label>
                            <input type="number" id="editKeluarga" min="0">
                        </div>

                        <div class="form-group">
                            <label for="editSaldo">Saldo (Rp)</label>
                            <input type="number" id="editSaldo" min="0">
                        </div>

                        <div class="form-group">
                            <label for="editTanggal">Tanggal Daftar</label>
                            <input type="date" id="editTanggal">
                        </div>

                        <div class="form-group form-group--full">
                            <label for="editAlamat">Alamat</label>
                            <textarea id="editAlamat" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="editBankInput">Bank / E-Wallet</label>
                            <div class="combo" id="comboBankEdit">
                                <input type="text" id="editBankInput" class="combo-input"
                                    placeholder="Ketik atau pilih bank/e-wallet..." maxlength="50" autocomplete="off">
                                <div class="combo-list" id="listBankEdit"></div>
                            </div>
                            <input type="hidden" id="editBank">
                        </div>

                        <div class="form-group">
                            <label for="editNoRekening">No. Rekening / No. HP E-Wallet</label>
                            <input type="text" id="editNoRekening" maxlength="30">
                        </div>

                        <div class="form-group form-group--full">
                            <label for="editPemilikRekening">Nama Pemilik Rekening</label>
                            <input type="text" id="editPemilikRekening">
                        </div>

                        <div class="form-group">
                            <label for="cari_editStatusRekening">Status Rekening</label>
                            <div class="combo combo--arrow" id="combo_editStatusRekening">
                                <input type="text" id="cari_editStatusRekening" class="combo-input"
                                    autocomplete="off">
                                <div class="combo-list" id="list_editStatusRekening"></div>
                            </div>
                            <select id="editStatusRekening" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="unverified">Belum Verifikasi</option>
                                <option value="verified">Terverifikasi</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalEdit')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan Perubahan</button>
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

    <!-- STYLES -->
    <style>
        /* =========================================================
                           VARIABEL TEMA (Light / Dark)
                           ========================================================= */
        :root {
            --dashboard-page: #f5f7fb;
            --dashboard-card: #ffffff;
            --dashboard-card-secondary: #f8fafc;
            --dashboard-text: #1f2937;
            --dashboard-text-secondary: #6b7280;
            --dashboard-text-muted: #9ca3af;
            --dashboard-border: #e5e7eb;
            --dashboard-table-head: #f8fafc;
            --dashboard-table-row: #ffffff;
            --dashboard-table-hover: #fafafa;
            --dashboard-input: #ffffff;
            --dashboard-shadow: 0 8px 25px rgba(15, 23, 42, .06);
        }

        [data-theme="dark"] {
            --dashboard-page: #0b1220;
            --dashboard-card: #151d2f;
            --dashboard-card-secondary: #1b2438;
            --dashboard-text: #f1f5f9;
            --dashboard-text-secondary: #aab6c8;
            --dashboard-text-muted: #748198;
            --dashboard-border: #29364d;
            --dashboard-table-head: #111a2c;
            --dashboard-table-row: #151d2f;
            --dashboard-table-hover: #202b40;
            --dashboard-input: #1b2438;
            --dashboard-shadow: 0 8px 25px rgba(0, 0, 0, .25);
        }

        body {
            background: var(--dashboard-page);
            color: var(--dashboard-text);
            transition: background-color .25s ease, color .25s ease;
        }

        /* =========================================================
                           TEMA BERSAMA
                           ========================================================= */

        .card {
            background: var(--dashboard-card);
            border: 1px solid var(--dashboard-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--dashboard-shadow);
            transition: background-color .25s ease, border-color .25s ease, color .25s ease, box-shadow .25s ease;
        }

        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 16px 24px;
        }

        .table-search {
            width: 360px;
            max-width: 100%;
            position: relative;
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
            color: var(--dashboard-text-secondary);
        }

        .table-search input {
            width: 100%;
            height: 40px;
            padding: 0 14px 0 40px;
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            outline: none;
            box-sizing: border-box;
            background: var(--dashboard-input);
            color: var(--dashboard-text);
        }

        .table-search input:focus {
            border-color: var(--primary, #4338ca);
        }

        /* Scrollbar Modern pada Tabel */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--dashboard-border) transparent;
        }

        .table-responsive::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: transparent;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: var(--dashboard-border);
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: var(--dashboard-text-muted);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
            background: var(--dashboard-table-row);
            color: var(--dashboard-text);
            table-layout: auto;
        }

        .data-table th {
            background: var(--dashboard-table-head);
            color: var(--dashboard-text-secondary);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 13px 16px;
            text-align: left;
            border-top: 1px solid var(--dashboard-border);
            border-bottom: 1px solid var(--dashboard-border);
            white-space: nowrap;
            transition: background-color .25s ease, color .25s ease, border-color .25s ease;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid var(--dashboard-border);
            font-size: 13px;
            color: var(--dashboard-text);
            vertical-align: middle;
            background: var(--dashboard-table-row);
            transition: background-color .25s ease, color .25s ease, border-color .25s ease;
        }

        .data-table tbody tr:hover td {
            background: var(--dashboard-table-hover);
        }

        .mono {
            font-family: monospace;
            font-size: 12px;
        }

        .text-subtle {
            color: var(--dashboard-text-secondary);
        }

        .user-name {
            font-weight: 700;
            color: var(--dashboard-text);
        }

        .truncate-text {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            overflow: hidden;
            line-height: 1.45;
            max-height: 2.9em;
            word-break: break-word;
        }

        .saldo-text {
            color: #15803d;
            white-space: nowrap;
        }

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

        .bank-main>strong {
            display: block;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .bank-info .bank-number {
            display: block;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding-left: 31px;
        }

        .table-actions {
            display: flex;
            gap: 6px;
            white-space: nowrap;
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            border: 1px solid var(--dashboard-border);
            background: var(--dashboard-card);
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--dashboard-text-secondary);
            transition: background-color .2s ease, color .2s ease, border-color .2s ease;
        }

        .icon-btn svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .icon-btn:hover {
            background: var(--dashboard-table-hover);
            color: var(--dashboard-text);
            border-color: var(--dashboard-border);
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            flex-wrap: wrap;
        }

        .table-info {
            font-size: 13px;
            color: var(--dashboard-text-secondary);
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
            color: var(--dashboard-text-secondary);
        }

        .per-page .combo {
            width: 84px;
        }

        .per-page .combo-input {
            padding-top: 6px;
            padding-bottom: 6px;
        }

        .combo--up .combo-list {
            top: auto;
            bottom: calc(100% + 4px);
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
            color: var(--dashboard-text-secondary);
        }

        .empty-desc {
            color: var(--dashboard-text-secondary);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
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
            max-width: 580px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: var(--dashboard-card);
            border: 1px solid var(--dashboard-border);
            border-radius: 14px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
            animation: modalPop .15s ease-out;
            transition: background-color .25s ease, border-color .25s ease, color .25s ease;
        }

        .modal-box--sm {
            max-width: 420px;
        }

        @keyframes modalPop {
            from {
                opacity: 0;
                transform: translateY(8px) scale(.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-box>form {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid var(--dashboard-border);
            flex-shrink: 0;
        }

        .modal-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--dashboard-text);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-title svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .modal-close {
            width: 30px;
            height: 30px;
            border: none;
            background: transparent;
            border-radius: 7px;
            font-size: 20px;
            line-height: 1;
            color: var(--dashboard-text-secondary);
            cursor: pointer;
        }

        .modal-close:hover {
            background: var(--dashboard-table-hover);
            color: var(--dashboard-text);
        }

        .modal-body {
            padding: 22px 24px;
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--dashboard-border) transparent;
        }

        .modal-body::-webkit-scrollbar {
            width: 6px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: var(--dashboard-border);
            border-radius: 4px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: var(--dashboard-text-muted);
        }

        .modal-body--center {
            text-align: center;
            padding-top: 28px;
        }

        .modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid var(--dashboard-border);
            flex-shrink: 0;
        }

        .modal-foot--center {
            justify-content: center;
        }

        .btn--danger {
            background: #dc2626;
            color: #fff;
            border: 1px solid #dc2626;
        }

        .btn--danger:hover {
            background: #b91c1c;
            border-color: #b91c1c;
        }

        .detail-avatar-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 18px;
            margin-bottom: 18px;
            border-bottom: 1px solid var(--dashboard-border);
        }

        .detail-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #eef2ff;
            color: #4338ca;
            font-weight: 700;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .detail-nama {
            font-size: 16px;
            font-weight: 700;
            color: var(--dashboard-text);
        }

        .detail-nik {
            margin-top: 3px;
            color: var(--dashboard-text-secondary);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 18px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .detail-item--full {
            grid-column: 1 / -1;
        }

        .detail-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--dashboard-text-muted);
            display: block;
        }

        .detail-value {
            font-size: 14px;
            color: var(--dashboard-text);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group--full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 700;
            color: var(--dashboard-text-secondary);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            width: 100%;
            background: var(--dashboard-input);
            color: var(--dashboard-text);
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
        }

        .form-group textarea {
            resize: vertical;
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
            background: var(--dashboard-card);
            border: 1px solid var(--dashboard-border);
            border-left: 4px solid #16a34a;
            border-radius: 10px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.15);
            padding: 14px 16px;
            pointer-events: auto;
            animation: toastIn .18s ease-out;
        }

        .toast.toast--error {
            border-left-color: #dc2626;
        }

        .toast-icon {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: rgba(34, 197, 94, .14);
            color: #4ade80;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toast.toast--error .toast-icon {
            background: rgba(239, 68, 68, .14);
            color: #f87171;
        }

        .toast-icon svg {
            width: 13px;
            height: 13px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.4;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .toast-body {
            flex: 1;
        }

        .toast-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--dashboard-text);
            margin: 0 0 2px;
        }

        .toast-text {
            font-size: 12.5px;
            color: var(--dashboard-text-secondary);
            margin: 0;
            line-height: 1.5;
        }

        .toast-close {
            border: none;
            background: transparent;
            color: var(--dashboard-text-muted);
            font-size: 16px;
            line-height: 1;
            cursor: pointer;
            padding: 0;
            flex-shrink: 0;
        }

        .toast-close:hover {
            color: var(--dashboard-text);
        }

        .toast.toast--leaving {
            animation: toastOut .18s ease-in forwards;
        }

        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateX(16px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes toastOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(16px);
            }
        }

        /* =========================================================
                           TAMBAHAN KHUSUS DATA WARGA
                           ========================================================= */

        .hero-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--dashboard-card);
            border: 1px solid var(--dashboard-border);
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: var(--dashboard-shadow);
            transition: background-color .25s ease, border-color .25s ease, color .25s ease, box-shadow .25s ease;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .stat-icon--blue {
            background: rgba(59, 130, 246, .14);
            color: #60a5fa;
        }

        .stat-icon--green {
            background: rgba(34, 197, 94, .14);
            color: #4ade80;
        }

        .stat-icon--amber {
            background: rgba(245, 158, 11, .14);
            color: #fbbf24;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-info .stat-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--dashboard-text-secondary);
        }

        .stat-info .stat-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--dashboard-text);
        }

        .filter-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .fpill {
            border: 1px solid var(--dashboard-border);
            background: transparent;
            color: var(--dashboard-text-secondary);
            padding: 8px 14px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }

        .fpill.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .kategori-tag {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 7px;
            font-size: 12.5px;
            font-weight: 600;
            background: rgba(67, 56, 202, .07);
            color: #4338ca;
            border: 1px solid #d9d6f7;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 9px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-badge--pending {
            background: #fef3e0;
            color: #b45309;
            border: 1px solid #f3d9a0;
        }

        .status-badge--approved {
            background: #e9f9ee;
            color: #15803d;
            border: 1px solid #bfead0;
        }

        .status-badge--icon {
            width: 24px;
            height: 24px;
            min-width: 24px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .status-badge--icon svg {
            width: 14px;
            height: 14px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .icon-btn--primary {
            border-color: #4338ca;
            color: #4338ca;
        }

        .icon-btn--primary:hover {
            background: #eef2ff;
        }

        .icon-btn--danger {
            color: #f87171;
        }

        .icon-btn--danger:hover {
            background: rgba(239, 68, 68, .12);
            border-color: rgba(239, 68, 68, .30);
            color: #f87171;
        }

        .confirm-icon {
            width: 52px;
            height: 52px;
            margin: 0 auto 12px;
            border-radius: 50%;
            background: rgba(239, 68, 68, .14);
            color: #f87171;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .confirm-icon svg {
            width: 24px;
            height: 24px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .confirm-title {
            margin: 0 0 6px;
            font-size: 16px;
            font-weight: 700;
            color: var(--dashboard-text);
        }

        .confirm-text {
            margin: 0;
            font-size: 13px;
            color: var(--dashboard-text-secondary);
            line-height: 1.5;
        }

        /* =========================================================
                           COMBOBOX (SEARCHABLE SELECT)
                           ========================================================= */

        .combo {
            position: relative;
        }

        .combo-input {
            width: 100%;
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            background: var(--dashboard-input);
            color: var(--dashboard-text);
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .combo-input:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
        }

        .combo-input::placeholder {
            color: var(--dashboard-text-muted);
        }

        .combo-hidden {
            display: none !important;
        }

        .combo--arrow .combo-input {
            cursor: pointer;
            padding-right: 30px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 9px center;
            background-size: 14px;
        }

        .combo-list {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            max-height: 180px;
            overflow-y: auto;
            background: var(--dashboard-card);
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .22);
            z-index: 60;
            scrollbar-width: thin;
            scrollbar-color: var(--dashboard-border) transparent;
        }

        .combo-list.show {
            display: block;
        }

        .combo-list::-webkit-scrollbar {
            width: 6px;
        }

        .combo-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .combo-list::-webkit-scrollbar-thumb {
            background: var(--dashboard-border);
            border-radius: 4px;
        }

        .combo-list::-webkit-scrollbar-thumb:hover {
            background: var(--dashboard-text-muted);
        }

        .combo-item {
            padding: 10px 14px;
            font-size: 13px;
            color: var(--dashboard-text);
            cursor: pointer;
            transition: background .1s ease, color .1s ease;
        }

        .combo-item.is-selected {
            font-weight: 700;
        }

        .combo-item:hover,
        .combo-item.is-active {
            background: var(--dashboard-table-hover);
            color: #22c55e;
        }

        .combo-empty {
            padding: 10px;
            font-size: 12px;
            color: var(--dashboard-text-muted);
            text-align: center;
        }

        @media (max-width: 900px) {
            .data-table {
                min-width: 1000px;
            }
        }

        @media (max-width: 640px) {
            .hero {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .hero-actions {
                width: 100%;
            }

            .detail-grid,
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .toast-wrap {
                left: 16px;
                right: 16px;
                top: 16px;
            }

            .toast {
                min-width: 0;
                max-width: none;
                width: 100%;
            }
        }

        /* =========================================================
                           DARK THEME — WARNA SEMANTIK & FORCE
                           ========================================================= */

        [data-theme="dark"] .stat-icon--blue {
            background: rgba(37, 99, 235, .18);
            color: #93c5fd;
        }

        [data-theme="dark"] .stat-icon--green {
            background: rgba(34, 197, 94, .16);
            color: #4ade80;
        }

        [data-theme="dark"] .stat-icon--amber {
            background: rgba(217, 119, 6, .18);
            color: #fbbf24;
        }

        [data-theme="dark"] .detail-avatar {
            background: rgba(99, 102, 241, .18);
            color: #a5b4fc;
        }

        [data-theme="dark"] .saldo-text {
            color: #4ade80;
        }

        [data-theme="dark"] .kategori-tag {
            background: rgba(99, 102, 241, .16);
            color: #a5b4fc;
            border-color: rgba(99, 102, 241, .35);
        }

        [data-theme="dark"] .status-badge--pending {
            background: rgba(245, 158, 11, .18);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, .35);
        }

        [data-theme="dark"] .status-badge--approved {
            background: rgba(34, 197, 94, .16);
            color: #4ade80;
            border-color: rgba(34, 197, 94, .35);
        }

        [data-theme="dark"] .icon-btn--primary {
            border-color: rgba(99, 102, 241, .35);
            color: #a5b4fc;
        }

        [data-theme="dark"] .icon-btn--primary:hover {
            background: rgba(99, 102, 241, .16);
        }

        [data-theme="dark"] .form-group input::placeholder,
        [data-theme="dark"] .form-group textarea::placeholder,
        [data-theme="dark"] .combo-input::placeholder,
        [data-theme="dark"] .table-search input::placeholder {
            color: rgba(255, 255, 255, .35);
        }

        [data-theme="dark"] .modal-overlay {
            background: rgba(0, 0, 0, .65);
        }

        [data-theme="dark"] .card,
        [data-theme="dark"] .stat-card,
        [data-theme="dark"] .modal-box,
        [data-theme="dark"] .toast,
        [data-theme="dark"] .combo-list {
            background: #151d2f;
            color: #f1f5f9;
            border-color: #29364d;
        }

        [data-theme="dark"] .data-table,
        [data-theme="dark"] .data-table tbody,
        [data-theme="dark"] .data-table tr,
        [data-theme="dark"] .data-table td {
            background: #151d2f;
            color: #f1f5f9;
        }

        [data-theme="dark"] .data-table th {
            background: #111a2c;
            color: #aab6c8;
            border-color: #29364d;
        }

        [data-theme="dark"] .data-table td {
            border-color: #29364d;
        }

        [data-theme="dark"] .data-table tbody tr:hover td {
            background: #202b40;
        }
    </style>

    <!-- SCRIPTS -->
    <script>
        const DAFTAR_BANK_EWALLET = [
            'Bank BCA', 'Bank Mandiri', 'Bank BRI', 'Bank BNI', 'Bank Syariah Indonesia',
            'Bank CIMB Niaga', 'Bank Danamon', 'Bank Permata', 'Bank BTN', 'Bank Mega',
            'Bank OCBC NISP', 'Bank Panin', 'Bank Maybank Indonesia', 'Bank BTPN',
            'Bank Jago', 'SeaBank', 'Bank Neo Commerce', 'Bank DKI', 'Bank Jatim',
            'Bank BJB', 'GoPay', 'OVO', 'DANA', 'ShopeePay', 'LinkAja', 'Jenius'
        ];

        document.addEventListener('DOMContentLoaded', function() {

            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif

            const searchInput = document.getElementById('searchWarga');
            const table = document.getElementById('wargaTable');
            const perPageSelect = document.getElementById('perPageSelect');
            const paginationContainer = document.getElementById('paginationButtons');
            const tableInfo = document.getElementById('tableInfoPagination');

            let currentPage = 1;

            function applyFilters() {
                const keyword = (searchInput?.value || '').toLowerCase().trim();
                const activePill = document.querySelector('.fpill.active');
                const statusFilter = activePill ? activePill.dataset.filter : 'all';

                const allRows = Array.from(table.querySelectorAll('tbody tr[data-status]'));
                const filtered = allRows.filter(function(row) {
                    const matchSearch = !keyword || row.textContent.toLowerCase().includes(keyword);
                    const matchStatus = statusFilter === 'all' || row.dataset.status === statusFilter;
                    return matchSearch && matchStatus;
                });

                const perPage = parseInt(perPageSelect ? perPageSelect.value : 10, 10);
                const totalPages = Math.ceil(filtered.length / perPage) || 1;
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);

                allRows.forEach(r => r.style.display = 'none');

                const start = (currentPage - 1) * perPage;
                const end = start + perPage;
                filtered.slice(start, end).forEach(r => r.style.display = '');

                if (tableInfo) {
                    tableInfo.innerHTML = filtered.length === 0 ?
                        'Tidak ada data yang ditampilkan' :
                        `Menampilkan warga <strong>${start + 1}</strong> - <strong>${Math.min(end, filtered.length)}</strong> dari <strong>${filtered.length}</strong>`;
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

            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    currentPage = 1;
                    applyFilters();
                });
            }

            document.querySelectorAll('.fpill').forEach(function(pill) {
                pill.addEventListener('click', function() {
                    document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                    currentPage = 1;
                    applyFilters();
                });
            });

            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    currentPage = 1;
                    applyFilters();
                });
            }

            if (paginationContainer) {
                paginationContainer.addEventListener('click', function(e) {
                    const btn = e.target.closest('button[data-page]');
                    if (!btn || btn.disabled) return;
                    currentPage = parseInt(btn.dataset.page, 10);
                    applyFilters();
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

            initComboBank('tambahBankInput', 'tambahBank', 'listBankTambah', 'comboBankTambah');
            initComboBank('editBankInput', 'editBank', 'listBankEdit', 'comboBankEdit');
            initComboSelect('editStatusRekening');
            initComboSelect('perPageSelect');

            applyFilters();
        });

        function bukaModal(id) {
            if (id === 'modalTambah') {
                const form = document.getElementById('formTambahWarga');
                if (form) form.reset();
                resetComboBank('tambahBankInput', 'tambahBank');
            }
            document.getElementById(id).classList.add('active');
        }

        function tutupModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function formatRupiah(angka) {
            return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
        }

        function formatTanggal(tgl) {
            if (!tgl) return '-';
            const d = new Date(tgl);
            if (isNaN(d)) return tgl;
            return d.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }

        function formatStatusRekening(status) {
            return status === 'verified' ? 'Terverifikasi' : 'Belum Verifikasi';
        }

        function initComboBank(inputId, hiddenId, listId, wrapId) {
            const input = document.getElementById(inputId);
            const hidden = document.getElementById(hiddenId);
            const list = document.getElementById(listId);

            if (!input || !hidden || !list) return;

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

            function sanitize(v) {
                return v.replace(/[^a-zA-Z\s.\-]/g, '');
            }

            function render(keyword) {
                const k = (keyword || '').toLowerCase().trim();
                const hasil = DAFTAR_BANK_EWALLET.filter(b => b.toLowerCase().includes(k));
                aktif = -1;

                list.innerHTML = hasil.length ?
                    hasil.map(b => `<div class="combo-item" data-value="${escapeHtml(b)}">${escapeHtml(b)}</div>`).join(
                        '') :
                    '<div class="combo-empty">Tekan Enter untuk pakai nama ini</div>';
            }

            function pilih(value) {
                hidden.value = value;
                input.value = value;
                list.classList.remove('show');
            }

            input.addEventListener('focus', function() {
                render(this.value);
                list.classList.add('show');
            });

            input.addEventListener('input', function() {
                const bersih = sanitize(this.value);
                if (bersih !== this.value) this.value = bersih;
                hidden.value = this.value;
                render(this.value);
                list.classList.add('show');
            });

            input.addEventListener('blur', function() {
                setTimeout(() => {
                    hidden.value = sanitize(input.value.trim());
                }, 150);
            });

            function sorot(arah) {
                const items = list.querySelectorAll('.combo-item');
                if (!items.length) return;
                aktif = (aktif + arah + items.length) % items.length;
                items.forEach((el, i) => el.classList.toggle('is-active', i === aktif));
                items[aktif].scrollIntoView({
                    block: 'nearest'
                });
            }

            input.addEventListener('keydown', function(e) {
                const items = list.querySelectorAll('.combo-item');

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
                    e.preventDefault();
                    if (list.classList.contains('show') && aktif > -1 && items[aktif]) {
                        pilih(items[aktif].dataset.value);
                    } else {
                        hidden.value = sanitize(this.value.trim());
                        list.classList.remove('show');
                    }
                } else if (e.key === 'Escape' && list.classList.contains('show')) {
                    e.stopPropagation();
                    list.classList.remove('show');
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

            document.addEventListener('click', function(e) {
                if (!e.target.closest('#' + wrapId)) list.classList.remove('show');
            });
        }

        const comboSelectRegistry = {};

        function syncComboSelect(selectId) {
            if (comboSelectRegistry[selectId]) comboSelectRegistry[selectId]();
        }

        function initComboSelect(selectId) {
            const select = document.getElementById(selectId);
            const input = document.getElementById('cari_' + selectId);
            const list = document.getElementById('list_' + selectId);
            if (!select || !input || !list) return;

            input.readOnly = true;
            let aktif = -1;

            const teks = o => o.textContent.replace(/\s+/g, ' ').trim();
            const esc = v => String(v).replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));

            function sync() {
                const o = select.options[select.selectedIndex];
                input.value = o ? teks(o) : '';
            }

            function buka() {
                list.innerHTML = Array.from(select.options).map(o =>
                    `<div class="combo-item${o.value === select.value ? ' is-selected' : ''}" data-value="${esc(o.value)}">${esc(teks(o))}</div>`
                ).join('');
                aktif = -1;
                list.classList.add('show');
            }

            function tutup() {
                list.classList.remove('show');
            }

            function pilih(value) {
                const berubah = select.value !== String(value);
                select.value = value;
                sync();
                tutup();
                if (berubah) select.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            }

            function sorot(arah) {
                const items = list.querySelectorAll('.combo-item');
                if (!items.length) return;
                aktif = (aktif + arah + items.length) % items.length;
                items.forEach((el, i) => el.classList.toggle('is-active', i === aktif));
            }

            input.addEventListener('focus', buka);
            input.addEventListener('click', () => {
                if (!list.classList.contains('show')) buka();
            });
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
                    e.stopPropagation();
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

        function resetComboBank(inputId, hiddenId) {
            const input = document.getElementById(inputId);
            const hidden = document.getElementById(hiddenId);
            if (input) input.value = '';
            if (hidden) hidden.value = '';
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
                <button class="toast-close" type="button">&times;</button>
            `;

            function hapusToast() {
                toast.classList.add('toast--leaving');
                setTimeout(() => toast.remove(), 180);
            }

            toast.querySelector('.toast-close').addEventListener('click', hapusToast);
            wrap.appendChild(toast);
            setTimeout(hapusToast, 3500);
        }

        function simpanTambahWarga(event) {
            event.preventDefault();
            const payload = {
                nik: document.getElementById('tambahNik').value,
                nama: document.getElementById('tambahNama').value,
                no_hp: document.getElementById('tambahHp').value,
                jumlah_anggota_keluarga: document.getElementById('tambahKeluarga').value,
                alamat: document.getElementById('tambahAlamat').value,
                nama_bank_ewallet: document.getElementById('tambahBank').value,
                nomor_rekening: document.getElementById('tambahNoRekening').value,
                nama_pemilik_rekening: document.getElementById('tambahPemilikRekening').value,
            };

            fetch(`{{ route('admin.warga.store') }}`, {
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
                        throw new Error(err.message || 'Gagal menyimpan data warga.');
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalTambah');
                    document.getElementById('formTambahWarga').reset();
                    resetComboBank('tambahBankInput', 'tambahBank');
                    showToast('Berhasil', 'Data warga baru telah ditambahkan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menyimpan', err.message, 'error');
                });

            return false;
        }

        function lihatWarga(warga) {
            const nama = warga.nama || '-';
            document.getElementById('detailAvatar').textContent = nama.trim().charAt(0).toUpperCase();
            document.getElementById('detailNama').textContent = nama;
            document.getElementById('detailNik').textContent = warga.nik || '-';
            document.getElementById('detailHp').textContent = warga.no_hp || '-';
            document.getElementById('detailKeluarga').textContent = (warga.jumlah_anggota_keluarga ?? 0) + ' orang';
            document.getElementById('detailSaldo').textContent = formatRupiah(warga.saldo);
            document.getElementById('detailTanggal').textContent = formatTanggal(warga.tanggal_daftar);
            document.getElementById('detailAlamat').textContent = warga.alamat || '-';
            document.getElementById('detailBank').textContent = warga.nama_bank_ewallet || '-';
            document.getElementById('detailNoRekening').textContent = warga.nomor_rekening || '-';
            document.getElementById('detailPemilikRekening').textContent = warga.nama_pemilik_rekening || '-';
            document.getElementById('detailStatusRekening').textContent = warga.nama_bank_ewallet ? formatStatusRekening(
                warga.status_rekening) : '-';

            bukaModal('modalDetail');
        }

        function editWarga(warga) {
            document.getElementById('editId').value = warga.id_warga;
            document.getElementById('editNik').value = warga.nik || '';
            document.getElementById('editNama').value = warga.nama || '';
            document.getElementById('editHp').value = warga.no_hp || '';
            document.getElementById('editKeluarga').value = warga.jumlah_anggota_keluarga || 0;
            document.getElementById('editSaldo').value = warga.saldo || 0;
            document.getElementById('editTanggal').value = warga.tanggal_daftar ? warga.tanggal_daftar.substring(0, 10) :
                '';
            document.getElementById('editAlamat').value = warga.alamat || '';
            document.getElementById('editBankInput').value = warga.nama_bank_ewallet || '';
            document.getElementById('editBank').value = warga.nama_bank_ewallet || '';
            document.getElementById('editNoRekening').value = warga.nomor_rekening || '';
            document.getElementById('editPemilikRekening').value = warga.nama_pemilik_rekening || '';
            document.getElementById('editStatusRekening').value = warga.status_rekening || 'unverified';
            syncComboSelect('editStatusRekening');

            bukaModal('modalEdit');
        }

        function simpanWarga(event) {
            event.preventDefault();
            const id = document.getElementById('editId').value;
            const payload = {
                nik: document.getElementById('editNik').value,
                nama: document.getElementById('editNama').value,
                no_hp: document.getElementById('editHp').value,
                jumlah_anggota_keluarga: document.getElementById('editKeluarga').value,
                saldo: document.getElementById('editSaldo').value,
                tanggal_daftar: document.getElementById('editTanggal').value,
                alamat: document.getElementById('editAlamat').value,
                nama_bank_ewallet: document.getElementById('editBank').value,
                nomor_rekening: document.getElementById('editNoRekening').value,
                nama_pemilik_rekening: document.getElementById('editPemilikRekening').value,
                status_rekening: document.getElementById('editStatusRekening').value,
            };

            fetch(`/admin/pages/warga/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        // Tangkap pesan error validasi dari Laravel (jika ada errors bagikan pesan pertamanya)
                        let errorMsg = data.message || 'Gagal memperbarui data warga.';
                        if (data.errors) {
                            const firstKey = Object.keys(data.errors)[0];
                            if (firstKey && data.errors[firstKey][0]) {
                                errorMsg = data.errors[firstKey][0];
                            }
                        }
                        throw new Error(errorMsg);
                    }
                    return data;
                })
                .then(() => {
                    tutupModal('modalEdit');
                    showToast('Berhasil disimpan', 'Perubahan data warga telah tersimpan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal Menyimpan', err.message, 'error');
                });

            return false;
        }

        let wargaAkanDihapus = null;

        function hapusWarga(warga) {
            wargaAkanDihapus = warga;
            document.getElementById('hapusNama').textContent = warga.nama || '-';
            document.getElementById('hapusNik').textContent = warga.nik || '-';
            bukaModal('modalHapus');
        }

        function konfirmasiHapus() {
            if (!wargaAkanDihapus) return;

            const id = wargaAkanDihapus.id_warga;
            const namaDihapus = wargaAkanDihapus.nama || 'Warga';

            fetch(`/admin/pages/warga/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Gagal menghapus data.');
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalHapus');
                    showToast('Data warga berhasil dihapus.', namaDihapus + ' telah dihapus dari data warga.');
                    wargaAkanDihapus = null;
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menghapus', err.message, 'error');
                });
        }
    </script>
@endsection
