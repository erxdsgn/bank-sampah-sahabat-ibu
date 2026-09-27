@extends('admin.layouts.app')

@section('title', 'Penjualan Sampah')
@section('active', 'penjualan-pengepul')
@section('crumbs', 'Transaksi Sampah | Penjualan Sampah')

@section('content')

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">TRANSAKSI SAMPAH</span>
            <h1 class="hero-title">Penjualan <span class="accent">Sampah</span></h1>
            <p class="hero-sub">Catat transaksi penjualan sampah bank sampah ke pengepul[cite: 1].</p>
        </div>

        <div class="hero-actions">
            <button class="btn btn--primary" type="button" onclick="bukaModal('modalTambahPenjualan')">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"></path>
                </svg>
                Tambah Penjualan
            </button>
        </div>
    </section>

    <section class="card">
        <!-- SEARCH TABLE -->
        <div class="table-toolbar">
            <div class="table-search">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchPenjualan" placeholder="Cari nama pengepul, kategori, atau tanggal..."
                    autocomplete="off">
            </div>

            <button class="btn btn--ghost" type="button" onclick="window.location.reload()">
                <svg viewBox="0 0 24 24">
                    <path d="M21 12a9 9 0 1 1-3-6.7L21 8"></path>
                    <path d="M21 3v5h-5"></path>
                </svg>
                Refresh
            </button>
        </div>

        <!-- TABLE PENJUALAN -->
        <div class="table-responsive">
            <table class="data-table" id="penjualanTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Pengepul</th>
                        <th>Kategori Sampah</th>
                        <th>Kuantitas</th>
                        <th>Harga Satuan</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barangKeluar as $index => $item)
                        @php
                            $satuanKat = strtolower($item->kategori->satuan ?? 'kg');
                            $isKg = ($satuanKat === 'kg');
                            // Jika kg, nilai di database (berat_gram) dibagi 1000. Jika pcs/lainnya, tampilkan apa adanya.
                            $kuantitasTampil = $isKg ? (($item->berat_gram ?? 0) / 1000) : ($item->berat_gram ?? 0);
                            $formattedKuantitas = $isKg
                                ? number_format($kuantitasTampil, 2, ',', '.')
                                : number_format($kuantitasTampil, 0, ',', '.');

                            $hargaSatuanTampil = $isKg ? (($item->harga_jual_per_gram ?? 0) * 1000) : ($item->harga_jual_per_gram ?? 0);
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal ?? $item->created_at)->format('d/m/Y') }}</td>
                            <td><strong>{{ $item->pembeli ?? 'Umum' }}</strong></td>
                            <td>{{ $item->kategori->nama_kategori ?? '-' }}</td>
                            <td>{{ $formattedKuantitas }} {{ $item->kategori->satuan ?? 'kg' }}</td>
                            <td>Rp {{ number_format($hargaSatuanTampil, 0, ',', '.') }}</td>
                            <td>
                                <strong class="saldo">
                                    Rp {{ number_format($item->total ?? 0, 0, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-table">
                                <div class="empty-icon">🗑️</div>
                                <strong>Belum Ada Transaksi Penjualan</strong>
                                <p>Silakan catat penjualan sampah baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="table-info">
                Total transaksi:
                <strong>{{ is_countable($barangKeluar) ? count($barangKeluar) : $barangKeluar->count() }}</strong>
            </div>
        </div>
    </section>

    <!-- TOAST -->
    <div class="toast-wrap" id="toastWrap"></div>

    <!-- MODAL TAMBAH PENJUALAN -->
    <div class="modal-overlay" id="modalTambahPenjualan">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Transaksi Penjualan ke Pengepul
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalTambahPenjualan')">&times;</button>
            </div>

            <form id="formTambahPenjualan" onsubmit="return simpanPenjualan(event)">
                <div class="modal-body">
                    <div class="form-grid">

                        <!-- NAMA PENGEPUL -->
                        <div class="form-group form-group--full">
                            <label for="tambahPembeli">Nama Pengepul</label>
                            <input type="text" id="tambahPembeli" placeholder="Masukkan nama pengepul / pembeli..."
                                required autocomplete="off">
                        </div>

                        <!-- PILIH KATEGORI SAMPAH -->
                        <div class="form-group form-group--full">
                            <label for="cariKategori">Kategori Sampah</label>
                            <div class="combo" id="comboKategori">
                                <input type="text" id="cariKategori" class="combo-input"
                                    placeholder="Ketik jenis / kategori sampah..." autocomplete="off">
                                <div class="combo-list" id="listKategori"></div>
                            </div>
                            <select id="tambahKategori" class="combo-hidden" tabindex="-1" aria-hidden="true" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($kategoriSampah as $k)
                                    @php
                                        $satuan = strtolower($k->satuan ?? 'kg');
                                        $stok = $k->stok_tersedia ?? 0;
                                        $formattedStok =
                                            $satuan === 'pcs' || fmod($stok, 1) == 0
                                                ? number_format($stok, 0, ',', '.')
                                                : number_format($stok, 2, ',', '.');
                                    @endphp
                                    <option value="{{ $k->id_kategori }}" data-stok="{{ $formattedStok }}"
                                        data-satuan="{{ $k->satuan ?? 'kg' }}">
                                        {{ $k->nama_kategori }} (Stok: {{ $formattedStok }} {{ $k->satuan ?? 'kg' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- HARGA SATUAN DAN KUANTITAS -->
                        <div class="form-group">
                            <label for="tambahHargaSatuan">Harga Jual per <span class="label-satuan">Satuan</span>
                                (Rp)</label>
                            <input type="number" id="tambahHargaSatuan" min="0" placeholder="Masukkan harga"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="tambahKuantitas">Kuantitas (<span class="label-satuan">Satuan</span>)</label>
                            <input type="number" id="tambahKuantitas" step="any" min="0.01"
                                placeholder="Masukkan jumlah" required>
                        </div>

                        <!-- TOTAL HARGA (KALKULASI) -->
                        <div class="form-group form-group--full">
                            <span class="detail-label">Total Penerimaan Penjualan</span>
                            <span class="detail-value saldo" id="totalHargaDisplay">Rp 0</span>
                        </div>

                        <!-- TANGGAL TRANSAKSI -->
                        <div class="form-group form-group--full">
                            <label for="tambahTanggal">Tanggal Transaksi</label>
                            <input type="date" id="tambahTanggal" required>
                        </div>

                    </div>
                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button"
                        onclick="tutupModal('modalTambahPenjualan')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- STYLESHEET -->
    <style>
        :root {
            --penjualan-page: #f5f7fb;
            --penjualan-surface: #ffffff;
            --penjualan-surface-secondary: #f8fafc;
            --penjualan-text: #1f2937;
            --penjualan-text-secondary: #6b7280;
            --penjualan-text-muted: #9ca3af;
            --penjualan-border: #e5e7eb;
            --penjualan-table-head: #f8fafc;
            --penjualan-table-hover: #fafafa;
            --penjualan-input: #ffffff;
            --penjualan-shadow: 0 8px 25px rgba(15, 23, 42, .06);
        }

        [data-theme="dark"] {
            --penjualan-page: #0b1220;
            --penjualan-surface: #151d2f;
            --penjualan-surface-secondary: #1b2438;
            --penjualan-text: #f1f5f9;
            --penjualan-text-secondary: #aab6c8;
            --penjualan-text-muted: #748198;
            --penjualan-border: #29364d;
            --penjualan-table-head: #111a2c;
            --penjualan-table-hover: #202b40;
            --penjualan-input: #111a2c;
            --penjualan-shadow: 0 8px 25px rgba(0, 0, 0, .25);
        }

        .card {
            background: var(--penjualan-surface);
            border: 1px solid var(--penjualan-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--penjualan-shadow);
        }

        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 16px 24px;
        }

        .table-search {
            width: 400px;
            max-width: 100%;
            position: relative;
            color: var(--penjualan-text-muted);
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
            pointer-events: none;
        }

        .table-search input {
            width: 100%;
            height: 40px;
            padding: 0 14px 0 40px;
            border: 1px solid var(--penjualan-border);
            border-radius: 8px;
            outline: none;
            box-sizing: border-box;
            background: var(--penjualan-input);
            color: var(--penjualan-text);
            font-family: inherit;
            font-size: 13px;
            transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
        }

        .table-search input::placeholder {
            color: var(--penjualan-text-muted);
        }

        .table-search input:focus {
            border-color: var(--primary, #16a34a);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        .data-table th {
            background: var(--penjualan-table-head);
            color: var(--penjualan-text-secondary);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 13px 16px;
            text-align: left;
            border-top: 1px solid var(--penjualan-border);
            border-bottom: 1px solid var(--penjualan-border);
            white-space: nowrap;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid var(--penjualan-border);
            font-size: 13px;
            color: var(--penjualan-text);
            vertical-align: middle;
            background: var(--penjualan-surface);
            transition: background .15s ease, color .15s ease;
        }

        .data-table tbody tr:hover td {
            background: var(--penjualan-table-hover);
        }

        .saldo {
            color: #22c55e !important;
            white-space: nowrap;
            font-weight: 700;
        }

        .empty-table {
            text-align: center;
            padding: 50px 40px !important;
            background: var(--penjualan-surface) !important;
        }

        .empty-icon {
            font-size: 30px;
            margin-bottom: 10px;
            opacity: .8;
        }

        .empty-table strong {
            display: block;
            color: var(--penjualan-text);
            font-size: 14px;
        }

        .empty-table p {
            margin: 5px 0 0;
            color: var(--penjualan-text-secondary);
            font-size: 13px;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, .68);
            backdrop-filter: blur(3px);
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
            background: var(--penjualan-surface);
            border: 1px solid var(--penjualan-border);
            border-radius: 14px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, .30);
            color: var(--penjualan-text);
        }

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid var(--penjualan-border);
            background: var(--penjualan-surface);
        }

        .modal-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--penjualan-text);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-title svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: #22c55e;
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
            color: var(--penjualan-text-muted);
            cursor: pointer;
        }

        .modal-body {
            padding: 22px 24px;
            background: var(--penjualan-surface);
        }

        .modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid var(--penjualan-border);
            background: var(--penjualan-surface);
        }

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
            color: var(--penjualan-text-secondary);
        }

        .form-group input,
        .form-group select {
            border: 1px solid var(--penjualan-border);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            width: 100%;
            background: var(--penjualan-input);
            color: var(--penjualan-text);
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
        }

        .detail-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--penjualan-text-muted);
            display: block;
        }

        .detail-value {
            font-size: 16px;
            color: var(--penjualan-text);
        }

        /* COMBOBOX STYLES */
        .combo {
            position: relative;
        }

        .combo-hidden {
            display: none !important;
        }

        .combo-input {
            width: 100%;
            border: 1px solid var(--penjualan-border);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            background: var(--penjualan-input);
            color: var(--penjualan-text);
        }

        .combo-input:focus {
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .10);
        }

        .combo-list {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            max-height: 200px;
            overflow-y: auto;
            background: var(--penjualan-surface);
            border: 1px solid var(--penjualan-border);
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .22);
            z-index: 20;
        }

        .combo-list.show {
            display: block;
        }

        .combo-item {
            padding: 9px 12px;
            font-size: 13px;
            color: var(--penjualan-text);
            cursor: pointer;
        }

        .combo-item:hover,
        .combo-item.is-active {
            background: var(--penjualan-table-hover);
            color: #22c55e;
        }

        .combo-empty {
            padding: 12px;
            font-size: 12.5px;
            color: var(--penjualan-text-muted);
            text-align: center;
        }

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
            background: var(--penjualan-surface);
            border: 1px solid var(--penjualan-border);
            border-left: 4px solid #22c55e;
            border-radius: 10px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .25);
            padding: 14px 16px;
            pointer-events: auto;
        }

        .toast.toast--error {
            border-left-color: #ef4444;
        }

        .toast-icon {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: rgba(34, 197, 94, .13);
            color: #22c55e;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toast.toast--error .toast-icon {
            background: rgba(239, 68, 68, .13);
            color: #ef4444;
        }

        .toast-body {
            flex: 1;
        }

        .toast-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--penjualan-text);
            margin: 0 0 2px;
        }

        .toast-text {
            font-size: 12.5px;
            color: var(--penjualan-text-secondary);
            margin: 0;
        }

        .toast-close {
            border: none;
            background: transparent;
            color: var(--penjualan-text-muted);
            font-size: 16px;
            cursor: pointer;
        }

        @media (max-width: 560px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group--full {
                grid-column: auto;
            }
        }
    </style>

    <!-- JAVASCRIPT SYSTEM -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            /* TANGGAL DEFAULT HARI INI */
            const today = new Date().toISOString().split('T')[0];
            const inputTanggal = document.getElementById('tambahTanggal');
            if (inputTanggal) inputTanggal.value = today;

            /* FILTER TABEL PENJUALAN */
            const searchInput = document.getElementById('searchPenjualan');
            const table = document.getElementById('penjualanTable');
            if (searchInput && table) {
                searchInput.addEventListener('keyup', function() {
                    const keyword = this.value.toLowerCase().trim();
                    table.querySelectorAll('tbody tr').forEach(function(row) {
                        row.style.display = row.textContent.toLowerCase().includes(keyword) ? '' :
                            'none';
                    });
                });
            }

            /* INISIALISASI COMBOBOX KATEGORI */
            initGenericCombo('comboKategori', 'tambahKategori', 'cariKategori', 'listKategori', onSelectKategori);

            /* CALCULATE TOTAL SAAT KUANTITAS ATAU HARGA DIUBAH */
            const elKuantitas = document.getElementById('tambahKuantitas');
            const elHarga = document.getElementById('tambahHargaSatuan');

            if (elKuantitas) elKuantitas.addEventListener('input', hitungTotal);
            if (elHarga) elHarga.addEventListener('input', hitungTotal);
        });

        /* REUSABLE SEARCHABLE COMBOBOX */
        function initGenericCombo(wrapId, selectId, inputId, listId, onSelectCallback) {
            const select = document.getElementById(selectId);
            const input = document.getElementById(inputId);
            const list = document.getElementById(listId);

            if (!select || !input || !list) return;

            const data = Array.from(select.options)
                .filter(o => o.value !== '')
                .map(o => ({
                    value: o.value,
                    label: o.textContent.replace(/\s+/g, ' ').trim(),
                    stok: o.dataset.stok || 0,
                    satuan: o.dataset.satuan || 'kg',
                    harga: o.dataset.harga || ''
                }));

            let aktif = -1;

            function escapeHtml(s) {
                return String(s).replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));
            }

            function render(keyword) {
                const k = (keyword || '').toLowerCase().trim();
                const hasil = data.filter(d => d.label.toLowerCase().includes(k));
                aktif = -1;

                list.innerHTML = hasil.length ?
                    hasil.map(d => `<div class="combo-item" data-value="${d.value}">${escapeHtml(d.label)}</div>`).join(
                        '') :
                    `<div class="combo-empty">Data tidak ditemukan</div>`;
            }

            function pilih(value) {
                const d = data.find(x => x.value === String(value));
                if (!d) return;

                select.value = d.value;
                input.value = d.label;
                list.classList.remove('show');

                if (typeof onSelectCallback === 'function') {
                    onSelectCallback(d);
                }
            }

            function sorot(arah) {
                const items = list.querySelectorAll('.combo-item');
                if (!items.length) return;
                aktif = (aktif + arah + items.length) % items.length;
                items.forEach((el, i) => el.classList.toggle('is-active', i === aktif));
                items[aktif].scrollIntoView({
                    block: 'nearest'
                });
            }

            input.addEventListener('focus', function() {
                render(select.value ? '' : this.value);
                list.classList.add('show');
            });

            input.addEventListener('input', function() {
                select.value = '';
                render(this.value);
                list.classList.add('show');
                if (typeof onSelectCallback === 'function') {
                    onSelectCallback(null);
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!list.classList.contains('show')) {
                        render(this.value);
                        list.classList.add('show');
                    }
                    sorot(1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    sorot(-1);
                } else if (e.key === 'Enter') {
                    const items = list.querySelectorAll('.combo-item');
                    if (list.classList.contains('show') && aktif > -1 && items[aktif]) {
                        e.preventDefault();
                        pilih(items[aktif].dataset.value);
                    }
                } else if (e.key === 'Escape') {
                    list.classList.remove('show');
                }
            });

            list.addEventListener('mousedown', function(e) {
                const item = e.target.closest('.combo-item');
                if (!item) return;
                e.preventDefault();
                pilih(item.dataset.value);
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('#' + wrapId)) list.classList.remove('show');
            });
        }

        /* CALLBACK KETIKA KATEGORI SAMPAH DIPILIH */
        function onSelectKategori(data) {
            const inputHarga = document.getElementById('tambahHargaSatuan');
            const labelSatuans = document.querySelectorAll('.label-satuan');

            if (data) {
                labelSatuans.forEach(el => el.textContent = data.satuan);
                if (inputHarga) inputHarga.focus();
            } else {
                labelSatuans.forEach(el => el.textContent = 'Satuan');
            }
            hitungTotal();
        }

        /* KALKULASI HARGA x KUANTITAS */
        function hitungTotal() {
            const harga = parseFloat(document.getElementById('tambahHargaSatuan')?.value) || 0;
            const kuantitas = parseFloat(document.getElementById('tambahKuantitas')?.value) || 0;
            const total = harga * kuantitas;

            const display = document.getElementById('totalHargaDisplay');
            if (display) {
                display.textContent = formatRupiah(total);
            }
        }

        /* MODAL CONTROL & UTILS */
        function bukaModal(id) {
            if (id === 'modalTambahPenjualan') {
                const form = document.getElementById('formTambahPenjualan');
                if (form) form.reset();

                const setVal = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.value = val;
                };

                setVal('tambahPembeli', '');
                setVal('tambahKategori', '');
                setVal('cariKategori', '');
                setVal('tambahHargaSatuan', '');
                setVal('tambahKuantitas', '');

                document.querySelectorAll('.label-satuan').forEach(el => el.textContent = 'Satuan');

                const totalDisplay = document.getElementById('totalHargaDisplay');
                if (totalDisplay) totalDisplay.textContent = 'Rp 0';

                const today = new Date().toISOString().split('T')[0];
                setVal('tambahTanggal', today);
            }
            const modal = document.getElementById(id);
            if (modal) modal.classList.add('active');
        }

        function tutupModal(id) {
            const modal = document.getElementById(id);
            if (modal) modal.classList.remove('active');
        }

        function formatRupiah(angka) {
            return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
        }

        function showToast(title, text, type = 'success') {
            const wrap = document.getElementById('toastWrap');
            if (!wrap) return;

            const toast = document.createElement('div');
            toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');

            const iconPath = type === 'error' ?
                '<path d="M18 6 6 18M6 6l12 12"></path>' :
                '<path d="M20 6 9 17l-5-5"></path>';

            toast.innerHTML = `
            <div class="toast-icon"><svg viewBox="0 0 24 24">${iconPath}</svg></div>
            <div class="toast-body">
                <p class="toast-title">${title}</p>
                <p class="toast-text">${text}</p>
            </div>
            <button class="toast-close" type="button" onclick="this.parentElement.remove()">&times;</button>
        `;

            wrap.appendChild(toast);
            setTimeout(() => toast.remove(), 3500);
        }

        /* SIMPAN TRANSAKSI VIA AJAX */
        function simpanPenjualan(event) {
            event.preventDefault();

            const namaPembeli = document.getElementById('tambahPembeli')?.value.trim();
            const idKategori = document.getElementById('tambahKategori')?.value;
            const kuantitas = document.getElementById('tambahKuantitas')?.value;
            const hargaSatuan = document.getElementById('tambahHargaSatuan')?.value;
            const tanggal = document.getElementById('tambahTanggal')?.value;

            if (!namaPembeli) {
                showToast('Gagal menyimpan', 'Silakan masukkan nama pengepul terlebih dahulu.', 'error');
                document.getElementById('tambahPembeli')?.focus();
                return false;
            }

            if (!idKategori) {
                showToast('Gagal menyimpan', 'Silakan pilih kategori sampah.', 'error');
                document.getElementById('cariKategori')?.focus();
                return false;
            }

            if (!hargaSatuan || parseFloat(hargaSatuan) <= 0) {
                showToast('Gagal menyimpan', 'Silakan masukkan harga jual yang valid.', 'error');
                document.getElementById('tambahHargaSatuan')?.focus();
                return false;
            }

            const payload = {
                pembeli: namaPembeli,
                id_kategori: idKategori,
                kuantitas: kuantitas,
                harga_satuan: hargaSatuan,
                tanggal_transaksi: tanggal
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            fetch(`{{ route('admin.penjualan-pengepul.store') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Gagal menyimpan transaksi.');
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalTambahPenjualan');
                    showToast('Berhasil', 'Transaksi penjualan ke pengepul berhasil ditambahkan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => showToast('Gagal menyimpan', err.message, 'error'));

            return false;
        }
    </script>
@endsection
