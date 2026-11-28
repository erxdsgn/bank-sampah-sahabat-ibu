@extends('admin.layouts.app')

@section('title', 'Verifikasi Data Setoran')
@section('active', 'verifikasi-setoran')
@section('crumbs', 'Transaksi Sampah | Verifikasi Data Setoran')

@push('styles')
    @vite('resources/css/pages/verifikasi-setoran.css')
@endpush

@section('content')
<div class="verifikasi-page">

    @php
        $fmtHarga = fn($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');
        // kg & liter boleh desimal (2 digit), satuan lain (pcs, set, unit, gram) bilangan bulat
        $fmtJumlah = fn($n, $satuan = 'gram') => in_array($satuan, ['kg', 'liter'], true)
            ? number_format((float) $n, 2, ',', '.')
            : number_format((float) $n, 0, ',', '.');

        $kategoriMap = $kategoriSampah->keyBy(function ($k) {
            return $k->id_kategori ?? $k->id;
        });

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
                Verifikasi setoran sampah warga &amp; Tambahkan setoran baru secara langsung.
            </p>
        </div>

        <div class="hero-actions">
            <div class="stat-chip">
                <span class="stat-dot stat-dot--amber"></span>
                <div class="stat-text">
                    <span class="stat-num">{{ $setoran->where('status', 'pending')->count() }}</span>
                    <span class="stat-label">Menunggu</span>
                </div>
            </div>

            <div class="stat-chip">
                <span class="stat-dot stat-dot--green"></span>
                <div class="stat-text">
                    <span class="stat-num">{{ $setoran->where('status', 'approved')->count() }}</span>
                    <span class="stat-label">Disetujui</span>
                </div>
            </div>

            <div class="stat-chip">
                <span class="stat-dot stat-dot--red"></span>
                <div class="stat-text">
                    <span class="stat-num">{{ $setoran->where('status', 'rejected')->count() }}</span>
                    <span class="stat-label">Ditolak</span>
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
                <button class="fpill active" type="button" data-filter="all">Semua</button>
                <button class="fpill" type="button" data-filter="pending">Menunggu</button>
                <button class="fpill" type="button" data-filter="approved">Disetujui</button>
                <button class="fpill" type="button" data-filter="rejected">Ditolak</button>
            </div>
        </div>

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
                    @forelse ($setoran as $index => $item)
                        @php
                            $rowKey = $item->getKey();
                            $details = $detailMap[$rowKey] ?? collect();

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
                            $jumlahJenis = count($items);

                            $totalNilai = $item->total_nilai
                                ?? $details->sum(function ($d) {
                                    return (float) ($d->subtotal ?? 0);
                                });

                            $row = [
                                'id' => $rowKey,
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
                            <td class="col-no">
                                <span class="mono no-urut">{{ $loop->iteration }}</span>
                                <span class="sr-only">{{ $row['kode_transaksi'] }}</span>
                            </td>

                            <td>
                                <strong>{{ $row['warga']['nama'] }}</strong>
                                <br>
                                <span class="mono">{{ $row['warga']['nik'] }}</span>
                            </td>

                            <td>
                                @if ($namaList->isEmpty())
                                    -
                                @elseif ($jumlahJenis > 1)
                                    {{ $jumlahJenis }} jenis
                                    <span class="sr-only">{{ $namaList->implode(', ') }}</span>
                                @else
                                    {{ $namaList->first() }}
                                @endif
                            </td>

                            <td>
                                @if ($jumlahJenis > 1)
                                    {{ $jumlahJenis }} jenis
                                @else
                                    {{ $fmtJumlah($row['items'][0]['jumlah'] ?? 0, $row['items'][0]['satuan'] ?? 'gram') }}
                                    {{ $row['items'][0]['satuan'] ?? 'gram' }}
                                @endif
                            </td>

                            <td>
                                <strong class="saldo">
                                    Rp {{ number_format($row['total_nilai'], 0, ',', '.') }}
                                </strong>
                            </td>

                            <td>
                                @if ($tanggal)
                                    {{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                @if ($item->status === 'pending')
                                    <span class="status-badge status-badge--pending">Menunggu</span>
                                @elseif ($item->status === 'approved')
                                    <span class="status-badge status-badge--approved">Disetujui</span>
                                @else
                                    <span class="status-badge status-badge--rejected">Ditolak</span>
                                @endif
                            </td>

                            <td>
                                <div class="table-actions">
                                    <button class="icon-btn"
                                        title="Lihat Detail Setoran"
                                        aria-label="Lihat Detail Setoran"
                                        type="button"
                                        onclick="bukaVerifikasi({{ Js::from($row) }}, false)">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                            <circle cx="12" cy="12" r="2.5"></circle>
                                        </svg>
                                    </button>

                                    @if ($item->status === 'pending')
                                        <button class="icon-btn icon-btn--edit"
                                            title="Edit & Verifikasi Setoran"
                                            aria-label="Edit & Verifikasi Setoran"
                                            type="button"
                                            onclick="bukaVerifikasi({{ Js::from($row) }}, true)">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state-cell">
                                <div class="empty-icon">🧾</div>
                                <strong>Belum Ada Setoran Masuk</strong>
                                <p class="empty-desc">Belum ada warga yang mengisi form setoran.</p>
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

    <div class="toast-wrap" id="verifikasiToastWrap"></div>

    <select id="masterKategori" class="combo-hidden" tabindex="-1" aria-hidden="true">
        <option value="" data-harga="0" data-satuan="" disabled selected>
            -- Pilih Kategori Sampah --
        </option>

        @foreach ($kategoriSampah as $kategori)
            @php
                $harga = $kategori->hargaTerbaru->harga_satuan ?? null;
                $satuan = $normalisasiSatuan($kategori->satuan ?? 'gram');
                $namaKat = $kategori->nama_kategori ?? ($kategori->nama_lengkap ?? ($kategori->nama ?? '-'));
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

    <div class="modal-overlay" id="modalVerifikasi">
        <div class="modal-box modal-box--wide">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <span id="verifJudul">Verifikasi Setoran</span>
                </h3>

                <button class="modal-close" type="button" onclick="tutupModal('modalVerifikasi')">
                    &times;
                </button>
            </div>

            <form id="formVerifikasi" onsubmit="return prosesVerifikasi(event)">
                <div class="modal-body">
                    <input type="hidden" id="verifId">

                    <div class="detail-avatar-row">
                        <div class="detail-avatar" id="verifAvatar">-</div>
                        <div>
                            <div class="detail-nama" id="verifNama">-</div>
                            <div class="detail-nik mono" id="verifNik">-</div>
                            <div class="detail-nik mono" id="verifKode">-</div>
                            <span class="status-badge verif-status-spacer" id="verifStatus"></span>
                        </div>
                    </div>

                    <div class="form-group form-group--full" id="verifFotoBlock">
                        <label>Foto Bukti dari Warga</label>
                        <div class="photo-frame" id="verifFotoWrap">
                            <span id="verifFotoKosong">Tidak ada foto dilampirkan</span>
                            <img id="verifFoto" src="" alt="Foto bukti setoran" style="display:none;">
                        </div>
                    </div>

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

                        <span class="field-sub" id="verifHintEdit">
                            Sesuaikan jenis dan jumlah dengan hasil pengecekan ulang sebelum menyetujui.
                        </span>
                    </div>

                    <div class="form-grid">
                        <div class="form-group form-group--full reject-reason" id="verifAlasanBlock">
                            <label for="verifAlasan">Catatan Penolakan</label>
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
                        Simpan &amp; Setujui Saldo
                    </button>
                </div>
            </form>
        </div>
    </div>

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
                        <div class="form-group form-group--full">
                            <label for="cariWarga">Nama Warga</label>
                            <div class="combo" id="comboWarga">
                                <input type="text" id="cariWarga" class="combo-input"
                                    placeholder="Ketik nama atau NIK warga..." autocomplete="off">
                                <div class="combo-list" id="listWarga"></div>
                            </div>

                            <select id="tambahWarga" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="">-- Pilih Warga --</option>
                                @foreach ($wargaList as $w)
                                    <option value="{{ $w->id_warga }}">
                                        {{ $w->nama }} (NIK: {{ $w->nik }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="tambahTanggal">Tanggal Setoran</label>
                            <!-- INPUT TANGGAL DISEMBUNYIKAN & OTOMATIS TERISI OLEH JS -->
                            <input type="hidden" id="tambahTanggal" required>
                        </div>

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

                        <div class="form-group form-group--full">
                            <label for="tambahCatatan">Catatan (opsional)</label>
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

</div>
@endsection

@push('scripts')
    <script>
        window.__VERIFIKASI_DATA__ = {
            flashSuccess: @json(session('success'))
        };
    </script>
    @vite('resources/js/pages/verifikasi-setoran.js')
@endpush
