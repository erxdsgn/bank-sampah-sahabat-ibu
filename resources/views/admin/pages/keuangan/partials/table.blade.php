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
                    

                    @forelse ($sortedTransaksi as $item)
                        <tr data-jenis="{{ $item->jenis }}" data-periode="{{ $item->periode }}" data-sumber="{{ $item->sumber }}">
                            <td class="nowrap">
                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge {{ $item->jenis === 'Pemasukan' ? 'badge--success' : 'badge--danger' }}">
                                    {{ $item->jenis }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $item->kategori }}</strong>
                            </td>
                            <td class="description-cell">
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

                                    {{-- Hapus --}}
                                    @unless ($item->otomatis)
                                        <button class="icon-btn danger" title="Hapus Transaksi" type="button" onclick="hapusTransaksi({{ Js::from($item) }})">
                                            <svg viewBox="0 0 24 24">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                                <path d="M10 11v6M14 11v6"></path>
                                            </svg>
                                        </button>
                                    @endunless
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
