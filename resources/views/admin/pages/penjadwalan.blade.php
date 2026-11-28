@extends('admin.layouts.app')

@section('title', 'Penjadwalan Setoran Sampah')
@section('active', 'penjadwalan-setoran')
@section('crumbs', 'Master Data | Penjadwalan Setoran Sampah')

@push('styles')
    @vite('resources/css/pages/penjadwalan-setoran.css')
@endpush

@section('content')
<div class="penjadwalan-page">

    @php
        $totalJadwal = $jadwalSetoran->count();
        $totalMendatang = $jadwalSetoran
            ->filter(
                fn($j) => \Carbon\Carbon::parse($j->tanggal_setoran)->isFuture() ||
                    \Carbon\Carbon::parse($j->tanggal_setoran)->isToday(),
            )
            ->count();
        $totalDipublikasi = $jadwalSetoran->filter(fn($j) => $j->status === 'dipublikasi')->count();
        $totalDibatalkan = $jadwalSetoran->filter(fn($j) => $j->status === 'dibatalkan')->count();

        $jadwalJs = $jadwalSetoran
            ->map(
                fn($j) => [
                    'id' => $j->id_jadwal,
                    'nama_agenda' => $j->nama_agenda,
                    'tanggal' => \Carbon\Carbon::parse($j->tanggal_setoran)->format('Y-m-d'),
                    'jam_mulai' => substr((string) $j->jam_mulai, 0, 5),
                    'jam_selesai' => substr((string) $j->jam_selesai, 0, 5),
                    'wilayah' => $j->wilayah ?? 'A (Perumahan)',
                    'kuota' => $j->kuota_maksimal ?? 150,
                    'status' => $j->status,
                ],
            )
            ->values();

        $iconEdit =
            '<svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path></svg>';
        $iconHapus =
            '<svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6M14 11v6"></path></svg>';
    @endphp

    {{-- HERO HEADER --}}
    <section class="hero">
        <div class="hero-text">
            <span class="eyebrow">MASTER DATA</span>
            <h1 class="hero-title">Penjadwalan <span class="accent">Setoran Sampah</span></h1>
            <p class="hero-sub">Kelola tanggal rutin penjemputan dan kuota setoran sampah untuk warga.</p>
        </div>
        <div class="hero-actions">
            <button class="btn btn--ghost" type="button" onclick="bukaModalKelolaPreset()">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path
                        d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                    </path>
                </svg>
                Kelola Preset
            </button>
            <button class="btn btn--primary" type="button" onclick="bukaModalTambahJadwal()">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Jadwal
            </button>
        </div>
    </section>

    {{-- SUMMARY CARDS (STATIS / TIDAK BISA DIKLIK) --}}
    <section class="summary-grid">
        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Total Jadwal</div>
                    <div class="summary-value">{{ $totalJadwal }}</div>
                </div>
                <div class="summary-icon">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Seluruh jadwal setoran terdaftar</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Jadwal Mendatang</div>
                    <div class="summary-value">{{ $totalMendatang }}</div>
                </div>
                <div class="summary-icon summary-icon--income">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Jadwal hari ini atau belum lewat</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Jadwal Dipublikasi</div>
                    <div class="summary-value">{{ $totalDipublikasi }}</div>
                </div>
                <div class="summary-icon summary-icon--income">
                    <svg viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Jadwal aktif tampil untuk warga</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Dibatalkan / Batal</div>
                    <div class="summary-value">{{ $totalDibatalkan }}</div>
                </div>
                <div class="summary-icon summary-icon--expense">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Jadwal setoran yang dibatalkan</div>
        </div>
    </section>

    {{-- PRESET PENJADWALAN RUTIN --}}
    <section class="card mb-4">
        <div class="preset-container">
            <div class="preset-info">
                <strong>Preset Rutin Cepat:</strong>
                <p class="muted">Klik preset, form langsung terisi dengan tanggal terdekat yang akan datang.</p>
            </div>
            <div class="preset-buttons" id="presetButtonsWrap"></div>
        </div>
    </section>

    {{-- DATA TABLE --}}
    <section class="card">
        <div class="table-toolbar">
            <div class="table-search">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchJadwal" placeholder="Cari nama agenda, wilayah, atau tanggal..."
                    autocomplete="off">
            </div>

            <div class="toolbar-right">
                <button class="btn btn--ghost" type="button" onclick="bukaModalKalender()">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="17" rx="2"></rect>
                        <path d="M3 10h18M8 2v4M16 2v4"></path>
                    </svg>
                    Tampilan Kalender
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
            <table class="data-table" id="jadwalTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Agenda Setoran</th>
                        <th>Tanggal Setoran</th>
                        <th>Waktu Operasional</th>
                        <th>Kelompok</th>
                        <th>Kuota Warga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="jadwalTableBody">
                    @forelse($jadwalSetoran as $index => $jadwal)
                        @php
                            $tglSetoran = $jadwal->tanggal_setoran
                                ? \Carbon\Carbon::parse($jadwal->tanggal_setoran)
                                : null;
                        @endphp
                        <tr data-row-kategori data-group="{{ $jadwal->id_jadwal }}"
                            data-status="{{ $jadwal->status }}"
                            data-tanggal="{{ $tglSetoran ? $tglSetoran->format('Y-m-d') : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $jadwal->nama_agenda }}</strong></td>
                            <td class="nowrap">
                                <strong>{{ $tglSetoran ? $tglSetoran->translatedFormat('d M Y') : '-' }}</strong>
                                <div class="muted day-sub">
                                    {{ $tglSetoran ? $tglSetoran->translatedFormat('l') : '' }}</div>
                            </td>
                            <td class="nowrap">
                                {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }} WIB
                            </td>
                            <td>
                                <span class="badge badge--satuan">{{ $jadwal->wilayah ?? 'A (Perumahan)' }}</span>
                            </td>
                            <td>
                                <strong>{{ $jadwal->kuota_maksimal ?? 150 }} Warga</strong>
                            </td>
                            <td>
                                @php
                                    $statusClass = 'badge--warning';
                                    if ($jadwal->status === 'dipublikasi') {
                                        $statusClass = 'badge--success';
                                    }
                                    if ($jadwal->status === 'selesai') {
                                        $statusClass = 'badge--satuan';
                                    }
                                    if ($jadwal->status === 'dibatalkan') {
                                        $statusClass = 'badge--danger-style';
                                    }
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ ucfirst($jadwal->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="icon-btn icon-btn--edit" title="Ubah Status" type="button"
                                        onclick="editJadwalModal({{ Js::from([
                                            'id_jadwal' => $jadwal->id_jadwal,
                                            'nama_agenda' => $jadwal->nama_agenda,
                                            'tanggal_setoran' => $jadwal->tanggal_setoran,
                                            'jam_mulai' => $jadwal->jam_mulai,
                                            'jam_selesai' => $jadwal->jam_selesai,
                                            'wilayah' => $jadwal->wilayah,
                                            'kuota_maksimal' => $jadwal->kuota_maksimal,
                                            'status' => $jadwal->status,
                                            'keterangan' => $jadwal->keterangan,
                                        ]) }})">
                                        {!! $iconEdit !!}
                                    </button>
                                    <button class="icon-btn icon-btn--danger" title="Hapus Jadwal" type="button"
                                        onclick="hapusJadwal({{ Js::from(['id_jadwal' => $jadwal->id_jadwal, 'nama_agenda' => $jadwal->nama_agenda]) }})">
                                        {!! $iconHapus !!}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state-cell">
                                <div class="empty-icon">📅</div>
                                <strong>Belum Ada Jadwal Setoran</strong>
                                <p class="empty-desc">Belum ada jadwal penjemputan/setoran sampah yang dibuat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="catalog-empty" id="jadwalKosongCari" style="display: none;">
                <div class="empty-icon">🔍</div>
                <strong>Data Tidak Ditemukan</strong>
                <p class="empty-desc">Tidak ada jadwal setoran yang cocok dengan pencarian atau filter ini.</p>
            </div>
        </div>

        <div class="table-footer">
            <div class="table-info" id="tableInfoPagination">Menampilkan data...</div>

            <div class="table-pagination-controls">
                <div class="per-page">
                    <label for="cari_perPageSelect">Data per halaman:</label>
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

    {{-- TOAST WRAPPER --}}
    <div class="toast-wrap" id="penjadwalanToastWrap"></div>

    {{-- MODAL 1: TAMBAH / EDIT JADWAL --}}
    <div class="modal-overlay" id="modalJadwal">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title" id="modalJadwalTitle">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span id="modalJadwalTitleText">Tambah Jadwal Setoran</span>
                </h3>
                <button class="modal-close" type="button" aria-label="Tutup"
                    onclick="tutupModal('modalJadwal')">&times;</button>
            </div>

            <form id="formJadwal" class="modal-form" onsubmit="return simpanJadwal(event)">
                <div class="modal-body">
                    <input type="hidden" id="jadwalId">
                    <div class="form-grid">
                        <div class="form-group form-group--full edit-only" id="editRingkasan"></div>

                        <div class="form-group form-group--full field-create-only">
                            <label for="nama_agenda">Nama Agenda Setoran</label>
                            <input type="text" id="nama_agenda" class="form-control"
                                placeholder="Contoh: Setoran Rutin Zonasi A" required autocomplete="off">
                            <span class="form-error" id="err_nama_agenda"></span>
                        </div>

                        <div class="form-group field-create-only">
                            <label for="tanggal_setoran">Tanggal Setoran</label>
                            <input type="date" id="tanggal_setoran" class="form-control" required>
                            <span class="date-hint" id="presetDateHint" hidden></span>
                            <span class="form-error" id="err_tanggal_setoran"></span>
                        </div>

                        <div class="form-group">
                            <label for="cari_status">Status Publikasi</label>
                            <div class="combo" id="combo_status">
                                <input type="text" id="cari_status" class="combo-input" placeholder="Pilih status..."
                                    autocomplete="off">
                                <div class="combo-list" id="list_status"></div>
                            </div>
                            <select id="status" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="draft">Draft (Internal)</option>
                                <option value="dipublikasi" selected>Dipublikasi (Aktif untuk Warga)</option>
                                <option value="selesai">Selesai</option>
                                <option value="dibatalkan">Dibatalkan</option>
                            </select>
                            <span class="form-error" id="err_status"></span>
                        </div>

                        {{-- JAM OPERASIONAL --}}
                        <div class="form-group field-create-only">
                            <label>Jam Mulai Operasional</label>
                            <div class="tp" id="tp_jam_mulai">
                                <div class="tp-seg">
                                    <button type="button" class="tp-step" data-part="h" data-dir="1"
                                        aria-label="Tambah jam mulai"><svg viewBox="0 0 24 24">
                                            <path d="m6 15 6-6 6 6"></path>
                                        </svg></button>
                                    <input type="text" class="tp-num" data-part="h" inputmode="numeric"
                                        maxlength="2" value="08" autocomplete="off" aria-label="Jam mulai (jam)">
                                    <button type="button" class="tp-step" data-part="h" data-dir="-1"
                                        aria-label="Kurangi jam mulai"><svg viewBox="0 0 24 24">
                                            <path d="m6 9 6 6 6-6"></path>
                                        </svg></button>
                                </div>
                                <span class="tp-colon">:</span>
                                <div class="tp-seg">
                                    <button type="button" class="tp-step" data-part="m" data-dir="1"
                                        aria-label="Tambah menit mulai"><svg viewBox="0 0 24 24">
                                            <path d="m6 15 6-6 6 6"></path>
                                        </svg></button>
                                    <input type="text" class="tp-num" data-part="m" inputmode="numeric"
                                        maxlength="2" value="00" autocomplete="off" aria-label="Jam mulai (menit)">
                                    <button type="button" class="tp-step" data-part="m" data-dir="-1"
                                        aria-label="Kurangi menit mulai"><svg viewBox="0 0 24 24">
                                            <path d="m6 9 6 6 6-6"></path>
                                        </svg></button>
                                </div>
                                <span class="tp-zone">WIB</span>
                            </div>
                            <input type="hidden" id="jam_mulai" value="08:00">
                            <span class="form-error" id="err_jam_mulai"></span>
                        </div>

                        <div class="form-group field-create-only">
                            <label>Jam Selesai Operasional</label>
                            <div class="tp" id="tp_jam_selesai">
                                <div class="tp-seg">
                                    <button type="button" class="tp-step" data-part="h" data-dir="1"
                                        aria-label="Tambah jam selesai"><svg viewBox="0 0 24 24">
                                            <path d="m6 15 6-6 6 6"></path>
                                        </svg></button>
                                    <input type="text" class="tp-num" data-part="h" inputmode="numeric"
                                        maxlength="2" value="12" autocomplete="off" aria-label="Jam selesai (jam)">
                                    <button type="button" class="tp-step" data-part="h" data-dir="-1"
                                        aria-label="Kurangi jam selesai"><svg viewBox="0 0 24 24">
                                            <path d="m6 9 6 6 6-6"></path>
                                        </svg></button>
                                </div>
                                <span class="tp-colon">:</span>
                                <div class="tp-seg">
                                    <button type="button" class="tp-step" data-part="m" data-dir="1"
                                        aria-label="Tambah menit selesai"><svg viewBox="0 0 24 24">
                                            <path d="m6 15 6-6 6 6"></path>
                                        </svg></button>
                                    <input type="text" class="tp-num" data-part="m" inputmode="numeric"
                                        maxlength="2" value="00" autocomplete="off"
                                        aria-label="Jam selesai (menit)">
                                    <button type="button" class="tp-step" data-part="m" data-dir="-1"
                                        aria-label="Kurangi menit selesai"><svg viewBox="0 0 24 24">
                                            <path d="m6 9 6 6 6-6"></path>
                                        </svg></button>
                                </div>
                                <span class="tp-zone">WIB</span>
                            </div>
                            <input type="hidden" id="jam_selesai" value="12:00">
                            <span class="form-error" id="err_jam_selesai"></span>
                        </div>

                        <div class="form-group form-group--full field-create-only durasi-wrap">
                            <div class="durasi-info" id="durasiInfo"></div>
                        </div>

                        <div class="form-group field-create-only">
                            <label for="cari_wilayah">Target Wilayah / Zonasi</label>
                            <div class="combo" id="combo_wilayah">
                                <input type="text" id="cari_wilayah" class="combo-input"
                                    placeholder="Pilih Zonasi..." autocomplete="off">
                                <div class="combo-list" id="list_wilayah"></div>
                            </div>
                            <select id="wilayah" class="combo-hidden" tabindex="-1" aria-hidden="true">
                                <option value="">Semua Kelompok</option>
                                <option value="A (Perumahan)">A (Perumahan)</option>
                                <option value="B (Luar Perumahan)">B (Luar Perumahan)</option>
                            </select>
                            <span class="form-error" id="err_wilayah"></span>
                        </div>

                        <div class="form-group field-create-only">
                            <label for="kuota_maksimal">Maksimal Kuota (Warga)</label>
                            <input type="number" id="kuota_maksimal" class="form-control" value="150"
                                min="1" max="150" step="1" inputmode="numeric" required>
                            <span class="form-hint">Bisa diatur 1 sampai 150 warga.</span>
                            <span class="form-error" id="err_kuota_maksimal"></span>
                        </div>

                        <div class="form-group form-group--full field-create-only">
                            <label for="keterangan">Catatan / Keterangan Tambahan</label>
                            <textarea id="keterangan" class="form-control" rows="2"
                                placeholder="Contoh: Warga wajib memilah sampah dari rumah."></textarea>
                            <span class="form-error" id="err_keterangan"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-foot">
                    <button class="btn btn--ghost" type="button" onclick="tutupModal('modalJadwal')">Batal</button>
                    <button class="btn btn--primary" type="submit" id="jadwalSubmitBtn">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 3: KONFIRMASI HAPUS (JADWAL / PRESET) --}}
    <div class="modal-overlay modal-overlay--top" id="modalHapus">
        <div class="modal-box modal-box--sm">
            <div class="modal-body modal-body--center">
                <div class="confirm-icon">
                    <svg viewBox="0 0 24 24">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                        <path d="M10 11v6M14 11v6"></path>
                    </svg>
                </div>

                <h3 class="confirm-title" id="hapusTitle">Hapus Data?</h3>
                <p class="confirm-text" id="hapusText">Tindakan ini tidak dapat dibatalkan.</p>
            </div>

            <div class="modal-foot modal-foot--center">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalHapus')">Batal</button>
                <button class="btn btn--danger" type="button" id="hapusConfirmBtn">Ya, Hapus</button>
            </div>
        </div>
    </div>

    {{-- MODAL 4: KALENDER SETORAN --}}
    <div class="modal-overlay" id="modalKalender">
        <div class="modal-box modal-box--xl">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <rect x="3" y="4" width="18" height="17" rx="2"></rect>
                        <path d="M3 10h18M8 2v4M16 2v4"></path>
                    </svg>
                    <span>Kalender Penjadwalan Setoran</span>
                </h3>
                <button class="modal-close" type="button" aria-label="Tutup"
                    onclick="tutupModal('modalKalender')">&times;</button>
            </div>

            <div class="modal-body">
                <div class="calendar-toolbar">
                    <div class="calendar-nav">
                        <button type="button" class="icon-btn" onclick="geserKalender(-1)"
                            aria-label="Bulan sebelumnya"><svg viewBox="0 0 24 24">
                                <path d="m15 18-6-6 6-6"></path>
                            </svg></button>
                        <div class="calendar-title" id="kalenderJudul"></div>
                        <button type="button" class="icon-btn" onclick="geserKalender(1)"
                            aria-label="Bulan berikutnya"><svg viewBox="0 0 24 24">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg></button>
                        <button type="button" class="btn btn--ghost calendar-today-btn"
                            onclick="kembaliHariIni()">Hari ini</button>
                    </div>
                    <div class="calendar-note" id="kalenderKet"></div>
                </div>
                <div class="calendar-grid-wrapper">
                    <div class="calendar-header-days">
                        <div>Min</div>
                        <div>Sen</div>
                        <div>Sel</div>
                        <div>Rab</div>
                        <div>Kam</div>
                        <div>Jum</div>
                        <div>Sab</div>
                    </div>
                    <div class="calendar-days-matrix" id="calendarDaysMatrix"></div>
                </div>
            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalKalender')">Tutup</button>
            </div>
        </div>
    </div>

    {{-- MODAL 5: KELOLA PRESET RUTIN --}}
    <div class="modal-overlay" id="modalKelolaPreset">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-title">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path
                            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                        </path>
                    </svg>
                    <span>Pengaturan Preset Rutin Cepat</span>
                </h3>
                <button class="modal-close" type="button" aria-label="Tutup"
                    onclick="tutupModal('modalKelolaPreset')">&times;</button>
            </div>

            <div class="modal-body">
                <datalist id="presetHariList">
                    <option value="Senin">
                    <option value="Selasa">
                    <option value="Rabu">
                    <option value="Kamis">
                    <option value="Jumat">
                    <option value="Sabtu">
                    <option value="Minggu">
                </datalist>
                <form id="formTambahPreset" onsubmit="return simpanPresetRutin(event)" class="preset-add-form">
                    <div class="form-grid">
                        <div class="form-group form-group--full">
                            <label for="preset_nama">Nama Label Preset</label>
                            <input type="text" id="preset_nama" class="form-control"
                                placeholder="Contoh: Setoran Sabtu / Tanggal 1 & 15" required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="preset_tgl1">Tanggal / Hari Pertama</label>
                            <input type="text" id="preset_tgl1" class="form-control"
                                placeholder="Angka 1-31 atau nama hari, misal: Sabtu" list="presetHariList" required>
                        </div>
                        <div class="form-group">
                            <label for="preset_tgl2">Tanggal / Hari Kedua (Opsional)</label>
                            <input type="text" id="preset_tgl2" class="form-control"
                                placeholder="Opsional, misal: 15" list="presetHariList">
                        </div>
                    </div>
                    <div class="preset-add-actions">
                        <button type="submit" class="btn btn--primary preset-add-btn">+ Tambah Preset</button>
                    </div>
                </form>

                <hr class="preset-divider">

                <div class="table-responsive">
                    <table class="data-table data-table--sm">
                        <thead>
                            <tr>
                                <th>Nama Preset</th>
                                <th>Kolom Tanggal 1</th>
                                <th>Kolom Tanggal 2</th>
                                <th class="preset-col-action">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="daftarPresetTableBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalKelolaPreset')">Selesai</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script>
        window.__PENJADWALAN_DATA__ = {
            storeUrl: "{{ route('admin.penjadwalan-setoran.store') }}",
            updateUrl: "{{ route('admin.penjadwalan-setoran.update', ':id') }}",
            hapusUrl: "{{ route('admin.penjadwalan-setoran.destroy', ':id') }}",
            jadwalList: @json($jadwalJs),
            iconHapus: @json($iconHapus)
        };
    </script>
    @vite('resources/js/pages/penjadwalan-setoran.js')
@endpush
