@extends('admin.layouts.app')

@section('title', 'Verifikasi Data Setoran')
@section('active', 'verifikasi-setoran')
@section('crumbs', 'Transaksi Sampah | Verifikasi Data Setoran')

@section('content')

    @php
        // Format harga per gram: buang nol desimal yang tidak perlu (5.00 -> 5, 5.50 -> 5,5)
        $fmtHarga = fn($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');

        // Cari nama kategori dari id_kategori (tidak bergantung pada relasi di model Setoran)
        $kategoriMap = $kategoriSampah->keyBy('id_kategori');
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
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"></path>
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
                <button class="fpill active" type="button" data-filter="all">Semua</button>
                <button class="fpill" type="button" data-filter="pending">Menunggu</button>
                <button class="fpill" type="button" data-filter="approved">Disetujui</button>
                <button class="fpill" type="button" data-filter="rejected">Ditolak</button>
            </div>

        </div>


        <!-- TABLE -->
        <div class="table-responsive">

            <table class="data-table" id="setoranTable">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Warga</th>
                        <th>Kategori Sampah</th>
                        <th>Berat</th>
                        <th>Nilai</th>
                        <th>Tanggal Setoran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($setoran as $index => $item)
                        @php
                            // Data yang dikirim ke modal (nama field = nama kolom tabel setoran)
                            // Kategori & berat ada di detail_setoran (bisa lebih dari satu baris)
                            $details = $detailMap[$item->getKey()] ?? collect();
                            $detail = $details->first();
                            $namaKategori = $details
                                ->map(fn($d) => optional($kategoriMap[$d->id_kategori] ?? null)->nama_lengkap)
                                ->filter()
                                ->implode(', ');

                            $row = [
                                'id' => $item->getKey(),
                                'status' => $item->status,
                                'warga' => [
                                    'nama' => $item->warga->nama ?? '-',
                                    'nik' => $item->warga->nik ?? '-',
                                ],
                                'id_kategori' => $detail->id_kategori ?? null,
                                'nama_kategori' => $namaKategori,
                                'total_berat' => $details->sum('berat_gram') ?: $item->total_berat ?? 0, // dalam GRAM
                                'total_nilai' => $item->total_nilai ?: $details->sum('subtotal'),
                                'catatan_admin' => $item->catatan_admin ?? '',
                                'foto_bukti' => $item->foto_sampah ?? null,
                            ];

                            $tanggal = $item->tanggal_setoran ?? $item->created_at;
                        @endphp

                        <tr data-status="{{ $item->status }}">

                            <!-- NO -->
                            <td>{{ $index + 1 }}</td>

                            <!-- WARGA -->
                            <td>
                                <strong>{{ $row['warga']['nama'] }}</strong><br>
                                <span class="mono">{{ $row['warga']['nik'] }}</span>
                            </td>

                            <!-- KATEGORI SAMPAH -->
                            <td>
                                <span class="kategori-tag">{{ $row['nama_kategori'] ?: '-' }}</span>
                            </td>

                            <!-- BERAT (gram) -->
                            <td>
                                <strong>{{ number_format($row['total_berat'], 0, ',', '.') }}</strong>
                                <span class="unit">gram</span>
                            </td>

                            <!-- NILAI -->
                            <td>
                                <strong>Rp {{ number_format($row['total_nilai'], 0, ',', '.') }}</strong>
                            </td>

                            <!-- TANGGAL SETORAN -->
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
                                    <span class="status-badge status-badge--pending">Menunggu</span>
                                @elseif($item->status === 'approved')
                                    <span class="status-badge status-badge--approved">Disetujui</span>
                                @else
                                    <span class="status-badge status-badge--rejected">Ditolak</span>
                                @endif
                            </td>

                            <!-- AKSI -->
                            <td>
                                <div class="table-actions">

                                    <button class="icon-btn {{ $item->status === 'pending' ? 'icon-btn--primary' : '' }}"
                                        title="{{ $item->status === 'pending' ? 'Verifikasi' : 'Lihat Detail' }}"
                                        type="button" onclick="bukaVerifikasi({{ Js::from($row) }})">

                                        @if ($item->status === 'pending')
                                            <svg viewBox="0 0 24 24">
                                                <path d="M9 11l3 3L22 4"></path>
                                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11">
                                                </path>
                                            </svg>
                                        @else
                                            <svg viewBox="0 0 24 24">
                                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                                <circle cx="12" cy="12" r="2.5"></circle>
                                            </svg>
                                        @endif

                                    </button>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                <div style="font-size: 30px; margin-bottom: 10px;">🧾</div>
                                <strong>Belum Ada Setoran Masuk</strong>
                                <p style="margin: 5px 0 0; color: #6b7280;">
                                    Belum ada warga yang mengisi form setoran.
                                </p>
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>


        <!-- FOOTER -->
        <div class="table-footer">
            <div class="table-info">
                Total setoran: <strong>{{ $setoran->count() }}</strong>
            </div>
        </div>

    </section>


    <!-- TOAST NOTIFIKASI -->
    <div class="toast-wrap" id="toastWrap"></div>


    <!-- ============================================= -->
    <!-- MODAL: VERIFIKASI SETORAN                      -->
    <!-- ============================================= -->
    <div class="modal-overlay" id="modalVerifikasi">
        <div class="modal-box">

            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                    <span id="verifJudul">Verifikasi Setoran</span>
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalVerifikasi')">&times;</button>
            </div>

            <form id="formVerifikasi" onsubmit="return prosesVerifikasi(event)">

                <div class="modal-body">

                    <input type="hidden" id="verifId">

                    <div class="detail-avatar-row">
                        <div class="detail-avatar" id="verifAvatar">-</div>
                        <div>
                            <div class="detail-nama" id="verifNama">-</div>
                            <div class="detail-nik mono" id="verifNik">-</div>
                        </div>
                    </div>

                    <div class="form-group form-group--full" id="verifFotoBlock">
                        <label>Foto Bukti dari Warga</label>
                        <div class="photo-frame" id="verifFotoWrap">
                            <span id="verifFotoKosong">Tidak ada foto dilampirkan</span>
                            <img id="verifFoto" src="" alt="Foto bukti setoran" style="display:none;">
                        </div>
                    </div>

                    <div class="form-grid">

                        <div class="form-group">
                            <label for="verifKategori">Kategori Sampah</label>
                            <select id="verifKategori" required>
                                <option value="" data-harga="0" disabled selected>-- Pilih Kategori Sampah --
                                </option>
                                @foreach ($kategoriSampah as $kategori)
                                    @php $harga = $kategori->hargaTerbaru->harga_per_gram ?? null; @endphp
                                    <option value="{{ $kategori->id_kategori }}" data-harga="{{ $harga ?? 0 }}">
                                        {{ $kategori->nama_lengkap }} —
                                        {{ $harga !== null ? 'Rp ' . $fmtHarga($harga) . '/gram' : 'belum ada harga' }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="field-sub" id="verifHargaInfo">Harga: -</span>
                        </div>

                        <div class="form-group">
                            <label for="verifBerat">Berat Sampah (gram)</label>
                            <input type="number" id="verifBerat" step="1" min="1" required>
                            <span class="field-sub">Sesuaikan dengan hasil timbang ulang jika berbeda.</span>
                        </div>

                        <div class="form-group form-group--full">
                            <div class="estimate-box">
                                <span class="lab">Estimasi nilai dari setoran ini</span>
                                <span class="val" id="verifEstimasi">Rp 0</span>
                            </div>
                        </div>

                        <div class="form-group form-group--full reject-reason" id="verifAlasanBlock">
                            <label for="verifAlasan">Catatan Penolakan</label>
                            <textarea id="verifAlasan" rows="3"
                                placeholder="Contoh: foto tidak jelas, berat tidak sesuai foto, kategori tidak cocok..."></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-foot" id="verifFootActions">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalVerifikasi')">Tutup</button>
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


    <!-- ============================================= -->
    <!-- MODAL: TAMBAH SETORAN (input manual oleh admin) -->
    <!-- ============================================= -->
    <div class="modal-overlay" id="modalTambah">
        <div class="modal-box">

            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Setoran
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalTambah')">&times;</button>
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
                            <label for="cariKategori">Kategori Sampah</label>

                            <div class="combo" id="comboKategori">
                                <input type="text" id="cariKategori" class="combo-input"
                                    placeholder="Ketik nama kategori sampah..." autocomplete="off">
                                <div class="combo-list" id="listKategori"></div>
                            </div>

                            <select id="tambahKategori" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="" data-harga="0">-- Pilih Kategori Sampah --</option>
                                @foreach ($kategoriSampah as $kategori)
                                    @php $harga = $kategori->hargaTerbaru->harga_per_gram ?? null; @endphp
                                    <option value="{{ $kategori->id_kategori }}" data-harga="{{ $harga ?? 0 }}">
                                        {{ $kategori->nama_lengkap }} —
                                        {{ $harga !== null ? 'Rp ' . $fmtHarga($harga) . '/gram' : 'belum ada harga' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tambahBerat">Berat Sampah (gram)</label>
                            <input type="number" id="tambahBerat" step="1" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="tambahTanggal">Tanggal Setoran</label>
                            <input type="date" id="tambahTanggal" required>
                        </div>

                        <div class="form-group form-group--full">
                            <div class="estimate-box">
                                <span class="lab">Estimasi nilai</span>
                                <span class="val" id="tambahEstimasi">Rp 0</span>
                            </div>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="tambahCatatan">Catatan (opsional)</label>
                            <textarea id="tambahCatatan" rows="2" placeholder="Contoh: setoran langsung di titik kumpul"></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalTambah')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan &amp; Proses Saldo</button>
                </div>

            </form>

        </div>
    </div>


    <style>
        /* =========================================================
           TEMA BERSAMA - disalin dari Data Warga supaya identik
           ========================================================= */

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
            border-color: var(--primary);
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
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

        .mono {
            font-family: monospace;
            font-size: 12px;
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
        }

        .icon-btn svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .icon-btn:hover {
            background: #f3f4f6;
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
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            background: var(--surface, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 14px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
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

        .modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid #edf0f2;
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

        .detail-avatar-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 18px;
            margin-bottom: 18px;
            border-bottom: 1px solid #edf0f2;
        }

        .detail-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #eef2ff;
            color: #4338ca;
            font-weight: 700;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .detail-nama {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }

        .detail-nik {
            margin-top: 3px;
            color: #6b7280;
        }

        .detail-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #9ca3af;
            display: block;
        }

        .detail-value {
            font-size: 14px;
            color: #1f2937;
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
            color: #374151;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            border: 1px solid #dfe3e8;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            width: 100%;
            background: #fff;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary, #4338ca);
        }

        .form-group textarea {
            resize: vertical;
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

        @media (max-width: 560px) {
            .form-grid {
                grid-template-columns: 1fr;
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

        /* =========================================================
           TAMBAHAN KHUSUS VERIFIKASI SETORAN
           ========================================================= */

        .hero-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .stat-chip {
            background: var(--surface, #fff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 12px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 130px;
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

        .stat-num {
            font-size: 18px;
            font-weight: 800;
            line-height: 1;
            color: #1f2937;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7280;
        }

        .filter-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .fpill {
            border: 1px solid #dfe3e8;
            background: transparent;
            color: #6b7280;
            padding: 8px 14px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }

        .fpill.active {
            background: #16a34a;
            border-color: #16a34a;
            color: #fff;
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

        .unit {
            color: #9ca3af;
            font-size: 12px;
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

        .icon-btn--primary {
            border-color: #16a34a;
            color: #16a34a;
        }

        .icon-btn--primary:hover {
            background: #e9f9ee;
        }

        .photo-frame {
            border: 1px dashed #dfe3e8;
            border-radius: 12px;
            min-height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 12.5px;
            background: #f8fafc;
            overflow: hidden;
        }

        .photo-frame img {
            width: 100%;
            max-height: 240px;
            object-fit: cover;
        }

        .field-sub {
            font-size: 11.5px;
            color: #9ca3af;
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

        .reject-reason {
            display: none;
        }

        .reject-reason.open {
            display: flex;
        }

        @media (max-width: 560px) {
            .hero-actions {
                width: 100%;
            }

            .stat-chip {
                flex: 1;
            }
        }

        /* =========================================================
           COMBOBOX (SEARCHABLE SELECT) - sama seperti Pencairan Saldo
           ========================================================= */

        .combo {
            position: relative;
        }

        .combo-hidden {
            display: none !important;
        }

        .combo-input {
            width: 100%;
            border: 1px solid #dfe3e8;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            background: #fff;
        }

        .combo-input:focus {
            border-color: var(--primary, #4338ca);
        }

        .combo-list {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            max-height: 220px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, .12);
            z-index: 20;
        }

        .combo-list.show {
            display: block;
        }

        .combo-item {
            padding: 9px 12px;
            font-size: 13px;
            color: #374151;
            cursor: pointer;
        }

        .combo-item:hover,
        .combo-item.is-active {
            background: #f3f4f6;
        }

        .combo-empty {
            padding: 12px;
            font-size: 12.5px;
            color: #9ca3af;
            text-align: center;
        }
    </style>


    <script>
        let verifItem = null;

        document.addEventListener('DOMContentLoaded', function() {

            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif

            const searchInput = document.getElementById('searchSetoran');
            const table = document.getElementById('setoranTable');

            function applyFilters() {
                const keyword = (searchInput?.value || '').toLowerCase().trim();
                const activePill = document.querySelector('.fpill.active');
                const statusFilter = activePill ? activePill.dataset.filter : 'all';

                table.querySelectorAll('tbody tr[data-status]').forEach(function(row) {
                    const matchSearch = !keyword || row.textContent.toLowerCase().includes(keyword);
                    const matchStatus = statusFilter === 'all' || row.dataset.status === statusFilter;
                    row.style.display = (matchSearch && matchStatus) ? '' : 'none';
                });
            }

            if (searchInput) {
                searchInput.addEventListener('keyup', applyFilters);
            }

            document.querySelectorAll('.fpill').forEach(function(pill) {
                pill.addEventListener('click', function() {
                    document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                    applyFilters();
                });
            });

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

            document.getElementById('verifKategori')?.addEventListener('change', updateEstimasi);
            document.getElementById('verifBerat')?.addEventListener('input', updateEstimasi);

            document.getElementById('tambahBerat')?.addEventListener('input', updateEstimasiTambah);

            // Combobox pada form Tambah Setoran (sama seperti Tambah Pencairan)
            initCombo({
                comboId: 'comboWarga',
                selectId: 'tambahWarga',
                inputId: 'cariWarga',
                listId: 'listWarga'
            });

            initCombo({
                comboId: 'comboKategori',
                selectId: 'tambahKategori',
                inputId: 'cariKategori',
                listId: 'listKategori',
                onChange: updateEstimasiTambah
            });

        });


        /*
        |--------------------------------------------------------------------------
        | Combobox (select + pencarian) - dipakai untuk warga & kategori
        |--------------------------------------------------------------------------
        */
        function initCombo(cfg) {
            const select = document.getElementById(cfg.selectId);
            const input = document.getElementById(cfg.inputId);
            const list = document.getElementById(cfg.listId);
            const onChange = cfg.onChange || function() {};
            if (!select || !input || !list) return;

            const data = Array.from(select.options)
                .filter(function(o) {
                    return o.value !== '';
                })
                .map(function(o) {
                    return {
                        value: o.value,
                        label: o.textContent.replace(/\s+/g, ' ').trim()
                    };
                });

            let aktif = -1;

            function escapeHtml(s) {
                return String(s).replace(/[&<>"']/g, function(c) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    } [c];
                });
            }

            function render(keyword) {
                const k = (keyword || '').toLowerCase().trim();
                const hasil = data.filter(function(d) {
                    return d.label.toLowerCase().includes(k);
                });

                aktif = -1;

                list.innerHTML = hasil.length ?
                    hasil.map(function(d) {
                        return '<div class="combo-item" data-value="' + d.value + '">' +
                            escapeHtml(d.label) + '</div>';
                    }).join('') :
                    '<div class="combo-empty">Data tidak ditemukan</div>';
            }

            function pilih(value) {
                const d = data.find(function(x) {
                    return x.value === String(value);
                });
                if (!d) return;

                select.value = d.value;
                input.value = d.label;
                list.classList.remove('show');
                onChange();
            }

            function sorot(arah) {
                const items = list.querySelectorAll('.combo-item');
                if (!items.length) return;

                aktif = (aktif + arah + items.length) % items.length;
                items.forEach(function(el, i) {
                    el.classList.toggle('is-active', i === aktif);
                });
                items[aktif].scrollIntoView({
                    block: 'nearest'
                });
            }

            input.addEventListener('focus', function() {
                render(select.value ? '' : this.value);
                list.classList.add('show');
            });

            input.addEventListener('input', function() {
                select.value = '';
                onChange();
                render(this.value);
                list.classList.add('show');
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!list.classList.contains('show')) {
                        render(this.value);
                        list.classList.add('show');
                    }
                    sorot(1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    sorot(-1);
                } else if (e.key === 'Enter') {
                    const items = list.querySelectorAll('.combo-item');
                    if (list.classList.contains('show') && aktif > -1 && items[aktif]) {
                        e.preventDefault();
                        pilih(items[aktif].dataset.value);
                    }
                } else if (e.key === 'Escape') {
                    // Tutup daftar dulu, modal tetap terbuka
                    if (list.classList.contains('show')) {
                        e.stopPropagation();
                        list.classList.remove('show');
                    }
                }
            });

            list.addEventListener('mousedown', function(e) {
                const item = e.target.closest('.combo-item');
                if (!item) return;
                e.preventDefault();
                pilih(item.dataset.value);
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('#' + cfg.comboId)) list.classList.remove('show');
            });
        }

        function resetCombo(selectId, inputId, listId) {
            const select = document.getElementById(selectId);
            const input = document.getElementById(inputId);
            const list = document.getElementById(listId);

            if (select) select.value = '';
            if (input) input.value = '';
            if (list) list.classList.remove('show');
        }


        /* ---------- Helper ---------- */

        function bukaModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function tutupModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function formatRupiah(angka) {
            return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID', {
                maximumFractionDigits: 2
            });
        }

        function formatHarga(n) {
            return 'Rp ' + (Number(n) || 0).toLocaleString('id-ID', {
                maximumFractionDigits: 2
            }) + ' / gram';
        }

        // Harga per gram dari atribut data-harga pada option yang dipilih
        function hargaDariSelect(select) {
            if (!select) return 0;
            return parseFloat(select.options[select.selectedIndex]?.dataset.harga || 0) || 0;
        }

        // Pilih option kategori berdasarkan id_kategori
        function pilihKategori(select, idKategori) {
            select.value = idKategori ? String(idKategori) : '';
            if (select.selectedIndex === -1) select.value = '';
        }

        function showToast(title, text, type = 'success') {

            const wrap = document.getElementById('toastWrap');

            const toast = document.createElement('div');
            toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');

            const iconPath = type === 'error' ?
                '<path d="M18 6 6 18M6 6l12 12"></path>' :
                '<path d="M20 6 9 17l-5-5"></path>';

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
        | Buka modal verifikasi (tombol mata / centang)
        |--------------------------------------------------------------------------
        */

        function bukaVerifikasi(item) {

            verifItem = item;

            const nama = item.warga?.nama || '-';

            document.getElementById('verifAvatar').textContent = nama.trim().charAt(0).toUpperCase();
            document.getElementById('verifNama').textContent = nama;
            document.getElementById('verifNik').textContent = item.warga?.nik || '-';
            document.getElementById('verifJudul').textContent =
                item.status === 'pending' ? 'Verifikasi Setoran' : 'Detail Setoran';

            document.getElementById('verifId').value = item.id;
            pilihKategori(document.getElementById('verifKategori'), item.id_kategori);
            document.getElementById('verifBerat').value = item.total_berat; // dalam gram
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

            const bisaDiedit = item.status === 'pending';
            document.getElementById('verifKategori').disabled = !bisaDiedit;
            document.getElementById('verifBerat').disabled = !bisaDiedit;
            document.getElementById('btnTolakSetoran').style.display = bisaDiedit ? 'inline-flex' : 'none';
            document.getElementById('btnSetujuiSetoran').style.display = bisaDiedit ? 'inline-flex' : 'none';

            updateEstimasi();
            bukaModal('modalVerifikasi');
        }

        function updateEstimasi() {
            const select = document.getElementById('verifKategori');
            const harga = hargaDariSelect(select); // Rp per gram
            const berat = parseFloat(document.getElementById('verifBerat').value) || 0; // gram
            const info = document.getElementById('verifHargaInfo');
            const sudahDisetujui = verifItem && verifItem.status === 'approved';

            if (sudahDisetujui) {
                info.textContent = 'Nilai dihitung saat setoran disetujui.';
                document.getElementById('verifEstimasi').textContent = formatRupiah(verifItem.total_nilai);
                return;
            }

            info.textContent = harga > 0 ?
                'Harga aktif: ' + formatHarga(harga) :
                'Kategori ini belum memiliki harga aktif.';

            document.getElementById('verifEstimasi').textContent = formatRupiah(harga * berat);
        }


        /*
        |--------------------------------------------------------------------------
        | Setujui setoran -> hitung nilai & simpan
        |--------------------------------------------------------------------------
        */

        function prosesVerifikasi(event) {
            event.preventDefault();

            const id = document.getElementById('verifId').value;

            const payload = {
                id_kategori: document.getElementById('verifKategori').value,
                berat_gram: document.getElementById('verifBerat').value,
            };

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
        | Tambah setoran manual oleh admin
        |--------------------------------------------------------------------------
        */

        function bukaTambahSetoran() {

            document.getElementById('formTambahSetoran').reset();

            resetCombo('tambahWarga', 'cariWarga', 'listWarga');
            resetCombo('tambahKategori', 'cariKategori', 'listKategori');

            const today = new Date().toISOString().substring(0, 10);
            document.getElementById('tambahTanggal').value = today;

            updateEstimasiTambah();
            bukaModal('modalTambah');
        }

        function updateEstimasiTambah() {
            const select = document.getElementById('tambahKategori');
            const harga = hargaDariSelect(select); // Rp per gram
            const berat = parseFloat(document.getElementById('tambahBerat').value) || 0; // gram

            document.getElementById('tambahEstimasi').textContent = formatRupiah(harga * berat);
        }

        function simpanSetoranBaru(event) {
            event.preventDefault();

            const payload = {
                id_warga: document.getElementById('tambahWarga').value,
                id_kategori: document.getElementById('tambahKategori').value,
                berat_gram: document.getElementById('tambahBerat').value,
                tanggal_setoran: document.getElementById('tambahTanggal').value,
                catatan_admin: document.getElementById('tambahCatatan')?.value || null,
            };

            if (!payload.id_warga) {
                showToast('Gagal menyimpan', 'Silakan pilih warga terlebih dahulu.', 'error');
                document.getElementById('cariWarga').focus();
                return false;
            }

            if (!payload.id_kategori) {
                showToast('Gagal menyimpan', 'Silakan pilih kategori sampah terlebih dahulu.', 'error');
                document.getElementById('cariKategori').focus();
                return false;
            }

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
        | Tolak setoran -> minta catatan dulu sebelum kirim
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
                    body: JSON.stringify({
                        catatan_admin: catatan
                    })
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
