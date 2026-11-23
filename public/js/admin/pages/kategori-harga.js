const { riwayatData, daftarKategori, urlKategoriStore, urlKategoriUpdate, urlKategoriHapus, flashSuccess } = window.KategoriHargaPageConfig;

        const BULAN_SINGKAT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const FIELD_KATEGORI = ['id_induk', 'nama_kategori', 'satuan', 'harga_awal', 'tanggal_awal'];
        const FIELD_HARGA = ['harga_satuan', 'tanggal_berlaku'];

        let hargaContext = null;
        let hapusContext = null;

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
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate())
                .padStart(2, '0');
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

            const iconPath = type === 'error' ?
                '<path d="M18 6 6 18M6 6l12 12"></path>' :
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

        function clearFormErrors(fields) {
            fields.forEach(f => {
                const el = document.getElementById('err_' + f);
                if (el) el.textContent = '';
                const input = document.getElementById(f);
                if (input) input.classList.remove('is-invalid');
            });
        }

        function tampilkanFormErrors(errors, fields) {
            fields.forEach(f => {
                if (errors && errors[f]) {
                    const el = document.getElementById('err_' + f);
                    const input = document.getElementById(f);
                    if (el) el.textContent = errors[f][0];
                    if (input) input.classList.add('is-invalid');
                }
            });
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
                const e = new Error(err.message || 'Terjadi kesalahan pada server.');
                e.errors = err.errors || null;
                throw e;
            }
            return res.json().catch(() => ({}));
        }

        /* ---------- COMBOBOX (menggantikan <select> bawaan browser) ---------- */
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
            const daftar = () => Array.from(select.options).filter(o => !o.disabled)
                .map(o => ({
                    value: o.value,
                    label: teks(o)
                }));

            function sync() {
                const o = select.options[select.selectedIndex];
                input.value = o ? teks(o) : '';
                input.disabled = select.disabled;
            }

            function render(keyword) {
                const k = (keyword || '').toLowerCase().trim();
                const hasil = daftar().filter(d => d.label.toLowerCase().includes(k));
                aktif = -1;
                list.innerHTML = hasil.length ?
                    hasil.map(d =>
                        `<div class="combo-item${d.value === select.value ? ' is-selected' : ''}" data-value="${escapeHtml(d.value)}">${escapeHtml(d.label)}</div>`
                    ).join('') :
                    '<div class="combo-empty">Data tidak ditemukan</div>';
            }

            function buka(keyword) {
                render(keyword);
                list.classList.add('show');
                const terpilih = list.querySelector('.is-selected');
                if (terpilih) terpilih.scrollIntoView({
                    block: 'nearest'
                });
            }

            function tutup() {
                list.classList.remove('show');
                sync(); // ketikan pencarian dibuang, tampilkan pilihan yang aktif
            }

            function pilih(value) {
                const berubah = select.value !== String(value);
                select.value = value;
                tutup();
                if (berubah) select.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            }

            function sorot(arah) {
                const items = list.querySelectorAll('.combo-item');
                if (!items.length) return;
                aktif = (aktif + arah + items.length) % items.length;
                items.forEach((el, i) => el.classList.toggle('is-active', i === aktif));
                items[aktif].scrollIntoView({
                    block: 'nearest'
                });
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

            input.addEventListener('keydown', function(e) {
                const items = list.querySelectorAll('.combo-item');
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!list.classList.contains('show')) buka(readonly ? '' : input.value);
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
                    e.stopPropagation(); // jangan ikut menutup modal
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
                e.preventDefault(); // pertahankan fokus agar list tidak tertutup saat scroll/klik
                const item = e.target.closest('.combo-item');
                if (item) pilih(item.dataset.value);
            });

            comboRegistry[selectId] = {
                sync
            };
            sync();
        }

        /* ---------- REKAP BULANAN ---------- */
        function hargaPadaBulan(id, ym) {
            const [y, m] = ym.split('-').map(Number);
            const akhir = new Date(Date.UTC(y, m, 0)).toISOString().substring(0, 10);
            return (riwayatData[id] || [])
                .filter(h => h.tanggal_raw && h.tanggal_raw <= akhir)
                .sort((a, b) => b.tanggal_raw.localeCompare(a.tanggal_raw) || b.id_harga - a.id_harga)[0] || null;
        }

        function bukaModalBulanan() {
            const sel = document.getElementById('rekapTahun');
            const now = new Date().getFullYear();
            let min = now;

            Object.values(riwayatData).forEach(list => (list || []).forEach(h => {
                if (h.tanggal_raw) min = Math.min(min, parseInt(h.tanggal_raw.substring(0, 4), 10));
            }));

            sel.innerHTML = '';
            for (let y = now; y >= min; y--) {
                sel.insertAdjacentHTML('beforeend', `<option value="${y}">${y}</option>`);
            }
            sel.value = now;
            syncCombo('rekapTahun');

            renderRekap();
            bukaModal('modalBulanan');
        }

        function geserTahun(delta) {
            const sel = document.getElementById('rekapTahun');
            const idx = sel.selectedIndex - delta;
            if (idx >= 0 && idx < sel.options.length) {
                sel.selectedIndex = idx;
                syncCombo('rekapTahun');
                renderRekap();
            }
        }

        function renderRekap() {
            const tahun = parseInt(document.getElementById('rekapTahun').value, 10);
            const head = document.getElementById('rekapHead');
            const body = document.getElementById('rekapBody');

            head.innerHTML = `
                <tr>
                    <th rowspan="2" class="col-nama">Jenis Sampah</th>
                    <th colspan="12">Harga Sampah Tahun ${tahun}</th>
                </tr>
                <tr>${BULAN_SINGKAT.map(b => `<th>${b}</th>`).join('')}</tr>
            `;

            if (daftarKategori.length === 0) {
                body.innerHTML =
                    '<tr><td colspan="13" class="text-center empty-desc">Belum ada kategori.</td></tr>';
                return;
            }

            body.innerHTML = daftarKategori.map(k => {
                let prev = null;
                const cells = BULAN_SINGKAT.map((_, i) => {
                    const ym = tahun + '-' + String(i + 1).padStart(2, '0');
                    const h = hargaPadaBulan(k.id, ym);
                    const val = h ? h.harga : null;
                    let berubah = val !== null && prev !== null && val !== prev;

                    if (i === 0 && val !== null) {
                        const hp = hargaPadaBulan(k.id, (tahun - 1) + '-12');
                        berubah = !!hp && hp.harga !== val;
                    }
                    prev = val;

                    if (val === null) return '<td class="kosong">–</td>';
                    return `<td class="${berubah ? 'berubah' : ''}">${Number(val).toLocaleString('id-ID')}</td>`;
                }).join('');

                const nama = escapeHtml(k.nama);
                if (!k.sub) {
                    return `<tr class="baris-induk"><td class="col-nama">${nama}</td>${cells}</tr>`;
                }
                return `<tr><td class="col-nama"><span class="sub-arrow">↳</span> ${nama} <small class="muted">${escapeHtml(k.satuan ?? '')}</small></td>${cells}</tr>`;
            }).join('');
        }

        /* ---------- KATEGORI: TAMBAH / EDIT ---------- */
        function resetOpsiInduk() {
            document.querySelectorAll('#id_induk option').forEach(o => o.disabled = false);
            document.getElementById('id_induk').disabled = false;
        }

        function bukaModalTambahKategori() {
            document.getElementById('formKategori').reset();
            document.getElementById('kategoriId').value = '';
            document.getElementById('satuan').value = 'kg';
            clearFormErrors(FIELD_KATEGORI);
            resetOpsiInduk();
            syncCombo('id_induk');
            syncCombo('satuan');

            document.getElementById('tanggal_awal').value = tanggalHariIni();
            document.getElementById('blokHargaAwal').style.display = '';
            document.getElementById('blokTanggalAwal').style.display = '';
            document.getElementById('harga_awal').required = true;
            document.getElementById('tanggal_awal').required = true;

            document.getElementById('modalKategoriTitleText').textContent = 'Tambah Kategori';
            document.getElementById('kategoriSubmitBtn').textContent = 'Simpan Kategori';
            bukaModal('modalKategori');
        }

        function editKategoriModal(kategori) {
            document.getElementById('formKategori').reset();
            document.getElementById('kategoriId').value = kategori.id_kategori;
            clearFormErrors(FIELD_KATEGORI);
            resetOpsiInduk();

            // Kategori tidak boleh menjadi induk dirinya sendiri
            const selfOpt = document.querySelector(`#id_induk option[value="${kategori.id_kategori}"]`);
            if (selfOpt) selfOpt.disabled = true;

            // Kategori yang sudah punya sub tidak boleh dijadikan sub kategori (maks. 2 tingkat)
            const selInduk = document.getElementById('id_induk');
            selInduk.value = kategori.id_induk || '';
            selInduk.disabled = !!kategori.punya_anak;

            document.getElementById('nama_kategori').value = kategori.nama_kategori || '';
            document.getElementById('satuan').value = kategori.satuan || 'kg';
            syncCombo('id_induk');
            syncCombo('satuan');

            document.getElementById('blokHargaAwal').style.display = 'none';
            document.getElementById('blokTanggalAwal').style.display = 'none';
            document.getElementById('harga_awal').required = false;
            document.getElementById('tanggal_awal').required = false;

            document.getElementById('modalKategoriTitleText').textContent = 'Edit Kategori';
            document.getElementById('kategoriSubmitBtn').textContent = 'Simpan Perubahan';
            bukaModal('modalKategori');
        }

        function simpanKategori(event) {
            event.preventDefault();
            const id = document.getElementById('kategoriId').value;
            const btn = document.getElementById('kategoriSubmitBtn');
            clearFormErrors(FIELD_KATEGORI);

            const selInduk = document.getElementById('id_induk');
            const payload = {
                // select yang disabled tetap mengirim nilai aslinya
                id_induk: selInduk.value || null,
                nama_kategori: document.getElementById('nama_kategori').value.trim(),
                satuan: document.getElementById('satuan').value,
            };

            if (!id) {
                payload.harga_satuan = document.getElementById('harga_awal').value;
                payload.tanggal_berlaku = document.getElementById('tanggal_awal').value;
            }

            const url = id ? urlKategoriUpdate.replace(':id', id) : urlKategoriStore;
            setBusy(btn, true);

            kirimJson(url, id ? 'PUT' : 'POST', payload)
                .then(() => {
                    tutupModal('modalKategori');
                    showToast('Berhasil disimpan', id ? 'Perubahan kategori telah tersimpan.' :
                        'Kategori baru telah ditambahkan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    if (err.errors) {
                        const e = {
                            ...err.errors
                        };
                        if (e.harga_satuan) e.harga_awal = e.harga_satuan;
                        if (e.tanggal_berlaku) e.tanggal_awal = e.tanggal_berlaku;
                        tampilkanFormErrors(e, FIELD_KATEGORI);
                    }
                    showToast('Gagal menyimpan', err.message, 'error');
                    setBusy(btn, false);
                });

            return false;
        }

        /* ---------- HARGA ---------- */
        function kelolaHarga(kategori) {
            hargaContext = kategori;
            document.getElementById('hargaNamaKategori').textContent = kategori.nama_kategori;
            document.getElementById('hargaSatuanLabel').textContent = kategori.satuan || 'satuan';
            document.getElementById('formHarga').reset();
            document.getElementById('tanggal_berlaku').value = tanggalHariIni();
            clearFormErrors(FIELD_HARGA);
            setBusy(document.getElementById('hargaSubmitBtn'), false);

            renderRiwayatHarga(kategori.id_kategori);
            bukaModal('modalHarga');
        }

        function renderRiwayatHarga(idKategori) {
            const tbody = document.getElementById('hargaTableBody');
            const riwayat = riwayatData[idKategori] || [];

            if (riwayat.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="3" class="empty-desc text-center">Belum ada riwayat harga.</td></tr>';
                return;
            }

            tbody.innerHTML = riwayat.map((h, i) => `
                <tr>
                    <td>${escapeHtml(h.tanggal)}</td>
                    <td><strong class="saldo">Rp ${Number(h.harga).toLocaleString('id-ID')}</strong></td>
                    <td>
                        <button class="icon-btn icon-btn--danger icon-btn--sm" title="Hapus" type="button"
                            data-index="${i}">
                            <svg viewBox="0 0 24 24">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                <path d="M10 11v6M14 11v6"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
            `).join('');

            tbody.querySelectorAll('button[data-index]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const item = riwayat[parseInt(btn.dataset.index, 10)];
                    hapusHarga(item.delete_url);
                });
            });
        }

        function tambahHarga(event) {
            event.preventDefault();
            if (!hargaContext) return false;

            const btn = document.getElementById('hargaSubmitBtn');
            clearFormErrors(FIELD_HARGA);

            const payload = {
                harga_satuan: document.getElementById('harga_satuan').value,
                tanggal_berlaku: document.getElementById('tanggal_berlaku').value,
            };

            setBusy(btn, true);

            kirimJson(hargaContext.store_url, 'POST', payload)
                .then(() => {
                    showToast('Berhasil disimpan', 'Harga baru telah ditambahkan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    if (err.errors) tampilkanFormErrors(err.errors, FIELD_HARGA);
                    showToast('Gagal menyimpan', err.message, 'error');
                    setBusy(btn, false);
                });

            return false;
        }

        /* ---------- HAPUS ---------- */
        function hapusHarga(url) {
            hapusContext = {
                type: 'harga',
                url
            };
            document.getElementById('hapusTitle').textContent = 'Hapus Data Harga?';
            document.getElementById('hapusText').textContent =
                'Anda akan menghapus data harga ini. Tindakan ini tidak dapat dibatalkan.';
            setBusy(document.getElementById('hapusConfirmBtn'), false);
            bukaModal('modalHapus');
        }

        function hapusKategori(kategori) {
            hapusContext = {
                type: 'kategori',
                url: urlKategoriHapus.replace(':id', kategori.id_kategori),
            };

            document.getElementById('hapusTitle').textContent = 'Hapus Kategori Sampah?';
            document.getElementById('hapusText').innerHTML =
                `Anda akan menghapus kategori <strong>${escapeHtml(kategori.nama_kategori)}</strong>. Tindakan ini tidak dapat dibatalkan.`;
            setBusy(document.getElementById('hapusConfirmBtn'), false);
            bukaModal('modalHapus');
        }

        function konfirmasiHapus() {
            if (!hapusContext) return;
            const btn = document.getElementById('hapusConfirmBtn');
            setBusy(btn, true);

            kirimJson(hapusContext.url, 'DELETE')
                .then(() => {
                    tutupModal('modalHapus');
                    showToast('Berhasil dihapus', 'Data telah dihapus.');
                    hapusContext = null;
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menghapus', err.message, 'error');
                    setBusy(btn, false);
                });
        }

        /* ---------- TABEL: PENCARIAN & PAGINASI (per grup induk) ---------- */
        document.addEventListener('DOMContentLoaded', function() {
            if (flashSuccess) showToast('Berhasil', flashSuccess);

            initCombo('id_induk');
            initCombo('satuan');
            initCombo('perPageSelect', {
                readonly: true
            });
            initCombo('rekapTahun', {
                readonly: true
            });

            const searchInput = document.getElementById('searchKategori');
            const table = document.getElementById('kategoriTable');
            const perPageSelect = document.getElementById('perPageSelect');
            const paginationContainer = document.getElementById('paginationButtons');
            const tableInfo = document.getElementById('tableInfoPagination');
            const kosongCari = document.getElementById('kategoriKosongCari');

            let currentPage = 1;

            // Kelompokkan baris: induk + sub-nya selalu satu halaman
            const groups = [];
            if (table) {
                const map = new Map();
                table.querySelectorAll('tbody tr[data-row-kategori]').forEach(row => {
                    const key = row.dataset.group;
                    if (!map.has(key)) {
                        const g = {
                            rows: []
                        };
                        map.set(key, g);
                        groups.push(g);
                    }
                    map.get(key).rows.push(row);
                });
            }

            function render() {
                if (!table) return;

                const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const filtered = groups.filter(g => g.rows.some(r => r.textContent.toLowerCase().includes(
                    keyword)));

                if (kosongCari) {
                    kosongCari.style.display = (groups.length > 0 && filtered.length === 0) ? '' : 'none';
                }

                const perPage = parseInt(perPageSelect ? perPageSelect.value : 10, 10);
                const totalPages = Math.ceil(filtered.length / perPage) || 1;
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);

                groups.forEach(g => g.rows.forEach(r => r.style.display = 'none'));

                const start = (currentPage - 1) * perPage;
                const end = start + perPage;
                filtered.slice(start, end).forEach(g => g.rows.forEach(r => r.style.display = ''));

                if (tableInfo) {
                    tableInfo.innerHTML = filtered.length === 0 ?
                        'Tidak ada data yang ditampilkan' :
                        `Menampilkan kategori induk <strong>${start + 1}</strong> - <strong>${Math.min(end, filtered.length)}</strong> dari <strong>${filtered.length}</strong>`;
                }

                if (!paginationContainer) return;

                let html =
                    `<button class="btn btn--ghost" type="button" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>‹</button>`;

                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                        html +=
                            `<button class="btn ${i === currentPage ? 'btn--primary' : 'btn--ghost'}" type="button" data-page="${i}">${i}</button>`;
                    } else if (i === currentPage - 2 || i === currentPage + 2) {
                        html += '<span class="pagination-dots">…</span>';
                    }
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
                    render();
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

            render();

            // Tutup modal: klik overlay & tombol Escape
            document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
                overlay.addEventListener('mousedown', function(e) {
                    if (e.target === overlay) overlay.classList.remove('active');
                });
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal-overlay.active').forEach(o => o.classList.remove(
                        'active'));
                }
            });
        });
