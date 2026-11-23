import { initComboSelect } from '../utils/combo-select';
import { escapeHtml, getCsrfToken } from '../utils/helpers';
import { renderPagination } from '../utils/paginations';
import { showToast } from '../utils/toast';

const data = Array.isArray(window.__KATALOG_DATA__) ? window.__KATALOG_DATA__ : [];
const urls = window.__KATALOG_URLS__ || {};

let currentPage = 1;
let hapusCtx = null;

/* ========== Helpers ========== */

function formatRupiah(angka) {
    return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
}

function imgUrl(foto) {
    if (!foto) return '';
    if (/^https?:\/\//i.test(foto)) return foto;
    const base = (urls.gambar || '').replace(/\/$/, '');
    const path = foto.replace(/^\//, '');
    return `${base}/${encodeURIComponent(path)}`;
}

// Mencari ID entah itu 'id' atau 'id_produk'
function getProdukId(p) {
    return p.id_produk !== undefined ? p.id_produk : p.id;
}

/* ========== Modal ========== */

function bukaModal(id) {
    document.getElementById(id)?.classList.add('active');
}

function tutupModal(id) {
    document.getElementById(id)?.classList.remove('active');
}

/* ========== Detail ========== */

function lihatDetail(id) {
    const produk = data.find((p) => Number(getProdukId(p)) === Number(id));
    if (!produk) return;

    document.getElementById('detailNama').textContent = produk.nama_produk;
    document.getElementById('detailDeskripsi').textContent = produk.deskripsi || '-';
    document.getElementById('detailHarga').textContent = formatRupiah(produk.harga);
    document.getElementById('detailStok').textContent = produk.stok;

    const img = document.getElementById('detailfoto');
    const kosong = document.getElementById('detailfotoKosong');

    if (produk.foto) {
        img.src = imgUrl(produk.foto);
        img.style.display = 'block';
        kosong.style.display = 'none';
    } else {
        img.style.display = 'none';
        kosong.style.display = 'flex';
    }

    bukaModal('modalDetail');
}

/* ========== Tambah ========== */

function resetFormTambah() {
    document.getElementById('formTambahProduk').reset();
}

function bukaModalTambah() {
    resetFormTambah();
    bukaModal('modalTambah');
}

function simpanTambah(event) {
    event.preventDefault();
    const fd = new FormData();
    fd.append('foto', document.getElementById('tambahfoto').files[0]);
    fd.append('nama_produk', document.getElementById('tambahNama').value.trim());
    fd.append('deskripsi', document.getElementById('tambahDeskripsi').value.trim());
    fd.append('harga', document.getElementById('tambahHarga').value);
    fd.append('stok', document.getElementById('tambahStok').value);

    fetch(urls.store, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: fd,
    })
        .then(async (res) => {
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.message || 'Gagal menyimpan produk.');
            return json;
        })
        .then(() => {
            showToast('Berhasil', 'Produk berhasil ditambahkan.');
            setTimeout(() => window.location.reload(), 800);
        })
        .catch((err) => showToast('Gagal', err.message, 'error'));

    return false;
}

/* ========== Edit ========== */

function bukaModalEdit(id) {
    const produk = data.find((p) => Number(getProdukId(p)) === Number(id));
    if (!produk) return;

    document.getElementById('editId').value = getProdukId(produk);
    document.getElementById('editNama').value = produk.nama_produk || '';
    document.getElementById('editDeskripsi').value = produk.deskripsi || '';
    document.getElementById('editHarga').value = produk.harga || '';
    document.getElementById('editStok').value = produk.stok || '';
    document.getElementById('editfoto').value = '';

    const img = document.getElementById('editfotoPreview');
    const kosong = document.getElementById('editfotoKosong');

    if (produk.foto) {
        img.src = imgUrl(produk.foto);
        img.style.display = 'block';
        kosong.style.display = 'none';
    } else {
        img.style.display = 'none';
        kosong.style.display = 'flex';
    }

    bukaModal('modalEdit');
}

function simpanEdit(event) {
    event.preventDefault();
    const id = document.getElementById('editId').value;
    const fd = new FormData();
    fd.append('_method', 'PUT');
    fd.append('nama_produk', document.getElementById('editNama').value.trim());
    fd.append('deskripsi', document.getElementById('editDeskripsi').value.trim());
    fd.append('harga', document.getElementById('editHarga').value);
    fd.append('stok', document.getElementById('editStok').value);

    const file = document.getElementById('editfoto').files[0];
    if (file) fd.append('foto', file);

    const url = (urls.update || '').replace(':id', id);

    fetch(url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: fd,
    })
        .then(async (res) => {
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.message || 'Gagal menyimpan perubahan.');
            return json;
        })
        .then(() => {
            showToast('Berhasil', 'Produk berhasil diperbarui.');
            setTimeout(() => window.location.reload(), 800);
        })
        .catch((err) => showToast('Gagal', err.message, 'error'));

    return false;
}

/* ========== Hapus ========== */

function hapusProduk(id) {
    const produk = data.find((p) => Number(getProdukId(p)) === Number(id));
    if (!produk) return;
    hapusCtx = produk;
    document.getElementById('hapusNama').textContent = produk.nama_produk;
    bukaModal('modalHapus');
}

function konfirmasiHapus() {
    if (!hapusCtx) return;
    const url = (urls.destroy || '').replace(':id', getProdukId(hapusCtx));

    fetch(url, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
    })
        .then(async (res) => {
            if (!res.ok) throw new Error('Gagal menghapus produk.');
            return res.json().catch(() => ({}));
        })
        .then(() => {
            showToast('Berhasil', 'Produk berhasil dihapus.');
            setTimeout(() => window.location.reload(), 800);
        })
        .catch((err) => showToast('Gagal', err.message, 'error'));
}

/* ========== Filter & Pagination ========== */

function filterKatalog(keyword, resetPage = false) {
    const k = (keyword ?? document.getElementById('searchKatalog').value ?? '')
        .toLowerCase().trim();

    const items = Array.from(document.querySelectorAll('#catalogGrid .catalog-item'));
    const filtered = items.filter((item) => (item.dataset.nama || '').includes(k));

    const perPage = parseInt(document.getElementById('perPageSelect')?.value || 8, 10) || 8;
    const totalPages = Math.ceil(filtered.length / perPage) || 1;

    if (resetPage) currentPage = 1;
    currentPage = Math.min(Math.max(currentPage, 1), totalPages);

    items.forEach((item) => { item.style.display = 'none'; });

    const start = (currentPage - 1) * perPage;
    filtered.slice(start, start + perPage).forEach((item) => {
        item.style.display = '';
    });

    const kosongCari = document.getElementById('catalogKosongCari');
    if (kosongCari) {
        kosongCari.style.display = (items.length > 0 && filtered.length === 0) ? 'block' : 'none';
    }

    renderPagination({
        infoId: 'tableInfoPagination',
        buttonId: 'paginationButtons',
        currentPage,
        totalPages,
        totalItems: filtered.length,
        perPage,
        unit: 'produk',
        onPageChange: (p) => {
            currentPage = p;
            filterKatalog();
        },
    });
}

/* ========== Bootstrap ========== */

document.addEventListener('DOMContentLoaded', () => {
    initComboSelect('perPageSelect');

    const searchInput = document.getElementById('searchKatalog');
    if (searchInput) {
        searchInput.addEventListener('keyup', () => filterKatalog(undefined, true));
    }

    const perPageSelect = document.getElementById('perPageSelect');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', () => filterKatalog(undefined, true));
    }

    // Tutup modal overlay saat klik area gelap
    document.querySelectorAll('.modal-overlay').forEach((overlay) => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) overlay.classList.remove('active');
        });
    });

    // Tutup modal dengan Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach((el) => {
                el.classList.remove('active');
            });
        }
    });

    filterKatalog(undefined, true);

    // Flash message (jika ada)
    const flash = window.__KATALOG_FLASH__;
    if (flash?.success) showToast('Berhasil', flash.success);
});

/* ========== Expose ke global (untuk onclick di Blade) ========== */

Object.assign(window, {
    bukaModal,
    tutupModal,
    lihatDetail,
    bukaModalTambah,
    simpanTambah,
    bukaModalEdit,
    simpanEdit,
    hapusProduk,
    konfirmasiHapus,
});
