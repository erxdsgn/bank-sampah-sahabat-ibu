@extends('admin.layouts.app')

@section('title', 'Pencairan Saldo')
@section('active', 'pencairan')
@section('crumbs', 'Transaksi Sampah | Pencairan Saldo')

@section('content')

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">TRANSAKSI SAMPAH</span>

            <h1 class="hero-title">
                Pencairan <span class="accent">Saldo</span>
            </h1>

            <p class="hero-sub">
                Kelola permohonan penarikan saldo tabungan warga.
            </p>
        </div>

        <div class="hero-actions">
            <button class="btn btn--primary" type="button" onclick="bukaModal('modalTambah')">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"></path>
                </svg>
                Tambah Pencairan
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
                <input type="text" id="searchPencairan" placeholder="Cari NIK, nama, nomor HP, atau alamat..."
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


        <!-- TABLE -->
        <div class="table-responsive">
            <table class="data-table" id="pencairanTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>No. HP</th>
                        <th>Alamat</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($pencairan as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><span class="mono">{{ $item->warga->nik ?? '-' }}</span></td>
                            <td><strong>{{ $item->warga->nama ?? 'Anonim' }}</strong></td>
                            <td>{{ $item->warga->no_hp ?? '-' }}</td>
                            <td>{{ $item->warga->alamat ?? '-' }}</td>
                            <td>
                                <strong class="saldo">
                                    Rp {{ number_format($item->jumlah ?? 0, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>
                                @if ($item->status == 'menunggu')
                                    <span class="badge badge--warning">Menunggu</span>
                                @elseif($item->status == 'selesai')
                                    <span class="badge badge--success">Selesai</span>
                                @else
                                    <span class="badge badge--danger">Ditolak</span>
                                @endif
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="icon-btn" title="Proses Pencairan" type="button"
                                        onclick='prosesPencairan(@json($item))'
                                        {{ $item->status !== 'menunggu' ? 'disabled' : '' }}>
                                        <svg viewBox="0 0 24 24">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px;">
                                <div style="font-size: 30px; margin-bottom: 10px;">📋</div>
                                <strong>Belum Ada Permohonan Pencairan</strong>
                                <p style="margin: 5px 0 0; color: #6b7280;">
                                    Belum ada warga yang mengajukan pencairan saldo.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        <!-- FOOTER -->
        <div class="table-footer">
            <div class="table-info">
                Total permohonan: <strong>{{ $pencairan->count() }}</strong>
            </div>
        </div>

    </section>


    <!-- TOAST -->
    <div class="toast-wrap" id="toastWrap"></div>


    <!-- MODAL: TAMBAH PENCAIRAN -->
    <div class="modal-overlay" id="modalTambah">
        <div class="modal-box">

            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Permohonan Pencairan
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalTambah')">&times;</button>
            </div>

            <form id="formTambahPencairan" onsubmit="return simpanTambah(event)">
                <div class="modal-body">

                    <div class="form-grid">

                        <div class="form-group form-group--full">
                            <label for="cariWarga">Nama Warga</label>

                            <div class="combo" id="comboWarga">
                                <input type="text" id="cariWarga" class="combo-input"
                                    placeholder="Ketik nama atau NIK warga..." autocomplete="off">
                                <div class="combo-list" id="listWarga"></div>
                            </div>

                            <select id="tambahWarga" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="">-- Pilih Warga --</option>
                                @foreach ($warga as $w)
                                    <option value="{{ $w->id_warga }}" data-saldo="{{ $w->saldo ?? 0 }}">
                                        {{ $w->nama }} (NIK: {{ $w->nik }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group form-group--full">
                            <span class="detail-label">Saldo Tersedia</span>
                            <span class="detail-value saldo" id="tambahSaldoInfo">-</span>
                        </div>

                        <div class="form-group">
                            <label for="tambahJumlah">Jumlah Penarikan (Rp)</label>
                            <input type="number" id="tambahJumlah" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="tambahMetode">Metode Transfer</label>
                            <input type="text" id="tambahMetode" placeholder="Tunai / Transfer Bank" required>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="tambahTanggal">Tanggal Pencairan</label>
                            <input type="date" id="tambahTanggal" required>
                        </div>

                    </div>

                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalTambah')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan Permohonan</button>
                </div>
            </form>

        </div>
    </div>


    <!-- MODAL: PROSES PENCAIRAN -->
    <div class="modal-overlay" id="modalProses">
        <div class="modal-box">

            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                    </svg>
                    Proses Pencairan Saldo
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalProses')">&times;</button>
            </div>

            <form id="formProses" onsubmit="return simpanProses(event)">
                <div class="modal-body">

                    <input type="hidden" id="editId">

                    <div class="form-grid">

                        <div class="form-group form-group--full">
                            <label>Nama Warga</label>
                            <input type="text" id="editNama" disabled>
                        </div>

                        <div class="form-group form-group--full">
                            <label>Nominal Penarikan</label>
                            <input type="text" id="editNominal" disabled>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="editStatus">Status Pencairan</label>
                            <select id="editStatus">
                                <option value="menunggu">Menunggu</option>
                                <option value="selesai">Selesai (Uang Diserahkan)</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>

                    </div>

                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalProses')">Batal</button>
                    <button class="btn btn--primary" type="submit">Simpan Perubahan</button>
                </div>
            </form>

        </div>
    </div>


    <style>
        .card {
            background: var(--surface, #ffffff);
            border: 1px solid var(--border, #e5e7eb);
            border-radius: 14px;
            overflow: hidden;
        }

        .table-toolbar {
            display: flex;
            justify-content: space-between;
            padding: 16px 24px;
        }

        .table-search {
            width: 400px;
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

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
        }

        .data-table th {
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 13px 16px;
            text-align: left;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid #edf0f2;
            font-size: 13px;
            color: #374151;
            vertical-align: middle;
        }

        .data-table tbody tr:hover {
            background: #fafafa;
        }

        .mono {
            font-family: monospace;
            font-size: 12px;
        }

        .saldo {
            white-space: nowrap;
        }

        .table-actions {
            display: flex;
            gap: 6px;
        }

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

        .icon-btn:disabled {
            opacity: .4;
            cursor: not-allowed;
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

        /* MODAL */
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

        .modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid #edf0f2;
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
            color: #374151;
        }

        .form-group input,
        .form-group select {
            border: 1px solid #dfe3e8;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
            width: 100%;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary, #4338ca);
        }

        .form-group input:disabled {
            background: #f3f4f6;
            color: #6b7280;
        }

        .detail-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #9ca3af;
            display: block;
        }

        .detail-value {
            font-size: 14px;
            color: #1f2937;
        }

        /* COMBOBOX WARGA (SEARCHABLE SELECT) */
        .combo {
            position: relative;
        }

        .combo-hidden {
            display: none !important;
        }

        .combo-input {
            width: 100%;
            border: 1px solid #dfe3e8;
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            box-sizing: border-box;
        }

        .combo-input:focus {
            border-color: var(--primary, #4338ca);
        }

        .combo-list {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            max-height: 220px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, .12);
            z-index: 20;
        }

        .combo-list.show {
            display: block;
        }

        .combo-item {
            padding: 9px 12px;
            font-size: 13px;
            color: #374151;
            cursor: pointer;
        }

        .combo-item:hover,
        .combo-item.is-active {
            background: #f3f4f6;
        }

        .combo-empty {
            padding: 12px;
            font-size: 12.5px;
            color: #9ca3af;
            text-align: center;
        }

        /* TOAST */
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

        @media (max-width: 560px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            @if (session('success'))
                showToast('Berhasil', @json(session('success')));
            @endif

            const searchInput = document.getElementById('searchPencairan');
            const table = document.getElementById('pencairanTable');

            if (searchInput && table) {
                searchInput.addEventListener('keyup', function() {
                    const keyword = this.value.toLowerCase().trim();
                    table.querySelectorAll('tbody tr').forEach(function(row) {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(keyword) ? '' : 'none';
                    });
                });
            }

            document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) overlay.classList.remove('active');
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay.active').forEach(function(overlay) {
                        overlay.classList.remove('active');
                    });
                }
            });

            initComboWarga();

        });


        /*
        |--------------------------------------------------------------------------
        | Combobox warga (select + pencarian nama / NIK)
        |--------------------------------------------------------------------------
        */
        function initComboWarga() {
            const select = document.getElementById('tambahWarga');
            const input = document.getElementById('cariWarga');
            const list = document.getElementById('listWarga');
            const info = document.getElementById('tambahSaldoInfo');
            if (!select || !input || !list) return;

            const data = Array.from(select.options)
                .filter(function(o) {
                    return o.value !== '';
                })
                .map(function(o) {
                    return {
                        value: o.value,
                        label: o.textContent.replace(/\s+/g, ' ').trim(),
                        saldo: Number(o.dataset.saldo || 0)
                    };
                });

            let aktif = -1;

            function escapeHtml(s) {
                return String(s).replace(/[&<>"']/g, function(c) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    } [c];
                });
            }

            function render(keyword) {
                const k = (keyword || '').toLowerCase().trim();
                const hasil = data.filter(function(d) {
                    return d.label.toLowerCase().includes(k);
                });

                aktif = -1;

                list.innerHTML = hasil.length ?
                    hasil.map(function(d) {
                        return '<div class="combo-item" data-value="' + d.value + '">' +
                            escapeHtml(d.label) + '</div>';
                    }).join('') :
                    '<div class="combo-empty">Warga tidak ditemukan</div>';
            }

            function pilih(value) {
                const d = data.find(function(x) {
                    return x.value === String(value);
                });
                if (!d) return;

                select.value = d.value;
                input.value = d.label;
                info.textContent = formatRupiah(d.saldo);
                list.classList.remove('show');
            }

            function sorot(arah) {
                const items = list.querySelectorAll('.combo-item');
                if (!items.length) return;

                aktif = (aktif + arah + items.length) % items.length;
                items.forEach(function(el, i) {
                    el.classList.toggle('is-active', i === aktif);
                });
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
                info.textContent = '-';
                render(this.value);
                list.classList.add('show');
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
                if (!e.target.closest('#comboWarga')) list.classList.remove('show');
            });
        }

        function resetComboWarga() {
            const select = document.getElementById('tambahWarga');
            const input = document.getElementById('cariWarga');
            const list = document.getElementById('listWarga');
            const info = document.getElementById('tambahSaldoInfo');

            if (select) select.value = '';
            if (input) input.value = '';
            if (list) list.classList.remove('show');
            if (info) info.textContent = '-';
        }

        function bukaModal(id) {
            if (id === 'modalTambah') {
                const form = document.getElementById('formTambahPencairan');
                if (form) form.reset();
                resetComboWarga();
            }
            document.getElementById(id).classList.add('active');
        }

        function tutupModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function formatRupiah(angka) {
            return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
        }

        function showToast(title, text, type = 'success') {
            const wrap = document.getElementById('toastWrap');
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
                <button class="toast-close" type="button" aria-label="Tutup">&times;</button>
            `;

            function hapusToast() {
                toast.remove();
            }

            toast.querySelector('.toast-close').addEventListener('click', hapusToast);
            wrap.appendChild(toast);
            setTimeout(hapusToast, 3500);
        }

        /*
        |--------------------------------------------------------------------------
        | Tambah permohonan pencairan
        |--------------------------------------------------------------------------
        */
        function simpanTambah(event) {
            event.preventDefault();

            const idWarga = document.getElementById('tambahWarga').value;

            if (!idWarga) {
                showToast('Gagal menyimpan', 'Silakan pilih warga terlebih dahulu.', 'error');
                document.getElementById('cariWarga').focus();
                return false;
            }

            const payload = {
                id_warga: idWarga,
                jumlah: document.getElementById('tambahJumlah').value,
                metode_transfer: document.getElementById('tambahMetode').value,
                tanggal_pencairan: document.getElementById('tambahTanggal').value,
            };

            fetch(`{{ route('admin.pencairan.store') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Gagal menyimpan permohonan.');
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalTambah');
                    showToast('Berhasil', 'Permohonan pencairan telah dibuat.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menyimpan', err.message, 'error');
                });

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Proses pencairan
        |--------------------------------------------------------------------------
        */
        function prosesPencairan(item) {
            document.getElementById('editId').value = item.id_pencairan;
            document.getElementById('editNama').value =
                (item.warga?.nama || 'Anonim') + ' (NIK: ' + (item.warga?.nik || '-') + ')';
            document.getElementById('editNominal').value = formatRupiah(item.jumlah);
            document.getElementById('editStatus').value = item.status;

            bukaModal('modalProses');
        }

        function simpanProses(event) {
            event.preventDefault();

            const id = document.getElementById('editId').value;
            const status = document.getElementById('editStatus').value;

            const baseUrl = "{{ route('admin.pencairan.index') }}"; // -> /admin/pages/pencairan

            fetch(`${baseUrl}/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status
                    })
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Gagal memproses pencairan.');
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalProses');
                    showToast('Berhasil disimpan', 'Status pencairan berhasil diperbarui menjadi: ' + status);
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menyimpan', err.message, 'error');
                });

            return false;
        }
    </script>

@endsection
