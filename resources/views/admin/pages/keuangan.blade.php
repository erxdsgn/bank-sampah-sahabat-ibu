@extends('admin.layouts.app')

@section('title', 'Keuangan')
@section('active', 'keuangan')
@section('crumbs', 'Keuangan & Produk | Keuangan')

@push('styles')
    @vite('resources/css/pages/keuangan.css')
@endpush

@section('content')
<div class="keuangan-page">

    @php
        $totalPemasukanTampil   = (float) $transaksi->where('jenis', 'Pemasukan')->sum('jumlah');
        $totalPengeluaranTampil = (float) $transaksi->where('jenis', 'Pengeluaran')->sum('jumlah');
        $saldoKasTampil         = $totalPemasukanTampil - $totalPengeluaranTampil;
        $jumlahTransaksiTampil  = $transaksi->count();
    @endphp

    {{-- =========================================================
         HERO SECTION
    ========================================================== --}}
    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">KEUANGAN & PRODUK</span>
            <h1 class="hero-title">
                Kelola <span class="accent">Keuangan</span>
            </h1>
            <p class="hero-sub">
                Transaksi otomatis dari setoran, pencairan saldo, dan penjualan ke pengepul.
            </p>
        </div>

        <div class="hero-actions">
            <button class="btn btn--primary" type="button" onclick="bukaModalTambah()">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"></path>
                </svg>
                Tambah Transaksi
            </button>
        </div>
    </section>

    {{-- =========================================================
         SUMMARY CARDS
    ========================================================== --}}
    <section class="summary-grid">
        {{-- Total Pemasukan --}}
        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Total Pemasukan</div>
                    <div class="summary-value">Rp {{ number_format($totalPemasukanTampil, 0, ',', '.') }}</div>
                </div>
                <div class="summary-icon summary-icon--income">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3v18"></path>
                        <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Akumulasi seluruh transaksi pemasukan</div>
        </div>

        {{-- Total Pengeluaran --}}
        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Total Pengeluaran</div>
                    <div class="summary-value">Rp {{ number_format($totalPengeluaranTampil, 0, ',', '.') }}</div>
                </div>
                <div class="summary-icon summary-icon--expense">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3v18"></path>
                        <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Akumulasi seluruh transaksi pengeluaran</div>
        </div>

        {{-- Saldo Kas --}}
        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Saldo Kas</div>
                    <div class="summary-value">{{ $saldoKasTampil < 0 ? '- ' : '' }}Rp {{ number_format(abs($saldoKasTampil), 0, ',', '.') }}</div>
                </div>
                <div class="summary-icon summary-icon--cash">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                        <path d="M3 10h18"></path>
                        <path d="M16 15h2"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Pemasukan dikurangi pengeluaran</div>
        </div>

        {{-- Jumlah Transaksi --}}
        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Jumlah Transaksi</div>
                    <div class="summary-value">{{ number_format($jumlahTransaksiTampil, 0, ',', '.') }}</div>
                </div>
                <div class="summary-icon summary-icon--transaction">
                    <svg viewBox="0 0 24 24">
                        <path d="M8 6h13"></path>
                        <path d="M8 12h13"></path>
                        <path d="M8 18h13"></path>
                        <path d="M3 6h.01"></path>
                        <path d="M3 12h.01"></path>
                        <path d="M3 18h.01"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Total transaksi yang tercatat</div>
        </div>
    </section>

    {{-- =========================================================
         TABLE CARD & TOOLBAR
    ========================================================== --}}
    <section class="card keuangan-card">
        <div class="table-toolbar">
            <div class="table-search">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchKeuangan" placeholder="Cari kategori atau keterangan..." autocomplete="off">
            </div>

            <div class="toolbar-right">
                {{-- Filter Sumber --}}
                <div class="combo combo--arrow" id="combo_filterSumber">
                    <input type="text" id="cari_filterSumber" class="combo-input" autocomplete="off" aria-label="Filter sumber">
                    <div class="combo-list" id="list_filterSumber"></div>
                </div>
                <select id="filterSumber" class="combo-hidden" tabindex="-1" aria-hidden="true">
                    <option value="">Semua Sumber</option>
                    <option value="kas">Kas Manual</option>
                    <option value="setoran">Setoran</option>
                    <option value="pencairan">Pencairan Saldo</option>
                    <option value="penjualan">Penjualan Pengepul</option>
                </select>

                {{-- Filter Jenis --}}
                <div class="combo combo--arrow" id="combo_filterJenis">
                    <input type="text" id="cari_filterJenis" class="combo-input" autocomplete="off" aria-label="Filter jenis">
                    <div class="combo-list" id="list_filterJenis"></div>
                </div>
                <select id="filterJenis" class="combo-hidden" tabindex="-1" aria-hidden="true">
                    <option value="">Semua Jenis</option>
                    <option value="Pemasukan">Pemasukan</option>
                    <option value="Pengeluaran">Pengeluaran</option>
                </select>

                {{-- Filter Periode --}}
                <div class="combo combo--arrow" id="combo_filterPeriode">
                    <input type="text" id="cari_filterPeriode" class="combo-input" autocomplete="off" aria-label="Filter periode">
                    <div class="combo-list" id="list_filterPeriode"></div>
                </div>
                <select id="filterPeriode" class="combo-hidden" tabindex="-1" aria-hidden="true">
                    <option value="">Semua Periode</option>
                    @foreach ($periodeList as $periode)
                        <option value="{{ $periode }}">{{ $periode }}</option>
                    @endforeach
                </select>

                <button class="btn btn--ghost" type="button" onclick="window.location.reload()">
                    <svg viewBox="0 0 24 24">
                        <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                        <path d="M21 3v5h-5"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        {{-- TABLE CONTENT --}}
        <div class="table-responsive">
            <table class="data-table" id="keuanganTable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="keuanganTableBody">
                    @php
                        $sortedTransaksi = $transaksi->sort(function ($a, $b) {
                            if ($a->tanggal === $b->tanggal) {
                                return ($b->id_kas ?? 0) <=> ($a->id_kas ?? 0);
                            }
                            return $b->tanggal <=> $a->tanggal;
                        });
                    @endphp

                    @forelse ($sortedTransaksi as $item)
                        <tr data-jenis="{{ $item->jenis }}" data-periode="{{ $item->periode }}" data-sumber="{{ $item->sumber }}">
                            <td class="nowrap cell-text">
                                {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge {{ $item->jenis === 'Pemasukan' ? 'badge--success' : 'badge--danger' }}">
                                    {{ $item->jenis }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $item->kategori }}</strong>
                            </td>
                            <td class="description-cell cell-text">
                                {{ $item->keterangan ?? '-' }}
                            </td>
                            <td>
                                <strong class="saldo {{ $item->jenis === 'Pemasukan' ? 'amount-in' : 'amount-out' }}">
                                    {{ $item->jenis === 'Pemasukan' ? '+' : '-' }}
                                    Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>
                                <div class="table-actions">
                                    {{-- Detail --}}
                                    <button class="icon-btn" title="Lihat Detail" type="button" onclick="lihatDetail({{ Js::from($item) }})">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                            <circle cx="12" cy="12" r="2.5"></circle>
                                        </svg>
                                    </button>

                                    {{-- Hapus (hanya untuk kas manual) --}}
                                    @if (empty($item->otomatis))
                                        <button class="icon-btn danger" title="Hapus Transaksi" type="button" onclick="hapusTransaksi({{ Js::from($item) }})">
                                            <svg viewBox="0 0 24 24">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                                <path d="M10 11v6M14 11v6"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-table-row">
                            <td colspan="6">
                                <div class="table-empty-content">
                                    <div class="empty-icon">📋</div>
                                    <strong>Belum Ada Transaksi</strong>
                                    <p>Belum ada transaksi keuangan yang tercatat.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Empty Search State --}}
            <div class="catalog-empty" id="keuanganKosongCari" style="display: none;">
                <div class="empty-icon">🔍</div>
                <strong>Data Tidak Ditemukan</strong>
                <p>Tidak ada transaksi yang cocok dengan pencarian atau filter ini.</p>
            </div>
        </div>

        {{-- TABLE FOOTER & PAGINATION --}}
        <div class="table-footer">
            <div class="table-info" id="tableInfoPagination">Menampilkan data...</div>

            <div class="table-pagination-controls">
                <div class="per-page">
                    <label for="cari_perPageSelect">Transaksi per halaman:</label>
                    <div class="combo combo--up combo--arrow" id="combo_perPageSelect">
                        <input type="text" id="cari_perPageSelect" class="combo-input" placeholder="" autocomplete="off">
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

    {{-- =========================================================
         TOAST CONTAINER
    ========================================================== --}}
    <div class="toast-wrap" id="keuanganToastWrap"></div>

    {{-- =========================================================
         MODAL TAMBAH TRANSAKSI
    ========================================================== --}}
    <div class="modal-overlay" id="modalTransaksi">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Transaksi
                </h3>
                <button class="modal-close" type="button" aria-label="Tutup" onclick="tutupModal('modalTransaksi')">&times;</button>
            </div>

            <form id="formTransaksi" class="modal-form" onsubmit="return simpanTransaksi(event)">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="cari_transaksiJenis">Jenis Transaksi</label>
                            <div class="combo combo--arrow" id="combo_transaksiJenis">
                                <input type="text" id="cari_transaksiJenis" class="combo-input" placeholder="Pilih jenis transaksi" readonly autocomplete="off">
                                <div class="combo-list" id="list_transaksiJenis"></div>
                            </div>
                            <select id="transaksiJenis" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="Pemasukan">Pemasukan</option>
                                <option value="Pengeluaran">Pengeluaran</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="transaksiTanggal">Tanggal</label>
                            <input type="date" id="transaksiTanggal" value="{{ now()->format('Y-m-d') }}" readonly required>
                        </div>

                        <div class="form-group">
                            <label for="transaksiKategori">Kategori</label>
                            <input type="text" id="transaksiKategori" placeholder="Contoh: Operasional" maxlength="100" required>
                        </div>

                        <div class="form-group">
                            <label for="transaksiJumlah">Jumlah (Rp)</label>
                            <input type="text" id="transaksiJumlah" inputmode="numeric" placeholder="0" autocomplete="off" required>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="transaksiKeterangan">Keterangan</label>
                            <textarea id="transaksiKeterangan" rows="3" placeholder="Tambahkan keterangan transaksi..." required></textarea>
                        </div>
                    </div>

                    <p class="form-note">
                        Catatan: transaksi yang sudah disimpan tidak dapat diedit, hanya dapat dilihat atau dihapus. Maksimal nominal Rp 50.000.000.
                    </p>
                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalTransaksi')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- =========================================================
         MODAL DETAIL TRANSAKSI
    ========================================================== --}}
    <div class="modal-overlay" id="modalDetail">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                        <circle cx="12" cy="12" r="2.5"></circle>
                    </svg>
                    Detail Transaksi
                </h3>
                <button class="modal-close" type="button" aria-label="Tutup" onclick="tutupModal('modalDetail')">&times;</button>
            </div>

            <div class="modal-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Jenis</span>
                        <span id="detailJenis">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tanggal</span>
                        <span class="detail-value" id="detailTanggal">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Kategori</span>
                        <span class="detail-value" id="detailKategori">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Sumber</span>
                        <span class="detail-value" id="detailSumber">-</span>
                    </div>
                    <div class="detail-item detail-item--full">
                        <span class="detail-label">Jumlah</span>
                        <span class="detail-value saldo" id="detailJumlah">-</span>
                    </div>
                    <div class="detail-item detail-item--full">
                        <span class="detail-label">Keterangan</span>
                        <span class="detail-value" id="detailKeterangan">-</span>
                    </div>
                </div>
            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalDetail')">Tutup</button>
            </div>
        </div>
    </div>

    {{-- =========================================================
         MODAL HAPUS TRANSAKSI
    ========================================================== --}}
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
                <h3 class="confirm-title">Hapus Transaksi?</h3>
                <p class="confirm-text">
                    Anda akan menghapus transaksi <strong id="hapusKategori">-</strong> sebesar <span id="hapusJumlah">-</span>. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="modal-foot modal-foot--center">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHapus')">Batal</button>
                <button class="btn btn--danger" type="button" onclick="konfirmasiHapusTransaksi()">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.__KEUANGAN_DATA__ = {
            saldoKas: @json($saldoKasTampil),
            storeUrl: "{{ route('admin.keuangan.store') }}",
            flashSuccess: @json(session('success')),
            flashError: @json(session('error'))
        };
    </script>
    @vite('resources/js/pages/keuangan.js')
@endpush
