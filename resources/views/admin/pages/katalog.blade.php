@extends('admin.layouts.app')

@section('title', 'Katalog Barang')
@section('active', 'katalog')
@section('crumbs', 'Keuangan & Produk | Katalog Barang')

@section('content')

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


    <section class="card">

        <!-- SEARCH -->
        <div class="table-toolbar">

            <div class="table-search">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchKatalog" placeholder="Cari nama produk..." autocomplete="off">
            </div>

            <button class="btn btn--ghost" type="button" onclick="window.location.reload()">
                <svg viewBox="0 0 24 24">
                    <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                    <path d="M21 3v5h-5"></path>
                </svg>
                Refresh
            </button>

        </div>


        <!-- GRID -->
        <div class="catalog-grid" id="catalogGrid">
            @forelse($katalogProduk as $produk)
                <div class="catalog-item" data-nama="{{ strtolower($produk->nama_produk) }}">

                    <div class="catalog-img">
                        @if ($produk->foto)
                            <img src="{{ asset('storage/' . $produk->foto) }}" alt="{{ $produk->nama_produk }}">
                        @else
                            <div class="catalog-img-kosong">📸 Tidak ada foto</div>
                        @endif
                    </div>

                    <div class="catalog-body">
                        <h3 class="catalog-title">{{ $produk->nama_produk }}</h3>

                        <div class="catalog-price">
                            Rp {{ number_format($produk->harga, 0, ',', '.') }}
                        </div>

                        <div class="catalog-stok">
                            @if ($produk->stok <= 5)
                                <span class="badge badge--warning">Stok Menipis: {{ $produk->stok }}</span>
                            @else
                                <span class="badge badge--success">Stok: {{ $produk->stok }}</span>
                            @endif
                        </div>

                        <div class="catalog-actions">
                            <button class="icon-btn" title="Lihat Detail" type="button"
                                onclick="lihatDetail({{ Js::from($produk) }})">
                                <svg viewBox="0 0 24 24">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                    <circle cx="12" cy="12" r="2.5"></circle>
                                </svg>
                            </button>

                            <button class="icon-btn" title="Edit Produk" type="button"
                                onclick="bukaModalEdit({{ Js::from($produk) }})">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                </svg>
                            </button>

                            <button class="icon-btn danger" title="Hapus Produk" type="button"
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
                    <div style="font-size: 36px; margin-bottom: 12px;">♻️</div>
                    <strong>Belum Ada Katalog Produk Daur Ulang</strong>
                    <p style="margin: 6px 0 0; color: #6b7280; font-size: 13px;">
                        Silakan klik tombol "Tambah Produk" di kanan atas untuk memasukkan data barang daur ulang.
                    </p>
                </div>
            @endforelse
        </div>

        <div class="catalog-empty" id="catalogKosongCari" style="display: none;">
            <div style="font-size: 36px; margin-bottom: 12px;">🔍</div>
            <strong>Produk Tidak Ditemukan</strong>
            <p style="margin: 6px 0 0; color: #6b7280; font-size: 13px;">
                Tidak ada produk yang cocok dengan kata kunci pencarian.
            </p>
        </div>


        <!-- FOOTER -->
        <div class="table-footer">
            <div class="table-info">
                Total produk: <strong id="totalProduk">{{ $katalogProduk->count() }}</strong>
            </div>
        </div>

    </section>


    <!-- ============================================= -->
    <!-- TOAST NOTIFIKASI                               -->
    <!-- ============================================= -->
    <div class="toast-wrap" id="toastWrap"></div>


    <!-- ============================================= -->
    <!-- MODAL: DETAIL PRODUK                           -->
    <!-- ============================================= -->
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
                <button class="modal-close" type="button" onclick="tutupModal('modalDetail')">&times;</button>
            </div>

            <div class="modal-body">

                <img id="detailfoto" class="preview-img preview-img--besar" src="" alt="Preview"
                    style="display: none;">
                <div id="detailfotoKosong" class="catalog-img-kosong catalog-img-kosong--besar">
                    📸 Tidak ada foto
                </div>

                <div class="detail-grid">

                    <div class="detail-item detail-item--full">
                        <span class="detail-label">Nama Barang</span>
                        <span class="detail-nama" id="detailNama">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Harga</span>
                        <span class="detail-value saldo" id="detailHarga">-</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Stok Tersedia</span>
                        <span class="detail-value" id="detailStok">-</span>
                    </div>

                </div>
            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalDetail')">Tutup</button>
            </div>

        </div>
    </div>


    <!-- ============================================= -->
    <!-- MODAL: TAMBAH PRODUK                           -->
    <!-- ============================================= -->
    <div class="modal-overlay" id="modalTambah">
        <div class="modal-box">

            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Produk Baru
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalTambah')">&times;</button>
            </div>

            <form id="formTambahProduk" onsubmit="return simpanTambah(event)">
                <div class="modal-body">

                    <div class="form-grid">

                        <div class="form-group form-group--full">
                            <label for="tambahfoto">Foto Produk</label>
                            <input type="file" id="tambahfoto" accept="image/jpeg,image/png,image/jpg,image/webp"
                                required>
                            <span class="field-hint">Format JPG, PNG, atau WEBP. Maksimal 2MB.</span>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="tambahNama">Nama Barang</label>
                            <input type="text" id="tambahNama" required>
                        </div>

                        <div class="form-group">
                            <label for="tambahHarga">Harga (Rp)</label>
                            <input type="number" id="tambahHarga" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="tambahStok">Stok</label>
                            <input type="number" id="tambahStok" min="0" required>
                        </div>

                    </div>

                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalTambah')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan Produk</button>
                </div>
            </form>

        </div>
    </div>


    <!-- ============================================= -->
    <!-- MODAL: EDIT PRODUK                             -->
    <!-- ============================================= -->
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
                <button class="modal-close" type="button" onclick="tutupModal('modalEdit')">&times;</button>
            </div>

            <form id="formEditProduk" onsubmit="return simpanEdit(event)">
                <div class="modal-body">

                    <input type="hidden" id="editId">

                    <div class="form-grid">

                        <div class="form-group form-group--full">
                            <span class="detail-label">Foto Saat Ini</span>
                            <img id="editfotoPreview" class="preview-img" src="" alt="Preview"
                                style="display: none;">
                            <div id="editfotoKosong" class="catalog-img-kosong catalog-img-kosong--kecil">
                                📸 Tidak ada foto
                            </div>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="editfoto">Ganti Foto (opsional)</label>
                            <input type="file" id="editfoto" accept="image/jpeg,image/png,image/jpg,image/webp">
                            <span class="field-hint">Biarkan kosong jika tidak ingin mengubah foto. Maks 2MB.</span>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="editNama">Nama Barang</label>
                            <input type="text" id="editNama" required>
                        </div>

                        <div class="form-group">
                            <label for="editHarga">Harga (Rp)</label>
                            <input type="number" id="editHarga" min="0" required>
                        </div>

                        <div class="form-group">
                            <label for="editStok">Stok</label>
                            <input type="number" id="editStok" min="0" required>
                        </div>

                    </div>

                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalEdit')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan Perubahan</button>
                </div>
            </form>

        </div>
    </div>


    <!-- ============================================= -->
    <!-- MODAL: KONFIRMASI HAPUS                        -->
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

                <h3 class="confirm-title">Hapus Produk?</h3>

                <p class="confirm-text">
                    Anda akan menghapus produk
                    <strong id="hapusNama">-</strong>
                    dari katalog.
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
           CARD & TOOLBAR
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
            flex-wrap: wrap;
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

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge--warning {
            background: #fef3c7;
            color: #d97706;
        }

        .badge--success {
            background: #dcfce7;
            color: #15803d;
        }

        .badge--danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        .saldo {
            white-space: nowrap;
        }

        /* =========================
           CATALOG GRID
        ========================= */

        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            padding: 4px 24px 24px;
        }

        .catalog-item {
            background: var(--surface, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform .2s, box-shadow .2s;
        }

        .catalog-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, .08);
        }

        .catalog-img {
            width: 100%;
            height: 180px;
            background: #edf2f7;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .catalog-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .catalog-img-kosong {
            color: #a0aec0;
            font-size: 13px;
            font-weight: 500;
        }

        .catalog-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            flex: 1;
            gap: 6px;
        }

        .catalog-title {
            font-size: 15px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .catalog-price {
            font-size: 15px;
            color: var(--primary, #24a86b);
            font-weight: 700;
        }

        .catalog-stok {
            margin-bottom: 10px;
        }

        .catalog-actions {
            display: flex;
            gap: 6px;
            border-top: 1px solid #edf0f2;
            padding-top: 12px;
            margin-top: auto;
        }

        .catalog-empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
        }

        .catalog-img-kosong--kecil {
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #edf2f7;
            border-radius: 8px;
        }

        .catalog-img-kosong--besar {
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #edf2f7;
            border-radius: 10px;
            margin-bottom: 18px;
        }

        .preview-img {
            width: 100%;
            max-height: 140px;
            object-fit: cover;
            border-radius: 8px;
        }

        .preview-img--besar {
            max-height: 220px;
            margin-bottom: 18px;
        }

        /* =========================
           TOMBOL AKSI
        ========================= */

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
            flex: 1;
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

        .icon-btn.danger {
            color: #dc2626;
        }

        .icon-btn.danger:hover {
            background: #fef2f2;
            border-color: #fecaca;
        }

        /* =========================
           MODAL (tema mengikuti .card)
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
            max-width: 560px;
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

        /* --- Detail modal --- */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 20px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .detail-item--full {
            grid-column: 1 / -1;
        }

        .detail-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #9ca3af;
            display: block;
        }

        .detail-nama {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }

        .detail-value {
            font-size: 14px;
            color: #1f2937;
        }

        /* --- Form modal --- */
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
        }

        .form-group input[type="file"] {
            padding: 7px 10px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary, #4338ca);
        }

        .field-hint {
            font-size: 11px;
            color: #9ca3af;
        }

        /* --- Delete modal --- */
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

        @media (max-width: 560px) {

            .detail-grid,
            .form-grid {
                grid-template-columns: 1fr;
            }

            .table-search {
                width: 100%;
            }
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
        document.addEventListener('DOMContentLoaded', function() {

            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif

            const searchInput = document.getElementById('searchKatalog');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    filterKatalog(this.value);
                });
            }

            // Tutup modal saat klik area gelap di luar box
            document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) overlay.classList.remove('active');
                });
            });

            // Tutup modal dengan tombol Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay.active').forEach(function(overlay) {
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
        | Toast notifikasi (pengganti alert() bawaan browser)
        |--------------------------------------------------------------------------
        */

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
        | Pencarian produk
        |--------------------------------------------------------------------------
        */

        function filterKatalog(keyword) {
            const k = (keyword || '').toLowerCase().trim();
            const items = document.querySelectorAll('#catalogGrid .catalog-item');
            let tampil = 0;

            items.forEach(function(item) {
                const cocok = item.dataset.nama.includes(k);
                item.style.display = cocok ? '' : 'none';
                if (cocok) tampil++;
            });

            const kosongCari = document.getElementById('catalogKosongCari');
            if (kosongCari) {
                kosongCari.style.display = (items.length > 0 && tampil === 0) ? '' : 'none';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Tambah produk
        |--------------------------------------------------------------------------
        */

        function bukaModalTambah() {
            const form = document.getElementById('formTambahProduk');
            if (form) form.reset();
            bukaModal('modalTambah');
        }

        function simpanTambah(event) {
            event.preventDefault();

            const fotoInput = document.getElementById('tambahfoto');
            const namaInput = document.getElementById('tambahNama');
            const hargaInput = document.getElementById('tambahHarga');
            const stokInput = document.getElementById('tambahStok');

            // Pastikan foto dipilih
            if (!fotoInput.files || fotoInput.files.length === 0) {
                showToast('Gagal', 'Foto produk wajib dipilih.', 'error');
                return false;
            }

            const foto = fotoInput.files[0];

            // Validasi ukuran foto maksimal 2MB
            if (foto.size > 2 * 1024 * 1024) {
                showToast('Gagal', 'Ukuran foto maksimal 2MB.', 'error');
                return false;
            }

            const formData = new FormData();

            formData.append('foto', foto);
            formData.append('nama_produk', namaInput.value.trim());
            formData.append('harga', hargaInput.value);
            formData.append('stok', stokInput.value);

            fetch("{{ route('admin.katalog.store') }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));

                    if (!res.ok) {
                        if (data.errors) {
                            throw new Error(Object.values(data.errors).flat().join(' '));
                        }
                        throw new Error(data.message || 'Gagal menyimpan produk.');
                    }

                    return data;
                })
                .then((data) => {
                    tutupModal('modalTambah');
                    showToast('Berhasil', data.message || 'Produk baru telah ditambahkan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menyimpan', err.message || 'Terjadi kesalahan saat menyimpan produk.', 'error');
                });

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Edit produk
        |--------------------------------------------------------------------------
        */

        function bukaModalEdit(produk) {

            document.getElementById('editId').value = produk.id_produk;
            document.getElementById('editNama').value = produk.nama_produk || '';
            document.getElementById('editHarga').value = produk.harga || 0;
            document.getElementById('editStok').value = produk.stok || 0;
            document.getElementById('editfoto').value = '';

            // Preview foto saat ini
            const imgPreview = document.getElementById('editfotoPreview');
            const divKosong = document.getElementById('editfotoKosong');

            if (produk.foto) {
                imgPreview.src = `/storage/${produk.foto}`;
                imgPreview.style.display = 'block';
                divKosong.style.display = 'none';
            } else {
                imgPreview.style.display = 'none';
                divKosong.style.display = 'flex';
            }

            bukaModal('modalEdit');
        }

        function simpanEdit(event) {
            event.preventDefault();

            const id = document.getElementById('editId').value;
            const file = document.getElementById('editfoto').files[0];

            // Validasi ukuran foto maksimal 2MB
            if (file && file.size > 2 * 1024 * 1024) {
                showToast('Gagal', 'Ukuran foto maksimal 2MB.', 'error');
                return false;
            }

            const formData = new FormData();
            formData.append('_method', 'PUT');
            if (file) formData.append('foto', file);
            formData.append('nama_produk', document.getElementById('editNama').value);
            formData.append('harga', document.getElementById('editHarga').value);
            formData.append('stok', document.getElementById('editStok').value);

            const baseUrl = "{{ route('admin.katalog.index') }}";

            fetch(`${baseUrl}/${id}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        const pesan = err.errors ?
                            Object.values(err.errors).flat().join(' ') :
                            (err.message || 'Gagal memperbarui produk.');
                        throw new Error(pesan);
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalEdit');
                    showToast('Berhasil disimpan', 'Perubahan data produk telah tersimpan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menyimpan', err.message, 'error');
                });

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Detail produk
        |--------------------------------------------------------------------------
        */

        function lihatDetail(item) {
            document.getElementById('detailNama').textContent = item.nama_produk || '-';
            document.getElementById('detailHarga').textContent = formatRupiah(item.harga);
            document.getElementById('detailStok').textContent = item.stok ?? '-';

            const foto = document.getElementById('detailfoto');
            const kosong = document.getElementById('detailfotoKosong');

            if (item.foto) {
                foto.src = `/storage/${item.foto}`;
                foto.style.display = '';
                kosong.style.display = 'none';
            } else {
                foto.style.display = 'none';
                kosong.style.display = 'flex';
            }

            bukaModal('modalDetail');
        }


        /*
        |--------------------------------------------------------------------------
        | Hapus produk -> popup konfirmasi (pengganti confirm() bawaan browser)
        |--------------------------------------------------------------------------
        */

        let produkAkanDihapus = null;

        function hapusProduk(produk) {

            produkAkanDihapus = produk;

            document.getElementById('hapusNama').textContent = produk.nama_produk || '-';

            bukaModal('modalHapus');
        }

        function konfirmasiHapus() {

            if (!produkAkanDihapus) {
                return;
            }

            const id = produkAkanDihapus.id_produk;
            const namaDihapus = produkAkanDihapus.nama_produk || 'Produk';
            const baseUrl = "{{ route('admin.katalog.index') }}";

            fetch(`${baseUrl}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Gagal menghapus produk.');
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalHapus');
                    showToast('Produk berhasil dihapus.', namaDihapus + ' telah dihapus dari katalog.');
                    produkAkanDihapus = null;
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menghapus', err.message, 'error');
                });
        }
    </script>

@endsection
