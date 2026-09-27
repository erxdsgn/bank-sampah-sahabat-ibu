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
    | MAP KATEGORI
    |--------------------------------------------------------------------------
    */
        $kategoriMap = $kategoriSampah->keyBy('id_kategori');

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
                        <th>No</th>
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
                        | DETAIL SETORAN
                        |--------------------------------------------------------------------------
                        */
                            $details = $detailMap[$item->getKey()] ?? collect();

                            $detail = $details->first();

                            /*
                        |--------------------------------------------------------------------------
                        | KATEGORI
                        |--------------------------------------------------------------------------
                        */
                            $namaKategori = $details
                                ->map(fn($d) => optional($kategoriMap[$d->id_kategori] ?? null)->nama_lengkap)
                                ->filter()
                                ->implode(', ');

                            /*
                        |--------------------------------------------------------------------------
                        | KATEGORI UTAMA DARI DETAIL
                        |--------------------------------------------------------------------------
                        */
                            $kategoriDetail = $detail ? $kategoriMap[$detail->id_kategori] ?? null : null;

                            /*
                        |--------------------------------------------------------------------------
                        | SATUAN
                        |
                        | Prioritas:
                        | 1. satuan pada detail_setoran jika tersedia
                        | 2. satuan kategori
                        | 3. gram sebagai fallback data lama
                        |--------------------------------------------------------------------------
                        */
                            $satuanRow = $normalisasiSatuan(
                                $detail->satuan ?? (optional($kategoriDetail)->satuan ?? 'gram'),
                            );

                            /*
                        |--------------------------------------------------------------------------
                        | JUMLAH
                        |
                        | Jika database sudah mempunyai jumlah_satuan/jumlah,
                        | gunakan itu.
                        |
                        | Jika belum, gunakan berat_gram untuk data lama.
                        |--------------------------------------------------------------------------
                        */
                            $jumlahDetail = null;

                            if ($detail) {
                                $jumlahDetail = $detail->jumlah_satuan ?? ($detail->jumlah ?? null);
                            }

                            /*
                        |--------------------------------------------------------------------------
                        | FALLBACK DATA LAMA
                        |--------------------------------------------------------------------------
                        */
                            if ($jumlahDetail === null) {
                                $jumlahDetail = $details->sum(function ($d) {
                                    return (float) ($d->berat_gram ?? 0);
                                });
                            }

                            /*
                        |--------------------------------------------------------------------------
                        | Jika tidak ada detail, ambil dari setoran
                        |--------------------------------------------------------------------------
                        */
                            if (!$jumlahDetail) {
                                $jumlahDetail = $item->jumlah_satuan ?? ($item->jumlah ?? ($item->total_berat ?? 0));
                            }

                            /*
                        |--------------------------------------------------------------------------
                        | TOTAL NILAI
                        |--------------------------------------------------------------------------
                        */
                            $totalNilai =
                                $item->total_nilai ??
                                $details->sum(function ($d) {
                                    return (float) ($d->subtotal ?? 0);
                                });

                            /*
                        |--------------------------------------------------------------------------
                        | DATA YANG DIKIRIM KE MODAL
                        |--------------------------------------------------------------------------
                        */
                            $row = [
                                'id' => $item->getKey(),

                                'status' => $item->status,

                                'warga' => [
                                    'nama' => $item->warga->nama ?? '-',
                                    'nik' => $item->warga->nik ?? '-',
                                ],

                                'id_kategori' => $detail->id_kategori ?? null,

                                'nama_kategori' => $namaKategori,

                                'jumlah' => (float) $jumlahDetail,

                                /*
                            | Tetap dikirim agar kompatibel dengan data lama.
                            */
                                'total_berat' => (float) $jumlahDetail,

                                'satuan' => $satuanRow,

                                'total_nilai' => (float) $totalNilai,

                                'catatan_admin' => $item->catatan_admin ?? '',

                                'foto_bukti' => $item->foto_sampah ?? null,
                            ];

                            $tanggal = $item->tanggal_setoran ?? $item->created_at;
                        @endphp


                        <tr data-status="{{ $item->status }}">

                            <!-- NO -->
                            <td>
                                {{ $index + 1 }}
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
                                <span class="kategori-tag">
                                    {{ $row['nama_kategori'] ?: '-' }}
                                </span>
                            </td>


                            <!-- JUMLAH -->
                            <td>

                                <strong>
                                    {{ $fmtJumlah($row['jumlah']) }}
                                </strong>

                                <span class="unit">
                                    {{ $simbolSatuan($row['satuan']) }}
                                </span>

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

        </div>


        <!-- FOOTER -->
        <div class="table-footer">

            <div class="table-info">
                Total setoran:
                <strong>{{ $setoran->count() }}</strong>
            </div>

        </div>

    </section>


    <!-- TOAST -->
    <div class="toast-wrap" id="toastWrap"></div>


    <!-- ========================================================= -->
    <!-- MODAL VERIFIKASI -->
    <!-- ========================================================= -->

    <div class="modal-overlay" id="modalVerifikasi">

        <div class="modal-box">

            <div class="modal-head">

                <h3 class="modal-title">

                    <svg viewBox="0 0 24 24">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
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


                    <div class="form-grid">


                        <!-- KATEGORI -->
                        <div class="form-group">

                            <label for="verifKategori">
                                Kategori Sampah
                            </label>

                            <select id="verifKategori" required>

                                <option value="" data-harga="0" data-satuan="" disabled selected>
                                    -- Pilih Kategori Sampah --
                                </option>

                                @foreach ($kategoriSampah as $kategori)
                                    @php
                                        $harga = $kategori->hargaTerbaru->harga_satuan ?? null;

                                        $satuan = $normalisasiSatuan($kategori->satuan ?? 'gram');
                                    @endphp

                                    <option value="{{ $kategori->id_kategori }}" data-harga="{{ $harga ?? 0 }}"
                                        data-satuan="{{ $satuan }}">

                                        {{ $kategori->nama_lengkap }}
                                        —

                                        @if ($harga !== null)
                                            Rp {{ $fmtHarga($harga) }}
                                            /
                                            {{ $simbolSatuan($satuan) }}
                                        @else
                                            belum ada harga
                                        @endif

                                    </option>
                                @endforeach

                            </select>


                            <span class="field-sub" id="verifHargaInfo">
                                Harga: -
                            </span>

                        </div>


                        <!-- JUMLAH -->
                        <div class="form-group">

                            <label for="verifBerat">
                                Jumlah
                            </label>

                            <div style="display:flex; gap:8px;">

                                <input type="number" id="verifBerat" step="0.001" min="0.001" required
                                    style="flex:1;">

                                <input type="text" id="verifSatuan" value="-" readonly style="width:100px;">

                            </div>


                            <span class="field-sub" id="verifSatuanInfo">
                                Satuan akan mengikuti kategori sampah.
                            </span>

                            <span class="field-sub">
                                Sesuaikan dengan hasil pengecekan ulang jika berbeda.
                            </span>

                        </div>


                        <!-- ESTIMASI -->
                        <div class="form-group form-group--full">

                            <div class="estimate-box">

                                <span class="lab">
                                    Estimasi nilai dari setoran ini
                                </span>

                                <span class="val" id="verifEstimasi">
                                    Rp 0
                                </span>

                            </div>

                        </div>


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

        <div class="modal-box">

            <div class="modal-head">

                <h3 class="modal-title">

                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
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


                        <!-- KATEGORI -->
                        <div class="form-group form-group--full">

                            <label for="cariKategori">
                                Kategori Sampah
                            </label>

                            <div class="combo" id="comboKategori">
                                <input type="text" id="cariKategori" class="combo-input"
                                    placeholder="Ketik nama kategori sampah..." autocomplete="off">

                                <div class="combo-list" id="listKategori">
                                </div>
                            </div>

                            {{-- Sembunyikan select asli agar hanya UI custom combo yang terlihat --}}
                            <select id="tambahKategori" name="kategori_id" style="display: none;" required>

                                <option value="" data-harga="0" data-satuan="" disabled selected>
                                    -- Pilih Kategori Sampah --
                                </option>

                                @foreach ($kategoriSampah as $kategori)
                                    @php
                                        $harga = $kategori->hargaTerbaru->harga_satuan ?? null;
                                        $satuan = $normalisasiSatuan($kategori->satuan ?? 'gram');
                                    @endphp

                                    <option value="{{ $kategori->id_kategori }}" data-harga="{{ $harga ?? 0 }}"
                                        data-satuan="{{ $satuan }}">

                                        {{ $kategori->nama_lengkap }} —
                                        @if ($harga !== null)
                                            Rp {{ $fmtHarga($harga) }} / {{ $simbolSatuan($satuan) }}
                                        @else
                                            belum ada harga
                                        @endif
                                    </option>
                                @endforeach

                            </select>

                        </div>


                        <!-- JUMLAH -->
                        <div class="form-group">

                            <label for="tambahBerat">
                                Jumlah
                            </label>

                            <div style="display:flex; gap:8px;">

                                <input type="number" id="tambahBerat" step="0.001" min="0.001" required
                                    style="flex:1;">

                                <input type="text" id="tambahSatuan" value="-" readonly style="width:100px;">

                            </div>

                            <span class="field-sub">
                                Satuan otomatis mengikuti kategori sampah.
                            </span>

                        </div>


                        <!-- TANGGAL -->
                        <div class="form-group">

                            <label for="tambahTanggal">
                                Tanggal Setoran
                            </label>

                            <input type="date" id="tambahTanggal" required>

                        </div>


                        <!-- ESTIMASI -->
                        <div class="form-group form-group--full">

                            <div class="estimate-box">

                                <span class="lab">
                                    Estimasi nilai
                                </span>

                                <span class="val" id="tambahEstimasi">
                                    Rp 0
                                </span>

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
            background: #16a34a;
            border-color: #16a34a;
            color: #fff;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
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
            padding: 16px 24px;
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
    </style>


    <script>
        let verifItem = null;


        /*
        |--------------------------------------------------------------------------
        | DOM READY
        |--------------------------------------------------------------------------
        */

        document.addEventListener('DOMContentLoaded', function() {

            @if (session('success'))
                showToast(
                    'Berhasil',
                    @json(session('success'))
                );
            @endif


            const searchInput =
                document.getElementById('searchSetoran');

            const table =
                document.getElementById('setoranTable');


            /*
            |--------------------------------------------------------------------------
            | FILTER TABLE
            |--------------------------------------------------------------------------
            */

            function applyFilters() {

                const keyword =
                    (searchInput?.value || '')
                    .toLowerCase()
                    .trim();

                const activePill =
                    document.querySelector('.fpill.active');

                const statusFilter =
                    activePill ?
                    activePill.dataset.filter :
                    'all';


                table
                    .querySelectorAll('tbody tr[data-status]')
                    .forEach(function(row) {

                        const matchSearch = !keyword ||
                            row.textContent
                            .toLowerCase()
                            .includes(keyword);

                        const matchStatus =
                            statusFilter === 'all' ||
                            row.dataset.status === statusFilter;


                        row.style.display =
                            (matchSearch && matchStatus) ?
                            '' :
                            'none';

                    });

            }


            if (searchInput) {
                searchInput.addEventListener(
                    'keyup',
                    applyFilters
                );
            }


            document
                .querySelectorAll('.fpill')
                .forEach(function(pill) {

                    pill.addEventListener(
                        'click',
                        function() {

                            document
                                .querySelectorAll('.fpill')
                                .forEach(
                                    p => p.classList.remove('active')
                                );

                            pill.classList.add('active');

                            applyFilters();

                        }
                    );

                });


            /*
            |--------------------------------------------------------------------------
            | CLOSE MODAL
            |--------------------------------------------------------------------------
            */

            document
                .querySelectorAll('.modal-overlay')
                .forEach(function(overlay) {

                    overlay.addEventListener(
                        'click',
                        function(e) {

                            if (e.target === overlay) {
                                overlay.classList.remove('active');
                            }

                        }
                    );

                });


            document.addEventListener(
                'keydown',
                function(e) {

                    if (e.key === 'Escape') {

                        document
                            .querySelectorAll('.modal-overlay.active')
                            .forEach(function(overlay) {
                                overlay.classList.remove('active');
                            });

                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | VERIFIKASI
            |--------------------------------------------------------------------------
            */

            document
                .getElementById('verifKategori')
                ?.addEventListener(
                    'change',
                    updateEstimasi
                );


            document
                .getElementById('verifBerat')
                ?.addEventListener(
                    'input',
                    updateEstimasi
                );


            /*
            |--------------------------------------------------------------------------
            | TAMBAH SETORAN
            |--------------------------------------------------------------------------
            */

            document
                .getElementById('tambahBerat')
                ?.addEventListener(
                    'input',
                    updateEstimasiTambah
                );


            /*
            |--------------------------------------------------------------------------
            | COMBO WARGA
            |--------------------------------------------------------------------------
            */

            initCombo({
                comboId: 'comboWarga',
                selectId: 'tambahWarga',
                inputId: 'cariWarga',
                listId: 'listWarga'
            });


            /*
            |--------------------------------------------------------------------------
            | COMBO KATEGORI
            |--------------------------------------------------------------------------
            */

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
        | COMBOBOX
        |--------------------------------------------------------------------------
        */

        function initCombo(cfg) {

            const select =
                document.getElementById(cfg.selectId);

            const input =
                document.getElementById(cfg.inputId);

            const list =
                document.getElementById(cfg.listId);

            const onChange =
                cfg.onChange || function() {};


            if (!select || !input || !list) {
                return;
            }


            const data =
                Array
                .from(select.options)
                .filter(function(o) {
                    return o.value !== '';
                })
                .map(function(o) {

                    return {
                        value: o.value,
                        label: o.textContent
                            .replace(/\s+/g, ' ')
                            .trim()
                    };

                });


            let aktif = -1;


            function escapeHtml(s) {

                return String(s).replace(
                    /[&<>"']/g,
                    function(c) {

                        return {
                            '&': '&amp;',
                            '<': '&lt;',
                            '>': '&gt;',
                            '"': '&quot;',
                            "'": '&#39;'
                        } [c];

                    }
                );

            }


            function render(keyword) {

                const k =
                    (keyword || '')
                    .toLowerCase()
                    .trim();


                const hasil =
                    data.filter(function(d) {

                        return d.label
                            .toLowerCase()
                            .includes(k);

                    });


                aktif = -1;


                list.innerHTML =
                    hasil.length

                    ?
                    hasil
                    .map(function(d) {

                        return `
                                <div
                                    class="combo-item"
                                    data-value="${d.value}">
                                    ${escapeHtml(d.label)}
                                </div>
                            `;

                    })
                    .join('')

                    :
                    '<div class="combo-empty">Data tidak ditemukan</div>';

            }


            function pilih(value) {

                const d =
                    data.find(function(x) {
                        return x.value === String(value);
                    });


                if (!d) {
                    return;
                }


                select.value = d.value;

                input.value = d.label;

                list.classList.remove('show');

                onChange();

            }


            function sorot(arah) {

                const items =
                    list.querySelectorAll('.combo-item');


                if (!items.length) {
                    return;
                }


                aktif =
                    (aktif + arah + items.length) %
                    items.length;


                items.forEach(function(el, i) {

                    el.classList.toggle(
                        'is-active',
                        i === aktif
                    );

                });


                items[aktif].scrollIntoView({
                    block: 'nearest'
                });

            }


            input.addEventListener(
                'focus',
                function() {

                    render(
                        select.value ?
                        '' :
                        this.value
                    );

                    list.classList.add('show');

                }
            );


            input.addEventListener(
                'input',
                function() {

                    select.value = '';

                    onChange();

                    render(this.value);

                    list.classList.add('show');

                }
            );


            input.addEventListener(
                'keydown',
                function(e) {

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

                        const items =
                            list.querySelectorAll('.combo-item');


                        if (
                            list.classList.contains('show') &&
                            aktif > -1 &&
                            items[aktif]
                        ) {

                            e.preventDefault();

                            pilih(
                                items[aktif].dataset.value
                            );

                        }

                    } else if (e.key === 'Escape') {

                        if (list.classList.contains('show')) {

                            e.stopPropagation();

                            list.classList.remove('show');

                        }

                    }

                }
            );


            list.addEventListener(
                'mousedown',
                function(e) {

                    const item =
                        e.target.closest('.combo-item');


                    if (!item) {
                        return;
                    }


                    e.preventDefault();

                    pilih(item.dataset.value);

                }
            );


            document.addEventListener(
                'click',
                function(e) {

                    if (!e.target.closest('#' + cfg.comboId)) {
                        list.classList.remove('show');
                    }

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | RESET COMBO
        |--------------------------------------------------------------------------
        */

        function resetCombo(
            selectId,
            inputId,
            listId
        ) {

            const select =
                document.getElementById(selectId);

            const input =
                document.getElementById(inputId);

            const list =
                document.getElementById(listId);


            if (select) {
                select.value = '';
            }

            if (input) {
                input.value = '';
            }

            if (list) {
                list.classList.remove('show');
            }

        }


        /*
        |--------------------------------------------------------------------------
        | MODAL
        |--------------------------------------------------------------------------
        */

        function bukaModal(id) {

            document
                .getElementById(id)
                .classList.add('active');

        }


        function tutupModal(id) {

            document
                .getElementById(id)
                .classList.remove('active');

        }


        /*
        |--------------------------------------------------------------------------
        | FORMAT RUPIAH
        |--------------------------------------------------------------------------
        */

        function formatRupiah(angka) {

            return 'Rp ' +
                (Number(angka) || 0)
                .toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                });

        }


        /*
        |--------------------------------------------------------------------------
        | FORMAT HARGA + SATUAN
        |--------------------------------------------------------------------------
        */

        function formatHarga(
            n,
            satuan = ''
        ) {

            const nilai =
                Number(n) || 0;


            return 'Rp ' +
                nilai.toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                }) +
                (
                    satuan ?
                    ' / ' + satuan :
                    ''
                );

        }


        /*
        |--------------------------------------------------------------------------
        | AMBIL HARGA DARI OPTION
        |--------------------------------------------------------------------------
        */

        function hargaDariSelect(select) {

            if (
                !select ||
                select.selectedIndex < 0
            ) {
                return 0;
            }


            return Number(
                select
                .options[select.selectedIndex]
                ?.dataset.harga || 0
            );

        }


        /*
        |--------------------------------------------------------------------------
        | AMBIL SATUAN DARI OPTION
        |--------------------------------------------------------------------------
        */

        function satuanDariSelect(select) {

            if (
                !select ||
                select.selectedIndex < 0
            ) {
                return '';
            }


            return select
                .options[select.selectedIndex]
                ?.dataset.satuan || '';

        }


        /*
        |--------------------------------------------------------------------------
        | PILIH KATEGORI
        |--------------------------------------------------------------------------
        */

        function pilihKategori(
            select,
            idKategori
        ) {

            select.value =
                idKategori ?
                String(idKategori) :
                '';


            if (select.selectedIndex === -1) {
                select.value = '';
            }

        }


        /*
        |--------------------------------------------------------------------------
        | TOAST
        |--------------------------------------------------------------------------
        */

        function showToast(
            title,
            text,
            type = 'success'
        ) {

            const wrap =
                document.getElementById('toastWrap');


            const toast =
                document.createElement('div');


            toast.className =
                'toast' +
                (
                    type === 'error' ?
                    ' toast--error' :
                    ''
                );


            const iconPath =
                type === 'error'

                ?
                '<path d="M18 6 6 18M6 6l12 12"></path>'

                :
                '<path d="M20 6 9 17l-5-5"></path>';


            toast.innerHTML = `
            <div class="toast-icon">
                <svg viewBox="0 0 24 24">
                    ${iconPath}
                </svg>
            </div>

            <div class="toast-body">
                <p class="toast-title">
                    ${title}
                </p>

                <p class="toast-text">
                    ${text}
                </p>
            </div>

            <button
                class="toast-close"
                type="button"
                aria-label="Tutup">
                &times;
            </button>
        `;


            function hapusToast() {

                toast.classList.add(
                    'toast--leaving'
                );

                setTimeout(
                    () => toast.remove(),
                    180
                );

            }


            toast
                .querySelector('.toast-close')
                .addEventListener(
                    'click',
                    hapusToast
                );


            wrap.appendChild(toast);


            setTimeout(
                hapusToast,
                3500
            );

        }


        /*
        |--------------------------------------------------------------------------
        | BUKA MODAL VERIFIKASI
        |--------------------------------------------------------------------------
        */

        function bukaVerifikasi(item) {

            verifItem = item;


            const nama =
                item.warga?.nama || '-';


            document
                .getElementById('verifAvatar')
                .textContent =
                nama
                .trim()
                .charAt(0)
                .toUpperCase();


            document
                .getElementById('verifNama')
                .textContent = nama;


            document
                .getElementById('verifNik')
                .textContent =
                item.warga?.nik || '-';


            document
                .getElementById('verifJudul')
                .textContent =
                item.status === 'pending' ?
                'Verifikasi Setoran' :
                'Detail Setoran';


            document
                .getElementById('verifId')
                .value = item.id;


            const select =
                document.getElementById('verifKategori');


            /*
            |--------------------------------------------------------------------------
            | PILIH KATEGORI
            |--------------------------------------------------------------------------
            */

            pilihKategori(
                select,
                item.id_kategori
            );


            /*
            |--------------------------------------------------------------------------
            | JUMLAH
            |--------------------------------------------------------------------------
            */

            document
                .getElementById('verifBerat')
                .value =
                item.jumlah ??
                item.total_berat ??
                0;


            /*
            |--------------------------------------------------------------------------
            | SATUAN
            |--------------------------------------------------------------------------
            |
            | Jika item mempunyai snapshot satuan,
            | gunakan itu.
            |
            | Jika tidak, ambil dari kategori.
            |--------------------------------------------------------------------------
            */

            const satuanItem =
                item.satuan ||
                satuanDariSelect(select) ||
                'gram';


            document
                .getElementById('verifSatuan')
                .value = satuanItem;


            document
                .getElementById('verifAlasan')
                .value =
                item.catatan_admin || '';


            document
                .getElementById('verifAlasanBlock')
                .classList.remove('open');


            /*
            |--------------------------------------------------------------------------
            | FOTO
            |--------------------------------------------------------------------------
            */

            const fotoEl =
                document.getElementById('verifFoto');

            const fotoKosong =
                document.getElementById('verifFotoKosong');


            if (item.foto_bukti) {

                fotoEl.src =
                    '/storage/' +
                    item.foto_bukti;

                fotoEl.style.display =
                    'block';

                fotoKosong.style.display =
                    'none';

            } else {

                fotoEl.style.display =
                    'none';

                fotoKosong.style.display =
                    'block';

            }


            /*
            |--------------------------------------------------------------------------
            | STATUS EDIT
            |--------------------------------------------------------------------------
            */

            const bisaDiedit =
                item.status === 'pending';


            select.disabled = !bisaDiedit;


            document
                .getElementById('verifBerat')
                .disabled = !bisaDiedit;


            document
                .getElementById('btnTolakSetoran')
                .style.display =
                bisaDiedit ?
                'inline-flex' :
                'none';


            document
                .getElementById('btnSetujuiSetoran')
                .style.display =
                bisaDiedit ?
                'inline-flex' :
                'none';


            updateEstimasi();


            bukaModal(
                'modalVerifikasi'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE ESTIMASI VERIFIKASI
        |--------------------------------------------------------------------------
        */

        function updateEstimasi() {

            const select =
                document.getElementById(
                    'verifKategori'
                );


            const harga =
                hargaDariSelect(select);


            const satuan =
                satuanDariSelect(select);


            const jumlah =
                parseFloat(
                    document
                    .getElementById('verifBerat')
                    .value
                ) || 0;


            const hargaInfo =
                document.getElementById(
                    'verifHargaInfo'
                );


            const satuanInput =
                document.getElementById(
                    'verifSatuan'
                );


            const satuanInfo =
                document.getElementById(
                    'verifSatuanInfo'
                );


            const estimasi =
                document.getElementById(
                    'verifEstimasi'
                );


            /*
            |--------------------------------------------------------------------------
            | SATUAN
            |--------------------------------------------------------------------------
            */

            satuanInput.value =
                satuan || '-';


            /*
            |--------------------------------------------------------------------------
            | KETERANGAN SATUAN
            |--------------------------------------------------------------------------
            */

            const keteranganSatuan = {

                gram: 'berat dalam gram',

                kg: 'berat dalam kilogram',

                pcs: 'jumlah barang',

                liter: 'volume dalam liter'

            };


            satuanInfo.textContent =
                satuan &&
                keteranganSatuan[satuan]

                ?
                'Jumlah berdasarkan ' +
                keteranganSatuan[satuan] +
                '.'

                :
                'Pilih kategori terlebih dahulu.';


            /*
            |--------------------------------------------------------------------------
            | HARGA
            |--------------------------------------------------------------------------
            */

            if (
                harga > 0 &&
                satuan
            ) {

                hargaInfo.textContent =
                    'Harga aktif: ' +
                    formatHarga(
                        harga,
                        satuan === 'liter' ?
                        'L' :
                        satuan
                    );

            } else {

                hargaInfo.textContent =
                    'Kategori ini belum memiliki harga aktif.';

            }


            /*
            |--------------------------------------------------------------------------
            | ESTIMASI
            |--------------------------------------------------------------------------
            */

            estimasi.textContent =
                formatRupiah(
                    harga * jumlah
                );

        }


        /*
        |--------------------------------------------------------------------------
        | PROSES VERIFIKASI
        |--------------------------------------------------------------------------
        */

        function prosesVerifikasi(event) {

            event.preventDefault();


            const id =
                document.getElementById(
                    'verifId'
                ).value;


            const payload = {

                id_kategori: document.getElementById(
                    'verifKategori'
                ).value,

                jumlah: document.getElementById(
                    'verifBerat'
                ).value

            };


            fetch(
                    `/admin/pages/verifikasi-setoran/${id}/setujui`, {
                        method: 'PATCH',

                        headers: {

                            'Content-Type': 'application/json',

                            'Accept': 'application/json',

                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content

                        },

                        body: JSON.stringify(payload)

                    }
                )

                .then(async (res) => {

                    if (!res.ok) {

                        const err =
                            await res
                            .json()
                            .catch(() => ({}));


                        throw new Error(
                            err.message ||
                            'Gagal menyetujui setoran.'
                        );

                    }


                    return res.json();

                })

                .then(() => {

                    tutupModal(
                        'modalVerifikasi'
                    );


                    showToast(
                        'Setoran disetujui',
                        'Saldo warga telah diperbarui.'
                    );


                    setTimeout(
                        () => window.location.reload(),
                        800
                    );

                })

                .catch((err) => {

                    showToast(
                        'Gagal menyetujui',
                        err.message,
                        'error'
                    );

                });


            return false;

        }


        /*
        |--------------------------------------------------------------------------
        | BUKA TAMBAH SETORAN
        |--------------------------------------------------------------------------
        */

        function bukaTambahSetoran() {

            document
                .getElementById(
                    'formTambahSetoran'
                )
                .reset();


            resetCombo(
                'tambahWarga',
                'cariWarga',
                'listWarga'
            );


            resetCombo(
                'tambahKategori',
                'cariKategori',
                'listKategori'
            );


            document
                .getElementById('tambahSatuan')
                .value = '-';


            const today =
                new Date()
                .toISOString()
                .substring(0, 10);


            document
                .getElementById(
                    'tambahTanggal'
                )
                .value = today;


            updateEstimasiTambah();


            bukaModal(
                'modalTambah'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE ESTIMASI TAMBAH
        |--------------------------------------------------------------------------
        */

        function updateEstimasiTambah() {

            const select =
                document.getElementById(
                    'tambahKategori'
                );


            const harga =
                hargaDariSelect(select);


            const satuan =
                satuanDariSelect(select);


            const jumlah =
                parseFloat(
                    document
                    .getElementById(
                        'tambahBerat'
                    )
                    .value
                ) || 0;


            const satuanInput =
                document.getElementById(
                    'tambahSatuan'
                );


            if (satuanInput) {

                satuanInput.value =
                    satuan || '-';

            }


            document
                .getElementById(
                    'tambahEstimasi'
                )
                .textContent =
                formatRupiah(
                    harga * jumlah
                );

        }


        /*
        |--------------------------------------------------------------------------
        | SIMPAN SETORAN BARU
        |--------------------------------------------------------------------------
        */

        function simpanSetoranBaru(event) {

            event.preventDefault();


            /*
            |--------------------------------------------------------------------------
            | PAYLOAD
            |--------------------------------------------------------------------------
            |
            | Perhatikan koma setelah jumlah.
            |--------------------------------------------------------------------------
            */

            const payload = {

                id_warga: document.getElementById(
                    'tambahWarga'
                ).value,

                id_kategori: document.getElementById(
                    'tambahKategori'
                ).value,

                jumlah: document.getElementById(
                    'tambahBerat'
                ).value,

                tanggal_setoran: document.getElementById(
                    'tambahTanggal'
                ).value,

                catatan_admin: document.getElementById(
                    'tambahCatatan'
                )?.value || null

            };


            /*
            |--------------------------------------------------------------------------
            | VALIDASI WARGA
            |--------------------------------------------------------------------------
            */

            if (!payload.id_warga) {

                showToast(
                    'Gagal menyimpan',
                    'Silakan pilih warga terlebih dahulu.',
                    'error'
                );


                document
                    .getElementById('cariWarga')
                    .focus();


                return false;

            }


            /*
            |--------------------------------------------------------------------------
            | VALIDASI KATEGORI
            |--------------------------------------------------------------------------
            */

            if (!payload.id_kategori) {

                showToast(
                    'Gagal menyimpan',
                    'Silakan pilih kategori sampah terlebih dahulu.',
                    'error'
                );


                document
                    .getElementById('cariKategori')
                    .focus();


                return false;

            }


            /*
            |--------------------------------------------------------------------------
            | VALIDASI JUMLAH
            |--------------------------------------------------------------------------
            */

            if (
                !payload.jumlah ||
                Number(payload.jumlah) <= 0
            ) {

                showToast(
                    'Gagal menyimpan',
                    'Jumlah setoran harus lebih dari 0.',
                    'error'
                );


                document
                    .getElementById('tambahBerat')
                    .focus();


                return false;

            }


            fetch(
                    `/admin/pages/verifikasi-setoran`, {
                        method: 'POST',

                        headers: {

                            'Content-Type': 'application/json',

                            'Accept': 'application/json',

                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content

                        },

                        body: JSON.stringify(payload)

                    }
                )

                .then(async (res) => {

                    if (!res.ok) {

                        const err =
                            await res
                            .json()
                            .catch(() => ({}));


                        throw new Error(
                            err.message ||
                            'Gagal menyimpan setoran.'
                        );

                    }


                    return res.json();

                })

                .then(() => {

                    tutupModal(
                        'modalTambah'
                    );


                    showToast(
                        'Setoran ditambahkan',
                        'Data setoran tersimpan dan saldo warga diperbarui.'
                    );


                    setTimeout(
                        () => window.location.reload(),
                        800
                    );

                })

                .catch((err) => {

                    showToast(
                        'Gagal menyimpan',
                        err.message,
                        'error'
                    );

                });


            return false;

        }


        /*
        |--------------------------------------------------------------------------
        | TOLAK SETORAN
        |--------------------------------------------------------------------------
        */

        function tolakSetoran() {

            const alasanBlock =
                document.getElementById(
                    'verifAlasanBlock'
                );


            /*
            |--------------------------------------------------------------------------
            | Tampilkan textarea terlebih dahulu
            |--------------------------------------------------------------------------
            */

            if (
                !alasanBlock
                .classList
                .contains('open')
            ) {

                alasanBlock
                    .classList
                    .add('open');


                document
                    .getElementById(
                        'verifAlasan'
                    )
                    .focus();


                return;

            }


            const catatan =
                document
                .getElementById(
                    'verifAlasan'
                )
                .value
                .trim();


            if (!catatan) {

                showToast(
                    'Catatan diperlukan',
                    'Isi alasan penolakan sebelum melanjutkan.',
                    'error'
                );


                return;

            }


            const id =
                document
                .getElementById(
                    'verifId'
                )
                .value;


            fetch(
                    `/admin/pages/verifikasi-setoran/${id}/tolak`, {
                        method: 'PATCH',

                        headers: {

                            'Content-Type': 'application/json',

                            'Accept': 'application/json',

                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content

                        },

                        body: JSON.stringify({
                            catatan_admin: catatan
                        })

                    }
                )

                .then(async (res) => {

                    if (!res.ok) {

                        const err =
                            await res
                            .json()
                            .catch(() => ({}));


                        throw new Error(
                            err.message ||
                            'Gagal menolak setoran.'
                        );

                    }


                    return res.json();

                })

                .then(() => {

                    tutupModal(
                        'modalVerifikasi'
                    );


                    showToast(
                        'Setoran ditolak',
                        'Warga perlu mengisi ulang data setoran.'
                    );


                    setTimeout(
                        () => window.location.reload(),
                        800
                    );

                })

                .catch((err) => {

                    showToast(
                        'Gagal menolak',
                        err.message,
                        'error'
                    );

                });

        }
    </script>

@endsection
