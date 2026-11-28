@extends('admin.layouts.app')

@section('title', 'Penjualan Sampah')
@section('active', 'penjualan-pengepul')
@section('crumbs', 'Transaksi Sampah | Penjualan Sampah')

@push('styles')
    @vite('resources/css/pages/penjualan-pengepul.css')
@endpush

@section('content')
<div class="penjualan-page">

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">TRANSAKSI SAMPAH</span>
            <h1 class="hero-title">Penjualan <span class="accent">Sampah</span></h1>
            <p class="hero-sub">Catat transaksi penjualan sampah bank sampah ke pengepul.</p>
        </div>

        <div class="hero-actions">
            <button class="btn btn--primary" type="button" onclick="bukaModal('modalTambahPenjualan')">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"></path>
                </svg>
                Tambah Penjualan
            </button>
        </div>
    </section>

    <section class="card">
        <!-- SEARCH TABLE -->
        <div class="table-toolbar">
            <div class="table-search">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchPenjualan" placeholder="Cari nama pengepul, kategori, atau tanggal..."
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

        <!-- TABLE PENJUALAN -->
        <div class="table-responsive">
            <table class="data-table" id="penjualanTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Pengepul</th>
                        <th>Kategori Sampah</th>
                        <th>Kuantitas</th>
                        <th>Harga Satuan</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barangKeluar as $index => $item)
                        @php
                            $satuan  = strtolower($item->kategori->satuan ?? 'kg');
                            $desimal = in_array($satuan, ['kg', 'liter'], true) ? 2 : 0;
                            $berat   = (float) ($item->berat ?? 0);
                        @endphp
                        <tr data-row-penjualan>
                            <td class="no-urut">{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal ?? $item->created_at)->format('d/m/Y') }}</td>
                            <td><strong>{{ $item->pembeli ?? 'Umum' }}</strong></td>
                            <td>{{ $item->kategori->nama_kategori ?? '-' }}</td>
                            <td>{{ number_format($berat, $desimal, ',', '.') }} {{ $item->kategori->satuan ?? 'kg' }}</td>
                            <td>Rp {{ number_format($item->harga_jual_satuan ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <strong class="saldo">
                                    Rp {{ number_format($item->total ?? 0, 0, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-table">
                                <div class="empty-icon">🗑️</div>
                                <strong>Belum Ada Transaksi Penjualan</strong>
                                <p>Silakan catat penjualan sampah baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="empty-table empty-search" id="penjualanKosongCari">
                <div class="empty-icon">🔍</div>
                <strong>Data Tidak Ditemukan</strong>
                <p>Tidak ada transaksi yang cocok dengan pencarian ini.</p>
            </div>
        </div>

        <div class="table-footer">
            <div class="table-info" id="tableInfoPagination">Menampilkan data...</div>

            <div class="table-pagination-controls">
                <div class="per-page">
                    <label for="cari_perPageSelect">Transaksi per halaman:</label>
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

    <!-- MODAL TAMBAH PENJUALAN -->
    <div class="modal-overlay" id="modalTambahPenjualan">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Transaksi Penjualan ke Pengepul
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalTambahPenjualan')">&times;</button>
            </div>

            <form id="formTambahPenjualan" onsubmit="return simpanPenjualan(event)">
                <div class="modal-body">
                    <div class="form-grid">

                        <!-- NAMA PENGEPUL -->
                        <div class="form-group form-group--full">
                            <label for="tambahPembeli">Nama Pengepul</label>
                            <input type="text" id="tambahPembeli" placeholder="Masukkan nama pengepul / pembeli..."
                                required autocomplete="off">
                        </div>

                        <!-- PILIH KATEGORI SAMPAH -->
                        <div class="form-group form-group--full">
                            <label for="cariKategori">Kategori Sampah</label>
                            <div class="combo" id="comboKategori">
                                <input type="text" id="cariKategori" class="combo-input"
                                    placeholder="Ketik jenis / kategori sampah..." autocomplete="off">
                                <div class="combo-list" id="listKategori"></div>
                            </div>
                            <select id="tambahKategori" class="combo-hidden" tabindex="-1" aria-hidden="true" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($kategoriSampah as $k)
                                    @php
                                        $satuan = strtolower($k->satuan ?? 'kg');
                                        $stok = $k->stok_tersedia ?? 0;
                                        $formattedStok =
                                            $satuan === 'pcs' || fmod($stok, 1) == 0
                                                ? number_format($stok, 0, ',', '.')
                                                : number_format($stok, 2, ',', '.');
                                    @endphp
                                    <option value="{{ $k->id_kategori }}" data-stok="{{ $formattedStok }}"
                                        data-satuan="{{ $k->satuan ?? 'kg' }}">
                                        {{ $k->nama_kategori }} (Stok: {{ $formattedStok }} {{ $k->satuan ?? 'kg' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- HARGA SATUAN DAN KUANTITAS -->
                        <div class="form-group">
                            <label for="tambahHargaSatuan">Harga Jual per <span class="label-satuan">Satuan</span>
                                (Rp)</label>
                            <input type="number" id="tambahHargaSatuan" min="0" step="any" placeholder="Masukkan harga"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="tambahKuantitas">Kuantitas (<span class="label-satuan">Satuan</span>)</label>
                            <input type="number" id="tambahKuantitas" step="any" min="0.01"
                                placeholder="Masukkan jumlah" required>
                        </div>

                        <!-- TOTAL HARGA (KALKULASI) -->
                        <div class="form-group form-group--full total-box">
                            <span class="detail-label">Total Penerimaan Penjualan</span>
                            <span class="detail-value saldo" id="totalHargaDisplay">Rp 0</span>
                        </div>

                        <!-- TANGGAL TRANSAKSI -->
                        <div class="form-group form-group--full">
                            <label for="tambahTanggal">Tanggal Transaksi</label>
                            <input type="date" id="tambahTanggal" class="input-locked" readonly tabindex="-1" aria-readonly="true">
                            <span class="lock-hint">Otomatis hari ini, tidak dapat diubah.</span>
                        </div>

                    </div>
                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button"
                        onclick="tutupModal('modalTambahPenjualan')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script>
        window.__PENJUALAN_DATA__ = {
            storeUrl: @json(route('admin.penjualan-pengepul.store'))
        };
    </script>
    @vite('resources/js/pages/penjualan-pengepul.js')
@endpush
