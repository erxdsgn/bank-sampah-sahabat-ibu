
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
                                            <strong
                                                title="{{ $item->warga->nama_bank_ewallet }}">{{ $item->warga->nama_bank_ewallet }}</strong>
                                        </div>
                                        <div class="mono rekening-number"
                                            title="{{ $item->warga->nomor_rekening ?? '-' }}">
                                            {{ $item->warga->nomor_rekening ?? '-' }}</div>
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

