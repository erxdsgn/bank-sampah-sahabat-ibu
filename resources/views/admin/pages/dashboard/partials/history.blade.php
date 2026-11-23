{{-- RIWAYAT SETORAN --}}
    <section class="card history-card">

        <div class="card-head">
            <div>
                <h2 class="card-title">Riwayat Setoran</h2>
                <p class="card-sub">Seluruh transaksi setoran yang tercatat</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="riwayatTable">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Warga</th>
                        <th>Jumlah</th>
                        <th>Nilai</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody id="riwayatTableBody">
                    @forelse($riwayat as $index => $setoran)
                        <tr data-row-riwayat>
                            <td class="riwayat-no"></td>

                            <td>{{ \Carbon\Carbon::parse($setoran->tanggal_setoran)->format('d/m/Y') }}</td>

                            <td><strong>{{ $setoran->nama }}</strong></td>

                            <td>{{ $setoran->jumlah_text }}</td>

                            <td>Rp {{ number_format($setoran->total_nilai, 0, ',', '.') }}</td>

                            <td>
                                <span class="badge badge--success">{{ $setoran->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-table">
                                <div class="empty-icon">📋</div>
                                <strong>Belum Ada Riwayat Setoran</strong>
                                <p class="empty-description">Belum ada transaksi setoran yang tercatat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="table-footer">

            <div class="table-info" id="riwayatInfoPagination">Menampilkan data...</div>

            <div class="table-pagination-controls">
                <div class="per-page">
                    <label for="cari_perPageRiwayat">Baris per halaman:</label>
                    <div class="combo combo--up combo--arrow" id="combo_perPageRiwayat">
                        <input type="text" id="cari_perPageRiwayat" class="combo-input" readonly
                            autocomplete="off" aria-label="Baris per halaman riwayat setoran">
                        <div class="combo-list" id="list_perPageRiwayat"></div>
                    </div>
                    <select id="perPageRiwayat" class="combo-hidden" tabindex="-1" aria-hidden="true">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="pagination-buttons" id="paginationButtonsRiwayat"></div>
            </div>

        </div>

    </section>
