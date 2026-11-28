@extends('admin.layouts.app')

@section('title', 'Data Warga')
@section('active', 'warga')
@section('crumbs', 'Master Data | Data Warga')

@push('styles')
    @vite('resources/css/pages/warga.css')
@endpush

@section('content')
    <div class="warga-page">

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
                <button class="btn btn--ghost" type="button" onclick="bukaModalLeaderboard('bulan_ini')">
                    <svg viewBox="0 0 24 24">
                        <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                        <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                        <path d="M4 22h16"></path>
                        <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                        <path d="M14 14.66V17c0 .55-.47.98-.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                        <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                    </svg>
                    Leaderboard Poin
                </button>

                <button class="btn btn--primary" type="button" onclick="bukaModal('modalTambah')">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Warga
                </button>
            </div>
        </section>

        <!-- STATS SUMMARY (4 CARD) -->
        <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
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
                    <span class="stat-label">Total Warga Aktif</span>
                    <span class="stat-value">{{ $warga->where('status', '!=', 'tidak_aktif')->count() }} aktif</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon--red" style="background-color: #fee2e2; color: #ef4444;">
                    <svg viewBox="0 0 24 24">
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"></path>
                        <path d="M19 8v6M16 11h6"></path>
                    </svg>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Warga Nonaktif</span>
                    <span class="stat-value">{{ $warga->where('status', 'tidak_aktif')->count() }} warga</span>
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
            <!-- TOOLBAR / SEARCH + FILTER (DROPDOWN) -->
            <div class="table-toolbar">
                <div class="table-search">
                    <svg viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input type="text" id="searchWarga" placeholder="Cari NIK, nama, atau HP..." autocomplete="off">
                </div>

                <div class="toolbar-right">
                    <!-- Dropdown Filter Status Aktif/Nonaktif -->
                    <div class="combo combo--arrow" id="comboFilterStatus">
                        <select id="filterStatusWarga" class="combo-hidden" tabindex="-1" aria-hidden="true">
                            <option value="all">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif</option>
                        </select>
                        <input type="text" id="cari_filterStatusWarga" class="combo-input" placeholder="Status Warga"
                            readonly>
                        <div class="combo-list" id="list_filterStatusWarga"></div>
                    </div>

                    <!-- Dropdown Filter Rekening -->
                    <div class="combo combo--arrow" id="comboFilterRek">
                        <select id="filterRekening" class="combo-hidden" tabindex="-1" aria-hidden="true">
                            <option value="all">Semua Rekening</option>
                            <option value="verified">Terverifikasi</option>
                            <option value="unverified">Belum Verifikasi</option>
                        </select>
                        <input type="text" id="cari_filterRekening" class="combo-input" placeholder="Status Rekening"
                            readonly>
                        <div class="combo-list" id="list_filterRekening"></div>
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-no-scroll">
                <table class="data-table" id="wargaTable">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th>Warga</th>
                            <th>No. HP</th>
                            <th>Kelompok</th>
                            <th>Status</th>
                            <th>Saldo</th>
                            <th>Rekening</th>
                            <th class="col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warga as $index => $item)
                            @php($isNonaktif = ($item->status ?? 'aktif') === 'tidak_aktif')
                            <tr data-status="{{ $item->nama_bank_ewallet ? $item->status_rekening : 'none' }}"
                                data-warga-status="{{ $isNonaktif ? 'tidak_aktif' : 'aktif' }}">
                                <td class="col-no">
                                    <span class="text-subtle no-urut">{{ $index + 1 }}</span>
                                </td>
                                <td>
                                    <strong class="user-name" title="{{ $item->nama }}">{{ $item->nama }}</strong>
                                    <div class="mono text-subtle subline-mt">{{ $item->nik }}</div>
                                </td>
                                <td>{{ $item->no_hp ?? '-' }}</td>
                                <td>
                                    @if (!empty($item->kelompok))
                                        <span class="kelompok-text">{{ $item->kelompok }}</span>
                                    @else
                                        <span class="text-subtle">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($isNonaktif)
                                        <span class="status-badge status-badge--pending">Tidak Aktif</span>
                                    @else
                                        <span class="status-badge status-badge--approved">Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="saldo-text">
                                        Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}
                                    </strong>
                                </td>
                                <td>
                                    @if ($item->nama_bank_ewallet)
                                        <div class="bank-info">
                                            <div class="bank-main">
                                                <span
                                                    class="status-badge status-badge--icon {{ $item->status_rekening === 'verified' ? 'status-badge--approved' : 'status-badge--pending' }}"
                                                    title="{{ $item->status_rekening === 'verified' ? 'Terverifikasi' : 'Belum Verifikasi' }}">
                                                    @if ($item->status_rekening === 'verified')
                                                        <svg viewBox="0 0 24 24">
                                                            <path d="M20 6 9 17l-5-5"></path>
                                                        </svg>
                                                    @else
                                                        <svg viewBox="0 0 24 24">
                                                            <circle cx="12" cy="12" r="9"></circle>
                                                            <path d="M12 7v5l3 2"></path>
                                                        </svg>
                                                    @endif
                                                </span>
                                                <strong
                                                    title="{{ $item->nama_bank_ewallet }}">{{ $item->nama_bank_ewallet }}</strong>
                                            </div>
                                            <span
                                                class="mono text-subtle bank-number">{{ $item->nomor_rekening ?? '-' }}</span>
                                        </div>
                                    @else
                                        <span class="text-subtle">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions table-actions--center">
                                        <button class="icon-btn" title="Lihat Detail" type="button"
                                            onclick='lihatWarga(@json($item))'>
                                            <svg viewBox="0 0 24 24">
                                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                                <circle cx="12" cy="12" r="2.5"></circle>
                                            </svg>
                                        </button>
                                        <button class="icon-btn icon-btn--edit" title="Ubah Status Rekening"
                                            type="button" onclick='editWarga(@json($item))'>
                                            <svg viewBox="0 0 24 24">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                            </svg>
                                        </button>
                                        <button class="icon-btn icon-btn--danger"
                                            title="{{ $isNonaktif ? 'Aktifkan Kembali Warga' : 'Nonaktifkan Warga' }}"
                                            type="button" onclick='hapusWarga(@json($item))'>
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
                                <td colspan="8" class="empty-state-cell">
                                    <div class="empty-icon">📋</div>
                                    <strong>Belum Ada Data Warga</strong>
                                    <p class="empty-desc">Belum ada masyarakat yang terdaftar di Bank Sampah.</p>
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
                    <h3 class="modal-title"><svg viewBox="0 0 24 24">
                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                            <circle cx="12" cy="12" r="2.5"></circle>
                        </svg>Detail Warga</h3>
                    <button class="modal-close" type="button" onclick="tutupModal('modalDetail')">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="detail-avatar-row">
                        <div class="detail-avatar" id="detailAvatar">-</div>
                        <div>
                            <div class="detail-nama" id="detailNama">-</div>
                            <div class="detail-nik-wrap"><span class="kategori-tag detail-nik-badge"
                                    id="detailNik">-</span></div>
                        </div>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item"><span class="detail-label">Kelompok Domisili</span><span
                                class="detail-value" id="detailKelompok">-</span></div>
                        <div class="detail-item"><span class="detail-label">No. HP</span><span class="detail-value"
                                id="detailHp">-</span></div>
                        <div class="detail-item"><span class="detail-label">Anggota Keluarga</span><span
                                class="detail-value" id="detailKeluarga">-</span></div>
                        <div class="detail-item"><span class="detail-label">Saldo</span><span
                                class="detail-value saldo-text" id="detailSaldo">-</span></div>
                        <div class="detail-item"><span class="detail-label">Tanggal Daftar</span><span
                                class="detail-value" id="detailTanggal">-</span></div>
                        <div class="detail-item detail-item--full"><span class="detail-label">Alamat</span><span
                                class="detail-value" id="detailAlamat">-</span></div>
                        <div class="detail-item"><span class="detail-label">Bank / E-Wallet</span><span
                                class="detail-value" id="detailBank">-</span></div>
                        <div class="detail-item"><span class="detail-label">No. Rekening</span><span
                                class="detail-value mono" id="detailNoRekening">-</span></div>
                        <div class="detail-item"><span class="detail-label">Nama Pemilik</span><span class="detail-value"
                                id="detailPemilikRekening">-</span></div>
                        <div class="detail-item"><span class="detail-label">Status Rekening</span><span
                                class="detail-value" id="detailStatusRekening">-</span></div>
                    </div>
                </div>
                <div class="modal-foot"><button class="btn btn--ghost" type="button"
                        onclick="tutupModal('modalDetail')">Tutup</button></div>
            </div>
        </div>

        <!-- MODAL: TAMBAH WARGA -->
        <div class="modal-overlay" id="modalTambah">
            <div class="modal-box">
                <div class="modal-head">
                    <h3 class="modal-title"><svg viewBox="0 0 24 24">
                            <path d="M12 5v14M5 12h14"></path>
                        </svg>Tambah Warga</h3>
                    <button class="modal-close" type="button" onclick="tutupModal('modalTambah')">&times;</button>
                </div>
                <form id="formTambahWarga" onsubmit="return simpanTambahWarga(event)">
                    <div class="modal-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>NIK</label>
                                <input type="text" id="tambahNik" class="form-control" inputmode="numeric"
                                    maxlength="16" required placeholder="Masukkan NIK 16 digit" autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" id="tambahNama" class="form-control" required
                                    placeholder="Nama lengkap warga">
                            </div>

                            <div class="form-group">
                                <label>Email Login</label>
                                <input type="email" id="tambahEmail" class="form-control" required
                                    placeholder="contoh@gmail.com">
                            </div>

                            <div class="form-group">
                                <label>Password Login</label>
                                <input type="password" id="tambahPassword" class="form-control" minlength="8" required
                                    placeholder="Minimal 8 karakter">
                            </div>

                            <div class="form-group">
                                <label>No. HP / WA</label>
                                <input type="text" id="tambahHp" class="form-control" inputmode="numeric"
                                    maxlength="14" required placeholder="08  atau 628" autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label>Kelompok Warga</label>
                                <div class="combo combo--arrow" id="comboKelompok">
                                    <select id="tambahKelompok" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                        <option value="">Pilih Kelompok...</option>
                                        <option value="A (Perumahan)">A (Perumahan)</option>
                                        <option value="B (Luar Perumahan)">B (Luar Perumahan)</option>
                                    </select>
                                    <input type="text" id="cari_tambahKelompok" class="combo-input"
                                        placeholder="Pilih Kelompok..." readonly>
                                    <div class="combo-list" id="list_tambahKelompok"></div>
                                </div>
                            </div>

                            <div class="form-group"><label>Anggota Keluarga</label><input type="number"
                                    id="tambahKeluarga" class="form-control" min="1" value="1"></div>

                            <div class="form-group">
                                <label>Bank / E-Wallet</label>
                                <div class="combo combo--arrow" id="comboBankTambah">
                                    <select id="tambahBank" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                        <option value="">Pilih Bank / E-Wallet...</option>
                                    </select>
                                    <input type="text" id="cari_tambahBank" class="combo-input"
                                        placeholder="Pilih Bank / E-Wallet..." readonly>
                                    <div class="combo-list" id="list_tambahBank"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>No. Rekening</label>
                                <input type="text" id="tambahNoRekening" class="form-control" inputmode="numeric"
                                    maxlength="19" placeholder="0000 0000 0000 0000" autocomplete="off">
                            </div>
                            <div class="form-group"><label>Nama Pemilik Rekening</label><input type="text"
                                    id="tambahPemilikRekening" class="form-control"
                                    placeholder="Nama pada akun rekening / e-wallet"></div>
                            <div class="form-group form-group--full"><label>Alamat</label>
                                <textarea id="tambahAlamat" class="form-control" rows="3" required placeholder="Alamat domisili lengkap..."></textarea>
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
                    <h3 class="modal-title"><svg viewBox="0 0 24 24">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                        </svg>Status Rekening</h3>
                    <button class="modal-close" type="button" onclick="tutupModal('modalEdit')">&times;</button>
                </div>
                <form id="formEditWarga" onsubmit="return simpanWarga(event)">
                    <div class="modal-body">
                        <input type="hidden" id="editId">
                        <input type="hidden" id="editStatusRekening" value="unverified">
                        <div class="edit-ringkasan" id="editRingkasan"></div>
                        <div class="form-group edit-status-group">
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
                    <div class="confirm-icon"><svg viewBox="0 0 24 24">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                            <path d="M10 11v6M14 11v6"></path>
                        </svg></div>
                    <h3 class="confirm-title">Nonaktifkan Data Warga?</h3>
                    <p class="confirm-text">Anda akan mengubah status <strong id="hapusNama">-</strong> (NIK: <span
                            class="mono" id="hapusNik">-</span>) menjadi <strong>Tidak Aktif</strong>. Riwayat setoran
                        tetap disimpan.</p>
                </div>
                <div class="modal-foot modal-foot--center">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHapus')">Batal</button>
                    <button class="btn btn--danger" type="button" onclick="konfirmasiHapus()">Ya, Nonaktifkan</button>
                </div>
            </div>
        </div>

        <!-- MODAL: LEADERBOARD POIN -->
        <div class="modal-overlay" id="modalLeaderboard">
            <div class="modal-box" id="modalLeaderboardContent"></div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        window.__WARGA_DATA__ = {
            flashSuccess: @json(session('success')),
            leaderboardPopupUrl: @json(route('admin.warga.leaderboard-popup')),
            storeUrl: @json(route('admin.warga.store'))
        };
    </script>
    @vite('resources/js/pages/warga.js')
@endpush
