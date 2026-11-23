<!-- DATA TABLE -->
    <section class="card">
        <div class="table-toolbar">
            <div class="table-search">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchKategori" placeholder="Cari nama kategori atau satuan..."
                    autocomplete="off">
            </div>

            <div class="toolbar-right">
                <button class="btn btn--ghost" type="button" onclick="bukaModalBulanan()">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="17" rx="2"></rect>
                        <path d="M3 10h18M8 2v4M16 2v4"></path>
                    </svg>
                    Harga per Bulan
                </button>

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
            <table class="data-table" id="kategoriTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Satuan Unit</th>
                        <th>Berlaku Sejak</th>
                        <th>Harga Aktif</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="kategoriTableBody">
                    @forelse($kategoriInduk as $index => $kategori)
                        @php
                            $tglInduk = $kategori->hargaTerbaru
                                ? \Carbon\Carbon::parse($kategori->hargaTerbaru->tanggal_berlaku)
                                : null;
                        @endphp

                        <tr data-row-kategori data-group="{{ $kategori->id_kategori }}">
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $kategori->nama_kategori }}</strong></td>
                            <td><span class="badge badge--satuan">{{ $formatSatuan($kategori->satuan) }}</span></td>
                            <td class="nowrap">{{ $tglInduk ? $tglInduk->translatedFormat('d M Y') : '-' }}</td>
                            <td>
                                @if ($kategori->hargaTerbaru)
                                    <strong class="saldo">Rp
                                        {{ number_format($kategori->hargaTerbaru->harga_satuan, 0, ',', '.') }}</strong>
                                @else
                                    <span class="muted">Belum diatur</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $kategori->hargaTerbaru ? 'badge--success' : 'badge--warning' }}">
                                    {{ $kategori->hargaTerbaru ? 'Aktif' : 'Belum Diatur' }}
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="icon-btn" title="Edit Kategori" type="button"
                                        onclick="editKategoriModal({{ Js::from(['id_kategori' => $kategori->id_kategori, 'id_induk' => $kategori->id_induk, 'nama_kategori' => $kategori->nama_kategori, 'satuan' => $kategori->satuan, 'punya_anak' => $kategori->anak->count() > 0]) }})">
                                        {!! $iconEdit !!}
                                    </button>
                                    <button class="icon-btn icon-btn--harga" title="Kelola Harga" type="button"
                                        onclick="kelolaHarga({{ Js::from(['id_kategori' => $kategori->id_kategori, 'nama_kategori' => $kategori->nama_kategori, 'satuan' => $kategori->satuan, 'store_url' => route('admin.kategori-harga.harga.store', $kategori->id_kategori)]) }})">
                                        {!! $iconHarga !!}
                                    </button>
                                    <button class="icon-btn icon-btn--danger" title="Hapus Kategori" type="button"
                                        onclick="hapusKategori({{ Js::from(['id_kategori' => $kategori->id_kategori, 'nama_kategori' => $kategori->nama_kategori]) }})">
                                        {!! $iconHapus !!}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        @foreach ($kategori->anak as $sub)
                            @php
                                $tglSub = $sub->hargaTerbaru
                                    ? \Carbon\Carbon::parse($sub->hargaTerbaru->tanggal_berlaku)
                                    : null;
                            @endphp

                            <tr data-row-kategori data-group="{{ $kategori->id_kategori }}" class="sub-row">
                                <td></td>
                                <td class="sub-cell"><span class="sub-arrow">↳</span> {{ $sub->nama_kategori }}</td>
                                <td><span class="badge badge--satuan">{{ $formatSatuan($sub->satuan) }}</span></td>
                                <td class="nowrap">{{ $tglSub ? $tglSub->translatedFormat('d M Y') : '-' }}</td>
                                <td>
                                    @if ($sub->hargaTerbaru)
                                        <strong class="saldo">Rp
                                            {{ number_format($sub->hargaTerbaru->harga_satuan, 0, ',', '.') }}</strong>
                                    @else
                                        <span class="muted">Belum diatur</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $sub->hargaTerbaru ? 'badge--success' : 'badge--warning' }}">
                                        {{ $sub->hargaTerbaru ? 'Aktif' : 'Belum Diatur' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button class="icon-btn" title="Edit Kategori" type="button"
                                            onclick="editKategoriModal({{ Js::from(['id_kategori' => $sub->id_kategori, 'id_induk' => $sub->id_induk, 'nama_kategori' => $sub->nama_kategori, 'satuan' => $sub->satuan, 'punya_anak' => false]) }})">
                                            {!! $iconEdit !!}
                                        </button>
                                        <button class="icon-btn icon-btn--harga" title="Kelola Harga" type="button"
                                            onclick="kelolaHarga({{ Js::from(['id_kategori' => $sub->id_kategori, 'nama_kategori' => $sub->nama_kategori, 'satuan' => $sub->satuan, 'store_url' => route('admin.kategori-harga.harga.store', $sub->id_kategori)]) }})">
                                            {!! $iconHarga !!}
                                        </button>
                                        <button class="icon-btn icon-btn--danger" title="Hapus Kategori" type="button"
                                            onclick="hapusKategori({{ Js::from(['id_kategori' => $sub->id_kategori, 'nama_kategori' => $sub->nama_kategori]) }})">
                                            {!! $iconHapus !!}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state-cell">
                                <div class="empty-icon">📦</div>
                                <strong>Belum Ada Data Kategori</strong>
                                <p class="empty-desc">Belum ada kategori sampah yang terdaftar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="catalog-empty" id="kategoriKosongCari" style="display: none;">
                <div class="empty-icon">🔍</div>
                <strong>Data Tidak Ditemukan</strong>
                <p class="empty-desc">Tidak ada kategori yang cocok dengan pencarian ini.</p>
            </div>
        </div>

        <div class="table-footer">
            <div class="table-info" id="tableInfoPagination">Menampilkan data...</div>

            <div class="table-pagination-controls">
                <div class="per-page">
                    <label for="cari_perPageSelect">Kategori per halaman:</label>
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
