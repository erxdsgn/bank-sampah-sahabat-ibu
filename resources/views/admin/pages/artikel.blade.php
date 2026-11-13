@extends('admin.layouts.app')

@section('title', 'Artikel & Edukasi')
@section('active', 'artikel')
@section('crumbs', 'Konten & Laporan | Artikel & Edukasi')

@section('content')

@php
    $totalKonten = $artikel->count();
    $totalPublikasi = $artikel->whereNotNull('tanggal_publish')->count();
    $totalDraft = $artikel->whereNull('tanggal_publish')->count();
    $totalAcara = $artikel->where('jenis', 'event')->count();

    $labelJenis = [
        'edukasi' => ['Edukasi Sampah', 'badge--info'],
        'artikel' => ['Artikel', 'badge--info'],
        'event' => ['Event', 'badge--purple'],
    ];

    $preview = $artikel->whereNotNull('tanggal_publish')->first() ?? $artikel->first();
@endphp

{{-- CDN CKEDITOR 5 --}}
<script src="https://cdn.jsdelivr.net/npm/@ckeditor/ckeditor5-build-classic@41.4.2/build/ckeditor.js"></script>

<div class="artikel-page">

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">KONTEN & LAPORAN</span>

            <h1 class="hero-title">
                Artikel & <span class="accent">Edukasi</span>
            </h1>

            <p class="hero-sub">
                Kelola edukasi, artikel, dan informasi yang ditampilkan pada aplikasi masyarakat dengan nyaman.
            </p>
        </div>

        <div class="hero-actions" id="heroActionBtn">
            <button class="btn btn--primary" type="button" onclick="alihKeForm('tambah')">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"></path>
                </svg>
                Tambah Konten Baru
            </button>
        </div>
    </section>


    {{-- SUMMARY --}}
    <section class="summary-grid" id="wrapperSummary">

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

            <div class="summary-info">Seluruh artikel & edukasi</div>
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

            <div class="summary-info">Sudah tampil di aplikasi warga</div>
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

            <div class="summary-info">Belum ditayangkan</div>
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

            <div class="summary-info">Segera hadir</div>
        </div>

    </section>


    {{-- VIEW UTAMA: DAFTAR KONTEN & PREVIEW MOBILE --}}
    <div class="artikel-columns" id="viewDaftar">

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

                    <div class="combo combo--arrow" id="combo_kategoriArtikel">
                        <input type="text" id="cari_kategoriArtikel" class="combo-input" autocomplete="off" aria-label="Filter kategori">
                        <div class="combo-list" id="list_kategoriArtikel"></div>
                    </div>
                    <select id="kategoriArtikel" class="combo-hidden" tabindex="-1" aria-hidden="true">
                        <option value="semua" selected>Semua Kategori</option>
                        <option value="edukasi">Edukasi Sampah</option>
                        <option value="artikel">Artikel</option>
                        <option value="event">Event</option>
                    </select>

                    <div class="combo combo--arrow" id="combo_statusArtikel">
                        <input type="text" id="cari_statusArtikel" class="combo-input" autocomplete="off" aria-label="Filter status">
                        <div class="combo-list" id="list_statusArtikel"></div>
                    </div>
                    <select id="statusArtikel" class="combo-hidden" tabindex="-1" aria-hidden="true">
                        <option value="semua" selected>Semua Status</option>
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
                                [$labelKategori, $kelasKategori] = $labelJenis[$item->jenis] ?? ['Edukasi Sampah', 'badge--info'];
                            @endphp

                            <tr data-judul="{{ strtolower($item->judul) }}" data-status="{{ $status }}" data-kategori="{{ $item->jenis }}">
                                <td>
                                    @if ($item->gambar)
                                        <img src="{{ Str::startsWith($item->gambar, 'http') ? $item->gambar : asset('uploads/artikel/' . $item->gambar) }}"
                                            alt="{{ $item->judul }}" class="artikel-thumb">
                                    @else
                                        <div class="artikel-thumb artikel-thumb--kosong">Tidak ada gambar</div>
                                    @endif
                                </td>

                                <td>
                                    <div class="artikel-judul">{{ $item->judul }}</div>
                                    <div class="artikel-ringkasan">{{ \Illuminate\Support\Str::limit(strip_tags($item->konten), 30) }}</div>
                                </td>

                                <td>
                                    <span class="badge {{ $kelasKategori }}">{{ $labelKategori }}</span>
                                </td>

                                <td>
                                    @if ($item->tanggal_publish)
                                        <span class="badge badge--success">Dipublikasikan</span>
                                    @else
                                        <span class="badge badge--warning">Draft</span>
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
                                        <button class="icon-btn" title="Lihat" type="button" onclick="bukaDetail({{ $item->id_artikel }})">
                                            <svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path><circle cx="12" cy="12" r="2.5"></circle></svg>
                                        </button>
                                        <button class="icon-btn" title="Edit" type="button" onclick="editArtikelForm({{ $item->id_artikel }})">
                                            <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path></svg>
                                        </button>
                                        <button class="icon-btn danger" title="Hapus" type="button" onclick="hapusArtikel({{ $item->id_artikel }}, {{ Js::from($item->judul) }})">
                                            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6M14 11v6"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr class="empty-row">
                                <td colspan="6">
                                    <div class="empty-icon">📄</div>
                                    <strong>Belum Ada Konten</strong>
                                    <p class="empty-text">Silakan tambahkan artikel atau edukasi pertama.</p>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

                <div class="catalog-empty" id="artikelKosongCari">
                    <div class="empty-icon">🔍</div>
                    <strong>Data Tidak Ditemukan</strong>
                    <p class="empty-text">Tidak ada konten yang cocok dengan pencarian atau filter ini.</p>
                </div>

            </div>


            <div class="table-footer">
                <div class="table-info" id="tableInfoPagination">Menampilkan data...</div>
                <div class="table-pagination-controls">
                    <div class="per-page">
                        <label for="cari_perPageSelect">Konten per halaman:</label>
                        <div class="combo combo--up combo--arrow" id="combo_perPageSelect">
                            <input type="text" id="cari_perPageSelect" class="combo-input" autocomplete="off">
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


        {{-- PREVIEW MOBILE --}}
        <section class="card preview-card">
            <div class="preview-head">
                <h3 class="card-title">Preview Aplikasi Mobile</h3>
            </div>

            <div class="form-group preview-select-group">
                <div class="combo" id="comboPreview">
                    <input type="text" id="cariPreview" class="combo-input" placeholder="Ketik judul konten..." autocomplete="off" {{ $artikel->isEmpty() ? 'disabled' : '' }}>
                    <div class="combo-list" id="listPreview"></div>
                </div>
                <select id="previewSelector" class="combo-hidden" tabindex="-1" aria-hidden="true">
                    @forelse($artikel as $item)
                        <option value="{{ $item->id_artikel }}" @selected($preview && $preview->id_artikel === $item->id_artikel)>
                            {{ $item->judul }} {{ $item->tanggal_publish ? '' : '(Draft)' }}
                        </option>
                    @empty
                        <option value="">Belum ada konten</option>
                    @endforelse
                </select>
            </div>

            <div class="phone">
                <div class="phone-header">← &nbsp; Edukasi Sampah</div>
                <div class="phone-body" id="phonePreviewBody"></div>
            </div>
        </section>

    </div>


    {{-- VIEW KEDUA: FORM FULL PAGE --}}
    <div class="card form-full-view" id="viewForm" style="display: none;">
        <div class="form-full-header">
            <div>
                <h2 class="card-title" id="formTitleText">Formulir Konten Artikel & Edukasi</h2>
                <p class="card-sub">Tulis materi secara profesional dan lengkapi media pendukung.</p>
            </div>
            <button class="btn btn--ghost" type="button" onclick="tutupFormFull()">
                ← Kembali ke Daftar
            </button>
        </div>

        <form id="formArtikel" onsubmit="return false;" class="form-full-body">
            <input type="hidden" id="artikelId">

            <div class="form-grid-columns">
                <div class="form-group form-group--full">
                    <label for="artikelJudul">Judul Konten <span class="text-danger">*</span></label>
                    <input type="text" id="artikelJudul" class="form-control" placeholder="Contoh: Cara Memilah Sampah dari Rumah" required>
                    <span class="form-error" id="err_judul"></span>
                </div>

                <div class="form-group">
                    <label for="artikelJenis">Jenis / Kategori Konten <span class="text-danger">*</span></label>
                    <select id="artikelJenis" class="form-control" required>
                        <option value="">-- Pilih Jenis Konten --</option>
                        <option value="edukasi">Edukasi Sampah</option>
                        <option value="artikel">Artikel</option>
                        <option value="event">Event</option>
                    </select>
                    <span class="form-error" id="err_jenis"></span>
                </div>

                <div class="form-group">
                    <label for="artikelTanggal">Tanggal Publikasi</label>
                    <input type="date" id="artikelTanggal" class="form-control">
                    <span class="field-hint">Kosongkan jika ingin menjadikannya sebagai draft.</span>
                    <span class="form-error" id="err_tanggal"></span>
                </div>

                <div class="form-group form-group--full">
                    <label for="artikelGambar">Gambar Utama / Thumbnail</label>
                    <input type="file" id="artikelGambar" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewGambar(this)">
                    <input type="hidden" id="artikelGambarUrl" name="gambar_url">

                    {{-- Panduan Ukuran & Format Gambar --}}
                    <div class="field-hint" style="margin-top: 6px; line-height: 1.5;">
                        <span style="display: inline-block; font-weight: 600; color: #22c55e; margin-bottom: 2px;">📐 Panduan Ukuran Gambar:</span><br>
                        • Format: <strong>JPG, PNG, atau WEBP</strong><br>
                        • Rekomendasi Resolusi: <strong>1200 x 630 piksel</strong> (Rasio 16:9 atau Landscape)<br>
                        • Ukuran Maksimal File: <strong>2 MB</strong>
                    </div>

                    <span class="form-error" id="err_gambar"></span>

                    <div class="gambar-preview" id="gambarPreviewWrap">
                        <img id="gambarPreviewImg" src="" alt="Preview">
                    </div>
                    <div id="gambarLamaInfo"></div>
                </div>

                {{-- CKEDITOR CONTAINER --}}
                <div class="form-group form-group--full">
                    <div class="editor-label-wrap">
                        <label for="editorKonten">Isi Konten / Materi <span class="text-danger">*</span></label>
                    </div>
                    <textarea id="editorKonten" class="form-control"></textarea>
                    <span class="form-error" id="err_konten"></span>
                </div>
            </div>

            <div class="form-full-footer">
                <button class="btn btn--ghost" type="button" onclick="tutupFormFull()">Batal</button>
                <button type="button" class="btn btn--outline" onclick="simpanArtikel('draft')">Simpan sebagai Draft</button>
                <button class="btn btn--primary" type="button" onclick="simpanArtikel('publish')">Publikasikan Konten</button>
            </div>
        </form>
    </div>


    {{-- TOAST --}}
    <div class="toast-wrap" id="toastWrap"></div>


    {{-- MODAL DETAIL --}}
    <div class="modal-overlay" id="modalDetail">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path><circle cx="12" cy="12" r="2.5"></circle></svg>
                    Detail Konten
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalDetail')">&times;</button>
            </div>
            <div class="modal-body">
                <div id="detailGambar"></div>
                <div class="detail-title" id="detailJudul">-</div>
                <div class="detail-date" id="detailTanggal">-</div>
                <div class="detail-text" id="detailKonten">-</div>
            </div>
            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalDetail')">Tutup</button>
            </div>
        </div>
    </div>


    {{-- MODAL HAPUS --}}
    <div class="modal-overlay" id="modalHapus">
        <div class="modal-box modal-box--sm">
            <div class="modal-body modal-body--center">
                <div class="confirm-icon">
                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6M14 11v6"></path></svg>
                </div>
                <h3 class="confirm-title">Hapus Konten?</h3>
                <p class="confirm-text">Anda akan menghapus konten <strong id="hapusJudul">-</strong>. Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-foot modal-foot--center">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHapus')">Batal</button>
                <button class="btn btn--danger" type="button" onclick="konfirmasiHapus()">Ya, Hapus</button>
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
            --table-hover: #071228;
            --input-bg: #111a2c;
            --empty: #111a2c;
            --shadow: 0 8px 25px rgba(0, 0, 0, .25);
        }

        .artikel-page input, .artikel-page select, .artikel-page textarea {
            color-scheme: light;
        }
        html[data-theme="dark"] .artikel-page input, html[data-theme="dark"] .artikel-page select, html[data-theme="dark"] .artikel-page textarea {
            color-scheme: dark;
        }

        /* SUMMARY */
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
            width: 40px; height: 40px; flex: 0 0 40px;
            border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(99, 102, 241, .14);
            color: #a5b4fc;
        }
        .artikel-page .summary-icon--income { background: rgba(34, 197, 94, .14); color: #4ade80; }
        .artikel-page .summary-icon--expense { background: rgba(245, 158, 11, .14); color: #fbbf24; }
        .artikel-page .summary-icon svg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 1.8; }
        .artikel-page .summary-info { margin-top: 12px; font-size: 12px; color: var(--text-secondary); }

        /* 2 COLUMN DAFTAR */
        .artikel-page .artikel-columns {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 300px;
            gap: 16px;
            margin-bottom: 16px;
            align-items: start;
        }

        .artikel-page .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        .artikel-page .card-title {
            margin: 0 0 4px;
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
        }
        .artikel-page .card-sub {
            margin: 0;
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* TOOLBAR */
        .artikel-page .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
        }
        .artikel-page .table-search {
            flex: 1 1 auto;
            min-width: 220px;
            position: relative;
        }
        .artikel-page .table-search svg {
            position: absolute; width: 18px; height: 18px; left: 12px; top: 50%; transform: translateY(-50%);
            fill: none; stroke: var(--text-secondary); stroke-width: 1.8; pointer-events: none;
        }
        .artikel-page .table-search input {
            width: 100%; height: 40px; padding: 0 14px 0 40px;
            border: 1px solid var(--border); border-radius: 8px; outline: none;
            background: var(--input-bg); color: var(--text); font-family: inherit;
        }
        .artikel-page .table-search input:focus {
            border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
        }
        .artikel-page .toolbar-right {
            display: flex; align-items: center; gap: 10px;
        }
        .artikel-page .toolbar-right .combo { width: 150px; }
        .artikel-page .toolbar-right .combo-input { height: 40px; color: var(--text-secondary); }

        /* TABLE */
        .artikel-page .table-responsive { width: 100%; overflow-x: auto; }
        .artikel-page .data-table { width: 100%; border-collapse: collapse; min-width: 760px; }
        .artikel-page .data-table th {
            background: var(--table-head); color: var(--text-secondary);
            font-size: 12px; font-weight: 700; text-transform: uppercase;
            padding: 13px 16px; text-align: left; border-bottom: 1px solid var(--border);
        }
        .artikel-page .data-table td {
            padding: 14px 16px; border-bottom: 1px solid var(--border);
            font-size: 13px; color: var(--text-secondary); vertical-align: middle; background: var(--table-row);
        }
        .artikel-page .data-table tbody tr:hover td { background: var(--table-hover); }
        .artikel-page .nowrap { white-space: nowrap; }

        .artikel-page .artikel-thumb {
            width: 65px; height: 50px; border-radius: 8px; object-fit: cover;
            background: var(--surface-secondary); border: 1px solid var(--border);
        }
        .artikel-page .artikel-thumb--kosong {
            display: flex; align-items: center; justify-content: center;
            color: var(--text-muted); font-size: 10px; text-align: center; padding: 4px;
        }
        .artikel-page .artikel-judul { color: var(--text); font-weight: 700; margin-bottom: 3px; line-height: 1.35; }
        .artikel-page .artikel-ringkasan {
            color: var(--text-secondary); font-size: 12px; width: 230px;
            display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden;
        }

        .artikel-page .table-actions { display: flex; gap: 6px; }
        .artikel-page .icon-btn {
            width: 34px; height: 34px; border: 1px solid var(--border);
            background: var(--surface-secondary); color: var(--text-secondary);
            border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;
        }
        .artikel-page .icon-btn:hover { background: var(--table-hover); color: var(--text); border-color: #3a4962; }
        .artikel-page .icon-btn svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 1.8; }
        .artikel-page .icon-btn.danger { color: #f87171; }
        .artikel-page .icon-btn.danger:hover { background: rgba(239, 68, 68, .12); border-color: rgba(239, 68, 68, .30); color: #fca5a5; }

        .artikel-page .table-footer {
            display: flex; justify-content: space-between; align-items: center;
            gap: 12px; padding: 16px 24px; flex-wrap: wrap;
        }
        .artikel-page .table-info { font-size: 13px; color: var(--text-secondary); }
        .artikel-page .table-pagination-controls { display: flex; align-items: center; gap: 12px; }
        .artikel-page .per-page { display: flex; align-items: center; gap: 6px; }
        .artikel-page .per-page label { font-size: 13px; color: var(--text-secondary); }
        .artikel-page .per-page .combo { width: 75px; }
        .artikel-page .pagination-buttons { display: flex; gap: 4px; align-items: center; }
        .artikel-page .pagination-buttons .btn { padding: 6px 12px; min-width: 32px; }

        .artikel-page .catalog-empty, .artikel-page .empty-row td { display: none; text-align: center; padding: 40px; }
        .artikel-page .empty-icon { font-size: 30px; margin-bottom: 10px; }
        .artikel-page .empty-text { margin: 5px 0 0; color: var(--text-secondary); font-size: 13px; }

        /* BADGES */
        .artikel-page .badge { display: inline-flex; align-items: center; padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .artikel-page .badge--success { background: rgba(34, 197, 94, .14); color: #4ade80; }
        .artikel-page .badge--warning { background: rgba(245, 158, 11, .14); color: #fbbf24; }
        .artikel-page .badge--info { background: rgba(14, 165, 233, .14); color: #38bdf8; }
        .artikel-page .badge--purple { background: rgba(168, 85, 247, .14); color: #c084fc; }

        /* PREVIEW MOBILE */
        .artikel-page .preview-card { padding: 20px; }
        .artikel-page .preview-head { margin-bottom: 14px; }
        .artikel-page .preview-select-group { margin-bottom: 14px; }

        .artikel-page .phone {
            width: 100%; max-width: 220px; height: 440px; border: 7px solid #263449;
            border-radius: 28px; overflow: hidden; margin: 0 auto; background: #f8fafc;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .30);
        }
        .artikel-page .phone-header { background: #15803d; color: #fff; padding: 14px 12px; font-size: 12px; font-weight: 600; }
        .artikel-page .phone-body { padding: 12px; height: calc(100% - 42px); overflow-y: auto; background: #f8fafc; }
        .artikel-page .phone-image { width: 100%; height: 110px; border-radius: 8px; object-fit: cover; margin-bottom: 10px; }
        .artikel-page .phone-image--kosong { display: flex; align-items: center; justify-content: center; background: #e5ece8; color: #71817b; font-size: 10px; }
        .artikel-page .phone-title { color: #1f2937; font-size: 15px; line-height: 1.3; font-weight: 700; }
        .artikel-page .phone-meta { color: #15803d; font-size: 9px; margin: 6px 0 10px; }
        .artikel-page .phone-meta .phone-badge-draft { background: #fef3c7; color: #b45309; padding: 1px 5px; border-radius: 10px; font-weight: 700; }
        .artikel-page .phone-content { color: #66758a; font-size: 10px; line-height: 1.6; }
        .artikel-page .phone-tip { background: #e8f5ed; color: #166534; padding: 8px; border-radius: 6px; font-size: 9px; margin-top: 12px; }
        .artikel-page .phone-empty { text-align: center; color: #9aa6b5; padding-top: 120px; font-size: 11px; }

        /* =========================================================
               FULL PAGE FORM
            ========================================================= */
        .artikel-page .form-full-view {
            padding: 24px;
            animation: fadeIn .2s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .artikel-page .form-full-header {
            display: flex; justify-content: space-between; align-items: center;
            padding-bottom: 20px; border-bottom: 1px solid var(--border); margin-bottom: 22px;
        }
        .artikel-page .form-full-body { display: flex; flex-direction: column; gap: 20px; }
        .artikel-page .form-grid-columns {
            display: grid; grid-template-columns: 1fr 1fr; gap: 18px;
        }
        .artikel-page .form-group { display: flex; flex-direction: column; gap: 6px; }
        .artikel-page .form-group--full { grid-column: 1 / -1; }
        .artikel-page .form-group label { font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); }
        .artikel-page .text-danger { color: #ef4444; }

        .artikel-page .form-control {
            width: 100%; padding: 11px 14px; font-size: 13.5px; color: var(--text);
            border: 1px solid var(--border); border-radius: 8px; outline: none; background: var(--input-bg);
            font-family: inherit; transition: border-color .15s ease, box-shadow .15s ease;
        }
        .artikel-page .form-control:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34, 197, 94, .10); }

        /* STYLES KHUSUS CKEDITOR */
        .ck-editor__editable_inline {
            min-height: 320px;
            background: var(--input-bg) !important;
            color: var(--text) !important;
            border-bottom-left-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
            padding: 12px 18px !important;
            font-size: 14px !important;
            line-height: 1.65 !important;
        }
        .ck.ck-toolbar {
            background: var(--surface-secondary) !important;
            border-color: var(--border) !important;
            border-top-left-radius: 8px !important;
            border-top-right-radius: 8px !important;
        }
        .ck.ck-button { color: var(--text) !important; }
        .ck.ck-button:hover { background: var(--border) !important; }

        .artikel-page .form-full-footer {
            display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid var(--border);
        }

        /* GAMBAR PREVIEW */
        .artikel-page .gambar-preview { display: none; margin-top: 10px; }
        .artikel-page .gambar-preview img, .artikel-page #gambarLamaInfo img {
            width: 130px; height: 90px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border);
        }
        .artikel-page #gambarLamaInfo small { display: block; margin-top: 6px; color: var(--text-muted); font-size: 11px; }

        /* MODAL UMUM */
        .artikel-page .modal-overlay {
            display: none; position: fixed; inset: 0; background: rgba(2, 6, 23, .72);
            align-items: center; justify-content: center; padding: 20px; z-index: 1000;
        }
        .artikel-page .modal-overlay.active { display: flex; }
        .artikel-page .modal-box {
            width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto;
            background: var(--surface); border: 1px solid var(--border); border-radius: 14px; box-shadow: 0 20px 45px rgba(0,0,0,.35);
        }
        .artikel-page .modal-box--sm { max-width: 420px; }
        .artikel-page .modal-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid var(--border); }
        .artikel-page .modal-title { margin: 0; font-size: 16px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 10px; }
        .artikel-page .modal-title svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 1.8; }
        .artikel-page .modal-close { border: none; background: transparent; font-size: 20px; color: var(--text-secondary); cursor: pointer; }
        .artikel-page .modal-body { padding: 20px 22px; }
        .artikel-page .modal-body--center { text-align: center; padding-top: 26px; }
        .artikel-page .modal-foot { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 22px; border-top: 1px solid var(--border); }
        .artikel-page .modal-foot--center { justify-content: center; }

        .artikel-page .detail-image { width: 100%; max-height: 250px; object-fit: cover; border-radius: 10px; margin-bottom: 16px; }
        .artikel-page .detail-title { color: var(--text); font-size: 18px; font-weight: 700; margin-bottom: 6px; }
        .artikel-page .detail-date { color: #4ade80; font-size: 12px; margin-bottom: 14px; }
        .artikel-page .detail-text { color: var(--text-secondary); line-height: 1.7; font-size: 13.5px; }

        /* TOAST & COMBO */
        .artikel-page .toast-wrap { position: fixed; top: 20px; right: 20px; z-index: 1100; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
        .artikel-page .toast {
            display: flex; align-items: flex-start; gap: 12px; min-width: 300px; max-width: 380px;
            background: var(--surface); border: 1px solid var(--border); border-left: 4px solid #22c55e;
            border-radius: 10px; box-shadow: 0 12px 30px rgba(0,0,0,.30); padding: 14px 16px; pointer-events: auto;
        }
        .artikel-page .toast.toast--error { border-left-color: #ef4444; }
        .artikel-page .toast-title { font-size: 13px; font-weight: 700; color: var(--text); margin: 0 0 2px; }
        .artikel-page .toast-text { font-size: 12.5px; color: var(--text-secondary); margin: 0; }
        .artikel-page .toast-close { border: none; background: transparent; color: var(--text-muted); font-size: 16px; cursor: pointer; }

        .artikel-page .combo { position: relative; }
        .artikel-page .combo-hidden { display: none !important; }
        .artikel-page .combo-input {
            width: 100% !important; border: 1px solid var(--border) !important; border-radius: 8px !important;
            padding: 9px 12px !important; font-size: 13px !important; font-family: inherit !important;
            background: var(--input-bg) !important; color: var(--text) !important; outline: none !important; cursor: pointer;
        }
        .artikel-page .combo-list {
            display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0;
            max-height: 200px; overflow-y: auto; background: var(--surface); border: 1px solid var(--border);
            border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,.22); z-index: 60;
        }
        .artikel-page .combo-list.show { display: block; }
        .artikel-page .combo-item { padding: 10px 14px; font-size: 13px; color: var(--text); cursor: pointer; }
        .artikel-page .combo-item:hover { background: var(--table-hover); color: #22c55e; }

        @media (max-width: 1100px) {
            .artikel-page .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .artikel-page .artikel-columns { grid-template-columns: 1fr; }
            .artikel-page .preview-card { display: none; }
            .artikel-page .form-grid-columns { grid-template-columns: 1fr; }
        }
    </style>


    <script>
        const dataArtikel = @json($artikel);
        const jsLabelJenis = @json(array_map(fn($v) => $v[0], $labelJenis));
        const urlArtikelStore = "{{ route('admin.artikel.store') }}";
        const urlArtikelUpdate = "{{ url('/admin/pages/artikel') }}/:id";
        const urlArtikelDestroy = "{{ url('/admin/pages/artikel') }}/:id";
        const urlGambarArtikel = "{{ asset('uploads/artikel') }}";

        let myEditor = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi CKEditor 5 Classic
            ClassicEditor
                .create(document.querySelector('#editorKonten'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'underline', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo']
                })
                .then(editor => {
                    myEditor = editor;
                })
                .catch(error => {
                    console.error('Gagal memuat CKEditor:', error);
                });

            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif

            // Event Filter & Search
            const searchInput = document.getElementById('searchArtikel');
            const statusSelect = document.getElementById('statusArtikel');
            const kategoriSelect = document.getElementById('kategoriArtikel');
            const perPageSelect = document.getElementById('perPageSelect');

            if (searchInput) searchInput.addEventListener('keyup', () => jalankanFilter(true));
            if (statusSelect) statusSelect.addEventListener('change', () => jalankanFilter(true));
            if (kategoriSelect) kategoriSelect.addEventListener('change', () => jalankanFilter(true));
            if (perPageSelect) perPageSelect.addEventListener('change', () => jalankanFilter(true));

            document.getElementById('paginationButtons')?.addEventListener('click', function(e) {
                const btn = e.target.closest('button[data-page]');
                if (!btn || btn.disabled) return;
                window.artikelCurrentPage = parseInt(btn.dataset.page, 10) || 1;
                jalankanFilter(false);
            });

            jalankanFilter(true);
            initComboSelect('perPageSelect');
            initComboSelect('kategoriArtikel');
            initComboSelect('statusArtikel');
            initComboPreview();

            const previewSelector = document.getElementById('previewSelector');
            renderPreview(previewSelector?.value || '');
        });


        /* NAVIGASI ANTARA DAFTAR & FORM FULL PAGE */
        function alihKeForm(mode, id = null) {
            document.getElementById('viewDaftar').style.display = 'none';
            document.getElementById('wrapperSummary').style.display = 'none';
            document.getElementById('heroActionBtn').style.display = 'none';
            document.getElementById('viewForm').style.display = 'block';

            const form = document.getElementById('formArtikel');
            form.reset();
            clearFormErrors(['judul', 'jenis', 'gambar', 'konten', 'tanggal']);
            document.getElementById('artikelGambarUrl').value = '';
            document.getElementById('gambarPreviewWrap').style.display = 'none';
            document.getElementById('gambarLamaInfo').innerHTML = '';

            if (myEditor) myEditor.setData('');

            if (mode === 'tambah') {
                document.getElementById('formTitleText').textContent = 'Tambah Konten Artikel & Edukasi Baru';
                document.getElementById('artikelId').value = '';
            } else if (mode === 'edit') {
                document.getElementById('formTitleText').textContent = 'Edit Konten Artikel & Edukasi';
                isiFormEdit(id);
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function tutupFormFull() {
            document.getElementById('viewForm').style.display = 'none';
            document.getElementById('viewDaftar').style.display = 'grid';
            document.getElementById('wrapperSummary').style.display = 'grid';
            document.getElementById('heroActionBtn').style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }


        /* EDIT ARTIKEL */
        function editArtikelForm(id) {
            alihKeForm('edit', id);
        }

        function isiFormEdit(id) {
            const artikel = dataArtikel.find(item => Number(item.id_artikel) === Number(id));
            if (!artikel) return;

            document.getElementById('artikelId').value = artikel.id_artikel;
            document.getElementById('artikelJudul').value = artikel.judul ?? '';
            document.getElementById('artikelJenis').value = artikel.jenis ?? 'edukasi';
            document.getElementById('artikelTanggal').value = artikel.tanggal_publish ?? '';

            if (myEditor) myEditor.setData(artikel.konten ?? '');

            const gambarLama = document.getElementById('gambarLamaInfo');
            if (artikel.gambar) {
                const imgSrc = artikel.gambar.startsWith('http') ? artikel.gambar : `${urlGambarArtikel}/${encodeURIComponent(artikel.gambar)}`;
                gambarLama.innerHTML = `<img src="${imgSrc}" alt="Gambar lama"><small>Gambar saat ini. Pilih file baru jika ingin menggantinya.</small>`;
            } else {
                gambarLama.innerHTML = '<small>Belum ada gambar.</small>';
            }
        }


        /* SIMPAN ARTIKEL */
        function simpanArtikel(aksi) {
            aksi = aksi === 'draft' ? 'draft' : 'publish';
            const id = document.getElementById('artikelId').value;
            clearFormErrors(['judul', 'jenis', 'gambar', 'konten', 'tanggal']);

            const formData = new FormData();
            formData.append('judul', document.getElementById('artikelJudul').value.trim());
            formData.append('jenis', document.getElementById('artikelJenis').value);
            formData.append('konten', myEditor ? myEditor.getData() : '');

            let tanggalPublish = '';
            if (aksi === 'publish') {
                tanggalPublish = document.getElementById('artikelTanggal').value;
                if (!tanggalPublish) {
                    const d = new Date();
                    tanggalPublish = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                }
            }
            formData.append('tanggal_publish', tanggalPublish);

            const file = document.getElementById('artikelGambar').files[0];
            const urlGambarFetched = document.getElementById('artikelGambarUrl').value;
            if (file) formData.append('gambar', file);
            else if (urlGambarFetched) formData.append('gambar_url', urlGambarFetched);

            let url = urlArtikelStore;
            if (id) {
                formData.append('_method', 'PUT');
                url = urlArtikelUpdate.replace(':id', id);
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    if (data.errors) tampilkanFormErrors(data.errors, ['judul', 'jenis', 'gambar', 'konten', 'tanggal']);
                    throw new Error(data.message || 'Gagal menyimpan artikel.');
                }
                return data;
            })
            .then(() => {
                showToast(aksi === 'draft' ? 'Draft Disimpan' : 'Dipublikasikan', 'Data artikel berhasil disimpan.');
                setTimeout(() => window.location.reload(), 800);
            })
            .catch(err => {
                showToast('Gagal', err.message || 'Terjadi kesalahan sistem.', 'error');
            });
        }


        /* FILTER TABEL */
        function jalankanFilter(resetPage = true) {
            const keyword = (document.getElementById('searchArtikel')?.value || '').toLowerCase().trim();
            const status = document.getElementById('statusArtikel')?.value || 'semua';
            const kategori = document.getElementById('kategoriArtikel')?.value || 'semua';
            const rows = Array.from(document.querySelectorAll('#artikelTableBody tr[data-judul]'));

            const filtered = rows.filter(row => {
                const judul = row.dataset.judul || '';
                const rowStatus = row.dataset.status || '';
                const rowKategori = row.dataset.kategori || '';
                return (!keyword || judul.includes(keyword)) && (status === 'semua' || rowStatus === status) && (kategori === 'semua' || rowKategori === kategori);
            });

            const perPage = parseInt(document.getElementById('perPageSelect')?.value || 10, 10);
            if (typeof window.artikelCurrentPage !== 'number' || resetPage) window.artikelCurrentPage = 1;

            const totalPages = Math.ceil(filtered.length / perPage) || 1;
            window.artikelCurrentPage = Math.min(Math.max(window.artikelCurrentPage, 1), totalPages);

            rows.forEach(r => r.style.display = 'none');
            const start = (window.artikelCurrentPage - 1) * perPage;
            filtered.slice(start, start + perPage).forEach(r => r.style.display = '');

            const tampil = filtered.length;
            document.getElementById('artikelKosongCari').style.display = (rows.length > 0 && tampil === 0) ? 'block' : 'none';

            const tableInfo = document.getElementById('tableInfoPagination');
            if (tableInfo) {
                tableInfo.innerHTML = tampil === 0 ? 'Tidak ada data' : `Menampilkan konten <strong>${start + 1}</strong> - <strong>${Math.min(start + perPage, tampil)}</strong> dari <strong>${tampil}</strong>`;
            }

            // Render pagination buttons
            const pContainer = document.getElementById('paginationButtons');
            if (pContainer) {
                let html = `<button class="btn btn--ghost" type="button" data-page="${window.artikelCurrentPage - 1}" ${window.artikelCurrentPage === 1 ? 'disabled' : ''}>‹</button>`;
                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= window.artikelCurrentPage - 1 && i <= window.artikelCurrentPage + 1)) {
                        html += `<button class="btn ${i === window.artikelCurrentPage ? 'btn--primary' : 'btn--ghost'}" type="button" data-page="${i}">${i}</button>`;
                    }
                }
                html += `<button class="btn btn--ghost" type="button" data-page="${window.artikelCurrentPage + 1}" ${window.artikelCurrentPage === totalPages ? 'disabled' : ''}>›</button>`;
                pContainer.innerHTML = html;
            }
        }


        /* PREVIEW GAMBAR & MOBILE */
        function previewGambar(input) {
            const wrap = document.getElementById('gambarPreviewWrap');
            const img = document.getElementById('gambarPreviewImg');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => { img.src = e.target.result; wrap.style.display = 'block'; };
                reader.readAsDataURL(input.files[0]);
            } else {
                wrap.style.display = 'none';
            }
        }

        function renderPreview(id) {
            const body = document.getElementById('phonePreviewBody');
            if (!body) return;
            const item = dataArtikel.find(a => String(a.id_artikel) === String(id));
            if (!item) {
                body.innerHTML = '<div class="phone-empty">Belum ada konten untuk ditampilkan.</div>';
                return;
            }

            let gambarHtml = item.gambar ?
                `<img src="${item.gambar.startsWith('http') ? item.gambar : `${urlGambarArtikel}/${encodeURIComponent(item.gambar)}`}" class="phone-image">` :
                '<div class="phone-image phone-image--kosong">Tidak ada gambar</div>';

            const jenisLabel = jsLabelJenis[item.jenis] || 'Edukasi Sampah';
            let metaHtml = '♻ ' + escapeHtml(jenisLabel);
            if (item.tanggal_publish) {
                metaHtml += ' &nbsp;•&nbsp; ' + item.tanggal_publish.split('T')[0].split(' ')[0];
            } else {
                metaHtml += ' <span class="phone-badge-draft">Draft</span>';
            }

            body.innerHTML = `
                ${gambarHtml}
                <div class="phone-title">${escapeHtml(item.judul)}</div>
                <div class="phone-meta">${metaHtml}</div>
                <div class="phone-content">${item.konten || ''}</div>
                <div class="phone-tip">💡 <strong>Tips Warga</strong><br>Pilah sampah dari rumah untuk lingkungan yang lebih bersih.</div>
            `;
        }


        /* COMBO & HELPER MODAL */
        function initComboSelect(selectId) {
            const select = document.getElementById(selectId);
            const input = document.getElementById('cari_' + selectId);
            const list = document.getElementById('list_' + selectId);
            if (!select || !input || !list) return;
            input.readOnly = true;

            const teks = o => o.textContent.trim();
            const sync = () => { const o = select.options[select.selectedIndex]; input.value = o ? teks(o) : ''; };

            input.addEventListener('click', e => {
                e.stopPropagation();
                list.innerHTML = Array.from(select.options).map(o => `<div class="combo-item" data-value="${o.value}">${teks(o)}</div>`).join('');
                list.classList.toggle('show');
            });

            list.addEventListener('mousedown', e => {
                e.preventDefault();
                const item = e.target.closest('.combo-item');
                if (item) {
                    select.value = item.dataset.value;
                    sync();
                    list.classList.remove('show');
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            document.addEventListener('click', () => list.classList.remove('show'));
            sync();
        }

        function initComboPreview() {
            const select = document.getElementById('previewSelector');
            const input = document.getElementById('cariPreview');
            const list = document.getElementById('listPreview');
            if (!select || !input || !list) return;

            const data = Array.from(select.options).filter(o => o.value !== '').map(o => ({ value: o.value, label: o.textContent.trim() }));

            input.addEventListener('focus', () => {
                list.innerHTML = data.map(d => `<div class="combo-item" data-value="${d.value}">${escapeHtml(d.label)}</div>`).join('');
                list.classList.add('show');
            });

            list.addEventListener('mousedown', e => {
                const item = e.target.closest('.combo-item');
                if (item) {
                    e.preventDefault();
                    select.value = item.dataset.value;
                    input.value = item.textContent.trim();
                    list.classList.remove('show');
                    renderPreview(item.dataset.value);
                }
            });

            document.addEventListener('click', e => { if (!e.target.closest('#comboPreview')) list.classList.remove('show'); });
            if (select.selectedOptions[0]) input.value = select.selectedOptions[0].textContent.trim();
        }

        function bukaModal(id) { document.getElementById(id)?.classList.add('active'); }
        function tutupModal(id) { document.getElementById(id)?.classList.remove('active'); }

        function bukaDetail(id) {
            const item = dataArtikel.find(a => Number(a.id_artikel) === Number(id));
            if (!item) return;
            document.getElementById('detailJudul').textContent = item.judul;
            document.getElementById('detailTanggal').textContent = (item.jenis || 'edukasi').toUpperCase() + ' • ' + (item.tanggal_publish ? 'Dipublikasikan' : 'Draft');
            document.getElementById('detailKonten').innerHTML = item.konten || '-';
            document.getElementById('detailGambar').innerHTML = item.gambar ? `<img src="${item.gambar.startsWith('http') ? item.gambar : `${urlGambarArtikel}/${encodeURIComponent(item.gambar)}`}" class="detail-image">` : '';
            bukaModal('modalDetail');
        }

        let hapusCtx = null;
        function hapusArtikel(id, judul) {
            hapusCtx = { id, judul };
            document.getElementById('hapusJudul').textContent = judul;
            bukaModal('modalHapus');
        }

        function konfirmasiHapus() {
            if (!hapusCtx) return;
            fetch(urlArtikelDestroy.replace(':id', hapusCtx.id), {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(res => { if (!res.ok) throw new Error('Gagal menghapus'); return res.json(); })
            .then(() => {
                showToast('Berhasil', 'Konten berhasil dihapus.');
                setTimeout(() => window.location.reload(), 800);
            })
            .catch(err => showToast('Gagal', err.message, 'error'));
        }

        function showToast(title, text, type = 'success') {
            const wrap = document.getElementById('toastWrap');
            if (!wrap) return;
            const toast = document.createElement('div');
            toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');
            toast.innerHTML = `<div class="toast-body"><p class="toast-title">${escapeHtml(title)}</p><p class="toast-text">${escapeHtml(text)}</p></div>`;
            wrap.appendChild(toast);
            setTimeout(() => toast.remove(), 3500);
        }

        function escapeHtml(text) {
            const d = document.createElement('div');
            d.textContent = text || '';
            return d.innerHTML;
        }

        function clearFormErrors(fields) {
            fields.forEach(f => { const el = document.getElementById('err_' + f); if (el) el.textContent = ''; });
        }

        function tampilkanFormErrors(errors, fields) {
            fields.forEach(f => { if (errors[f]) document.getElementById('err_' + f).textContent = errors[f][0]; });
        }
    </script>

</div>


@endsection
