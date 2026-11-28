import { escapeHtml, getCsrfToken } from '../utils/helpers';
import { renderPagination } from '../utils/paginations';

const pageData = window.__WARGA_DATA__ || {};

const DAFTAR_BANK_EWALLET = [
    'Bank BCA',
    'Bank BRI',
    'Bank Mandiri',
    'Bank BNI',
    'Bank BSI',
    'GoPay',
    'OVO',
    'DANA',
    'ShopeePay',
    'LinkAja',
];

let wargaAkanDihapus = null;
const comboSelectRegistry = {};

function showToast(title, text, type = 'success') {
    const wrap = document.getElementById('toastWrap');
    if (!wrap) return;

    const toast = document.createElement('div');
    toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');

    const iconPath =
        type === 'error'
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

    function hapusToast() {
        toast.classList.add('toast--leaving');
        setTimeout(() => toast.remove(), 180);
    }

    toast.querySelector('.toast-close').addEventListener('click', hapusToast);
    wrap.appendChild(toast);
    setTimeout(hapusToast, 3500);
}

/* ---------- RESET COMBO (SELECT) ---------- */
function resetComboBank(selectId) {
    const select = document.getElementById(selectId);
    if (select) select.value = '';
    if (comboSelectRegistry[selectId]) comboSelectRegistry[selectId]();
}

function bukaModal(id) {
    if (id === 'modalTambah') {
        const form = document.getElementById('formTambahWarga');
        if (form) form.reset();
        resetComboBank('tambahBank');
    }
    const el = document.getElementById(id);
    if (el) el.classList.add('active');
}

function tutupModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('active');
}

function bukaModalLeaderboard() {
    loadLeaderboardPopup('season_ini');
}

function loadLeaderboardPopup(periode) {
    const url = pageData.leaderboardPopupUrl || '/admin/pages/warga/leaderboard-popup';
    fetch(`${url}?periode=${encodeURIComponent(periode)}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
    })
        .then(async (res) => {
            if (!res.ok) {
                const errJson = await res.json().catch(() => ({}));
                throw new Error(errJson.message || 'Gagal memuat dari server');
            }
            return res.json();
        })
        .then((data) => {
            const content = document.getElementById('modalLeaderboardContent');
            const modal = document.getElementById('modalLeaderboard');
            if (content) content.innerHTML = data.html;
            if (modal) modal.classList.add('active');
        })
        .catch((err) => {
            console.error(err);
            showToast('Gagal', 'Gagal memuat data leaderboard.', 'error');
        });
}

function formatRupiah(angka) {
    return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
}

function formatTanggal(tgl) {
    if (!tgl) return '-';
    const d = new Date(tgl);
    if (isNaN(d)) return tgl;
    return d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

function formatStatusRekening(status) {
    return status === 'verified' ? 'Terverifikasi' : 'Belum Verifikasi';
}

function formatKelompokWarga(warga) {
    const raw =
        warga?.kelompok ??
        warga?.kelompok_domisili ??
        warga?.nama_kelompok ??
        '';
    const nilai = String(raw).trim().toUpperCase();

    if (!nilai) return '-';
    if (nilai === 'A' || nilai.startsWith('A ') || nilai === 'KELOMPOK A' || nilai.includes('PERUMAHAN') && !nilai.includes('LUAR')) {
        return 'A (Perumahan)';
    }
    if (nilai === 'B' || nilai.startsWith('B ') || nilai === 'KELOMPOK B' || nilai.includes('LUAR PERUMAHAN')) {
        return 'B (Luar Perumahan)';
    }

    return String(raw).trim();
}

/* ---------- COMBO SELECT (DIPAKAI KELOMPOK, BANK, FILTER, PER-PAGE) ---------- */
function initComboSelect(selectId) {
    const select = document.getElementById(selectId);
    const input = document.getElementById('cari_' + selectId);
    const list = document.getElementById('list_' + selectId);
    if (!select || !input || !list) return;

    input.readOnly = true;
    let aktif = -1;

    const teks = (o) => o.textContent.replace(/\s+/g, ' ').trim();

    function sync() {
        const o = select.options[select.selectedIndex];
        input.value = o ? teks(o) : '';
    }

    function buka() {
        list.innerHTML = Array.from(select.options)
            .map(
                (o) =>
                    `<div class="combo-item${o.value === select.value ? ' is-selected' : ''}" data-value="${escapeHtml(o.value)}">${escapeHtml(teks(o))}</div>`,
            )
            .join('');
        aktif = -1;
        list.classList.add('show');
    }

    function tutup() {
        list.classList.remove('show');
    }

    function pilih(value) {
        const berubah = select.value !== String(value);
        select.value = value;
        sync();
        tutup();
        if (berubah) select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function sorot(arah) {
        const items = list.querySelectorAll('.combo-item');
        if (!items.length) return;
        aktif = (aktif + arah + items.length) % items.length;
        items.forEach((el, i) => el.classList.toggle('is-active', i === aktif));
    }

    input.addEventListener('focus', buka);
    input.addEventListener('click', () => {
        if (!list.classList.contains('show')) buka();
    });

    input.addEventListener('keydown', function (e) {
        const items = list.querySelectorAll('.combo-item');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!list.classList.contains('show')) buka();
            sorot(1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            sorot(-1);
        } else if (e.key === 'Enter') {
            if (list.classList.contains('show') && aktif > -1 && items[aktif]) {
                e.preventDefault();
                pilih(items[aktif].dataset.value);
            }
        } else if (e.key === 'Escape' && list.classList.contains('show')) {
            e.stopPropagation();
            tutup();
        }
    });

    list.addEventListener('mouseover', function (e) {
        const item = e.target.closest('.combo-item');
        if (!item) return;
        list.querySelectorAll('.combo-item').forEach((el, i) => {
            el.classList.toggle('is-active', el === item);
            if (el === item) aktif = i;
        });
    });

    list.addEventListener('mousedown', function (e) {
        e.preventDefault();
        const item = e.target.closest('.combo-item');
        if (item) pilih(item.dataset.value);
    });

    document.addEventListener('mousedown', function (e) {
        const wrap = input.closest('.combo');
        if (wrap && !wrap.contains(e.target)) tutup();
    });

    comboSelectRegistry[selectId] = sync;
    sync();
}

/* ---------- BANK: ISI OPSI DARI DAFTAR, LALU PAKAI COMBO SELECT ---------- */
function initComboBankSelect(selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;

    select.innerHTML =
        '<option value="">Pilih Bank / E-Wallet...</option>' +
        DAFTAR_BANK_EWALLET.map(
            (b) => `<option value="${escapeHtml(b)}">${escapeHtml(b)}</option>`,
        ).join('');

    initComboSelect(selectId);
}

/* ---------- SIMPAN TAMBAH WARGA ---------- */
function simpanTambahWarga(event) {
    event.preventDefault();
    const emailInput = document.getElementById('tambahEmail').value.trim();
    if (!emailInput) {
        showToast(
            'Peringatan',
            'Email wajib diisi untuk melakukan verifikasi/pendaftaran akun.',
            'error',
        );
        return false;
    }

    const payload = {
        nik: document.getElementById('tambahNik').value,
        nama: document.getElementById('tambahNama').value,
        email: emailInput,
        password: document.getElementById('tambahPassword').value,
        no_hp: document.getElementById('tambahHp').value,
        kelompok: document.getElementById('tambahKelompok').value,
        jumlah_anggota_keluarga: document.getElementById('tambahKeluarga').value,
        alamat: document.getElementById('tambahAlamat').value,
        nama_bank_ewallet: document.getElementById('tambahBank').value,
        nomor_rekening: document.getElementById('tambahNoRekening').value,
        nama_pemilik_rekening: document.getElementById('tambahPemilikRekening').value,
    };

    const storeUrl = pageData.storeUrl || '/admin/pages/warga';
    fetch(storeUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify(payload),
    })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                let errorMsg = data.message || 'Gagal menyimpan data warga.';
                if (data.errors) {
                    const firstKey = Object.keys(data.errors)[0];
                    if (firstKey && data.errors[firstKey][0]) {
                        errorMsg = data.errors[firstKey][0];
                    }
                }
                throw new Error(errorMsg);
            }
            return data;
        })
        .then(() => {
            tutupModal('modalTambah');
            document.getElementById('formTambahWarga').reset();
            resetComboBank('tambahBank');
            showToast('Berhasil', 'Data warga baru telah ditambahkan.');
            setTimeout(() => window.location.reload(), 800);
        })
        .catch((err) => {
            showToast('Gagal menyimpan', err.message, 'error');
        });

    return false;
}

/* ---------- DETAIL / EDIT / HAPUS ---------- */
function lihatWarga(warga) {
    const nama = warga.nama || '-';
    document.getElementById('detailAvatar').textContent = nama.trim().charAt(0).toUpperCase();
    document.getElementById('detailNama').textContent = nama;
    document.getElementById('detailNik').textContent = warga.nik || '-';

    const elKelompok = document.getElementById('detailKelompok');
    if (elKelompok) {
        elKelompok.textContent = formatKelompokWarga(warga);
    }

    document.getElementById('detailHp').textContent = warga.no_hp || '-';
    document.getElementById('detailKeluarga').textContent =
        (warga.jumlah_anggota_keluarga ?? 0) + ' orang';
    document.getElementById('detailSaldo').textContent = formatRupiah(warga.saldo);
    document.getElementById('detailTanggal').textContent = formatTanggal(warga.tanggal_daftar);
    document.getElementById('detailAlamat').textContent = warga.alamat || '-';
    document.getElementById('detailBank').textContent = warga.nama_bank_ewallet || '-';
    document.getElementById('detailNoRekening').textContent = warga.nomor_rekening || '-';
    document.getElementById('detailPemilikRekening').textContent =
        warga.nama_pemilik_rekening || '-';
    document.getElementById('detailStatusRekening').textContent = warga.nama_bank_ewallet
        ? formatStatusRekening(warga.status_rekening)
        : '-';

    bukaModal('modalDetail');
}

function setSegStatus(value) {
    document.getElementById('editStatusRekening').value = value;
    document.querySelectorAll('#segStatusRekening .seg-btn').forEach(function (b) {
        b.classList.toggle('is-active', b.dataset.value === value);
    });
}

function initSegStatusRekening() {
    const seg = document.getElementById('segStatusRekening');
    if (!seg) return;
    seg.addEventListener('click', function (e) {
        const btn = e.target.closest('.seg-btn');
        if (btn) setSegStatus(btn.dataset.value);
    });
}

function editWarga(warga) {
    document.getElementById('editId').value = warga.id_warga;
    setSegStatus(warga.status_rekening === 'verified' ? 'verified' : 'unverified');

    const adaRekening = !!warga.nama_bank_ewallet;
    const baris = (label, val) =>
        `<div class="er-row"><span class="er-label">${escapeHtml(label)}</span><span class="er-value">${escapeHtml(val || '-')}</span></div>`;

    document.getElementById('editRingkasan').innerHTML =
        `<div class="er-nama">${escapeHtml(warga.nama || '-')}</div>` +
        baris('Bank / E-Wallet', warga.nama_bank_ewallet) +
        baris('No. Rekening', warga.nomor_rekening) +
        baris('Pemilik', warga.nama_pemilik_rekening) +
        (adaRekening
            ? ''
            : '<div class="er-note">Warga ini belum mengisi data rekening, status belum bisa diubah.</div>');

    document.getElementById('editSubmitBtn').disabled = !adaRekening;
    bukaModal('modalEdit');
}

function simpanWarga(event) {
    event.preventDefault();
    const id = document.getElementById('editId').value;
    const payload = {
        status_rekening: document.getElementById('editStatusRekening').value,
    };

    fetch(`/admin/pages/warga/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify(payload),
    })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                let errorMsg = data.message || 'Gagal memperbarui status rekening.';
                if (data.errors) {
                    const firstKey = Object.keys(data.errors)[0];
                    if (firstKey && data.errors[firstKey][0]) {
                        errorMsg = data.errors[firstKey][0];
                    }
                }
                throw new Error(errorMsg);
            }
            return data;
        })
        .then(() => {
            tutupModal('modalEdit');
            showToast('Berhasil disimpan', 'Status rekening telah diperbarui.');
            setTimeout(() => window.location.reload(), 800);
        })
        .catch((err) => {
            showToast('Gagal Menyimpan', err.message, 'error');
        });

    return false;
}

function hapusWarga(warga) {
    wargaAkanDihapus = warga;
    const isNonaktif = (warga.status || 'aktif') === 'tidak_aktif';

    const titleEl = document.querySelector('#modalHapus .confirm-title');
    const textEl = document.querySelector('#modalHapus .confirm-text');
    const btnEl = document.querySelector('#modalHapus .btn--danger');

    document.getElementById('hapusNama').textContent = warga.nama || '-';
    document.getElementById('hapusNik').textContent = warga.nik || '-';

    if (titleEl) {
        titleEl.textContent = isNonaktif ? 'Aktifkan Kembali Warga?' : 'Nonaktifkan Data Warga?';
    }
    if (textEl) {
        textEl.innerHTML = isNonaktif
            ? `Anda akan mengaktifkan kembali data <strong id="hapusNama">${escapeHtml(warga.nama || '-')}</strong> (NIK: <span class="mono" id="hapusNik">${escapeHtml(warga.nik || '-')}</span>).`
            : `Anda akan mengubah status <strong id="hapusNama">${escapeHtml(warga.nama || '-')}</strong> (NIK: <span class="mono" id="hapusNik">${escapeHtml(warga.nik || '-')}</span>) menjadi <strong>Tidak Aktif</strong>. Riwayat setoran tetap disimpan.`;
    }
    if (btnEl) {
        btnEl.textContent = isNonaktif ? 'Ya, Aktifkan' : 'Ya, Nonaktifkan';
    }

    bukaModal('modalHapus');
}

function konfirmasiHapus() {
    if (!wargaAkanDihapus) return;

    const id = wargaAkanDihapus.id_warga;
    const namaWarga = wargaAkanDihapus.nama || 'Warga';
    const sedangNonaktif = (wargaAkanDihapus.status || 'aktif') === 'tidak_aktif';

    fetch(`/admin/pages/warga/${id}`, {
        method: 'DELETE',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
    })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(data.message || 'Gagal memperbarui status warga.');
            }
            return data;
        })
        .then((data) => {
            tutupModal('modalHapus');
            const pesan =
                data.message ||
                (sedangNonaktif
                    ? `${namaWarga} telah diaktifkan kembali.`
                    : `${namaWarga} telah dinonaktifkan.`);
            showToast('Berhasil', pesan);
            wargaAkanDihapus = null;
            setTimeout(() => window.location.reload(), 800);
        })
        .catch((err) => {
            showToast('Gagal', err.message, 'error');
        });
}

/* ---------- INIT ---------- */
document.addEventListener('DOMContentLoaded', function () {
    if (pageData.flashSuccess) {
        showToast('Berhasil', pageData.flashSuccess);
    }

    const searchInput = document.getElementById('searchWarga');
    const filterStatusWarga = document.getElementById('filterStatusWarga');
    const filterRekening = document.getElementById('filterRekening');
    const table = document.getElementById('wargaTable');
    const perPageSelect = document.getElementById('perPageSelect');

    let currentPage = 1;
    const allRows = table ? Array.from(table.querySelectorAll('tbody tr[data-status]')) : [];

    function applyFilters() {
        if (!table) return;

        const keyword = (searchInput?.value || '').toLowerCase().trim();
        const statusWargaFilter = filterStatusWarga ? filterStatusWarga.value : 'all';
        const statusRekFilter = filterRekening ? filterRekening.value : 'all';

        const filtered = allRows.filter(function (row) {
            const matchSearch = !keyword || row.textContent.toLowerCase().includes(keyword);
            const matchStatusWarga = statusWargaFilter === 'all' || row.dataset.wargaStatus === statusWargaFilter;
            const matchStatusRek = statusRekFilter === 'all' || row.dataset.status === statusRekFilter;

            return matchSearch && matchStatusWarga && matchStatusRek;
        });

        const perPage = parseInt(perPageSelect ? perPageSelect.value : 10, 10);
        const totalPages = Math.ceil(filtered.length / perPage) || 1;
        currentPage = Math.min(Math.max(currentPage, 1), totalPages);

        allRows.forEach((r) => {
            r.style.display = 'none';
        });

        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        filtered.slice(start, end).forEach(function (r, i) {
            r.style.display = '';
            const no = r.querySelector('.no-urut');
            if (no) no.textContent = start + i + 1;
        });

        renderPagination({
            infoId: 'tableInfoPagination',
            buttonId: 'paginationButtons',
            currentPage: currentPage,
            totalPages: totalPages,
            totalItems: filtered.length,
            perPage: perPage,
            unit: 'warga',
            onPageChange: (p) => {
                currentPage = p;
                applyFilters();
            },
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentPage = 1;
            applyFilters();
        });
    }

    if (filterStatusWarga) {
        filterStatusWarga.addEventListener('change', function () {
            currentPage = 1;
            applyFilters();
        });
    }

    if (filterRekening) {
        filterRekening.addEventListener('change', function () {
            currentPage = 1;
            applyFilters();
        });
    }

    if (perPageSelect) {
        perPageSelect.addEventListener('change', function () {
            currentPage = 1;
            applyFilters();
        });
    }

    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.classList.remove('active');
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(function (overlay) {
                overlay.classList.remove('active');
            });
        }
    });

    initSegStatusRekening();

    initComboSelect('perPageSelect');
    initComboSelect('filterStatusWarga');
    initComboSelect('filterRekening');
    initComboSelect('tambahKelompok');
    initComboBankSelect('tambahBank');

    applyFilters();
});

Object.assign(window, {
    bukaModal,
    tutupModal,
    bukaModalLeaderboard,
    loadLeaderboardPopup,
    simpanTambahWarga,
    lihatWarga,
    editWarga,
    simpanWarga,
    hapusWarga,
    konfirmasiHapus,
});
