@extends('admin.layouts.app')

@section('title', 'Artikel & Edukasi')
@section('active', 'artikel')
@section('crumbs', 'Konten & Laporan | Artikel & Edukasi')

@push('styles')
    @vite('resources/css/pages/artikel.css')
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@ckeditor/ckeditor5-build-classic@41.4.2/build/ckeditor.js"></script>
    <script>
        window.__ARTIKEL_DATA__ = {
            items: @json($artikel),
            urls: {
                store: "{{ route('admin.artikel.store') }}",
                update: "{{ url('/admin/pages/artikel') }}/:id",
                destroy: "{{ url('/admin/pages/artikel') }}/:id",
                gambar: "{{ asset('uploads/artikel') }}",
            },
            flashSuccess: @json(session('success')),
        };
    </script>
    @vite('resources/js/pages/artikel.js')
@endpush

@section('content')

@php
    $totalKonten = $artikel->count();
    $totalPublikasi = $artikel->whereNotNull('tanggal_publish')->count();
    $totalDraft = $artikel->whereNull('tanggal_publish')->count();

    // Rincian per kategori (untuk keterangan kartu Total Konten)
    $totalEdukasi = $artikel->where('jenis', 'edukasi')->count();
    $totalArtikelJenis = $artikel->where('jenis', 'artikel')->count();
    $totalEvent = $artikel->whereIn('jenis', ['event', 'acara'])->count();

    // Konten yang terakhir dipublikasikan
    $terakhirPublish = $artikel->whereNotNull('tanggal_publish')->sortByDesc('tanggal_publish')->first();

    $labelJenis = [
        'edukasi' => ['Edukasi Sampah', 'badge--info'],
        'artikel' => ['Artikel', 'badge--info'],
        'event'   => ['Event', 'badge--purple'],
        'acara'   => ['Event', 'badge--purple'],
    ];

    $bannerConfig = [
        'edukasi' => ['label' => 'Banner Edukasi', 'icon' => '🌱', 'class' => 'banner-grad--edukasi'],
        'event'   => ['label' => 'Banner Event',   'icon' => '📅', 'class' => 'banner-grad--event'],
        'artikel' => ['label' => 'Banner Artikel', 'icon' => '📰', 'class' => 'banner-grad--artikel'],
    ];
@endphp

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

            <div class="summary-info">
                {{ $totalEdukasi }} Edukasi · {{ $totalArtikelJenis }} Artikel · {{ $totalEvent }} Event
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
                @if ($totalKonten > 0)
                    {{ round(($totalPublikasi / $totalKonten) * 100) }}% dari total, sudah tampil di aplikasi warga
                @else
                    Sudah tampil di aplikasi warga
                @endif
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

            <div class="summary-info">Belum ditayangkan, siap dipublikasikan</div>
        </div>


        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Publikasi Terakhir</div>
                    <div class="summary-value">
                        {{ $terakhirPublish ? \Carbon\Carbon::parse($terakhirPublish->tanggal_publish)->format('d/m/Y') : '-' }}
                    </div>
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
                {{ $terakhirPublish ? \Illuminate\Support\Str::limit($terakhirPublish->judul, 40) : 'Belum ada konten dipublikasikan' }}
            </div>
        </div>

    </section>


    {{-- VIEW UTAMA: DAFTAR KONTEN --}}
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
                            <th>Tampilan / Media</th>
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
                                $jenisKey = $item->jenis === 'acara' ? 'event' : $item->jenis;
                                [$labelKategori, $kelasKategori] = $labelJenis[$item->jenis] ?? ['Edukasi Sampah', 'badge--info'];
                                $isBanner = $item->gambar && \Illuminate\Support\Str::startsWith($item->gambar, 'banner:');
                                $bannerKey = $isBanner ? str_replace('banner:', '', $item->gambar) : null;
                                $bannerInfo = $bannerKey ? ($bannerConfig[$bannerKey] ?? $bannerConfig['edukasi']) : null;
                                $cleanKontenRow = trim(html_entity_decode(strip_tags($item->konten), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                                $isKontenLink = filter_var($cleanKontenRow, FILTER_VALIDATE_URL) !== false;
                            @endphp

                            <tr data-judul="{{ strtolower($item->judul) }}" data-status="{{ $status }}" data-kategori="{{ $jenisKey }}">
                                <td>
                                    @if ($isBanner && $bannerInfo)
                                        <div class="artikel-thumb artikel-thumb--banner {{ $bannerInfo['class'] }}">
                                            <span>{{ $bannerInfo['icon'] }}</span>
                                            <small>{{ ucfirst($bannerKey) }}</small>
                                        </div>
                                    @elseif ($item->gambar)
                                        <img src="{{ Str::startsWith($item->gambar, 'http') ? $item->gambar : asset('uploads/artikel/' . $item->gambar) }}"
                                            alt="{{ $item->judul }}" class="artikel-thumb">
                                    @else
                                        <div class="artikel-thumb artikel-thumb--kosong">Teks Saja</div>
                                    @endif
                                </td>

                                <td>
                                    <div class="artikel-judul">{{ $item->judul }}</div>
                                    @if ($isKontenLink)
                                        <div class="artikel-ringkasan" style="color: #1E9E66; font-weight: 600;">
                                            🔗 Link Web ({{ parse_url($cleanKontenRow, PHP_URL_HOST) ?? 'Sumber Eksternal' }})
                                        </div>
                                    @else
                                        <div class="artikel-ringkasan">{{ \Illuminate\Support\Str::limit($cleanKontenRow, 45) }}</div>
                                    @endif
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

    </div>


    {{-- VIEW KEDUA: FORM FULL PAGE --}}
    <div class="card form-full-view" id="viewForm" style="display: none;">
        <div class="form-full-header">
            <div>
                <h2 class="card-title" id="formTitleText">Formulir Konten Artikel & Edukasi</h2>
                <p class="card-sub">Tulis materi secara manual atau tempel link artikel dari Google agar tampil utuh di aplikasi warga.</p>
            </div>
            <button class="btn btn--ghost" type="button" onclick="tutupFormFull()">
                ← Kembali ke Daftar
            </button>
        </div>

        <form id="formArtikel" onsubmit="return false;" class="form-full-body">
            <input type="hidden" id="artikelId">
            <input type="hidden" id="modeMedia" name="mode_media" value="gambar">

            <div class="form-grid-columns">
                <div class="form-group form-group--full">
                    <label for="artikelJudul">Judul Konten <span class="text-danger">*</span></label>
                    <input type="text" id="artikelJudul" class="form-control" placeholder="Contoh: Cara Memilah Sampah dari Rumah" oninput="updateFormMediaPreview()" required>
                    <span class="form-error" id="err_judul"></span>
                </div>

                <div class="form-group">
                    <label for="cari_artikelJenis">Jenis / Kategori Konten <span class="text-danger">*</span></label>
                    <div class="combo combo--arrow" id="combo_artikelJenis">
                        <input type="text" id="cari_artikelJenis" class="combo-input" placeholder="Pilih jenis konten" readonly autocomplete="off">
                        <div class="combo-list" id="list_artikelJenis"></div>
                    </div>
                    <select id="artikelJenis" class="combo-hidden" tabindex="-1" aria-hidden="true" required>
                        <option value="edukasi">Edukasi Sampah</option>
                        <option value="artikel">Artikel</option>
                        <option value="event">Event</option>
                    </select>
                    <span class="form-error" id="err_jenis"></span>
                </div>

                <div class="form-group">
                    <label for="artikelTanggal">Tanggal Publikasi</label>
                    <input type="date" id="artikelTanggal" class="form-control" value="{{ now()->format('Y-m-d') }}" min="{{ now()->format('Y-m-d') }}">
                    <span class="form-error" id="err_tanggal"></span>
                </div>

                {{-- PILIHAN MODE TAMPILAN MEDIA --}}
                <div class="form-group form-group--full">
                    <label>Gaya Tampilan Header / Media <span class="text-danger">*</span></label>

                    <div class="media-mode-grid" id="mediaModeGrid">
                        <button type="button" class="media-mode-card active" data-mode="gambar" onclick="pilihModeMedia('gambar')">
                            <div class="media-mode-icon media-mode-icon--img">🖼️</div>
                            <div class="media-mode-info">
                                <strong>Upload / URL Gambar</strong>
                                <span>Gunakan foto atau poster</span>
                            </div>
                        </button>

                        <button type="button" class="media-mode-card" data-mode="banner_edukasi" onclick="pilihModeMedia('banner_edukasi')">
                            <div class="media-mode-icon banner-grad--edukasi">🌱</div>
                            <div class="media-mode-info">
                                <strong>Banner Warna Edukasi</strong>
                                <span>Latar hijau alami</span>
                            </div>
                        </button>

                        <button type="button" class="media-mode-card" data-mode="banner_event" onclick="pilihModeMedia('banner_event')">
                            <div class="media-mode-icon banner-grad--event">📅</div>
                            <div class="media-mode-info">
                                <strong>Banner Warna Event</strong>
                                <span>Latar amber hangat</span>
                            </div>
                        </button>

                        <button type="button" class="media-mode-card" data-mode="banner_artikel" onclick="pilihModeMedia('banner_artikel')">
                            <div class="media-mode-icon banner-grad--artikel">📰</div>
                            <div class="media-mode-info">
                                <strong>Banner Warna Artikel</strong>
                                <span>Latar teal sejuk</span>
                            </div>
                        </button>

                        <button type="button" class="media-mode-card" data-mode="tanpa_gambar" onclick="pilihModeMedia('tanpa_gambar')">
                            <div class="media-mode-icon media-mode-icon--text">📝</div>
                            <div class="media-mode-info">
                                <strong>Teks Saja (Tanpa Gambar)</strong>
                                <span>Fokus teks, tanpa banner</span>
                            </div>
                        </button>
                    </div>

                    {{-- PANEL 1: UPLOAD GAMBAR --}}
                    <div id="panelUploadGambar" class="media-option-panel">
                        <input type="file" id="artikelGambar" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewGambar(this)">
                        <input type="hidden" id="artikelGambarUrl" name="gambar_url">

                        <div class="field-hint" style="margin-top: 8px; line-height: 1.5;">
                            <span style="display: inline-block; font-weight: 600; color: #1E9E66; margin-bottom: 2px;">📐 Panduan Ukuran Gambar:</span><br>
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

                    {{-- PANEL 2: PRATINJAU BANNER WARNA / TEKS SAJA --}}
                    <div id="panelBannerPreview" class="media-option-panel" style="display: none;">
                        <div class="form-live-card-preview" id="formLiveCardPreview"></div>
                    </div>
                </div>

                {{-- PILIHAN SUMBER KONTEN: MANUAL VS LINK WEB --}}
                <div class="form-group form-group--full">
                    <label>Sumber Isi Konten <span class="text-danger">*</span></label>
                    <input type="hidden" id="sumberKonten" name="sumber_konten" value="manual">

                    <div class="sumber-mode-grid">
                        <button type="button" class="media-mode-card active" data-sumber="manual" onclick="pilihSumberKonten('manual')">
                            <div class="media-mode-icon media-mode-icon--text">✍️</div>
                            <div class="media-mode-info">
                                <strong>Tulis Manual (Editor)</strong>
                                <span>Ketik atau format artikel sendiri</span>
                            </div>
                        </button>

                        <button type="button" class="media-mode-card" data-sumber="link" onclick="pilihSumberKonten('link')">
                            <div class="media-mode-icon media-mode-icon--img">🔗</div>
                            <div class="media-mode-info">
                                <strong>Tempel Link Google / Web</strong>
                                <span>Tampilkan halaman web 100% utuh di mobile</span>
                            </div>
                        </button>
                    </div>

                    <div id="panelKontenManual">
                        <textarea id="editorKonten" class="form-control"></textarea>
                        <span class="form-error" id="err_konten"></span>
                    </div>

                    <div id="panelKontenLink" class="media-option-panel" style="display: none; margin-top: 0;">
                        <label for="artikelLinkUrl" style="margin-bottom: 6px; display: block;">URL / Link Artikel dari Google <span class="text-danger">*</span></label>
                        <input type="url" id="artikelLinkUrl" class="form-control" placeholder="https://www.contoh-berita.com/judul-artikel-sampah">
                        <span class="field-hint" style="margin-top: 6px; display: block;">
                            Salin link artikel dari Google/browser lalu tempel di sini. Saat warga membuka artikel di aplikasi mobile, halaman web asli akan ditampilkan secara utuh tanpa perubahan.
                        </span>
                        <span class="form-error" id="err_link_url"></span>
                    </div>
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

</div>

@endsection
