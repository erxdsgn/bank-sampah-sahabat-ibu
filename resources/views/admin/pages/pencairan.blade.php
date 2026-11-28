@extends('admin.layouts.app')

@section('title', 'Pencairan Saldo')
@section('active', 'pencairan')
@section('crumbs', 'Transaksi Sampah | Pencairan Saldo')

@push('styles')
    @vite('resources/css/pages/pencairan.css')
@endpush

@section('content')
<div class="pencairan-page">

    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">TRANSAKSI SAMPAH</span>
            <h1 class="hero-title">Pencairan <span class="accent">Saldo</span></h1>
            <p class="hero-sub">Kelola permohonan penarikan saldo tabungan warga.</p>
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

    <section class="card pencairan-card">
        {{-- SEARCH --}}
        <div class="table-toolbar">
            <div class="table-search">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchPencairan" placeholder="Cari nama, NIK, nomor HP, atau metode..."
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

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="data-table" id="pencairanTable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Warga</th>
                        <th>Kontak</th>
                        <th>Metode</th>
                        <th>Rekening</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pencairan as $item)
                        @php
                            $statusNormalized = strtolower(trim((string) ($item->status ?? '')));
                            $metodeNormalized = strtolower(trim((string) ($item->metode_transfer ?? '')));
                        @endphp
                        <tr data-row-pencairan>
                            <td class="nowrap">
                                {{ $item->tanggal_pencairan ? \Carbon\Carbon::parse($item->tanggal_pencairan)->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                <strong>{{ $item->warga->nama ?? 'Anonim' }}</strong>
                                <div class="mono">{{ $item->warga->nik ?? '-' }}</div>
                            </td>
                            <td class="nowrap">{{ $item->warga->no_hp ?? '-' }}</td>
                            <td>
                                @if ($metodeNormalized === 'tunai')
                                    <span class="metode-badge metode-badge--tunai">Tunai</span>
                                @elseif ($metodeNormalized === 'transfer')
                                    <span class="metode-badge metode-badge--transfer">Transfer</span>
                                @else
                                    <span class="muted-text">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($metodeNormalized !== 'tunai' && $item->warga && $item->warga->nama_bank_ewallet)
                                    <div class="bank-info">
                                        <div class="bank-main">
                                            <strong title="{{ $item->warga->nama_bank_ewallet }}">
                                                {{ $item->warga->nama_bank_ewallet }}
                                            </strong>
                                        </div>
                                        <div class="mono rekening-number" title="{{ $item->warga->nomor_rekening ?? '-' }}">
                                            {{ $item->warga->nomor_rekening ?? '-' }}
                                        </div>
                                    </div>
                                @else
                                    <span class="muted-text">-</span>
                                @endif
                            </td>
                            <td>
                                <strong class="saldo">
                                    Rp {{ number_format($item->jumlah ?? 0, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>
                                @if ($statusNormalized === 'menunggu')
                                    <span class="badge badge--warning">Menunggu</span>
                                @elseif($statusNormalized === 'selesai')
                                    <span class="badge badge--success">Selesai</span>
                                @else
                                    <span class="badge badge--danger">Ditolak</span>
                                @endif
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="icon-btn" title="Lihat Invoice Ringkas" type="button"
                                        onclick='bukaInvoiceCard(@json($item))'>
                                        <svg viewBox="0 0 24 24">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>

                                    @if ($statusNormalized === 'menunggu')
                                        <button class="icon-btn icon-btn--edit" title="Ubah Status Pencairan" type="button"
                                            onclick='prosesPencairan(@json($item))'>
                                            <svg viewBox="0 0 24 24">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-table">
                                <div class="empty-icon">📋</div>
                                <strong>Belum Ada Permohonan Pencairan</strong>
                                <p>Belum ada warga yang mengajukan pencairan saldo.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="empty-table" id="pencairanKosongCari" style="display: none;">
                <div class="empty-icon">🔍</div>
                <strong>Data Tidak Ditemukan</strong>
                <p>Tidak ada permohonan yang cocok dengan pencarian ini.</p>
            </div>
        </div>

        <div class="table-footer">
            <div class="table-info" id="tableInfoPagination">Menampilkan data...</div>

            <div class="table-pagination-controls">
                <div class="per-page">
                    <label for="cari_perPageSelect">Permohonan per halaman:</label>
                    <div class="combo combo--up combo--arrow" id="combo_perPageSelect">
                        <input type="text" id="cari_perPageSelect" class="combo-input" placeholder="" autocomplete="off">
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

    {{-- TOAST --}}
    <div class="toast-wrap" id="pencairanToastWrap"></div>

    {{-- MODAL INVOICE CARD --}}
    <div class="modal-overlay" id="modalInvoice">
        <div class="modal-box invoice-modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    Nota Pencairan Saldo
                </h3>
                <button class="modal-close" type="button" onclick="tutupModal('modalInvoice')">&times;</button>
            </div>

            <div class="modal-body">
                <div class="invoice-paper">
                    <div class="inv-top">
                        <div>
                            <span class="inv-company-name">Bank Sampah Sahabat Ibu</span>
                            <span class="inv-company-addr">Tegal Besar, Kaliwates, Jember</span>
                        </div>
                        <div class="inv-title-group">
                            <span class="mono inv-number" id="invId">#INV-000</span>
                            <span class="mono inv-text-muted" id="invTanggal">-</span>
                        </div>
                    </div>

                    <div class="inv-divider"></div>

                    <div class="inv-grid-compact">
                        <div>
                            <span class="inv-label">Warga Penerima</span>
                            <strong class="inv-text-dark inv-block" id="invNama">-</strong>
                            <span class="mono inv-text-muted inv-block inv-nik" id="invNik">-</span>
                        </div>
                        <div class="inv-align-right">
                            <span class="inv-label">Status Transaksi</span>
                            <div id="invBadgeStatus" class="inv-status-wrap">-</div>
                        </div>
                    </div>

                    <div class="inv-compact-box">
                        <div class="inv-compact-col">
                            <span class="inv-label">Tujuan Pencairan</span>
                            <div class="inv-text-dark" id="invBankInfo">-</div>
                            <div class="mono inv-text-muted" id="invRekening">-</div>
                        </div>
                        <div class="inv-compact-col inv-compact-right">
                            <span class="inv-label">Jumlah Dicairkan</span>
                            <div class="inv-total-amount" id="invNominal">Rp 0</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-foot">
                <button class="btn btn--whatsapp" id="btnKirimWa" type="button" onclick="kirimInvoiceWhatsApp()">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path
                            d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z">
                        </path>
                    </svg>
                    Kirim WA
                </button>
                <button class="btn btn--primary" type="button" onclick="tutupModal('modalInvoice')">Tutup</button>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH PENCAIRAN --}}
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
                                    <option value="{{ $w->id_warga }}" data-saldo="{{ $w->saldo ?? 0 }}"
                                        data-bank="{{ $w->nama_bank_ewallet ?? '' }}"
                                        data-rekening="{{ $w->nomor_rekening ?? '' }}"
                                        data-status-rekening="{{ $w->status_rekening ?? '' }}">
                                        {{ $w->nama }} (NIK: {{ $w->nik }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <span class="detail-label">Saldo Tersedia</span>
                            <span class="detail-value saldo" id="tambahSaldoInfo">-</span>
                        </div>

                        <div class="form-group">
                            <span class="detail-label">Rekening / E-Wallet</span>
                            <span class="detail-value" id="tambahRekeningInfo">-</span>
                        </div>

                        <div class="form-group">
                            <label for="tambahJumlah">Jumlah Penarikan (Rp)</label>
                            <input type="number" id="tambahJumlah" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="cari_tambahMetode">Metode Pencairan</label>
                            <div class="combo combo--arrow" id="combo_tambahMetode">
                                <input type="text" id="cari_tambahMetode" class="combo-input" autocomplete="off">
                                <div class="combo-list" id="list_tambahMetode"></div>
                            </div>
                            <select id="tambahMetode" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="">-- Pilih Metode --</option>
                                <option value="tunai">Tunai</option>
                                <option value="transfer">Transfer Bank</option>
                            </select>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="tambahTanggal">Tanggal Pencairan</label>
                            <input type="date" id="tambahTanggal" readonly required>
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

    {{-- MODAL PROSES PENCAIRAN --}}
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

                        <div class="form-group">
                            <label>Bank / E-Wallet Tujuan</label>
                            <input type="text" id="editBank" disabled>
                        </div>

                        <div class="form-group">
                            <label>No. Rekening / No. HP</label>
                            <input type="text" id="editNoRekening" disabled>
                        </div>

                        <div class="form-group form-group--full">
                            <label>Status Rekening</label>
                            <input type="text" id="editStatusRekening" disabled>
                        </div>

                        <div class="form-group form-group--full">
                            <label for="cari_editStatus">Status Pencairan</label>
                            <div class="combo combo--arrow" id="combo_editStatus">
                                <input type="text" id="cari_editStatus" class="combo-input" autocomplete="off">
                                <div class="combo-list" id="list_editStatus"></div>
                            </div>
                            <select id="editStatus" class="combo-hidden" tabindex="-1" aria-hidden="true">
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

</div>
@endsection

@push('scripts')
    <script>
        window.__PENCAIRAN_DATA__ = {
            storeUrl: "{{ route('admin.pencairan.store') }}",
            baseUrl: "{{ route('admin.pencairan.index') }}",
            flashSuccess: @json(session('success'))
        };
    </script>
    @vite('resources/js/pages/pencairan.js')
@endpush
