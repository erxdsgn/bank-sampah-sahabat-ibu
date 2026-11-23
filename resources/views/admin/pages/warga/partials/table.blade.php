    <!-- MAIN DATA TABLE CARD -->
    <section class="card">
        <!-- TOOLBAR / SEARCH + FILTER -->
        <div class="table-toolbar">
            <div class="table-search">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchWarga" placeholder="Cari NIK, nama, atau HP..." autocomplete="off">
            </div>

            <div class="filter-pills" id="filterPills">
                <button class="fpill active" type="button" data-filter="all">Semua</button>
                <button class="fpill" type="button" data-filter="verified">Terverifikasi</button>
                <button class="fpill" type="button" data-filter="unverified">Belum Verifikasi</button>
            </div>
        </div>

        <!-- TABLE TANPA SCROLL KANAN-KIRI -->
        <div class="table-no-scroll">
            <table class="data-table" id="wargaTable">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th>Warga</th>
                        <th>No. HP</th>
                        <th>Kelompok</th>
                        <th>Saldo</th>
                        <th>Rekening</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warga as $index => $item)
                        <tr data-status="{{ $item->nama_bank_ewallet ? $item->status_rekening : 'none' }}">
                            <!-- NOMOR URUT -->
                            <td style="text-align: center;">
                                <span class="text-subtle" style="font-weight: 600;">
                                    {{ $index + 1 }}
                                </span>
                            </td>

                            <!-- NAMA WARGA & NIK -->
                            <td>
                                <strong class="user-name">{{ $item->nama }}</strong>
                                <div class="mono text-subtle" style="margin-top: 2px;">{{ $item->nik }}</div>
                            </td>

                            <!-- NO HP -->
                            <td>{{ $item->no_hp ?? '-' }}</td>

                            <!-- KELOMPOK (SIMPEL, HANYA HURUF ATAU DASH JIKA KOSONG) -->
                            <td>
                                @if (!empty($item->kelompok))
                                    <span style="font-weight: 600;">{{ $item->kelompok }}</span>
                                @else
                                    <span class="text-subtle">-</span>
                                @endif
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

                                    <button class="icon-btn icon-btn--primary" title="Ubah Status Rekening" type="button"
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
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                <div style="font-size: 30px; margin-bottom: 10px;">📋</div>
                                <strong>Belum Ada Data Warga</strong>
                                <p class="empty-desc" style="margin: 5px 0 0;">Belum ada masyarakat yang terdaftar di Bank
                                    Sampah.</p>
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

