@extends('admin.layouts.app')

@section('title', 'Penjadwalan Setoran Sampah')
@section('active', 'penjadwalan-setoran')
@section('crumbs', 'Master Data | Penjadwalan Setoran Sampah')

@section('content')

    @php
        // 1. Ringkasan Statistik
        $totalJadwal = $jadwalSetoran->count();
        $totalMendatang = $jadwalSetoran
            ->filter(
                fn($j) => \Carbon\Carbon::parse($j->tanggal_setoran)->isFuture() ||
                    \Carbon\Carbon::parse($j->tanggal_setoran)->isToday(),
            )
            ->count();
        $totalSelesai = $jadwalSetoran->filter(fn($j) => $j->status === 'selesai')->count();
        $totalDibatalkan = $jadwalSetoran->filter(fn($j) => $j->status === 'dibatalkan')->count();

        // Data ringkas untuk kalender (JS)
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

        // SVG Reusable Icons
        $iconEdit =
            '<svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
        $iconHapus =
            '<svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6M14 11v6"></path></svg>';
    @endphp

    <!-- HERO HEADER -->
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

    <!-- SUMMARY CARDS -->
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
            <div class="summary-info">Semua jadwal yang terdaftar</div>
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
            <div class="summary-info">Jadwal aktif atau belum lewat</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-top">
                <div>
                    <div class="summary-label">Setoran Selesai</div>
                    <div class="summary-value">{{ $totalSelesai }}</div>
                </div>
                <div class="summary-icon summary-icon--income">
                    <svg viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
            </div>
            <div class="summary-info">Pengumpulan sampah selesai</div>
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

    <!-- PRESET PENJADWALAN RUTIN (DINAMIS & LOCALSTORAGE) -->
    <section class="card mb-4">
        <div class="preset-container">
            <div class="preset-info">
                <strong>Preset Rutin Cepat:</strong>
                <p class="muted">Klik preset, form langsung terisi dengan tanggal terdekat yang akan datang.</p>
            </div>
            <div class="preset-buttons" id="presetButtonsWrap">
                <!-- Render via JS -->
            </div>
        </div>
    </section>

    <!-- DATA TABLE -->
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
                        <th>Target Wilayah / ZONASI</th>
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
                        <tr data-row-kategori data-group="{{ $jadwal->id_jadwal }}">
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $jadwal->nama_agenda }}</strong></td>
                            <td class="nowrap">
                                <strong>{{ $tglSetoran ? $tglSetoran->translatedFormat('d M Y') : '-' }}</strong>
                                <div class="muted" style="font-size: 11px;">
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
                                    <button class="icon-btn" title="Ubah Status" type="button"
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
                <p class="empty-desc">Tidak ada jadwal setoran yang cocok dengan pencarian ini.</p>
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

    <!-- TOAST WRAPPER -->
    <div class="toast-wrap" id="toastWrap"></div>

    <!-- MODAL 1: TAMBAH / EDIT JADWAL -->
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

                        <!-- JAM OPERASIONAL -->
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

                        <div class="form-group form-group--full field-create-only" style="margin-top: -6px;">
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
                                <option value="">Semua Kelompok</option> <!-- Opsi baru untuk semua -->
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

    <!-- MODAL 3: KONFIRMASI HAPUS (JADWAL / PRESET) -->
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

    <!-- MODAL 4: KALENDER SETORAN -->
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
                        <button type="button" class="btn btn--ghost" style="padding: 7px 14px;"
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
                    <div class="calendar-days-matrix" id="calendarDaysMatrix">
                        <!-- Generasi tanggal via JavaScript -->
                    </div>
                </div>
            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalKalender')">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL 5: KELOLA PRESET RUTIN (DENGAN PEMISAHAN TANGGAL & HAPUS BER-TOAST) -->
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
                <form id="formTambahPreset" onsubmit="return simpanPresetRutin(event)" style="margin-bottom: 20px;">
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
                    <div style="margin-top: 12px; text-align: right;">
                        <button type="submit" class="btn btn--primary" style="padding: 8px 16px;">+ Tambah
                            Preset</button>
                    </div>
                </form>

                <hr style="border: 0; border-top: 1px solid var(--dashboard-border); margin: 16px 0;">

                <div class="table-responsive">
                    <table class="data-table data-table--sm">
                        <thead>
                            <tr>
                                <th>Nama Preset</th>
                                <th>Kolom Tanggal 1</th>
                                <th>Kolom Tanggal 2</th>
                                <th style="width: 80px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="daftarPresetTableBody">
                            <!-- Render via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-foot">
                <button class="btn btn--ghost" type="button" onclick="tutupModal('modalKelolaPreset')">Selesai</button>
            </div>
        </div>
    </div>

    <!-- EXTENDED STYLESHEET -->
    <style>
        :root {
            --dashboard-page: #f5f7fb;
            --dashboard-card: #ffffff;
            --dashboard-card-secondary: #f8fafc;
            --dashboard-text: #1f2937;
            --dashboard-text-secondary: #6b7280;
            --dashboard-text-muted: #9ca3af;
            --dashboard-border: #e5e7eb;
            --dashboard-table-head: #f8fafc;
            --dashboard-table-row: #ffffff;
            --dashboard-table-hover: #f8fafc;
            --dashboard-input: #ffffff;
            --dashboard-row-strong: #e5e7eb;
            --dashboard-shadow: 0 8px 25px rgba(15, 23, 42, .06);
            --dashboard-shadow-lg: 0 18px 45px rgba(15, 23, 42, .12);

            --primary-color: var(--primary, #4338ca);
            --primary-ring: rgba(67, 56, 202, .10);

            --combo-accent: #4338ca;
            --combo-ring: rgba(67, 56, 202, .12);
            --combo-input-bg: #ffffff;
            --combo-hover: #eef2ff;

            --tone-primary-bg: #eef2ff;
            --tone-income-bg: #dcfce7;
            --tone-income-fg: #16a34a;
            --tone-expense-bg: #fee2e2;
            --tone-expense-fg: #dc2626;

            --badge-satuan-bg: #e0f2fe;
            --badge-satuan-fg: #0369a1;
            --badge-success-bg: #dcfce7;
            --badge-success-fg: #15803d;
            --badge-warning-bg: #fef3c7;
            --badge-warning-fg: #b45309;

            --btn-harga-bg: #dcfce7;
            --btn-harga-bd: #bbf7d0;
            --btn-harga-fg: #15803d;

            --btn-danger-bg: #fef2f2;
            --btn-danger-bd: #fecaca;
            --btn-danger-fg: #dc2626;
        }

        [data-theme="dark"] {
            --dashboard-page: #0b1220;
            --dashboard-card: #151d2f;
            --dashboard-card-secondary: #1b2438;
            --dashboard-text: #f1f5f9;
            --dashboard-text-secondary: #aab6c8;
            --dashboard-text-muted: #748198;
            --dashboard-border: #29364d;
            --dashboard-table-head: #111a2c;
            --dashboard-table-row: #151d2f;
            --dashboard-table-hover: #202b40;
            --dashboard-input: #1b2438;
            --dashboard-row-strong: #29364d;
            --dashboard-shadow: 0 8px 25px rgba(0, 0, 0, .25);
            --dashboard-shadow-lg: 0 20px 50px rgba(0, 0, 0, .35);

            --primary-color: #818cf8;
            --primary-ring: rgba(129, 140, 248, .18);
            --combo-accent: #818cf8;
            --combo-ring: rgba(129, 140, 248, .20);
            --combo-input-bg: #111a2c;
            --combo-hover: #202b40;

            --tone-primary-bg: rgba(129, 140, 248, .15);
            --tone-income-bg: rgba(34, 197, 94, .15);
            --tone-income-fg: #4ade80;
            --tone-expense-bg: rgba(220, 38, 38, .15);
            --tone-expense-fg: #f87171;

            --badge-satuan-bg: rgba(3, 105, 161, .25);
            --badge-satuan-fg: #38bdf8;
            --badge-success-bg: rgba(34, 197, 94, .20);
            --badge-success-fg: #4ade80;
            --badge-warning-bg: rgba(245, 158, 11, .20);
            --badge-warning-fg: #fbbf24;

            --btn-harga-bg: rgba(34, 197, 94, .15);
            --btn-harga-bd: rgba(34, 197, 94, .35);
            --btn-harga-fg: #4ade80;

            --btn-danger-bg: rgba(220, 38, 38, .15);
            --btn-danger-bd: rgba(220, 38, 38, .35);
            --btn-danger-fg: #f87171;
        }

        .mb-4 {
            margin-bottom: 20px;
        }

        .preset-container {
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .preset-info strong {
            font-size: 13.5px;
            color: var(--dashboard-text);
        }

        .preset-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .badge--danger-style {
            background: var(--tone-expense-bg);
            color: var(--tone-expense-fg);
        }

        /* Time Picker */
        .tp {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 8px 12px;
            border: 1px solid var(--dashboard-border);
            border-radius: 12px;
            background: var(--dashboard-input);
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .tp:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-ring);
        }

        .tp-seg {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        .tp-step {
            width: 56px;
            height: 24px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: var(--dashboard-text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .12s ease, color .12s ease;
        }

        .tp-step:hover {
            background: var(--combo-hover);
            color: var(--combo-accent);
        }

        .tp-step svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.4;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .tp-num {
            width: 56px;
            height: 46px;
            text-align: center;
            border: 0;
            outline: none;
            border-radius: 10px;
            background: var(--dashboard-card-secondary);
            color: var(--dashboard-text);
            font-family: inherit;
            font-size: 26px;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            padding: 0;
        }

        .tp-num:focus {
            background: var(--combo-hover);
            color: var(--combo-accent);
        }

        .tp-colon {
            font-size: 24px;
            font-weight: 800;
            color: var(--dashboard-text-muted);
        }

        .tp-zone {
            font-size: 11px;
            font-weight: 700;
            color: var(--dashboard-text-secondary);
            margin-left: 2px;
        }

        .durasi-info {
            font-size: 12.5px;
            color: var(--dashboard-text-secondary);
        }

        .durasi-info.is-error {
            color: #dc2626;
            font-weight: 600;
        }

        /* Kalender */
        .calendar-grid-wrapper {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .calendar-header-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-weight: 700;
            font-size: 12px;
            color: var(--dashboard-text-secondary);
            padding-bottom: 6px;
            border-bottom: 1px solid var(--dashboard-border);
        }

        .calendar-days-matrix {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
        }

        .calendar-day-cell {
            min-height: 80px;
            padding: 8px;
            background: var(--dashboard-card-secondary);
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            font-size: 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .calendar-day-number {
            font-weight: 700;
            color: var(--dashboard-text);
        }

        .calendar-day-event {
            background: var(--badge-success-bg);
            color: var(--badge-success-fg);
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 4px;
        }

        /* Summary */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .summary-card {
            border: 1px solid var(--dashboard-border);
            background: var(--dashboard-card);
            color: var(--dashboard-text);
            border-radius: 14px;
            padding: 18px;
            box-shadow: var(--dashboard-shadow);
        }

        .summary-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .summary-label {
            font-size: 13px;
            color: var(--dashboard-text-secondary);
            margin-bottom: 8px;
        }

        .summary-value {
            font-size: 21px;
            font-weight: 800;
            line-height: 1.25;
            color: var(--dashboard-text);
        }

        .summary-icon {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--tone-primary-bg);
            color: var(--primary-color);
        }

        .summary-icon--income {
            background: var(--tone-income-bg);
            color: var(--tone-income-fg);
        }

        .summary-icon--expense {
            background: var(--tone-expense-bg);
            color: var(--tone-expense-fg);
        }

        .summary-icon svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .summary-info {
            margin-top: 12px;
            font-size: 12px;
            color: var(--dashboard-text-secondary);
            line-height: 1.5;
        }

        .card {
            background: var(--dashboard-card);
            border: 1px solid var(--dashboard-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: var(--dashboard-shadow);
            color: var(--dashboard-text);
        }

        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            flex-wrap: wrap;
        }

        .table-search {
            width: 400px;
            max-width: 100%;
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
            color: var(--dashboard-text-secondary);
            pointer-events: none;
        }

        .table-search input {
            width: 100%;
            height: 40px;
            padding: 0 14px 0 40px;
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            outline: none;
            background: var(--dashboard-input);
            color: var(--dashboard-text);
            font-family: inherit;
            font-size: 13px;
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
            background: var(--dashboard-table-row);
            color: var(--dashboard-text);
        }

        .data-table--sm {
            min-width: 0;
        }

        .data-table th {
            background: var(--dashboard-table-head);
            color: var(--dashboard-text-secondary);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 13px 16px;
            text-align: left;
            border-top: 1px solid var(--dashboard-border);
            border-bottom: 1px solid var(--dashboard-border);
            white-space: nowrap;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid var(--dashboard-border);
            font-size: 13px;
            color: var(--dashboard-text);
            vertical-align: middle;
            background: var(--dashboard-table-row);
        }

        .data-table tbody tr:hover td {
            background: var(--dashboard-table-hover);
        }

        .nowrap {
            white-space: nowrap;
        }

        .muted,
        .empty-desc {
            color: var(--dashboard-text-secondary);
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge--satuan {
            background: var(--badge-satuan-bg);
            color: var(--badge-satuan-fg);
        }

        .badge--success {
            background: var(--badge-success-bg);
            color: var(--badge-success-fg);
        }

        .badge--warning {
            background: var(--badge-warning-bg);
            color: var(--badge-warning-fg);
        }

        .table-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            border: 1px solid var(--dashboard-border);
            background: var(--dashboard-card);
            color: var(--dashboard-text-secondary);
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .15s ease;
        }

        .icon-btn:hover {
            background: var(--dashboard-table-hover);
            color: var(--dashboard-text);
        }

        .icon-btn svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .icon-btn--danger {
            color: var(--btn-danger-fg);
            background: var(--btn-danger-bg);
            border-color: var(--btn-danger-bd);
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            flex-wrap: wrap;
        }

        .table-info {
            font-size: 13px;
            color: var(--dashboard-text-secondary);
        }

        .table-pagination-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .per-page {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .per-page label {
            font-size: 13px;
            color: var(--dashboard-text-secondary);
        }

        .per-page .combo {
            width: 84px;
        }

        .pagination-buttons {
            display: flex;
            gap: 4px;
            align-items: center;
        }

        .catalog-empty {
            text-align: center;
            padding: 50px 20px;
            color: var(--dashboard-text);
        }

        .empty-state-cell {
            text-align: center;
            padding: 40px !important;
        }

        .empty-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }

        .form-group--full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--dashboard-text-secondary);
        }

        .form-control {
            width: 100%;
            min-height: 40px;
            padding: 10px 14px;
            font-size: 13px;
            font-family: inherit;
            color: var(--dashboard-text);
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            outline: none;
            background: var(--dashboard-input);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-ring);
        }

        .form-error {
            display: block;
            margin-top: 2px;
            font-size: 12px;
            color: #dc2626;
            min-height: 14px;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .60);
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
            max-width: 620px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: var(--dashboard-card);
            color: var(--dashboard-text);
            border: 1px solid var(--dashboard-border);
            border-radius: 14px;
            box-shadow: var(--dashboard-shadow-lg);
        }

        .modal-box--sm {
            max-width: 420px;
        }

        .modal-box--xl {
            max-width: 1180px;
        }

        .modal-form {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 20px 24px;
            border-bottom: 1px solid var(--dashboard-border);
        }

        .modal-title {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-close {
            width: 30px;
            height: 30px;
            border: none;
            background: transparent;
            font-size: 20px;
            color: var(--dashboard-text-secondary);
            cursor: pointer;
        }

        .modal-body {
            padding: 22px 24px;
            overflow-y: auto;
            flex: 1;
        }

        .modal-body--center {
            text-align: center;
            padding-top: 28px;
        }

        .modal-foot {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid var(--dashboard-border);
        }

        .modal-foot--center {
            justify-content: center;
        }

        .btn--danger {
            background: #dc2626;
            color: #fff;
            border: 1px solid #dc2626;
        }

        .btn--danger:hover {
            background: #b91c1c;
        }

        .confirm-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 14px;
            border-radius: 50%;
            background: var(--btn-danger-bg);
            color: var(--btn-danger-fg);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .confirm-icon svg {
            width: 24px;
            height: 24px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .confirm-title {
            margin: 0 0 8px;
            font-size: 16px;
            font-weight: 700;
        }

        .confirm-text {
            margin: 0;
            font-size: 13px;
            color: var(--dashboard-text-secondary);
            line-height: 1.6;
        }

        /* Toast */
        .toast-wrap {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 3000;
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
            background: var(--dashboard-card);
            color: var(--dashboard-text);
            border: 1px solid var(--dashboard-border);
            border-left: 4px solid #16a34a;
            border-radius: 10px;
            box-shadow: var(--dashboard-shadow-lg);
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
            background: var(--tone-income-bg);
            color: var(--tone-income-fg);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .toast.toast--error .toast-icon {
            background: var(--tone-expense-bg);
            color: var(--tone-expense-fg);
        }

        .toast-body {
            flex: 1;
        }

        .toast-title {
            font-size: 13px;
            font-weight: 700;
            margin: 0 0 2px;
        }

        .toast-text {
            font-size: 12.5px;
            color: var(--dashboard-text-secondary);
            margin: 0;
        }

        .toast-close {
            border: none;
            background: transparent;
            color: var(--dashboard-text-secondary);
            font-size: 16px;
            cursor: pointer;
        }

        /* Combobox */
        .combo {
            position: relative;
        }

        .combo-hidden {
            display: none !important;
        }

        .combo-input {
            width: 100%;
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            background: var(--dashboard-input);
            color: var(--dashboard-text);
        }

        .combo--arrow .combo-input {
            cursor: pointer;
            padding-right: 30px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 9px center;
            background-size: 14px;
        }

        .combo-list {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            max-height: 200px;
            overflow-y: auto;
            background: var(--dashboard-card);
            border: 1px solid var(--dashboard-border);
            border-radius: 8px;
            z-index: 60;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .22);
        }

        .combo-list.show {
            display: block;
        }

        .combo-item {
            padding: 10px 14px;
            font-size: 13px;
            cursor: pointer;
        }

        .combo-item:hover,
        .combo-item.is-active {
            background: var(--combo-hover);
            color: var(--combo-accent);
        }

        /* ---- Tambahan ---- */
        .modal-overlay--top {
            z-index: 2000;
        }

        .form-hint {
            display: block;
            font-size: 12px;
            color: var(--dashboard-text-secondary);
        }

        .date-hint {
            display: block;
            font-size: 12px;
            color: var(--dashboard-text-secondary);
            line-height: 1.8;
        }

        .date-hint[hidden] {
            display: none;
        }

        .link-btn {
            border: 1px solid var(--dashboard-border);
            background: var(--tone-primary-bg);
            color: var(--primary-color);
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 999px;
            cursor: pointer;
            margin-left: 4px;
        }

        .link-btn:hover {
            border-color: var(--primary-color);
        }

        .preset-next {
            margin-left: 8px;
            padding: 2px 8px;
            border-radius: 999px;
            background: var(--tone-primary-bg);
            color: var(--primary-color);
            font-size: 11px;
            font-weight: 700;
        }

        /* Kalender (data nyata, hanya status dipublikasi) */
        .calendar-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .calendar-nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .calendar-title {
            min-width: 170px;
            text-align: center;
            font-size: 15px;
            font-weight: 800;
            color: var(--dashboard-text);
        }

        .calendar-note {
            font-size: 12.5px;
            color: var(--dashboard-text-secondary);
        }

        .calendar-header-days,
        .calendar-days-matrix {
            grid-template-columns: repeat(7, minmax(0, 1fr));
        }

        .calendar-day-cell {
            justify-content: flex-start;
            gap: 4px;
            min-width: 0;
            min-height: 96px;
        }

        .calendar-day-cell--empty {
            background: transparent;
            border-color: transparent;
        }

        .calendar-day-cell--past {
            opacity: .55;
        }

        .calendar-day-cell--today {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px var(--primary-ring);
        }

        .calendar-day-cell--today .calendar-day-number {
            color: var(--primary-color);
        }

        .calendar-day-event {
            margin-top: 0;
        }

        .calendar-day-more {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--dashboard-text-secondary);
        }

        @media (max-width: 720px) {
            .form-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .calendar-day-cell {
                min-height: 70px;
                padding: 5px;
            }
        }

        /* Edit hanya status */
        .edit-only {
            display: none;
        }

        #formJadwal.is-edit .edit-only {
            display: flex;
        }

        #formJadwal.is-edit .field-create-only {
            display: none;
        }

        #formJadwal.is-edit .form-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .edit-ringkasan {
            padding: 14px 16px;
            border: 1px solid var(--dashboard-border);
            border-radius: 10px;
            background: var(--dashboard-card-secondary);
            line-height: 1.7;
        }

        .edit-ringkasan .form-hint {
            margin-top: 6px;
        }

        .date-chip {
            display: inline-block;
            margin-left: 4px;
            padding: 2px 10px;
            border-radius: 999px;
            background: var(--tone-primary-bg);
            color: var(--primary-color);
            font-size: 12px;
            font-weight: 700;
        }

        .form-control:disabled {
            opacity: .7;
            cursor: not-allowed;
        }
    </style>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        const urlJadwalStore = "{{ route('admin.penjadwalan-setoran.store') }}";
        const urlJadwalUpdate = "{{ route('admin.penjadwalan-setoran.update', ':id') }}";
        const urlJadwalHapus = "{{ route('admin.penjadwalan-setoran.destroy', ':id') }}";

        const FIELD_JADWAL = ['nama_agenda', 'tanggal_setoran', 'jam_mulai', 'jam_selesai', 'wilayah', 'kuota_maksimal',
            'status', 'keterangan'
        ];

        const dataJadwal = @json($jadwalJs);

        // Storage Preset Rutin Cepat (Persisten via localStorage)
        let presetList = JSON.parse(localStorage.getItem('bank_sampah_presets')) || [{
                id: 1,
                nama_preset: 'Awal & Tengah Bulan',
                tgl_1: '1',
                tgl_2: '15'
            },
            {
                id: 2,
                nama_preset: 'Tengah & Akhir Bulan',
                tgl_1: '14',
                tgl_2: '28'
            },
            {
                id: 3,
                nama_preset: 'Setoran Sabtu Rutin',
                tgl_1: 'Sabtu',
                tgl_2: ''
            }
        ];

        function simpanPresetStorage() {
            localStorage.setItem('bank_sampah_presets', JSON.stringify(presetList));
        }

        /* ---------- UTIL ---------- */
        function escapeHtml(s) {
            return String(s ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
        }

        function getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.content : '';
        }

        function tanggalHariIni() {
            const d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2,
                '0');
        }

        function setBusy(btn, busy) {
            if (btn) btn.disabled = busy;
        }

        function bukaModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function tutupModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function showToast(title, text, type = 'success') {
            const wrap = document.getElementById('toastWrap');
            if (!wrap) return;

            const toast = document.createElement('div');
            toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');
            const iconPath = type === 'error' ? '<path d="M18 6 6 18M6 6l12 12"></path>' :
                '<path d="M20 6 9 17l-5-5"></path>';

            toast.innerHTML = `
                <div class="toast-icon"><svg viewBox="0 0 24 24">${iconPath}</svg></div>
                <div class="toast-body">
                    <p class="toast-title">${escapeHtml(title)}</p>
                    <p class="toast-text">${escapeHtml(text)}</p>
                </div>
                <button class="toast-close" type="button" aria-label="Tutup">&times;</button>
            `;

            const hapusToast = () => toast.remove();
            toast.querySelector('.toast-close').addEventListener('click', hapusToast);
            wrap.appendChild(toast);
            setTimeout(hapusToast, 3500);
        }

        /* ---------- TIME PICKER ---------- */
        const timePickers = {};
        const pad2 = n => String(n).padStart(2, '0');

        function menitDariJam(v) {
            const m = /^(\d{1,2}):(\d{2})/.exec(v || '');
            return m ? parseInt(m[1], 10) * 60 + parseInt(m[2], 10) : null;
        }

        function updateDurasi() {
            const el = document.getElementById('durasiInfo');
            if (!el) return;
            const a = menitDariJam(document.getElementById('jam_mulai').value);
            const b = menitDariJam(document.getElementById('jam_selesai').value);
            if (a === null || b === null) {
                el.textContent = '';
                return;
            }
            const d = b - a;
            if (d <= 0) {
                el.textContent = 'Jam selesai harus lebih akhir dari jam mulai.';
                el.classList.add('is-error');
            } else {
                const jam = Math.floor(d / 60),
                    mnt = d % 60;
                el.textContent = 'Durasi operasional: ' + (jam ? jam + ' jam' : '') + (jam && mnt ? ' ' : '') + (mnt ? mnt +
                    ' menit' : '');
                el.classList.remove('is-error');
            }
        }

        function initTimePicker(id) {
            const root = document.getElementById('tp_' + id);
            const hidden = document.getElementById(id);
            if (!root || !hidden) return;
            const hEl = root.querySelector('.tp-num[data-part="h"]');
            const mEl = root.querySelector('.tp-num[data-part="m"]');
            const batas = {
                h: 23,
                m: 59
            };

            const nilai = el => Math.min(batas[el.dataset.part], parseInt(el.value, 10) || 0);
            const simpanHidden = () => {
                hidden.value = pad2(nilai(hEl)) + ':' + pad2(nilai(mEl));
                updateDurasi();
            };
            const tulis = (h, m) => {
                hEl.value = pad2(h);
                mEl.value = pad2(m);
                simpanHidden();
            };

            function geser(part, arah) {
                const h = nilai(hEl),
                    m = nilai(mEl);
                if (part === 'h') {
                    tulis((h + arah + 24) % 24, m);
                } else {
                    const baru = arah > 0 ? (Math.floor(m / 5) + 1) * 5 : (Math.ceil(m / 5) - 1) * 5;
                    tulis(h, (baru + 60) % 60);
                }
            }

            root.addEventListener('click', e => {
                const b = e.target.closest('.tp-step');
                if (b) geser(b.dataset.part, parseInt(b.dataset.dir, 10));
            });

            [hEl, mEl].forEach(el => {
                el.addEventListener('focus', () => el.select());
                el.addEventListener('input', () => {
                    el.value = el.value.replace(/\D/g, '').slice(0, 2);
                    simpanHidden();
                });
                el.addEventListener('blur', () => {
                    el.value = pad2(nilai(el));
                    simpanHidden();
                });
                el.addEventListener('keydown', e => {
                    if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                        e.preventDefault();
                        geser(el.dataset.part, e.key === 'ArrowUp' ? 1 : -1);
                    }
                });
            });

            timePickers[id] = {
                set(v) {
                    const t = menitDariJam(v);
                    if (t !== null) tulis(Math.floor(t / 60), t % 60);
                }
            };
            timePickers[id].set(hidden.value);
        }

        function setTimeValue(id, v) {
            if (timePickers[id]) timePickers[id].set(v);
        }

        async function kirimJson(url, method, payload) {
            const headers = {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            };
            const opts = {
                method,
                headers
            };
            if (payload !== undefined) {
                headers['Content-Type'] = 'application/json';
                opts.body = JSON.stringify(payload);
            }
            const res = await fetch(url, opts);
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                const pesanPertama = err.errors ? (Object.values(err.errors)[0] || [])[0] : null;
                const e = new Error(pesanPertama || err.message || 'Terjadi kesalahan pada server.');
                e.errors = err.errors || null;
                throw e;
            }
            return res.json().catch(() => ({}));
        }

        /* ---------- COMBOBOX ---------- */
        const comboRegistry = {};

        function syncCombo(selectId) {
            if (comboRegistry[selectId]) comboRegistry[selectId].sync();
        }

        function initCombo(selectId, opts = {}) {
            const select = document.getElementById(selectId);
            const input = document.getElementById('cari_' + selectId);
            const list = document.getElementById('list_' + selectId);
            if (!select || !input || !list) return;

            const readonly = !!opts.readonly;
            input.readOnly = readonly;
            let aktif = -1;

            const teks = o => o.textContent.replace(/\s+/g, ' ').trim();
            const daftar = () => Array.from(select.options).map(o => ({
                value: o.value,
                label: teks(o)
            }));

            function sync() {
                const o = select.options[select.selectedIndex];
                input.value = o ? teks(o) : '';
            }

            function render(keyword) {
                const k = (keyword || '').toLowerCase().trim();
                const hasil = daftar().filter(d => d.label.toLowerCase().includes(k));
                aktif = -1;
                list.innerHTML = hasil.length ?
                    hasil.map(d =>
                        `<div class="combo-item${d.value === select.value ? ' is-selected' : ''}" data-value="${escapeHtml(d.value)}">${escapeHtml(d.label)}</div>`
                        ).join('') :
                    '<div class="combo-item">Data tidak ditemukan</div>';
            }

            // Posisi daftar mengikuti ruang layar: turun jika muat, naik jika di bawah sempit
            function posisi() {
                const r = input.getBoundingClientRect();
                const celah = 4,
                    margin = 10,
                    maksTinggi = 240;
                const ruangBawah = window.innerHeight - r.bottom - margin;
                const ruangAtas = r.top - margin;
                const tinggiIsi = Math.min(list.scrollHeight, maksTinggi);
                const keBawah = ruangBawah >= tinggiIsi || ruangBawah >= ruangAtas;

                list.style.position = 'fixed';
                list.style.left = r.left + 'px';
                list.style.width = r.width + 'px';
                list.style.right = 'auto';
                if (keBawah) {
                    list.style.top = (r.bottom + celah) + 'px';
                    list.style.bottom = 'auto';
                    list.style.maxHeight = Math.max(80, Math.min(maksTinggi, ruangBawah)) + 'px';
                } else {
                    list.style.top = 'auto';
                    list.style.bottom = (window.innerHeight - r.top + celah) + 'px';
                    list.style.maxHeight = Math.max(80, Math.min(maksTinggi, ruangAtas)) + 'px';
                }
            }

            function buka(keyword) {
                render(keyword);
                list.classList.add('show');
                posisi();
            }

            function tutup() {
                list.classList.remove('show');
                sync();
            }

            const ikutiPosisi = () => {
                if (list.classList.contains('show')) posisi();
            };
            window.addEventListener('resize', ikutiPosisi);
            window.addEventListener('scroll', ikutiPosisi, true); // termasuk scroll di dalam modal

            function pilih(value) {
                select.value = value;
                tutup();
                select.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            }

            input.addEventListener('focus', () => {
                if (!readonly) input.select();
                buka('');
            });
            input.addEventListener('click', () => {
                if (!list.classList.contains('show')) buka('');
            });
            input.addEventListener('input', () => buka(input.value));
            input.addEventListener('blur', tutup);

            list.addEventListener('mousedown', function(e) {
                e.preventDefault();
                const item = e.target.closest('.combo-item');
                if (item && item.dataset.value) pilih(item.dataset.value);
            });

            comboRegistry[selectId] = {
                sync
            };
            sync();
        }

        /* ---------- JADWAL MODAL ---------- */
        const KUOTA_MAKS = 150;
        let opsiPresetAktif = [];

        function resetHintPreset() {
            opsiPresetAktif = [];
            const h = document.getElementById('presetDateHint');
            if (h) {
                h.hidden = true;
                h.innerHTML = '';
            }
            const t = document.getElementById('tanggal_setoran');
            if (t) t.disabled = false;
        }

        function bersihkanErrorJadwal() {
            document.querySelectorAll('#formJadwal .form-error').forEach(el => el.textContent = '');
        }

        function isiFieldError(field, pesan) {
            const el = document.getElementById('err_' + field);
            if (el) el.textContent = pesan;
        }

        function bukaModalTambahJadwal() {
            document.getElementById('formJadwal').reset();
            document.getElementById('jadwalId').value = '';
            document.getElementById('formJadwal').classList.remove('is-edit');
            bersihkanErrorJadwal();
            resetHintPreset();

            const tglHariIni = tanggalHariIni();
            const tglInput = document.getElementById('tanggal_setoran');
            tglInput.value = tglHariIni;
            tglInput.min = tglHariIni; // Tanggal kemarin tidak bisa dipilih

            document.getElementById('status').value = 'dipublikasi';
            document.getElementById('wilayah').value = 'A (Perumahan)';
            document.getElementById('kuota_maksimal').value = KUOTA_MAKS;
            setTimeValue('jam_mulai', '08:00');
            setTimeValue('jam_selesai', '12:00');

            syncCombo('status');
            syncCombo('wilayah');

            document.getElementById('modalJadwalTitleText').textContent = 'Tambah Jadwal Setoran';
            document.getElementById('jadwalSubmitBtn').textContent = 'Simpan Jadwal';
            bukaModal('modalJadwal');
        }

        function editJadwalModal(jadwal) {
            const form = document.getElementById('formJadwal');
            form.reset();
            form.classList.add('is-edit');
            document.getElementById('jadwalId').value = jadwal.id_jadwal;
            bersihkanErrorJadwal();
            resetHintPreset();

            const tglStr = jadwal.tanggal_setoran ? String(jadwal.tanggal_setoran).substring(0, 10) : '';
            const jm = jadwal.jam_mulai ? jadwal.jam_mulai.substring(0, 5) : '08:00';
            const js = jadwal.jam_selesai ? jadwal.jam_selesai.substring(0, 5) : '12:00';
            const kuota = Math.min(KUOTA_MAKS, parseInt(jadwal.kuota_maksimal, 10) || KUOTA_MAKS);

            // Field lain tetap terisi (disembunyikan) agar form valid; hanya status yang dikirim
            document.getElementById('tanggal_setoran').min = '';
            document.getElementById('nama_agenda').value = jadwal.nama_agenda || '';
            document.getElementById('tanggal_setoran').value = tglStr;
            setTimeValue('jam_mulai', jm);
            setTimeValue('jam_selesai', js);
            document.getElementById('wilayah').value = jadwal.wilayah || 'A (Perumahan)';
            document.getElementById('kuota_maksimal').value = kuota;
            document.getElementById('status').value = jadwal.status || 'dipublikasi';
            syncCombo('status');
            syncCombo('wilayah');

            const tgl = tglStr ? new Date(tglStr + 'T00:00:00') : null;
            document.getElementById('editRingkasan').innerHTML = `
                <div class="edit-ringkasan">
                    <strong>${escapeHtml(jadwal.nama_agenda)}</strong>
                    <div class="muted">${tgl ? escapeHtml(labelPanjang(tgl)) : '-'} | ${jm} - ${js} WIB | ${escapeHtml(jadwal.wilayah || 'A (Perumahan)')} | Kuota ${kuota} warga</div>
                    <span class="form-hint">Hanya status yang bisa diubah. Jadwal berstatus Selesai, Dibatalkan, atau Draft tidak tampil di aplikasi warga.</span>
                </div>`;

            document.getElementById('modalJadwalTitleText').textContent = 'Ubah Status Jadwal';
            document.getElementById('jadwalSubmitBtn').textContent = 'Simpan Status';
            bukaModal('modalJadwal');
        }

        function simpanJadwal(event) {
            event.preventDefault();
            bersihkanErrorJadwal();

            const id = document.getElementById('jadwalId').value;
            const btn = document.getElementById('jadwalSubmitBtn');

            // EDIT: hanya status
            if (id) {
                setBusy(btn, true);
                kirimJson(urlJadwalUpdate.replace(':id', id), 'PUT', {
                        status: document.getElementById('status').value
                    })
                    .then(() => {
                        tutupModal('modalJadwal');
                        showToast('Status diperbarui', 'Status jadwal berhasil disimpan.');
                        setTimeout(() => window.location.reload(), 800);
                    })
                    .catch((err) => {
                        showToast('Gagal menyimpan', err.message, 'error');
                        setBusy(btn, false);
                    });
                return false;
            }

            // TAMBAH
            const tglVal = document.getElementById('tanggal_setoran').value;
            const jamMulai = document.getElementById('jam_mulai').value;
            const jamSelesai = document.getElementById('jam_selesai').value;
            const kuota = parseInt(document.getElementById('kuota_maksimal').value, 10);
            const banyakTanggal = opsiPresetAktif.length > 1;

            if (!banyakTanggal && tglVal < tanggalHariIni()) {
                isiFieldError('tanggal_setoran', 'Tanggal sudah lewat.');
                showToast('Kesalahan Tanggal', 'Tidak dapat membuat jadwal untuk tanggal yang sudah lewat.', 'error');
                return false;
            }
            if (menitDariJam(jamSelesai) <= menitDariJam(jamMulai)) {
                isiFieldError('jam_selesai', 'Jam selesai harus lebih akhir dari jam mulai.');
                showToast('Kesalahan Jam', 'Jam selesai harus lebih akhir dari jam mulai.', 'error');
                return false;
            }
            if (!Number.isInteger(kuota) || kuota < 1 || kuota > KUOTA_MAKS) {
                isiFieldError('kuota_maksimal', 'Kuota harus 1 sampai ' + KUOTA_MAKS + ' warga.');
                showToast('Kesalahan Kuota', 'Maksimal kuota harus antara 1 dan ' + KUOTA_MAKS + ' warga.', 'error');
                return false;
            }

            const dasar = {
                nama_agenda: document.getElementById('nama_agenda').value.trim(),
                jam_mulai: jamMulai,
                jam_selesai: jamSelesai,
                status: document.getElementById('status').value,
                wilayah: document.getElementById('wilayah').value,
                kuota_maksimal: kuota,
                keterangan: document.getElementById('keterangan').value.trim(),
            };
            // Preset 2 tanggal = 2 baris jadwal terpisah (masing-masing punya ID sendiri)
            const daftarTanggal = banyakTanggal ? opsiPresetAktif.map(o => o.ymd) : [tglVal];

            setBusy(btn, true);
            (async () => {
                let tersimpan = 0;
                try {
                    for (const t of daftarTanggal) {
                        await kirimJson(urlJadwalStore, 'POST', {
                            ...dasar,
                            tanggal_setoran: t
                        });
                        tersimpan++;
                    }
                    tutupModal('modalJadwal');
                    showToast('Berhasil disimpan', tersimpan > 1 ? tersimpan +
                        ' jadwal setoran baru telah ditambahkan.' : 'Jadwal setoran baru telah ditambahkan.');
                    setTimeout(() => window.location.reload(), 800);
                } catch (err) {
                    if (err.errors) Object.entries(err.errors).forEach(([f, m]) => isiFieldError(f, m[0]));
                    if (tersimpan > 0) {
                        showToast('Sebagian tersimpan', tersimpan + ' dari ' + daftarTanggal.length +
                            ' jadwal tersimpan. ' + err.message, 'error');
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        showToast('Gagal menyimpan', err.message, 'error');
                        setBusy(btn, false);
                    }
                }
            })();

            return false;
        }

        /* ---------- PRESET RUTIN ---------- */
        const HARI_ID = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        function indeksHari(teks) {
            const k = String(teks || '').toLowerCase().replace(/[^a-z]/g, ''); // "Jum'at" -> "jumat"
            return HARI_ID.findIndex(h => h.toLowerCase() === k);
        }

        // Kembalikan teks baku ("Sabtu" / "15") atau null bila tidak valid
        function normalisasiTokenPreset(teks) {
            const t = String(teks || '').trim();
            const idx = indeksHari(t);
            if (idx >= 0) return HARI_ID[idx];
            if (/^\d{1,2}$/.test(t) && parseInt(t, 10) >= 1 && parseInt(t, 10) <= 31) return String(parseInt(t, 10));
            return null;
        }

        function ymd(d) {
            return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
        }

        function labelPanjang(d) {
            return d.toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        }

        function labelPendek(d) {
            return d.toLocaleDateString('id-ID', {
                weekday: 'short',
                day: 'numeric',
                month: 'short'
            });
        }

        // Tanggal terdekat yang akan datang (hari ini masih boleh, kemarin tidak)
        function tanggalBerikutnya(token) {
            const t = normalisasiTokenPreset(token);
            if (!t) return null;
            const hariIni = new Date();
            hariIni.setHours(0, 0, 0, 0);

            const idx = indeksHari(t);
            if (idx >= 0) {
                const d = new Date(hariIni);
                d.setDate(d.getDate() + ((idx - d.getDay() + 7) % 7));
                return d;
            }

            const tgl = parseInt(t, 10);
            for (let i = 0; i < 14; i++) {
                const y = hariIni.getFullYear();
                const m = hariIni.getMonth() + i;
                if (tgl > new Date(y, m + 1, 0).getDate()) continue; // bulan ini tidak punya tanggal tsb (mis. 31)
                const cand = new Date(y, m, tgl);
                if (cand >= hariIni) return cand;
            }
            return null;
        }

        function opsiTanggalPreset(p) {
            const unik = new Map();
            [p.tgl_1, p.tgl_2].filter(Boolean).forEach(tok => {
                const d = tanggalBerikutnya(tok);
                if (d) unik.set(ymd(d), d);
            });
            return Array.from(unik.values()).sort((a, b) => a - b);
        }

        function terapkanPresetRutin(id) {
            const p = presetList.find(x => x.id === id);
            if (!p) return;

            const opsi = opsiTanggalPreset(p);
            if (!opsi.length) {
                showToast('Preset tidak valid',
                    'Tanggal atau hari pada preset ini tidak dikenali. Perbaiki di Kelola Preset.', 'error');
                return;
            }

            bukaModalTambahJadwal();
            opsiPresetAktif = opsi.map(d => ({
                ymd: ymd(d),
                label: labelPendek(d)
            }));
            document.getElementById('nama_agenda').value = p.nama_preset;

            const tgl = document.getElementById('tanggal_setoran');
            tgl.value = opsiPresetAktif[0].ymd;

            if (opsiPresetAktif.length > 1) {
                tgl.disabled = true; // tanggal ditentukan preset
                const hint = document.getElementById('presetDateHint');
                hint.hidden = false;
                hint.innerHTML = 'Akan dibuat <strong>' + opsiPresetAktif.length + ' jadwal terpisah</strong>:' +
                    opsiPresetAktif.map(o => `<span class="date-chip">${escapeHtml(o.label)}</span>`).join('');
                showToast('Preset diterapkan', opsiPresetAktif.length + ' tanggal akan dibuat sebagai baris terpisah.');
            } else {
                showToast('Preset diterapkan', 'Tanggal terisi otomatis: ' + labelPanjang(opsi[0]) + '.');
            }
        }

        function renderPresetUI() {
            const container = document.getElementById('presetButtonsWrap');
            const tbody = document.getElementById('daftarPresetTableBody');
            if (!container) return;

            if (presetList.length === 0) {
                container.innerHTML = '<span class="muted" style="font-size: 12px;">Belum ada preset rutin.</span>';
                if (tbody) tbody.innerHTML =
                    '<tr><td colspan="4" class="text-center muted">Belum ada preset terdaftar</td></tr>';
                return;
            }

            container.innerHTML = presetList.map(p => {
                const next = opsiTanggalPreset(p)[0];
                return `
                <button type="button" class="btn btn--ghost" onclick="terapkanPresetRutin(${Number(p.id)})">
                    🗓️ ${escapeHtml(p.nama_preset)}
                    ${next ? `<span class="preset-next">${escapeHtml(labelPendek(next))}</span>` : ''}
                </button>`;
            }).join('');

            if (tbody) {
                tbody.innerHTML = presetList.map((p, index) => `
                    <tr>
                        <td><strong>${escapeHtml(p.nama_preset)}</strong></td>
                        <td><span class="badge badge--satuan">${escapeHtml(p.tgl_1)}</span></td>
                        <td>${p.tgl_2 ? `<span class="badge badge--satuan">${escapeHtml(p.tgl_2)}</span>` : '<span class="muted">-</span>'}</td>
                        <td class="text-center">
                            <button type="button" class="icon-btn icon-btn--danger" title="Hapus Preset" onclick="konfirmasiHapusPreset(${index})">
                                {!! $iconHapus !!}
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        }

        function bukaModalKelolaPreset() {
            renderPresetUI();
            bukaModal('modalKelolaPreset');
        }

        function simpanPresetRutin(event) {
            event.preventDefault();
            const nama = document.getElementById('preset_nama').value.trim();
            const raw1 = document.getElementById('preset_tgl1').value.trim();
            const raw2 = document.getElementById('preset_tgl2').value.trim();

            const tgl1 = normalisasiTokenPreset(raw1);
            const tgl2 = raw2 ? normalisasiTokenPreset(raw2) : '';

            if (!tgl1 || (raw2 && !tgl2)) {
                showToast('Format tidak valid', 'Isi dengan angka tanggal 1-31 atau nama hari (Senin sampai Minggu).',
                    'error');
                return false;
            }

            presetList.push({
                id: Date.now(),
                nama_preset: nama,
                tgl_1: tgl1,
                tgl_2: tgl2
            });
            simpanPresetStorage();
            document.getElementById('formTambahPreset').reset();
            renderPresetUI();
            showToast('Preset Ditambahkan', 'Preset rutin baru berhasil disimpan.');
            return false;
        }

        function konfirmasiHapusPreset(index) {
            const p = presetList[index];
            document.getElementById('hapusTitle').textContent = 'Hapus Preset Ini?';
            document.getElementById('hapusText').innerHTML =
                `Anda akan menghapus preset <strong>${escapeHtml(p.nama_preset)}</strong>.`;

            const confirmBtn = document.getElementById('hapusConfirmBtn');
            // Ganti event handler tombol konfirmasi modal hapus khusus untuk preset
            confirmBtn.onclick = function() {
                presetList.splice(index, 1);
                simpanPresetStorage();
                renderPresetUI();
                tutupModal('modalHapus');
                showToast('Preset Dihapus', 'Preset berhasil dihapus dari daftar.');
            };

            bukaModal('modalHapus');
        }

        /* ---------- KALENDER (hanya jadwal berstatus dipublikasi) ---------- */
        const NAMA_BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
            'Oktober', 'November', 'Desember'
        ];
        let kalenderTahun = new Date().getFullYear();
        let kalenderBulan = new Date().getMonth();

        function bukaModalKalender() {
            kembaliHariIni();
            bukaModal('modalKalender');
        }

        function kembaliHariIni() {
            const n = new Date();
            kalenderTahun = n.getFullYear();
            kalenderBulan = n.getMonth();
            renderKalender();
        }

        function geserKalender(delta) {
            kalenderBulan += delta;
            if (kalenderBulan > 11) {
                kalenderBulan = 0;
                kalenderTahun++;
            }
            if (kalenderBulan < 0) {
                kalenderBulan = 11;
                kalenderTahun--;
            }
            renderKalender();
        }

        function renderKalender() {
            const matrix = document.getElementById('calendarDaysMatrix');
            document.getElementById('kalenderJudul').textContent = NAMA_BULAN[kalenderBulan] + ' ' + kalenderTahun;

            const perTanggal = {};
            dataJadwal.filter(j => j.status === 'dipublikasi').forEach(j => {
                (perTanggal[j.tanggal] = perTanggal[j.tanggal] || []).push(j);
            });

            const awal = new Date(kalenderTahun, kalenderBulan, 1).getDay();
            const total = new Date(kalenderTahun, kalenderBulan + 1, 0).getDate();
            const hariIni = tanggalHariIni();
            let jumlahBulanIni = 0;
            let html = '';

            for (let i = 0; i < awal; i++) html += '<div class="calendar-day-cell calendar-day-cell--empty"></div>';

            for (let d = 1; d <= total; d++) {
                const key = kalenderTahun + '-' + pad2(kalenderBulan + 1) + '-' + pad2(d);
                const items = (perTanggal[key] || []).slice().sort((a, b) => a.jam_mulai.localeCompare(b.jam_mulai));
                jumlahBulanIni += items.length;

                let cls = 'calendar-day-cell';
                if (key === hariIni) cls += ' calendar-day-cell--today';
                else if (key < hariIni) cls += ' calendar-day-cell--past';

                const events = items.slice(0, 2).map(j => {
                    const tip =
                        `${j.nama_agenda} | ${j.jam_mulai}-${j.jam_selesai} WIB | ${j.wilayah} | Kuota ${j.kuota} warga`;
                    return `<div class="calendar-day-event" title="${escapeHtml(tip)}">${escapeHtml(j.jam_mulai)} ${escapeHtml(j.nama_agenda)}</div>`;
                }).join('');
                const more = items.length > 2 ? `<div class="calendar-day-more">+${items.length - 2} lagi</div>` : '';

                html += `<div class="${cls}"><span class="calendar-day-number">${d}</span>${events}${more}</div>`;
            }

            matrix.innerHTML = html;
            document.getElementById('kalenderKet').textContent = jumlahBulanIni ?
                `Menampilkan ${jumlahBulanIni} jadwal yang sudah dipublikasikan ke warga bulan ini.` :
                'Belum ada jadwal yang dipublikasikan ke warga bulan ini.';
        }

        function hapusJadwal(jadwal) {
            const confirmBtn = document.getElementById('hapusConfirmBtn');
            document.getElementById('hapusTitle').textContent = 'Hapus Jadwal Setoran?';
            document.getElementById('hapusText').innerHTML =
                `Anda akan menghapus agenda <strong>${escapeHtml(jadwal.nama_agenda)}</strong>.`;

            confirmBtn.onclick = function() {
                setBusy(confirmBtn, true);
                kirimJson(urlJadwalHapus.replace(':id', jadwal.id_jadwal), 'DELETE')
                    .then(() => {
                        tutupModal('modalHapus');
                        showToast('Berhasil dihapus', 'Data jadwal setoran telah dihapus.');
                        setTimeout(() => window.location.reload(), 800);
                    })
                    .catch((err) => {
                        showToast('Gagal menghapus', err.message, 'error');
                        setBusy(confirmBtn, false);
                    });
            };

            bukaModal('modalHapus');
        }

        document.addEventListener('DOMContentLoaded', function() {
            initCombo('status');
            initCombo('wilayah');
            initCombo('perPageSelect', {
                readonly: true
            });
            initTimePicker('jam_mulai');
            initTimePicker('jam_selesai');
            renderPresetUI();

            const searchInput = document.getElementById('searchJadwal');
            const table = document.getElementById('jadwalTable');
            const perPageSelect = document.getElementById('perPageSelect');
            const paginationContainer = document.getElementById('paginationButtons');
            const tableInfo = document.getElementById('tableInfoPagination');
            const kosongCari = document.getElementById('jadwalKosongCari');

            let currentPage = 1;
            const groups = [];

            if (table) {
                table.querySelectorAll('tbody tr[data-row-kategori]').forEach(row => {
                    const key = row.dataset.group;
                    let g = groups.find(x => x.key === key);
                    if (!g) {
                        g = {
                            key,
                            rows: []
                        };
                        groups.push(g);
                    }
                    g.rows.push(row);
                });
            }

            function renderTable() {
                if (!table) return;
                const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const filtered = groups.filter(g => g.rows.some(r => r.textContent.toLowerCase().includes(
                keyword)));

                if (kosongCari) kosongCari.style.display = (groups.length > 0 && filtered.length === 0) ? '' :
                    'none';

                const perPage = parseInt(perPageSelect ? perPageSelect.value : 10, 10);
                const totalPages = Math.ceil(filtered.length / perPage) || 1;
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);

                groups.forEach(g => g.rows.forEach(r => r.style.display = 'none'));

                const start = (currentPage - 1) * perPage;
                const end = start + perPage;
                filtered.slice(start, end).forEach(g => g.rows.forEach(r => r.style.display = ''));

                if (tableInfo) {
                    tableInfo.innerHTML = filtered.length === 0 ? 'Tidak ada data' :
                        `Menampilkan <strong>${start + 1}</strong> - <strong>${Math.min(end, filtered.length)}</strong> dari <strong>${filtered.length}</strong>`;
                }

                if (!paginationContainer) return;
                let html =
                    `<button class="btn btn--ghost" type="button" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>‹</button>`;
                for (let i = 1; i <= totalPages; i++) {
                    html +=
                        `<button class="btn ${i === currentPage ? 'btn--primary' : 'btn--ghost'}" type="button" data-page="${i}">${i}</button>`;
                }
                html +=
                    `<button class="btn btn--ghost" type="button" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>›</button>`;
                paginationContainer.innerHTML = html;
            }

            if (paginationContainer) {
                paginationContainer.addEventListener('click', function(e) {
                    const btn = e.target.closest('button[data-page]');
                    if (!btn || btn.disabled) return;
                    currentPage = parseInt(btn.dataset.page, 10);
                    renderTable();
                });
            }

            if (searchInput) searchInput.addEventListener('input', () => {
                currentPage = 1;
                renderTable();
            });
            if (perPageSelect) perPageSelect.addEventListener('change', () => {
                currentPage = 1;
                renderTable();
            });

            renderTable();
        });
    </script>
@endsection
