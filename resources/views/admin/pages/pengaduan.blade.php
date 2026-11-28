@extends('admin.layouts.app')

@section('title', 'Pengaduan Warga')
@section('active', 'pengaduan')
@section('crumbs', 'Laporan & Akun | Pengaduan Warga')

@push('styles')
    @vite('resources/css/pages/pengaduan.css')
@endpush

@section('content')
    <div class="pengaduan-page">
        @if (session('success'))
            <div class="alert success">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                </span>
                <div class="body">
                    <div class="title">Berhasil</div>
                    <div>{{ session('success') }}</div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert danger">
                <span class="ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4m0 4h.01" />
                    </svg>
                </span>
                <div class="body">
                    <div class="title">Periksa Input</div>
                    <div>{{ $errors->first() }}</div>
                </div>
            </div>
        @endif

        <section class="hero">
            <div class="hero-text">
                <span class="eyebrow">LAYANAN &amp; MASUKAN</span>
                <h1 class="hero-title">Pengaduan <span class="accent">Warga</span></h1>
                <p class="hero-sub">Pantau laporan warga, cek foto bukti, dan beri tanggapan langsung.</p>
            </div>

            <div class="hero-stats">
                <div class="stat-chip">
                    <span class="stat-dot stat-dot--amber" aria-hidden="true"></span>
                    <div>
                        <div class="stat-num">{{ $counts['menunggu'] ?? 0 }}</div>
                        <div class="stat-label">Menunggu</div>
                    </div>
                </div>
                <div class="stat-chip">
                    <span class="stat-dot stat-dot--blue" aria-hidden="true"></span>
                    <div>
                        <div class="stat-num">{{ $counts['diproses'] ?? 0 }}</div>
                        <div class="stat-label">Diproses</div>
                    </div>
                </div>
                <div class="stat-chip">
                    <span class="stat-dot stat-dot--green" aria-hidden="true"></span>
                    <div>
                        <div class="stat-num">{{ $counts['selesai'] ?? 0 }}</div>
                        <div class="stat-label">Selesai</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="card pengaduan-card">
            <form id="formFilterPengaduan" method="GET" action="{{ route('admin.pengaduan.index') }}"
                class="table-toolbar">
                <div class="table-search">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input type="text" id="searchPengaduan" name="q" value="{{ request('q') }}"
                        placeholder="Cari nama warga, NIK, judul, atau isi laporan..." autocomplete="off"
                        aria-label="Cari pengaduan">
                </div>

                <div class="toolbar-right">
                    {{-- Filter Status --}}
                    <div class="combo combo--arrow" id="combo_filterStatus">
                        <input type="text" id="cari_filterStatus" class="combo-input" autocomplete="off"
                            aria-label="Filter status">
                        <div class="combo-list" id="list_filterStatus"></div>
                    </div>
                    <select id="filterStatus" name="status" class="combo-hidden" tabindex="-1" aria-hidden="true">
                        <option value="">Semua Status</option>
                        <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                        <option value="diproses" @selected(request('status') === 'diproses')>Diproses</option>
                        <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                        <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
                    </select>

                    {{-- Filter Kategori --}}
                    <div class="combo combo--arrow" id="combo_filterKategori">
                        <input type="text" id="cari_filterKategori" class="combo-input" autocomplete="off"
                            aria-label="Filter kategori">
                        <div class="combo-list" id="list_filterKategori"></div>
                    </div>
                    <select id="filterKategori" name="kategori" class="combo-hidden" tabindex="-1" aria-hidden="true">
                        <option value="">Semua Kategori</option>
                        @foreach (\App\Models\Pengaduan::KATEGORI_LABEL as $key => $label)
                            <option value="{{ $key }}" @selected(request('kategori') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>

                    <button type="button" id="btnResetPengaduan" class="btn btn--ghost">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 6h18"></path>
                            <path d="M8 6V4h8v2"></path>
                            <path d="M19 6l-1 14H6L5 6"></path>
                        </svg>
                        Reset
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th>Warga</th>
                            <th>Kategori</th>
                            <th>Laporan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pengaduans as $item)
                            @php
                                $namaWarga = $item->warga->nama ?? ($item->user->username ?? 'Warga');
                                $nikWarga = $item->warga->nik ?? '-';
                                $tanggalLengkap = optional($item->created_at)->translatedFormat('d M Y, H:i') ?? '-';
                                $tanggalSingkat = optional($item->created_at)->translatedFormat('d M Y') ?? '-';
                                $badgeModifier = match ($item->status) {
                                    'menunggu' => 'status-badge--menunggu',
                                    'diproses' => 'status-badge--diproses',
                                    'selesai' => 'status-badge--selesai',
                                    default => 'status-badge--ditolak',
                                };
                                $isLocked = in_array($item->status, ['selesai', 'ditolak']);
                            @endphp
                            <tr>
                                <td class="col-no mono">{{ $pengaduans->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="cell-name">{{ $namaWarga }}</div>
                                    <div class="mono">{{ $nikWarga }}</div>
                                </td>
                                <td>
                                    <span class="kategori-tag">{{ $item->kategori_label }}</span>
                                </td>
                                <td>
                                    <div class="report-title">{{ $item->judul }}</div>
                                    <div class="report-snippet">{{ $item->deskripsi }}</div>
                                </td>
                                <td class="cell-date" style="white-space: nowrap;">{{ $tanggalSingkat }}</td>
                                <td style="white-space: nowrap;">
                                    <span class="status-badge {{ $badgeModifier }}">{{ $item->status_label }}</span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <button type="button"
                                        class="btn btn--sm {{ $isLocked ? 'btn--ghost' : 'btn--outline-primary' }}"
                                        data-open-pengaduan
                                        data-action-url="{{ route('admin.pengaduan.update', $item->id_pengaduan) }}"
                                        data-kategori="{{ $item->kategori_label }}" data-judul="{{ $item->judul }}"
                                        data-nama="{{ $namaWarga }}" data-nik="{{ $nikWarga }}"
                                        data-tanggal="{{ $tanggalLengkap }}" data-deskripsi="{{ $item->deskripsi }}"
                                        data-foto-url="{{ $item->foto_url ?? '' }}" data-status="{{ $item->status }}"
                                        data-tanggapan="{{ $item->tanggapan_admin ?? '' }}">
                                        {{ $isLocked ? 'Lihat Detail' : 'Detail & Balas' }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-cell">
                                    @if (request()->filled('q') || request()->filled('status') || request()->filled('kategori'))
                                        <div class="empty-icon" aria-hidden="true">🔍</div>
                                        <strong>Data Tidak Ditemukan</strong>
                                        <p class="empty-text">Tidak ada pengaduan yang cocok dengan pencarian atau filter ini.</p>
                                    @else
                                        <div class="empty-icon" aria-hidden="true">📭</div>
                                        <strong>Belum Ada Pengaduan Warga</strong>
                                        <p class="empty-text">Laporan yang dikirim warga akan muncul di halaman ini.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Selalu ada di halaman agar bisa diperbarui lewat pencarian tanpa refresh --}}
            <div class="pagination-wrap" id="pengaduanPagination">
                @if ($pengaduans->hasPages())
                    {{ $pengaduans->links() }}
                @endif
            </div>
        </section>

        {{-- MODAL DETAIL & TANGGAPAN --}}
        <div id="modalPengaduan" class="modal-overlay" aria-hidden="true" role="dialog" aria-modal="true"
            aria-labelledby="mpJudul">
            <div class="modal-box">
                <div class="modal-head">
                    <div>
                        <span id="mpKategori" class="kategori-tag"></span>
                        <h3 id="mpJudul" class="modal-title" style="margin-top: 8px;"></h3>
                        <div id="mpMeta" class="modal-sub"></div>
                    </div>
                    <button type="button" id="closeModalPengaduan" class="modal-close"
                        aria-label="Tutup modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="field">
                        <span class="field-label">Isi Laporan</span>
                        <div id="mpDeskripsi" class="detail-box"></div>
                    </div>

                    <div id="mpFotoWrap" class="field is-hidden">
                        <span class="field-label">Foto Bukti</span>
                        <img id="mpFoto" src="" alt="Foto bukti pengaduan" class="photo-preview">
                    </div>

                    <form id="formTanggapanPengaduan" method="POST" action=""
                        style="display: flex; flex-direction: column; gap: 14px; border-top: 1px solid var(--border-soft); padding-top: 14px;">
                        @csrf
                        @method('PATCH')

                        <div class="field">
                            <label for="cari_mpStatus" class="field-label">Status Laporan</label>
                            <div class="combo combo--arrow" id="combo_mpStatus">
                                <input type="text" id="cari_mpStatus" class="combo-input" autocomplete="off"
                                    aria-label="Status laporan">
                                <div class="combo-list" id="list_mpStatus"></div>
                            </div>
                            <select id="mpStatus" name="status" class="combo-hidden" tabindex="-1"
                                aria-hidden="true">
                                <option value="menunggu">Menunggu</option>
                                <option value="selesai">Selesai</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="mpTanggapan" class="field-label">Tanggapan Pengurus</label>
                            <textarea id="mpTanggapan" name="tanggapan_admin" rows="3" class="textarea"
                                placeholder="Tulis balasan atau tindak lanjut untuk warga..."></textarea>
                        </div>

                        <div id="mpLockNotice" class="is-hidden"
                            style="font-size: 12.5px; color: var(--t-muted); background: var(--bg-muted); padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border-soft);">
                            Pengaduan yang sudah <strong>Selesai</strong> atau <strong>Ditolak</strong> bersifat final dan
                            tidak dapat diubah kembali.
                        </div>

                        <div class="modal-foot" style="padding: 8px 0 0; border-top: 0;">
                            <button type="button" id="cancelModalPengaduan" class="btn btn--ghost">Batal</button>
                            <button type="submit" id="btnSimpanPengaduan" class="btn btn--primary">Simpan
                                Tanggapan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.__PENGADUAN_DATA__ = {
            resetUrl: "{{ route('admin.pengaduan.index') }}"
        };
    </script>
    @vite('resources/js/pages/pengaduan.js')
@endpush
