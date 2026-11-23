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


            <!-- GRID PRODUK -->
            <div class="catalog-grid" id="catalogGrid">

                @forelse($katalogProduk as $produk)

                    <div class="catalog-item" data-nama="{{ strtolower($produk->nama_produk) }}">

                        <div class="catalog-img">
                            @if ($produk->foto)
                                <img src="{{ asset('storage/' . $produk->foto) }}" alt="{{ $produk->nama_produk }}">
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

                            @if ($produk->deskripsi)
                                <p class="catalog-desc">
                                    {{ $produk->deskripsi }}
                                </p>
                            @endif

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
                                <button class="icon-btn" title="Lihat Detail" type="button"
                                    onclick="lihatDetail({{ Js::from($produk) }})">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                        <circle cx="12" cy="12" r="2.5"></circle>
                                    </svg>
                                </button>

                                <!-- EDIT -->
                                <button class="icon-btn" title="Edit Produk" type="button"
                                    onclick="bukaModalEdit({{ Js::from($produk) }})">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                    </svg>
                                </button>

                                <!-- HAPUS -->
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
                        <div class="empty-icon">♻️</div>
                        <strong>Belum Ada Katalog Produk Daur Ulang</strong>
                        <p class="empty-text">
                            Silakan klik tombol "Tambah Produk" di kanan atas
                            untuk memasukkan data barang daur ulang.
                        </p>
                    </div>

                @endforelse

            </div>


            <!-- HASIL PENCARIAN KOSONG -->
            <div class="catalog-empty catalog-empty--search" id="catalogKosongCari">
                <div class="empty-icon">🔍</div>
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
