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
            <button class="fpill active" type="button" data-filter="all">Semua</button>
            <button class="fpill" type="button" data-filter="pending">Menunggu</button>
            <button class="fpill" type="button" data-filter="approved">Disetujui</button>
            <button class="fpill" type="button" data-filter="rejected">Ditolak</button>
        </div>

    </div>


    <!-- TABLE -->
    <div class="table-responsive">

        <table class="data-table" id="setoranTable">

            <thead>
                <tr>
                    <th class="col-no">No</th>
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
                        | DETAIL SETORAN (BISA LEBIH DARI SATU JENIS SAMPAH)
                        |--------------------------------------------------------------------------
                        */
                        $details = $detailMap[$item->getKey()] ?? collect();

                        $items = $details
                            ->map(function ($d) use ($kategoriMap, $normalisasiSatuan) {
                                $katId = $d->id_kategori ?? $d->id_kategori_sampah;
                                $kat = $kategoriMap[$katId] ?? null;

                                return [
                                    'id_detail' => $d->id_detail_setoran ?? ($d->id_detail ?? ($d->id ?? null)),
                                    'id_kategori' => $katId,
                                    'nama_kategori' => optional($kat)->nama_kategori
                                                        ?? optional($kat)->nama_lengkap
                                                        ?? optional($kat)->nama
                                                        ?? optional($d->kategori)->nama_kategori,
                                    'satuan' => $normalisasiSatuan(
                                        $d->satuan ?? (optional($kat)->satuan ?? 'gram'),
                                    ),
                                    'jumlah' => (float) ($d->jumlah_satuan ?? ($d->jumlah ?? ($d->berat_gram ?? 0))),
                                    'subtotal' => isset($d->subtotal) ? (float) $d->subtotal : null,
                                ];
                            })
                            ->values()
                            ->all();

                        // Data lama tanpa detail: tampilkan satu baris dari data setoran
                        if (empty($items)) {
                            $katLamaId = $item->id_kategori ?? $item->id_kategori_sampah ?? null;
                            $katLama = $kategoriMap[$katLamaId] ?? null;
                            $items = [
                                [
                                    'id_detail' => null,
                                    'id_kategori' => $katLamaId,
                                    'nama_kategori' => optional($katLama)->nama_kategori
                                                        ?? optional($katLama)->nama_lengkap
                                                        ?? optional($item->kategori)->nama_kategori
                                                        ?? optional($item->kategori)->nama,
                                    'satuan' => $normalisasiSatuan($item->satuan ?? 'gram'),
                                    'jumlah' => (float) ($item->jumlah_satuan ?? ($item->jumlah ?? ($item->total_berat ?? 0))),
                                ],
                            ];
                        }

                        $namaList = collect($items)->pluck('nama_kategori')->filter()->unique()->values();

                        $totalNilai =
                            $item->total_nilai ??
                            $details->sum(function ($d) {
                                return (float) ($d->subtotal ?? 0);
                            });

                        $row = [
                            'id' => $item->getKey(),
                            'kode_transaksi' => $item->kode_transaksi ?? '-',
                            'status' => $item->status,
                            'warga' => [
                                'nama' => $item->warga->nama ?? '-',
                                'nik' => $item->warga->nik ?? '-',
                            ],
                            'items' => $items,
                            'total_nilai' => (float) $totalNilai,
                            'catatan_admin' => $item->catatan_admin ?? '',
                            'foto_bukti' => $item->foto_sampah ?? null,
                        ];

                        $tanggal = $item->tanggal_setoran ?? $item->created_at;
                    @endphp


                    <tr data-status="{{ $item->status }}">

                        <!-- NO -->
                        <td class="col-no">
                            <span class="mono no-urut">{{ $loop->iteration }}</span>
                            <span class="sr-only">{{ $row['kode_transaksi'] }}</span>
                        </td>

                        <!-- WARGA -->
                        <td>
                            <strong>{{ $row['warga']['nama'] }}</strong>
                            <br>
                            <span class="mono">{{ $row['warga']['nik'] }}</span>
                        </td>

                        <!-- KATEGORI -->
                        <td>
                            @if ($namaList->isEmpty())
                                <span class="kategori-tag">-</span>
                            @else
                                <div class="kategori-stack">
                                    @foreach ($namaList->take(2) as $nm)
                                        <span class="kategori-tag">{{ $nm }}</span>
                                    @endforeach

                                    @if ($namaList->count() > 2)
                                        <span class="kategori-tag kategori-tag--more"
                                            title="{{ $namaList->slice(2)->implode(', ') }}">
                                            +{{ $namaList->count() - 2 }} lainnya
                                        </span>
                                        <span class="sr-only">{{ $namaList->slice(2)->implode(', ') }}</span>
                                    @endif
                                </div>
                            @endif
                        </td>

                        <!-- JUMLAH -->
                        <td>
                            @if (count($row['items']) > 1)
                                <strong>{{ count($row['items']) }} jenis</strong>
                            @else
                                <strong>{{ $fmtJumlah($row['items'][0]['jumlah'] ?? 0) }}</strong>
                                <span class="unit">{{ $row['items'][0]['satuan'] ?? 'gram' }}</span>
                            @endif
                        </td>

                        <!-- NILAI -->
                        <td>
                            <strong>Rp {{ number_format($row['total_nilai'], 0, ',', '.') }}</strong>
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
                                <span class="status-badge status-badge--pending">Menunggu</span>
                            @elseif($item->status === 'approved')
                                <span class="status-badge status-badge--approved">Disetujui</span>
                            @else
                                <span class="status-badge status-badge--rejected">Ditolak</span>
                            @endif
                        </td>

                        <!-- AKSI -->
                        <td>
                            <div class="table-actions">
                                <button class="icon-btn {{ $item->status === 'pending' ? 'icon-btn--primary' : '' }}"
                                    title="{{ $item->status === 'pending' ? 'Lihat Detail & Verifikasi' : 'Lihat Detail' }}"
                                    type="button" onclick="bukaVerifikasi({{ Js::from($row) }})">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                        <circle cx="12" cy="12" r="2.5"></circle>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding:40px;">
                            <div style="font-size:30px; margin-bottom:10px;">🧾</div>
                            <strong>Belum Ada Setoran Masuk</strong>
                            <p style="margin:5px 0 0; color:#6b7280;">Belum ada warga yang mengisi form setoran.</p>
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>

        <div class="catalog-empty" id="setoranKosongCari" style="display: none;">
            <div class="empty-icon">🔍</div>
            <strong>Data Tidak Ditemukan</strong>
            <p class="empty-desc">Tidak ada setoran yang cocok dengan pencarian ini.</p>
        </div>

    </div>


    <!-- FOOTER -->
    <div class="table-footer">
        <div class="table-info" id="tableInfoPagination">Menampilkan data...</div>

        <div class="table-pagination-controls">
            <div class="per-page">
                <label for="cari_perPageSelect">Setoran per halaman:</label>
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
