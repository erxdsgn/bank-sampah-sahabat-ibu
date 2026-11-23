import { escapeHtml, getCsrfToken } from '../utils/helpers';
import { renderPagination } from '../utils/paginations';

const pageData = window.__PENJUALAN_DATA__ || {};

function tanggalLokalHariIni() {
    const d = new Date();
    return d.getFullYear() + '-' +
        String(d.getMonth() + 1).padStart(2, '0') + '-' +
        String(d.getDate()).padStart(2, '0');
}

function formatRupiah(angka) {
    return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
}

function showToast(title, text, type = 'success') {
    const wrap = document.getElementById('toastWrap');
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

function hitungTotal() {
    const harga = parseFloat(document.getElementById('tambahHargaSatuan')?.value) || 0;
    const kuantitas = parseFloat(document.getElementById('tambahKuantitas')?.value) || 0;
    const total = harga * kuantitas;

    const display = document.getElementById('totalHargaDisplay');
    if (display) {
        display.textContent = formatRupiah(total);
    }
}

function onSelectKategori(data) {
    const inputHarga = document.getElementById('tambahHargaSatuan');
    const labelSatuans = document.querySelectorAll('.label-satuan');

    if (data) {
        labelSatuans.forEach(el => {
            el.textContent = data.satuan;
        });
        if (inputHarga) inputHarga.focus();
    } else {
        labelSatuans.forEach(el => {
            el.textContent = 'Satuan';
        });
    }
    hitungTotal();
}

function bukaModal(id) {
    if (id === 'modalTambahPenjualan') {
        const form = document.getElementById('formTambahPenjualan');
        if (form) form.reset();

        const setVal = (targetId, val) => {
            const el = document.getElementById(targetId);
            if (el) el.value = val;
        };

        setVal('tambahPembeli', '');
        setVal('tambahKategori', '');
        setVal('cariKategori', '');
        setVal('tambahHargaSatuan', '');
        setVal('tambahKuantitas', '');

        document.querySelectorAll('.label-satuan').forEach(el => {
            el.textContent = 'Satuan';
        });

        const totalDisplay = document.getElementById('totalHargaDisplay');
        if (totalDisplay) totalDisplay.textContent = 'Rp 0';

        setVal('tambahTanggal', tanggalLokalHariIni());
    }

    const modal = document.getElementById(id);
    if (modal) modal.classList.add('active');
}

function tutupModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
}

function initPerPageCombo() {
    const select = document.getElementById('perPageSelect');
    const input = document.getElementById('cari_perPageSelect');
    const list = document.getElementById('list_perPageSelect');
    if (!select || !input || !list) return;

    input.readOnly = true;
    let aktif = -1;

    const teks = o => o.textContent.replace(/\s+/g, ' ').trim();

    function sync() {
        const o = select.options[select.selectedIndex];
        input.value = o ? teks(o) : '';
    }

    function buka() {
        list.innerHTML = Array.from(select.options).map(o =>
            `<div class="combo-item${o.value === select.value ? ' is-selected' : ''}" data-value="${escapeHtml(o.value)}">${escapeHtml(teks(o))}</div>`
        ).join('');
        aktif = -1;
        list.classList.add('show');
        const terpilih = list.querySelector('.is-selected');
        if (terpilih) terpilih.scrollIntoView({ block: 'nearest' });
    }

    function tutup() {
        list.classList.remove('show');
        sync();
    }

    function pilih(value) {
        const berubah = select.value !== String(value);
        select.value = value;
        tutup();
        if (berubah) select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function sorot(arah) {
        const items = list.querySelectorAll('.combo-item');
        if (!items.length) return;
        aktif = (aktif + arah + items.length) % items.length;
        items.forEach((el, i) => el.classList.toggle('is-active', i === aktif));
        items[aktif].scrollIntoView({ block: 'nearest' });
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

    sync();
}

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

    function render(keyword) {
        const k = (keyword || '').toLowerCase().trim();
        const hasil = data.filter(d => d.label.toLowerCase().includes(k));
        aktif = -1;

        list.innerHTML = hasil.length
            ? hasil.map((d, index) => `<div class="combo-item" data-index="${index}" data-value="${escapeHtml(d.value)}">${escapeHtml(d.label)}</div>`).join('')
            : '<div class="combo-empty">Data tidak ditemukan</div>';
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
        items[aktif].scrollIntoView({ block: 'nearest' });
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
        const items = list.querySelectorAll('.combo-item');

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
            if (list.classList.contains('show') && aktif > -1 && items[aktif]) {
                e.preventDefault();
                pilih(items[aktif].dataset.value);
            }
        } else if (e.key === 'Escape') {
            list.classList.remove('show');
        }
    });

    list.addEventListener('mouseover', function(e) {
        const item = e.target.closest('.combo-item');
        if (!item) return;

        const items = list.querySelectorAll('.combo-item');
        items.forEach((el, i) => {
            if (el === item) {
                aktif = i;
                el.classList.add('is-active');
            } else {
                el.classList.remove('is-active');
            }
        });
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

function simpanPenjualan(event) {
    event.preventDefault();

    const namaPembeli = document.getElementById('tambahPembeli')?.value.trim();
    const idKategori = document.getElementById('tambahKategori')?.value;
    const kuantitas = document.getElementById('tambahKuantitas')?.value;
    const hargaSatuan = document.getElementById('tambahHargaSatuan')?.value;

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
        harga_satuan: hargaSatuan
    };

    const storeUrl = pageData.storeUrl || '/admin/pages/penjualan-pengepul';

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

document.addEventListener('DOMContentLoaded', function() {
    const inputTanggal = document.getElementById('tambahTanggal');
    if (inputTanggal) inputTanggal.value = tanggalLokalHariIni();

    const searchInput = document.getElementById('searchPenjualan');
    const table = document.getElementById('penjualanTable');
    const perPageSelect = document.getElementById('perPageSelect');
    const kosongCari = document.getElementById('penjualanKosongCari');

    let currentPage = 1;
    const rows = table ? Array.from(table.querySelectorAll('tbody tr[data-row-penjualan]')) : [];

    function render() {
        if (!table) return;

        const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const filtered = rows.filter(r => r.textContent.toLowerCase().includes(keyword));

        if (kosongCari) {
            kosongCari.style.display = (rows.length > 0 && filtered.length === 0) ? '' : 'none';
        }

        const perPage = parseInt(perPageSelect ? perPageSelect.value : 10, 10);
        const totalPages = Math.ceil(filtered.length / perPage) || 1;
        currentPage = Math.min(Math.max(currentPage, 1), totalPages);

        rows.forEach(r => {
            r.style.display = 'none';
        });

        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        filtered.slice(start, end).forEach(function(r, i) {
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
            unit: 'transaksi',
            onPageChange: (p) => {
                currentPage = p;
                render();
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentPage = 1;
            render();
        });
    }

    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            currentPage = 1;
            render();
        });
    }

    initPerPageCombo();
    render();

    initGenericCombo('comboKategori', 'tambahKategori', 'cariKategori', 'listKategori', onSelectKategori);

    const elKuantitas = document.getElementById('tambahKuantitas');
    const elHarga = document.getElementById('tambahHargaSatuan');

    if (elKuantitas) elKuantitas.addEventListener('input', hitungTotal);
    if (elHarga) elHarga.addEventListener('input', hitungTotal);
});

Object.assign(window, {
    bukaModal,
    tutupModal,
    simpanPenjualan
});
