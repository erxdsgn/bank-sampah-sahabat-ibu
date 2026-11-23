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
                                    <div class="artikel-ringkasan">{{ \Illuminate\Support\Str::limit(strip_tags($item->konten), 45) }}</div>
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
