    @extends('admin.layouts.app')

    @section('title', 'Kategori & Harga Sampah')
    @section('active', 'kategori-harga')
    @section('crumbs', 'Master Data | Kategori & Harga Sampah')

    @section('content')

        @php
            // Ringkasan
            $totalInduk = $kategoriInduk->count();
            $totalSub = $kategoriInduk->sum(fn($k) => $k->anak->count());
            $totalHarga = $kategoriInduk->reduce(function ($carry, $k) {
                $carry += $k->hargaTerbaru ? 1 : 0;
                $carry += $k->anak->filter(fn($s) => $s->hargaTerbaru)->count();
                return $carry;
            }, 0);
            $totalBelum = $totalInduk + $totalSub - $totalHarga;

            // Riwayat harga per kategori (induk & anak), dipakai oleh modal "Kelola Harga"
            $riwayatMap = [];
            $mapRiwayat = function ($kat) use (&$riwayatMap) {
                $riwayatMap[$kat->id_kategori] = collect($kat->hargaSampah ?? [])
                    ->sortByDesc('tanggal_berlaku')
                    ->map(function ($h) use ($kat) {
                        return [
                            'id_harga' => $h->id_harga,
                            'harga' => (float) $h->harga_per_gram,
                            'tanggal' => optional($h->tanggal_berlaku)->format('d M Y'),
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
        @endphp

        <section class="hero">
            <div class="hero-text">
                <span class="eyebrow">MASTER DATA</span>

                <h1 class="hero-title">
                    Kategori & <span class="accent">Harga Sampah</span>
                </h1>

                <p class="hero-sub">
                    Kelola kategori sampah beserta riwayat dan penetapan harganya.
                </p>
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


        <!-- SUMMARY -->
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
                            <circle cx="6" cy="3" r="0"></circle>
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


        <section class="card">

            <!-- SEARCH -->
            <div class="table-toolbar">

                <div class="table-search">
                    <svg viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input type="text" id="searchKategori" placeholder="Cari nama kategori atau satuan..."
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
                            <tr data-row-kategori>

                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $kategori->nama_kategori }}</strong></td>
                                <td>{{ $kategori->satuan }}</td>

                                <td class="nowrap">
                                    {{ $kategori->hargaTerbaru ? $kategori->hargaTerbaru->tanggal_berlaku->translatedFormat('d M Y') : '-' }}
                                </td>

                                <td>
                                    @if ($kategori->hargaTerbaru)
                                        <strong class="saldo">Rp
                                            {{ number_format($kategori->hargaTerbaru->harga_per_gram, 0, ',', '.') }}</strong>
                                    @else
                                        <span class="muted">Belum diatur</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($kategori->hargaTerbaru)
                                        <span class="badge badge--success">Aktif</span>
                                    @else
                                        <span class="badge badge--warning">Belum Diatur</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="table-actions">

                                        <button class="icon-btn icon-btn--harga" title="Kelola Harga" type="button"
                                            onclick="kelolaHarga({{ Js::from([
                                                'id_kategori' => $kategori->id_kategori,
                                                'nama_kategori' => $kategori->nama_kategori,
                                                'satuan' => $kategori->satuan,
                                                'store_url' => route('admin.kategori-harga.harga.store', $kategori->id_kategori),
                                            ]) }})">
                                            <svg viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="9"></circle>
                                                <path
                                                    d="M15 8.5c-.6-.7-1.6-1.1-2.8-1.1-1.7 0-2.8.9-2.8 2.2 0 1.2 1 1.8 2.8 2.2 1.8.4 2.8 1 2.8 2.2 0 1.3-1.1 2.2-2.9 2.2-1.3 0-2.4-.5-3.1-1.3M12 5v14">
                                                </path>
                                            </svg>
                                        </button>

                                        <button class="icon-btn icon-btn--danger" title="Hapus Kategori" type="button"
                                            onclick="hapusKategori({{ Js::from($kategori) }})">
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
                                <tr data-row-kategori style="background:rgba(0,0,0,0.015);">

                                    <td></td>
                                    <td style="padding-left: 28px; color:#6b7280;">
                                        <span style="opacity:.5;">↳</span> {{ $sub->nama_kategori }}
                                    </td>
                                    <td>{{ $sub->satuan }}</td>

                                    <td class="nowrap">
                                        {{ $sub->hargaTerbaru ? $sub->hargaTerbaru->tanggal_berlaku->translatedFormat('d M Y') : '-' }}
                                    </td>

                                    <td>
                                        @if ($sub->hargaTerbaru)
                                            <strong class="saldo">Rp
                                                {{ number_format($sub->hargaTerbaru->harga_per_gram, 0, ',', '.') }}</strong>
                                        @else
                                            <span class="muted">Belum diatur</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($sub->hargaTerbaru)
                                            <span class="badge badge--success">Aktif</span>
                                        @else
                                            <span class="badge badge--warning">Belum Diatur</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="table-actions">

                                            <button class="icon-btn icon-btn--harga" title="Kelola Harga" type="button"
                                                onclick="kelolaHarga({{ Js::from([
                                                    'id_kategori' => $sub->id_kategori,
                                                    'nama_kategori' => $sub->nama_kategori,
                                                    'satuan' => $sub->satuan,
                                                    'store_url' => route('admin.kategori-harga.harga.store', $sub->id_kategori),
                                                ]) }})">
                                                <svg viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="9"></circle>
                                                    <path
                                                        d="M15 8.5c-.6-.7-1.6-1.1-2.8-1.1-1.7 0-2.8.9-2.8 2.2 0 1.2 1 1.8 2.8 2.2 1.8.4 2.8 1 2.8 2.2 0 1.3-1.1 2.2-2.9 2.2-1.3 0-2.4-.5-3.1-1.3M12 5v14">
                                                    </path>
                                                </svg>
                                            </button>

                                            <button class="icon-btn icon-btn--danger" title="Hapus Kategori"
                                                type="button" onclick="hapusKategori({{ Js::from($sub) }})">
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
                                <td colspan="5" style="text-align: center; padding: 40px;">
                                    <div style="font-size: 30px; margin-bottom: 10px;">📦</div>
                                    <strong>Belum Ada Data Kategori</strong>
                                    <p style="margin: 5px 0 0; color: #6b7280;">
                                        Belum ada kategori sampah yang terdaftar.
                                    </p>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

                <div class="catalog-empty" id="kategoriKosongCari" style="display: none;">
                    <div style="font-size: 30px; margin-bottom: 10px;">🔍</div>
                    <strong>Data Tidak Ditemukan</strong>
                    <p style="margin: 5px 0 0; color: #6b7280;">
                        Tidak ada kategori yang cocok dengan pencarian ini.
                    </p>
                </div>

            </div>


            <div class="table-footer">
                <div class="table-info">
                    Total kategori induk: <strong>{{ $totalInduk }}</strong>
                </div>
            </div>

        </section>


        <!-- TOAST -->
        <div class="toast-wrap" id="toastWrap"></div>


        <!-- ============================================= -->
        <!-- MODAL: TAMBAH / EDIT KATEGORI                  -->
        <!-- ============================================= -->
        <div class="modal-overlay" id="modalKategori">
            <div class="modal-box">

                <div class="modal-head">
                    <h3 class="modal-title" id="modalKategoriTitle">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 5v14M5 12h14"></path>
                        </svg>
                        Tambah Kategori
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

                            <div class="form-group form-group--full">
                                <label for="nama_kategori">Nama Kategori</label>
                                <input type="text" id="nama_kategori" class="form-control"
                                    placeholder="Contoh: Botol Plastik" required>
                                <span class="form-error" id="err_nama_kategori"></span>
                            </div>

                            {{-- Field harga awal: hanya tampil saat mode Tambah --}}
                            <div class="form-group" id="blokHargaAwal">
                                <label for="harga_awal">Harga per Gram (Rp)</label>
                                <input type="number" step="0.01" id="harga_awal" class="form-control"
                                    placeholder="Contoh: 5000">
                                <span class="form-error" id="err_harga_per_gram"></span>
                            </div>

                            <div class="form-group" id="blokTanggalAwal">
                                <label for="tanggal_awal">Berlaku Mulai</label>
                                <input type="date" id="tanggal_awal" class="form-control">
                                <span class="form-error" id="err_tanggal_berlaku"></span>
                            </div>

                        </div>

                    </div>

                    <div class="modal-foot">
                        <button class="btn btn--ghost" type="button"
                            onclick="tutupModal('modalKategori')">Batal</button>
                        <button class="btn btn--primary" type="submit" id="kategoriSubmitBtn">Simpan Kategori</button>
                    </div>
                </form>

            </div>
        </div>


        <!-- ============================================= -->
        <!-- MODAL: KELOLA HARGA                            -->
        <!-- ============================================= -->
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
                                <label for="harga_per_gram">Harga per <span id="hargaSatuanLabel">kg</span> (Rp)</label>
                                <input type="number" step="0.01" id="harga_per_gram" class="form-control"
                                    placeholder="Contoh: 5000" required>
                                <span class="form-error" id="err_harga_per_gram"></span>
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
                                    <td colspan="4" style="text-align:center; padding:24px; color:#9aa0a6;">
                                        Belum ada riwayat harga.
                                    </td>
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


        <!-- ============================================= -->
        <!-- MODAL: KONFIRMASI HAPUS (kategori & harga)     -->
        <!-- ============================================= -->
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

                    <p class="confirm-text" id="hapusText">
                        Tindakan ini tidak dapat dibatalkan.
                    </p>

                </div>

                <div class="modal-foot modal-foot--center">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHapus')">Batal</button>
                    <button class="btn btn--danger" type="button" onclick="konfirmasiHapus()">Ya, Hapus</button>
                </div>

            </div>
        </div>


        <style>
            /* =========================
                    SUMMARY
                    ========================= */
            .summary-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 16px;
                margin-bottom: 20px;
            }

            .summary-card {
                border: 1px solid var(--border, #e5e7eb);
                background: var(--surface, #ffffff);
                border-radius: 14px;
                padding: 18px;
                min-width: 0;
            }

            .summary-card-top {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 12px;
            }

            .summary-label {
                font-size: 13px;
                color: #6b7280;
                margin-bottom: 8px;
            }

            .summary-value {
                font-size: 21px;
                font-weight: 800;
                line-height: 1.25;
                color: #1f2937;
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
                color: var(--primary, #4338ca);
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
                color: #6b7280;
            }

            @media (max-width: 1100px) {
                .summary-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 560px) {
                .summary-grid {
                    grid-template-columns: 1fr;
                }
            }

            /* =========================
                    TABLE / CARD
                    ========================= */
            .card {
                background: var(--surface, #ffffff);
                border: 1px solid var(--border, #e5e7eb);
                border-radius: 14px;
                overflow: hidden;
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
            }

            .table-search input {
                width: 100%;
                height: 40px;
                padding: 0 14px 0 40px;
                border: 1px solid #dfe3e8;
                border-radius: 8px;
                outline: none;
                box-sizing: border-box;
            }

            .table-search input:focus {
                border-color: var(--primary, #4338ca);
            }

            .table-responsive {
                width: 100%;
                overflow-x: auto;
            }

            .data-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 900px;
            }

            .data-table--sm {
                min-width: 0;
            }

            .data-table th {
                background: #f8fafc;
                color: #64748b;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                padding: 13px 16px;
                text-align: left;
                border-top: 1px solid #e5e7eb;
                border-bottom: 1px solid #e5e7eb;
                white-space: nowrap;
            }

            .data-table td {
                padding: 16px;
                border-bottom: 1px solid #edf0f2;
                font-size: 13px;
                color: #374151;
                vertical-align: middle;
            }

            .data-table tbody tr:hover {
                background: #fafafa;
            }

            .nowrap {
                white-space: nowrap;
            }

            .saldo {
                white-space: nowrap;
            }

            .muted {
                color: #9aa0a6;
            }

            .badge {
                display: inline-flex;
                align-items: center;
                padding: 5px 11px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 700;
                white-space: nowrap;
            }

            .badge--success {
                background: #dcfce7;
                color: #15803d;
            }

            .badge--warning {
                background: #fef3c7;
                color: #b45309;
            }

            .table-actions {
                display: flex;
                gap: 6px;
            }

            .icon-btn {
                width: 34px;
                height: 34px;
                border: 1px solid #e1e5e9;
                background: #fff;
                border-radius: 7px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all .15s ease;
            }

            .icon-btn svg {
                width: 16px;
                height: 16px;
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
                flex-shrink: 0;
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

            .table-footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 16px 24px;
            }

            .table-info {
                font-size: 13px;
                color: #6b7280;
            }

            .catalog-empty {
                text-align: center;
                padding: 50px 20px;
            }

            /* =========================
                    FORM
                    ========================= */
            .form-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
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
                color: #64748b;
            }

            .form-control {
                width: 100%;
                padding: 10px 14px;
                font-size: 13px;
                color: #374151;
                border: 1px solid #dfe3e8;
                border-radius: 8px;
                outline: none;
                box-sizing: border-box;
                font-family: inherit;
                transition: border-color .15s ease;
                background: #fff;
            }

            .form-control:focus {
                border-color: var(--primary, #4338ca);
            }

            select.form-control {
                cursor: pointer;
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

            @media (max-width: 640px) {
                .form-grid {
                    grid-template-columns: 1fr;
                }
            }

            /* =========================
                    MODAL
                    ========================= */
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
                max-width: 620px;
                max-height: 90vh;
                overflow-y: auto;
                background: var(--surface, #ffffff);
                border: 1px solid var(--border, #e5e7eb);
                border-radius: 14px;
                box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
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
                padding: 20px 24px;
                border-bottom: 1px solid #edf0f2;
            }

            .modal-title {
                margin: 0;
                font-size: 16px;
                font-weight: 700;
                color: #1f2937;
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
                color: #374151;
            }

            .modal-close {
                width: 30px;
                height: 30px;
                border: none;
                background: transparent;
                border-radius: 7px;
                font-size: 20px;
                line-height: 1;
                color: #6b7280;
                cursor: pointer;
            }

            .modal-close:hover {
                background: #f3f4f6;
                color: #111827;
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
                border-top: 1px solid #edf0f2;
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
                color: #1f2937;
            }

            .confirm-text {
                margin: 0;
                font-size: 13px;
                color: #6b7280;
                line-height: 1.6;
            }

            /* =========================
                    TOAST
                    ========================= */
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
                background: var(--surface, #ffffff);
                border: 1px solid var(--border, #e5e7eb);
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
            }

            .toast-title {
                font-size: 13px;
                font-weight: 700;
                color: #1f2937;
                margin: 0 0 2px;
            }

            .toast-text {
                font-size: 12.5px;
                color: #6b7280;
                margin: 0;
                line-height: 1.5;
            }

            .toast-close {
                border: none;
                background: transparent;
                color: #9ca3af;
                font-size: 16px;
                line-height: 1;
                cursor: pointer;
                padding: 0;
                flex-shrink: 0;
            }

            .toast-close:hover {
                color: #111827;
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
        </style>


        <script>
            /*
                                                                                        |--------------------------------------------------------------------------
                                                                                        | Data riwayat harga per kategori (dari server, dipakai modal Kelola Harga)
                                                                                        |--------------------------------------------------------------------------
                                                                                        */
            const riwayatData = @json($riwayatMap);

            // Template URL dinamis (":id" diganti id_kategori / id_harga saat dipakai)
            const urlKategoriStore = "{{ route('admin.kategori-harga.store') }}";
            const urlKategoriUpdate = "{{ route('admin.kategori-harga.update', ':id') }}";
            const urlKategoriHapus = "{{ route('admin.kategori-harga.destroy', ':id') }}";

            /*
            |--------------------------------------------------------------------------
            | Helper: modal & toast
            |--------------------------------------------------------------------------
            */
            function bukaModal(id) {
                document.getElementById(id).classList.add('active');
            }

            function tutupModal(id) {
                document.getElementById(id).classList.remove('active');
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
                    <button class="toast-close" type="button" aria-label="Tutup">&times;</button>
                `;

                function hapusToast() {
                    toast.classList.add('toast--leaving');
                    setTimeout(() => toast.remove(), 180);
                }

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

            /*
            |--------------------------------------------------------------------------
            | Pencarian tabel kategori
            |--------------------------------------------------------------------------
            */
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

            /*
            |--------------------------------------------------------------------------
            | Tambah / Edit Kategori
            |--------------------------------------------------------------------------
            */
            function bukaModalTambahKategori() {
                document.getElementById('formKategori').reset();
                document.getElementById('kategoriId').value = '';
                clearFormErrors('kategori', ['id_induk', 'nama_kategori', 'harga_per_gram', 'tanggal_berlaku']);

                document.getElementById('tanggal_awal').value = new Date().toISOString().substring(0, 10);

                // Mode tambah: field harga wajib diisi & ditampilkan
                document.getElementById('blokHargaAwal').style.display = '';
                document.getElementById('blokTanggalAwal').style.display = '';
                document.getElementById('harga_awal').required = true;
                document.getElementById('tanggal_awal').required = true;

                document.getElementById('modalKategoriTitle').lastChild.textContent = ' Tambah Kategori';
                document.getElementById('kategoriSubmitBtn').textContent = 'Simpan Kategori';
                bukaModal('modalKategori');
            }

            function editKategoriModal(kategori) {
                document.getElementById('kategoriId').value = kategori.id_kategori;
                document.getElementById('id_induk').value = kategori.id_induk || '';
                document.getElementById('nama_kategori').value = kategori.nama_kategori || '';
                clearFormErrors('kategori', ['id_induk', 'nama_kategori', 'harga_per_gram', 'tanggal_berlaku']);

                // Mode edit: harga awal tidak relevan (harga diubah lewat "Kelola Harga")
                document.getElementById('blokHargaAwal').style.display = 'none';
                document.getElementById('blokTanggalAwal').style.display = 'none';
                document.getElementById('harga_awal').required = false;
                document.getElementById('tanggal_awal').required = false;

                document.getElementById('modalKategoriTitle').lastChild.textContent = ' Edit Kategori';
                document.getElementById('kategoriSubmitBtn').textContent = 'Simpan Perubahan';

                bukaModal('modalKategori');
            }

            function simpanKategori(event) {
                event.preventDefault();

                const id = document.getElementById('kategoriId').value;
                clearFormErrors('kategori', ['id_induk', 'nama_kategori', 'harga_per_gram', 'tanggal_berlaku']);

                const payload = {
                    id_induk: document.getElementById('id_induk').value || null,
                    nama_kategori: document.getElementById('nama_kategori').value,
                };

                // Mode tambah: sertakan harga awal
                if (!id) {
                    payload.harga_per_gram = document.getElementById('harga_awal').value;
                    payload.tanggal_berlaku = document.getElementById('tanggal_awal').value;
                }

                const url = id ? urlKategoriUpdate.replace(':id', id) : urlKategoriStore;

                fetch(url, {
                        method: id ? 'PUT' : 'POST',
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
                            if (err.errors) {
                                tampilkanFormErrors(err.errors, ['id_induk', 'nama_kategori', 'harga_per_gram',
                                    'tanggal_berlaku'
                                ]);
                                throw new Error(err.message || 'Periksa kembali isian form.');
                            }
                            throw new Error(err.message || 'Gagal menyimpan kategori.');
                        }
                        return res.json();
                    })
                    .then(() => {
                        tutupModal('modalKategori');
                        showToast('Berhasil disimpan', id ?
                            'Perubahan kategori telah tersimpan.' :
                            'Kategori baru beserta harga awal telah ditambahkan.');
                        setTimeout(() => window.location.reload(), 800);
                    })
                    .catch((err) => {
                        showToast('Gagal menyimpan', err.message, 'error');
                    });

                return false;
            }

            /*
            |--------------------------------------------------------------------------
            | Kelola Harga
            |--------------------------------------------------------------------------
            */
            let hargaContext = null;

            function kelolaHarga(kategori) {
                hargaContext = kategori;

                document.getElementById('hargaNamaKategori').textContent = kategori.nama_kategori;
                document.getElementById('hargaSatuanLabel').textContent = kategori.satuan || 'kg';
                document.getElementById('formHarga').reset();
                document.getElementById('tanggal_berlaku').value = new Date().toISOString().substring(0, 10);
                clearFormErrors('harga', ['harga_per_gram', 'tanggal_berlaku']);

                renderRiwayatHarga(kategori.id_kategori);

                bukaModal('modalHarga');
            }

            function renderRiwayatHarga(idKategori) {
                const tbody = document.getElementById('hargaTableBody');
                const riwayat = riwayatData[idKategori] || [];

                if (riwayat.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" style="text-align:center; padding:24px; color:#9aa0a6;">
                                Belum ada riwayat harga.
                            </td>
                        </tr>`;
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

                clearFormErrors('harga', ['harga_per_gram', 'tanggal_berlaku']);

                const payload = {
                    harga_per_gram: document.getElementById('harga_per_gram').value,
                    tanggal_berlaku: document.getElementById('tanggal_berlaku').value,
                };

                fetch(hargaContext.store_url, {
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
                            if (err.errors) {
                                tampilkanFormErrors(err.errors, ['harga_per_gram', 'tanggal_berlaku']);
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

            /*
            |--------------------------------------------------------------------------
            | Hapus (kategori & harga, memakai modal yang sama)
            |--------------------------------------------------------------------------
            */
            let hapusContext = null;

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
                        showToast('Berhasil dihapus',
                            hapusContext.type === 'kategori' ?
                            `${hapusContext.label} telah dihapus dari data kategori.` :
                            'Data harga telah dihapus.');
                        hapusContext = null;
                        setTimeout(() => window.location.reload(), 800);
                    })
                    .catch((err) => {
                        showToast('Gagal menghapus', err.message, 'error');
                    });
            }
        </script>

    @endsection
