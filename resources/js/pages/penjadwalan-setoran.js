import { escapeHtml, getCsrfToken } from '../utils/helpers';
import { renderPagination } from '../utils/paginations';

const dataPage = window.__PENJADWALAN_DATA__ || {};
const urlJadwalStore = dataPage.storeUrl || '';
const urlJadwalUpdate = dataPage.updateUrl || '';
const urlJadwalHapus = dataPage.hapusUrl || '';
const dataJadwal = Array.isArray(dataPage.jadwalList) ? dataPage.jadwalList : [];
const iconHapusSvg = dataPage.iconHapus || '';

const KUOTA_MAKS = 150;
const pad2 = n => String(n).padStart(2, '0');

let presetList = JSON.parse(localStorage.getItem('bank_sampah_presets')) || [
    { id: 1, nama_preset: 'Awal & Tengah Bulan', tgl_1: '1', tgl_2: '15' },
    { id: 2, nama_preset: 'Tengah & Akhir Bulan', tgl_1: '14', tgl_2: '28' },
    { id: 3, nama_preset: 'Setoran Sabtu Rutin', tgl_1: 'Sabtu', tgl_2: '' }
];

function simpanPresetStorage() {
    localStorage.setItem('bank_sampah_presets', JSON.stringify(presetList));
}

function tanggalHariIni() {
    const d = new Date();
    return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
}

function setBusy(btn, busy) {
    if (btn) btn.disabled = busy;
}

function bukaModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('active');
}

function tutupModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('active');
}

function penjadwalanShowToast(title, text, type = 'success') {
    const wrap = document.getElementById('penjadwalanToastWrap');
    if (!wrap) return;

    const toast = document.createElement('div');
    toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');
    const iconPath = type === 'error'
        ? '<path d="M18 6 6 18M6 6l12 12"></path>'
        : '<path d="M20 6 9 17l-5-5"></path>';

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
        const jam = Math.floor(d / 60);
        const mnt = d % 60;
        el.textContent = 'Durasi operasional: ' + (jam ? jam + ' jam' : '') + (jam && mnt ? ' ' : '') + (mnt ? mnt + ' menit' : '');
        el.classList.remove('is-error');
    }
}

function initTimePicker(id) {
    const root = document.getElementById('tp_' + id);
    const hidden = document.getElementById(id);
    if (!root || !hidden) return;
    const hEl = root.querySelector('.tp-num[data-part="h"]');
    const mEl = root.querySelector('.tp-num[data-part="m"]');
    const batas = { h: 23, m: 59 };

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
        const h = nilai(hEl);
        const m = nilai(mEl);
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
    const opts = { method, headers };
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
const penjadwalanComboRegistry = {};

function syncPenjadwalanCombo(selectId) {
    if (penjadwalanComboRegistry[selectId]) penjadwalanComboRegistry[selectId].sync();
}

function initPenjadwalanCombo(selectId, opts = {}) {
    const select = document.getElementById(selectId);
    const input = document.getElementById('cari_' + selectId);
    const list = document.getElementById('list_' + selectId);
    if (!select || !input || !list) return;

    const readonly = !!opts.readonly;
    input.readOnly = readonly;

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
        list.innerHTML = hasil.length
            ? hasil.map(d =>
                `<div class="combo-item${d.value === select.value ? ' is-selected' : ''}" data-value="${escapeHtml(d.value)}">${escapeHtml(d.label)}</div>`
            ).join('')
            : '<div class="combo-item">Data tidak ditemukan</div>';
    }

    function posisi() {
        const r = input.getBoundingClientRect();
        const celah = 4;
        const margin = 10;
        const maksTinggi = 240;
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
    window.addEventListener('scroll', ikutiPosisi, true);

    function pilih(value) {
        select.value = value;
        tutup();
        select.dispatchEvent(new Event('change', { bubbles: true }));
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
        if (item && 'value' in item.dataset) pilih(item.dataset.value);
    });

    penjadwalanComboRegistry[selectId] = { sync };
    sync();
}

/* ---------- JADWAL MODAL ---------- */
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
    document.querySelectorAll('#formJadwal .form-error').forEach(el => {
        el.textContent = '';
    });
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
    tglInput.min = tglHariIni;

    document.getElementById('status').value = 'dipublikasi';
    document.getElementById('wilayah').value = 'A (Perumahan)';
    document.getElementById('kuota_maksimal').value = KUOTA_MAKS;
    setTimeValue('jam_mulai', '08:00');
    setTimeValue('jam_selesai', '12:00');

    syncPenjadwalanCombo('status');
    syncPenjadwalanCombo('wilayah');

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

    document.getElementById('tanggal_setoran').min = '';
    document.getElementById('nama_agenda').value = jadwal.nama_agenda || '';
    document.getElementById('tanggal_setoran').value = tglStr;
    setTimeValue('jam_mulai', jm);
    setTimeValue('jam_selesai', js);
    document.getElementById('wilayah').value = jadwal.wilayah || 'A (Perumahan)';
    document.getElementById('kuota_maksimal').value = kuota;
    document.getElementById('status').value = jadwal.status || 'dipublikasi';
    syncPenjadwalanCombo('status');
    syncPenjadwalanCombo('wilayah');

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

    if (id) {
        setBusy(btn, true);
        kirimJson(urlJadwalUpdate.replace(':id', id), 'PUT', {
            status: document.getElementById('status').value
        })
        .then(() => {
            tutupModal('modalJadwal');
            penjadwalanShowToast('Status diperbarui', 'Status jadwal berhasil disimpan.');
            setTimeout(() => window.location.reload(), 800);
        })
        .catch((err) => {
            penjadwalanShowToast('Gagal menyimpan', err.message, 'error');
            setBusy(btn, false);
        });
        return false;
    }

    const tglVal = document.getElementById('tanggal_setoran').value;
    const jamMulai = document.getElementById('jam_mulai').value;
    const jamSelesai = document.getElementById('jam_selesai').value;
    const kuota = parseInt(document.getElementById('kuota_maksimal').value, 10);
    const banyakTanggal = opsiPresetAktif.length > 1;

    if (!banyakTanggal && tglVal < tanggalHariIni()) {
        isiFieldError('tanggal_setoran', 'Tanggal sudah lewat.');
        penjadwalanShowToast('Kesalahan Tanggal', 'Tidak dapat membuat jadwal untuk tanggal yang sudah lewat.', 'error');
        return false;
    }
    if (menitDariJam(jamSelesai) <= menitDariJam(jamMulai)) {
        isiFieldError('jam_selesai', 'Jam selesai harus lebih akhir dari jam mulai.');
        penjadwalanShowToast('Kesalahan Jam', 'Jam selesai harus lebih akhir dari jam mulai.', 'error');
        return false;
    }
    if (!Number.isInteger(kuota) || kuota < 1 || kuota > KUOTA_MAKS) {
        isiFieldError('kuota_maksimal', 'Kuota harus 1 sampai ' + KUOTA_MAKS + ' warga.');
        penjadwalanShowToast('Kesalahan Kuota', 'Maksimal kuota harus antara 1 dan ' + KUOTA_MAKS + ' warga.', 'error');
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
            penjadwalanShowToast(
                'Berhasil disimpan',
                tersimpan > 1 ? tersimpan + ' jadwal setoran baru telah ditambahkan.' : 'Jadwal setoran baru telah ditambahkan.'
            );
            setTimeout(() => window.location.reload(), 800);
        } catch (err) {
            if (err.errors) Object.entries(err.errors).forEach(([f, m]) => isiFieldError(f, m[0]));
            if (tersimpan > 0) {
                penjadwalanShowToast('Sebagian tersimpan', tersimpan + ' dari ' + daftarTanggal.length + ' jadwal tersimpan. ' + err.message, 'error');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                penjadwalanShowToast('Gagal menyimpan', err.message, 'error');
                setBusy(btn, false);
            }
        }
    })();

    return false;
}

/* ---------- PRESET RUTIN ---------- */
const HARI_ID = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

function indeksHari(teks) {
    const k = String(teks || '').toLowerCase().replace(/[^a-z]/g, '');
    return HARI_ID.findIndex(h => h.toLowerCase() === k);
}

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
        if (tgl > new Date(y, m + 1, 0).getDate()) continue;
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
        penjadwalanShowToast('Preset tidak valid', 'Tanggal atau hari pada preset ini tidak dikenali. Perbaiki di Kelola Preset.', 'error');
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
        tgl.disabled = true;
        const hint = document.getElementById('presetDateHint');
        hint.hidden = false;
        hint.innerHTML = 'Akan dibuat <strong>' + opsiPresetAktif.length + ' jadwal terpisah</strong>:' +
            opsiPresetAktif.map(o => `<span class="date-chip">${escapeHtml(o.label)}</span>`).join('');
        penjadwalanShowToast('Preset diterapkan', opsiPresetAktif.length + ' tanggal akan dibuat sebagai baris terpisah.');
    } else {
        penjadwalanShowToast('Preset diterapkan', 'Tanggal terisi otomatis: ' + labelPanjang(opsi[0]) + '.');
    }
}

function renderPresetUI() {
    const container = document.getElementById('presetButtonsWrap');
    const tbody = document.getElementById('daftarPresetTableBody');
    if (!container) return;

    if (presetList.length === 0) {
        container.innerHTML = '<span class="muted" style="font-size: 12px;">Belum ada preset rutin.</span>';
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center muted">Belum ada preset terdaftar</td></tr>';
        }
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
                        ${iconHapusSvg}
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
        penjadwalanShowToast('Format tidak valid', 'Isi dengan angka tanggal 1-31 atau nama hari (Senin sampai Minggu).', 'error');
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
    penjadwalanShowToast('Preset Ditambahkan', 'Preset rutin baru berhasil disimpan.');
    return false;
}

function konfirmasiHapusPreset(index) {
    const p = presetList[index];
    document.getElementById('hapusTitle').textContent = 'Hapus Preset Ini?';
    document.getElementById('hapusText').innerHTML = `Anda akan menghapus preset <strong>${escapeHtml(p.nama_preset)}</strong>.`;

    const confirmBtn = document.getElementById('hapusConfirmBtn');
    confirmBtn.onclick = function() {
        presetList.splice(index, 1);
        simpanPresetStorage();
        renderPresetUI();
        tutupModal('modalHapus');
        penjadwalanShowToast('Preset Dihapus', 'Preset berhasil dihapus dari daftar.');
    };

    bukaModal('modalHapus');
}

/* ---------- KALENDER ---------- */
const NAMA_BULAN = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
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

    for (let i = 0; i < awal; i++) {
        html += '<div class="calendar-day-cell calendar-day-cell--empty"></div>';
    }

    for (let d = 1; d <= total; d++) {
        const key = kalenderTahun + '-' + pad2(kalenderBulan + 1) + '-' + pad2(d);
        const items = (perTanggal[key] || []).slice().sort((a, b) => a.jam_mulai.localeCompare(b.jam_mulai));
        jumlahBulanIni += items.length;

        let cls = 'calendar-day-cell';
        if (key === hariIni) cls += ' calendar-day-cell--today';
        else if (key < hariIni) cls += ' calendar-day-cell--past';

        const events = items.slice(0, 2).map(j => {
            const tip = `${j.nama_agenda} | ${j.jam_mulai}-${j.jam_selesai} WIB | ${j.wilayah} | Kuota ${j.kuota} warga`;
            return `<div class="calendar-day-event" title="${escapeHtml(tip)}">${escapeHtml(j.jam_mulai)} ${escapeHtml(j.nama_agenda)}</div>`;
        }).join('');
        const more = items.length > 2 ? `<div class="calendar-day-more">+${items.length - 2} lagi</div>` : '';

        html += `<div class="${cls}"><span class="calendar-day-number">${d}</span>${events}${more}</div>`;
    }

    matrix.innerHTML = html;
    document.getElementById('kalenderKet').textContent = jumlahBulanIni
        ? `Menampilkan ${jumlahBulanIni} jadwal yang sudah dipublikasikan ke warga bulan ini.`
        : 'Belum ada jadwal yang dipublikasikan ke warga bulan ini.';
}

function hapusJadwal(jadwal) {
    const confirmBtn = document.getElementById('hapusConfirmBtn');
    document.getElementById('hapusTitle').textContent = 'Hapus Jadwal Setoran?';
    document.getElementById('hapusText').innerHTML = `Anda akan menghapus agenda <strong>${escapeHtml(jadwal.nama_agenda)}</strong>.`;

    confirmBtn.onclick = function() {
        setBusy(confirmBtn, true);
        kirimJson(urlJadwalHapus.replace(':id', jadwal.id_jadwal), 'DELETE')
            .then(() => {
                tutupModal('modalHapus');
                penjadwalanShowToast('Berhasil dihapus', 'Data jadwal setoran telah dihapus.');
                setTimeout(() => window.location.reload(), 800);
            })
            .catch((err) => {
                penjadwalanShowToast('Gagal menghapus', err.message, 'error');
                setBusy(confirmBtn, false);
            });
    };

    bukaModal('modalHapus');
}

document.addEventListener('DOMContentLoaded', function() {
    initPenjadwalanCombo('status');
    initPenjadwalanCombo('wilayah');
    initPenjadwalanCombo('perPageSelect', { readonly: true });
    initTimePicker('jam_mulai');
    initTimePicker('jam_selesai');
    renderPresetUI();

    const searchInput = document.getElementById('searchJadwal');
    const table = document.getElementById('jadwalTable');
    const perPageSelect = document.getElementById('perPageSelect');
    const kosongCari = document.getElementById('jadwalKosongCari');

    let currentPage = 1;
    const groups = [];

    if (table) {
        table.querySelectorAll('tbody tr[data-row-kategori]').forEach(row => {
            const key = row.dataset.group;
            let g = groups.find(x => x.key === key);
            if (!g) {
                g = { key, rows: [] };
                groups.push(g);
            }
            g.rows.push(row);
        });
    }

    function renderTable() {
        if (!table) return;
        const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const filtered = groups.filter(g => g.rows.some(r => r.textContent.toLowerCase().includes(keyword)));

        if (kosongCari) {
            kosongCari.style.display = (groups.length > 0 && filtered.length === 0) ? '' : 'none';
        }

        const perPage = parseInt(perPageSelect ? perPageSelect.value : 10, 10);
        const totalPages = Math.ceil(filtered.length / perPage) || 1;
        currentPage = Math.min(Math.max(currentPage, 1), totalPages);

        groups.forEach(g => g.rows.forEach(r => {
            r.style.display = 'none';
        }));

        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        filtered.slice(start, end).forEach(g => g.rows.forEach(r => {
            r.style.display = '';
        }));

        renderPagination({
            infoId: 'tableInfoPagination',
            buttonId: 'paginationButtons',
            currentPage: currentPage,
            totalPages: totalPages,
            totalItems: filtered.length,
            perPage: perPage,
            unit: 'jadwal',
            onPageChange: (p) => {
                currentPage = p;
                renderTable();
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            currentPage = 1;
            renderTable();
        });
    }
    if (perPageSelect) {
        perPageSelect.addEventListener('change', () => {
            currentPage = 1;
            renderTable();
        });
    }

    renderTable();
});

Object.assign(window, {
    bukaModal,
    tutupModal,
    bukaModalTambahJadwal,
    editJadwalModal,
    simpanJadwal,
    terapkanPresetRutin,
    bukaModalKelolaPreset,
    simpanPresetRutin,
    konfirmasiHapusPreset,
    bukaModalKalender,
    kembaliHariIni,
    geserKalender,
    hapusJadwal
});
