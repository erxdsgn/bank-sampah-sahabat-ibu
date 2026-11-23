@extends('admin.layouts.app')

@section('title', 'Kategori & Harga Sampah')
@section('active', 'kategori-harga')
@section('crumbs', 'Master Data | Kategori & Harga Sampah')

@push('styles')
    @vite('resources/css/pages/kategori-harga.css')
@endpush

@section('content')
<div class="kategori-page">

    @php
        $totalInduk = $kategoriInduk->count();
        $totalSub = $kategoriInduk->sum(fn($k) => $k->anak->count());
        $totalHarga = $kategoriInduk->reduce(function ($carry, $k) {
            $carry += $k->hargaTerbaru ? 1 : 0;
            $carry += $k->anak->filter(fn($s) => $s->hargaTerbaru)->count();
            return $carry;
        }, 0);
        $totalBelum = $totalInduk + $totalSub - $totalHarga;

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

        $iconEdit = '<svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
        $iconHarga = '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M15 8.5c-.6-.7-1.6-1.1-2.8-1.1-1.7 0-2.8.9-2.8 2.2 0 1.2 1 1.8 2.8 2.2 1.8.4 2.8 1 2.8 2.2 0 1.3-1.1 2.2-2.9 2.2-1.3 0-2.4-.5-3.1-1.3M12 5v14"></path></svg>';
        $iconHapus = '<svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6M14 11v6"></path></svg>';
    @endphp

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">MASTER DATA</span>
            <h1 class="hero-title">Kategori & <span class="accent">Harga Sampah</span></h1>
            <p class="hero-sub">Kelola kategori sampah beserta riwayat dan penetapan harganya.</p>
        </div>
        <div class="hero-actions">
            <button class="btn btn--primary btn-add-kategori" type="button" onclick="bukaModalTambahKategori()">
                <span class="btn-icon-plus">
                    <svg viewBox="0 0 24 24">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </span>
                <span>Tambah Kategori</span>
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
                    <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 10h18"></path></svg>
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
                <div class="summary-icon summary-icon--sub">
                    <svg viewBox="0 0 24 24"><path d="M6 3v12a3 3 0 0 0 3 3h9"></path><circle cx="18" cy="6" r="2"></circle></svg>
                </div>
            </div>
            <div class="summary-info">Jumlah kategori turunan</div>
        </div>

        <div class="summary-card summary-card--featured">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Sudah Ada Harga</div>
                    <div class="summary-value">{{ $totalHarga }}</div>
                </div>
                <div class="summary-icon summary-icon--income">
                    <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"></path></svg>
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
                    <svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"></path></svg>
                </div>
            </div>
            <div class="summary-info">Kategori perlu ditetapkan harga</div>
        </div>
    </section>

    <!-- MAIN TABLE CARD -->
    <section class="card table-card">
        <div class="table-toolbar">
            <div class="table-search">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"></circle><path d="m21 21-4.3-4.3"></path></svg>
                <input type="text" id="searchKategori" placeholder="Cari nama kategori atau satuan..." autocomplete="off">
            </div>
            <div class="toolbar-right">
                <button class="btn btn-bulanan" type="button" onclick="bukaModalBulanan()">
                    <span class="btn-icon-wrap">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"></rect><path d="M3 10h18M8 2v4M16 2v4"></path></svg>
                    </span>
                    <span>Harga per Bulan</span>
                </button>
                <button class="btn btn-refresh" type="button" onclick="window.location.reload()">
                    <svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path><path d="M21 3v5h-5"></path></svg>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="kategoriTable">
                <thead>
                    <tr>
                        <th class="col-no">No</th>
                        <th>Nama Kategori</th>
                        <th>Satuan Unit</th>
                        <th>Berlaku Sejak</th>
                        <th>Harga Aktif</th>
                        <th>Status</th>
                        <th class="col-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody id="kategoriTableBody">
                    @forelse($kategoriInduk as $index => $kategori)
                        @php
                            $tglInduk = $kategori->hargaTerbaru ? \Carbon\Carbon::parse($kategori->hargaTerbaru->tanggal_berlaku) : null;
                        @endphp
                        <tr data-row-kategori data-group="{{ $kategori->id_kategori }}">
                            <td class="col-no">{{ $index + 1 }}</td>
                            <td><strong>{{ $kategori->nama_kategori }}</strong></td>
                            <td><span class="badge badge--satuan">{{ $formatSatuan($kategori->satuan) }}</span></td>
                            <td class="nowrap">{{ $tglInduk ? $tglInduk->translatedFormat('d M Y') : '-' }}</td>
                            <td>
                                @if ($kategori->hargaTerbaru)
                                    <strong class="saldo">Rp {{ number_format($kategori->hargaTerbaru->harga_satuan, 0, ',', '.') }}</strong>
                                @else
                                    <span class="muted">Belum diatur</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $kategori->hargaTerbaru ? 'badge--success' : 'badge--warning' }}">
                                    {{ $kategori->hargaTerbaru ? 'Aktif' : 'Belum Diatur' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="table-actions">
                                    <button class="icon-btn action-btn--edit" title="Edit Kategori" type="button"
                                        data-payload="{{ json_encode(['id_kategori' => $kategori->id_kategori, 'id_induk' => $kategori->id_induk, 'nama_kategori' => $kategori->nama_kategori, 'satuan' => $kategori->satuan, 'punya_anak' => $kategori->anak->count() > 0]) }}"
                                        onclick="editKategoriModal(JSON.parse(this.dataset.payload))">
                                        {!! $iconEdit !!}
                                    </button>
                                    <button class="icon-btn icon-btn--harga action-btn--harga" title="Kelola Harga" type="button"
                                        data-payload="{{ json_encode(['id_kategori' => $kategori->id_kategori, 'nama_kategori' => $kategori->nama_kategori, 'satuan' => $kategori->satuan, 'store_url' => route('admin.kategori-harga.harga.store', $kategori->id_kategori)]) }}"
                                        onclick="kelolaHarga(JSON.parse(this.dataset.payload))">
                                        {!! $iconHarga !!}
                                    </button>
                                    <button class="icon-btn icon-btn--danger action-btn--delete" title="Hapus Kategori" type="button"
                                        data-payload="{{ json_encode(['id_kategori' => $kategori->id_kategori, 'nama_kategori' => $kategori->nama_kategori]) }}"
                                        onclick="hapusKategori(JSON.parse(this.dataset.payload))">
                                        {!! $iconHapus !!}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @foreach ($kategori->anak as $sub)
                            @php
                                $tglSub = $sub->hargaTerbaru ? \Carbon\Carbon::parse($sub->hargaTerbaru->tanggal_berlaku) : null;
                            @endphp
                            <tr data-row-kategori data-group="{{ $kategori->id_kategori }}" class="sub-row">
                                <td class="col-no"></td>
                                <td class="sub-cell"><span class="sub-arrow">↳</span> {{ $sub->nama_kategori }}</td>
                                <td><span class="badge badge--satuan">{{ $formatSatuan($sub->satuan) }}</span></td>
                                <td class="nowrap">{{ $tglSub ? $tglSub->translatedFormat('d M Y') : '-' }}</td>
                                <td>
                                    @if ($sub->hargaTerbaru)
                                        <strong class="saldo">Rp {{ number_format($sub->hargaTerbaru->harga_satuan, 0, ',', '.') }}</strong>
                                    @else
                                        <span class="muted">Belum diatur</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $sub->hargaTerbaru ? 'badge--success' : 'badge--warning' }}">
                                        {{ $sub->hargaTerbaru ? 'Aktif' : 'Belum Diatur' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="table-actions">
                                        <button class="icon-btn action-btn--edit" title="Edit Kategori" type="button"
                                            data-payload="{{ json_encode(['id_kategori' => $sub->id_kategori, 'id_induk' => $sub->id_induk, 'nama_kategori' => $sub->nama_kategori, 'satuan' => $sub->satuan, 'punya_anak' => false]) }}"
                                            onclick="editKategoriModal(JSON.parse(this.dataset.payload))">
                                            {!! $iconEdit !!}
                                        </button>
                                        <button class="icon-btn icon-btn--harga action-btn--harga" title="Kelola Harga" type="button"
                                            data-payload="{{ json_encode(['id_kategori' => $sub->id_kategori, 'nama_kategori' => $sub->nama_kategori, 'satuan' => $sub->satuan, 'store_url' => route('admin.kategori-harga.harga.store', $sub->id_kategori)]) }}"
                                            onclick="kelolaHarga(JSON.parse(this.dataset.payload))">
                                            {!! $iconHarga !!}
                                        </button>
                                        <button class="icon-btn icon-btn--danger action-btn--delete" title="Hapus Kategori" type="button"
                                            data-payload="{{ json_encode(['id_kategori' => $sub->id_kategori, 'nama_kategori' => $sub->nama_kategori]) }}"
                                            onclick="hapusKategori(JSON.parse(this.dataset.payload))">
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
                    <div class="combo combo--up combo--arrow combo--pill" id="combo_perPageSelect">
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

    <!-- MODAL 1: TAMBAH / EDIT KATEGORI -->
    <div class="modal-overlay" id="modalKategori">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title" id="modalKategoriTitle">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span id="modalKategoriTitleText">Tambah Kategori</span>
                </h3>
                <button class="modal-close" type="button" aria-label="Tutup" onclick="tutupModal('modalKategori')">&times;</button>
            </div>
            <form id="formKategori" class="modal-form" onsubmit="return simpanKategori(event)">
                <div class="modal-body">
                    <input type="hidden" id="kategoriId">
                    <div class="form-grid">
                        <div class="form-group form-group--full">
                            <label for="cari_id_induk">Kategori Induk</label>
                            <div class="combo combo--arrow" id="combo_id_induk">
                                <input type="text" id="cari_id_induk" class="combo-input" placeholder="Pilih kategori induk..." autocomplete="off">
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
                            <input type="text" id="nama_kategori" class="form-control" placeholder="Contoh: Botol Plastik" required autocomplete="off">
                            <span class="form-error" id="err_nama_kategori"></span>
                        </div>
                        <div class="form-group">
                            <label for="cari_satuan">Satuan Jual</label>
                            <div class="combo combo--arrow combo-satuan" id="combo_satuan">
                                <input type="text" id="cari_satuan" class="combo-input" placeholder="Pilih satuan..." autocomplete="off">
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
                            <input type="number" step="0.01" min="0" id="harga_awal" class="form-control" placeholder="Contoh: 5000">
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
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M15 8.5c-.6-.7-1.6-1.1-2.8-1.1-1.7 0-2.8.9-2.8 2.2 0 1.2 1 1.8 2.8 2.2 1.8.4 2.8 1 2.8 2.2 0 1.3-1.1 2.2-2.9 2.2-1.3 0-2.4-.5-3.1-1.3M12 5v14"></path>
                    </svg>
                    <span>Kelola Harga — <span id="hargaNamaKategori">-</span></span>
                </h3>
                <button class="modal-close" type="button" aria-label="Tutup" onclick="tutupModal('modalHarga')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formHarga" onsubmit="return tambahHarga(event)">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="harga_satuan">Harga per <span id="hargaSatuanLabel">satuan</span> (Rp)</label>
                            <input type="number" step="0.01" min="0" id="harga_satuan" class="form-control" placeholder="Contoh: 5000" required>
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
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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
                                <th class="text-center">Aksi</th>
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
                <button class="btn btn--danger" type="button" id="hapusConfirmBtn" onclick="konfirmasiHapus()">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <!-- MODAL 4: REKAP HARGA PER BULAN -->
    <div class="modal-overlay" id="modalBulanan">
        <div class="modal-box modal-box--xl">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <rect x="3" y="4" width="18" height="17" rx="2"></rect>
                        <path d="M3 10h18M8 2v4M16 2v4"></path>
                    </svg>
                    <span>Update Harga per Bulan</span>
                </h3>
                <button class="modal-close" type="button" aria-label="Tutup" onclick="tutupModal('modalBulanan')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="bulan-bar">
                    <button class="btn btn--ghost btn-nav-year" type="button" onclick="geserTahun(-1)" aria-label="Tahun sebelumnya">‹</button>
                    <div class="combo combo--arrow combo--pill" id="combo_rekapTahun">
                        <input type="text" id="cari_rekapTahun" class="combo-input" placeholder="" autocomplete="off">
                        <div class="combo-list" id="list_rekapTahun"></div>
                    </div>
                    <select id="rekapTahun" class="combo-hidden" tabindex="-1" aria-hidden="true" onchange="renderRekap()"></select>
                    <button class="btn btn--ghost btn-nav-year" type="button" onclick="geserTahun(1)" aria-label="Tahun berikutnya">›</button>
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

</div>
@endsection

@push('scripts')
    <script>
        window.__KATEGORI_DATA__ = {
            riwayat: @json($riwayatMap),
            daftar: @json($daftarKategori),
            urls: {
                store: "{{ route('admin.kategori-harga.store') }}",
                update: "{{ route('admin.kategori-harga.update', ':id') }}",
                destroy: "{{ route('admin.kategori-harga.destroy', ':id') }}"
            }
        };
        window.__FLASH_SUCCESS__ = @json(session('success'));
    </script>
    @vite('resources/js/pages/kategori-harga.js')
@endpush
