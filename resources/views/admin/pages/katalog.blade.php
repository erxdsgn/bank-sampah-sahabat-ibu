@extends('admin.layouts.app')

@section('title', 'Katalog Barang Daur Ulang')
@section('active', 'katalog')
@section('crumbs', 'Keuangan & Produk | Katalog Barang')

@push('styles')
    @vite('resources/css/pages/katalog.css')
@endpush

@section('content')
    <div class="katalog-page">

        {{-- HERO --}}
        <section class="hero">
            <div class="hero-text">
                <span class="eyebrow">KEUANGAN & PRODUK</span>
                <h1 class="hero-title">Katalog <span class="accent">Barang</span></h1>
                <p class="hero-sub">Kelola informasi produk hasil daur ulang Bank Sampah</p>
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

        {{-- CARD KATALOG --}}
        <section class="card">

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
                            <div class="catalog-price">Rp {{ number_format($produk->harga, 0, ',', '.') }}</div>

                            @if ($produk->deskripsi)
                                <p class="catalog-desc">{{ $produk->deskripsi }}</p>
                            @endif

                            <div class="catalog-stok">
                                @if ($produk->stok <= 5)
                                    <span class="badge badge--warning">Stok Menipis: {{ $produk->stok }}</span>
                                @else
                                    <span class="badge badge--success">Stok: {{ $produk->stok }}</span>
                                @endif
                            </div>

                            <div class="catalog-actions">
                                <button class="icon-btn" title="Lihat Detail" type="button"
                                    onclick="lihatDetail({{ $produk->id_produk }})">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                        <circle cx="12" cy="12" r="2.5"></circle>
                                    </svg>
                                </button>
                                <button class="icon-btn" title="Edit Produk" type="button"
                                    onclick="bukaModalEdit({{ $produk->id_produk }})">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                    </svg>
                                </button>
                                <button class="icon-btn danger" title="Hapus Produk" type="button"
                                    onclick="hapusProduk({{ $produk->id_produk }})">
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
                        <div class="empty-icon">♻️</div>
                        <strong>Belum Ada Katalog Produk Daur Ulang</strong>
                        <p class="empty-text">
                            Silakan klik tombol "Tambah Produk" di kanan atas
                            untuk memasukkan data barang daur ulang.
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="catalog-empty catalog-empty--search" id="catalogKosongCari">
                <div class="empty-icon">🔍</div>
                <strong>Produk Tidak Ditemukan</strong>
                <p class="empty-text">Tidak ada produk yang cocok dengan kata kunci pencarian.</p>
            </div>

            <div class="table-footer">
                <div class="table-info" id="tableInfoPagination">Menampilkan data...</div>
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

        <div class="toast-wrap" id="toastWrap"></div>

        {{-- MODAL DETAIL --}}
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
                    <div id="detailfotoWrap" class="detail-foto-wrap" onclick="bukaLightboxGambar()" title="Klik untuk memperbesar gambar">
                        <img id="detailfoto" class="preview-img preview-img--besar" src="" alt="Preview">
                        <span class="detail-foto-hint">🔍 Klik untuk perbesar</span>
                    </div>
                    <div id="detailfotoKosong" class="catalog-img-kosong catalog-img-kosong--besar">📸 Tidak ada foto
                    </div>
                    <div class="detail-grid">
                        <div class="detail-item detail-item--full">
                            <span class="detail-label">Nama Barang</span>
                            <span class="detail-nama" id="detailNama">-</span>
                        </div>
                        <div class="detail-item detail-item--full">
                            <span class="detail-label">Deskripsi</span>
                            <span class="detail-value detail-deskripsi" id="detailDeskripsi">-</span>
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

        {{-- MODAL LIGHTBOX GAMBAR PENUH --}}
        <div class="modal-overlay lightbox-overlay" id="modalLightbox">
            <button class="lightbox-close" type="button" aria-label="Tutup" onclick="tutupModal('modalLightbox')">&times;</button>
            <div class="lightbox-content">
                <img id="lightboxImg" class="lightbox-img" src="" alt="Pratinjau Gambar Penuh">
                <div id="lightboxCaption" class="lightbox-caption"></div>
            </div>
        </div>

        {{-- MODAL TAMBAH --}}
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
                <form id="formTambahProduk" class="modal-form" onsubmit="return simpanTambah(event)">
                    <div class="modal-body">
                        <div class="form-grid">
                            <div class="form-group form-group--full">
                                <label for="tambahfoto">Foto Produk</label>
                                <input class="form-control" type="file" id="tambahfoto" accept="image/jpeg,image/png,image/jpg,image/webp,image/heic,image/heif,.heic,.heif"
                                    required>
                                <span class="field-hint">Format JPG, PNG, WEBP, atau HEIC. Maksimal 2MB.</span>
                            </div>
                            <div class="form-group form-group--full">
                                <label for="tambahNama">Nama Barang</label>
                                <input class="form-control" type="text" id="tambahNama" maxlength="80" required>
                            </div>
                            <div class="form-group form-group--full">
                                <label for="tambahDeskripsi">Deskripsi Produk</label>
                                <textarea class="form-control" id="tambahDeskripsi" rows="4" maxlength="500"
                                    placeholder="Contoh: Tas dari anyaman plastik bekas, tahan air, ukuran 30x25 cm."></textarea>
                                <span class="field-hint">Opsional, maksimal 500 karakter.</span>
                            </div>
                            <div class="form-group">
                                <label for="tambahHarga">Harga (Rp)</label>
                                <input class="form-control" type="number" id="tambahHarga" min="1" max="500000" required>
                            </div>
                            <div class="form-group">
                                <label for="tambahStok">Stok</label>
                                <input class="form-control" type="number" id="tambahStok" min="1" max="500" required>
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

        {{-- MODAL EDIT --}}
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
                <form id="formEditProduk" class="modal-form" onsubmit="return simpanEdit(event)">
                    <div class="modal-body">
                        <input type="hidden" id="editId">
                        <div class="form-grid">
                            <div class="form-group form-group--full">
                                <span class="detail-label">Foto Saat Ini</span>
                                <img id="editfotoPreview" class="preview-img" src="" alt="Preview">
                                <div id="editfotoKosong" class="catalog-img-kosong catalog-img-kosong--kecil">📸 Tidak ada
                                    foto</div>
                            </div>
                            <div class="form-group form-group--full">
                                <label for="editfoto">Ganti Foto (opsional)</label>
                                <input class="form-control" type="file" id="editfoto" accept="image/jpeg,image/png,image/jpg,image/webp,image/heic,image/heif,.heic,.heif">
                                <span class="field-hint">Biarkan kosong jika tidak ingin mengubah foto. Maks 2MB.</span>
                            </div>
                            <div class="form-group form-group--full">
                                <label for="editNama">Nama Barang</label>
                                <input class="form-control" type="text" id="editNama" maxlength="80" required>
                            </div>
                            <div class="form-group form-group--full">
                                <label for="editDeskripsi">Deskripsi Produk</label>
                                <textarea class="form-control" id="editDeskripsi" rows="4" maxlength="500" placeholder="Tulis deskripsi singkat produk."></textarea>
                                <span class="field-hint">Opsional, maksimal 500 karakter.</span>
                            </div>
                            <div class="form-group">
                                <label for="editHarga">Harga (Rp)</label>
                                <input class="form-control" type="number" id="editHarga" min="1" max="500000" required>
                            </div>
                            <div class="form-group">
                                <label for="editStok">Stok</label>
                                <input class="form-control" type="number" id="editStok" min="1" max="500" required>
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
                    <h3 class="confirm-title">Hapus Produk?</h3>
                    <p class="confirm-text">
                        Anda akan menghapus produk <strong id="hapusNama">-</strong> dari katalog.
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="modal-foot modal-foot--center">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHapus')">Batal</button>
                    <button class="btn btn--danger" type="button" onclick="konfirmasiHapus()">Ya, Hapus</button>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>

    <script>
        window.__KATALOG_DATA__ = @json($katalogProduk);
        window.__KATALOG_URLS__ = {
            store: @json(route('admin.katalog.store')),
            update: @json(route('admin.katalog.update', ['id' => ':id'])),
            destroy: @json(route('admin.katalog.destroy', ['id' => ':id'])),
            gambar: @json(asset('storage')),
        };

        document.addEventListener('DOMContentLoaded', function() {
            const inputTambah = document.getElementById('tambahfoto');
            if (inputTambah) {
                inputTambah.addEventListener('change', async function() {
                    await convertHeicToJpg(this);
                });
            }

            const inputEdit = document.getElementById('editfoto');
            if (inputEdit) {
                inputEdit.addEventListener('change', async function() {
                    await convertHeicToJpg(this);
                });
            }

            async function convertHeicToJpg(inputElement) {
                const file = inputElement.files[0];
                if (!file) return;

                if (file.name.toLowerCase().endsWith('.heic') || file.name.toLowerCase().endsWith('.heif') || file.type === 'image/heic') {
                    try {
                        const convertedBlob = await heic2any({
                            blob: file,
                            toType: "image/jpeg",
                            quality: 0.8
                        });

                        const newFile = new File([convertedBlob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                            type: "image/jpeg",
                            lastModified: Date.now()
                        });

                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(newFile);
                        inputElement.files = dataTransfer.files;

                        if (window.showToast) {
                            window.showToast('Info', 'Foto HEIC berhasil dikonversi ke JPG.', 'success');
                        }
                    } catch (error) {
                        console.error('Gagal konversi HEIC:', error);
                        alert('Gagal memproses gambar HEIC. Pastikan formatnya benar.');
                        inputElement.value = '';
                    }
                }
            }
        });
    </script>

    @vite('resources/js/pages/katalog.js')
@endpush
