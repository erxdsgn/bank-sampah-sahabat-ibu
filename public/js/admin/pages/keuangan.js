// Ambil saldo dari PHP untuk validasi frontend
    const { saldoKasSistem, flashSuccess, flashError, urlStore } = window.KeuanganPageConfig;

    document.addEventListener('DOMContentLoaded', function() {
        if (flashSuccess) showToast('Berhasil', flashSuccess);

        if (flashError) showToast('Gagal', flashError, 'error');

        const searchInput = document.getElementById('searchKeuangan');
        const filterSumber = document.getElementById('filterSumber');
        const filterJenis = document.getElementById('filterJenis');
        const filterPeriode = document.getElementById('filterPeriode');

        if (searchInput) searchInput.addEventListener('keyup', filterTabel);
        if (filterSumber) filterSumber.addEventListener('change', filterTabel);
        if (filterJenis) filterJenis.addEventListener('change', filterTabel);
        if (filterPeriode) filterPeriode.addEventListener('change', filterTabel);

        const paginationContainer = document.getElementById('paginationButtons');
        if (paginationContainer) {
            paginationContainer.addEventListener('click', function(e) {
                const btn = e.target.closest('button[data-page]');
                if (!btn || btn.disabled) return;
                keuanganPage = parseInt(btn.dataset.page, 10);
                renderTabel();
            });
        }

        const perPageSelect = document.getElementById('perPageSelect');
        if (perPageSelect) perPageSelect.addEventListener('change', filterTabel);

        initComboSelect('perPageSelect');
        initComboSelect('filterSumber');
        initComboSelect('filterJenis');
        initComboSelect('filterPeriode');
        initComboSelect('transaksiJenis', { placeholderKosong: true });

        renderTabel();

        document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) overlay.classList.remove('active');
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(function(overlay) {
                    overlay.classList.remove('active');
                });
            }
        });
    });

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

        if (!document.querySelector('.modal-overlay.active')) {
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

    function showToast(title, text, type = 'success') {
        const wrap = document.getElementById('toastWrap');
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
                <p class="toast-title">${title}</p>
                <p class="toast-text">${text}</p>
            </div>
            <button class="toast-close" type="button" aria-label="Tutup">&times;</button>
        `;

        function hapusToast() { toast.remove(); }

        toast.querySelector('.toast-close').addEventListener('click', hapusToast);
        wrap.appendChild(toast);
        setTimeout(hapusToast, 3500);
    }

    let keuanganPage = 1;

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
        const paginationContainer = document.getElementById('paginationButtons');
        const tableInfo = document.getElementById('tableInfoPagination');

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

        rows.forEach(r => r.style.display = 'none');

        const start = (keuanganPage - 1) * perPage;
        const end = start + perPage;
        filtered.slice(start, end).forEach(r => r.style.display = '');

        if (tableInfo) {
            tableInfo.innerHTML = filtered.length === 0
                ? 'Tidak ada data yang ditampilkan'
                : `Menampilkan transaksi <strong>${start + 1}</strong> - <strong>${Math.min(end, filtered.length)}</strong> dari <strong>${filtered.length}</strong>`;
        }

        if (!paginationContainer) return;

        let html = `<button class="btn btn--ghost" type="button" data-page="${keuanganPage - 1}" ${keuanganPage === 1 ? 'disabled' : ''}>‹</button>`;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= keuanganPage - 1 && i <= keuanganPage + 1)) {
                html += `<button class="btn ${i === keuanganPage ? 'btn--primary' : 'btn--ghost'}" type="button" data-page="${i}">${i}</button>`;
            } else if (i === keuanganPage - 2 || i === keuanganPage + 2) {
                html += '<span class="pagination-dots">…</span>';
            }
        }

        html += `<button class="btn btn--ghost" type="button" data-page="${keuanganPage + 1}" ${keuanganPage === totalPages ? 'disabled' : ''}>›</button>`;
        paginationContainer.innerHTML = html;
    }

    const comboRegistry = {};

    function syncCombo(selectId) {
        if (comboRegistry[selectId]) comboRegistry[selectId]();
    }

    function initComboSelect(selectId, opts = {}) {
        const select = document.getElementById(selectId);
        const input = document.getElementById('cari_' + selectId);
        const list = document.getElementById('list_' + selectId);
        if (!select || !input || !list) return;

        const placeholderKosong = !!opts.placeholderKosong;
        input.readOnly = true;
        let aktif = -1;

        const teks = o => o.textContent.replace(/\s+/g, ' ').trim();
        const esc = v => String(v).replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));

        if (placeholderKosong) {
            const kosong = Array.from(select.options).find(o => o.value === '');
            if (kosong) input.placeholder = teks(kosong);
        }

        const daftar = () => Array.from(select.options).filter(o => !(placeholderKosong && o.value === ''));

        function sync() {
            const o = select.options[select.selectedIndex];
            input.value = (!o || (placeholderKosong && o.value === '')) ? '' : teks(o);
        }

        function buka() {
            list.innerHTML = daftar().map(o =>
                `<div class="combo-item${o.value === select.value ? ' is-selected' : ''}" data-value="${esc(o.value)}">${esc(teks(o))}</div>`
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
        input.addEventListener('click', () => { if (!list.classList.contains('show')) buka(); });
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

        comboRegistry[selectId] = sync;
        sync();
    }

    function bukaModalTambah() {
        const form = document.getElementById('formTransaksi');
        if (form) form.reset();

        syncCombo('transaksiJenis');

        const tanggal = document.getElementById('transaksiTanggal');
        if (tanggal) {
            const now = new Date();
            const localDate = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().substring(0, 10);
            tanggal.value = localDate;
        }

        bukaModal('modalTransaksi');
    }

    function simpanTransaksi(event) {
        event.preventDefault();

        const jenis = document.getElementById('transaksiJenis').value;
        const jumlah = parseFloat(document.getElementById('transaksiJumlah').value) || 0;

        if (!jenis) {
            showToast('Gagal menyimpan', 'Silakan pilih jenis transaksi.', 'error');
            document.getElementById('cari_transaksiJenis')?.focus();
            return false;
        }

        // --- VALIDASI SALDO DI FRONTEND ---
        if (jenis === 'Pengeluaran' && jumlah > saldoKasSistem) {
            showToast(
                'Gagal menyimpan',
                `Saldo kas tidak mencukupi! Sisa saldo Anda saat ini adalah ${formatRupiah(saldoKasSistem)}.`,
                'error'
            );
            return false; // Hentikan proses
        }
        // ----------------------------------

        const payload = {
            jenis: jenis,
            tanggal: document.getElementById('transaksiTanggal').value,
            kategori: document.getElementById('transaksiKategori').value,
            jumlah: jumlah,
            keterangan: document.getElementById('transaksiKeterangan').value
        };

        fetch(urlStore, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
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
            showToast('Berhasil disimpan', 'Transaksi baru telah ditambahkan.');
            setTimeout(() => window.location.reload(), 800);
        })
        .catch((err) => {
            showToast('Gagal menyimpan', err.message, 'error');
        });

        return false;
    }

    function lihatDetail(item) {
        const badgeClass = item.jenis === 'Pemasukan' ? 'badge--success' : 'badge--danger';

        document.getElementById('detailJenis').innerHTML = `<span class="badge ${badgeClass}">${item.jenis}</span>`;
        document.getElementById('detailTanggal').textContent = formatTanggal(item.tanggal);
        document.getElementById('detailKategori').textContent = item.kategori || '-';
        document.getElementById('detailSumber').textContent = item.otomatis ? 'Otomatis · ' + item.sumber_label : (item.sumber_label || 'Kas Manual');

        const tanda = item.jenis === 'Pemasukan' ? '+ ' : '- ';
        const jumlahElement = document.getElementById('detailJumlah');

        jumlahElement.textContent = tanda + formatRupiah(item.jumlah);
        jumlahElement.classList.remove('amount-in', 'amount-out');
        jumlahElement.classList.add(item.jenis === 'Pemasukan' ? 'amount-in' : 'amount-out');
        document.getElementById('detailKeterangan').textContent = item.keterangan || '-';

        bukaModal('modalDetail');
    }

    let transaksiAkanDihapus = null;

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
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
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
            showToast('Berhasil dihapus', `Transaksi "${kategori}" telah dihapus.`);
            transaksiAkanDihapus = null;
            setTimeout(() => window.location.reload(), 800);
        })
        .catch((err) => {
            showToast('Gagal menghapus', err.message, 'error');
        });
    }
