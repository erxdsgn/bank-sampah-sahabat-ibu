@extends('admin.layouts.app')

@section('title', 'Katalog Barang')
@section('active', 'katalog')
@section('crumbs', 'Keuangan & Produk | Katalog Barang')

@section('content')

    <div class="katalog-page">

        <section class="hero">
            <div class="hero-text">
                <span class="eyebrow">KEUANGAN & PRODUK</span>

                <h1 class="hero-title">
                    Katalog <span class="accent">Barang</span>
                </h1>

                <p class="hero-sub">
                    Kelola informasi produk hasil daur ulang Bank Sampah
                </p>
            </div>

            <div class="hero-actions">
                <button class="btn btn--primary" type="button" onclick="bukaModalTambah()">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Produk
                </button>
            </div>
        </section>


        <!-- =========================================================
             CARD KATALOG
        ========================================================== -->
        <section class="card">

            <!-- SEARCH -->
            <div class="table-toolbar">

                <div class="table-search">
                    <svg viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>

                    <input
                        type="text"
                        id="searchKatalog"
                        placeholder="Cari nama produk..."
                        autocomplete="off">
                </div>

                <button
                    class="btn btn--ghost"
                    type="button"
                    onclick="window.location.reload()">

                    <svg viewBox="0 0 24 24">
                        <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                        <path d="M21 3v5h-5"></path>
                    </svg>

                    Refresh
                </button>

            </div>


            <!-- GRID PRODUK -->
            <div class="catalog-grid" id="catalogGrid">

                @forelse($katalogProduk as $produk)

                    <div
                        class="catalog-item"
                        data-nama="{{ strtolower($produk->nama_produk) }}">

                        <div class="catalog-img">

                            @if ($produk->foto)

                                <img
                                    src="{{ asset('storage/' . $produk->foto) }}"
                                    alt="{{ $produk->nama_produk }}">

                            @else

                                <div class="catalog-img-kosong">
                                    📸 Tidak ada foto
                                </div>

                            @endif

                        </div>


                        <div class="catalog-body">

                            <h3 class="catalog-title">
                                {{ $produk->nama_produk }}
                            </h3>

                            <div class="catalog-price">
                                Rp {{ number_format($produk->harga, 0, ',', '.') }}
                            </div>

                            <div class="catalog-stok">

                                @if ($produk->stok <= 5)

                                    <span class="badge badge--warning">
                                        Stok Menipis: {{ $produk->stok }}
                                    </span>

                                @else

                                    <span class="badge badge--success">
                                        Stok: {{ $produk->stok }}
                                    </span>

                                @endif

                            </div>


                            <div class="catalog-actions">

                                <!-- DETAIL -->
                                <button
                                    class="icon-btn"
                                    title="Lihat Detail"
                                    type="button"
                                    onclick="lihatDetail({{ Js::from($produk) }})">

                                    <svg viewBox="0 0 24 24">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                        <circle cx="12" cy="12" r="2.5"></circle>
                                    </svg>

                                </button>


                                <!-- EDIT -->
                                <button
                                    class="icon-btn"
                                    title="Edit Produk"
                                    type="button"
                                    onclick="bukaModalEdit({{ Js::from($produk) }})">

                                    <svg viewBox="0 0 24 24">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                    </svg>

                                </button>


                                <!-- HAPUS -->
                                <button
                                    class="icon-btn danger"
                                    title="Hapus Produk"
                                    type="button"
                                    onclick="hapusProduk({{ Js::from($produk) }})">

                                    <svg viewBox="0 0 24 24">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                        <path d="M10 11v6M14 11v6"></path>
                                    </svg>

                                </button>

                            </div>

                        </div>

                    </div>

                @empty

                    <div class="catalog-empty" id="catalogKosong">

                        <div class="empty-icon">
                            ♻️
                        </div>

                        <strong>Belum Ada Katalog Produk Daur Ulang</strong>

                        <p class="empty-text">
                            Silakan klik tombol "Tambah Produk" di kanan atas
                            untuk memasukkan data barang daur ulang.
                        </p>

                    </div>

                @endforelse

            </div>


            <!-- HASIL PENCARIAN KOSONG -->
            <div
                class="catalog-empty catalog-empty--search"
                id="catalogKosongCari">

                <div class="empty-icon">
                    🔍
                </div>

                <strong>Produk Tidak Ditemukan</strong>

                <p class="empty-text">
                    Tidak ada produk yang cocok dengan kata kunci pencarian.
                </p>

            </div>


            <!-- FOOTER -->
            <div class="table-footer">

                <div class="table-info" id="tableInfoPagination">
                    Menampilkan data...
                </div>

                <div class="table-pagination-controls">
                    <div class="per-page">
                        <label for="cari_perPageSelect">Produk per halaman:</label>
                        <div class="combo combo--up combo--arrow" id="combo_perPageSelect">
                            <input type="text" id="cari_perPageSelect" class="combo-input" placeholder=""
                                autocomplete="off">
                            <div class="combo-list" id="list_perPageSelect"></div>
                        </div>
                        <select id="perPageSelect" class="combo-hidden" tabindex="-1" aria-hidden="true">
                            <option value="8" selected>8</option>
                            <option value="12">12</option>
                            <option value="16">16</option>
                            <option value="20">20</option>
                        </select>
                    </div>

                    <div class="pagination-buttons" id="paginationButtons"></div>
                </div>

            </div>

        </section>


        <!-- =========================================================
             TOAST
        ========================================================== -->
        <div class="toast-wrap" id="toastWrap"></div>


        <!-- =========================================================
             MODAL DETAIL
        ========================================================== -->
        <div class="modal-overlay" id="modalDetail">

            <div class="modal-box">

                <div class="modal-head">

                    <h3 class="modal-title">

                        <svg viewBox="0 0 24 24">
                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                            <circle cx="12" cy="12" r="2.5"></circle>
                        </svg>

                        Detail Produk

                    </h3>

                    <button
                        class="modal-close"
                        type="button"
                        onclick="tutupModal('modalDetail')">

                        &times;

                    </button>

                </div>


                <div class="modal-body">

                    <img
                        id="detailfoto"
                        class="preview-img preview-img--besar"
                        src=""
                        alt="Preview">

                    <div
                        id="detailfotoKosong"
                        class="catalog-img-kosong catalog-img-kosong--besar">

                        📸 Tidak ada foto

                    </div>


                    <div class="detail-grid">

                        <div class="detail-item detail-item--full">

                            <span class="detail-label">
                                Nama Barang
                            </span>

                            <span
                                class="detail-nama"
                                id="detailNama">
                                -
                            </span>

                        </div>


                        <div class="detail-item">

                            <span class="detail-label">
                                Harga
                            </span>

                            <span
                                class="detail-value saldo"
                                id="detailHarga">
                                -
                            </span>

                        </div>


                        <div class="detail-item">

                            <span class="detail-label">
                                Stok Tersedia
                            </span>

                            <span
                                class="detail-value"
                                id="detailStok">
                                -
                            </span>

                        </div>

                    </div>

                </div>


                <div class="modal-foot">

                    <button
                        class="btn btn--ghost"
                        type="button"
                        onclick="tutupModal('modalDetail')">

                        Tutup

                    </button>

                </div>

            </div>

        </div>


        <!-- =========================================================
             MODAL TAMBAH
        ========================================================== -->
        <div class="modal-overlay" id="modalTambah">

            <div class="modal-box">

                <div class="modal-head">

                    <h3 class="modal-title">

                        <svg viewBox="0 0 24 24">
                            <path d="M12 5v14M5 12h14"></path>
                        </svg>

                        Tambah Produk Baru

                    </h3>

                    <button
                        class="modal-close"
                        type="button"
                        onclick="tutupModal('modalTambah')">

                        &times;

                    </button>

                </div>


                <form
                    id="formTambahProduk"
                    onsubmit="return simpanTambah(event)">

                    <div class="modal-body">

                        <div class="form-grid">

                            <div class="form-group form-group--full">

                                <label for="tambahfoto">
                                    Foto Produk
                                </label>

                                <input
                                    type="file"
                                    id="tambahfoto"
                                    accept="image/jpeg,image/png,image/jpg,image/webp"
                                    required>

                                <span class="field-hint">
                                    Format JPG, PNG, atau WEBP. Maksimal 2MB.
                                </span>

                            </div>


                            <div class="form-group form-group--full">

                                <label for="tambahNama">
                                    Nama Barang
                                </label>

                                <input
                                    type="text"
                                    id="tambahNama"
                                    required>

                            </div>


                            <div class="form-group">

                                <label for="tambahHarga">
                                    Harga (Rp)
                                </label>

                                <input
                                    type="number"
                                    id="tambahHarga"
                                    min="0"
                                    required>

                            </div>


                            <div class="form-group">

                                <label for="tambahStok">
                                    Stok
                                </label>

                                <input
                                    type="number"
                                    id="tambahStok"
                                    min="0"
                                    required>

                            </div>

                        </div>

                    </div>


                    <div class="modal-foot">

                        <button
                            class="btn btn--ghost"
                            type="button"
                            onclick="tutupModal('modalTambah')">

                            Batal

                        </button>

                        <button
                            class="btn btn--primary"
                            type="submit">

                            Simpan Produk

                        </button>

                    </div>

                </form>

            </div>

        </div>


        <!-- =========================================================
             MODAL EDIT
        ========================================================== -->
        <div class="modal-overlay" id="modalEdit">

            <div class="modal-box">

                <div class="modal-head">

                    <h3 class="modal-title">

                        <svg viewBox="0 0 24 24">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                        </svg>

                        Edit Produk

                    </h3>

                    <button
                        class="modal-close"
                        type="button"
                        onclick="tutupModal('modalEdit')">

                        &times;

                    </button>

                </div>


                <form
                    id="formEditProduk"
                    onsubmit="return simpanEdit(event)">

                    <div class="modal-body">

                        <input
                            type="hidden"
                            id="editId">


                        <div class="form-grid">

                            <div class="form-group form-group--full">

                                <span class="detail-label">
                                    Foto Saat Ini
                                </span>

                                <img
                                    id="editfotoPreview"
                                    class="preview-img"
                                    src=""
                                    alt="Preview">

                                <div
                                    id="editfotoKosong"
                                    class="catalog-img-kosong catalog-img-kosong--kecil">

                                    📸 Tidak ada foto

                                </div>

                            </div>


                            <div class="form-group form-group--full">

                                <label for="editfoto">
                                    Ganti Foto (opsional)
                                </label>

                                <input
                                    type="file"
                                    id="editfoto"
                                    accept="image/jpeg,image/png,image/jpg,image/webp">

                                <span class="field-hint">
                                    Biarkan kosong jika tidak ingin mengubah foto. Maks 2MB.
                                </span>

                            </div>


                            <div class="form-group form-group--full">

                                <label for="editNama">
                                    Nama Barang
                                </label>

                                <input
                                    type="text"
                                    id="editNama"
                                    required>

                            </div>


                            <div class="form-group">

                                <label for="editHarga">
                                    Harga (Rp)
                                </label>

                                <input
                                    type="number"
                                    id="editHarga"
                                    min="0"
                                    required>

                            </div>


                            <div class="form-group">

                                <label for="editStok">
                                    Stok
                                </label>

                                <input
                                    type="number"
                                    id="editStok"
                                    min="0"
                                    required>

                            </div>

                        </div>

                    </div>


                    <div class="modal-foot">

                        <button
                            class="btn btn--ghost"
                            type="button"
                            onclick="tutupModal('modalEdit')">

                            Batal

                        </button>

                        <button
                            class="btn btn--primary"
                            type="submit">

                            Simpan Perubahan

                        </button>

                    </div>

                </form>

            </div>

        </div>


        <!-- =========================================================
             MODAL HAPUS
        ========================================================== -->
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
                        Hapus Produk?
                    </h3>


                    <p class="confirm-text">
                        Anda akan menghapus produk
                        <strong id="hapusNama">-</strong>
                        dari katalog.
                        Tindakan ini tidak dapat dibatalkan.
                    </p>

                </div>


                <div class="modal-foot modal-foot--center">

                    <button
                        class="btn btn--ghost"
                        type="button"
                        onclick="tutupModal('modalHapus')">

                        Batal

                    </button>

                    <button
                        class="btn btn--danger"
                        type="button"
                        onclick="konfirmasiHapus()">

                        Ya, Hapus

                    </button>

                </div>

            </div>

        </div>


        <style>
            /* =========================================================
               THEME VARIABLES
            ========================================================== */

            .katalog-page {
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


            html[data-theme="dark"] .katalog-page {
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


            .katalog-page input,
            .katalog-page select,
            .katalog-page textarea {
                color-scheme: light;
            }


            html[data-theme="dark"] .katalog-page input,
            html[data-theme="dark"] .katalog-page select,
            html[data-theme="dark"] .katalog-page textarea {
                color-scheme: dark;
            }


            /* =========================================================
               CARD & TOOLBAR
            ========================================================== */

            .katalog-page .card {
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 14px;
                overflow: hidden;
                box-shadow: var(--shadow);
            }


            .katalog-page .table-toolbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
                padding: 16px 24px;
            }


            .katalog-page .table-search {
                width: 400px;
                max-width: 100%;
                position: relative;
                color: var(--text-secondary);
            }


            .katalog-page .table-search svg {
                position: absolute;
                width: 18px;
                height: 18px;
                left: 12px;
                top: 50%;
                transform: translateY(-50%);
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
                pointer-events: none;
            }


            .katalog-page .table-search input {
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
                transition: border-color .15s, box-shadow .15s;
            }


            .katalog-page .table-search input::placeholder {
                color: var(--text-muted);
            }


            .katalog-page .table-search input:focus {
                border-color: #22c55e;
                box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
            }


            .katalog-page :not(.pagination-buttons)>.btn--ghost {
                background: var(--surface);
                color: var(--text-secondary);
                border-color: var(--border);
            }


            .katalog-page :not(.pagination-buttons)>.btn--ghost:hover {
                background: var(--surface-secondary);
                color: var(--text);
                border-color: var(--border);
            }


            /* =========================================================
               FOOTER
            ========================================================== */

            .katalog-page .table-footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                padding: 16px 24px;
                flex-wrap: wrap;
                border-top: 1px solid var(--border);
            }


            .katalog-page .table-info {
                font-size: 13px;
                color: var(--text-secondary);
            }


            .katalog-page .table-info strong {
                color: var(--text);
            }


            /* =========================================================
               PAGINATION — SAMA DENGAN HALAMAN DATA WARGA & ARTIKEL
            ========================================================== */

            .katalog-page .table-pagination-controls {
                display: flex;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
            }

            .katalog-page .per-page {
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .katalog-page .per-page label {
                font-size: 13px;
                color: var(--text-secondary);
            }

            .katalog-page .per-page .combo {
                width: 84px;
            }

            .katalog-page .per-page .combo-input {
                padding-top: 6px;
                padding-bottom: 6px;
            }

            .katalog-page .combo {
                position: relative;
            }

            .katalog-page .combo-hidden {
                display: none !important;
            }

            .katalog-page .combo-input {
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
                transition: border-color .2s ease, box-shadow .2s ease;
            }

            .katalog-page .combo--arrow .combo-input {
                cursor: pointer;
                padding-right: 30px;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right 9px center;
                background-size: 14px;
            }

            .katalog-page .combo-input::placeholder {
                color: var(--text-muted);
            }

            .katalog-page .combo-input:focus {
                border-color: #22c55e;
                box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
            }

            .katalog-page .combo-list {
                display: none;
                position: absolute;
                top: calc(100% + 4px);
                left: 0;
                right: 0;
                max-height: 180px;
                overflow-y: auto;
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, .22);
                z-index: 60;
                scrollbar-width: thin;
                scrollbar-color: var(--border) transparent;
            }

            .katalog-page .combo-list.show {
                display: block;
            }

            .katalog-page .combo-list::-webkit-scrollbar {
                width: 6px;
            }

            .katalog-page .combo-list::-webkit-scrollbar-track {
                background: transparent;
            }

            .katalog-page .combo-list::-webkit-scrollbar-thumb {
                background: var(--border);
                border-radius: 4px;
            }

            .katalog-page .combo-list::-webkit-scrollbar-thumb:hover {
                background: var(--text-muted);
            }

            .katalog-page .combo--up .combo-list {
                top: auto;
                bottom: calc(100% + 4px);
            }

            .katalog-page .combo-item {
                padding: 10px 14px;
                font-size: 13px;
                color: var(--text);
                cursor: pointer;
                transition: background .1s ease, color .1s ease;
            }

            .katalog-page .combo-item.is-selected {
                font-weight: 700;
            }

            .katalog-page .combo-item:hover,
            .katalog-page .combo-item.is-active {
                background: var(--table-hover);
                color: #22c55e;
            }

            .katalog-page .combo-empty {
                padding: 10px;
                font-size: 12px;
                color: var(--text-muted);
                text-align: center;
            }

            .katalog-page .pagination-buttons {
                display: flex;
                gap: 4px;
                align-items: center;
            }

            .katalog-page .pagination-buttons .btn {
                padding: 6px 12px;
                min-width: 32px;
            }

            .katalog-page .pagination-buttons .btn:disabled {
                opacity: .5;
                cursor: not-allowed;
            }

            .katalog-page .pagination-dots {
                padding: 0 4px;
                color: var(--text-secondary);
            }


            /* =========================================================
               BADGES
            ========================================================== */

            .katalog-page .badge {
                display: inline-flex;
                align-items: center;
                padding: 6px 12px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 700;
                white-space: nowrap;
            }


            .katalog-page .badge--warning {
                background: rgba(245, 158, 11, .14);
                color: #fbbf24;
            }


            .katalog-page .badge--success {
                background: rgba(34, 197, 94, .14);
                color: #4ade80;
            }


            .katalog-page .badge--danger {
                background: rgba(239, 68, 68, .14);
                color: #f87171;
            }


            .katalog-page .saldo {
                white-space: nowrap;
            }


            /* =========================================================
               CATALOG GRID
            ========================================================== */

            .katalog-page .catalog-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
                gap: 20px;
                padding: 4px 24px 24px;
            }


            .katalog-page .catalog-item {
                background: var(--table-row);
                border: 1px solid var(--border);
                border-radius: 14px;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                min-width: 0;
                transition:
                    transform .2s ease,
                    box-shadow .2s ease,
                    border-color .2s ease;
            }


            .katalog-page .catalog-item:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, .16);
                border-color: rgba(74, 222, 128, .28);
            }


            /* =========================================================
               PRODUCT IMAGE
            ========================================================== */

            .katalog-page .catalog-img {
                width: 100%;
                height: 180px;
                background: var(--empty);
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                border-bottom: 1px solid var(--border);
            }


            .katalog-page .catalog-img img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }


            .katalog-page .catalog-img-kosong {
                color: var(--text-muted);
                font-size: 13px;
                font-weight: 500;
                text-align: center;
            }


            /* =========================================================
               PRODUCT BODY
            ========================================================== */

            .katalog-page .catalog-body {
                padding: 16px;
                display: flex;
                flex-direction: column;
                flex: 1;
                gap: 6px;
                min-width: 0;
            }


            .katalog-page .catalog-title {
                font-size: 15px;
                font-weight: 700;
                color: var(--text);
                margin: 0;
                line-height: 1.45;
                word-break: break-word;
            }


            .katalog-page .catalog-price {
                font-size: 15px;
                color: #4ade80;
                font-weight: 700;
                margin-top: 1px;
            }


            .katalog-page .catalog-stok {
                margin-bottom: 10px;
            }


            .katalog-page .catalog-actions {
                display: flex;
                gap: 6px;
                border-top: 1px solid var(--border);
                padding-top: 12px;
                margin-top: auto;
            }


            /* =========================================================
               EMPTY STATE
            ========================================================== */

            .katalog-page .catalog-empty {
                grid-column: 1 / -1;
                text-align: center;
                padding: 60px 20px;
                color: var(--text);
            }


            .katalog-page .catalog-empty--search {
                display: none;
            }


            .katalog-page .empty-icon {
                font-size: 36px;
                line-height: 1;
                margin-bottom: 12px;
            }


            .katalog-page .empty-text {
                margin: 6px 0 0;
                color: var(--text-secondary);
                font-size: 13px;
                line-height: 1.6;
            }


            /* =========================================================
               IMAGE PREVIEW
            ========================================================== */

            .katalog-page .catalog-img-kosong--kecil {
                height: 90px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: var(--empty);
                border: 1px solid var(--border);
                border-radius: 8px;
            }


            .katalog-page .catalog-img-kosong--besar {
                height: 160px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: var(--empty);
                border: 1px solid var(--border);
                border-radius: 10px;
                margin-bottom: 18px;
            }


            .katalog-page .preview-img {
                width: 100%;
                max-height: 140px;
                object-fit: cover;
                border-radius: 8px;
                border: 1px solid var(--border);
                background: var(--empty);
            }


            .katalog-page .preview-img--besar {
                max-height: 220px;
                margin-bottom: 18px;
            }


            /* =========================================================
               ACTION BUTTON
            ========================================================== */

            .katalog-page .icon-btn {
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
                flex: 1;
                transition:
                    background .15s,
                    border-color .15s,
                    color .15s;
            }


            .katalog-page .icon-btn svg {
                width: 16px;
                height: 16px;
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
            }


            .katalog-page .icon-btn:hover {
                background: var(--table-hover);
                color: var(--text);
                border-color: #3a4963;
            }


            .katalog-page .icon-btn.danger {
                color: #f87171;
            }


            .katalog-page .icon-btn.danger:hover {
                background: rgba(239, 68, 68, .10);
                border-color: rgba(239, 68, 68, .35);
                color: #f87171;
            }


            /* =========================================================
               MODAL
            ========================================================== */

            .katalog-page .modal-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(2, 6, 23, .72);
                align-items: center;
                justify-content: center;
                padding: 20px;
                z-index: 1000;
                backdrop-filter: blur(2px);
            }


            .katalog-page .modal-overlay.active {
                display: flex;
            }


            .katalog-page .modal-box {
                width: 100%;
                max-width: 560px;
                max-height: 90vh;
                overflow-y: auto;
                background: var(--surface);
                color: var(--text);
                border: 1px solid var(--border);
                border-radius: 14px;
                box-shadow: 0 20px 45px rgba(0, 0, 0, .35);
                animation: modalPop .15s ease-out;
            }


            .katalog-page .modal-box--sm {
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


            .katalog-page .modal-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 20px 24px;
                border-bottom: 1px solid var(--border);
            }


            .katalog-page .modal-title {
                margin: 0;
                font-size: 16px;
                font-weight: 700;
                color: var(--text);
                display: flex;
                align-items: center;
                gap: 10px;
            }


            .katalog-page .modal-title svg {
                width: 18px;
                height: 18px;
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
            }


            .katalog-page .modal-close {
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


            .katalog-page .modal-close:hover {
                background: var(--surface-secondary);
                color: var(--text);
            }


            .katalog-page .modal-body {
                padding: 22px 24px;
            }


            .katalog-page .modal-body--center {
                text-align: center;
                padding-top: 28px;
            }


            .katalog-page .modal-foot {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                padding: 16px 24px;
                border-top: 1px solid var(--border);
            }


            .katalog-page .modal-foot--center {
                justify-content: center;
            }


            /* =========================================================
               DANGER BUTTON
            ========================================================== */

            .katalog-page .btn--danger {
                background: #dc2626;
                color: #ffffff;
                border: 1px solid #dc2626;
            }


            .katalog-page .btn--danger:hover {
                background: #b91c1c;
                border-color: #b91c1c;
            }


            /* =========================================================
               DETAIL
            ========================================================== */

            .katalog-page .detail-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px 20px;
            }


            .katalog-page .detail-item {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }


            .katalog-page .detail-item--full {
                grid-column: 1 / -1;
            }


            .katalog-page .detail-label {
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .03em;
                color: var(--text-muted);
                display: block;
            }


            .katalog-page .detail-nama {
                font-size: 16px;
                font-weight: 700;
                color: var(--text);
                line-height: 1.4;
            }


            .katalog-page .detail-value {
                font-size: 14px;
                color: var(--text);
            }


            /* =========================================================
               FORM
            ========================================================== */

            .katalog-page .form-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px 18px;
            }


            .katalog-page .form-group {
                display: flex;
                flex-direction: column;
                gap: 6px;
                min-width: 0;
            }


            .katalog-page .form-group--full {
                grid-column: 1 / -1;
            }


            .katalog-page .form-group label {
                font-size: 12px;
                font-weight: 700;
                color: var(--text);
            }


            .katalog-page .form-group input,
            .katalog-page .form-group select,
            .katalog-page .form-group textarea {
                border: 1px solid var(--border);
                border-radius: 8px;
                padding: 9px 12px;
                font-size: 13px;
                font-family: inherit;
                outline: none;
                box-sizing: border-box;
                width: 100%;
                background: var(--input-bg);
                color: var(--text);
                transition:
                    border-color .15s,
                    box-shadow .15s;
            }


            .katalog-page .form-group input::placeholder,
            .katalog-page .form-group textarea::placeholder {
                color: var(--text-muted);
            }


            .katalog-page .form-group input[type="file"] {
                padding: 7px 10px;
            }


            .katalog-page .form-group input:focus,
            .katalog-page .form-group select:focus,
            .katalog-page .form-group textarea:focus {
                border-color: #22c55e;
                box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
            }


            .katalog-page .field-hint {
                font-size: 11px;
                color: var(--text-muted);
                line-height: 1.5;
            }


            /* =========================================================
               CONFIRM DELETE
            ========================================================== */

            .katalog-page .confirm-icon {
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


            .katalog-page .confirm-icon svg {
                width: 24px;
                height: 24px;
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
            }


            .katalog-page .confirm-title {
                margin: 0 0 8px;
                font-size: 16px;
                font-weight: 700;
                color: var(--text);
            }


            .katalog-page .confirm-text {
                margin: 0;
                font-size: 13px;
                color: var(--text-secondary);
                line-height: 1.6;
            }


            .katalog-page .confirm-text strong {
                color: var(--text);
            }


            /* =========================================================
               TOAST
            ========================================================== */

            .katalog-page .toast-wrap {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1100;
                display: flex;
                flex-direction: column;
                gap: 10px;
                pointer-events: none;
            }


            .katalog-page .toast {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                min-width: 300px;
                max-width: 380px;
                background: var(--surface);
                border: 1px solid var(--border);
                border-left: 4px solid #22c55e;
                border-radius: 10px;
                box-shadow: 0 12px 30px rgba(0, 0, 0, .28);
                padding: 14px 16px;
                pointer-events: auto;
                animation: toastIn .18s ease-out;
            }


            .katalog-page .toast.toast--error {
                border-left-color: #ef4444;
            }


            .katalog-page .toast-icon {
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


            .katalog-page .toast.toast--error .toast-icon {
                background: rgba(239, 68, 68, .14);
                color: #f87171;
            }


            .katalog-page .toast-icon svg {
                width: 13px;
                height: 13px;
                fill: none;
                stroke: currentColor;
                stroke-width: 2.4;
            }


            .katalog-page .toast-body {
                flex: 1;
                min-width: 0;
            }


            .katalog-page .toast-title {
                font-size: 13px;
                font-weight: 700;
                color: var(--text);
                margin: 0 0 2px;
            }


            .katalog-page .toast-text {
                font-size: 12.5px;
                color: var(--text-secondary);
                margin: 0;
                line-height: 1.5;
                word-break: break-word;
            }


            .katalog-page .toast-close {
                border: none;
                background: transparent;
                color: var(--text-muted);
                font-size: 16px;
                line-height: 1;
                cursor: pointer;
                padding: 0;
                flex-shrink: 0;
            }


            .katalog-page .toast-close:hover {
                color: var(--text);
            }


            .katalog-page .toast.toast--leaving {
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
               RESPONSIVE
            ========================================================== */

            @media (max-width: 900px) {

                .katalog-page .catalog-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

            }


            @media (max-width: 700px) {

                .katalog-page .table-toolbar {
                    align-items: stretch;
                }

                .katalog-page .table-search {
                    width: 100%;
                }

                .katalog-page .table-toolbar > .btn {
                    width: 100%;
                    justify-content: center;
                }

                .katalog-page .catalog-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 14px;
                    padding-left: 16px;
                    padding-right: 16px;
                }

                .katalog-page .table-toolbar {
                    padding: 14px 16px;
                }

                .katalog-page .table-footer {
                    padding: 14px 16px;
                }

            }


            @media (max-width: 560px) {

                .katalog-page .catalog-grid {
                    grid-template-columns: 1fr;
                }

                .katalog-page .detail-grid,
                .katalog-page .form-grid {
                    grid-template-columns: 1fr;
                }

                .katalog-page .form-group--full,
                .katalog-page .detail-item--full {
                    grid-column: auto;
                }

                .katalog-page .modal-overlay {
                    padding: 12px;
                }

                .katalog-page .modal-head {
                    padding: 16px 18px;
                }

                .katalog-page .modal-body {
                    padding: 18px;
                }

                .katalog-page .modal-foot {
                    padding: 14px 18px;
                }

            }


            @media (max-width: 480px) {

                .katalog-page .toast-wrap {
                    left: 16px;
                    right: 16px;
                    top: 16px;
                }

                .katalog-page .toast {
                    min-width: 0;
                    max-width: none;
                    width: 100%;
                }

            }
        </style>


        <script>
            document.addEventListener('DOMContentLoaded', function() {

                @if (session('success'))
                    showToast('Berhasil', @json(session('success')));
                @endif


                const searchInput = document.getElementById('searchKatalog');

                if (searchInput) {
                    searchInput.addEventListener('keyup', function() {
                        filterKatalog(this.value, true);
                    });
                }

                const perPageSelect = document.getElementById('perPageSelect');
                const paginationContainer = document.getElementById('paginationButtons');

                initComboSelect('perPageSelect');

                if (perPageSelect) {
                    perPageSelect.addEventListener('change', function() {
                        filterKatalog(undefined, true);
                    });
                }

                if (paginationContainer) {
                    paginationContainer.addEventListener('click', function(e) {
                        const btn = e.target.closest('button[data-page]');
                        if (!btn || btn.disabled) return;
                        window.katalogCurrentPage = parseInt(btn.dataset.page, 10);
                        filterKatalog();
                    });
                }

                filterKatalog(undefined, true);


                // Tutup modal saat klik area gelap di luar box
                document.querySelectorAll('.modal-overlay').forEach(function(overlay) {

                    overlay.addEventListener('click', function(e) {

                        if (e.target === overlay) {
                            overlay.classList.remove('active');
                        }

                    });

                });


                // Tutup modal dengan tombol Escape
                document.addEventListener('keydown', function(e) {

                    if (e.key === 'Escape') {

                        document
                            .querySelectorAll('.modal-overlay.active')
                            .forEach(function(overlay) {

                                overlay.classList.remove('active');

                            });

                    }

                });

            });


            /*
            |--------------------------------------------------------------------------
            | Helper: buka / tutup modal
            |--------------------------------------------------------------------------
            */

            function bukaModal(id) {
                document.getElementById(id).classList.add('active');
            }


            function tutupModal(id) {
                document.getElementById(id).classList.remove('active');
            }


            function formatRupiah(angka) {
                return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
            }


            /*
            |--------------------------------------------------------------------------
            | Toast notifikasi
            |--------------------------------------------------------------------------
            */

            function showToast(title, text, type = 'success') {

                const wrap = document.getElementById('toastWrap');

                const toast = document.createElement('div');

                toast.className =
                    'toast' +
                    (type === 'error' ? ' toast--error' : '');


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

                    <button
                        class="toast-close"
                        type="button"
                        aria-label="Tutup">
                        &times;
                    </button>
                `;


                function hapusToast() {

                    toast.classList.add('toast--leaving');

                    setTimeout(() => toast.remove(), 180);

                }


                toast
                    .querySelector('.toast-close')
                    .addEventListener('click', hapusToast);


                wrap.appendChild(toast);

                setTimeout(hapusToast, 3500);

            }


            /*
            |--------------------------------------------------------------------------
            | Pencarian produk + pagination
            |--------------------------------------------------------------------------
            */

            window.katalogCurrentPage = 1;

            function filterKatalog(keyword, resetPage) {

                const searchInput = document.getElementById('searchKatalog');
                const perPageSelect = document.getElementById('perPageSelect');
                const paginationContainer = document.getElementById('paginationButtons');
                const tableInfo = document.getElementById('tableInfoPagination');

                const k = (
                    keyword !== undefined ?
                    keyword :
                    (searchInput ? searchInput.value : '')
                ).toLowerCase().trim();

                const items = Array.from(
                    document.querySelectorAll('#catalogGrid .catalog-item')
                );

                const filtered = items.filter(function(item) {
                    return item.dataset.nama.includes(k);
                });

                const perPage = parseInt(perPageSelect ? perPageSelect.value : 8, 10) || 8;
                const totalPages = Math.ceil(filtered.length / perPage) || 1;

                if (resetPage) {
                    window.katalogCurrentPage = 1;
                }

                window.katalogCurrentPage = Math.min(
                    Math.max(window.katalogCurrentPage, 1),
                    totalPages
                );

                const currentPage = window.katalogCurrentPage;

                items.forEach(function(item) {
                    item.style.display = 'none';
                });

                const start = (currentPage - 1) * perPage;
                const end = start + perPage;

                filtered.slice(start, end).forEach(function(item) {
                    item.style.display = '';
                });


                const kosongCari = document.getElementById('catalogKosongCari');

                if (kosongCari) {
                    kosongCari.style.display =
                        (items.length > 0 && filtered.length === 0) ? '' : 'none';
                }


                if (tableInfo) {
                    tableInfo.innerHTML = filtered.length === 0 ?
                        'Tidak ada data yang ditampilkan' :
                        `Menampilkan produk <strong>${start + 1}</strong> - <strong>${Math.min(end, filtered.length)}</strong> dari <strong>${filtered.length}</strong>`;
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


            /*
            |--------------------------------------------------------------------------
            | Combo select (Produk per halaman) — sama dengan halaman warga & artikel
            |--------------------------------------------------------------------------
            */

            const comboSelectRegistry = {};

            function syncComboSelect(selectId) {
                if (comboSelectRegistry[selectId]) comboSelectRegistry[selectId]();
            }

            function initComboSelect(selectId) {
                const select = document.getElementById(selectId);
                const input = document.getElementById('cari_' + selectId);
                const list = document.getElementById('list_' + selectId);
                if (!select || !input || !list) return;

                input.readOnly = true;
                let aktif = -1;

                const teks = o => o.textContent.replace(/\s+/g, ' ').trim();
                const esc = v => String(v).replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));

                function sync() {
                    const o = select.options[select.selectedIndex];
                    input.value = o ? teks(o) : '';
                }

                function buka() {
                    list.innerHTML = Array.from(select.options).map(o =>
                        `<div class="combo-item${o.value === select.value ? ' is-selected' : ''}" data-value="${esc(o.value)}">${esc(teks(o))}</div>`
                    ).join('');
                    aktif = -1;
                    list.classList.add('show');
                }

                function tutup() {
                    list.classList.remove('show');
                }

                function pilih(value) {
                    const berubah = select.value !== String(value);
                    select.value = value;
                    sync();
                    tutup();
                    if (berubah) select.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }

                function sorot(arah) {
                    const items = list.querySelectorAll('.combo-item');
                    if (!items.length) return;
                    aktif = (aktif + arah + items.length) % items.length;
                    items.forEach((el, i) => el.classList.toggle('is-active', i === aktif));
                }

                input.addEventListener('focus', buka);
                input.addEventListener('click', () => {
                    if (!list.classList.contains('show')) buka();
                });
                input.addEventListener('blur', tutup);

                input.addEventListener('keydown', function(e) {
                    const items = list.querySelectorAll('.combo-item');
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (!list.classList.contains('show')) buka();
                        sorot(1);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        sorot(-1);
                    } else if (e.key === 'Enter') {
                        if (list.classList.contains('show') && aktif > -1 && items[aktif]) {
                            e.preventDefault();
                            pilih(items[aktif].dataset.value);
                        }
                    } else if (e.key === 'Escape' && list.classList.contains('show')) {
                        e.stopPropagation();
                        tutup();
                    }
                });

                list.addEventListener('mouseover', function(e) {
                    const item = e.target.closest('.combo-item');
                    if (!item) return;
                    list.querySelectorAll('.combo-item').forEach((el, i) => {
                        el.classList.toggle('is-active', el === item);
                        if (el === item) aktif = i;
                    });
                });

                list.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    const item = e.target.closest('.combo-item');
                    if (item) pilih(item.dataset.value);
                });

                comboSelectRegistry[selectId] = sync;
                sync();
            }


            /*
            |--------------------------------------------------------------------------
            | Tambah produk
            |--------------------------------------------------------------------------
            */

            function bukaModalTambah() {

                const form =
                    document.getElementById('formTambahProduk');

                if (form) {
                    form.reset();
                }

                bukaModal('modalTambah');

            }


            function simpanTambah(event) {

                event.preventDefault();


                const fotoInput =
                    document.getElementById('tambahfoto');

                const namaInput =
                    document.getElementById('tambahNama');

                const hargaInput =
                    document.getElementById('tambahHarga');

                const stokInput =
                    document.getElementById('tambahStok');


                // Pastikan foto dipilih
                if (!fotoInput.files || fotoInput.files.length === 0) {

                    showToast(
                        'Gagal',
                        'Foto produk wajib dipilih.',
                        'error'
                    );

                    return false;
                }


                const foto = fotoInput.files[0];


                // Validasi ukuran foto maksimal 2MB
                if (foto.size > 2 * 1024 * 1024) {

                    showToast(
                        'Gagal',
                        'Ukuran foto maksimal 2MB.',
                        'error'
                    );

                    return false;
                }


                const formData = new FormData();

                formData.append('foto', foto);
                formData.append(
                    'nama_produk',
                    namaInput.value.trim()
                );
                formData.append(
                    'harga',
                    hargaInput.value
                );
                formData.append(
                    'stok',
                    stokInput.value
                );


                fetch("{{ route('admin.katalog.store') }}", {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN':
                                document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    .content
                        },
                        body: formData
                    })
                    .then(async (res) => {

                        const data =
                            await res.json()
                            .catch(() => ({}));


                        if (!res.ok) {

                            if (data.errors) {

                                throw new Error(
                                    Object
                                        .values(data.errors)
                                        .flat()
                                        .join(' ')
                                );

                            }

                            throw new Error(
                                data.message ||
                                'Gagal menyimpan produk.'
                            );

                        }


                        return data;

                    })
                    .then((data) => {

                        tutupModal('modalTambah');

                        showToast(
                            'Berhasil',
                            data.message ||
                            'Produk baru telah ditambahkan.'
                        );

                        setTimeout(
                            () => window.location.reload(),
                            800
                        );

                    })
                    .catch((err) => {

                        showToast(
                            'Gagal menyimpan',
                            err.message ||
                            'Terjadi kesalahan saat menyimpan produk.',
                            'error'
                        );

                    });


                return false;

            }


            /*
            |--------------------------------------------------------------------------
            | Edit produk
            |--------------------------------------------------------------------------
            */

            function bukaModalEdit(produk) {

                document.getElementById('editId').value =
                    produk.id_produk;

                document.getElementById('editNama').value =
                    produk.nama_produk || '';

                document.getElementById('editHarga').value =
                    produk.harga || 0;

                document.getElementById('editStok').value =
                    produk.stok || 0;

                document.getElementById('editfoto').value = '';


                // Preview foto saat ini
                const imgPreview =
                    document.getElementById('editfotoPreview');

                const divKosong =
                    document.getElementById('editfotoKosong');


                if (produk.foto) {

                    imgPreview.src =
                        `/storage/${produk.foto}`;

                    imgPreview.style.display =
                        'block';

                    divKosong.style.display =
                        'none';

                } else {

                    imgPreview.style.display =
                        'none';

                    divKosong.style.display =
                        'flex';

                }


                bukaModal('modalEdit');

            }


            function simpanEdit(event) {

                event.preventDefault();


                const id =
                    document.getElementById('editId').value;

                const file =
                    document.getElementById('editfoto')
                    .files[0];


                // Validasi ukuran foto maksimal 2MB
                if (file && file.size > 2 * 1024 * 1024) {

                    showToast(
                        'Gagal',
                        'Ukuran foto maksimal 2MB.',
                        'error'
                    );

                    return false;

                }


                const formData = new FormData();

                formData.append('_method', 'PUT');

                if (file) {
                    formData.append('foto', file);
                }

                formData.append(
                    'nama_produk',
                    document.getElementById('editNama').value
                );

                formData.append(
                    'harga',
                    document.getElementById('editHarga').value
                );

                formData.append(
                    'stok',
                    document.getElementById('editStok').value
                );


                const baseUrl =
                    "{{ route('admin.katalog.index') }}";


                fetch(`${baseUrl}/${id}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN':
                                document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    .content
                        },
                        body: formData
                    })
                    .then(async (res) => {

                        if (!res.ok) {

                            const err =
                                await res.json()
                                .catch(() => ({}));


                            const pesan =
                                err.errors ?
                                    Object
                                        .values(err.errors)
                                        .flat()
                                        .join(' ') :
                                    (
                                        err.message ||
                                        'Gagal memperbarui produk.'
                                    );


                            throw new Error(pesan);

                        }


                        return res.json();

                    })
                    .then(() => {

                        tutupModal('modalEdit');

                        showToast(
                            'Berhasil disimpan',
                            'Perubahan data produk telah tersimpan.'
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
            | Detail produk
            |--------------------------------------------------------------------------
            */

            function lihatDetail(item) {

                document.getElementById('detailNama').textContent =
                    item.nama_produk || '-';

                document.getElementById('detailHarga').textContent =
                    formatRupiah(item.harga);

                document.getElementById('detailStok').textContent =
                    item.stok ?? '-';


                const foto =
                    document.getElementById('detailfoto');

                const kosong =
                    document.getElementById('detailfotoKosong');


                if (item.foto) {

                    foto.src =
                        `/storage/${item.foto}`;

                    foto.style.display =
                        '';

                    kosong.style.display =
                        'none';

                } else {

                    foto.style.display =
                        'none';

                    kosong.style.display =
                        'flex';

                }


                bukaModal('modalDetail');

            }


            /*
            |--------------------------------------------------------------------------
            | Hapus produk
            |--------------------------------------------------------------------------
            */

            let produkAkanDihapus = null;


            function hapusProduk(produk) {

                produkAkanDihapus =
                    produk;


                document.getElementById('hapusNama').textContent =
                    produk.nama_produk || '-';


                bukaModal('modalHapus');

            }


            function konfirmasiHapus() {

                if (!produkAkanDihapus) {
                    return;
                }


                const id =
                    produkAkanDihapus.id_produk;

                const namaDihapus =
                    produkAkanDihapus.nama_produk ||
                    'Produk';

                const baseUrl =
                    "{{ route('admin.katalog.index') }}";


                fetch(`${baseUrl}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN':
                                document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    .content
                        }
                    })
                    .then(async (res) => {

                        if (!res.ok) {

                            const err =
                                await res.json()
                                .catch(() => ({}));

                            throw new Error(
                                err.message ||
                                'Gagal menghapus produk.'
                            );

                        }


                        return res.json();

                    })
                    .then(() => {

                        tutupModal('modalHapus');

                        showToast(
                            'Produk berhasil dihapus.',
                            namaDihapus +
                            ' telah dihapus dari katalog.'
                        );

                        produkAkanDihapus = null;


                        setTimeout(
                            () => window.location.reload(),
                            800
                        );

                    })
                    .catch((err) => {

                        showToast(
                            'Gagal menghapus',
                            err.message,
                            'error'
                        );

                    });

            }
        </script>

    </div>

@endsection