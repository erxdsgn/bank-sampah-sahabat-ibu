@extends('admin.layouts.app')

@section('title', 'Artikel & Edukasi')
@section('active', 'artikel')
@section('crumbs', 'Konten & Laporan | Artikel & Edukasi')

@section('content')

    @php
        $totalKonten = $artikel->count();
        $totalPublikasi = $artikel->whereNotNull('tanggal_publish')->count();
        $totalDraft = $artikel->whereNull('tanggal_publish')->count();
        $totalAcara = $artikel->where('jenis', 'acara')->count();

        $labelJenis = [
            'edukasi' => ['Edukasi Sampah', 'badge--info'],
            'artikel' => ['Artikel', 'badge--info'],
            'acara' => ['Acara', 'badge--purple'],
        ];

        $preview = $artikel->whereNotNull('tanggal_publish')->first() ?? $artikel->first();
    @endphp

    <div class="artikel-page">

        <section class="hero">
            <div class="hero-text">
                <span class="eyebrow">KONTEN & LAPORAN</span>

                <h1 class="hero-title">
                    Artikel & <span class="accent">Edukasi</span>
                </h1>

                <p class="hero-sub">
                    Kelola edukasi, artikel, dan informasi yang ditampilkan pada aplikasi masyarakat.
                </p>
            </div>

            <div class="hero-actions">
                <button class="btn btn--primary" type="button" onclick="bukaModalTambah()">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Konten
                </button>
            </div>
        </section>


        {{-- SUMMARY --}}
        <section class="summary-grid">

            <div class="summary-card">
                <div class="summary-card-top">
                    <div>
                        <div class="summary-label">Total Konten</div>
                        <div class="summary-value">{{ $totalKonten }}</div>
                    </div>

                    <div class="summary-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
                            <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z"></path>
                            <path d="M9 13h6M9 17h6"></path>
                        </svg>
                    </div>
                </div>

                <div class="summary-info">
                    Seluruh artikel & edukasi
                </div>
            </div>


            <div class="summary-card">
                <div class="summary-card-top">
                    <div>
                        <div class="summary-label">Dipublikasikan</div>
                        <div class="summary-value">{{ $totalPublikasi }}</div>
                    </div>

                    <div class="summary-icon summary-icon--income">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 6 9 17l-5-5"></path>
                        </svg>
                    </div>
                </div>

                <div class="summary-info">
                    Sudah tampil di aplikasi warga
                </div>
            </div>


            <div class="summary-card">
                <div class="summary-card-top">
                    <div>
                        <div class="summary-label">Draft</div>
                        <div class="summary-value">{{ $totalDraft }}</div>
                    </div>

                    <div class="summary-icon summary-icon--expense">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                        </svg>
                    </div>
                </div>

                <div class="summary-info">
                    Belum ditayangkan
                </div>
            </div>


            <div class="summary-card">
                <div class="summary-card-top">
                    <div>
                        <div class="summary-label">Acara Mendatang</div>
                        <div class="summary-value">{{ $totalAcara }}</div>
                    </div>

                    <div class="summary-icon">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                            <path d="M3 10h18"></path>
                            <path d="M8 3v4M16 3v4"></path>
                        </svg>
                    </div>
                </div>

                <div class="summary-info">
                    Segera hadir
                </div>
            </div>

        </section>


        <div class="artikel-columns">

            {{-- DAFTAR ARTIKEL --}}
            <section class="card">

                <div class="table-toolbar">

                    <div class="table-search">
                        <svg viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>

                        <input type="text" id="searchArtikel" placeholder="Cari judul artikel..." autocomplete="off">
                    </div>


                    <div class="toolbar-right">

                        <select id="kategoriArtikel" class="toolbar-select">
                            <option value="semua">Semua Kategori</option>
                            <option value="edukasi">Edukasi Sampah</option>
                            <option value="artikel">Artikel</option>
                            <option value="acara">Acara</option>
                        </select>

                        <select id="statusArtikel" class="toolbar-select">
                            <option value="semua">Semua Status</option>
                            <option value="publish">Dipublikasikan</option>
                            <option value="draft">Draft</option>
                        </select>

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

                    <table class="data-table" id="artikelTable">

                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Judul Konten</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="artikelTableBody">

                            @forelse($artikel as $item)
                                @php
                                    $status = $item->tanggal_publish ? 'publish' : 'draft';

                                    [$labelKategori, $kelasKategori] = $labelJenis[$item->jenis] ?? [
                                        'Edukasi Sampah',
                                        'badge--info',
                                    ];
                                @endphp

                                <tr data-judul="{{ strtolower($item->judul) }}" data-status="{{ $status }}"
                                    data-kategori="{{ $item->jenis }}">

                                    <td>
                                        @if ($item->gambar)
                                            <img src="{{ asset('uploads/artikel/' . $item->gambar) }}"
                                                alt="{{ $item->judul }}" class="artikel-thumb">
                                        @else
                                            <div class="artikel-thumb artikel-thumb--kosong">
                                                Tidak ada gambar
                                            </div>
                                        @endif
                                    </td>


                                    <td>
                                        <div class="artikel-judul">
                                            {{ $item->judul }}
                                        </div>

                                        <div class="artikel-ringkasan">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($item->konten), 15) }}
                                        </div>
                                    </td>


                                    <td>
                                        <span class="badge {{ $kelasKategori }}">
                                            {{ $labelKategori }}
                                        </span>
                                    </td>


                                    <td>

                                        @if ($item->tanggal_publish)
                                            <span class="badge badge--success">
                                                Dipublikasikan
                                            </span>
                                        @else
                                            <span class="badge badge--warning">
                                                Draft
                                            </span>
                                        @endif

                                    </td>


                                    <td class="nowrap">
                                        @if ($item->tanggal_publish)
                                            {{ \Carbon\Carbon::parse($item->tanggal_publish)->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>

                                        <div class="table-actions">

                                            <button class="icon-btn" title="Lihat" type="button"
                                                onclick="bukaDetail({{ $item->id_artikel }})">

                                                <svg viewBox="0 0 24 24">
                                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                                    <circle cx="12" cy="12" r="2.5"></circle>
                                                </svg>

                                            </button>


                                            <button class="icon-btn" title="Edit" type="button"
                                                onclick="editArtikelModal({{ $item->id_artikel }})">

                                                <svg viewBox="0 0 24 24">
                                                    <path d="M12 20h9"></path>
                                                    <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                                </svg>

                                            </button>


                                            <button class="icon-btn danger" title="Hapus" type="button"
                                                onclick="hapusArtikel({{ $item->id_artikel }}, {{ Js::from($item->judul) }})">

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

                                <tr class="empty-row">
                                    <td colspan="6">

                                        <div class="empty-icon">📄</div>

                                        <strong>Belum Ada Konten</strong>

                                        <p class="empty-text">
                                            Silakan tambahkan artikel atau edukasi pertama.
                                        </p>

                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>


                    <div class="catalog-empty" id="artikelKosongCari">

                        <div class="empty-icon">🔍</div>

                        <strong>Data Tidak Ditemukan</strong>

                        <p class="empty-text">
                            Tidak ada konten yang cocok dengan pencarian atau filter ini.
                        </p>

                    </div>

                </div>


                <div class="table-footer">

                    <div class="table-info">
                        Menampilkan
                        <strong id="jumlahTampil">{{ $artikel->count() }}</strong>
                        dari
                        <strong>{{ $artikel->count() }}</strong>
                        konten
                    </div>

                </div>

            </section>


            {{-- PREVIEW MOBILE --}}
            <section class="card preview-card">

                <div class="preview-head">
                    <h3 class="card-title">
                        Preview Aplikasi Mobile
                    </h3>
                </div>


                <div class="form-group preview-select-group">

                    <div class="combo" id="comboPreview">

                        <input type="text" id="cariPreview" class="combo-input" placeholder="Ketik judul konten..."
                            autocomplete="off" {{ $artikel->isEmpty() ? 'disabled' : '' }}>

                        <div class="combo-list" id="listPreview">
                        </div>

                    </div>


                    <select id="previewSelector" class="combo-hidden" tabindex="-1" aria-hidden="true">

                        @forelse($artikel as $item)
                            <option value="{{ $item->id_artikel }}" @selected($preview && $preview->id_artikel === $item->id_artikel)>

                                {{ $item->judul }}
                                {{ $item->tanggal_publish ? '' : '(Draft)' }}

                            </option>

                        @empty

                            <option value="">
                                Belum ada konten
                            </option>
                        @endforelse

                    </select>

                </div>


                <div class="phone">

                    <div class="phone-header">
                        ← &nbsp; Edukasi Sampah
                    </div>

                    <div class="phone-body" id="phonePreviewBody">
                    </div>

                </div>

            </section>

        </div>


        {{-- TOAST --}}
        <div class="toast-wrap" id="toastWrap">
        </div>


        {{-- MODAL TAMBAH / EDIT --}}
        <div class="modal-overlay" id="modalArtikel">

            <div class="modal-box">

                <div class="modal-head">

                    <h3 class="modal-title" id="modalArtikelTitle">

                        <svg viewBox="0 0 24 24">
                            <path d="M12 5v14M5 12h14"></path>
                        </svg>

                        Tambah Konten

                    </h3>


                    <button class="modal-close" type="button" onclick="tutupModal('modalArtikel')">

                        &times;

                    </button>

                </div>


                <form id="formArtikel" onsubmit="return false;">

                    <div class="modal-body">

                        <input type="hidden" id="artikelId">


                        <div class="form-grid">

                            <div class="form-group form-group--full">

                                <label for="artikelJudul">
                                    Judul Konten
                                </label>

                                <input type="text" id="artikelJudul" class="form-control"
                                    placeholder="Contoh: Cara Memilah Sampah dari Rumah" required>

                                <span class="form-error" id="err_judul">
                                </span>

                            </div>


                            <div class="form-group form-group--full">

                                <label for="artikelJenis">
                                    Jenis Konten
                                </label>

                                <select id="artikelJenis" class="form-control" required>

                                    <option value="">
                                        -- Pilih Jenis Konten --
                                    </option>

                                    <option value="edukasi">
                                        Edukasi Sampah
                                    </option>

                                    <option value="artikel">
                                        Artikel
                                    </option>

                                    <option value="acara">
                                        Acara
                                    </option>

                                </select>


                                <span class="field-hint">
                                    Pilih jenis konten yang akan ditampilkan pada aplikasi masyarakat.
                                </span>

                                <span class="form-error" id="err_jenis">
                                </span>

                            </div>


                            <div class="form-group form-group--full">

                                <label for="artikelGambar">
                                    Gambar Utama
                                </label>

                                <input type="file" id="artikelGambar" class="form-control"
                                    accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewGambar(this)">

                                <span class="field-hint">
                                    Format JPG, PNG, atau WEBP. Maksimal 2MB.
                                    Kosongkan saat edit jika tidak ingin mengganti gambar.
                                </span>

                                <span class="form-error" id="err_gambar">
                                </span>


                                <div class="gambar-preview" id="gambarPreviewWrap">

                                    <img id="gambarPreviewImg" src="" alt="Preview">

                                </div>


                                <div id="gambarLamaInfo"></div>

                            </div>


                            <div class="form-group form-group--full">

                                <label for="artikelKonten">
                                    Isi Konten
                                </label>

                                <textarea id="artikelKonten" class="form-control" rows="8"
                                    placeholder="Tuliskan artikel atau materi edukasi di sini..." required></textarea>

                                <span class="form-error" id="err_konten">
                                </span>

                            </div>


                            <div class="form-group form-group--full">

                                <label for="artikelTanggal">
                                    Tanggal Publikasi
                                </label>

                                <input type="date" id="artikelTanggal" class="form-control">

                                <span class="form-error" id="err_tanggal">
                                </span>

                            </div>

                        </div>

                    </div>


                    <div class="modal-foot">

                        <button class="btn btn--ghost" type="button" onclick="tutupModal('modalArtikel')">

                            Batal

                        </button>


                        <button type="button" id="btnSimpanDraft" class="btn btn--outline">

                            Simpan sebagai Draft

                        </button>


                        <button class="btn btn--primary" type="button" id="artikelSubmitBtn"
                            onclick="simpanArtikel('publish')">

                            Publikasikan

                        </button>

                    </div>

                </form>

            </div>

        </div>


        {{-- MODAL DETAIL --}}
        <div class="modal-overlay" id="modalDetail">

            <div class="modal-box">

                <div class="modal-head">

                    <h3 class="modal-title">

                        <svg viewBox="0 0 24 24">
                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                            <circle cx="12" cy="12" r="2.5"></circle>
                        </svg>

                        Detail Konten

                    </h3>


                    <button class="modal-close" type="button" onclick="tutupModal('modalDetail')">

                        &times;

                    </button>

                </div>


                <div class="modal-body">

                    <div id="detailGambar"></div>

                    <div class="detail-title" id="detailJudul">
                        -
                    </div>

                    <div class="detail-date" id="detailTanggal">
                        -
                    </div>

                    <div class="detail-text" id="detailKonten">
                        -
                    </div>

                </div>


                <div class="modal-foot">

                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalDetail')">

                        Tutup

                    </button>

                </div>

            </div>

        </div>


        {{-- MODAL HAPUS --}}
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


                    <h3 class="confirm-title">
                        Hapus Konten?
                    </h3>


                    <p class="confirm-text">
                        Anda akan menghapus konten
                        <strong id="hapusJudul">-</strong>.
                        Tindakan ini tidak dapat dibatalkan.
                    </p>

                </div>


                <div class="modal-foot modal-foot--center">

                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHapus')">

                        Batal

                    </button>


                    <button class="btn btn--danger" type="button" onclick="konfirmasiHapus()">

                        Ya, Hapus

                    </button>

                </div>

            </div>

        </div>


        <style>
            /* =========================================================
                   ARTIKEL PAGE — DASHBOARD THEME
                ========================================================= */

            .artikel-page {
                --page-bg: #f5f7fb;
                --surface: #ffffff;
                --surface-secondary: #f8fafc;
                --text: #1f2937;
                --text-secondary: #6b7280;
                --text-muted: #9ca3af;
                --border: #e5e7eb;
                --table-head: #f8fafc;
                --table-row: #ffffff;
                --table-hover: #fafafa;
                --input-bg: #ffffff;
                --empty: #f8fafc;
                --shadow: 0 8px 25px rgba(15, 23, 42, .06);

                color: var(--text);
            }

            html[data-theme="dark"] .artikel-page {
                --page-bg: #0b1220;
                --surface: #151d2f;
                --surface-secondary: #1b2438;
                --text: #f1f5f9;
                --text-secondary: #aab6c8;
                --text-muted: #748198;
                --border: #29364d;
                --table-head: #111a2c;
                --table-row: #151d2f;
                --table-hover: #202b40;
                --input-bg: #111a2c;
                --empty: #111a2c;
                --shadow: 0 8px 25px rgba(0, 0, 0, .25);
            }

            .artikel-page input,
            .artikel-page select,
            .artikel-page textarea {
                color-scheme: light;
            }

            html[data-theme="dark"] .artikel-page input,
            html[data-theme="dark"] .artikel-page select,
            html[data-theme="dark"] .artikel-page textarea {
                color-scheme: dark;
            }


            /* =========================================================
                   SUMMARY
                ========================================================= */

            .artikel-page .summary-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 16px;
                margin-bottom: 20px;
            }

            .artikel-page .summary-card {
                border: 1px solid var(--border);
                background: var(--surface);
                border-radius: 14px;
                padding: 18px;
                min-width: 0;
                box-shadow: var(--shadow);
            }

            .artikel-page .summary-card-top {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 12px;
            }

            .artikel-page .summary-label {
                font-size: 13px;
                color: var(--text-secondary);
                margin-bottom: 8px;
            }

            .artikel-page .summary-value {
                font-size: 21px;
                font-weight: 800;
                line-height: 1.25;
                color: var(--text);
            }

            .artikel-page .summary-icon {
                width: 40px;
                height: 40px;
                flex: 0 0 40px;
                border-radius: 11px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(99, 102, 241, .14);
                color: #a5b4fc;
            }

            .artikel-page .summary-icon--income {
                background: rgba(34, 197, 94, .14);
                color: #4ade80;
            }

            .artikel-page .summary-icon--expense {
                background: rgba(245, 158, 11, .14);
                color: #fbbf24;
            }

            .artikel-page .summary-icon svg {
                width: 20px;
                height: 20px;
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
            }

            .artikel-page .summary-info {
                margin-top: 12px;
                font-size: 12px;
                color: var(--text-secondary);
            }


            /* =========================================================
                   2 COLUMN
                ========================================================= */

            .artikel-page .artikel-columns {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 300px;
                gap: 16px;
                margin-bottom: 16px;
                align-items: start;
            }


            /* =========================================================
                   CARD
                ========================================================= */

            .artikel-page .card {
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 14px;
                overflow: hidden;
                box-shadow: var(--shadow);
            }

            .artikel-page .card-title {
                margin: 0 0 5px;
                font-size: 16px;
                font-weight: 700;
                color: var(--text);
            }

            .artikel-page .card-sub {
                margin: 0;
                font-size: 13px;
                color: var(--text-secondary);
            }


            /* =========================================================
                   SEARCH / TOOLBAR
                ========================================================= */

            .artikel-page .table-toolbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                padding: 16px 24px;
                flex-wrap: wrap;
            }

            .artikel-page .table-search {
                width: 360px;
                position: relative;
            }

            .artikel-page .table-search svg {
                position: absolute;
                width: 18px;
                height: 18px;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                fill: none;
                stroke: var(--text-secondary);
                stroke-width: 1.8;
                pointer-events: none;
            }

            .artikel-page .table-search input {
                width: 100%;
                height: 40px;
                padding: 0 14px 0 40px;
                border: 1px solid var(--border);
                border-radius: 8px;
                outline: none;
                box-sizing: border-box;
                background: var(--input-bg);
                color: var(--text);
                font-family: inherit;
            }

            .artikel-page .table-search input::placeholder {
                color: var(--text-muted);
            }

            .artikel-page .table-search input:focus {
                border-color: #22c55e;
                box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
            }

            .artikel-page .toolbar-right {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .artikel-page .toolbar-select {
                height: 40px;
                padding: 0 12px;
                border: 1px solid var(--border);
                border-radius: 8px;
                background: var(--input-bg);
                font-family: inherit;
                font-size: 13px;
                color: var(--text);
                outline: none;
                cursor: pointer;
            }

            .artikel-page .toolbar-select:focus {
                border-color: #22c55e;
                box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
            }

            .artikel-page .btn--ghost {
                background: var(--surface-secondary);
                color: var(--text-secondary);
                border: 1px solid var(--border);
            }

            .artikel-page .btn--ghost:hover {
                background: var(--table-hover);
                color: var(--text);
                border-color: var(--border);
            }


            /* =========================================================
                   TABLE
                ========================================================= */

            .artikel-page .table-responsive {
                width: 100%;
                overflow-x: auto;
            }

            .artikel-page .data-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 760px;
            }

            .artikel-page .data-table th {
                background: var(--table-head);
                color: var(--text-secondary);
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                padding: 13px 16px;
                text-align: left;
                border-top: 1px solid var(--border);
                border-bottom: 1px solid var(--border);
                white-space: nowrap;
            }

            .artikel-page .data-table td {
                padding: 16px;
                border-bottom: 1px solid var(--border);
                font-size: 13px;
                color: var(--text-secondary);
                vertical-align: middle;
                background: var(--table-row);
            }

            .artikel-page .data-table tbody tr {
                transition: background .15s ease;
            }

            .artikel-page .data-table tbody tr:hover td {
                background: var(--table-hover);
            }

            .artikel-page .nowrap {
                white-space: nowrap;
            }


            /* =========================================================
                   THUMBNAIL
                ========================================================= */

            .artikel-page .artikel-thumb {
                width: 70px;
                height: 55px;
                border-radius: 8px;
                object-fit: cover;
                display: block;
                background: var(--surface-secondary);
                border: 1px solid var(--border);
            }

            .artikel-page .artikel-thumb--kosong {
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--text-muted);
                font-size: 10px;
                text-align: center;
                padding: 4px;
            }

            .artikel-page .artikel-judul {
                color: var(--text);
                font-weight: 700;
                margin-bottom: 4px;
                line-height: 1.35;
            }

            .artikel-page .artikel-ringkasan {
                color: var(--text-secondary);
                font-size: 12px;
                line-height: 1.45;
                width: 250px;
                max-width: 250px;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
                overflow: hidden;
                word-break: break-word;
                overflow-wrap: anywhere;
            }


            /* =========================================================
                   TABLE ACTIONS
                ========================================================= */

            .artikel-page .table-actions {
                display: flex;
                gap: 6px;
            }

            .artikel-page .icon-btn {
                width: 34px;
                height: 34px;
                border: 1px solid var(--border);
                background: var(--surface-secondary);
                color: var(--text-secondary);
                border-radius: 7px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: .15s ease;
            }

            .artikel-page .icon-btn:hover {
                background: var(--table-hover);
                color: var(--text);
                border-color: #3a4962;
            }

            .artikel-page .icon-btn svg {
                width: 16px;
                height: 16px;
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
            }

            .artikel-page .icon-btn.danger {
                color: #f87171;
            }

            .artikel-page .icon-btn.danger:hover {
                background: rgba(239, 68, 68, .12);
                border-color: rgba(239, 68, 68, .30);
                color: #fca5a5;
            }


            /* =========================================================
                   FOOTER / EMPTY
                ========================================================= */

            .artikel-page .table-footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 16px 24px;
            }

            .artikel-page .table-info {
                font-size: 13px;
                color: var(--text-secondary);
            }

            .artikel-page .catalog-empty {
                display: none;
                text-align: center;
                padding: 50px 20px;
                color: var(--text);
            }

            .artikel-page .empty-row td {
                text-align: center;
                padding: 40px !important;
                background: var(--table-row);
            }

            .artikel-page .empty-icon {
                font-size: 30px;
                margin-bottom: 10px;
            }

            .artikel-page .empty-text {
                margin: 5px 0 0;
                color: var(--text-secondary);
                font-size: 13px;
            }


            /* =========================================================
                   BADGES
                ========================================================= */

            .artikel-page .badge {
                display: inline-flex;
                align-items: center;
                padding: 6px 12px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 700;
                white-space: nowrap;
            }

            .artikel-page .badge--success {
                background: rgba(34, 197, 94, .14);
                color: #4ade80;
            }

            .artikel-page .badge--warning {
                background: rgba(245, 158, 11, .14);
                color: #fbbf24;
            }

            .artikel-page .badge--info {
                background: rgba(14, 165, 233, .14);
                color: #38bdf8;
            }

            .artikel-page .badge--purple {
                background: rgba(168, 85, 247, .14);
                color: #c084fc;
            }


            /* =========================================================
                   PREVIEW
                ========================================================= */

            .artikel-page .preview-card {
                padding: 20px;
            }

            .artikel-page .preview-head {
                margin-bottom: 16px;
            }

            .artikel-page .preview-select-group {
                margin-bottom: 16px;
            }

            .artikel-page .combo {
                position: relative;
            }

            .artikel-page .combo-hidden {
                display: none !important;
            }

            .artikel-page .combo-input {
                width: 100%;
                border: 1px solid var(--border);
                border-radius: 8px;
                padding: 9px 12px;
                font-size: 13px;
                font-family: inherit;
                outline: none;
                box-sizing: border-box;
                background: var(--input-bg);
                color: var(--text);
            }

            .artikel-page .combo-input::placeholder {
                color: var(--text-muted);
            }

            .artikel-page .combo-input:focus {
                border-color: #22c55e;
                box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
            }

            .artikel-page .combo-input:disabled {
                background: var(--surface-secondary);
                color: var(--text-muted);
                cursor: not-allowed;
            }

            .artikel-page .combo-list {
                display: none;
                position: absolute;
                top: calc(100% + 4px);
                left: 0;
                right: 0;
                max-height: 220px;
                overflow-y: auto;
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, .25);
                z-index: 20;
            }

            .artikel-page .combo-list.show {
                display: block;
            }

            .artikel-page .combo-item {
                padding: 9px 12px;
                font-size: 13px;
                color: var(--text-secondary);
                cursor: pointer;
            }

            .artikel-page .combo-item:hover,
            .artikel-page .combo-item.is-active {
                background: var(--table-hover);
                color: var(--text);
            }

            .artikel-page .combo-empty {
                padding: 12px;
                font-size: 12.5px;
                color: var(--text-muted);
                text-align: center;
            }


            /* =========================================================
                   PHONE MOCKUP
                ========================================================= */

            .artikel-page .phone {
                width: 100%;
                max-width: 230px;
                height: 455px;
                border: 7px solid #263449;
                border-radius: 29px;
                overflow: hidden;
                margin: 0 auto;
                background: #f8fafc;
                box-shadow: 0 12px 30px rgba(0, 0, 0, .30);
            }

            .artikel-page .phone-header {
                background: #15803d;
                color: #fff;
                padding: 16px 14px;
                font-size: 13px;
                font-weight: 600;
            }

            .artikel-page .phone-body {
                padding: 12px;
                height: calc(100% - 46px);
                overflow-y: auto;
                background: #f8fafc;
            }

            .artikel-page .phone-image {
                width: 100%;
                height: 115px;
                border-radius: 8px;
                object-fit: cover;
                margin-bottom: 11px;
            }

            .artikel-page .phone-image--kosong {
                display: flex;
                align-items: center;
                justify-content: center;
                background: #e5ece8;
                color: #71817b;
                font-size: 11px;
            }

            .artikel-page .phone-title {
                color: #1f2937;
                font-size: 16px;
                line-height: 1.3;
                font-weight: 700;
            }

            .artikel-page .phone-meta {
                color: #15803d;
                font-size: 9px;
                margin: 8px 0 12px;
            }

            .artikel-page .phone-meta .phone-badge-draft {
                display: inline-block;
                margin-left: 4px;
                padding: 1px 6px;
                border-radius: 20px;
                background: #fef3c7;
                color: #b45309;
                font-weight: 700;
            }

            .artikel-page .phone-content {
                color: #66758a;
                font-size: 10px;
                line-height: 1.65;
            }

            .artikel-page .phone-tip {
                background: #e8f5ed;
                color: #166534;
                padding: 10px;
                border-radius: 8px;
                font-size: 9px;
                line-height: 1.5;
                margin-top: 14px;
            }

            .artikel-page .phone-empty {
                text-align: center;
                color: #9aa6b5;
                padding-top: 130px;
                font-size: 12px;
            }


            /* =========================================================
                   MODAL
                ========================================================= */

            .artikel-page .modal-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(2, 6, 23, .72);
                align-items: center;
                justify-content: center;
                padding: 20px;
                z-index: 1000;
            }

            .artikel-page .modal-overlay.active {
                display: flex;
            }

            .artikel-page .modal-box {
                width: 100%;
                max-width: 620px;
                max-height: 90vh;
                overflow-y: auto;
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 14px;
                box-shadow: 0 20px 45px rgba(0, 0, 0, .35);
                animation: artikelModalPop .15s ease-out;
            }

            .artikel-page .modal-box--sm {
                max-width: 420px;
            }

            @keyframes artikelModalPop {
                from {
                    opacity: 0;
                    transform: translateY(8px) scale(.98);
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .artikel-page .modal-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 20px 24px;
                border-bottom: 1px solid var(--border);
            }

            .artikel-page .modal-title {
                margin: 0;
                font-size: 16px;
                font-weight: 700;
                color: var(--text);
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .artikel-page .modal-title svg {
                width: 18px;
                height: 18px;
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
            }

            .artikel-page .modal-close {
                width: 30px;
                height: 30px;
                border: none;
                background: transparent;
                border-radius: 7px;
                font-size: 20px;
                line-height: 1;
                color: var(--text-secondary);
                cursor: pointer;
            }

            .artikel-page .modal-close:hover {
                background: var(--surface-secondary);
                color: var(--text);
            }

            .artikel-page .modal-body {
                padding: 22px 24px;
            }

            .artikel-page .modal-body--center {
                text-align: center;
                padding-top: 28px;
            }

            .artikel-page .modal-foot {
                display: flex;
                justify-content: flex-end;
                flex-wrap: wrap;
                gap: 10px;
                padding: 16px 24px;
                border-top: 1px solid var(--border);
            }

            .artikel-page .modal-foot--center {
                justify-content: center;
            }


            /* =========================================================
                   BUTTONS
                ========================================================= */

            .artikel-page .btn--danger {
                background: #dc2626;
                color: #fff;
                border: 1px solid #dc2626;
            }

            .artikel-page .btn--danger:hover {
                background: #b91c1c;
                border-color: #b91c1c;
            }

            .artikel-page .btn--outline {
                background: transparent;
                color: #4ade80;
                border: 1px solid #22c55e;
            }

            .artikel-page .btn--outline:hover {
                background: rgba(34, 197, 94, .10);
            }


            /* =========================================================
                   FORM
                ========================================================= */

            .artikel-page .form-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .artikel-page .form-group {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }

            .artikel-page .form-group--full {
                grid-column: 1 / -1;
            }

            .artikel-page .form-group label {
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                color: var(--text-secondary);
            }

            .artikel-page .form-control {
                width: 100%;
                padding: 10px 14px;
                font-size: 13px;
                color: var(--text);
                border: 1px solid var(--border);
                border-radius: 8px;
                outline: none;
                box-sizing: border-box;
                font-family: inherit;
                transition: border-color .15s ease, box-shadow .15s ease;
                background: var(--input-bg);
            }

            .artikel-page .form-control::placeholder {
                color: var(--text-muted);
            }

            .artikel-page .form-control:focus {
                border-color: #22c55e;
                box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
            }

            .artikel-page textarea.form-control {
                resize: vertical;
            }

            .artikel-page .form-control.is-invalid {
                border-color: #ef4444;
            }

            .artikel-page .form-error {
                display: block;
                margin-top: 2px;
                font-size: 12px;
                color: #f87171;
                min-height: 14px;
            }

            .artikel-page .field-hint {
                display: block;
                margin-top: 4px;
                font-size: 11px;
                color: var(--text-muted);
                line-height: 1.5;
            }


            /* =========================================================
                   IMAGE PREVIEW
                ========================================================= */

            .artikel-page .gambar-preview {
                display: none;
                margin-top: 10px;
            }

            .artikel-page .gambar-preview img {
                width: 120px;
                height: 85px;
                border-radius: 8px;
                object-fit: cover;
                border: 1px solid var(--border);
            }

            .artikel-page #gambarLamaInfo img {
                width: 120px;
                height: 85px;
                border-radius: 8px;
                object-fit: cover;
                margin-top: 8px;
                border: 1px solid var(--border);
            }

            .artikel-page #gambarLamaInfo small {
                display: block;
                margin-top: 6px;
                color: var(--text-muted);
                font-size: 11px;
            }


            /* =========================================================
                   DETAIL
                ========================================================= */

            .artikel-page .detail-image {
                width: 100%;
                max-height: 260px;
                object-fit: cover;
                border-radius: 10px;
                margin-bottom: 18px;
                border: 1px solid var(--border);
            }

            .artikel-page .detail-title {
                color: var(--text);
                font-size: 20px;
                font-weight: 700;
                margin-bottom: 8px;
                line-height: 1.35;
            }

            .artikel-page .detail-date {
                color: #4ade80;
                font-size: 12px;
                margin-bottom: 16px;
            }

            .artikel-page .detail-text {
                color: var(--text-secondary);
                line-height: 1.8;
                font-size: 14px;
                white-space: pre-line;
            }


            /* =========================================================
                   CONFIRM DELETE
                ========================================================= */

            .artikel-page .confirm-icon {
                width: 56px;
                height: 56px;
                margin: 0 auto 14px;
                border-radius: 50%;
                background: rgba(239, 68, 68, .14);
                color: #f87171;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .artikel-page .confirm-icon svg {
                width: 24px;
                height: 24px;
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
            }

            .artikel-page .confirm-title {
                margin: 0 0 8px;
                font-size: 16px;
                font-weight: 700;
                color: var(--text);
            }

            .artikel-page .confirm-text {
                margin: 0;
                font-size: 13px;
                color: var(--text-secondary);
                line-height: 1.6;
            }


            /* =========================================================
                   TOAST
                ========================================================= */

            .artikel-page .toast-wrap {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1100;
                display: flex;
                flex-direction: column;
                gap: 10px;
                pointer-events: none;
            }

            .artikel-page .toast {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                min-width: 300px;
                max-width: 380px;
                background: var(--surface);
                border: 1px solid var(--border);
                border-left: 4px solid #22c55e;
                border-radius: 10px;
                box-shadow: 0 12px 30px rgba(0, 0, 0, .30);
                padding: 14px 16px;
                pointer-events: auto;
            }

            .artikel-page .toast.toast--error {
                border-left-color: #ef4444;
            }

            .artikel-page .toast-icon {
                width: 22px;
                height: 22px;
                border-radius: 50%;
                background: rgba(34, 197, 94, .14);
                color: #4ade80;
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .artikel-page .toast.toast--error .toast-icon {
                background: rgba(239, 68, 68, .14);
                color: #f87171;
            }

            .artikel-page .toast-icon svg {
                width: 13px;
                height: 13px;
                fill: none;
                stroke: currentColor;
                stroke-width: 2.4;
            }

            .artikel-page .toast-body {
                flex: 1;
            }

            .artikel-page .toast-title {
                font-size: 13px;
                font-weight: 700;
                color: var(--text);
                margin: 0 0 2px;
            }

            .artikel-page .toast-text {
                font-size: 12.5px;
                color: var(--text-secondary);
                margin: 0;
                line-height: 1.5;
            }

            .artikel-page .toast-close {
                border: none;
                background: transparent;
                color: var(--text-muted);
                font-size: 16px;
                line-height: 1;
                cursor: pointer;
                padding: 0;
                flex-shrink: 0;
            }

            .artikel-page .toast-close:hover {
                color: var(--text);
            }


            /* =========================================================
                   RESPONSIVE
                ========================================================= */

            @media (max-width: 1100px) {

                .artikel-page .summary-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .artikel-page .artikel-columns {
                    grid-template-columns: 1fr;
                }

                .artikel-page .preview-card {
                    display: none;
                }
            }

            @media (max-width: 760px) {

                .artikel-page .table-toolbar {
                    align-items: stretch;
                }

                .artikel-page .table-search {
                    width: 100%;
                }

                .artikel-page .toolbar-right {
                    width: 100%;
                }

                .artikel-page .toolbar-select {
                    flex: 1 1 150px;
                }
            }

            @media (max-width: 560px) {

                .artikel-page .summary-grid {
                    grid-template-columns: 1fr;
                }

                .artikel-page .modal-overlay {
                    padding: 12px;
                }

                .artikel-page .modal-head {
                    padding: 16px;
                }

                .artikel-page .modal-body {
                    padding: 18px 16px;
                }

                .artikel-page .modal-foot {
                    padding: 14px 16px;
                }

                .artikel-page .modal-foot .btn {
                    flex: 1 1 auto;
                }

                .artikel-page .toast-wrap {
                    left: 12px;
                    right: 12px;
                    top: 12px;
                }

                .artikel-page .toast {
                    min-width: 0;
                    width: 100%;
                    max-width: none;
                }
            }
        </style>


        <script>
            const dataArtikel = @json($artikel);

            const jsLabelJenis = @json(collect($labelJenis)->map(fn($v) => $v[0]));

            const urlArtikelStore = "{{ route('admin.artikel.store') }}";

            const urlArtikelUpdate =
                "{{ url('/admin/pages/artikel') }}/:id";

            const urlArtikelDestroy =
                "{{ url('/admin/pages/artikel') }}/:id";

            const urlGambarArtikel =
                "{{ asset('uploads/artikel') }}";


            /*
            |--------------------------------------------------------------------------
            | HELPER MODAL
            |--------------------------------------------------------------------------
            */

            function bukaModal(id) {
                const modal = document.getElementById(id);

                if (modal) {
                    modal.classList.add('active');
                }
            }


            function tutupModal(id) {
                const modal = document.getElementById(id);

                if (modal) {
                    modal.classList.remove('active');
                }
            }


            /*
            |--------------------------------------------------------------------------
            | TOAST
            |--------------------------------------------------------------------------
            */

            function showToast(title, text, type = 'success') {

                const wrap =
                    document.getElementById('toastWrap');

                if (!wrap) return;

                const toast =
                    document.createElement('div');

                toast.className =
                    'toast' +
                    (type === 'error' ? ' toast--error' : '');

                const iconPath =
                    type === 'error' ?
                    '<path d="M18 6 6 18M6 6l12 12"></path>' :
                    '<path d="M20 6 9 17l-5-5"></path>';

                toast.innerHTML = `
                    <div class="toast-icon">
                        <svg viewBox="0 0 24 24">
                            ${iconPath}
                        </svg>
                    </div>

                    <div class="toast-body">
                        <p class="toast-title">
                            ${escapeHtml(title)}
                        </p>

                        <p class="toast-text">
                            ${escapeHtml(text)}
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
                    if (toast) {
                        toast.remove();
                    }
                }


                const closeButton =
                    toast.querySelector('.toast-close');

                if (closeButton) {
                    closeButton.addEventListener(
                        'click',
                        hapusToast
                    );
                }


                wrap.appendChild(toast);

                setTimeout(
                    hapusToast,
                    3500
                );
            }


            /*
            |--------------------------------------------------------------------------
            | ERROR FORM
            |--------------------------------------------------------------------------
            */

            function clearFormErrors(fields) {

                fields.forEach(function(field) {

                    const el =
                        document.getElementById(
                            'err_' + field
                        );

                    if (el) {
                        el.textContent = '';
                    }

                });
            }


            function tampilkanFormErrors(errors, fields) {

                fields.forEach(function(field) {

                    if (
                        errors &&
                        errors[field]
                    ) {

                        const el =
                            document.getElementById(
                                'err_' + field
                            );

                        if (el) {
                            el.textContent =
                                errors[field][0];
                        }

                    }

                });
            }


            /*
            |--------------------------------------------------------------------------
            | DOM READY
            |--------------------------------------------------------------------------
            */

            document.addEventListener(
                'DOMContentLoaded',
                function() {

                    @if (session('success'))

                        showToast(
                            'Berhasil',
                            @json(session('success'))
                        );
                    @endif


                    const kategoriSelect =
                        document.getElementById(
                            'kategoriArtikel'
                        );

                    if (kategoriSelect) {
                        kategoriSelect.addEventListener(
                            'change',
                            jalankanFilter
                        );
                    }


                    const searchInput =
                        document.getElementById(
                            'searchArtikel'
                        );

                    const statusSelect =
                        document.getElementById(
                            'statusArtikel'
                        );


                    if (searchInput) {
                        searchInput.addEventListener(
                            'keyup',
                            jalankanFilter
                        );
                    }


                    if (statusSelect) {
                        statusSelect.addEventListener(
                            'change',
                            jalankanFilter
                        );
                    }


                    document
                        .querySelectorAll('.artikel-page .modal-overlay')
                        .forEach(function(overlay) {

                            overlay.addEventListener(
                                'click',
                                function(e) {

                                    if (
                                        e.target === overlay
                                    ) {

                                        overlay.classList.remove(
                                            'active'
                                        );

                                    }

                                }
                            );

                        });


                    document.addEventListener(
                        'keydown',
                        function(e) {

                            if (e.key === 'Escape') {

                                document
                                    .querySelectorAll(
                                        '.artikel-page .modal-overlay.active'
                                    )
                                    .forEach(
                                        function(modal) {

                                            modal.classList.remove(
                                                'active'
                                            );

                                        }
                                    );

                            }

                        }
                    );


                    const previewSelector =
                        document.getElementById(
                            'previewSelector'
                        );

                    if (
                        previewSelector &&
                        previewSelector.value
                    ) {

                        renderPreview(
                            previewSelector.value
                        );

                    } else {

                        renderPreview('');

                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | FILTER
            |--------------------------------------------------------------------------
            */

            function jalankanFilter() {

                const searchElement =
                    document.getElementById(
                        'searchArtikel'
                    );

                const statusElement =
                    document.getElementById(
                        'statusArtikel'
                    );

                const kategoriElement =
                    document.getElementById(
                        'kategoriArtikel'
                    );


                const keyword =
                    (
                        searchElement?.value ||
                        ''
                    )
                    .toLowerCase()
                    .trim();


                const status =
                    statusElement?.value ||
                    'semua';


                const kategori =
                    kategoriElement?.value ||
                    'semua';


                const rows =
                    document.querySelectorAll(
                        '#artikelTableBody tr[data-judul]'
                    );


                let tampil = 0;


                rows.forEach(function(row) {

                    const judul =
                        row.dataset.judul || '';

                    const rowStatus =
                        row.dataset.status || '';

                    const rowKategori =
                        row.dataset.kategori || '';


                    const cocokJudul = !keyword ||
                        judul.includes(keyword);

                    const cocokStatus =
                        status === 'semua' ||
                        rowStatus === status;

                    const cocokKategori =
                        kategori === 'semua' ||
                        rowKategori === kategori;


                    const cocok =
                        cocokJudul &&
                        cocokStatus &&
                        cocokKategori;


                    row.style.display =
                        cocok ? '' : 'none';


                    if (cocok) {
                        tampil++;
                    }

                });


                const kosong =
                    document.getElementById(
                        'artikelKosongCari'
                    );


                if (kosong) {

                    kosong.style.display =
                        (
                            rows.length > 0 &&
                            tampil === 0
                        ) ?
                        '' :
                        'none';

                }


                const info =
                    document.getElementById(
                        'jumlahTampil'
                    );


                if (info) {
                    info.textContent = tampil;
                }

            }


            /*
            |--------------------------------------------------------------------------
            | PREVIEW GAMBAR
            |--------------------------------------------------------------------------
            */

            function previewGambar(input) {

                const wrap =
                    document.getElementById(
                        'gambarPreviewWrap'
                    );

                const img =
                    document.getElementById(
                        'gambarPreviewImg'
                    );


                if (
                    input.files &&
                    input.files[0]
                ) {

                    const reader =
                        new FileReader();


                    reader.onload =
                        function(e) {

                            img.src =
                                e.target.result;

                            wrap.style.display =
                                'block';

                        };


                    reader.readAsDataURL(
                        input.files[0]
                    );

                } else {

                    wrap.style.display =
                        'none';

                }

            }


            /*
            |--------------------------------------------------------------------------
            | PREVIEW MOBILE
            |--------------------------------------------------------------------------
            */

            function renderPreview(id) {

                const body =
                    document.getElementById(
                        'phonePreviewBody'
                    );

                if (!body) return;


                const item =
                    dataArtikel.find(
                        function(a) {
                            return String(a.id_artikel) === String(id);
                        }
                    );


                if (!item) {

                    body.innerHTML =
                        '<div class="phone-empty">Belum ada konten untuk ditampilkan.</div>';

                    return;
                }


                let gambarHtml = '';


                if (item.gambar) {

                    gambarHtml = `
                        <img
                            src="${urlGambarArtikel}/${encodeURIComponent(item.gambar)}"
                            class="phone-image"
                            alt="${escapeHtml(item.judul)}">
                    `;

                } else {

                    gambarHtml =
                        '<div class="phone-image phone-image--kosong">Tidak ada gambar</div>';

                }


                const jenisLabel =
                    jsLabelJenis[item.jenis] ||
                    'Edukasi Sampah';


                let metaHtml =
                    '♻ ' +
                    escapeHtml(jenisLabel);


                if (item.tanggal_publish) {

                    // Format tanggal menggunakan angka saja (DD/MM/YYYY)
                    const parts = item.tanggal_publish.split('-');
                    const formattedDate = parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : item.tanggal_publish;

                    metaHtml +=
                        ' &nbsp;•&nbsp; ' +
                        formattedDate;

                } else {

                    metaHtml +=
                        ' <span class="phone-badge-draft">Draft</span>';

                }


                const kontenPolos =
                    (
                        item.konten || ''
                    )
                    .replace(
                        /<[^>]*>/g,
                        ''
                    );


                const kontenRingkas =
                    kontenPolos.length > 500 ?
                    kontenPolos.slice(0, 500).trim() + '...' :
                    kontenPolos;


                body.innerHTML = `
                    ${gambarHtml}

                    <div class="phone-title">
                        ${escapeHtml(item.judul)}
                    </div>

                    <div class="phone-meta">
                        ${metaHtml}
                    </div>

                    <div class="phone-content">
                        ${escapeHtml(kontenRingkas)}
                    </div>

                    <div class="phone-tip">
                        💡 <strong>Tips</strong><br>
                        Pilah sampah organik dan anorganik mulai dari rumah sebelum disetorkan ke Bank Sampah.
                    </div>
                `;
            }


            /*
            |--------------------------------------------------------------------------
            | COMBO PREVIEW
            |--------------------------------------------------------------------------
            */

            function initComboPreview() {

                const select =
                    document.getElementById(
                        'previewSelector'
                    );

                const input =
                    document.getElementById(
                        'cariPreview'
                    );

                const list =
                    document.getElementById(
                        'listPreview'
                    );


                if (
                    !select ||
                    !input ||
                    !list
                ) {
                    return;
                }


                const data =
                    Array.from(select.options)
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


                function render(keyword) {

                    const k =
                        (
                            keyword ||
                            ''
                        )
                        .toLowerCase()
                        .trim();


                    const hasil =
                        data.filter(
                            function(d) {
                                return d.label
                                    .toLowerCase()
                                    .includes(k);
                            }
                        );


                    aktif = -1;


                    list.innerHTML =
                        hasil.length ?
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
                        .join('') :
                        '<div class="combo-empty">Konten tidak ditemukan</div>';
                }


                function pilih(value) {

                    const d =
                        data.find(
                            function(x) {
                                return x.value === String(value);
                            }
                        );


                    if (!d) return;


                    select.value =
                        d.value;

                    input.value =
                        d.label;

                    list.classList.remove(
                        'show'
                    );

                    renderPreview(
                        d.value
                    );
                }


                function sorot(arah) {

                    const items =
                        list.querySelectorAll(
                            '.combo-item'
                        );


                    if (!items.length) return;


                    aktif =
                        (
                            aktif +
                            arah +
                            items.length
                        ) %
                        items.length;


                    items.forEach(
                        function(el, i) {

                            el.classList.toggle(
                                'is-active',
                                i === aktif
                            );

                        }
                    );


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

                        list.classList.add(
                            'show'
                        );

                    }
                );


                input.addEventListener(
                    'input',
                    function() {

                        render(
                            this.value
                        );

                        list.classList.add(
                            'show'
                        );

                    }
                );


                input.addEventListener(
                    'keydown',
                    function(e) {

                        if (
                            e.key ===
                            'ArrowDown'
                        ) {

                            e.preventDefault();


                            if (
                                !list.classList.contains(
                                    'show'
                                )
                            ) {

                                render(
                                    this.value
                                );

                                list.classList.add(
                                    'show'
                                );

                            }


                            sorot(1);

                        } else if (
                            e.key ===
                            'ArrowUp'
                        ) {

                            e.preventDefault();

                            sorot(-1);

                        } else if (
                            e.key ===
                            'Enter'
                        ) {

                            const items =
                                list.querySelectorAll(
                                    '.combo-item'
                                );


                            if (
                                list.classList.contains(
                                    'show'
                                ) &&
                                aktif > -1 &&
                                items[aktif]
                            ) {

                                e.preventDefault();

                                pilih(
                                    items[aktif]
                                    .dataset.value
                                );

                            }

                        } else if (
                            e.key ===
                            'Escape'
                        ) {

                            list.classList.remove(
                                'show'
                            );

                        }

                    }
                );


                list.addEventListener(
                    'mousedown',
                    function(e) {

                        const item =
                            e.target.closest(
                                '.combo-item'
                            );


                        if (!item) return;


                        e.preventDefault();


                        pilih(
                            item.dataset.value
                        );

                    }
                );


                document.addEventListener(
                    'click',
                    function(e) {

                        if (
                            !e.target.closest(
                                '#comboPreview'
                            )
                        ) {

                            list.classList.remove(
                                'show'
                            );

                        }

                    }
                );


                if (select.value) {

                    const selected =
                        data.find(
                            function(d) {
                                return d.value === select.value;
                            }
                        );


                    if (selected) {
                        input.value =
                            selected.label;
                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | TAMBAH KONTEN
            |--------------------------------------------------------------------------
            */

            function bukaModalTambah() {

                const form =
                    document.getElementById(
                        'formArtikel'
                    );


                if (form) {
                    form.reset();
                }


                document.getElementById(
                    'artikelId'
                ).value = '';


                document.getElementById(
                    'artikelJenis'
                ).value = '';


                document.getElementById(
                    'gambarPreviewWrap'
                ).style.display = 'none';


                document.getElementById(
                    'gambarLamaInfo'
                ).innerHTML = '';


                clearFormErrors([
                    'judul',
                    'jenis',
                    'gambar',
                    'konten',
                    'tanggal'
                ]);


                document.getElementById(
                        'modalArtikelTitle'
                    ).lastChild.textContent =
                    ' Tambah Konten';


                bukaModal(
                    'modalArtikel'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | EDIT KONTEN
            |--------------------------------------------------------------------------
            */

            function editArtikelModal(id) {

                const artikel =
                    dataArtikel.find(
                        function(item) {

                            return Number(
                                item.id_artikel
                            ) === Number(id);

                        }
                    );


                if (!artikel) {

                    showToast(
                        'Gagal',
                        'Data konten tidak ditemukan.',
                        'error'
                    );

                    return;
                }


                document.getElementById(
                    'formArtikel'
                ).reset();


                clearFormErrors([
                    'judul',
                    'jenis',
                    'gambar',
                    'konten',
                    'tanggal'
                ]);


                document.getElementById(
                        'artikelId'
                    ).value =
                    artikel.id_artikel;


                document.getElementById(
                        'artikelJudul'
                    ).value =
                    artikel.judul ?? '';


                document.getElementById(
                        'artikelJenis'
                    ).value =
                    artikel.jenis ?? 'edukasi';


                document.getElementById(
                        'artikelKonten'
                    ).value =
                    artikel.konten ?? '';


                document.getElementById(
                        'artikelTanggal'
                    ).value =
                    artikel.tanggal_publish ?? '';


                document.getElementById(
                        'gambarPreviewWrap'
                    ).style.display =
                    'none';


                const gambarLama =
                    document.getElementById(
                        'gambarLamaInfo'
                    );


                if (artikel.gambar) {

                    gambarLama.innerHTML = `
                        <img
                            src="${urlGambarArtikel}/${encodeURIComponent(artikel.gambar)}"
                            alt="Gambar lama">

                        <small>
                            Gambar saat ini.
                            Pilih file baru untuk menggantinya.
                        </small>
                    `;

                } else {

                    gambarLama.innerHTML =
                        '<small>Belum ada gambar.</small>';

                }


                document.getElementById(
                        'modalArtikelTitle'
                    ).lastChild.textContent =
                    ' Edit Konten';


                bukaModal(
                    'modalArtikel'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | SIMPAN / UPDATE
            |--------------------------------------------------------------------------
            */

            function simpanArtikel(aksi) {

                aksi =
                    aksi === 'draft' ?
                    'draft' :
                    'publish';


                const id =
                    document.getElementById(
                        'artikelId'
                    ).value;


                clearFormErrors([
                    'judul',
                    'jenis',
                    'gambar',
                    'konten',
                    'tanggal'
                ]);


                const formData =
                    new FormData();


                formData.append(
                    'judul',
                    document.getElementById(
                        'artikelJudul'
                    ).value.trim()
                );


                formData.append(
                    'jenis',
                    document.getElementById(
                        'artikelJenis'
                    ).value
                );


                formData.append(
                    'konten',
                    document.getElementById(
                        'artikelKonten'
                    ).value
                );


                let tanggalPublish = '';


                if (aksi === 'publish') {

                    tanggalPublish =
                        document.getElementById(
                            'artikelTanggal'
                        ).value;


                    if (!tanggalPublish) {

                        const sekarang =
                            new Date();


                        tanggalPublish =
                            sekarang.getFullYear() +
                            '-' +
                            String(
                                sekarang.getMonth() + 1
                            ).padStart(2, '0') +
                            '-' +
                            String(
                                sekarang.getDate()
                            ).padStart(2, '0');

                    }

                }


                formData.append(
                    'tanggal_publish',
                    tanggalPublish
                );


                const file =
                    document.getElementById(
                        'artikelGambar'
                    ).files[0];


                if (file) {
                    formData.append(
                        'gambar',
                        file
                    );
                }


                let url =
                    urlArtikelStore;


                if (id) {

                    formData.append(
                        '_method',
                        'PUT'
                    );

                    url =
                        urlArtikelUpdate.replace(
                            ':id',
                            id
                        );

                }


                fetch(
                        url, {
                            method: 'POST',

                            headers: {
                                'Accept': 'application/json',

                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]'
                                ).content
                            },

                            body: formData
                        }
                    )
                    .then(
                        async function(response) {

                            const data =
                                await response
                                .json()
                                .catch(
                                    () => ({})
                                );


                            if (!response.ok) {

                                if (data.errors) {

                                    tampilkanFormErrors(
                                        data.errors,
                                        [
                                            'judul',
                                            'jenis',
                                            'gambar',
                                            'konten',
                                            'tanggal'
                                        ]
                                    );

                                }


                                throw new Error(
                                    data.message ||
                                    'Gagal menyimpan artikel.'
                                );

                            }


                            return data;

                        }
                    )
                    .then(
                        function() {

                            tutupModal(
                                'modalArtikel'
                            );


                            if (aksi === 'draft') {

                                showToast(
                                    'Draft berhasil disimpan',
                                    'Artikel berhasil disimpan sebagai draft.'
                                );

                            } else {

                                showToast(
                                    'Berhasil dipublikasikan',
                                    'Artikel berhasil dipublikasikan.'
                                );

                            }


                            setTimeout(
                                function() {
                                    window.location.reload();
                                },
                                800
                            );

                        }
                    )
                    .catch(
                        function(error) {

                            console.error(
                                'simpanArtikel:',
                                error
                            );


                            showToast(
                                'Gagal menyimpan',
                                error.message ||
                                'Terjadi kesalahan saat menyimpan artikel.',
                                'error'
                            );

                        }
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | DETAIL
            |--------------------------------------------------------------------------
            */

            function bukaDetail(id) {

                const artikel =
                    dataArtikel.find(
                        function(item) {

                            return Number(
                                item.id_artikel
                            ) === Number(id);

                        }
                    );


                if (!artikel) {

                    showToast(
                        'Gagal',
                        'Data konten tidak ditemukan.',
                        'error'
                    );

                    return;
                }


                document.getElementById(
                        'detailJudul'
                    ).textContent =
                    artikel.judul ?? '-';


                let jenisText =
                    'Edukasi Sampah';


                if (
                    artikel.jenis ===
                    'artikel'
                ) {

                    jenisText =
                        'Artikel';

                } else if (
                    artikel.jenis ===
                    'acara'
                ) {

                    jenisText =
                        'Acara';

                }


                if (artikel.tanggal_publish) {

                    const tanggal =
                        new Date(
                            artikel.tanggal_publish +
                            'T00:00:00'
                        );


                    document.getElementById(
                            'detailTanggal'
                        ).textContent =
                        jenisText +
                        ' • Dipublikasikan pada ' +
                        tanggal.toLocaleDateString(
                            'id-ID', {
                                day: '2-digit',
                                month: 'long',
                                year: 'numeric'
                            }
                        );

                } else {

                    document.getElementById(
                            'detailTanggal'
                        ).textContent =
                        jenisText +
                        ' • Status: Draft';

                }


                document.getElementById(
                        'detailKonten'
                    ).textContent =
                    artikel.konten ?? '-';


                const detailGambar =
                    document.getElementById(
                        'detailGambar'
                    );


                if (artikel.gambar) {

                    detailGambar.innerHTML = `
                        <img
                            src="${urlGambarArtikel}/${encodeURIComponent(artikel.gambar)}"
                            class="detail-image"
                            alt="${escapeHtml(artikel.judul)}">
                    `;

                } else {

                    detailGambar.innerHTML = '';

                }


                bukaModal(
                    'modalDetail'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | ESCAPE HTML
            |--------------------------------------------------------------------------
            */

            function escapeHtml(text) {

                const div =
                    document.createElement(
                        'div'
                    );


                div.textContent =
                    text ?? '';


                return div.innerHTML;

            }


            /*
            |--------------------------------------------------------------------------
            | HAPUS
            |--------------------------------------------------------------------------
            */

            let hapusContext = null;


            function hapusArtikel(
                id,
                judul
            ) {

                hapusContext = {
                    id: id,
                    judul: judul
                };


                document.getElementById(
                        'hapusJudul'
                    ).textContent =
                    judul || '-';


                bukaModal(
                    'modalHapus'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | KONFIRMASI HAPUS
            |--------------------------------------------------------------------------
            */

            function konfirmasiHapus() {

                if (!hapusContext) {
                    return;
                }


                fetch(
                        urlArtikelDestroy.replace(
                            ':id',
                            hapusContext.id
                        ), {
                            method: 'DELETE',

                            headers: {
                                'Accept': 'application/json',

                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]'
                                ).content
                            }
                        }
                    )
                    .then(
                        async function(res) {

                            if (!res.ok) {

                                const err =
                                    await res
                                    .json()
                                    .catch(
                                        () => ({})
                                    );


                                throw new Error(
                                    err.message ||
                                    'Gagal menghapus konten.'
                                );

                            }


                            return res.json();

                        }
                    )
                    .then(
                        function() {

                            tutupModal(
                                'modalHapus'
                            );


                            showToast(
                                'Berhasil dihapus',
                                `Konten "${hapusContext.judul}" telah dihapus.`
                            );


                            hapusContext =
                                null;


                            setTimeout(
                                function() {
                                    window.location.reload();
                                },
                                800
                            );

                        }
                    )
                    .catch(
                        function(err) {

                            showToast(
                                'Gagal menghapus',
                                err.message,
                                'error'
                            );

                        }
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | INIT DRAFT + COMBO PREVIEW
            |--------------------------------------------------------------------------
            */

            document.addEventListener(
                'DOMContentLoaded',
                function() {

                    const btnDraft =
                        document.getElementById(
                            'btnSimpanDraft'
                        );


                    if (btnDraft) {

                        btnDraft.addEventListener(
                            'click',
                            function(e) {

                                e.preventDefault();
                                e.stopPropagation();

                                simpanArtikel(
                                    'draft'
                                );

                            }
                        );

                    }


                    initComboPreview();


                    const previewSelector =
                        document.getElementById(
                            'previewSelector'
                        );


                    if (
                        previewSelector &&
                        previewSelector.value
                    ) {

                        renderPreview(
                            previewSelector.value
                        );

                    } else {

                        renderPreview('');

                    }

                }
            );
        </script>

    </div>

@endsection
