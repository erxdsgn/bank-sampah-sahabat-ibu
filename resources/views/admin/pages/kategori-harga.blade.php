@extends('admin.layouts.app')

@section('title', 'Kategori & Harga Sampah')
@section('active', 'kategori-harga')
@section('crumbs', 'Master Data | Kategori & Harga Sampah')

@section('content')

    @php
        // 1. Ringkasan Statistik Data
        $totalInduk = $kategoriInduk->count();
        $totalSub = $kategoriInduk->sum(fn($k) => $k->anak->count());
        $totalHarga = $kategoriInduk->reduce(function ($carry, $k) {
            $carry += $k->hargaTerbaru ? 1 : 0;
            $carry += $k->anak->filter(fn($s) => $s->hargaTerbaru)->count();
            return $carry;
        }, 0);
        $totalBelum = $totalInduk + $totalSub - $totalHarga;

        // 2. Pemetaan Riwayat Harga Kategori
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

        // 3. Daftar Kategori untuk Rekap Bulanan
        $daftarKategori = [];
        foreach ($kategoriInduk as $k) {
            $daftarKategori[] = [
                'id' => $k->id_kategori,
                'nama' => $k->nama_kategori,
                'satuan' => $k->satuan,
                'sub' => false,
            ];
            foreach ($k->anak as $s) {
                $daftarKategori[] = [
                    'id' => $s->id_kategori,
                    'nama' => $s->nama_kategori,
                    'satuan' => $s->satuan,
                    'sub' => true,
                ];
            }
        }
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
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"></path>
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

    <!-- DATA TABLE CONTAINER -->
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
                        <th>Satuan</th>
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
                        <tr data-row-kategori>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $kategori->nama_kategori }}</strong></td>
                            <td><span class="badge badge--satuan">{{ $kategori->satuan ?? '-' }}</span></td>
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
                                        onclick="editKategoriModal({{ Js::from(['id_kategori' => $kategori->id_kategori, 'id_induk' => $kategori->id_induk, 'nama_kategori' => $kategori->nama_kategori, 'satuan' => $kategori->satuan]) }})">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                    <button class="icon-btn icon-btn--harga" title="Kelola Harga" type="button"
                                        onclick="kelolaHarga({{ Js::from(['id_kategori' => $kategori->id_kategori, 'nama_kategori' => $kategori->nama_kategori, 'satuan' => $kategori->satuan, 'store_url' => route('admin.kategori-harga.harga.store', $kategori->id_kategori)]) }})">
                                        <svg viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="9"></circle>
                                            <path
                                                d="M15 8.5c-.6-.7-1.6-1.1-2.8-1.1-1.7 0-2.8.9-2.8 2.2 0 1.2 1 1.8 2.8 2.2 1.8.4 2.8 1 2.8 2.2 0 1.3-1.1 2.2-2.9 2.2-1.3 0-2.4-.5-3.1-1.3M12 5v14">
                                            </path>
                                        </svg>
                                    </button>
                                    <button class="icon-btn icon-btn--danger" title="Hapus Kategori" type="button"
                                        onclick="hapusKategori({{ Js::from(['id_kategori' => $kategori->id_kategori, 'nama_kategori' => $kategori->nama_kategori]) }})">
                                        <svg viewBox="0 0 24 24">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                            <path d="M10 11v6M14 11v6"></path>
                                        </svg>
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
                            <tr data-row-kategori class="sub-row">
                                <td></td>
                                <td class="sub-cell"><span class="sub-arrow">↳</span> {{ $sub->nama_kategori }}</td>
                                <td><span class="badge badge--satuan">{{ $sub->satuan ?? '-' }}</span></td>
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
                                            onclick="editKategoriModal({{ Js::from(['id_kategori' => $sub->id_kategori, 'id_induk' => $sub->id_induk, 'nama_kategori' => $sub->nama_kategori, 'satuan' => $sub->satuan]) }})">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </button>
                                        <button class="icon-btn icon-btn--harga" title="Kelola Harga" type="button"
                                            onclick="kelolaHarga({{ Js::from(['id_kategori' => $sub->id_kategori, 'nama_kategori' => $sub->nama_kategori, 'satuan' => $sub->satuan, 'store_url' => route('admin.kategori-harga.harga.store', $sub->id_kategori)]) }})">
                                            <svg viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="9"></circle>
                                                <path
                                                    d="M15 8.5c-.6-.7-1.6-1.1-2.8-1.1-1.7 0-2.8.9-2.8 2.2 0 1.2 1 1.8 2.8 2.2 1.8.4 2.8 1 2.8 2.2 0 1.3-1.1 2.2-2.9 2.2-1.3 0-2.4-.5-3.1-1.3M12 5v14">
                                                </path>
                                            </svg>
                                        </button>
                                        <button class="icon-btn icon-btn--danger" title="Hapus Kategori" type="button"
                                            onclick="hapusKategori({{ Js::from(['id_kategori' => $sub->id_kategori, 'nama_kategori' => $sub->nama_kategori]) }})">
                                            <svg viewBox="0 0 24 24">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                                <path d="M10 11v6M14 11v6"></path>
                                            </svg>
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
            <div class="table-info">Total kategori induk: <strong>{{ $totalInduk }}</strong></div>
        </div>
    </section>

    <!-- TOAST CONTAINER -->
    <div class="toast-wrap" id="toastWrap"></div>

    <!-- MODAL 1: TAMBAH / EDIT KATEGORI -->
    <div class="modal-overlay" id="modalKategori">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title" id="modalKategoriTitle">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    <span>Tambah Kategori</span>
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalKategori')">&times;</button>
            </div>

            <form id="formKategori" onsubmit="return simpanKategori(event)">
                <div class="modal-body">
                    <input type="hidden" id="kategoriId">
                    <div class="form-grid">
                        <div class="form-group form-group--full">
                            <label for="id_induk">Kategori Induk (opsional)</label>
                            <select id="id_induk" class="form-control">
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
                                placeholder="Contoh: Botol Plastik" required>
                            <span class="form-error" id="err_nama_kategori"></span>
                        </div>

                        <div class="form-group">
                            <label for="satuan">Satuan Jual</label>
                            <select id="satuan" class="form-control" required>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="gram">Gram (gram)</option>
                                <option value="liter">Liter (L)</option>
                            </select>
                            <span class="form-error" id="err_satuan"></span>
                        </div>

                        <div class="form-group" id="blokHargaAwal">
                            <label for="harga_awal">Harga Satuan (Rp)</label>
                            <input type="number" step="0.01" id="harga_awal" class="form-control"
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
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path
                            d="M15 8.5c-.6-.7-1.6-1.1-2.8-1.1-1.7 0-2.8.9-2.8 2.2 0 1.2 1 1.8 2.8 2.2 1.8.4 2.8 1 2.8 2.2 0 1.3-1.1 2.2-2.9 2.2-1.3 0-2.4-.5-3.1-1.3M12 5v14">
                        </path>
                    </svg>
                    Kelola Harga — <span id="hargaNamaKategori">-</span>
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalHarga')">&times;</button>
            </div>

            <div class="modal-body">
                <form id="formHarga" onsubmit="return tambahHarga(event)">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="harga_satuan">Harga per <span id="hargaSatuanLabel">satuan</span> (Rp)</label>
                            <input type="number" step="0.01" id="harga_satuan" class="form-control"
                                placeholder="Contoh: 5000" required>
                            <span class="form-error" id="err_harga_satuan"></span>
                        </div>

                        <div class="form-group">
                            <label for="tanggal_berlaku">Berlaku Mulai</label>
                            <input type="date" id="tanggal_berlaku" class="form-control" required>
                            <span class="form-error" id="err_tanggal_berlaku"></span>
                        </div>
                    </div>

                    <div class="form-actions form-actions--modal">
                        <button class="btn btn--primary" type="submit">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 5v14M5 12h14"></path>
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
                <button class="btn btn--danger" type="button" onclick="konfirmasiHapus()">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <!-- MODAL 4: REKAP HARGA PER BULAN -->
    <div class="modal-overlay" id="modalBulanan">
        <div class="modal-box modal-box--xl">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="17" rx="2"></rect>
                        <path d="M3 10h18M8 2v4M16 2v4"></path>
                    </svg>
                    Update Harga per Bulan
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalBulanan')">&times;</button>
            </div>

            <div class="modal-body">
                <div class="bulan-bar">
                    <button class="btn btn--ghost" type="button" onclick="geserTahun(-1)">‹</button>
                    <select id="rekapTahun" class="form-control" onchange="renderRekap()"></select>
                    <button class="btn btn--ghost" type="button" onclick="geserTahun(1)">›</button>
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
        /* =========================================================
               DASHBOARD THEME
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
            --dashboard-table-hover: #f8fafc;

            --dashboard-input: #ffffff;
            --dashboard-row-strong: #e5e7eb;

            --dashboard-shadow: 0 8px 25px rgba(15, 23, 42, .06);
            --dashboard-shadow-lg: 0 18px 45px rgba(15, 23, 42, .12);

            --primary-color: var(--primary, #4338ca);
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

            --primary-color: #818cf8;
        }


        /* =========================================================
               GLOBAL
               ========================================================= */

        body {
            background: var(--dashboard-page);
            color: var(--dashboard-text);
            transition: background-color .25s ease, color .25s ease;
        }

        * {
            box-sizing: border-box;
        }


        /* =========================================================
               SUMMARY CARD
               ========================================================= */

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .summary-card {
            border: 1px solid var(--dashboard-border);
            background: var(--dashboard-card);
            border-radius: 14px;
            padding: 18px;
            box-shadow: var(--dashboard-shadow);
            transition: transform .2s ease, box-shadow .2s ease, background .25s ease, border-color .25s ease;
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
            background: #eef2ff;
            color: var(--primary-color);
        }

        .summary-icon--income {
            background: #dcfce7;
            color: #16a34a;
        }

        .summary-icon--expense {
            background: #fee2e2;
            color: #dc2626;
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


        /* =========================================================
               CARD
               ========================================================= */

        .card {
            background: var(--dashboard-card);
            border: 1px solid var(--dashboard-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--dashboard-shadow);
            color: var(--dashboard-text);
        }


        /* =========================================================
               TABLE TOOLBAR
               ========================================================= */

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
            background: var(--dashboard-input);
            color: var(--dashboard-text);
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .table-search input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 56, 202, .08);
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }


        /* =========================================================
               TABLE RESPONSIVE
               ========================================================= */

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive::-webkit-scrollbar,
        .rekap-wrap::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .table-responsive::-webkit-scrollbar-track,
        .rekap-wrap::-webkit-scrollbar-track {
            background: transparent;
        }

        .table-responsive::-webkit-scrollbar-thumb,
        .rekap-wrap::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 20px;
        }

        [data-theme="dark"] .table-responsive::-webkit-scrollbar-thumb,
        [data-theme="dark"] .rekap-wrap::-webkit-scrollbar-thumb {
            background: #475569;
        }


        /* =========================================================
               MAIN DATA TABLE
               ========================================================= */

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
        }

        .data-table tbody tr:hover td {
            background: var(--dashboard-table-hover);
        }

        .sub-row td {
            background: var(--dashboard-card-secondary);
        }

        .sub-cell {
            padding-left: 28px;
            color: var(--dashboard-text-secondary);
        }

        .sub-arrow {
            opacity: .5;
        }

        .nowrap,
        .saldo {
            white-space: nowrap;
        }

        .muted,
        .empty-desc {
            color: var(--dashboard-text-secondary);
        }

        .text-center {
            text-align: center;
        }


        /* =========================================================
               BADGE
               ========================================================= */

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
            background: #e0f2fe;
            color: #0369a1;
        }

        .badge--success {
            background: #dcfce7;
            color: #15803d;
        }

        .badge--warning {
            background: #fef3c7;
            color: #b45309;
        }


        /* =========================================================
               TABLE ACTIONS
               ========================================================= */

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
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s ease, border-color .15s ease, color .15s ease, transform .15s ease;
            color: var(--dashboard-text-secondary);
        }

        .icon-btn:hover {
            transform: translateY(-1px);
        }

        .icon-btn svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .icon-btn--harga {
            color: #16a34a;
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .icon-btn--harga:hover {
            background: #dcfce7;
            border-color: #86efac;
            color: #15803d;
        }

        .icon-btn--danger {
            color: #dc2626;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .icon-btn--danger:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        .icon-btn--danger.icon-btn--sm {
            width: 34px;
            padding: 0;
        }


        /* =========================================================
               TABLE FOOTER & EMPTY STATE
               ========================================================= */

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
        }

        .table-info {
            font-size: 13px;
            color: var(--dashboard-text-secondary);
        }

        .catalog-empty {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-state-cell {
            text-align: center;
            padding: 40px;
        }

        .empty-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }


        /* =========================================================
               MONTH BAR
               ========================================================= */

        .bulan-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .bulan-bar .form-control {
            width: auto;
            min-width: 110px;
            padding: 8px 12px;
        }

        .bulan-bar .btn {
            padding: 6px 12px;
        }


        /* =========================================================
               REKAP HARGA BULANAN (GARIS SAJA / TANPA SHADOW)
               ========================================================= */

        .modal-box--xl {
            max-width: 1180px;
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
        }


        /* =========================================================
               REKAP TABLE
               ========================================================= */

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

        .rekap-table tr th:first-child,
        .rekap-table tr td:first-child {
            border-left: 0;
        }


        /* =========================================================
               REKAP HEADER STICKY
               ========================================================= */

        .rekap-table thead th {
            background: var(--dashboard-table-head);
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            position: sticky;
            top: 0;
            z-index: 30;
            border-bottom: 2px solid var(--dashboard-border);
        }

        .rekap-table thead tr:first-child th {
            top: 0;
            z-index: 31;
        }

        .rekap-table thead tr:nth-child(2) th {
            top: 34px;
            z-index: 30;
        }


        /* =========================================================
               STICKY COLUMN "JENIS SAMPAH" (GARIS SAJA)
               ========================================================= */

        .rekap-table .col-nama {
            position: sticky;
            left: 0;
            width: 240px;
            min-width: 240px;
            max-width: 240px;
            text-align: left;
            background: var(--dashboard-card) !important;
            color: var(--dashboard-text);
            z-index: 40;
            border-right: 2px solid var(--dashboard-border) !important;
            overflow: hidden;
            isolation: isolate;
        }

        .rekap-table thead .col-nama {
            top: 0;
            background: var(--dashboard-table-head) !important;
            z-index: 50;
            border-right: 2px solid var(--dashboard-border) !important;
        }

        .rekap-table .col-nama.sub {
            padding-left: 22px;
            background: var(--dashboard-card) !important;
            color: var(--dashboard-text);
            z-index: 40;
        }

        .rekap-table .col-nama small {
            color: var(--dashboard-text-secondary);
            margin-left: 6px;
            font-size: 11px;
        }


        /* =========================================================
               BARIS KATEGORI INDUK
               ========================================================= */

        .rekap-table tr.baris-induk td {
            background: var(--dashboard-row-strong);
            font-weight: 800;
            text-transform: uppercase;
            color: var(--dashboard-text);
        }

        .rekap-table tr.baris-induk .col-nama {
            position: sticky;
            left: 0;
            width: 240px;
            min-width: 240px;
            max-width: 240px;
            background: var(--dashboard-row-strong) !important;
            color: var(--dashboard-text);
            font-weight: 800;
            z-index: 45;
            border-right: 2px solid var(--dashboard-border) !important;
        }


        /* =========================================================
               HARGA BERUBAH & KOSONG
               ========================================================= */

        .rekap-table td.berubah {
            background: #fef3c7;
            font-weight: 700;
            color: #1f2937;
            border: 1px solid #fcd34d;
        }

        .rekap-table td.kosong {
            color: var(--dashboard-text-muted);
            text-align: center;
            font-weight: 500;
        }

        .rekap-table tbody td:not(.col-nama) {
            position: relative;
            z-index: 1;
        }

        .legend-info {
            margin: 12px 0 0;
            font-size: 12px;
            line-height: 1.6;
            color: var(--dashboard-text-secondary);
        }


        /* =========================================================
               FORM & MODAL
               ========================================================= */

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px 20px;
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
            text-transform: uppercase;
            color: var(--dashboard-text-secondary);
        }

        .form-control {
            width: 100%;
            min-height: 40px;
            padding: 10px 14px;
            font-size: 13px;
            color: var(--dashboard-text);
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            outline: none;
            background: var(--dashboard-input);
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 56, 202, .08);
        }

        .form-control.is-invalid {
            border-color: #dc2626;
        }

        .form-error {
            display: block;
            margin-top: 2px;
            font-size: 12px;
            color: #dc2626;
            min-height: 14px;
        }

        .form-actions--modal {
            display: flex;
            justify-content: flex-end;
            margin-top: 4px;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .60);
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 1000;
            backdrop-filter: blur(2px);
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            width: 100%;
            max-width: 620px;
            max-height: 90vh;
            overflow-y: auto;
            background: var(--dashboard-card);
            border: 1px solid var(--dashboard-border);
            border-radius: 14px;
            box-shadow: var(--dashboard-shadow-lg);
            animation: modalPop .15s ease-out;
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

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 20px 24px;
            border-bottom: 1px solid var(--dashboard-border);
            background: var(--dashboard-card);
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
            color: var(--dashboard-text-secondary);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background .15s ease, color .15s ease;
        }

        .modal-close:hover {
            background: var(--dashboard-table-hover);
            color: var(--dashboard-text);
        }

        .modal-body {
            padding: 22px 24px;
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
            background: var(--dashboard-card);
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

        .confirm-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 14px;
            border-radius: 50%;
            background: #fef2f2;
            color: #dc2626;
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


        /* =========================================================
               TOAST
               ========================================================= */

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
            box-shadow: var(--dashboard-shadow-lg);
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
            background: #dcfce7;
            color: #16a34a;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toast.toast--error .toast-icon {
            background: #fee2e2;
            color: #dc2626;
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
               DARK MODE
               ========================================================= */

        [data-theme="dark"] .summary-icon {
            background: rgba(99, 102, 241, .18);
            color: #a5b4fc;
        }

        [data-theme="dark"] .summary-icon--income {
            background: rgba(34, 197, 94, .16);
            color: #4ade80;
        }

        [data-theme="dark"] .summary-icon--expense {
            background: rgba(220, 38, 38, .16);
            color: #f87171;
        }

        [data-theme="dark"] .badge--satuan {
            background: rgba(3, 105, 161, .20);
            color: #38bdf8;
        }

        [data-theme="dark"] .badge--success {
            background: rgba(34, 197, 94, .16);
            color: #4ade80;
        }

        [data-theme="dark"] .badge--warning {
            background: rgba(245, 158, 11, .18);
            color: #fbbf24;
        }

        [data-theme="dark"] .icon-btn {
            background: #151d2f;
            border-color: #29364d;
        }

        [data-theme="dark"] .icon-btn--harga {
            background: rgba(34, 197, 94, .12);
            border-color: rgba(34, 197, 94, .35);
            color: #4ade80;
        }

        [data-theme="dark"] .icon-btn--harga:hover {
            background: rgba(34, 197, 94, .20);
            border-color: rgba(34, 197, 94, .50);
            color: #86efac;
        }

        [data-theme="dark"] .icon-btn--danger {
            background: rgba(220, 38, 38, .12);
            border-color: rgba(220, 38, 38, .35);
            color: #f87171;
        }

        [data-theme="dark"] .icon-btn--danger:hover {
            background: rgba(220, 38, 38, .20);
            border-color: rgba(220, 38, 38, .50);
            color: #fca5a5;
        }


        /* =========================================================
               DARK MODE - REKAP TABLE
               ========================================================= */

        [data-theme="dark"] .rekap-table {
            background: #151d2f;
        }

        [data-theme="dark"] .rekap-table th,
        [data-theme="dark"] .rekap-table td {
            border-color: #29364d;
            color: #f1f5f9;
            background: #151d2f;
        }

        [data-theme="dark"] .rekap-table thead th {
            background: #111a2c;
            color: #aab6c8;
            border-bottom: 2px solid #29364d;
        }

        [data-theme="dark"] .rekap-table .col-nama {
            background: #151d2f !important;
            color: #f1f5f9;
            border-right: 2px solid #29364d !important;
        }

        [data-theme="dark"] .rekap-table thead .col-nama {
            background: #111a2c !important;
            border-right: 2px solid #29364d !important;
        }

        [data-theme="dark"] .rekap-table tr.baris-induk td {
            background: #29364d;
            color: #f1f5f9;
        }

        [data-theme="dark"] .rekap-table tr.baris-induk .col-nama {
            background: #29364d !important;
            color: #f1f5f9;
            border-right: 2px solid #3b4d6d !important;
        }

        [data-theme="dark"] .rekap-table .col-nama small {
            color: #aab6c8;
        }

        [data-theme="dark"] .rekap-table td.berubah {
            background: rgba(245, 158, 11, .20);
            color: #fde68a;
            border: 1px solid rgba(245, 158, 11, .40);
        }

        [data-theme="dark"] .rekap-table td.kosong {
            color: #748198;
        }

        [data-theme="dark"] .rekap-legend .dot {
            background: rgba(245, 158, 11, .25);
            border-color: #fbbf24;
        }


        /* =========================================================
               DARK MODE - DATA TABLE & MODAL / FORM
               ========================================================= */

        [data-theme="dark"] .data-table {
            background: #151d2f;
            color: #f1f5f9;
        }

        [data-theme="dark"] .data-table th {
            background: #111a2c;
            color: #aab6c8;
            border-color: #29364d;
        }

        [data-theme="dark"] .data-table td {
            background: #151d2f;
            color: #f1f5f9;
            border-color: #29364d;
        }

        [data-theme="dark"] .data-table tbody tr:hover td {
            background: #202b40;
        }

        [data-theme="dark"] .sub-row td {
            background: #1b2438;
        }

        [data-theme="dark"] .form-control,
        [data-theme="dark"] .table-search input {
            background: #1b2438;
            color: #f1f5f9;
            border-color: #29364d;
        }

        [data-theme="dark"] .form-control:focus,
        [data-theme="dark"] .table-search input:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 3px rgba(129, 140, 248, .12);
        }

        [data-theme="dark"] .form-control::placeholder,
        [data-theme="dark"] .table-search input::placeholder {
            color: rgba(255, 255, 255, .35);
        }

        [data-theme="dark"] .modal-overlay {
            background: rgba(0, 0, 0, .68);
        }

        [data-theme="dark"] .modal-box,
        [data-theme="dark"] .modal-head,
        [data-theme="dark"] .modal-foot,
        [data-theme="dark"] .toast,
        [data-theme="dark"] .summary-card,
        [data-theme="dark"] .card {
            background: #151d2f;
            color: #f1f5f9;
            border-color: #29364d;
        }

        [data-theme="dark"] .confirm-icon {
            background: rgba(220, 38, 38, .16);
            color: #f87171;
        }

        [data-theme="dark"] .toast-icon {
            background: rgba(34, 197, 94, .16);
            color: #4ade80;
        }

        [data-theme="dark"] .toast.toast--error .toast-icon {
            background: rgba(220, 38, 38, .16);
            color: #f87171;
        }


        /* =========================================================
               RESPONSIVE - TABLET & MOBILE
               ========================================================= */

        @media (max-width: 1100px) {
            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .table-search {
                width: 320px;
            }

            .modal-box--xl {
                max-width: calc(100vw - 30px);
            }
        }

        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }

            .summary-card {
                padding: 14px;
            }

            .summary-value {
                font-size: 18px;
            }

            .summary-icon {
                width: 36px;
                height: 36px;
                flex-basis: 36px;
            }

            .table-toolbar {
                padding: 14px 16px;
            }

            .table-search {
                width: 100%;
            }

            .toolbar-right {
                width: 100%;
            }

            .rekap-wrap {
                max-height: 65vh;
                margin-left: -1px;
                margin-right: -1px;
                border-radius: 8px;
            }

            .rekap-table .col-nama,
            .rekap-table tr.baris-induk .col-nama {
                width: 210px;
                min-width: 210px;
                max-width: 210px;
            }

            .modal-overlay {
                padding: 12px;
            }

            .modal-box {
                max-height: 94vh;
            }

            .modal-head,
            .modal-body,
            .modal-foot {
                padding-left: 18px;
                padding-right: 18px;
            }

            .toast-wrap {
                left: 12px;
                right: 12px;
                top: 12px;
            }

            .toast {
                min-width: 0;
                width: 100%;
                max-width: none;
            }
        }

        @media (max-width: 560px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .summary-card {
                padding: 16px;
            }

            .table-toolbar {
                align-items: stretch;
            }

            .toolbar-right {
                flex-direction: column;
                align-items: stretch;
            }

            .bulan-bar {
                width: 100%;
            }

            .bulan-bar .form-control {
                flex: 1;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group--full {
                grid-column: auto;
            }

            .modal-foot {
                flex-wrap: wrap;
            }

            .modal-foot .btn {
                flex: 1;
            }

            .rekap-table .col-nama,
            .rekap-table tr.baris-induk .col-nama {
                width: 190px;
                min-width: 190px;
                max-width: 190px;
            }
        }
    </style>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        const riwayatData = @json($riwayatMap);
        const daftarKategori = @json($daftarKategori);

        const urlKategoriStore = "{{ route('admin.kategori-harga.store') }}";
        const urlKategoriUpdate = "{{ route('admin.kategori-harga.update', ':id') }}";
        const urlKategoriHapus = "{{ route('admin.kategori-harga.destroy', ':id') }}";

        const BULAN_SINGKAT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const FIELD_KATEGORI = ['id_induk', 'nama_kategori', 'satuan', 'harga_awal', 'tanggal_awal'];

        let hargaContext = null;
        let hapusContext = null;

        function getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.content : '';
        }

        function tanggalHariIni() {
            const d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2,
                '0');
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
                    <p class="toast-title">${title}</p>
                    <p class="toast-text">${text}</p>
                </div>
                <button class="toast-close" type="button" aria-label="Tutup">&times;</button>
            `;

            const hapusToast = () => {
                toast.classList.add('toast--leaving');
                setTimeout(() => toast.remove(), 180);
            };

            toast.querySelector('.toast-close').addEventListener('click', hapusToast);
            wrap.appendChild(toast);
            setTimeout(hapusToast, 3500);
        }

        function clearFormErrors(prefix, fields) {
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

            renderRekap();
            bukaModal('modalBulanan');
        }

        function geserTahun(delta) {
            const sel = document.getElementById('rekapTahun');
            const idx = sel.selectedIndex - delta;
            if (idx >= 0 && idx < sel.options.length) {
                sel.selectedIndex = idx;
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
                    <th colspan="12">Harga Nasabah Tahun ${tahun}</th>
                </tr>
                <tr>${BULAN_SINGKAT.map(b => `<th>${b}</th>`).join('')}</tr>
            `;

            if (daftarKategori.length === 0) {
                body.innerHTML = '<tr><td colspan="13" class="text-center empty-desc">Belum ada kategori.</td></tr>';
                return;
            }

            body.innerHTML = daftarKategori.map(k => {
                let prev = null;
                const cells = BULAN_SINGKAT.map((_, i) => {
                    const ym = tahun + '-' + String(i + 1).padStart(2, '0');
                    const h = hargaPadaBulan(k.id, ym);
                    const val = h ? h.harga : null;
                    const berubah = val !== null && prev !== null && val !== prev;

                    let berubahJan = false;
                    if (i === 0 && val !== null) {
                        const hp = hargaPadaBulan(k.id, (tahun - 1) + '-12');
                        berubahJan = !!hp && hp.harga !== val;
                    }
                    prev = val;

                    if (val === null) return '<td class="kosong">–</td>';
                    return `<td class="${(berubah || berubahJan) ? 'berubah' : ''}">${Number(val).toLocaleString('id-ID')}</td>`;
                }).join('');

                if (!k.sub) {
                    return `<tr class="baris-induk"><td class="col-nama">${k.nama}</td>${cells}</tr>`;
                }
                return `<tr><td class="col-nama sub"><span class="sub-arrow">↳</span> ${k.nama} <small>${k.satuan ?? ''}</small></td>${cells}</tr>`;
            }).join('');
        }

        function bukaModalTambahKategori() {
            document.getElementById('formKategori').reset();
            document.getElementById('kategoriId').value = '';
            document.getElementById('satuan').value = 'kg';
            clearFormErrors('kategori', FIELD_KATEGORI);

            document.getElementById('tanggal_awal').value = tanggalHariIni();
            document.getElementById('blokHargaAwal').style.display = '';
            document.getElementById('blokTanggalAwal').style.display = '';
            document.getElementById('harga_awal').required = true;
            document.getElementById('tanggal_awal').required = true;

            const titleEl = document.getElementById('modalKategoriTitle');
            if (titleEl)(titleEl.querySelector('span') || titleEl).textContent = 'Tambah Kategori';
            document.getElementById('kategoriSubmitBtn').textContent = 'Simpan Kategori';
            bukaModal('modalKategori');
        }

        function editKategoriModal(kategori) {
            document.getElementById('kategoriId').value = kategori.id_kategori;
            document.getElementById('id_induk').value = kategori.id_induk || '';
            document.getElementById('nama_kategori').value = kategori.nama_kategori || '';
            document.getElementById('satuan').value = kategori.satuan || 'kg';
            clearFormErrors('kategori', FIELD_KATEGORI);

            document.getElementById('blokHargaAwal').style.display = 'none';
            document.getElementById('blokTanggalAwal').style.display = 'none';
            document.getElementById('harga_awal').required = false;
            document.getElementById('tanggal_awal').required = false;

            const titleEl = document.getElementById('modalKategoriTitle');
            if (titleEl)(titleEl.querySelector('span') || titleEl).textContent = 'Edit Kategori';
            document.getElementById('kategoriSubmitBtn').textContent = 'Simpan Perubahan';

            bukaModal('modalKategori');
        }

        function simpanKategori(event) {
            event.preventDefault();
            const id = document.getElementById('kategoriId').value;
            clearFormErrors('kategori', FIELD_KATEGORI);

            const payload = {
                id_induk: document.getElementById('id_induk').value || null,
                nama_kategori: document.getElementById('nama_kategori').value,
                satuan: document.getElementById('satuan').value,
            };

            if (!id) {
                payload.harga_satuan = document.getElementById('harga_awal').value;
                payload.tanggal_berlaku = document.getElementById('tanggal_awal').value;
            }

            const url = id ? urlKategoriUpdate.replace(':id', id) : urlKategoriStore;

            fetch(url, {
                    method: id ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify(payload)
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        if (err.errors) {
                            const e = {
                                ...err.errors
                            };
                            if (e.harga_satuan) e.harga_awal = e.harga_satuan;
                            if (e.tanggal_berlaku) e.tanggal_awal = e.tanggal_berlaku;
                            tampilkanFormErrors(e, FIELD_KATEGORI);
                            throw new Error(err.message || 'Periksa kembali isian form.');
                        }
                        throw new Error(err.message || 'Gagal menyimpan kategori.');
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalKategori');
                    showToast('Berhasil disimpan', id ? 'Perubahan kategori telah tersimpan.' :
                        'Kategori baru beserta harga awal telah ditambahkan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menyimpan', err.message, 'error');
                });

            return false;
        }

        function kelolaHarga(kategori) {
            hargaContext = kategori;
            document.getElementById('hargaNamaKategori').textContent = kategori.nama_kategori;
            document.getElementById('hargaSatuanLabel').textContent = kategori.satuan || 'satuan';
            document.getElementById('formHarga').reset();
            document.getElementById('tanggal_berlaku').value = tanggalHariIni();
            clearFormErrors('harga', ['harga_satuan', 'tanggal_berlaku']);

            renderRiwayatHarga(kategori.id_kategori);
            bukaModal('modalHarga');
        }

        function renderRiwayatHarga(idKategori) {
            const tbody = document.getElementById('hargaTableBody');
            const riwayat = riwayatData[idKategori] || [];

            if (riwayat.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="empty-desc text-center">Belum ada riwayat harga.</td></tr>';
                return;
            }

            tbody.innerHTML = riwayat.map(h => `
                <tr>
                    <td>${h.tanggal}</td>
                    <td><strong class="saldo">Rp ${Number(h.harga).toLocaleString('id-ID')}</strong></td>
                    <td>
                        <button class="icon-btn icon-btn--danger icon-btn--sm" title="Hapus"
                            type="button" onclick='hapusHarga(${h.id_harga}, ${JSON.stringify(h.delete_url)})'>
                            <svg viewBox="0 0 24 24">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                <path d="M10 11v6M14 11v6"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function tambahHarga(event) {
            event.preventDefault();
            if (!hargaContext) return false;

            clearFormErrors('harga', ['harga_satuan', 'tanggal_berlaku']);
            const payload = {
                harga_satuan: document.getElementById('harga_satuan').value,
                tanggal_berlaku: document.getElementById('tanggal_berlaku').value,
            };

            fetch(hargaContext.store_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify(payload)
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        if (err.errors) {
                            tampilkanFormErrors(err.errors, ['harga_satuan', 'tanggal_berlaku']);
                            throw new Error(err.message || 'Periksa kembali isian form.');
                        }
                        throw new Error(err.message || 'Gagal menambah harga.');
                    }
                    return res.json();
                })
                .then(() => {
                    showToast('Berhasil', 'Harga baru telah ditambahkan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menyimpan', err.message, 'error');
                });

            return false;
        }

        function hapusHarga(idHarga, url) {
            hapusContext = {
                type: 'harga',
                url: url,
                label: 'data harga ini'
            };
            document.getElementById('hapusTitle').textContent = 'Hapus Data Harga?';
            document.getElementById('hapusText').innerHTML =
                'Anda akan menghapus data harga ini. Tindakan ini tidak dapat dibatalkan.';
            bukaModal('modalHapus');
        }

        function hapusKategori(kategori) {
            hapusContext = {
                type: 'kategori',
                url: urlKategoriHapus.replace(':id', kategori.id_kategori),
                label: kategori.nama_kategori,
            };

            document.getElementById('hapusTitle').textContent = 'Hapus Kategori Sampah?';
            document.getElementById('hapusText').innerHTML =
                `Anda akan menghapus kategori <strong>${kategori.nama_kategori}</strong>. Tindakan ini tidak dapat dibatalkan.`;
            bukaModal('modalHapus');
        }

        function konfirmasiHapus() {
            if (!hapusContext) return;

            fetch(hapusContext.url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
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
                    showToast('Berhasil dihapus', hapusContext.type === 'kategori' ?
                        `${hapusContext.label} telah dihapus dari data kategori.` :
                        'Data harga telah dihapus.');
                    hapusContext = null;
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menghapus', err.message, 'error');
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif

            const searchInput = document.getElementById('searchKategori');
            const table = document.getElementById('kategoriTable');

            if (searchInput && table) {
                searchInput.addEventListener('keyup', function() {
                    const keyword = this.value.toLowerCase().trim();
                    const rows = table.querySelectorAll('tbody tr[data-row-kategori]');
                    let tampil = 0;

                    rows.forEach(function(row) {
                        const cocok = row.textContent.toLowerCase().includes(keyword);
                        row.style.display = cocok ? '' : 'none';
                        if (cocok) tampil++;
                    });

                    const kosong = document.getElementById('kategoriKosongCari');
                    if (kosong) kosong.style.display = (rows.length > 0 && tampil === 0) ? '' : 'none';
                });
            }

            document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
                overlay.addEventListener('click', function(e) {
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
