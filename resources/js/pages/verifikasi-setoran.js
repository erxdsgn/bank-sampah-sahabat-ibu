import { escapeHtml, getCsrfToken } from '../utils/helpers';
import { renderPagination } from '../utils/paginations';

const pageData = window.__VERIFIKASI_DATA__ || {};
let verifEditor = null;
let tambahEditor = null;
const verifikasiComboRegistry = {};

function verifikasiShowToast(title, text, type = 'success') {
    const wrap = document.getElementById('verifikasiToastWrap');
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
        toast.classList.add('toast--leaving');
        setTimeout(() => toast.remove(), 180);
    }

    toast.querySelector('.toast-close').addEventListener('click', hapusToast);
    wrap.appendChild(toast);
    setTimeout(hapusToast, 3500);
}

function syncVerifikasiCombo(selectId) {
    if (verifikasiComboRegistry[selectId]) verifikasiComboRegistry[selectId]();
}

function initVerifikasiCombo(cfg) {
    const select = document.getElementById(cfg.selectId);
    const input = document.getElementById(cfg.inputId);
    const list = document.getElementById(cfg.listId);
    const onChange = cfg.onChange || function() {};
    const isDisabled = cfg.isDisabled || function() { return false; };

    if (!select || !input || !list) return;

    const data = Array.from(select.options)
        .filter(o => o.value !== '')
        .map(o => ({
            value: o.value,
            label: o.textContent.replace(/\s+/g, ' ').trim()
        }));

    const readonly = !!cfg.readonly;
    input.readOnly = readonly;

    let aktif = -1;

    function sync() {
        const d = data.find(x => x.value === String(select.value));
        input.value = d ? d.label : '';
        input.disabled = select.disabled;
    }

    function render(keyword) {
        const k = (keyword || '').toLowerCase().trim();
        const hasil = data.filter(d => d.label.toLowerCase().includes(k));

        aktif = -1;

        list.innerHTML = hasil.length
            ? hasil.map(d => {
                const nonaktif = d.value !== String(select.value) && isDisabled(d.value);
                return `<div class="combo-item${d.value === String(select.value) ? ' is-selected' : ''}${nonaktif ? ' is-disabled' : ''}" data-value="${escapeHtml(d.value)}"${nonaktif ? ' aria-disabled="true"' : ''}>${escapeHtml(d.label)}${nonaktif ? ' <span class="combo-note">· sudah dipilih</span>' : ''}</div>`;
            }).join('')
            : '<div class="combo-empty">Data tidak ditemukan</div>';
    }

    function buka(keyword) {
        render(keyword);
        list.classList.add('show');
        const terpilih = list.querySelector('.is-selected');
        if (terpilih) terpilih.scrollIntoView({ block: 'nearest' });
    }

    function tutup() {
        list.classList.remove('show');
        sync();
    }

    function pilih(value) {
        const d = data.find(x => x.value === String(value));
        if (!d) return;
        if (d.value !== String(select.value) && isDisabled(d.value)) return;

        const berubah = select.value !== d.value;
        select.value = d.value;
        tutup();
        if (berubah) onChange();
    }

    function sorot(arah) {
        const items = list.querySelectorAll('.combo-item:not(.is-disabled)');
        if (!items.length) return;

        aktif = (aktif + arah + items.length) % items.length;

        items.forEach((el, i) => {
            el.classList.toggle('is-active', i === aktif);
        });

        items[aktif].scrollIntoView({ block: 'nearest' });
    }

    input.addEventListener('focus', function() {
        if (!readonly) input.select();
        buka('');
    });

    input.addEventListener('click', function() {
        if (!list.classList.contains('show')) buka('');
    });

    input.addEventListener('input', function() {
        buka(this.value);
    });

    input.addEventListener('blur', tutup);

    input.addEventListener('keydown', function(e) {
        const items = list.querySelectorAll('.combo-item:not(.is-disabled)');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!list.classList.contains('show')) buka(readonly ? '' : this.value);
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
            if (list.classList.contains('show')) {
                e.stopPropagation();
                tutup();
            }
        }
    });

    list.addEventListener('mouseover', function(e) {
        const item = e.target.closest('.combo-item:not(.is-disabled)');
        if (!item) return;

        list.querySelectorAll('.combo-item:not(.is-disabled)').forEach((el, i) => {
            el.classList.toggle('is-active', el === item);
            if (el === item) aktif = i;
        });
    });

    list.addEventListener('mousedown', function(e) {
        e.preventDefault();
        const item = e.target.closest('.combo-item:not(.is-disabled)');
        if (item) pilih(item.dataset.value);
    });

    verifikasiComboRegistry[cfg.selectId] = sync;
    sync();
}

function resetVerifikasiCombo(selectId, inputId, listId) {
    const select = document.getElementById(selectId);
    const input = document.getElementById(inputId);
    const list = document.getElementById(listId);

    if (select) select.value = '';
    if (input) input.value = '';
    if (list) list.classList.remove('show');
}

function bukaModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('active');
}

function tutupModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('active');
}

function formatRupiah(angka) {
    return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID', {
        maximumFractionDigits: 2
    });
}

function formatHarga(n, satuan = '') {
    const nilai = Number(n) || 0;
    return 'Rp ' + nilai.toLocaleString('id-ID', {
        maximumFractionDigits: 2
    }) + (satuan ? ' / ' + satuan : '');
}

function hargaDariSelect(select) {
    if (!select || select.selectedIndex < 0) return 0;
    return Number(select.options[select.selectedIndex]?.dataset.harga || 0);
}

// Satuan yang boleh desimal (berbasis berat/volume). Selain ini wajib bilangan bulat.
const SATUAN_DESIMAL = ['kg', 'liter'];

function satuanBolehDesimal(satuan) {
    return SATUAN_DESIMAL.includes((satuan || '').toLowerCase());
}

function satuanDariSelect(select) {
    if (!select || select.selectedIndex < 0) return '';
    return select.options[select.selectedIndex]?.dataset.satuan || '';
}

function pilihKategori(select, idKategori) {
    select.value = idKategori ? String(idKategori) : '';
    if (select.selectedIndex === -1) {
        select.value = '';
    }
}

function createItemEditor(cfg) {
    const listEl = document.getElementById(cfg.listId);
    const totalEl = document.getElementById(cfg.totalId);
    const countEl = document.getElementById(cfg.countId);
    const addBtn = document.getElementById(cfg.addBtnId);
    const master = document.getElementById('masterKategori');

    let seq = 0;
    let editable = true;

    const rows = () => Array.from(listEl.querySelectorAll('.item-row'));

    function updateRow(row) {
        const select = row.querySelector('select');
        const harga = hargaDariSelect(select);
        const satuan = satuanDariSelect(select);
        const jumlah = parseFloat(row.querySelector('.item-qty').value) || 0;
        const tersimpan = !editable && row.dataset.subtotal !== '' ? Number(row.dataset.subtotal) : null;
        const nilai = tersimpan !== null ? tersimpan : harga * jumlah;

        row.querySelector('.item-unit').value = satuan || '-';

        // Atur input jumlah sesuai satuan: kg/liter desimal, pcs/set/unit/gram bulat
        const qtyInput = row.querySelector('.item-qty');
        if (satuan && row.dataset.satuanDipakai !== satuan) {
            row.dataset.satuanDipakai = satuan;
            if (satuanBolehDesimal(satuan)) {
                qtyInput.step = 'any';
                qtyInput.min = '0.01';
                qtyInput.setAttribute('inputmode', 'decimal');
            } else {
                qtyInput.step = '1';
                qtyInput.min = '1';
                qtyInput.setAttribute('inputmode', 'numeric');
                const v = parseFloat(qtyInput.value);
                if (!isNaN(v) && !Number.isInteger(v)) {
                    qtyInput.value = Math.ceil(v);
                }
            }
        }

        let info = 'Pilih kategori terlebih dahulu.';
        if (select.value) {
            info = harga > 0 && satuan
                ? 'Harga aktif: ' + formatHarga(harga, satuan === 'liter' ? 'L' : satuan)
                : 'Kategori ini belum memiliki harga aktif.';
        }
        if (tersimpan !== null) info = 'Nilai tercatat saat transaksi.';

        row.querySelector('.item-info').textContent = info;
        row.querySelector('.item-sub').textContent = formatRupiah(nilai);

        return nilai;
    }

    function recalc() {
        const list = rows();
        let total = 0;

        list.forEach((row, i) => {
            total += updateRow(row);
            row.querySelector('.item-row__no').textContent = 'Jenis sampah ' + (i + 1);
            row.querySelector('.item-row__remove').style.display =
                editable && list.length > 1 ? '' : 'none';
        });

        totalEl.textContent = formatRupiah(total);
        countEl.textContent = list.length + ' jenis';
        if (addBtn) addBtn.style.display = editable ? '' : 'none';
    }

    function applyEditable(row) {
        const select = row.querySelector('select');
        select.disabled = !editable;
        row.querySelector('.item-qty').disabled = !editable;
        syncVerifikasiCombo(select.id);
    }

    function addRow(data = {}, focus = false) {
        const uid = cfg.prefix + '_' + (++seq);
        const row = document.createElement('div');

        row.className = 'item-row';
        row.dataset.idDetail = data.id_detail ?? '';
        row.dataset.subtotal = data.subtotal ?? '';
        row.innerHTML = `
            <div class="item-row__head">
                <span class="item-row__no"></span>
                <button type="button" class="item-row__remove" title="Hapus jenis ini" aria-label="Hapus jenis ini">&times;</button>
            </div>

            <div class="item-row__grid">
                <div class="form-group">
                    <label for="${uid}_inp">Kategori Sampah</label>
                    <div class="combo">
                        <input type="text" id="${uid}_inp" class="combo-input"
                            placeholder="Ketik / pilih kategori sampah..." autocomplete="off">
                        <div class="combo-list" id="${uid}_list"></div>
                    </div>
                    <select id="${uid}_sel" class="combo-hidden" tabindex="-1" aria-hidden="true"></select>
                </div>

                <div class="form-group">
                    <label for="${uid}_qty">Jumlah</label>
                    <div class="item-row__qty">
                        <input type="number" id="${uid}_qty" class="item-qty" step="0.001" min="0.001" required>
                        <input type="text" class="item-unit" value="-" readonly tabindex="-1">
                    </div>
                </div>
            </div>

            <div class="item-row__foot">
                <span class="item-info">Pilih kategori terlebih dahulu.</span>
                <span class="item-sub">Rp 0</span>
            </div>
        `;

        listEl.appendChild(row);

        const select = row.querySelector('select');
        select.innerHTML = master.innerHTML;
        pilihKategori(select, data.id_kategori);
        select.disabled = !editable;

        const qty = row.querySelector('.item-qty');
        qty.value = data.jumlah ?? '';
        qty.disabled = !editable;
        qty.addEventListener('input', function() {
            row.classList.remove('is-invalid');
            recalc();
        });

        initVerifikasiCombo({
            selectId: select.id,
            inputId: uid + '_inp',
            listId: uid + '_list',
            isDisabled: function(value) {
                return rows().some(r => r !== row && r.querySelector('select').value === String(value));
            },
            onChange: function() {
                row.classList.remove('is-invalid');
                recalc();
            }
        });

        row.querySelector('.item-row__remove').addEventListener('click', function() {
            delete verifikasiComboRegistry[select.id];
            row.remove();
            recalc();
        });

        recalc();

        if (focus) {
            row.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            row.querySelector('.combo-input').focus();
        }

        return row;
    }

    function clear() {
        rows().forEach(r => delete verifikasiComboRegistry[r.querySelector('select').id]);
        listEl.innerHTML = '';
    }

    function setItems(items, canEdit) {
        clear();
        editable = !!canEdit;

        const data = Array.isArray(items) && items.length ? items : [{}];
        data.forEach(d => addRow(d));

        rows().forEach(applyEditable);
        recalc();
    }

    function getItems() {
        return rows().map(row => ({
            id_detail: row.dataset.idDetail || null,
            id_kategori: row.querySelector('select').value,
            jumlah: row.querySelector('.item-qty').value
        }));
    }

    function validate() {
        const list = rows();
        const dipakai = new Set();

        for (const row of list) {
            row.classList.remove('is-invalid');
        }

        for (const row of list) {
            const kat = row.querySelector('select').value;
            const jml = Number(row.querySelector('.item-qty').value);

            if (!kat) {
                row.classList.add('is-invalid');
                row.querySelector('.combo-input').focus();
                return 'Silakan pilih kategori sampah pada setiap baris.';
            }

            if (!jml || jml <= 0) {
                row.classList.add('is-invalid');
                row.querySelector('.item-qty').focus();
                return 'Jumlah setiap jenis sampah harus lebih dari 0.';
            }

            const satuanRow = satuanDariSelect(row.querySelector('select'));
            if (!satuanBolehDesimal(satuanRow) && !Number.isInteger(jml)) {
                row.classList.add('is-invalid');
                row.querySelector('.item-qty').focus();
                return 'Satuan ' + satuanRow + ' harus berupa bilangan bulat (tanpa desimal).';
            }

            if (dipakai.has(kat)) {
                row.classList.add('is-invalid');
                row.querySelector('.combo-input').focus();
                return 'Kategori yang sama dipilih lebih dari sekali. Gabungkan jumlahnya dalam satu baris.';
            }

            dipakai.add(kat);
        }

        return null;
    }

    if (addBtn) {
        addBtn.addEventListener('click', function() {
            addRow({}, true);
        });
    }

    return { setItems, getItems, validate, addRow };
}

function bukaVerifikasi(item, allowEdit = true) {
    const nama = item.warga?.nama || '-';
    const bisaDiedit = item.status === 'pending' && !!allowEdit;

    document.getElementById('verifAvatar').textContent = nama.trim().charAt(0).toUpperCase();
    document.getElementById('verifNama').textContent = nama;
    document.getElementById('verifNik').textContent = item.warga?.nik || '-';
    document.getElementById('verifKode').textContent = 'ID Transaksi: ' + (item.kode_transaksi || '-');
    document.getElementById('verifJudul').textContent = bisaDiedit
        ? 'Edit & Verifikasi Setoran'
        : 'Detail Setoran';
    document.getElementById('verifId').value = item.id;

    const statusEl = document.getElementById('verifStatus');
    const statusMap = {
        pending: ['Menunggu', 'status-badge--pending'],
        approved: ['Disetujui', 'status-badge--approved'],
        rejected: ['Ditolak', 'status-badge--rejected']
    };
    const [statusTeks, statusKelas] = statusMap[item.status] || statusMap.rejected;
    statusEl.className = 'status-badge verif-status-spacer ' + statusKelas;
    statusEl.textContent = statusTeks;

    document.getElementById('verifAlasan').value = item.catatan_admin || '';
    document.getElementById('verifAlasanBlock').classList.remove('open');

    const hintEl = document.getElementById('verifHintEdit');
    if (hintEl) {
        hintEl.textContent = bisaDiedit
            ? 'Sesuaikan jenis dan jumlah dengan hasil pengecekan ulang sebelum menyetujui.'
            : 'Mode lihat detail (hanya baca).';
    }

    const fotoEl = document.getElementById('verifFoto');
    const fotoKosong = document.getElementById('verifFotoKosong');

    if (item.foto_bukti) {
        fotoEl.src = '/storage/' + item.foto_bukti;
        fotoEl.style.display = 'block';
        fotoKosong.style.display = 'none';
    } else {
        fotoEl.style.display = 'none';
        fotoKosong.style.display = 'block';
    }

    verifEditor.setItems(item.items, bisaDiedit);

    document.getElementById('btnTolakSetoran').style.display = bisaDiedit ? 'inline-flex' : 'none';
    document.getElementById('btnSetujuiSetoran').style.display = bisaDiedit ? 'inline-flex' : 'none';

    bukaModal('modalVerifikasi');
}

function prosesVerifikasi(event) {
    event.preventDefault();

    const error = verifEditor.validate();
    if (error) {
        verifikasiShowToast('Gagal memproses', error, 'error');
        return false;
    }

    const id = document.getElementById('verifId').value;
    const payload = { items: verifEditor.getItems() };

    fetch(`/admin/pages/verifikasi-setoran/${id}/setujui`, {
        method: 'PATCH',
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
            let errorMessage = err.message || 'Gagal menyetujui setoran.';
            if (err.errors) {
                const firstErrorKey = Object.keys(err.errors)[0];
                if (firstErrorKey) errorMessage = err.errors[firstErrorKey][0];
            }
            throw new Error(errorMessage);
        }
        return res.json();
    })
    .then(() => {
        tutupModal('modalVerifikasi');
        verifikasiShowToast('Setoran disetujui', 'Data hasil cek ulang tersimpan dan saldo warga diperbarui.');
        setTimeout(() => window.location.reload(), 800);
    })
    .catch((err) => {
        verifikasiShowToast('Gagal menyetujui', err.message, 'error');
    });

    return false;
}

function bukaTambahSetoran() {
    const form = document.getElementById('formTambahSetoran');
    form.reset();
    resetVerifikasiCombo('tambahWarga', 'cariWarga', 'listWarga');

    // Mengambil tanggal hari ini (Waktu Lokal) dan menyimpannya otomatis
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const today = `${year}-${month}-${day}`;

    const inputTanggal = document.getElementById('tambahTanggal');
    if(inputTanggal) inputTanggal.value = today;

    tambahEditor.setItems([{}], true);
    bukaModal('modalTambah');
}

function simpanSetoranBaru(event) {
    event.preventDefault();

    const idWarga = document.getElementById('tambahWarga').value;

    if (!idWarga) {
        verifikasiShowToast('Gagal menyimpan', 'Silakan pilih warga terlebih dahulu.', 'error');
        document.getElementById('cariWarga').focus();
        return false;
    }

    const error = tambahEditor.validate();
    if (error) {
        verifikasiShowToast('Gagal menyimpan', error, 'error');
        return false;
    }

    const payload = {
        id_warga: idWarga,
        tanggal_setoran: document.getElementById('tambahTanggal').value,
        catatan_admin: document.getElementById('tambahCatatan')?.value || null,
        items: tambahEditor.getItems().map(({ id_kategori, jumlah }) => ({ id_kategori, jumlah }))
    };

    fetch('/admin/pages/verifikasi-setoran', {
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
            let errorMessage = err.message || 'Gagal menyimpan setoran.';
            if (err.errors) {
                const firstErrorKey = Object.keys(err.errors)[0];
                if (firstErrorKey) errorMessage = err.errors[firstErrorKey][0];
            }
            throw new Error(errorMessage);
        }
        return res.json();
    })
    .then(() => {
        tutupModal('modalTambah');
        verifikasiShowToast('Setoran ditambahkan', 'Data setoran tersimpan dan saldo warga diperbarui.');
        setTimeout(() => window.location.reload(), 800);
    })
    .catch((err) => {
        verifikasiShowToast('Gagal menyimpan', err.message, 'error');
    });

    return false;
}

function tolakSetoran() {
    const alasanBlock = document.getElementById('verifAlasanBlock');

    if (!alasanBlock.classList.contains('open')) {
        alasanBlock.classList.add('open');
        document.getElementById('verifAlasan').focus();
        return;
    }

    const catatan = document.getElementById('verifAlasan').value.trim();

    if (!catatan) {
        verifikasiShowToast('Catatan diperlukan', 'Isi alasan penolakan sebelum melanjutkan.', 'error');
        return;
    }

    const id = document.getElementById('verifId').value;

    fetch(`/admin/pages/verifikasi-setoran/${id}/tolak`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({ catatan_admin: catatan })
    })
    .then(async (res) => {
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            let errorMessage = err.message || 'Gagal menolak setoran.';
            if (err.errors) {
                const firstErrorKey = Object.keys(err.errors)[0];
                if (firstErrorKey) errorMessage = err.errors[firstErrorKey][0];
            }
            throw new Error(errorMessage);
        }
        return res.json();
    })
    .then(() => {
        tutupModal('modalVerifikasi');
        verifikasiShowToast('Setoran ditolak', 'Warga perlu mengisi ulang data setoran.');
        setTimeout(() => window.location.reload(), 800);
    })
    .catch((err) => {
        verifikasiShowToast('Gagal menolak', err.message, 'error');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    if (pageData.flashSuccess) {
        verifikasiShowToast('Berhasil', pageData.flashSuccess);
    }

    const searchInput = document.getElementById('searchSetoran');
    const table = document.getElementById('setoranTable');
    const perPageSelect = document.getElementById('perPageSelect');
    const kosongCari = document.getElementById('setoranKosongCari');

    let currentPage = 1;
    const rows = table ? Array.from(table.querySelectorAll('tbody tr[data-status]')) : [];

    function render() {
        if (!table) return;

        const keyword = (searchInput?.value || '').toLowerCase().trim();
        const activePill = document.querySelector('.fpill.active');
        const statusFilter = activePill ? activePill.dataset.filter : 'all';

        const filtered = rows.filter(function(row) {
            const matchSearch = !keyword || row.textContent.toLowerCase().includes(keyword);
            const matchStatus = statusFilter === 'all' || row.dataset.status === statusFilter;
            return matchSearch && matchStatus;
        });

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
            unit: 'setoran',
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

    document.querySelectorAll('.fpill').forEach(function(pill) {
        pill.addEventListener('click', function() {
            document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            currentPage = 1;
            render();
        });
    });

    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            currentPage = 1;
            render();
        });
    }

    initVerifikasiCombo({
        comboId: 'combo_perPageSelect',
        selectId: 'perPageSelect',
        inputId: 'cari_perPageSelect',
        listId: 'list_perPageSelect',
        readonly: true,
        onChange: function() {
            currentPage = 1;
            render();
        }
    });

    initVerifikasiCombo({
        comboId: 'comboWarga',
        selectId: 'tambahWarga',
        inputId: 'cariWarga',
        listId: 'listWarga'
    });

    verifEditor = createItemEditor({
        prefix: 'verif',
        listId: 'verifItemList',
        totalId: 'verifEstimasi',
        countId: 'verifItemCount',
        addBtnId: 'verifAddItem'
    });

    tambahEditor = createItemEditor({
        prefix: 'tambah',
        listId: 'tambahItemList',
        totalId: 'tambahEstimasi',
        countId: 'tambahItemCount',
        addBtnId: 'tambahAddItem'
    });

    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(function(overlay) {
                overlay.classList.remove('active');
            });
        }
    });

    render();
});

Object.assign(window, {
    bukaModal,
    tutupModal,
    bukaVerifikasi,
    prosesVerifikasi,
    bukaTambahSetoran,
    simpanSetoranBaru,
    tolakSetoran
});
