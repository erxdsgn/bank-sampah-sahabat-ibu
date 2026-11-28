import { initComboSelect } from '../utils/combo-select';
import { escapeHtml, getCsrfToken } from '../utils/helpers';
import { renderPagination } from '../utils/paginations';

const dataKeuangan = window.__KEUANGAN_DATA__ || {};
const saldoKasSistem = Number(dataKeuangan.saldoKas ?? 0);
const storeUrl = dataKeuangan.storeUrl || '';

let keuanganPage = 1;
let transaksiAkanDihapus = null;

function tanggalHariIniLokal() {
    const now = new Date();
    return new Date(now.getTime() - now.getTimezoneOffset() * 60000)
        .toISOString()
        .substring(0, 10);
}

function bukaModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('active');
        document.body.classList.add('modal-open');
    }
}

function tutupModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');

    if (!document.querySelector('.keuangan-page .modal-overlay.active')) {
        document.body.classList.remove('modal-open');
    }
}

function formatRupiah(angka) {
    return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
}

function formatTanggal(tgl) {
    if (!tgl) return '-';
    const d = new Date(String(tgl).length === 10 ? tgl + 'T00:00:00' : tgl);
    if (isNaN(d)) return tgl;
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function keuanganShowToast(title, text, type = 'success') {
    const wrap = document.getElementById('keuanganToastWrap');
    if (!wrap) return;

    const toast = document.createElement('div');
    toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');

    const iconPath = type === 'error'
        ? '<path d="M18 6 6 18M6 6l12 12"></path>'
        : '<path d="M20 6 9 17l-5-5"></path>';

    toast.innerHTML = `
        <div class="toast-icon">
            <svg viewBox="0 0 24 24">${iconPath}</svg>
        </div>
        <div class="toast-body">
            <p class="toast-title">${escapeHtml(title)}</p>
            <p class="toast-text">${escapeHtml(text)}</p>
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

function filterTabel() {
    keuanganPage = 1;
    renderTabel();
}

function renderTabel() {
    const keyword = (document.getElementById('searchKeuangan')?.value || '').toLowerCase().trim();
    const sumber = document.getElementById('filterSumber')?.value || '';
    const jenis = document.getElementById('filterJenis')?.value || '';
    const periode = document.getElementById('filterPeriode')?.value || '';

    const perPageSelect = document.getElementById('perPageSelect');
    const rows = Array.from(document.querySelectorAll('#keuanganTableBody tr[data-jenis]'));

    const filtered = rows.filter(function(row) {
        const cocokSumber = !sumber || row.dataset.sumber === sumber;
        const cocokJenis = !jenis || row.dataset.jenis === jenis;
        const cocokPeriode = !periode || row.dataset.periode === periode;
        const cocokCari = !keyword || row.textContent.toLowerCase().includes(keyword);

        return cocokSumber && cocokJenis && cocokPeriode && cocokCari;
    });

    const kosongCari = document.getElementById('keuanganKosongCari');
    if (kosongCari) {
        kosongCari.style.display = (rows.length > 0 && filtered.length === 0) ? '' : 'none';
    }

    const perPage = parseInt(perPageSelect ? perPageSelect.value : 10, 10);
    const totalPages = Math.ceil(filtered.length / perPage) || 1;
    keuanganPage = Math.min(Math.max(keuanganPage, 1), totalPages);

    rows.forEach(r => {
        r.style.display = 'none';
    });

    const start = (keuanganPage - 1) * perPage;
    const end = start + perPage;
    filtered.slice(start, end).forEach(r => {
        r.style.display = '';
    });

    renderPagination({
        infoId: 'tableInfoPagination',
        buttonId: 'paginationButtons',
        currentPage: keuanganPage,
        totalPages: totalPages,
        totalItems: filtered.length,
        perPage: perPage,
        unit: 'transaksi',
        onPageChange: (p) => {
            keuanganPage = p;
            renderTabel();
        }
    });
}

function bukaModalTambah() {
    const form = document.getElementById('formTransaksi');
    if (form) form.reset();

    const jenisEl = document.getElementById('transaksiJenis');
    if (jenisEl) jenisEl.value = '';
    initComboSelect('transaksiJenis', { placeholderKosong: true });

    const tanggal = document.getElementById('transaksiTanggal');
    if (tanggal) {
        const hariIni = tanggalHariIniLokal();
        tanggal.min = hariIni;
        tanggal.value = hariIni;
    }

    bukaModal('modalTransaksi');
}

function simpanTransaksi(event) {
    event.preventDefault();

    const jenis = document.getElementById('transaksiJenis').value;
    const tanggalVal = document.getElementById('transaksiTanggal').value;
    const jumlah = parseFloat(document.getElementById('transaksiJumlah').value) || 0;
    const hariIni = tanggalHariIniLokal();

    if (!jenis) {
        keuanganShowToast('Gagal menyimpan', 'Silakan pilih jenis transaksi.', 'error');
        document.getElementById('cari_transaksiJenis')?.focus();
        return false;
    }

    if (!tanggalVal || tanggalVal < hariIni) {
        keuanganShowToast('Kesalahan Tanggal', 'Tanggal transaksi tidak boleh mundur (lebih awal dari hari ini).', 'error');
        document.getElementById('transaksiTanggal')?.focus();
        return false;
    }

    if (jenis === 'Pengeluaran' && jumlah > saldoKasSistem) {
        keuanganShowToast(
            'Gagal menyimpan',
            `Saldo kas tidak mencukupi! Sisa saldo Anda saat ini adalah ${formatRupiah(saldoKasSistem)}.`,
            'error'
        );
        return false;
    }

    const payload = {
        jenis: jenis,
        tanggal: tanggalVal,
        kategori: document.getElementById('transaksiKategori').value,
        jumlah: jumlah,
        keterangan: document.getElementById('transaksiKeterangan').value
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
            const pesan = err.errors
                ? Object.values(err.errors).flat().join(' ')
                : (err.message || 'Gagal menyimpan transaksi.');
            throw new Error(pesan);
        }
        return res.json();
    })
    .then(() => {
        tutupModal('modalTransaksi');
        keuanganShowToast('Berhasil disimpan', 'Transaksi baru telah ditambahkan.');
        setTimeout(() => window.location.reload(), 800);
    })
    .catch((err) => {
        keuanganShowToast('Gagal menyimpan', err.message, 'error');
    });

    return false;
}

function lihatDetail(item) {
    const badgeClass = item.jenis === 'Pemasukan' ? 'badge--success' : 'badge--danger';

    document.getElementById('detailJenis').innerHTML = `<span class="badge ${badgeClass}">${escapeHtml(item.jenis)}</span>`;
    document.getElementById('detailTanggal').textContent = formatTanggal(item.tanggal);
    document.getElementById('detailKategori').textContent = item.kategori || '-';
    document.getElementById('detailSumber').textContent = item.otomatis
        ? 'Otomatis · ' + (item.sumber_label || '')
        : (item.sumber_label || 'Kas Manual');

    const tanda = item.jenis === 'Pemasukan' ? '+ ' : '- ';
    const jumlahElement = document.getElementById('detailJumlah');

    jumlahElement.textContent = tanda + formatRupiah(item.jumlah);
    jumlahElement.classList.remove('amount-in', 'amount-out');
    jumlahElement.classList.add(item.jenis === 'Pemasukan' ? 'amount-in' : 'amount-out');
    document.getElementById('detailKeterangan').textContent = item.keterangan || '-';

    bukaModal('modalDetail');
}

function hapusTransaksi(item) {
    transaksiAkanDihapus = item;
    document.getElementById('hapusKategori').textContent = item.kategori || '-';
    document.getElementById('hapusJumlah').textContent = formatRupiah(item.jumlah);
    bukaModal('modalHapus');
}

function konfirmasiHapusTransaksi() {
    if (!transaksiAkanDihapus) return;

    const id = transaksiAkanDihapus.id_kas;
    const kategori = transaksiAkanDihapus.kategori || 'Transaksi';

    fetch(`/admin/pages/keuangan/${id}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        }
    })
    .then(async (res) => {
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal menghapus transaksi.');
        }
        return res.json();
    })
    .then(() => {
        tutupModal('modalHapus');
        keuanganShowToast('Berhasil dihapus', `Transaksi "${kategori}" telah dihapus.`);
        transaksiAkanDihapus = null;
        setTimeout(() => window.location.reload(), 800);
    })
    .catch((err) => {
        keuanganShowToast('Gagal menghapus', err.message, 'error');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    if (dataKeuangan.flashSuccess) {
        keuanganShowToast('Berhasil', dataKeuangan.flashSuccess);
    }

    if (dataKeuangan.flashError) {
        keuanganShowToast('Gagal', dataKeuangan.flashError, 'error');
    }

    const tanggalInput = document.getElementById('transaksiTanggal');
    if (tanggalInput) {
        tanggalInput.min = tanggalHariIniLokal();
    }

    const searchInput = document.getElementById('searchKeuangan');
    const filterSumber = document.getElementById('filterSumber');
    const filterJenis = document.getElementById('filterJenis');
    const filterPeriode = document.getElementById('filterPeriode');
    const perPageSelect = document.getElementById('perPageSelect');

    if (searchInput) searchInput.addEventListener('keyup', filterTabel);
    if (filterSumber) filterSumber.addEventListener('change', filterTabel);
    if (filterJenis) filterJenis.addEventListener('change', filterTabel);
    if (filterPeriode) filterPeriode.addEventListener('change', filterTabel);
    if (perPageSelect) perPageSelect.addEventListener('change', filterTabel);

    initComboSelect('perPageSelect', { readonly: true });
    initComboSelect('filterSumber', { readonly: true });
    initComboSelect('filterJenis', { readonly: true });
    initComboSelect('filterPeriode', { readonly: true });
    initComboSelect('transaksiJenis', { placeholderKosong: true });

    renderTabel();

    document.querySelectorAll('.keuangan-page .modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) overlay.classList.remove('active');
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.keuangan-page .modal-overlay.active').forEach(function(overlay) {
                overlay.classList.remove('active');
            });
        }
    });
});

Object.assign(window, {
    bukaModal,
    tutupModal,
    bukaModalTambah,
    simpanTransaksi,
    lihatDetail,
    hapusTransaksi,
    konfirmasiHapusTransaksi
});
