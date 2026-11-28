import { escapeHtml, getCsrfToken } from '../utils/helpers';
import { renderPagination } from '../utils/paginations';

const dataPencairan = window.__PENCAIRAN_DATA__ || {};
const storeUrl = dataPencairan.storeUrl || '';
const baseUrl = dataPencairan.baseUrl || '';

let activeInvoiceItem = null;
let pencairanPage = 1;
const pencairanComboRegistry = {};

function formatRupiah(angka) {
    return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
}

function formatStatusRekening(status) {
    return status === 'verified' ? 'Terverifikasi' : 'Belum Verifikasi';
}

function pencairanShowToast(title, text, type = 'success') {
    const wrap = document.getElementById('pencairanToastWrap');
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

    toast.querySelector('.toast-close').addEventListener('click', () => toast.remove());
    wrap.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

function posisikanDropdownModal(input, list) {
    if (!input.closest('.modal-box')) return;

    const rect = input.getBoundingClientRect();
    const ruangBawahViewport = Math.max(80, window.innerHeight - rect.bottom - 12);

    list.style.top = `${rect.bottom + 4}px`;
    list.style.left = `${rect.left}px`;
    list.style.width = `${rect.width}px`;
    list.style.maxHeight = `${Math.min(200, ruangBawahViewport)}px`;
}

function syncPencairanCombo(selectId) {
    if (pencairanComboRegistry[selectId]) pencairanComboRegistry[selectId]();
}

function initPencairanSelectCombo(selectId, onChange) {
    const select = document.getElementById(selectId);
    const input = document.getElementById('cari_' + selectId);
    const list = document.getElementById('list_' + selectId);
    if (!select || !input || !list) return;

    input.readOnly = true;
    let aktif = -1;

    const teks = o => o.textContent.replace(/\s+/g, ' ').trim();

    const kosong = Array.from(select.options).find(o => o.value === '');
    if (kosong) input.placeholder = teks(kosong);

    function sync() {
        const o = select.options[select.selectedIndex];
        input.value = (o && o.value !== '') ? teks(o) : '';
    }

    function buka() {
        list.innerHTML = Array.from(select.options).filter(o => o.value !== '').map(o =>
            `<div class="combo-item${o.value === select.value ? ' is-selected' : ''}" data-value="${escapeHtml(o.value)}">${escapeHtml(teks(o))}</div>`
        ).join('');
        aktif = -1;
        list.classList.add('show');
        posisikanDropdownModal(input, list);
    }

    function tutup() {
        list.classList.remove('show');
    }

    function pilih(value) {
        const berubah = select.value !== String(value);
        select.value = value;
        sync();
        tutup();
        if (berubah && typeof onChange === 'function') onChange();
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
    input.addEventListener('blur', tutup);

    input.addEventListener('keydown', function(e) {
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

    list.addEventListener('mouseover', function(e) {
        const item = e.target.closest('.combo-item');
        if (!item) return;
        list.querySelectorAll('.combo-item').forEach((el, i) => {
            el.classList.toggle('is-active', el === item);
            if (el === item) aktif = i;
        });
    });

    list.addEventListener('mousedown', function(e) {
        e.preventDefault();
        const item = e.target.closest('.combo-item');
        if (item) pilih(item.dataset.value);
    });

    pencairanComboRegistry[selectId] = sync;
    sync();
}

function initComboWarga() {
    const select = document.getElementById('tambahWarga');
    const input = document.getElementById('cariWarga');
    const list = document.getElementById('listWarga');
    const info = document.getElementById('tambahSaldoInfo');
    const rekeningInfo = document.getElementById('tambahRekeningInfo');

    if (!select || !input || !list) return;

    const data = Array.from(select.options)
        .filter(o => o.value !== '')
        .map(o => ({
            value: o.value,
            label: o.textContent.replace(/\s+/g, ' ').trim(),
            saldo: Number(o.dataset.saldo || 0),
            bank: o.dataset.bank || '',
            rekening: o.dataset.rekening || '',
            statusRekening: o.dataset.statusRekening || ''
        }));

    let aktif = -1;

    function renderList(keyword) {
        const k = (keyword || '').toLowerCase().trim();
        const hasil = data.filter(d => d.label.toLowerCase().includes(k));
        aktif = -1;

        list.innerHTML = hasil.length
            ? hasil.map(d => `<div class="combo-item" data-value="${escapeHtml(d.value)}">${escapeHtml(d.label)}</div>`).join('')
            : '<div class="combo-empty">Warga tidak ditemukan</div>';
    }

    function buka(keyword) {
        renderList(keyword);
        list.classList.add('show');
        posisikanDropdownModal(input, list);
    }

    function pilih(value) {
        const d = data.find(x => x.value === String(value));
        if (!d) return;

        select.value = d.value;
        input.value = d.label;
        if (info) info.textContent = formatRupiah(d.saldo);

        if (rekeningInfo) {
            rekeningInfo.textContent = d.bank
                ? `${d.bank} - ${d.rekening || '-'}`
                : 'Tunai / Lainnya';
        }
        list.classList.remove('show');
    }

    function sorot(arah) {
        const items = list.querySelectorAll('.combo-item');
        if (!items.length) return;
        aktif = (aktif + arah + items.length) % items.length;
        items.forEach((el, i) => el.classList.toggle('is-active', i === aktif));
        items[aktif].scrollIntoView({ block: 'nearest' });
    }

    function tutup() {
        list.classList.remove('show');
        const d = data.find(x => x.value === String(select.value));
        input.value = d ? d.label : '';
    }

    input.addEventListener('focus', function() {
        this.select();
        buka('');
    });

    input.addEventListener('input', function() {
        buka(this.value);
    });

    input.addEventListener('blur', tutup);

    input.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!list.classList.contains('show')) {
                buka(this.value);
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
        } else if (e.key === 'Escape' && list.classList.contains('show')) {
            e.stopPropagation();
            tutup();
        }
    });

    list.addEventListener('mouseover', function(e) {
        const item = e.target.closest('.combo-item');
        if (!item) return;
        list.querySelectorAll('.combo-item').forEach((el, i) => {
            el.classList.toggle('is-active', el === item);
            if (el === item) aktif = i;
        });
    });

    list.addEventListener('mousedown', function(e) {
        e.preventDefault();
        const item = e.target.closest('.combo-item');
        if (item) pilih(item.dataset.value);
    });
}

function resetComboWarga() {
    const select = document.getElementById('tambahWarga');
    const input = document.getElementById('cariWarga');
    const list = document.getElementById('listWarga');
    const info = document.getElementById('tambahSaldoInfo');
    const rekeningInfo = document.getElementById('tambahRekeningInfo');

    if (select) select.value = '';
    if (input) input.value = '';
    if (list) list.classList.remove('show');
    if (info) info.textContent = '-';
    if (rekeningInfo) rekeningInfo.textContent = '-';
}

function bukaModal(id) {
    if (id === 'modalTambah') {
        const form = document.getElementById('formTambahPencairan');
        if (form) form.reset();
        resetComboWarga();
        syncPencairanCombo('tambahMetode');

        const inputTanggal = document.getElementById('tambahTanggal');
        if (inputTanggal) {
            const now = new Date();
            const localDate = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().split('T')[0];
            inputTanggal.value = localDate;
        }
    }

    const modal = document.getElementById(id);
    if (modal) modal.classList.add('active');
}

function tutupModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
}

function simpanTambah(event) {
    event.preventDefault();
    const selectWarga = document.getElementById('tambahWarga');
    const idWarga = selectWarga.value;

    if (!idWarga) {
        pencairanShowToast('Gagal menyimpan', 'Silakan pilih warga terlebih dahulu.', 'error');
        document.getElementById('cariWarga')?.focus();
        return false;
    }

    const selectedOption = selectWarga.options[selectWarga.selectedIndex];
    const saldoTersedia = Number(selectedOption.dataset.saldo || 0);
    const jumlahDitarik = Number(document.getElementById('tambahJumlah').value);

    if (jumlahDitarik > saldoTersedia) {
        pencairanShowToast('Gagal menyimpan', 'Jumlah penarikan melebihi saldo tersedia warga!', 'error');
        document.getElementById('tambahJumlah')?.focus();
        return false;
    }

    if (!document.getElementById('tambahMetode').value) {
        pencairanShowToast('Gagal menyimpan', 'Silakan pilih metode pencairan.', 'error');
        document.getElementById('cari_tambahMetode')?.focus();
        return false;
    }

    const payload = {
        id_warga: idWarga,
        jumlah: jumlahDitarik,
        metode_transfer: document.getElementById('tambahMetode').value,
        tanggal_pencairan: document.getElementById('tambahTanggal').value,
    };

    fetch(storeUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
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
        pencairanShowToast('Berhasil', 'Permohonan pencairan telah dibuat.');
        setTimeout(() => window.location.reload(), 800);
    })
    .catch((err) => pencairanShowToast('Gagal menyimpan', err.message, 'error'));

    return false;
}

function bukaInvoiceCard(item) {
    activeInvoiceItem = item;

    document.getElementById('invId').textContent = `#INV-${item.id_pencairan}`;
    document.getElementById('invTanggal').textContent = item.tanggal_pencairan
        ? new Date(item.tanggal_pencairan).toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        })
        : '-';

    const namaWarga = item.warga?.nama || 'Anonim';
    document.getElementById('invNama').textContent = namaWarga;
    document.getElementById('invNik').textContent = `(NIK: ${item.warga?.nik || '-'})`;

    const bankInfo = document.getElementById('invBankInfo');
    const rekInfo = document.getElementById('invRekening');

    if (item.warga && item.warga.nama_bank_ewallet) {
        bankInfo.textContent = item.warga.nama_bank_ewallet;
        rekInfo.textContent = item.warga.nomor_rekening || '-';
    } else {
        bankInfo.textContent = 'Tunai / Langsung';
        rekInfo.textContent = '-';
    }

    document.getElementById('invNominal').textContent = formatRupiah(item.jumlah);

    const badgeStatus = document.getElementById('invBadgeStatus');
    if (item.status === 'menunggu') {
        badgeStatus.innerHTML = '<span class="badge badge--warning">Menunggu</span>';
    } else if (item.status === 'selesai') {
        badgeStatus.innerHTML = '<span class="badge badge--success">Selesai</span>';
    } else {
        badgeStatus.innerHTML = '<span class="badge badge--danger">Ditolak</span>';
    }

    bukaModal('modalInvoice');
}

function kirimInvoiceWhatsApp() {
    if (!activeInvoiceItem) {
        pencairanShowToast('Gagal', 'Data invoice tidak ditemukan.', 'error');
        return;
    }

    const noHp = activeInvoiceItem.warga?.no_hp;
    if (!noHp) {
        pencairanShowToast('Gagal', 'Nomor HP warga tidak tersedia.', 'error');
        return;
    }

    let formattedPhone = noHp.replace(/\D/g, '');
    if (formattedPhone.startsWith('0')) {
        formattedPhone = '62' + formattedPhone.slice(1);
    }

    const namaWarga = activeInvoiceItem.warga?.nama || 'Warga';
    const idInv = `#INV-${activeInvoiceItem.id_pencairan}`;
    const nominal = formatRupiah(activeInvoiceItem.jumlah);
    const status = (activeInvoiceItem.status || '').toUpperCase();
    const metodePencairan = activeInvoiceItem.warga?.nama_bank_ewallet
        ? activeInvoiceItem.warga.nama_bank_ewallet
        : 'Tunai';

    const tanggal = activeInvoiceItem.tanggal_pencairan
        ? new Date(activeInvoiceItem.tanggal_pencairan).toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        })
        : '-';

    const emojiPin = '\u{1F4CC}';
    const emojiCalendar = '\u{1F4C5}';
    const emojiMoneyBag = '\u{1F4B0}';
    const emojiCash = '\u{1F4B5}';
    const emojiSparkles = '\u{2728}';
    const emojiSeedling = '\u{1F331}';

    const pesan = `*NOTIFIKASI PENCAIRAN SALDO*
*BANK SAMPAH SAHABAT IBU*
---------------------------------------
Halo *${namaWarga}*,

Permohonan pencairan saldo tabungan sampah Anda telah kami proses dengan rincian berikut:

${emojiPin} *No. Invoice* : ${idInv}
${emojiCalendar} *Tanggal*    : ${tanggal}
${emojiCash} *Metode*     : ${metodePencairan}
${emojiMoneyBag} *Jumlah*     : *${nominal}*
${emojiSparkles} *Status*     : *${status}*
---------------------------------------
Terima kasih telah aktif menabung dan menjaga kebersihan lingkungan bersama kami! ${emojiSeedling}

_Pesan otomatis oleh Sistem Bank Sampah Sahabat Ibu_`;

    const urlWa = `https://wa.me/${formattedPhone}?text=${encodeURIComponent(pesan)}`;
    window.open(urlWa, '_blank');
}

function prosesPencairan(item) {
    document.getElementById('editId').value = item.id_pencairan;
    document.getElementById('editNama').value = `${item.warga?.nama || 'Anonim'} (NIK: ${item.warga?.nik || '-'})`;
    document.getElementById('editNominal').value = formatRupiah(item.jumlah);
    document.getElementById('editBank').value = item.warga?.nama_bank_ewallet || '-';
    document.getElementById('editNoRekening').value = item.warga?.nomor_rekening || '-';
    document.getElementById('editStatusRekening').value = item.warga?.nama_bank_ewallet
        ? formatStatusRekening(item.warga?.status_rekening)
        : '-';
    document.getElementById('editStatus').value = item.status;
    syncPencairanCombo('editStatus');

    bukaModal('modalProses');
}

function simpanProses(event) {
    event.preventDefault();
    const id = document.getElementById('editId').value;
    const status = document.getElementById('editStatus').value;

    fetch(`${baseUrl}/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({ status })
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
        pencairanShowToast('Berhasil disimpan', 'Status pencairan berhasil diperbarui.');
        setTimeout(() => window.location.reload(), 800);
    })
    .catch((err) => pencairanShowToast('Gagal menyimpan', err.message, 'error'));

    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    if (dataPencairan.flashSuccess) {
        pencairanShowToast('Berhasil', dataPencairan.flashSuccess);
    }

    const searchInput = document.getElementById('searchPencairan');
    const table = document.getElementById('pencairanTable');
    const perPageSelect = document.getElementById('perPageSelect');
    const kosongCari = document.getElementById('pencairanKosongCari');
    const rows = table ? Array.from(table.querySelectorAll('tbody tr[data-row-pencairan]')) : [];

    function render() {
        if (!table) return;

        const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const filtered = rows.filter(r => r.textContent.toLowerCase().includes(keyword));

        if (kosongCari) {
            kosongCari.style.display = (rows.length > 0 && filtered.length === 0) ? '' : 'none';
        }

        const perPage = parseInt(perPageSelect ? perPageSelect.value : 10, 10);
        const totalPages = Math.ceil(filtered.length / perPage) || 1;
        pencairanPage = Math.min(Math.max(pencairanPage, 1), totalPages);

        rows.forEach(r => {
            r.style.display = 'none';
        });

        const start = (pencairanPage - 1) * perPage;
        const end = start + perPage;
        filtered.slice(start, end).forEach(r => {
            r.style.display = '';
        });

        renderPagination({
            infoId: 'tableInfoPagination',
            buttonId: 'paginationButtons',
            currentPage: pencairanPage,
            totalPages: totalPages,
            totalItems: filtered.length,
            perPage: perPage,
            unit: 'permohonan',
            onPageChange: (p) => {
                pencairanPage = p;
                render();
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            pencairanPage = 1;
            render();
        });
    }

    document.querySelectorAll('.pencairan-page .modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) overlay.classList.remove('active');
        });
    });

    // Tutup / perbarui posisi dropdown modal jika area scroll modal digeser atau layar di-resize
    document.querySelectorAll('.pencairan-page .modal-body').forEach(function(body) {
        body.addEventListener('scroll', function() {
            const listAktif = body.querySelector('.combo-list.show');
            if (!listAktif) return;
            const input = listAktif.closest('.combo')?.querySelector('.combo-input');
            if (input) posisikanDropdownModal(input, listAktif);
        });
    });

    window.addEventListener('resize', function() {
        const listAktif = document.querySelector('.pencairan-page .modal-box .combo-list.show');
        if (!listAktif) return;
        const input = listAktif.closest('.combo')?.querySelector('.combo-input');
        if (input) posisikanDropdownModal(input, listAktif);
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.pencairan-page .modal-overlay.active').forEach(function(overlay) {
                overlay.classList.remove('active');
            });
        }
    });

    initComboWarga();
    initPencairanSelectCombo('tambahMetode');
    initPencairanSelectCombo('editStatus');
    initPencairanSelectCombo('perPageSelect', function() {
        pencairanPage = 1;
        render();
    });

    render();
});

Object.assign(window, {
    bukaModal,
    tutupModal,
    simpanTambah,
    bukaInvoiceCard,
    kirimInvoiceWhatsApp,
    prosesPencairan,
    simpanProses
});
