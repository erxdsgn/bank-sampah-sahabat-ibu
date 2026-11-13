@extends('admin.layouts.app')

@section('title', 'Verifikasi Data Setoran')
@section('active', 'verifikasi-setoran')
@section('crumbs', 'Transaksi Sampah | Verifikasi Data Setoran')

@section('content')

    @php
        /*
    |--------------------------------------------------------------------------
    | FORMAT ANGKA
    |--------------------------------------------------------------------------
    */
        $fmtHarga = fn($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');
        $fmtJumlah = fn($n) => rtrim(rtrim(number_format((float) $n, 3, ',', '.'), '0'), ',');

        /*
    |--------------------------------------------------------------------------
    | MAP KATEGORI (Mendukung ID dari id_kategori maupun id)
    |--------------------------------------------------------------------------
    */
        $kategoriMap = $kategoriSampah->keyBy(function ($k) {
            return $k->id_kategori ?? $k->id;
        });

        /*
    |--------------------------------------------------------------------------
    | NORMALISASI SATUAN
    |--------------------------------------------------------------------------
    */
        $normalisasiSatuan = function ($satuan) {
            $satuan = strtolower(trim((string) $satuan));

            return match ($satuan) {
                'g', 'gram' => 'gram',
                'kg', 'kilogram', 'kilogram (kg)' => 'kg',
                'pcs', 'piece', 'pieces' => 'pcs',
                'l', 'liter', 'litre', 'liter (l)' => 'liter',
                'set' => 'set',
                'unit' => 'unit',
                default => 'gram',
            };
        };

        /*
    |--------------------------------------------------------------------------
    | NAMA SATUAN
    |--------------------------------------------------------------------------
    */
        $namaSatuan = function ($satuan) use ($normalisasiSatuan) {
            return match ($normalisasiSatuan($satuan)) {
                'gram' => 'Gram',
                'kg' => 'Kilogram',
                'pcs' => 'Pieces',
                'liter' => 'Liter',
                'set' => 'Set',
                'unit' => 'Unit',
                default => 'Gram',
            };
        };

        /*
    |--------------------------------------------------------------------------
    | SIMBOL SATUAN
    |--------------------------------------------------------------------------
    */
        $simbolSatuan = function ($satuan) use ($normalisasiSatuan) {
            return match ($normalisasiSatuan($satuan)) {
                'gram' => 'gram',
                'kg' => 'kg',
                'pcs' => 'pcs',
                'liter' => 'L',
                'set' => 'set',
                'unit' => 'unit',
                default => 'gram',
            };
        };
    @endphp

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">TRANSAKSI SAMPAH</span>

            <h1 class="hero-title">
                Verifikasi <span class="accent">Setoran</span>
            </h1>

            <p class="hero-sub">
                Verifikasi setoran sampah warga & Tambahkan setoran baru secara langsung.
            </p>
        </div>

        <div class="hero-actions">

            <div class="stat-chip">
                <span class="stat-dot stat-dot--amber"></span>
                <div>
                    <div class="stat-num">{{ $setoran->where('status', 'pending')->count() }}</div>
                    <div class="stat-label">Menunggu</div>
                </div>
            </div>

            <div class="stat-chip">
                <span class="stat-dot stat-dot--green"></span>
                <div>
                    <div class="stat-num">{{ $setoran->where('status', 'approved')->count() }}</div>
                    <div class="stat-label">Disetujui</div>
                </div>
            </div>

            <div class="stat-chip">
                <span class="stat-dot stat-dot--red"></span>
                <div>
                    <div class="stat-num">{{ $setoran->where('status', 'rejected')->count() }}</div>
                    <div class="stat-label">Ditolak</div>
                </div>
            </div>

            <button class="btn btn--primary" type="button" onclick="bukaTambahSetoran()">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Setoran
            </button>

        </div>
    </section>


    <section class="card">

        <!-- SEARCH + FILTER -->
        <div class="table-toolbar">

            <div class="table-search">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>

                <input type="text" id="searchSetoran" placeholder="Cari nama warga, NIK, atau kategori sampah..."
                    autocomplete="off">
            </div>

            <div class="filter-pills" id="filterPills">

                <button class="fpill active" type="button" data-filter="all">
                    Semua
                </button>

                <button class="fpill" type="button" data-filter="pending">
                    Menunggu
                </button>

                <button class="fpill" type="button" data-filter="approved">
                    Disetujui
                </button>

                <button class="fpill" type="button" data-filter="rejected">
                    Ditolak
                </button>

            </div>

        </div>


        <!-- TABLE -->
        <div class="table-responsive">

            <table class="data-table" id="setoranTable">

                <thead>
                    <tr>
                        <th class="col-no">No</th>
                        <th>Warga</th>
                        <th>Kategori Sampah</th>
                        <th>Jumlah</th>
                        <th>Nilai</th>
                        <th>Tanggal Setoran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($setoran as $index => $item)
                        @php
                            /*
                            |--------------------------------------------------------------------------
                            | DETAIL SETORAN (BISA LEBIH DARI SATU JENIS SAMPAH)
                            |--------------------------------------------------------------------------
                            */
                            $details = $detailMap[$item->getKey()] ?? collect();

                            $items = $details
                                ->map(function ($d) use ($kategoriMap, $normalisasiSatuan) {
                                    $katId = $d->id_kategori ?? $d->id_kategori_sampah;
                                    $kat = $kategoriMap[$katId] ?? null;

                                    return [
                                        'id_detail' => $d->id_detail_setoran ?? ($d->id_detail ?? ($d->id ?? null)),
                                        'id_kategori' => $katId,
                                        'nama_kategori' => optional($kat)->nama_kategori
                                                            ?? optional($kat)->nama_lengkap
                                                            ?? optional($kat)->nama
                                                            ?? optional($d->kategori)->nama_kategori,
                                        'satuan' => $normalisasiSatuan(
                                            $d->satuan ?? (optional($kat)->satuan ?? 'gram'),
                                        ),
                                        'jumlah' => (float) ($d->jumlah_satuan ?? ($d->jumlah ?? ($d->berat_gram ?? 0))),
                                        'subtotal' => isset($d->subtotal) ? (float) $d->subtotal : null,
                                    ];
                                })
                                ->values()
                                ->all();

                            // Data lama tanpa detail: tampilkan satu baris dari data setoran
                            if (empty($items)) {
                                $katLamaId = $item->id_kategori ?? $item->id_kategori_sampah ?? null;
                                $katLama = $kategoriMap[$katLamaId] ?? null;
                                $items = [
                                    [
                                        'id_detail' => null,
                                        'id_kategori' => $katLamaId,
                                        'nama_kategori' => optional($katLama)->nama_kategori
                                                            ?? optional($katLama)->nama_lengkap
                                                            ?? optional($item->kategori)->nama_kategori
                                                            ?? optional($item->kategori)->nama,
                                        'satuan' => $normalisasiSatuan($item->satuan ?? 'gram'),
                                        'jumlah' => (float) ($item->jumlah_satuan ?? ($item->jumlah ?? ($item->total_berat ?? 0))),
                                    ],
                                ];
                            }

                            $namaList = collect($items)->pluck('nama_kategori')->filter()->unique()->values();

                            $totalNilai =
                                $item->total_nilai ??
                                $details->sum(function ($d) {
                                    return (float) ($d->subtotal ?? 0);
                                });

                            $row = [
                                'id' => $item->getKey(),
                                'kode_transaksi' => $item->kode_transaksi ?? '-',
                                'status' => $item->status,
                                'warga' => [
                                    'nama' => $item->warga->nama ?? '-',
                                    'nik' => $item->warga->nik ?? '-',
                                ],
                                'items' => $items,
                                'total_nilai' => (float) $totalNilai,
                                'catatan_admin' => $item->catatan_admin ?? '',
                                'foto_bukti' => $item->foto_sampah ?? null,
                            ];

                            $tanggal = $item->tanggal_setoran ?? $item->created_at;
                        @endphp


                        <tr data-status="{{ $item->status }}">

                            <!-- NO -->
                            <td class="col-no">
                                <span class="mono no-urut">{{ $loop->iteration }}</span>
                                <span class="sr-only">{{ $row['kode_transaksi'] }}</span>
                            </td>


                            <!-- WARGA -->
                            <td>
                                <strong>{{ $row['warga']['nama'] }}</strong>
                                <br>
                                <span class="mono">
                                    {{ $row['warga']['nik'] }}
                                </span>
                            </td>


                            <!-- KATEGORI -->
                            <td>
                                @if ($namaList->isEmpty())
                                    <span class="kategori-tag">-</span>
                                @else
                                    <div class="kategori-stack">
                                        @foreach ($namaList->take(2) as $nm)
                                            <span class="kategori-tag">{{ $nm }}</span>
                                        @endforeach

                                        @if ($namaList->count() > 2)
                                            <span class="kategori-tag kategori-tag--more"
                                                title="{{ $namaList->slice(2)->implode(', ') }}">
                                                +{{ $namaList->count() - 2 }} lainnya
                                            </span>
                                            <span class="sr-only">{{ $namaList->slice(2)->implode(', ') }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>


                            <!-- JUMLAH -->
                            <td>
                                @if (count($row['items']) > 1)
                                    <strong>{{ count($row['items']) }} jenis</strong>
                                @else
                                    <strong>{{ $fmtJumlah($row['items'][0]['jumlah'] ?? 0) }}</strong>
                                    <span class="unit">{{ $row['items'][0]['satuan'] ?? 'gram' }}</span>
                                @endif
                            </td>


                            <!-- NILAI -->
                            <td>
                                <strong>
                                    Rp {{ number_format($row['total_nilai'], 0, ',', '.') }}
                                </strong>
                            </td>


                            <!-- TANGGAL -->
                            <td>

                                @if ($tanggal)
                                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                                @else
                                    -
                                @endif

                            </td>


                            <!-- STATUS -->
                            <td>

                                @if ($item->status === 'pending')
                                    <span class="status-badge status-badge--pending">
                                        Menunggu
                                    </span>
                                @elseif($item->status === 'approved')
                                    <span class="status-badge status-badge--approved">
                                        Disetujui
                                    </span>
                                @else
                                    <span class="status-badge status-badge--rejected">
                                        Ditolak
                                    </span>
                                @endif

                            </td>


                            <!-- AKSI -->
                            <td>

                                <div class="table-actions">

                                    <button class="icon-btn {{ $item->status === 'pending' ? 'icon-btn--primary' : '' }}"
                                        title="{{ $item->status === 'pending' ? 'Lihat Detail & Verifikasi' : 'Lihat Detail' }}"
                                        type="button" onclick="bukaVerifikasi({{ Js::from($row) }})">

                                        <svg viewBox="0 0 24 24">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                            <circle cx="12" cy="12" r="2.5"></circle>
                                        </svg>

                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" style="text-align:center; padding:40px;">

                                <div style="font-size:30px; margin-bottom:10px;">
                                    🧾
                                </div>

                                <strong>
                                    Belum Ada Setoran Masuk
                                </strong>

                                <p style="margin:5px 0 0; color:#6b7280;">
                                    Belum ada warga yang mengisi form setoran.
                                </p>

                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </table>

            <div class="catalog-empty" id="setoranKosongCari" style="display: none;">
                <div class="empty-icon">🔍</div>
                <strong>Data Tidak Ditemukan</strong>
                <p class="empty-desc">Tidak ada setoran yang cocok dengan pencarian ini.</p>
            </div>

        </div>


        <!-- FOOTER -->
        <div class="table-footer">
            <div class="table-info" id="tableInfoPagination">Menampilkan data...</div>

            <div class="table-pagination-controls">
                <div class="per-page">
                    <label for="cari_perPageSelect">Setoran per halaman:</label>
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


    {{-- Sumber opsi kategori; disalin ke setiap baris rincian sampah --}}
    <select id="masterKategori" class="combo-hidden" tabindex="-1" aria-hidden="true">
        <option value="" data-harga="0" data-satuan="" disabled selected>
            -- Pilih Kategori Sampah --
        </option>

        @foreach ($kategoriSampah as $kategori)
            @php
                $harga = $kategori->hargaTerbaru->harga_satuan ?? null;
                $satuan = $normalisasiSatuan($kategori->satuan ?? 'gram');
                $namaKat = $kategori->nama_kategori ?? $kategori->nama_lengkap ?? $kategori->nama ?? '-';
                $idKat = $kategori->id_kategori ?? $kategori->id;
            @endphp

            <option value="{{ $idKat }}" data-harga="{{ $harga ?? 0 }}"
                data-satuan="{{ $satuan }}">
                {{ $namaKat }} —
                @if ($harga !== null)
                    Rp {{ $fmtHarga($harga) }} / {{ $simbolSatuan($satuan) }}
                @else
                    belum ada harga
                @endif
            </option>
        @endforeach
    </select>


    <!-- ========================================================= -->
    <!-- MODAL VERIFIKASI -->
    <!-- ========================================================= -->

    <div class="modal-overlay" id="modalVerifikasi">

        <div class="modal-box modal-box--wide">

            <div class="modal-head">

                <h3 class="modal-title">

                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>

                    <span id="verifJudul">
                        Verifikasi Setoran
                    </span>

                </h3>

                <button class="modal-close" type="button" onclick="tutupModal('modalVerifikasi')">
                    &times;
                </button>

            </div>


            <form id="formVerifikasi" onsubmit="return prosesVerifikasi(event)">

                <div class="modal-body">

                    <input type="hidden" id="verifId">


                    <!-- WARGA -->
                    <div class="detail-avatar-row">

                        <div class="detail-avatar" id="verifAvatar">
                            -
                        </div>

                        <div>

                            <div class="detail-nama" id="verifNama">
                                -
                            </div>

                            <div class="detail-nik mono" id="verifNik">
                                -
                            </div>

                            <div class="detail-nik mono" id="verifKode">
                                -
                            </div>

                            <span class="status-badge" id="verifStatus" style="margin-top:6px;"></span>

                        </div>

                    </div>


                    <!-- FOTO -->
                    <div class="form-group form-group--full" id="verifFotoBlock">

                        <label>
                            Foto Bukti dari Warga
                        </label>

                        <div class="photo-frame" id="verifFotoWrap">

                            <span id="verifFotoKosong">
                                Tidak ada foto dilampirkan
                            </span>

                            <img id="verifFoto" src="" alt="Foto bukti setoran" style="display:none;">

                        </div>

                    </div>


                    <!-- RINCIAN SAMPAH -->
                    <div class="items-section">

                        <div class="items-head">
                            <div class="items-title">
                                Rincian Sampah
                                <span class="items-count" id="verifItemCount">0 jenis</span>
                            </div>

                            <button type="button" class="items-add" id="verifAddItem">
                                + Tambah Jenis
                            </button>
                        </div>

                        <div class="item-list" id="verifItemList"></div>

                        <div class="estimate-box">
                            <span class="lab">Estimasi total nilai setoran</span>
                            <span class="val" id="verifEstimasi">Rp 0</span>
                        </div>

                        <span class="field-sub">
                            Sesuaikan jenis dan jumlah dengan hasil pengecekan ulang jika berbeda.
                        </span>

                    </div>


                    <div class="form-grid">


                        <!-- ALASAN PENOLAKAN -->
                        <div class="form-group form-group--full reject-reason" id="verifAlasanBlock">

                            <label for="verifAlasan">
                                Catatan Penolakan
                            </label>

                            <textarea id="verifAlasan" rows="3"
                                placeholder="Contoh: foto tidak jelas, jumlah tidak sesuai foto, kategori tidak cocok..."></textarea>

                        </div>

                    </div>

                </div>


                <div class="modal-foot" id="verifFootActions">

                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalVerifikasi')">
                        Tutup
                    </button>

                    <button class="btn btn--danger" type="button" id="btnTolakSetoran" onclick="tolakSetoran()">
                        Tolak
                    </button>

                    <button class="btn btn--primary" type="submit" id="btnSetujuiSetoran">
                        Setujui &amp; Proses Saldo
                    </button>

                </div>

            </form>

        </div>

    </div>


    <!-- ========================================================= -->
    <!-- MODAL TAMBAH SETORAN -->
    <!-- ========================================================= -->

    <div class="modal-overlay" id="modalTambah">

        <div class="modal-box modal-box--wide">

            <div class="modal-head">

                <h3 class="modal-title">

                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>

                    Tambah Setoran

                </h3>

                <button class="modal-close" type="button" onclick="tutupModal('modalTambah')">
                    &times;
                </button>

            </div>


            <form id="formTambahSetoran" onsubmit="return simpanSetoranBaru(event)">

                <div class="modal-body">

                    <div class="form-grid">


                        <!-- WARGA -->
                        <div class="form-group form-group--full">

                            <label for="cariWarga">
                                Nama Warga
                            </label>

                            <div class="combo" id="comboWarga">

                                <input type="text" id="cariWarga" class="combo-input"
                                    placeholder="Ketik nama atau NIK warga..." autocomplete="off">

                                <div class="combo-list" id="listWarga">
                                </div>

                            </div>


                            <select id="tambahWarga" class="combo-hidden" tabindex="-1" aria-hidden="true">

                                <option value="">
                                    -- Pilih Warga --
                                </option>

                                @foreach ($wargaList as $w)
                                    <option value="{{ $w->id_warga }}">
                                        {{ $w->nama }}
                                        (NIK: {{ $w->nik }})
                                    </option>
                                @endforeach

                            </select>

                        </div>



                        <!-- TANGGAL -->
                        <div class="form-group form-group--full">

                            <label for="tambahTanggal">
                                Tanggal Setoran
                            </label>

                            <input type="date" id="tambahTanggal" required disabled>

                        </div>



                        <!-- RINCIAN SAMPAH -->
                        <div class="form-group form-group--full">

                            <div class="items-section">

                                <div class="items-head">
                                    <div class="items-title">
                                        Rincian Sampah
                                        <span class="items-count" id="tambahItemCount">0 jenis</span>
                                    </div>

                                    <button type="button" class="items-add" id="tambahAddItem">
                                        + Tambah Jenis
                                    </button>
                                </div>

                                <div class="item-list" id="tambahItemList"></div>

                                <div class="estimate-box">
                                    <span class="lab">Estimasi total nilai</span>
                                    <span class="val" id="tambahEstimasi">Rp 0</span>
                                </div>

                            </div>

                        </div>


                        <!-- CATATAN -->
                        <div class="form-group form-group--full">

                            <label for="tambahCatatan">
                                Catatan (opsional)
                            </label>

                            <textarea id="tambahCatatan" rows="2" placeholder="Contoh: setoran langsung di titik kumpul"></textarea>

                        </div>

                    </div>

                </div>


                <div class="modal-foot">

                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalTambah')">
                        Batal
                    </button>

                    <button class="btn btn--primary" type="submit">
                        Simpan &amp; Proses Saldo
                    </button>

                </div>

            </form>

        </div>

    </div>


    <style>
        /* =========================================================
           VERIFIKASI SETORAN
           ========================================================= */

        :root {
            --vs-page: #f5f7fb;
            --vs-surface: #ffffff;
            --vs-surface-2: #f8fafc;

            --vs-text: #1f2937;
            --vs-text-secondary: #6b7280;
            --vs-text-muted: #9ca3af;

            --vs-border: #e5e7eb;
            --vs-border-soft: #edf0f2;

            --vs-table-head: #f8fafc;
            --vs-table-hover: #fafafa;

            --vs-input-bg: #ffffff;
            --vs-empty: #f8fafc;

            --vs-shadow: 0 8px 25px rgba(15, 23, 42, .06);
            --vs-modal-shadow: 0 20px 45px rgba(15, 23, 42, .18);
        }

        [data-theme="dark"] {
            --vs-page: #0b1220;
            --vs-surface: #151d2f;
            --vs-surface-2: #1b2438;

            --vs-text: #f1f5f9;
            --vs-text-secondary: #aab6c8;
            --vs-text-muted: #748198;

            --vs-border: #29364d;
            --vs-border-soft: #29364d;

            --vs-table-head: #111a2c;
            --vs-table-hover: #202b40;

            --vs-input-bg: #111a2c;
            --vs-empty: #111a2c;

            --vs-shadow: 0 8px 25px rgba(0, 0, 0, .25);
            --vs-modal-shadow: 0 20px 45px rgba(0, 0, 0, .45);
        }

        .card {
            background: var(--vs-surface);
            border: 1px solid var(--vs-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--vs-shadow);
            color: var(--vs-text);
        }

        .hero-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .stat-chip {
            background: var(--vs-surface);
            border: 1px solid var(--vs-border);
            border-radius: 12px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 130px;
            box-shadow: var(--vs-shadow);
        }

        .stat-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .stat-dot--amber {
            background: #d97706;
        }

        .stat-dot--green {
            background: #16a34a;
        }

        .stat-dot--red {
            background: #dc2626;
        }

        [data-theme="dark"] .stat-dot--amber {
            background: #fbbf24;
            box-shadow: 0 0 0 4px rgba(251, 191, 36, .10);
        }

        [data-theme="dark"] .stat-dot--green {
            background: #4ade80;
            box-shadow: 0 0 0 4px rgba(74, 222, 128, .10);
        }

        [data-theme="dark"] .stat-dot--red {
            background: #f87171;
            box-shadow: 0 0 0 4px rgba(248, 113, 113, .10);
        }

        .stat-num {
            font-size: 18px;
            font-weight: 800;
            line-height: 1;
            color: var(--vs-text);
        }

        .stat-label {
            font-size: 12px;
            color: var(--vs-text-secondary);
            margin-top: 3px;
        }

        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 16px 24px;
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
            stroke: var(--vs-text-muted);
            stroke-width: 1.8;
            pointer-events: none;
        }

        .table-search input {
            width: 100%;
            height: 40px;
            padding: 0 14px 0 40px;
            border: 1px solid var(--vs-border);
            border-radius: 8px;
            outline: none;
            box-sizing: border-box;
            background: var(--vs-input-bg);
            color: var(--vs-text);
            transition: border-color .15s, box-shadow .15s;
        }

        .table-search input::placeholder {
            color: var(--vs-text-muted);
        }

        .table-search input:focus {
            border-color: var(--primary, #16a34a);
            box-shadow: 0 0 0 3px rgba(22, 163, 74, .10);
        }

        .filter-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .fpill {
            border: 1px solid var(--vs-border);
            background: transparent;
            color: var(--vs-text-secondary);
            padding: 8px 14px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: .15s ease;
        }

        .fpill:hover {
            background: var(--vs-surface-2);
            color: var(--vs-text);
        }

        .fpill.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
            color: var(--vs-text);
        }

        .data-table th {
            background: var(--vs-table-head);
            color: var(--vs-text-secondary);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 13px 16px;
            text-align: left;
            border-top: 1px solid var(--vs-border);
            border-bottom: 1px solid var(--vs-border);
            white-space: nowrap;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid var(--vs-border-soft);
            font-size: 13px;
            color: var(--vs-text);
            vertical-align: middle;
            background: var(--vs-surface);
        }

        .data-table tbody tr {
            transition: background .15s ease;
        }

        .data-table tbody tr:hover td {
            background: var(--vs-table-hover);
        }

        .data-table strong {
            color: var(--vs-text);
        }

        .mono {
            font-family: monospace;
            font-size: 12px;
            color: var(--vs-text-secondary);
        }

        .kode-trx {
            white-space: nowrap;
            font-weight: 600;
            color: var(--vs-text);
        }

        .unit {
            color: var(--vs-text-muted);
            font-size: 12px;
        }

        .kategori-tag {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 7px;
            font-size: 12.5px;
            font-weight: 600;
            background: rgba(22, 163, 74, .08);
            color: #15803d;
            border: 1px solid #bfead0;
        }

        [data-theme="dark"] .kategori-tag {
            background: rgba(74, 222, 128, .10);
            color: #4ade80;
            border-color: rgba(74, 222, 128, .25);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 11px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-badge--pending {
            background: #fef3e0;
            color: #b45309;
            border: 1px solid #f3d9a0;
        }

        .status-badge--approved {
            background: #e9f9ee;
            color: #15803d;
            border: 1px solid #bfead0;
        }

        .status-badge--rejected {
            background: #fdeced;
            color: #c0293c;
            border: 1px solid #f4c2c8;
        }

        [data-theme="dark"] .status-badge--pending {
            background: rgba(251, 191, 36, .12);
            color: #fbbf24;
            border-color: rgba(251, 191, 36, .25);
        }

        [data-theme="dark"] .status-badge--approved {
            background: rgba(74, 222, 128, .12);
            color: #4ade80;
            border-color: rgba(74, 222, 128, .25);
        }

        [data-theme="dark"] .status-badge--rejected {
            background: rgba(248, 113, 113, .12);
            color: #f87171;
            border-color: rgba(248, 113, 113, .25);
        }

        .table-actions {
            display: flex;
            gap: 6px;
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            border: 1px solid var(--vs-border);
            background: var(--vs-surface);
            color: var(--vs-text-secondary);
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: .15s ease;
        }

        .icon-btn svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .icon-btn:hover {
            background: var(--vs-surface-2);
            color: var(--vs-text);
        }

        .icon-btn--primary {
            border-color: #16a34a;
            color: #16a34a;
        }

        .icon-btn--primary:hover {
            background: rgba(22, 163, 74, .10);
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            flex-wrap: wrap;
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
            color: var(--vs-text-secondary);
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
            color: var(--vs-text-secondary);
        }

        .catalog-empty {
            text-align: center;
            padding: 50px 20px;
            color: var(--vs-text);
        }

        .empty-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .empty-desc {
            margin: 5px 0 0;
            color: var(--vs-text-secondary);
        }

        .table-info {
            font-size: 13px;
            color: var(--vs-text-secondary);
        }

        .table-info strong {
            color: var(--vs-text);
        }

        .data-table td[style*="text-align: center"] {
            background: var(--vs-empty);
            color: var(--vs-text);
        }

        .data-table td[style*="text-align: center"] strong {
            color: var(--vs-text);
        }

        .data-table td[style*="text-align: center"] p {
            color: var(--vs-text-secondary) !important;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .50);
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 1000;
            backdrop-filter: blur(2px);
        }

        [data-theme="dark"] .modal-overlay {
            background: rgba(2, 6, 23, .72);
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            background: var(--vs-surface);
            border: 1px solid var(--vs-border);
            border-radius: 14px;
            box-shadow: var(--vs-modal-shadow);
            color: var(--vs-text);
            animation: modalPop .15s ease-out;
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
            border-bottom: 1px solid var(--vs-border);
        }

        .modal-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--vs-text);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
        }

        .modal-title svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: #16a34a;
            stroke-width: 1.8;
        }

        .modal-close {
            width: 30px;
            height: 30px;
            border: none;
            background: transparent;
            border-radius: 7px;
            font-size: 20px;
            line-height: 1;
            color: var(--vs-text-secondary);
            cursor: pointer;
        }

        .modal-close:hover {
            background: var(--vs-surface-2);
            color: var(--vs-text);
        }

        .modal-body {
            padding: 22px 24px;
        }

        .modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid var(--vs-border);
            background: var(--vs-surface);
        }

        .detail-avatar-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 18px;
            margin-bottom: 18px;
            border-bottom: 1px solid var(--vs-border);
        }

        .detail-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(22, 163, 74, .10);
            color: #15803d;
            font-weight: 700;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        [data-theme="dark"] .detail-avatar {
            background: rgba(74, 222, 128, .12);
            color: #4ade80;
        }

        .detail-nama {
            font-size: 16px;
            font-weight: 700;
            color: var(--vs-text);
        }

        .detail-nik {
            margin-top: 3px;
            color: var(--vs-text-secondary);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 18px;
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
            color: var(--vs-text);
        }

        .form-group input,
        .form-group select,
        .form-group textarea,
        .combo-input {
            border: 1px solid var(--vs-border);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            width: 100%;
            background: var(--vs-input-bg);
            color: var(--vs-text);
            transition: border-color .15s, box-shadow .15s;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder,
        .combo-input::placeholder {
            color: var(--vs-text-muted);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus,
        .combo-input:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, .10);
        }

        .form-group input:disabled,
        .form-group select:disabled {
            opacity: .65;
            cursor: not-allowed;
            background: var(--vs-surface-2);
        }

        .form-group textarea {
            resize: vertical;
        }

        [data-theme="dark"] select option {
            background: #111a2c;
            color: #f1f5f9;
        }

        .field-sub {
            font-size: 11.5px;
            color: var(--vs-text-muted);
        }

        .photo-frame {
            border: 1px dashed var(--vs-border);
            border-radius: 12px;
            min-height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--vs-text-muted);
            font-size: 12.5px;
            background: var(--vs-empty);
            overflow: hidden;
        }

        .photo-frame img {
            width: 100%;
            max-height: 240px;
            object-fit: cover;
        }

        .estimate-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-radius: 12px;
            background: #e9f9ee;
            border: 1px solid #bfead0;
        }

        .estimate-box .lab {
            font-size: 12.5px;
            color: #15803d;
            font-weight: 600;
        }

        .estimate-box .val {
            font-size: 19px;
            font-weight: 800;
            color: #15803d;
        }

        [data-theme="dark"] .estimate-box {
            background: rgba(74, 222, 128, .10);
            border-color: rgba(74, 222, 128, .25);
        }

        [data-theme="dark"] .estimate-box .lab,
        [data-theme="dark"] .estimate-box .val {
            color: #4ade80;
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

        .combo {
            position: relative;
        }

        .combo-hidden {
            display: none !important;
        }

        .combo-list {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            max-height: 220px;
            overflow-y: auto;
            background: var(--vs-surface);
            border: 1px solid var(--vs-border);
            border-radius: 8px;
            box-shadow: var(--vs-modal-shadow);
            z-index: 20;
        }

        .combo-list.show {
            display: block;
        }

        .combo-item {
            padding: 9px 12px;
            font-size: 13px;
            color: var(--vs-text);
            cursor: pointer;
            transition: background .12s;
        }

        .combo-item:hover,
        .combo-item.is-active {
            background: var(--vs-table-hover);
        }

        .combo-empty {
            padding: 12px;
            font-size: 12.5px;
            color: var(--vs-text-muted);
            text-align: center;
        }

        .reject-reason {
            display: none;
        }

        .reject-reason.open {
            display: flex;
        }

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
            background: var(--vs-surface);
            border: 1px solid var(--vs-border);
            border-left: 4px solid #16a34a;
            border-radius: 10px;
            box-shadow: var(--vs-modal-shadow);
            padding: 14px 16px;
            pointer-events: auto;
            animation: toastIn .18s ease-out;
            color: var(--vs-text);
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
            color: var(--vs-text);
            margin: 0 0 2px;
        }

        .toast-text {
            font-size: 12.5px;
            color: var(--vs-text-secondary);
            margin: 0;
            line-height: 1.5;
        }

        .toast-close {
            border: none;
            background: transparent;
            color: var(--vs-text-muted);
            font-size: 16px;
            line-height: 1;
            cursor: pointer;
            padding: 0;
            flex-shrink: 0;
        }

        .toast-close:hover {
            color: var(--vs-text);
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

        @media (max-width: 560px) {

            .form-grid {
                grid-template-columns: 1fr;
            }

            .hero-actions {
                width: 100%;
            }

            .stat-chip {
                flex: 1;
                min-width: 0;
            }

            .table-toolbar {
                padding: 14px 16px;
            }

            .table-footer {
                padding: 14px 16px;
            }

            .modal-body {
                padding: 18px 16px;
            }

            .modal-head,
            .modal-foot {
                padding-left: 16px;
                padding-right: 16px;
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

            .modal-overlay {
                padding: 10px;
            }

            .modal-box {
                max-height: 94vh;
                border-radius: 12px;
            }

            .modal-foot {
                flex-wrap: wrap;
            }

            .modal-foot .btn {
                flex: 1;
            }
        }

        /* =========================================================
           MODAL & COMBOBOX — konsisten dengan halaman lain
           ========================================================= */
        .modal-box {
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-box > form {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }

        .modal-head,
        .modal-foot {
            flex-shrink: 0;
        }

        .modal-body {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--vs-border) transparent;
        }

        .modal-body::-webkit-scrollbar { width: 6px; }
        .modal-body::-webkit-scrollbar-track { background: transparent; }
        .modal-body::-webkit-scrollbar-thumb { background: var(--vs-border); border-radius: 4px; }
        .modal-body::-webkit-scrollbar-thumb:hover { background: var(--vs-text-muted); }

        .combo-input:disabled {
            opacity: .65;
            cursor: not-allowed;
            background: var(--vs-surface-2);
        }

        .combo-list {
            box-shadow: 0 10px 25px rgba(0, 0, 0, .22);
            z-index: 60;
            scrollbar-width: thin;
            scrollbar-color: var(--vs-border) transparent;
        }

        .combo-list::-webkit-scrollbar { width: 6px; }
        .combo-list::-webkit-scrollbar-track { background: transparent; }
        .combo-list::-webkit-scrollbar-thumb { background: var(--vs-border); border-radius: 4px; }
        .combo-list::-webkit-scrollbar-thumb:hover { background: var(--vs-text-muted); }

        .combo-item {
            padding: 10px 14px;
            transition: background .1s ease, color .1s ease;
        }

        .combo-item.is-selected { font-weight: 700; }

        .combo-list {
            min-width: 84px;
        }

        .combo--up .combo-list {
            top: auto;
            bottom: calc(100% + 4px);
        }

        .combo--arrow .combo-input {
            cursor: pointer;
            padding-right: 30px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 9px center;
            background-size: 14px;
        }

        .combo-item:hover,
        .combo-item.is-active {
            background: var(--vs-table-hover);
            color: #16a34a;
        }

        /* =========================================================
           RINCIAN BEBERAPA JENIS SAMPAH
           ========================================================= */
        .col-no {
            width: 56px;
            text-align: center;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
        }

        .modal-box--wide {
            max-width: 680px;
        }

        .kategori-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            max-width: 240px;
        }

        .kategori-tag--more {
            background: var(--vs-surface-2);
            color: var(--vs-text-secondary);
            border-color: var(--vs-border);
        }

        .items-section {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .items-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .items-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 700;
            color: var(--vs-text);
        }

        .items-count {
            padding: 2px 9px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 600;
            background: rgba(22, 163, 74, .10);
            color: #15803d;
        }

        [data-theme="dark"] .items-count {
            background: rgba(74, 222, 128, .12);
            color: #4ade80;
        }

        .items-add {
            padding: 7px 12px;
            border-radius: 8px;
            border: 1px dashed #16a34a;
            background: transparent;
            color: #16a34a;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            transition: background .12s;
        }

        .items-add:hover {
            background: rgba(22, 163, 74, .08);
        }

        .item-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .item-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 12px 14px;
            border: 1px solid var(--vs-border);
            border-radius: 12px;
            background: var(--vs-surface-2);
        }

        .item-row.is-invalid {
            border-color: #dc2626;
        }

        .item-row__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .item-row__no {
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--vs-text-secondary);
        }

        .item-row__remove {
            width: 26px;
            height: 26px;
            border: none;
            border-radius: 7px;
            background: transparent;
            color: var(--vs-text-muted);
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
        }

        .item-row__remove:hover {
            background: rgba(220, 38, 38, .10);
            color: #dc2626;
        }

        .item-row__grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 190px;
            gap: 12px;
        }

        .item-row__qty {
            display: flex;
            gap: 8px;
        }

        .item-row__qty .item-qty {
            flex: 1;
            min-width: 0;
        }

        .item-row__qty .item-unit {
            width: 72px;
        }

        .item-row__foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-top: 8px;
            border-top: 1px dashed var(--vs-border);
        }

        .item-info {
            font-size: 11.5px;
            color: var(--vs-text-muted);
        }

        .item-sub {
            font-size: 13px;
            font-weight: 700;
            color: var(--vs-text);
            white-space: nowrap;
        }

        .combo-item.is-disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .combo-item.is-disabled:hover {
            background: transparent;
            color: var(--vs-text);
        }

        .combo-note {
            font-size: 11.5px;
            font-style: italic;
        }

        @media (max-width: 560px) {
            .item-row__grid {
                grid-template-columns: 1fr;
            }
        }
    </style>


    <script>
        let verifItem = null;
        let verifEditor = null;
        let tambahEditor = null;

        /*
        |--------------------------------------------------------------------------
        | DOM READY
        |--------------------------------------------------------------------------
        */
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif

            const searchInput = document.getElementById('searchSetoran');
            const table = document.getElementById('setoranTable');
            const perPageSelect = document.getElementById('perPageSelect');
            const paginationContainer = document.getElementById('paginationButtons');
            const tableInfo = document.getElementById('tableInfoPagination');
            const kosongCari = document.getElementById('setoranKosongCari');

            let currentPage = 1;

            const rows = table ? Array.from(table.querySelectorAll('tbody tr[data-status]')) : [];

            /*
            |--------------------------------------------------------------------------
            | FILTER TABLE & PAGINASI
            |--------------------------------------------------------------------------
            */
            function render() {
                if (!table) return;

                const keyword = (searchInput?.value || '').toLowerCase().trim();
                const activePill = document.querySelector('.fpill.active');
                const statusFilter = activePill ? activePill.dataset.filter : 'all';

                const filtered = rows.filter(function(row) {
                    const matchSearch = !keyword || row.textContent.toLowerCase().includes(keyword);
                    const matchStatus = statusFilter === 'all' || row.dataset.status === statusFilter;
                    return matchSearch && matchStatus;
                });

                if (kosongCari) {
                    kosongCari.style.display = (rows.length > 0 && filtered.length === 0) ? '' : 'none';
                }

                const perPage = parseInt(perPageSelect ? perPageSelect.value : 10, 10);
                const totalPages = Math.ceil(filtered.length / perPage) || 1;
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);

                rows.forEach(r => r.style.display = 'none');

                const start = (currentPage - 1) * perPage;
                const end = start + perPage;
                filtered.slice(start, end).forEach(function(r, i) {
                    r.style.display = '';
                    const no = r.querySelector('.no-urut');
                    if (no) no.textContent = start + i + 1;
                });

                if (tableInfo) {
                    tableInfo.innerHTML = filtered.length === 0 ?
                        'Tidak ada data yang ditampilkan' :
                        `Menampilkan setoran <strong>${start + 1}</strong> - <strong>${Math.min(end, filtered.length)}</strong> dari <strong>${filtered.length}</strong>`;
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

            document.querySelectorAll('.fpill').forEach(function(pill) {
                pill.addEventListener('click', function() {
                    document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                    currentPage = 1;
                    render();
                });
            });

            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    currentPage = 1;
                    render();
                });
            }

            initCombo({
                comboId: 'combo_perPageSelect',
                selectId: 'perPageSelect',
                inputId: 'cari_perPageSelect',
                listId: 'list_perPageSelect',
                readonly: true,
                onChange: function() {
                    currentPage = 1;
                    render();
                }
            });

            render();

            /*
            |--------------------------------------------------------------------------
            | CLOSE MODAL
            |--------------------------------------------------------------------------
            */
            document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        overlay.classList.remove('active');
                    }
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay.active').forEach(function(overlay) {
                        overlay.classList.remove('active');
                    });
                }
            });

            /*
            |--------------------------------------------------------------------------
            | VERIFIKASI & TAMBAH EVENT LISTENERS
            |--------------------------------------------------------------------------
            */
            verifEditor = createItemEditor({
                prefix: 'verif',
                listId: 'verifItemList',
                totalId: 'verifEstimasi',
                countId: 'verifItemCount',
                addBtnId: 'verifAddItem'
            });

            tambahEditor = createItemEditor({
                prefix: 'tambah',
                listId: 'tambahItemList',
                totalId: 'tambahEstimasi',
                countId: 'tambahItemCount',
                addBtnId: 'tambahAddItem'
            });

            /*
            |--------------------------------------------------------------------------
            | COMBOBOX INISIALISASI
            |--------------------------------------------------------------------------
            */
            initCombo({
                comboId: 'comboWarga',
                selectId: 'tambahWarga',
                inputId: 'cariWarga',
                listId: 'listWarga'
            });
        });


        /*
        |--------------------------------------------------------------------------
        | COMBOBOX COMPONENT UTAMA
        |--------------------------------------------------------------------------
        */
        const comboRegistry = {};

        function syncCombo(selectId) {
            if (comboRegistry[selectId]) comboRegistry[selectId]();
        }

        function initCombo(cfg) {
            const select = document.getElementById(cfg.selectId);
            const input = document.getElementById(cfg.inputId);
            const list = document.getElementById(cfg.listId);
            const onChange = cfg.onChange || function() {};
            const isDisabled = cfg.isDisabled || function() { return false; };

            if (!select || !input || !list) return;

            const data = Array.from(select.options)
                .filter(o => o.value !== '')
                .map(o => ({
                    value: o.value,
                    label: o.textContent.replace(/\s+/g, ' ').trim()
                }));

            const readonly = !!cfg.readonly;
            input.readOnly = readonly;

            let aktif = -1;

            function escapeHtml(s) {
                return String(s).replace(/[&<>"']/g, function(c) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    }[c];
                });
            }

            function sync() {
                const d = data.find(x => x.value === String(select.value));
                input.value = d ? d.label : '';
                input.disabled = select.disabled;
            }

            function render(keyword) {
                const k = (keyword || '').toLowerCase().trim();
                const hasil = data.filter(d => d.label.toLowerCase().includes(k));

                aktif = -1;

                list.innerHTML = hasil.length
                    ? hasil.map(d => {
                        const nonaktif = d.value !== String(select.value) && isDisabled(d.value);
                        return `<div class="combo-item${d.value === String(select.value) ? ' is-selected' : ''}${nonaktif ? ' is-disabled' : ''}" data-value="${escapeHtml(d.value)}"${nonaktif ? ' aria-disabled="true"' : ''}>${escapeHtml(d.label)}${nonaktif ? ' <span class="combo-note">· sudah dipilih</span>' : ''}</div>`;
                    }).join('')
                    : '<div class="combo-empty">Data tidak ditemukan</div>';
            }

            function buka(keyword) {
                render(keyword);
                list.classList.add('show');
                const terpilih = list.querySelector('.is-selected');
                if (terpilih) terpilih.scrollIntoView({ block: 'nearest' });
            }

            function tutup() {
                list.classList.remove('show');
                sync();
            }

            function pilih(value) {
                const d = data.find(x => x.value === String(value));
                if (!d) return;
                if (d.value !== String(select.value) && isDisabled(d.value)) return;

                const berubah = select.value !== d.value;
                select.value = d.value;
                tutup();
                if (berubah) onChange();
            }

            function sorot(arah) {
                const items = list.querySelectorAll('.combo-item:not(.is-disabled)');
                if (!items.length) return;

                aktif = (aktif + arah + items.length) % items.length;

                items.forEach((el, i) => {
                    el.classList.toggle('is-active', i === aktif);
                });

                items[aktif].scrollIntoView({ block: 'nearest' });
            }

            input.addEventListener('focus', function() {
                if (!readonly) input.select();
                buka('');
            });

            input.addEventListener('click', function() {
                if (!list.classList.contains('show')) buka('');
            });

            input.addEventListener('input', function() {
                buka(this.value);
            });

            input.addEventListener('blur', tutup);

            input.addEventListener('keydown', function(e) {
                const items = list.querySelectorAll('.combo-item:not(.is-disabled)');

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!list.classList.contains('show')) buka(readonly ? '' : this.value);
                    sorot(1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    sorot(-1);
                } else if (e.key === 'Enter') {
                    if (list.classList.contains('show') && aktif > -1 && items[aktif]) {
                        e.preventDefault();
                        pilih(items[aktif].dataset.value);
                    }
                } else if (e.key === 'Escape') {
                    if (list.classList.contains('show')) {
                        e.stopPropagation();
                        tutup();
                    }
                }
            });

            list.addEventListener('mouseover', function(e) {
                const item = e.target.closest('.combo-item:not(.is-disabled)');
                if (!item) return;

                list.querySelectorAll('.combo-item:not(.is-disabled)').forEach((el, i) => {
                    el.classList.toggle('is-active', el === item);
                    if (el === item) aktif = i;
                });
            });

            list.addEventListener('mousedown', function(e) {
                e.preventDefault();
                const item = e.target.closest('.combo-item:not(.is-disabled)');
                if (item) pilih(item.dataset.value);
            });

            comboRegistry[cfg.selectId] = sync;
            sync();
        }


        /*
        |--------------------------------------------------------------------------
        | RESET COMBO
        |--------------------------------------------------------------------------
        */
        function resetCombo(selectId, inputId, listId) {
            const select = document.getElementById(selectId);
            const input = document.getElementById(inputId);
            const list = document.getElementById(listId);

            if (select) select.value = '';
            if (input) input.value = '';
            if (list) list.classList.remove('show');
        }


        /*
        |--------------------------------------------------------------------------
        | MODAL CONTROL
        |--------------------------------------------------------------------------
        */
        function bukaModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function tutupModal(id) {
            document.getElementById(id).classList.remove('active');
        }


        /*
        |--------------------------------------------------------------------------
        | FORMATTER HELPERS
        |--------------------------------------------------------------------------
        */
        function formatRupiah(angka) {
            return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID', {
                maximumFractionDigits: 2
            });
        }

        function formatHarga(n, satuan = '') {
            const nilai = Number(n) || 0;
            return 'Rp ' + nilai.toLocaleString('id-ID', {
                maximumFractionDigits: 2
            }) + (satuan ? ' / ' + satuan : '');
        }

        function hargaDariSelect(select) {
            if (!select || select.selectedIndex < 0) return 0;
            return Number(select.options[select.selectedIndex]?.dataset.harga || 0);
        }

        function satuanDariSelect(select) {
            if (!select || select.selectedIndex < 0) return '';
            return select.options[select.selectedIndex]?.dataset.satuan || '';
        }

        function pilihKategori(select, idKategori) {
            select.value = idKategori ? String(idKategori) : '';
            if (select.selectedIndex === -1) {
                select.value = '';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | TOAST NOTIFICATION
        |--------------------------------------------------------------------------
        */
        function showToast(title, text, type = 'success') {
            const wrap = document.getElementById('toastWrap');
            const toast = document.createElement('div');

            toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');
            const iconPath = type === 'error'
                ? '<path d="M18 6 6 18M6 6l12 12"></path>'
                : '<path d="M20 6 9 17l-5-5"></path>';

            toast.innerHTML = `
                <div class="toast-icon">
                    <svg viewBox="0 0 24 24">${iconPath}</svg>
                </div>
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


        /*
        |--------------------------------------------------------------------------
        | EDITOR RINCIAN SAMPAH (BANYAK JENIS DALAM SATU SETORAN)
        |--------------------------------------------------------------------------
        */
        function createItemEditor(cfg) {
            const listEl = document.getElementById(cfg.listId);
            const totalEl = document.getElementById(cfg.totalId);
            const countEl = document.getElementById(cfg.countId);
            const addBtn = document.getElementById(cfg.addBtnId);
            const master = document.getElementById('masterKategori');

            let seq = 0;
            let editable = true;

            const rows = () => Array.from(listEl.querySelectorAll('.item-row'));

            function updateRow(row) {
                const select = row.querySelector('select');
                const harga = hargaDariSelect(select);
                const satuan = satuanDariSelect(select);
                const jumlah = parseFloat(row.querySelector('.item-qty').value) || 0;
                const tersimpan = !editable && row.dataset.subtotal !== '' ? Number(row.dataset.subtotal) : null;
                const nilai = tersimpan !== null ? tersimpan : harga * jumlah;

                row.querySelector('.item-unit').value = satuan || '-';

                let info = 'Pilih kategori terlebih dahulu.';
                if (select.value) {
                    info = harga > 0 && satuan
                        ? 'Harga aktif: ' + formatHarga(harga, satuan === 'liter' ? 'L' : satuan)
                        : 'Kategori ini belum memiliki harga aktif.';
                }
                if (tersimpan !== null) info = 'Nilai tercatat saat transaksi.';

                row.querySelector('.item-info').textContent = info;
                row.querySelector('.item-sub').textContent = formatRupiah(nilai);

                return nilai;
            }

            function recalc() {
                const list = rows();
                let total = 0;

                list.forEach((row, i) => {
                    total += updateRow(row);
                    row.querySelector('.item-row__no').textContent = 'Jenis sampah ' + (i + 1);
                    row.querySelector('.item-row__remove').style.display =
                        editable && list.length > 1 ? '' : 'none';
                });

                totalEl.textContent = formatRupiah(total);
                countEl.textContent = list.length + ' jenis';
                if (addBtn) addBtn.style.display = editable ? '' : 'none';
            }

            function applyEditable(row) {
                const select = row.querySelector('select');
                select.disabled = !editable;
                row.querySelector('.item-qty').disabled = !editable;
                syncCombo(select.id);
            }

            function addRow(data = {}, focus = false) {
                const uid = cfg.prefix + '_' + (++seq);
                const row = document.createElement('div');

                row.className = 'item-row';
                row.dataset.idDetail = data.id_detail ?? '';
                row.dataset.subtotal = data.subtotal ?? '';
                row.innerHTML = `
                    <div class="item-row__head">
                        <span class="item-row__no"></span>
                        <button type="button" class="item-row__remove" title="Hapus jenis ini" aria-label="Hapus jenis ini">&times;</button>
                    </div>

                    <div class="item-row__grid">
                        <div class="form-group">
                            <label for="${uid}_inp">Kategori Sampah</label>
                            <div class="combo">
                                <input type="text" id="${uid}_inp" class="combo-input"
                                    placeholder="Ketik / pilih kategori sampah..." autocomplete="off">
                                <div class="combo-list" id="${uid}_list"></div>
                            </div>
                            <select id="${uid}_sel" class="combo-hidden" tabindex="-1" aria-hidden="true"></select>
                        </div>

                        <div class="form-group">
                            <label for="${uid}_qty">Jumlah</label>
                            <div class="item-row__qty">
                                <input type="number" id="${uid}_qty" class="item-qty" step="0.001" min="0.001" required>
                                <input type="text" class="item-unit" value="-" readonly tabindex="-1">
                            </div>
                        </div>
                    </div>

                    <div class="item-row__foot">
                        <span class="item-info">Pilih kategori terlebih dahulu.</span>
                        <span class="item-sub">Rp 0</span>
                    </div>
                `;

                listEl.appendChild(row);

                const select = row.querySelector('select');
                select.innerHTML = master.innerHTML;
                pilihKategori(select, data.id_kategori);
                select.disabled = !editable;

                const qty = row.querySelector('.item-qty');
                qty.value = data.jumlah ?? '';
                qty.disabled = !editable;
                qty.addEventListener('input', function() {
                    row.classList.remove('is-invalid');
                    recalc();
                });

                initCombo({
                    selectId: select.id,
                    inputId: uid + '_inp',
                    listId: uid + '_list',
                    isDisabled: function(value) {
                        return rows().some(r => r !== row && r.querySelector('select').value === String(value));
                    },
                    onChange: function() {
                        row.classList.remove('is-invalid');
                        recalc();
                    }
                });

                row.querySelector('.item-row__remove').addEventListener('click', function() {
                    delete comboRegistry[select.id];
                    row.remove();
                    recalc();
                });

                recalc();

                if (focus) {
                    row.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    row.querySelector('.combo-input').focus();
                }

                return row;
            }

            function clear() {
                rows().forEach(r => delete comboRegistry[r.querySelector('select').id]);
                listEl.innerHTML = '';
            }

            function setItems(items, canEdit) {
                clear();
                editable = !!canEdit;

                const data = Array.isArray(items) && items.length ? items : [{}];
                data.forEach(d => addRow(d));

                rows().forEach(applyEditable);
                recalc();
            }

            function getItems() {
                return rows().map(row => ({
                    id_detail: row.dataset.idDetail || null,
                    id_kategori: row.querySelector('select').value,
                    jumlah: row.querySelector('.item-qty').value
                }));
            }

            function validate() {
                const list = rows();
                const dipakai = new Set();

                for (const row of list) {
                    row.classList.remove('is-invalid');
                }

                for (const row of list) {
                    const kat = row.querySelector('select').value;
                    const jml = Number(row.querySelector('.item-qty').value);

                    if (!kat) {
                        row.classList.add('is-invalid');
                        row.querySelector('.combo-input').focus();
                        return 'Silakan pilih kategori sampah pada setiap baris.';
                    }

                    if (!jml || jml <= 0) {
                        row.classList.add('is-invalid');
                        row.querySelector('.item-qty').focus();
                        return 'Jumlah setiap jenis sampah harus lebih dari 0.';
                    }

                    if (dipakai.has(kat)) {
                        row.classList.add('is-invalid');
                        row.querySelector('.combo-input').focus();
                        return 'Kategori yang sama dipilih lebih dari sekali. Gabungkan jumlahnya dalam satu baris.';
                    }

                    dipakai.add(kat);
                }

                return null;
            }

            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    addRow({}, true);
                });
            }

            return { setItems, getItems, validate, addRow };
        }


        /*
        |--------------------------------------------------------------------------
        | BUKA MODAL VERIFIKASI
        |--------------------------------------------------------------------------
        */
        function bukaVerifikasi(item) {
            verifItem = item;
            const nama = item.warga?.nama || '-';
            const bisaDiedit = item.status === 'pending';

            document.getElementById('verifAvatar').textContent = nama.trim().charAt(0).toUpperCase();
            document.getElementById('verifNama').textContent = nama;
            document.getElementById('verifNik').textContent = item.warga?.nik || '-';
            document.getElementById('verifKode').textContent = 'ID Transaksi: ' + (item.kode_transaksi || '-');
            document.getElementById('verifJudul').textContent = bisaDiedit ? 'Verifikasi Setoran' : 'Detail Setoran';
            document.getElementById('verifId').value = item.id;

            const statusEl = document.getElementById('verifStatus');
            const statusMap = {
                pending: ['Menunggu', 'status-badge--pending'],
                approved: ['Disetujui', 'status-badge--approved'],
                rejected: ['Ditolak', 'status-badge--rejected']
            };
            const [statusTeks, statusKelas] = statusMap[item.status] || statusMap.rejected;
            statusEl.className = 'status-badge ' + statusKelas;
            statusEl.textContent = statusTeks;

            document.getElementById('verifAlasan').value = item.catatan_admin || '';
            document.getElementById('verifAlasanBlock').classList.remove('open');

            const fotoEl = document.getElementById('verifFoto');
            const fotoKosong = document.getElementById('verifFotoKosong');

            if (item.foto_bukti) {
                fotoEl.src = '/storage/' + item.foto_bukti;
                fotoEl.style.display = 'block';
                fotoKosong.style.display = 'none';
            } else {
                fotoEl.style.display = 'none';
                fotoKosong.style.display = 'block';
            }

            verifEditor.setItems(item.items, bisaDiedit);

            document.getElementById('btnTolakSetoran').style.display = bisaDiedit ? 'inline-flex' : 'none';
            document.getElementById('btnSetujuiSetoran').style.display = bisaDiedit ? 'inline-flex' : 'none';

            bukaModal('modalVerifikasi');
        }


        /*
        |--------------------------------------------------------------------------
        | PROSES VERIFIKASI (SETUJUI)
        |--------------------------------------------------------------------------
        */
        function prosesVerifikasi(event) {
            event.preventDefault();

            const error = verifEditor.validate();
            if (error) {
                showToast('Gagal memproses', error, 'error');
                return false;
            }

            const id = document.getElementById('verifId').value;
            const payload = { items: verifEditor.getItems() };

            fetch(`/admin/pages/verifikasi-setoran/${id}/setujui`, {
                method: 'PATCH',
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
                    throw new Error(err.message || 'Gagal menyetujui setoran.');
                }
                return res.json();
            })
            .then(() => {
                tutupModal('modalVerifikasi');
                showToast('Setoran disetujui', 'Saldo warga telah diperbarui.');
                setTimeout(() => window.location.reload(), 800);
            })
            .catch((err) => {
                showToast('Gagal menyetujui', err.message, 'error');
            });

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | BUKA TAMBAH SETORAN
        |--------------------------------------------------------------------------
        */
        function bukaTambahSetoran() {
            document.getElementById('formTambahSetoran').reset();
            resetCombo('tambahWarga', 'cariWarga', 'listWarga');

            const now = new Date();
            const today = now.getFullYear() + '-' +
                String(now.getMonth() + 1).padStart(2, '0') + '-' +
                String(now.getDate()).padStart(2, '0');
            document.getElementById('tambahTanggal').value = today;

            tambahEditor.setItems([{}], true);
            bukaModal('modalTambah');
        }


        /*
        |--------------------------------------------------------------------------
        | SIMPAN SETORAN BARU
        |--------------------------------------------------------------------------
        */
        function simpanSetoranBaru(event) {
            event.preventDefault();

            const idWarga = document.getElementById('tambahWarga').value;

            if (!idWarga) {
                showToast('Gagal menyimpan', 'Silakan pilih warga terlebih dahulu.', 'error');
                document.getElementById('cariWarga').focus();
                return false;
            }

            const error = tambahEditor.validate();
            if (error) {
                showToast('Gagal menyimpan', error, 'error');
                return false;
            }

            const payload = {
                id_warga: idWarga,
                tanggal_setoran: document.getElementById('tambahTanggal').value,
                catatan_admin: document.getElementById('tambahCatatan')?.value || null,
                items: tambahEditor.getItems().map(({ id_kategori, jumlah }) => ({ id_kategori, jumlah }))
            };

            fetch(`/admin/pages/verifikasi-setoran`, {
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
                    throw new Error(err.message || 'Gagal menyimpan setoran.');
                }
                return res.json();
            })
            .then(() => {
                tutupModal('modalTambah');
                showToast('Setoran ditambahkan', 'Data setoran tersimpan dan saldo warga diperbarui.');
                setTimeout(() => window.location.reload(), 800);
            })
            .catch((err) => {
                showToast('Gagal menyimpan', err.message, 'error');
            });

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | TOLAK SETORAN
        |--------------------------------------------------------------------------
        */
        function tolakSetoran() {
            const alasanBlock = document.getElementById('verifAlasanBlock');

            if (!alasanBlock.classList.contains('open')) {
                alasanBlock.classList.add('open');
                document.getElementById('verifAlasan').focus();
                return;
            }

            const catatan = document.getElementById('verifAlasan').value.trim();

            if (!catatan) {
                showToast('Catatan diperlukan', 'Isi alasan penolakan sebelum melanjutkan.', 'error');
                return;
            }

            const id = document.getElementById('verifId').value;

            fetch(`/admin/pages/verifikasi-setoran/${id}/tolak`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ catatan_admin: catatan })
            })
            .then(async (res) => {
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || 'Gagal menolak setoran.');
                }
                return res.json();
            })
            .then(() => {
                tutupModal('modalVerifikasi');
                showToast('Setoran ditolak', 'Warga perlu mengisi ulang data setoran.');
                setTimeout(() => window.location.reload(), 800);
            })
            .catch((err) => {
                showToast('Gagal menolak', err.message, 'error');
            });
        }
    </script>

@endsection
