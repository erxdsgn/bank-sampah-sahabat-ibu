@extends('admin.layouts.app')

@section('title', 'Kategori & Harga Sampah')
@section('active', 'kategori-harga')
@section('crumbs', 'Master Data | Kategori & Harga Sampah')

@section('content')

    @php
        // 1. Ringkasan statistik
        $totalInduk = $kategoriInduk->count();
        $totalSub = $kategoriInduk->sum(fn($k) => $k->anak->count());
        $totalHarga = $kategoriInduk->reduce(function ($carry, $k) {
            $carry += $k->hargaTerbaru ? 1 : 0;
            $carry += $k->anak->filter(fn($s) => $s->hargaTerbaru)->count();
            return $carry;
        }, 0);
        $totalBelum = $totalInduk + $totalSub - $totalHarga;

        // Helper: singkatan satuan -> nama lengkap
        $formatSatuan = function ($satuan) {
            $map = [
                'kg' => 'Kilogram (kg)',
                'pcs' => 'Pieces (pcs)',
                'gram' => 'Gram (gram)',
                'liter' => 'Liter (L)',
                'set' => 'Set (set)',
                'unit' => 'Unit (unit)',
            ];
            $satuanLower = strtolower(trim((string) $satuan));
            return $map[$satuanLower] ?? ($satuan ? ucfirst($satuan) : '-');
        };

        // 2. Riwayat harga per kategori
        $riwayatMap = [];
        $mapRiwayat = function ($kat) use (&$riwayatMap) {
            $riwayatMap[$kat->id_kategori] = collect($kat->hargaSampah ?? [])
                ->sortByDesc('tanggal_berlaku')
                ->map(function ($h) use ($kat) {
                    $tgl = $h->tanggal_berlaku ? \Carbon\Carbon::parse($h->tanggal_berlaku) : null;
                    return [
                        'id_harga' => $h->id_harga,
                        'harga' => (float) $h->harga_satuan,
                        'tanggal' => $tgl ? $tgl->translatedFormat('d M Y') : '-',
                        'tanggal_raw' => $tgl ? $tgl->format('Y-m-d') : '',
                        'delete_url' => route('admin.kategori-harga.harga.destroy', [
                            $kat->id_kategori,
                            'hargaSampah' => $h->id_harga,
                        ]),
                    ];
                })
                ->values();
        };

        foreach ($kategoriInduk as $k) {
            $mapRiwayat($k);
            foreach ($k->anak as $s) {
                $mapRiwayat($s);
            }
        }

        // 3. Daftar kategori untuk rekap bulanan
        $daftarKategori = [];
        foreach ($kategoriInduk as $k) {
            $daftarKategori[] = [
                'id' => $k->id_kategori,
                'nama' => $k->nama_kategori,
                'satuan' => $formatSatuan($k->satuan),
                'sub' => false,
            ];
            foreach ($k->anak as $s) {
                $daftarKategori[] = [
                    'id' => $s->id_kategori,
                    'nama' => $s->nama_kategori,
                    'satuan' => $formatSatuan($s->satuan),
                    'sub' => true,
                ];
            }
        }

        // SVG reusable
        $iconEdit =
            '<svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
        $iconHarga =
            '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M15 8.5c-.6-.7-1.6-1.1-2.8-1.1-1.7 0-2.8.9-2.8 2.2 0 1.2 1 1.8 2.8 2.2 1.8.4 2.8 1 2.8 2.2 0 1.3-1.1 2.2-2.9 2.2-1.3 0-2.4-.5-3.1-1.3M12 5v14"></path></svg>';
        $iconHapus =
            '<svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6M14 11v6"></path></svg>';
    @endphp

    <!-- HERO HEADER -->
    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">MASTER DATA</span>
            <h1 class="hero-title">Kategori & <span class="accent">Harga Sampah</span></h1>
            <p class="hero-sub">Kelola kategori sampah beserta riwayat dan penetapan harganya.</p>
        </div>
        <div class="hero-actions">
            <button class="btn btn--primary" type="button" onclick="bukaModalTambahKategori()">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Kategori
            </button>
        </div>
    </section>

    <!-- SUMMARY CARDS -->
    <section class="summary-grid">
        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Kategori Induk</div>
                    <div class="summary-value">{{ $totalInduk }}</div>
                </div>
                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                        <path d="M3 10h18"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Jumlah kategori utama</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Sub Kategori</div>
                    <div class="summary-value">{{ $totalSub }}</div>
                </div>
                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M6 3v12a3 3 0 0 0 3 3h9"></path>
                        <circle cx="18" cy="6" r="2"></circle>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Jumlah kategori turunan</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Sudah Ada Harga</div>
                    <div class="summary-value">{{ $totalHarga }}</div>
                </div>
                <div class="summary-icon summary-icon--income">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 6 9 17l-5-5"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Kategori dengan harga aktif</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Belum Ada Harga</div>
                    <div class="summary-value">{{ $totalBelum }}</div>
                </div>
                <div class="summary-icon summary-icon--expense">
                    <svg viewBox="0 0 24 24">
                        <path d="M18 6 6 18M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Kategori yang perlu ditetapkan harganya</div>
        </div>
    </section>

    <!-- DATA TABLE -->
    <section class="card">
        <div class="table-toolbar">
            <div class="table-search">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchKategori" placeholder="Cari nama kategori atau satuan..."
                    autocomplete="off">
            </div>

            <div class="toolbar-right">
                <button class="btn btn--ghost" type="button" onclick="bukaModalBulanan()">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="17" rx="2"></rect>
                        <path d="M3 10h18M8 2v4M16 2v4"></path>
                    </svg>
                    Harga per Bulan
                </button>

                <button class="btn btn--ghost" type="button" onclick="window.location.reload()">
                    <svg viewBox="0 0 24 24">
                        <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                        <path d="M21 3v5h-5"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="kategoriTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Satuan Unit</th>
                        <th>Berlaku Sejak</th>
                        <th>Harga Aktif</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="kategoriTableBody">
                    @forelse($kategoriInduk as $index => $kategori)
                        @php
                            $tglInduk = $kategori->hargaTerbaru
                                ? \Carbon\Carbon::parse($kategori->hargaTerbaru->tanggal_berlaku)
                                : null;
                        @endphp
                        <tr data-row-kategori data-group="{{ $kategori->id_kategori }}">
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $kategori->nama_kategori }}</strong></td>
                            <td><span class="badge badge--satuan">{{ $formatSatuan($kategori->satuan) }}</span></td>
                            <td class="nowrap">{{ $tglInduk ? $tglInduk->translatedFormat('d M Y') : '-' }}</td>
                            <td>
                                @if ($kategori->hargaTerbaru)
                                    <strong class="saldo">Rp
                                        {{ number_format($kategori->hargaTerbaru->harga_satuan, 0, ',', '.') }}</strong>
                                @else
                                    <span class="muted">Belum diatur</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $kategori->hargaTerbaru ? 'badge--success' : 'badge--warning' }}">
                                    {{ $kategori->hargaTerbaru ? 'Aktif' : 'Belum Diatur' }}
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="icon-btn" title="Edit Kategori" type="button"
                                        onclick="editKategoriModal({{ Js::from(['id_kategori' => $kategori->id_kategori, 'id_induk' => $kategori->id_induk, 'nama_kategori' => $kategori->nama_kategori, 'satuan' => $kategori->satuan, 'punya_anak' => $kategori->anak->count() > 0]) }})">
                                        {!! $iconEdit !!}
                                    </button>
                                    <button class="icon-btn icon-btn--harga" title="Kelola Harga" type="button"
                                        onclick="kelolaHarga({{ Js::from(['id_kategori' => $kategori->id_kategori, 'nama_kategori' => $kategori->nama_kategori, 'satuan' => $kategori->satuan, 'store_url' => route('admin.kategori-harga.harga.store', $kategori->id_kategori)]) }})">
                                        {!! $iconHarga !!}
                                    </button>
                                    <button class="icon-btn icon-btn--danger" title="Hapus Kategori" type="button"
                                        onclick="hapusKategori({{ Js::from(['id_kategori' => $kategori->id_kategori, 'nama_kategori' => $kategori->nama_kategori]) }})">
                                        {!! $iconHapus !!}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        @foreach ($kategori->anak as $sub)
                            @php
                                $tglSub = $sub->hargaTerbaru
                                    ? \Carbon\Carbon::parse($sub->hargaTerbaru->tanggal_berlaku)
                                    : null;
                            @endphp
                            <tr data-row-kategori data-group="{{ $kategori->id_kategori }}" class="sub-row">
                                <td></td>
                                <td class="sub-cell"><span class="sub-arrow">↳</span> {{ $sub->nama_kategori }}</td>
                                <td><span class="badge badge--satuan">{{ $formatSatuan($sub->satuan) }}</span></td>
                                <td class="nowrap">{{ $tglSub ? $tglSub->translatedFormat('d M Y') : '-' }}</td>
                                <td>
                                    @if ($sub->hargaTerbaru)
                                        <strong class="saldo">Rp
                                            {{ number_format($sub->hargaTerbaru->harga_satuan, 0, ',', '.') }}</strong>
                                    @else
                                        <span class="muted">Belum diatur</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $sub->hargaTerbaru ? 'badge--success' : 'badge--warning' }}">
                                        {{ $sub->hargaTerbaru ? 'Aktif' : 'Belum Diatur' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button class="icon-btn" title="Edit Kategori" type="button"
                                            onclick="editKategoriModal({{ Js::from(['id_kategori' => $sub->id_kategori, 'id_induk' => $sub->id_induk, 'nama_kategori' => $sub->nama_kategori, 'satuan' => $sub->satuan, 'punya_anak' => false]) }})">
                                            {!! $iconEdit !!}
                                        </button>
                                        <button class="icon-btn icon-btn--harga" title="Kelola Harga" type="button"
                                            onclick="kelolaHarga({{ Js::from(['id_kategori' => $sub->id_kategori, 'nama_kategori' => $sub->nama_kategori, 'satuan' => $sub->satuan, 'store_url' => route('admin.kategori-harga.harga.store', $sub->id_kategori)]) }})">
                                            {!! $iconHarga !!}
                                        </button>
                                        <button class="icon-btn icon-btn--danger" title="Hapus Kategori" type="button"
                                            onclick="hapusKategori({{ Js::from(['id_kategori' => $sub->id_kategori, 'nama_kategori' => $sub->nama_kategori]) }})">
                                            {!! $iconHapus !!}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state-cell">
                                <div class="empty-icon">📦</div>
                                <strong>Belum Ada Data Kategori</strong>
                                <p class="empty-desc">Belum ada kategori sampah yang terdaftar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="catalog-empty" id="kategoriKosongCari" style="display: none;">
                <div class="empty-icon">🔍</div>
                <strong>Data Tidak Ditemukan</strong>
                <p class="empty-desc">Tidak ada kategori yang cocok dengan pencarian ini.</p>
            </div>
        </div>

        <div class="table-footer">
            <div class="table-info" id="tableInfoPagination">Menampilkan data...</div>

            <div class="table-pagination-controls">
                <div class="per-page">
                    <label for="cari_perPageSelect">Kategori per halaman:</label>
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

    <!-- MODAL 1: TAMBAH / EDIT KATEGORI -->
    <div class="modal-overlay" id="modalKategori">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title" id="modalKategoriTitle">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span id="modalKategoriTitleText">Tambah Kategori</span>
                </h3>
                <button class="modal-close" type="button" aria-label="Tutup"
                    onclick="tutupModal('modalKategori')">&times;</button>
            </div>

            <form id="formKategori" class="modal-form" onsubmit="return simpanKategori(event)">
                <div class="modal-body">
                    <input type="hidden" id="kategoriId">
                    <div class="form-grid">
                        <div class="form-group form-group--full">
                            <label for="cari_id_induk">Kategori Induk (opsional)</label>
                            <div class="combo" id="combo_id_induk">
                                <input type="text" id="cari_id_induk" class="combo-input"
                                    placeholder="Pilih / ketik kategori induk..." autocomplete="off">
                                <div class="combo-list" id="list_id_induk"></div>
                            </div>
                            <select id="id_induk" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="">— Tidak ada (kategori utama) —</option>
                                @foreach ($kategoriInduk as $induk)
                                    <option value="{{ $induk->id_kategori }}">{{ $induk->nama_kategori }}</option>
                                @endforeach
                            </select>
                            <span class="form-error" id="err_id_induk"></span>
                        </div>

                        <div class="form-group">
                            <label for="nama_kategori">Nama Kategori</label>
                            <input type="text" id="nama_kategori" class="form-control"
                                placeholder="Contoh: Botol Plastik" required autocomplete="off">
                            <span class="form-error" id="err_nama_kategori"></span>
                        </div>

                        <div class="form-group">
                            <label for="cari_satuan">Satuan Jual</label>
                            <div class="combo combo-satuan" id="combo_satuan">
                                <input type="text" id="cari_satuan" class="combo-input" placeholder="Pilih satuan..."
                                    autocomplete="off">
                                <div class="combo-list" id="list_satuan"></div>
                            </div>
                            <select id="satuan" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="kg">Kilogram (kg)</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="gram">Gram (gram)</option>
                                <option value="liter">Liter (L)</option>
                                <option value="set">Set (set)</option>
                                <option value="unit">Unit (unit)</option>
                            </select>
                            <span class="form-error" id="err_satuan"></span>
                        </div>

                        <div class="form-group" id="blokHargaAwal">
                            <label for="harga_awal">Harga Satuan (Rp)</label>
                            <input type="number" step="0.01" min="0" id="harga_awal" class="form-control"
                                placeholder="Contoh: 5000">
                            <span class="form-error" id="err_harga_awal"></span>
                        </div>

                        <div class="form-group" id="blokTanggalAwal">
                            <label for="tanggal_awal">Berlaku Mulai</label>
                            <input type="date" id="tanggal_awal" class="form-control">
                            <span class="form-error" id="err_tanggal_awal"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalKategori')">Batal</button>
                    <button class="btn btn--primary" type="submit" id="kategoriSubmitBtn">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: KELOLA HARGA -->
    <div class="modal-overlay" id="modalHarga">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path
                            d="M15 8.5c-.6-.7-1.6-1.1-2.8-1.1-1.7 0-2.8.9-2.8 2.2 0 1.2 1 1.8 2.8 2.2 1.8.4 2.8 1 2.8 2.2 0 1.3-1.1 2.2-2.9 2.2-1.3 0-2.4-.5-3.1-1.3M12 5v14">
                        </path>
                    </svg>
                    <span>Kelola Harga — <span id="hargaNamaKategori">-</span></span>
                </h3>
                <button class="modal-close" type="button" aria-label="Tutup"
                    onclick="tutupModal('modalHarga')">&times;</button>
            </div>

            <div class="modal-body">
                <form id="formHarga" onsubmit="return tambahHarga(event)">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="harga_satuan">Harga per <span id="hargaSatuanLabel">satuan</span> (Rp)</label>
                            <input type="number" step="0.01" min="0" id="harga_satuan" class="form-control"
                                placeholder="Contoh: 5000" required>
                            <span class="form-error" id="err_harga_satuan"></span>
                        </div>

                        <div class="form-group">
                            <label for="tanggal_berlaku">Berlaku Mulai</label>
                            <input type="date" id="tanggal_berlaku" class="form-control" required>
                            <span class="form-error" id="err_tanggal_berlaku"></span>
                        </div>
                    </div>

                    <div class="form-actions--modal">
                        <button class="btn btn--primary" type="submit" id="hargaSubmitBtn">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Tambah Harga
                        </button>
                    </div>
                </form>

                <h4 class="modal-subtitle">Riwayat Penetapan Harga</h4>

                <div class="table-responsive">
                    <table class="data-table data-table--sm">
                        <thead>
                            <tr>
                                <th>Tanggal Berlaku</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="hargaTableBody">
                            <tr>
                                <td colspan="3" class="empty-desc text-center">Belum ada riwayat harga.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHarga')">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL 3: KONFIRMASI HAPUS -->
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

                <h3 class="confirm-title" id="hapusTitle">Hapus Data?</h3>
                <p class="confirm-text" id="hapusText">Tindakan ini tidak dapat dibatalkan.</p>
            </div>

            <div class="modal-foot modal-foot--center">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHapus')">Batal</button>
                <button class="btn btn--danger" type="button" id="hapusConfirmBtn" onclick="konfirmasiHapus()">Ya,
                    Hapus</button>
            </div>
        </div>
    </div>

    <!-- MODAL 4: REKAP HARGA PER BULAN -->
    <div class="modal-overlay" id="modalBulanan">
        <div class="modal-box modal-box--xl">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <rect x="3" y="4" width="18" height="17" rx="2"></rect>
                        <path d="M3 10h18M8 2v4M16 2v4"></path>
                    </svg>
                    <span>Update Harga per Bulan</span>
                </h3>
                <button class="modal-close" type="button" aria-label="Tutup"
                    onclick="tutupModal('modalBulanan')">&times;</button>
            </div>

            <div class="modal-body">
                <div class="bulan-bar">
                    <button class="btn btn--ghost" type="button" onclick="geserTahun(-1)"
                        aria-label="Tahun sebelumnya">‹</button>
                    <div class="combo combo--arrow" id="combo_rekapTahun">
                        <input type="text" id="cari_rekapTahun" class="combo-input" placeholder=""
                            autocomplete="off">
                        <div class="combo-list" id="list_rekapTahun"></div>
                    </div>
                    <select id="rekapTahun" class="combo-hidden" tabindex="-1" aria-hidden="true"
                        onchange="renderRekap()"></select>
                    <button class="btn btn--ghost" type="button" onclick="geserTahun(1)"
                        aria-label="Tahun berikutnya">›</button>
                    <span class="rekap-legend"><i class="dot"></i> harga berubah dari bulan sebelumnya</span>
                </div>

                <div class="rekap-wrap">
                    <table class="rekap-table">
                        <thead id="rekapHead"></thead>
                        <tbody id="rekapBody"></tbody>
                    </table>
                </div>

                <p class="muted legend-info">
                    Harga dalam Rupiah. Tanda “–” berarti belum ada harga pada bulan itu.
                    Bulan tanpa perubahan otomatis memakai harga bulan sebelumnya.
                </p>
            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalBulanan')">Tutup</button>
            </div>
        </div>
    </div>

    <!-- STYLESHEET -->
    <style>
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
            --dashboard-table-hover: #f8fafc;
            --dashboard-input: #ffffff;
            --dashboard-row-strong: #e5e7eb;
            --dashboard-shadow: 0 8px 25px rgba(15, 23, 42, .06);
            --dashboard-shadow-lg: 0 18px 45px rgba(15, 23, 42, .12);

            --primary-color: var(--primary, #4338ca);
            --primary-ring: rgba(67, 56, 202, .10);

            --combo-accent: #4338ca;
            --combo-ring: rgba(67, 56, 202, .12);
            --combo-input-bg: #ffffff;
            --combo-hover: #eef2ff;

            --tone-primary-bg: #eef2ff;
            --tone-income-bg: #dcfce7;
            --tone-income-fg: #16a34a;
            --tone-expense-bg: #fee2e2;
            --tone-expense-fg: #dc2626;

            --badge-satuan-bg: #e0f2fe;
            --badge-satuan-fg: #0369a1;
            --badge-success-bg: #dcfce7;
            --badge-success-fg: #15803d;
            --badge-warning-bg: #fef3c7;
            --badge-warning-fg: #b45309;

            /* Tombol Berlogo Uang (Tetap Hijau di Mode Terang maupun Gelap) */
            --btn-harga-bg: #dcfce7;
            --btn-harga-bd: #bbf7d0;
            --btn-harga-fg: #15803d;

            --btn-danger-bg: #fef2f2;
            --btn-danger-bd: #fecaca;
            --btn-danger-fg: #dc2626;
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
            --dashboard-row-strong: #29364d;
            --dashboard-shadow: 0 8px 25px rgba(0, 0, 0, .25);
            --dashboard-shadow-lg: 0 20px 50px rgba(0, 0, 0, .35);

            /* Konsistensi Warna Biru untuk Tombol Umum (seperti Tambah Kategori) */
            --primary-color: #818cf8;
            --primary-ring: rgba(129, 140, 248, .18);
            --combo-accent: #818cf8;
            --combo-ring: rgba(129, 140, 248, .20);
            --combo-input-bg: #111a2c;
            --combo-hover: #202b40;

            --tone-primary-bg: rgba(129, 140, 248, .15);
            --tone-income-bg: rgba(34, 197, 94, .15);
            --tone-income-fg: #4ade80;
            --tone-expense-bg: rgba(220, 38, 38, .15);
            --tone-expense-fg: #f87171;

            --badge-satuan-bg: rgba(3, 105, 161, .25);
            --badge-satuan-fg: #38bdf8;
            --badge-success-bg: rgba(34, 197, 94, .20);
            --badge-success-fg: #4ade80;
            --badge-warning-bg: rgba(245, 158, 11, .20);
            --badge-warning-fg: #fbbf24;

            /* TOMBOL BERLOGO UANG TETAP DIJAGA NUANSA HIJAU NYA */
            --btn-harga-bg: rgba(34, 197, 94, .15);
            --btn-harga-bd: rgba(34, 197, 94, .35);
            --btn-harga-fg: #4ade80;

            --btn-danger-bg: rgba(220, 38, 38, .15);
            --btn-danger-bd: rgba(220, 38, 38, .35);
            --btn-danger-fg: #f87171;
        }

        /* ---------- SUMMARY ---------- */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .summary-card {
            border: 1px solid var(--dashboard-border);
            background: var(--dashboard-card);
            color: var(--dashboard-text);
            border-radius: 14px;
            padding: 18px;
            box-shadow: var(--dashboard-shadow);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--dashboard-shadow-lg);
        }

        .summary-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .summary-label {
            font-size: 13px;
            color: var(--dashboard-text-secondary);
            margin-bottom: 8px;
        }

        .summary-value {
            font-size: 21px;
            font-weight: 800;
            line-height: 1.25;
            color: var(--dashboard-text);
        }

        .summary-icon {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--tone-primary-bg);
            color: var(--primary-color);
        }

        .summary-icon--income {
            background: var(--tone-income-bg);
            color: var(--tone-income-fg);
        }

        .summary-icon--expense {
            background: var(--tone-expense-bg);
            color: var(--tone-expense-fg);
        }

        .summary-icon svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .summary-info {
            margin-top: 12px;
            font-size: 12px;
            color: var(--dashboard-text-secondary);
            line-height: 1.5;
        }

        /* ---------- CARD & TOOLBAR ---------- */
        .card {
            background: var(--dashboard-card);
            border: 1px solid var(--dashboard-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--dashboard-shadow);
            color: var(--dashboard-text);
        }

        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            flex-wrap: wrap;
        }

        .table-search {
            width: 400px;
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
            pointer-events: none;
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
            font-family: inherit;
            font-size: 13px;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .table-search input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-ring);
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* ---------- TABLE ---------- */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }

        .data-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
            background: var(--dashboard-table-row);
            color: var(--dashboard-text);
        }

        .data-table--sm {
            min-width: 0;
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
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid var(--dashboard-border);
            font-size: 13px;
            color: var(--dashboard-text);
            vertical-align: middle;
            background: var(--dashboard-table-row);
            transition: background .15s ease;
        }

        .data-table tbody tr:hover td {
            background: var(--dashboard-table-hover);
        }

        .data-table .sub-row td {
            background: var(--dashboard-card-secondary);
        }

        .data-table .sub-row:hover td {
            background: var(--dashboard-table-hover);
        }

        .sub-cell {
            padding-left: 28px !important;
            color: var(--dashboard-text-secondary) !important;
        }

        .sub-arrow {
            opacity: .5;
        }

        .nowrap,
        .saldo {
            white-space: nowrap;
        }

        .saldo {
            color: var(--tone-income-fg);
        }

        .muted,
        .empty-desc {
            color: var(--dashboard-text-secondary);
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge--satuan {
            background: var(--badge-satuan-bg);
            color: var(--badge-satuan-fg);
        }

        .badge--success {
            background: var(--badge-success-bg);
            color: var(--badge-success-fg);
        }

        .badge--warning {
            background: var(--badge-warning-bg);
            color: var(--badge-warning-fg);
        }

        /* ---------- ICON BUTTONS ---------- */
        .table-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            border: 1px solid var(--dashboard-border);
            background: var(--dashboard-card);
            color: var(--dashboard-text-secondary);
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s ease, border-color .15s ease, color .15s ease, transform .15s ease;
        }

        .icon-btn:hover {
            transform: translateY(-1px);
            background: var(--dashboard-table-hover);
            color: var(--dashboard-text);
        }

        .icon-btn--sm {
            width: 30px;
            height: 30px;
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

        .icon-btn--harga {
            color: var(--btn-harga-fg);
            background: var(--btn-harga-bg);
            border-color: var(--btn-harga-bd);
        }

        .icon-btn--harga:hover {
            color: var(--btn-harga-fg);
            background: var(--btn-harga-bg);
            filter: brightness(1.1);
        }

        .icon-btn--danger {
            color: var(--btn-danger-fg);
            background: var(--btn-danger-bg);
            border-color: var(--btn-danger-bd);
        }

        .icon-btn--danger:hover {
            color: var(--btn-danger-fg);
            background: var(--btn-danger-bg);
            filter: brightness(.96);
        }

        /* ---------- FOOTER & PAGINATION ---------- */
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

        .catalog-empty {
            text-align: center;
            padding: 50px 20px;
            color: var(--dashboard-text);
        }

        .empty-state-cell {
            text-align: center;
            padding: 40px !important;
        }

        .empty-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        /* ---------- FORM ---------- */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }

        .form-group--full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--dashboard-text-secondary);
        }

        .form-control {
            width: 100%;
            min-height: 40px;
            padding: 10px 14px;
            font-size: 13px;
            font-family: inherit;
            color: var(--dashboard-text);
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            outline: none;
            box-sizing: border-box;
            background: var(--dashboard-input);
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-ring);
        }

        .form-control.is-invalid {
            border-color: #dc2626;
        }

        .form-control:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .form-error {
            display: block;
            margin-top: 2px;
            font-size: 12px;
            color: #dc2626;
            min-height: 14px;
        }

        .form-actions--modal {
            margin-top: 16px;
            display: flex;
            justify-content: flex-end;
        }

        /* ---------- MODAL ---------- */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .60);
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
            max-width: 620px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: var(--dashboard-card);
            color: var(--dashboard-text);
            border: 1px solid var(--dashboard-border);
            border-radius: 14px;
            box-shadow: var(--dashboard-shadow-lg);
        }

        .modal-box--sm {
            max-width: 420px;
        }

        .modal-box--xl {
            max-width: 1180px;
        }

        .modal-form {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 20px 24px;
            border-bottom: 1px solid var(--dashboard-border);
            background: var(--dashboard-card);
            flex-shrink: 0;
        }

        .modal-title {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: var(--dashboard-text);
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            flex: 1;
        }

        .modal-title span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .modal-subtitle {
            margin: 20px 0 12px;
            font-size: 13px;
            font-weight: 700;
            color: var(--dashboard-text);
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background .2s ease, color .2s ease;
        }

        .modal-close:hover {
            background: var(--dashboard-card-secondary);
            color: var(--dashboard-text);
        }

        .modal-body {
            padding: 22px 24px;
            background: var(--dashboard-card);
            overflow-y: auto;
            flex: 1;
            min-height: 0;
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
            align-items: center;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid var(--dashboard-border);
            background: var(--dashboard-card);
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
        }

        .btn:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .confirm-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 14px;
            border-radius: 50%;
            background: var(--btn-danger-bg);
            color: var(--btn-danger-fg);
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
            margin: 0 0 8px;
            font-size: 16px;
            font-weight: 700;
            color: var(--dashboard-text);
        }

        .confirm-text {
            margin: 0;
            font-size: 13px;
            color: var(--dashboard-text-secondary);
            line-height: 1.6;
        }

        /* ---------- REKAP BULANAN ---------- */
        .bulan-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .bulan-bar .btn {
            padding: 6px 12px;
        }

        .rekap-legend {
            margin-left: auto;
            font-size: 12px;
            color: var(--dashboard-text-secondary);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .rekap-legend .dot {
            width: 12px;
            height: 12px;
            flex: 0 0 12px;
            border-radius: 3px;
            background: #fef3c7;
            border: 1px solid #fcd34d;
            display: inline-block;
        }

        .rekap-wrap {
            margin-top: 16px;
            width: 100%;
            overflow: auto;
            max-height: 62vh;
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            isolation: isolate;
            background: var(--dashboard-card);
            scrollbar-width: thin;
            scrollbar-color: var(--dashboard-border) transparent;
        }

        .rekap-wrap::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .rekap-wrap::-webkit-scrollbar-track {
            background: transparent;
        }

        .rekap-wrap::-webkit-scrollbar-thumb {
            background: var(--dashboard-border);
            border-radius: 4px;
        }

        .rekap-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            min-width: 980px;
            font-size: 13px;
            background: var(--dashboard-table-row);
            color: var(--dashboard-text);
        }

        .rekap-table th,
        .rekap-table td {
            border-right: 1px solid var(--dashboard-border);
            border-bottom: 1px solid var(--dashboard-border);
            padding: 8px 10px;
            text-align: right;
            white-space: nowrap;
            color: var(--dashboard-text);
            background: var(--dashboard-table-row);
        }

        .rekap-table thead th {
            background: var(--dashboard-table-head);
            color: var(--dashboard-text-secondary);
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            position: sticky;
            top: 0;
            z-index: 30;
            border-bottom: 2px solid var(--dashboard-border);
        }

        .rekap-table .col-nama {
            position: sticky;
            left: 0;
            width: 240px;
            min-width: 240px;
            max-width: 240px;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: left;
            background: var(--dashboard-card);
            z-index: 40;
            border-right: 2px solid var(--dashboard-border);
        }

        .rekap-table thead .col-nama {
            top: 0;
            background: var(--dashboard-table-head);
            z-index: 50;
        }

        .rekap-table tr.baris-induk td {
            background: var(--dashboard-row-strong);
            font-weight: 800;
            text-transform: uppercase;
        }

        .rekap-table tr.baris-induk .col-nama {
            z-index: 45;
        }

        .rekap-table td.berubah {
            background: #fef3c7;
            font-weight: 700;
            color: #1f2937;
            border: 1px solid #fcd34d;
        }

        [data-theme="dark"] .rekap-table td.berubah {
            background: rgba(245, 158, 11, .25);
            color: #fde68a;
            border-color: rgba(251, 191, 36, .45);
        }

        .rekap-table td.kosong {
            color: var(--dashboard-text-muted);
            text-align: center;
            font-weight: 500;
        }

        .legend-info {
            margin: 12px 0 0;
            font-size: 12px;
            line-height: 1.6;
        }

        /* ---------- TOAST ---------- */
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
            color: var(--dashboard-text);
            border: 1px solid var(--dashboard-border);
            border-left: 4px solid #16a34a;
            border-radius: 10px;
            box-shadow: var(--dashboard-shadow-lg);
            padding: 14px 16px;
            pointer-events: auto;
        }

        .toast.toast--error {
            border-left-color: #dc2626;
        }

        .toast-icon {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--tone-income-bg);
            color: var(--tone-income-fg);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toast-icon svg {
            width: 14px;
            height: 14px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .toast.toast--error .toast-icon {
            background: var(--tone-expense-bg);
            color: var(--tone-expense-fg);
        }

        .toast-body {
            flex: 1;
            min-width: 0;
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
            color: var(--dashboard-text-secondary);
            font-size: 16px;
            cursor: pointer;
            padding: 0;
        }

        /* ---------- FORM DALAM MODAL ---------- */
        .modal-box .form-group label {
            text-transform: none;
            font-size: 12px;
            font-weight: 700;
            color: var(--dashboard-text-secondary);
        }

        .modal-box .form-control {
            min-height: 0;
            padding: 9px 12px;
            background: var(--combo-input-bg);
        }

        .modal-box .form-control:focus {
            border-color: var(--combo-accent);
            box-shadow: 0 0 0 3px var(--combo-ring);
        }

        /* ---------- COMBOBOX ---------- */
        .combo {
            position: relative;
        }

        .combo-hidden {
            display: none !important;
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
            background: var(--combo-input-bg);
            color: var(--dashboard-text);
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .combo-input::placeholder {
            color: var(--dashboard-text-muted);
        }

        .combo-input:focus {
            border-color: var(--combo-accent);
            box-shadow: 0 0 0 3px var(--combo-ring);
        }

        .combo-input:disabled {
            opacity: .6;
            cursor: not-allowed;
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
            min-width: 84px;
            max-height: 200px;
            overflow-y: auto;
            background: var(--dashboard-card);
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .22);
            z-index: 60;
            scrollbar-width: thin;
            scrollbar-color: var(--dashboard-border) transparent;
        }

        .combo-satuan .combo-list {
            max-height: 125px;
        }

        .combo--up .combo-list {
            top: auto;
            bottom: calc(100% + 4px);
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
            background: var(--combo-hover);
            color: var(--combo-accent);
        }

        .combo-empty {
            padding: 12px;
            font-size: 12.5px;
            color: var(--dashboard-text-muted);
            text-align: center;
        }
    </style>

    <!-- JAVASCRIPT -->
    <script>
        const riwayatData = @json($riwayatMap);
        const daftarKategori = @json($daftarKategori);

        const urlKategoriStore = "{{ route('admin.kategori-harga.store') }}";
        const urlKategoriUpdate = "{{ route('admin.kategori-harga.update', ':id') }}";
        const urlKategoriHapus = "{{ route('admin.kategori-harga.destroy', ':id') }}";

        const BULAN_SINGKAT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const FIELD_KATEGORI = ['id_induk', 'nama_kategori', 'satuan', 'harga_awal', 'tanggal_awal'];
        const FIELD_HARGA = ['harga_satuan', 'tanggal_berlaku'];

        let hargaContext = null;
        let hapusContext = null;

        /* ---------- UTIL ---------- */
        function escapeHtml(s) {
            return String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
        }

        function getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.content : '';
        }

        function tanggalHariIni() {
            const d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate())
                .padStart(2, '0');
        }

        function setBusy(btn, busy) {
            if (btn) btn.disabled = busy;
        }

        function bukaModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function tutupModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function showToast(title, text, type = 'success') {
            const wrap = document.getElementById('toastWrap');
            if (!wrap) return;

            const toast = document.createElement('div');
            toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');

            const iconPath = type === 'error' ?
                '<path d="M18 6 6 18M6 6l12 12"></path>' :
                '<path d="M20 6 9 17l-5-5"></path>';

            toast.innerHTML = `
                <div class="toast-icon"><svg viewBox="0 0 24 24">${iconPath}</svg></div>
                <div class="toast-body">
                    <p class="toast-title">${escapeHtml(title)}</p>
                    <p class="toast-text">${escapeHtml(text)}</p>
                </div>
                <button class="toast-close" type="button" aria-label="Tutup">&times;</button>
            `;

            const hapusToast = () => toast.remove();
            toast.querySelector('.toast-close').addEventListener('click', hapusToast);
            wrap.appendChild(toast);
            setTimeout(hapusToast, 3500);
        }

        function clearFormErrors(fields) {
            fields.forEach(f => {
                const el = document.getElementById('err_' + f);
                if (el) el.textContent = '';
                const input = document.getElementById(f);
                if (input) input.classList.remove('is-invalid');
            });
        }

        function tampilkanFormErrors(errors, fields) {
            fields.forEach(f => {
                if (errors && errors[f]) {
                    const el = document.getElementById('err_' + f);
                    const input = document.getElementById(f);
                    if (el) el.textContent = errors[f][0];
                    if (input) input.classList.add('is-invalid');
                }
            });
        }

        async function kirimJson(url, method, payload) {
            const headers = {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            };
            const opts = {
                method,
                headers
            };
            if (payload !== undefined) {
                headers['Content-Type'] = 'application/json';
                opts.body = JSON.stringify(payload);
            }
            const res = await fetch(url, opts);
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                const e = new Error(err.message || 'Terjadi kesalahan pada server.');
                e.errors = err.errors || null;
                throw e;
            }
            return res.json().catch(() => ({}));
        }

        /* ---------- COMBOBOX (menggantikan <select> bawaan browser) ---------- */
        const comboRegistry = {};

        function syncCombo(selectId) {
            if (comboRegistry[selectId]) comboRegistry[selectId].sync();
        }

        function initCombo(selectId, opts = {}) {
            const select = document.getElementById(selectId);
            const input = document.getElementById('cari_' + selectId);
            const list = document.getElementById('list_' + selectId);
            if (!select || !input || !list) return;

            const readonly = !!opts.readonly;
            input.readOnly = readonly;
            let aktif = -1;

            const teks = o => o.textContent.replace(/\s+/g, ' ').trim();
            const daftar = () => Array.from(select.options).filter(o => !o.disabled)
                .map(o => ({
                    value: o.value,
                    label: teks(o)
                }));

            function sync() {
                const o = select.options[select.selectedIndex];
                input.value = o ? teks(o) : '';
                input.disabled = select.disabled;
            }

            function render(keyword) {
                const k = (keyword || '').toLowerCase().trim();
                const hasil = daftar().filter(d => d.label.toLowerCase().includes(k));
                aktif = -1;
                list.innerHTML = hasil.length ?
                    hasil.map(d =>
                        `<div class="combo-item${d.value === select.value ? ' is-selected' : ''}" data-value="${escapeHtml(d.value)}">${escapeHtml(d.label)}</div>`
                    ).join('') :
                    '<div class="combo-empty">Data tidak ditemukan</div>';
            }

            function buka(keyword) {
                render(keyword);
                list.classList.add('show');
                const terpilih = list.querySelector('.is-selected');
                if (terpilih) terpilih.scrollIntoView({
                    block: 'nearest'
                });
            }

            function tutup() {
                list.classList.remove('show');
                sync(); // ketikan pencarian dibuang, tampilkan pilihan yang aktif
            }

            function pilih(value) {
                const berubah = select.value !== String(value);
                select.value = value;
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
                items[aktif].scrollIntoView({
                    block: 'nearest'
                });
            }

            input.addEventListener('focus', () => {
                if (!readonly) input.select();
                buka('');
            });
            input.addEventListener('click', () => {
                if (!list.classList.contains('show')) buka('');
            });
            input.addEventListener('input', () => buka(input.value));
            input.addEventListener('blur', tutup);

            input.addEventListener('keydown', function(e) {
                const items = list.querySelectorAll('.combo-item');
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!list.classList.contains('show')) buka(readonly ? '' : input.value);
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
                e.preventDefault(); // pertahankan fokus agar list tidak tertutup saat scroll/klik
                const item = e.target.closest('.combo-item');
                if (item) pilih(item.dataset.value);
            });

            comboRegistry[selectId] = {
                sync
            };
            sync();
        }

        /* ---------- REKAP BULANAN ---------- */
        function hargaPadaBulan(id, ym) {
            const [y, m] = ym.split('-').map(Number);
            const akhir = new Date(Date.UTC(y, m, 0)).toISOString().substring(0, 10);
            return (riwayatData[id] || [])
                .filter(h => h.tanggal_raw && h.tanggal_raw <= akhir)
                .sort((a, b) => b.tanggal_raw.localeCompare(a.tanggal_raw) || b.id_harga - a.id_harga)[0] || null;
        }

        function bukaModalBulanan() {
            const sel = document.getElementById('rekapTahun');
            const now = new Date().getFullYear();
            let min = now;

            Object.values(riwayatData).forEach(list => (list || []).forEach(h => {
                if (h.tanggal_raw) min = Math.min(min, parseInt(h.tanggal_raw.substring(0, 4), 10));
            }));

            sel.innerHTML = '';
            for (let y = now; y >= min; y--) {
                sel.insertAdjacentHTML('beforeend', `<option value="${y}">${y}</option>`);
            }
            sel.value = now;
            syncCombo('rekapTahun');

            renderRekap();
            bukaModal('modalBulanan');
        }

        function geserTahun(delta) {
            const sel = document.getElementById('rekapTahun');
            const idx = sel.selectedIndex - delta;
            if (idx >= 0 && idx < sel.options.length) {
                sel.selectedIndex = idx;
                syncCombo('rekapTahun');
                renderRekap();
            }
        }

        function renderRekap() {
            const tahun = parseInt(document.getElementById('rekapTahun').value, 10);
            const head = document.getElementById('rekapHead');
            const body = document.getElementById('rekapBody');

            head.innerHTML = `
                <tr>
                    <th rowspan="2" class="col-nama">Jenis Sampah</th>
                    <th colspan="12">Harga Sampah Tahun ${tahun}</th>
                </tr>
                <tr>${BULAN_SINGKAT.map(b => `<th>${b}</th>`).join('')}</tr>
            `;

            if (daftarKategori.length === 0) {
                body.innerHTML =
                    '<tr><td colspan="13" class="text-center empty-desc">Belum ada kategori.</td></tr>';
                return;
            }

            body.innerHTML = daftarKategori.map(k => {
                let prev = null;
                const cells = BULAN_SINGKAT.map((_, i) => {
                    const ym = tahun + '-' + String(i + 1).padStart(2, '0');
                    const h = hargaPadaBulan(k.id, ym);
                    const val = h ? h.harga : null;
                    let berubah = val !== null && prev !== null && val !== prev;

                    if (i === 0 && val !== null) {
                        const hp = hargaPadaBulan(k.id, (tahun - 1) + '-12');
                        berubah = !!hp && hp.harga !== val;
                    }
                    prev = val;

                    if (val === null) return '<td class="kosong">–</td>';
                    return `<td class="${berubah ? 'berubah' : ''}">${Number(val).toLocaleString('id-ID')}</td>`;
                }).join('');

                const nama = escapeHtml(k.nama);
                if (!k.sub) {
                    return `<tr class="baris-induk"><td class="col-nama">${nama}</td>${cells}</tr>`;
                }
                return `<tr><td class="col-nama"><span class="sub-arrow">↳</span> ${nama} <small class="muted">${escapeHtml(k.satuan ?? '')}</small></td>${cells}</tr>`;
            }).join('');
        }

        /* ---------- KATEGORI: TAMBAH / EDIT ---------- */
        function resetOpsiInduk() {
            document.querySelectorAll('#id_induk option').forEach(o => o.disabled = false);
            document.getElementById('id_induk').disabled = false;
        }

        function bukaModalTambahKategori() {
            document.getElementById('formKategori').reset();
            document.getElementById('kategoriId').value = '';
            document.getElementById('satuan').value = 'kg';
            clearFormErrors(FIELD_KATEGORI);
            resetOpsiInduk();
            syncCombo('id_induk');
            syncCombo('satuan');

            document.getElementById('tanggal_awal').value = tanggalHariIni();
            document.getElementById('blokHargaAwal').style.display = '';
            document.getElementById('blokTanggalAwal').style.display = '';
            document.getElementById('harga_awal').required = true;
            document.getElementById('tanggal_awal').required = true;

            document.getElementById('modalKategoriTitleText').textContent = 'Tambah Kategori';
            document.getElementById('kategoriSubmitBtn').textContent = 'Simpan Kategori';
            bukaModal('modalKategori');
        }

        function editKategoriModal(kategori) {
            document.getElementById('formKategori').reset();
            document.getElementById('kategoriId').value = kategori.id_kategori;
            clearFormErrors(FIELD_KATEGORI);
            resetOpsiInduk();

            // Kategori tidak boleh menjadi induk dirinya sendiri
            const selfOpt = document.querySelector(`#id_induk option[value="${kategori.id_kategori}"]`);
            if (selfOpt) selfOpt.disabled = true;

            // Kategori yang sudah punya sub tidak boleh dijadikan sub kategori (maks. 2 tingkat)
            const selInduk = document.getElementById('id_induk');
            selInduk.value = kategori.id_induk || '';
            selInduk.disabled = !!kategori.punya_anak;

            document.getElementById('nama_kategori').value = kategori.nama_kategori || '';
            document.getElementById('satuan').value = kategori.satuan || 'kg';
            syncCombo('id_induk');
            syncCombo('satuan');

            document.getElementById('blokHargaAwal').style.display = 'none';
            document.getElementById('blokTanggalAwal').style.display = 'none';
            document.getElementById('harga_awal').required = false;
            document.getElementById('tanggal_awal').required = false;

            document.getElementById('modalKategoriTitleText').textContent = 'Edit Kategori';
            document.getElementById('kategoriSubmitBtn').textContent = 'Simpan Perubahan';
            bukaModal('modalKategori');
        }

        function simpanKategori(event) {
            event.preventDefault();
            const id = document.getElementById('kategoriId').value;
            const btn = document.getElementById('kategoriSubmitBtn');
            clearFormErrors(FIELD_KATEGORI);

            const selInduk = document.getElementById('id_induk');
            const payload = {
                // select yang disabled tetap mengirim nilai aslinya
                id_induk: selInduk.value || null,
                nama_kategori: document.getElementById('nama_kategori').value.trim(),
                satuan: document.getElementById('satuan').value,
            };

            if (!id) {
                payload.harga_satuan = document.getElementById('harga_awal').value;
                payload.tanggal_berlaku = document.getElementById('tanggal_awal').value;
            }

            const url = id ? urlKategoriUpdate.replace(':id', id) : urlKategoriStore;
            setBusy(btn, true);

            kirimJson(url, id ? 'PUT' : 'POST', payload)
                .then(() => {
                    tutupModal('modalKategori');
                    showToast('Berhasil disimpan', id ? 'Perubahan kategori telah tersimpan.' :
                        'Kategori baru telah ditambahkan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    if (err.errors) {
                        const e = {
                            ...err.errors
                        };
                        if (e.harga_satuan) e.harga_awal = e.harga_satuan;
                        if (e.tanggal_berlaku) e.tanggal_awal = e.tanggal_berlaku;
                        tampilkanFormErrors(e, FIELD_KATEGORI);
                    }
                    showToast('Gagal menyimpan', err.message, 'error');
                    setBusy(btn, false);
                });

            return false;
        }

        /* ---------- HARGA ---------- */
        function kelolaHarga(kategori) {
            hargaContext = kategori;
            document.getElementById('hargaNamaKategori').textContent = kategori.nama_kategori;
            document.getElementById('hargaSatuanLabel').textContent = kategori.satuan || 'satuan';
            document.getElementById('formHarga').reset();
            document.getElementById('tanggal_berlaku').value = tanggalHariIni();
            clearFormErrors(FIELD_HARGA);
            setBusy(document.getElementById('hargaSubmitBtn'), false);

            renderRiwayatHarga(kategori.id_kategori);
            bukaModal('modalHarga');
        }

        function renderRiwayatHarga(idKategori) {
            const tbody = document.getElementById('hargaTableBody');
            const riwayat = riwayatData[idKategori] || [];

            if (riwayat.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="3" class="empty-desc text-center">Belum ada riwayat harga.</td></tr>';
                return;
            }

            tbody.innerHTML = riwayat.map((h, i) => `
                <tr>
                    <td>${escapeHtml(h.tanggal)}</td>
                    <td><strong class="saldo">Rp ${Number(h.harga).toLocaleString('id-ID')}</strong></td>
                    <td>
                        <button class="icon-btn icon-btn--danger icon-btn--sm" title="Hapus" type="button"
                            data-index="${i}">
                            <svg viewBox="0 0 24 24">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                <path d="M10 11v6M14 11v6"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
            `).join('');

            tbody.querySelectorAll('button[data-index]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const item = riwayat[parseInt(btn.dataset.index, 10)];
                    hapusHarga(item.delete_url);
                });
            });
        }

        function tambahHarga(event) {
            event.preventDefault();
            if (!hargaContext) return false;

            const btn = document.getElementById('hargaSubmitBtn');
            clearFormErrors(FIELD_HARGA);

            const payload = {
                harga_satuan: document.getElementById('harga_satuan').value,
                tanggal_berlaku: document.getElementById('tanggal_berlaku').value,
            };

            setBusy(btn, true);

            kirimJson(hargaContext.store_url, 'POST', payload)
                .then(() => {
                    showToast('Berhasil disimpan', 'Harga baru telah ditambahkan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    if (err.errors) tampilkanFormErrors(err.errors, FIELD_HARGA);
                    showToast('Gagal menyimpan', err.message, 'error');
                    setBusy(btn, false);
                });

            return false;
        }

        /* ---------- HAPUS ---------- */
        function hapusHarga(url) {
            hapusContext = {
                type: 'harga',
                url
            };
            document.getElementById('hapusTitle').textContent = 'Hapus Data Harga?';
            document.getElementById('hapusText').textContent =
                'Anda akan menghapus data harga ini. Tindakan ini tidak dapat dibatalkan.';
            setBusy(document.getElementById('hapusConfirmBtn'), false);
            bukaModal('modalHapus');
        }

        function hapusKategori(kategori) {
            hapusContext = {
                type: 'kategori',
                url: urlKategoriHapus.replace(':id', kategori.id_kategori),
            };

            document.getElementById('hapusTitle').textContent = 'Hapus Kategori Sampah?';
            document.getElementById('hapusText').innerHTML =
                `Anda akan menghapus kategori <strong>${escapeHtml(kategori.nama_kategori)}</strong>. Tindakan ini tidak dapat dibatalkan.`;
            setBusy(document.getElementById('hapusConfirmBtn'), false);
            bukaModal('modalHapus');
        }

        function konfirmasiHapus() {
            if (!hapusContext) return;
            const btn = document.getElementById('hapusConfirmBtn');
            setBusy(btn, true);

            kirimJson(hapusContext.url, 'DELETE')
                .then(() => {
                    tutupModal('modalHapus');
                    showToast('Berhasil dihapus', 'Data telah dihapus.');
                    hapusContext = null;
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menghapus', err.message, 'error');
                    setBusy(btn, false);
                });
        }

        /* ---------- TABEL: PENCARIAN & PAGINASI (per grup induk) ---------- */
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif

            initCombo('id_induk');
            initCombo('satuan');
            initCombo('perPageSelect', {
                readonly: true
            });
            initCombo('rekapTahun', {
                readonly: true
            });

            const searchInput = document.getElementById('searchKategori');
            const table = document.getElementById('kategoriTable');
            const perPageSelect = document.getElementById('perPageSelect');
            const paginationContainer = document.getElementById('paginationButtons');
            const tableInfo = document.getElementById('tableInfoPagination');
            const kosongCari = document.getElementById('kategoriKosongCari');

            let currentPage = 1;

            // Kelompokkan baris: induk + sub-nya selalu satu halaman
            const groups = [];
            if (table) {
                const map = new Map();
                table.querySelectorAll('tbody tr[data-row-kategori]').forEach(row => {
                    const key = row.dataset.group;
                    if (!map.has(key)) {
                        const g = {
                            rows: []
                        };
                        map.set(key, g);
                        groups.push(g);
                    }
                    map.get(key).rows.push(row);
                });
            }

            function render() {
                if (!table) return;

                const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const filtered = groups.filter(g => g.rows.some(r => r.textContent.toLowerCase().includes(
                    keyword)));

                if (kosongCari) {
                    kosongCari.style.display = (groups.length > 0 && filtered.length === 0) ? '' : 'none';
                }

                const perPage = parseInt(perPageSelect ? perPageSelect.value : 10, 10);
                const totalPages = Math.ceil(filtered.length / perPage) || 1;
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);

                groups.forEach(g => g.rows.forEach(r => r.style.display = 'none'));

                const start = (currentPage - 1) * perPage;
                const end = start + perPage;
                filtered.slice(start, end).forEach(g => g.rows.forEach(r => r.style.display = ''));

                if (tableInfo) {
                    tableInfo.innerHTML = filtered.length === 0 ?
                        'Tidak ada data yang ditampilkan' :
                        `Menampilkan kategori induk <strong>${start + 1}</strong> - <strong>${Math.min(end, filtered.length)}</strong> dari <strong>${filtered.length}</strong>`;
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

            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    currentPage = 1;
                    render();
                });
            }

            render();

            // Tutup modal: klik overlay & tombol Escape
            document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
                overlay.addEventListener('mousedown', function(e) {
                    if (e.target === overlay) overlay.classList.remove('active');
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay.active').forEach(o => o.classList.remove(
                        'active'));
                }
            });
        });
    </script>
@endsection
