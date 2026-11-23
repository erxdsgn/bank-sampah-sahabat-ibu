        const DAFTAR_BANK_EWALLET = [
            'Bank BCA', 'Bank Mandiri', 'Bank BRI', 'Bank BNI', 'Bank Syariah Indonesia',
            'Bank CIMB Niaga', 'Bank Danamon', 'Bank Permata', 'Bank BTN', 'Bank Mega',
            'Bank OCBC NISP', 'Bank Panin', 'Bank Maybank Indonesia', 'Bank BTPN',
            'Bank Jago', 'SeaBank', 'Bank Neo Commerce', 'Bank DKI', 'Bank Jatim',
            'Bank BJB', 'GoPay', 'OVO', 'DANA', 'ShopeePay', 'LinkAja', 'Jenius'
        ];

        document.addEventListener('DOMContentLoaded', function() {
            if (window.PAGE_CONFIG?.success) showToast('Berhasil', window.PAGE_CONFIG.success);

            const searchInput = document.getElementById('searchWarga');
            const table = document.getElementById('wargaTable');
            const perPageSelect = document.getElementById('perPageSelect');
            const paginationContainer = document.getElementById('paginationButtons');
            const tableInfo = document.getElementById('tableInfoPagination');

            let currentPage = 1;

            function applyFilters() {
                const keyword = (searchInput?.value || '').toLowerCase().trim();
                const activePill = document.querySelector('.fpill.active');
                const statusFilter = activePill ? activePill.dataset.filter : 'all';

                const allRows = Array.from(table.querySelectorAll('tbody tr[data-status]'));
                const filtered = allRows.filter(function(row) {
                    const matchSearch = !keyword || row.textContent.toLowerCase().includes(keyword);
                    const matchStatus = statusFilter === 'all' || row.dataset.status === statusFilter;
                    return matchSearch && matchStatus;
                });

                const perPage = parseInt(perPageSelect ? perPageSelect.value : 10, 10);
                const totalPages = Math.ceil(filtered.length / perPage) || 1;
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);

                allRows.forEach(r => r.style.display = 'none');

                const start = (currentPage - 1) * perPage;
                const end = start + perPage;
                filtered.slice(start, end).forEach(r => r.style.display = '');

                if (tableInfo) {
                    tableInfo.innerHTML = filtered.length === 0 ?
                        'Tidak ada data yang ditampilkan' :
                        `Menampilkan warga <strong>${start + 1}</strong> - <strong>${Math.min(end, filtered.length)}</strong> dari <strong>${filtered.length}</strong>`;
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

            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    currentPage = 1;
                    applyFilters();
                });
            }

            document.querySelectorAll('.fpill').forEach(function(pill) {
                pill.addEventListener('click', function() {
                    document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                    currentPage = 1;
                    applyFilters();
                });
            });

            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    currentPage = 1;
                    applyFilters();
                });
            }

            if (paginationContainer) {
                paginationContainer.addEventListener('click', function(e) {
                    const btn = e.target.closest('button[data-page]');
                    if (!btn || btn.disabled) return;
                    currentPage = parseInt(btn.dataset.page, 10);
                    applyFilters();
                });
            }

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

            initComboBank('tambahBankInput', 'tambahBank', 'listBankTambah', 'comboBankTambah');
            initComboBank('editBankInput', 'editBank', 'listBankEdit', 'comboBankEdit');
            initSegStatusRekening();
            initComboSelect('perPageSelect');

            applyFilters();
        });

        function bukaModal(id) {
            if (id === 'modalTambah') {
                const form = document.getElementById('formTambahWarga');
                if (form) form.reset();
                resetComboBank('tambahBankInput', 'tambahBank');
            }
            document.getElementById(id).classList.add('active');
        }

        function tutupModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        
        // FUNGSI AJAX UNTUK MODAL LEADERBOARD
        function bukaModalLeaderboard() {
            loadLeaderboardPopup('season_ini');
        }

        function loadLeaderboardPopup(periode) {
            fetch(`${window.PAGE_CONFIG.routes.leaderboard}?periode=${periode}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(async res => {
                    if (!res.ok) {
                        const errJson = await res.json().catch(() => ({}));
                        throw new Error(errJson.message || 'Gagal memuat dari server');
                    }
                    return res.json();
                })
                .then(data => {
                    document.getElementById('modalLeaderboardContent').innerHTML = data.html;
                    document.getElementById('modalLeaderboard').classList.add('active');
                })
                .catch(err => {
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
                year: 'numeric'
            });
        }

        function formatStatusRekening(status) {
            return status === 'verified' ? 'Terverifikasi' : 'Belum Verifikasi';
        }

        function initComboBank(inputId, hiddenId, listId, wrapId) {
            const input = document.getElementById(inputId);
            const hidden = document.getElementById(hiddenId);
            const list = document.getElementById(listId);

            if (!input || !hidden || !list) return;

            let aktif = -1;

            function escapeHtml(s) {
                return String(s).replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));
            }

            function sanitize(v) {
                return v.replace(/[^a-zA-Z\s.\-]/g, '');
            }

            function render(keyword) {
                const k = (keyword || '').toLowerCase().trim();
                const hasil = DAFTAR_BANK_EWALLET.filter(b => b.toLowerCase().includes(k));
                aktif = -1;

                list.innerHTML = hasil.length ?
                    hasil.map(b => `<div class="combo-item" data-value="${escapeHtml(b)}">${escapeHtml(b)}</div>`).join(
                        '') :
                    '<div class="combo-empty">Tekan Enter untuk pakai nama ini</div>';
            }

            function pilih(value) {
                hidden.value = value;
                input.value = value;
                list.classList.remove('show');
            }

            input.addEventListener('focus', function() {
                render(this.value);
                list.classList.add('show');
            });

            input.addEventListener('input', function() {
                const bersih = sanitize(this.value);
                if (bersih !== this.value) this.value = bersih;
                hidden.value = this.value;
                render(this.value);
                list.classList.add('show');
            });

            input.addEventListener('blur', function() {
                setTimeout(() => {
                    hidden.value = sanitize(input.value.trim());
                }, 150);
            });

            function sorot(arah) {
                const items = list.querySelectorAll('.combo-item');
                if (!items.length) return;
                aktif = (aktif + arah + items.length) % items.length;
                items.forEach((el, i) => el.classList.toggle('is-active', i === aktif));
                items[aktif].scrollIntoView({
                    block: 'nearest'
                });
            }

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
                    e.preventDefault();
                    if (list.classList.contains('show') && aktif > -1 && items[aktif]) {
                        pilih(items[aktif].dataset.value);
                    } else {
                        hidden.value = sanitize(this.value.trim());
                        list.classList.remove('show');
                    }
                } else if (e.key === 'Escape' && list.classList.contains('show')) {
                    e.stopPropagation();
                    list.classList.remove('show');
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

            document.addEventListener('click', function(e) {
                if (!e.target.closest('#' + wrapId)) list.classList.remove('show');
            });
        }

        const comboSelectRegistry = {};

        function syncComboSelect(selectId) {
            if (comboSelectRegistry[selectId]) comboSelectRegistry[selectId]();
        }

        function initComboSelect(selectId) {
            const select = document.getElementById(selectId);
            const input = document.getElementById('cari_' + selectId);
            const list = document.getElementById('list_' + selectId);
            if (!select || !input || !list) return;

            input.readOnly = true;
            let aktif = -1;

            const teks = o => o.textContent.replace(/\s+/g, ' ').trim();
            const esc = v => String(v).replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));

            function sync() {
                const o = select.options[select.selectedIndex];
                input.value = o ? teks(o) : '';
            }

            function buka() {
                list.innerHTML = Array.from(select.options).map(o =>
                    `<div class="combo-item${o.value === select.value ? ' is-selected' : ''}" data-value="${esc(o.value)}">${esc(teks(o))}</div>`
                ).join('');
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
                if (berubah) select.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
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

            comboSelectRegistry[selectId] = sync;
            sync();
        }

        function resetComboBank(inputId, hiddenId) {
            const input = document.getElementById(inputId);
            const hidden = document.getElementById(hiddenId);
            if (input) input.value = '';
            if (hidden) hidden.value = '';
        }

        function showToast(title, text, type = 'success') {
            const wrap = document.getElementById('toastWrap');
            const toast = document.createElement('div');
            toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');

            const iconPath = type === 'error' ?
                '<path d="M18 6 6 18M6 6l12 12"></path>' :
                '<path d="M20 6 9 17l-5-5"></path>';

            toast.innerHTML = `
                <div class="toast-icon"><svg viewBox="0 0 24 24">${iconPath}</svg></div>
                <div class="toast-body">
                    <p class="toast-title">${title}</p>
                    <p class="toast-text">${text}</p>
                </div>
                <button class="toast-close" type="button">&times;</button>
            `;

            function hapusToast() {
                toast.classList.add('toast--leaving');
                setTimeout(() => toast.remove(), 180);
            }

            toast.querySelector('.toast-close').addEventListener('click', hapusToast);
            wrap.appendChild(toast);
            setTimeout(hapusToast, 3500);
        }

        // SIMPAN TAMBAH WARGA DENGAN CEK EMAIL
        function simpanTambahWarga(event) {
            event.preventDefault();
            const emailInput = document.getElementById('tambahEmail').value.trim();
            if (!emailInput) {
                showToast('Peringatan', 'Email wajib diisi untuk melakukan verifikasi/pendaftaran akun.', 'error');
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

            fetch(`${window.PAGE_CONFIG.routes.store}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
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
                    resetComboBank('tambahBankInput', 'tambahBank');
                    showToast('Berhasil', 'Data warga baru telah ditambahkan.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menyimpan', err.message, 'error');
                });

            return false;
        }

        // FUNGSI DETAIL WARGA DENGAN TEKS BIASA UNTUK KELOMPOK
        function lihatWarga(warga) {
            const nama = warga.nama || '-';
            document.getElementById('detailAvatar').textContent = nama.trim().charAt(0).toUpperCase();
            document.getElementById('detailNama').textContent = nama;
            document.getElementById('detailNik').textContent = warga.nik || '-';

            // Menampilkan kelompok sebagai teks biasa
            let labelKelompok = '-';
            if (warga.kelompok === 'A') {
                labelKelompok = 'A (Perumahan)';
            } else if (warga.kelompok === 'B') {
                labelKelompok = 'B (Luar Perumahan)';
            }

            const elKelompok = document.getElementById('detailKelompok');
            if (elKelompok) {
                elKelompok.textContent = labelKelompok;
            }

            document.getElementById('detailHp').textContent = warga.no_hp || '-';
            document.getElementById('detailKeluarga').textContent = (warga.jumlah_anggota_keluarga ?? 0) + ' orang';
            document.getElementById('detailSaldo').textContent = formatRupiah(warga.saldo);
            document.getElementById('detailTanggal').textContent = formatTanggal(warga.tanggal_daftar);
            document.getElementById('detailAlamat').textContent = warga.alamat || '-';
            document.getElementById('detailBank').textContent = warga.nama_bank_ewallet || '-';
            document.getElementById('detailNoRekening').textContent = warga.nomor_rekening || '-';
            document.getElementById('detailPemilikRekening').textContent = warga.nama_pemilik_rekening || '-';
            document.getElementById('detailStatusRekening').textContent = warga.nama_bank_ewallet ? formatStatusRekening(
                warga.status_rekening) : '-';

            bukaModal('modalDetail');
        }

        function escHtmlWarga(v) {
            return String(v ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
        }

        function setSegStatus(value) {
            document.getElementById('editStatusRekening').value = value;
            document.querySelectorAll('#segStatusRekening .seg-btn').forEach(function(b) {
                b.classList.toggle('is-active', b.dataset.value === value);
            });
        }

        function initSegStatusRekening() {
            document.getElementById('segStatusRekening').addEventListener('click', function(e) {
                const btn = e.target.closest('.seg-btn');
                if (btn) setSegStatus(btn.dataset.value);
            });
        }

        function editWarga(warga) {
            document.getElementById('editId').value = warga.id_warga;
            setSegStatus(warga.status_rekening === 'verified' ? 'verified' : 'unverified');

            const adaRekening = !!warga.nama_bank_ewallet;
            const baris = (label, val) =>
                `<div class="er-row"><span class="er-label">${label}</span><span class="er-value">${escHtmlWarga(val || '-')}</span></div>`;

            document.getElementById('editRingkasan').innerHTML =
                `<div class="er-nama">${escHtmlWarga(warga.nama || '-')}</div>` +
                baris('Bank / E-Wallet', warga.nama_bank_ewallet) +
                baris('No. Rekening', warga.nomor_rekening) +
                baris('Pemilik', warga.nama_pemilik_rekening) +
                (adaRekening ? '' :
                    '<div class="er-note">Warga ini belum mengisi data rekening, status belum bisa diubah.</div>');

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
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
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

        let wargaAkanDihapus = null;

        function hapusWarga(warga) {
            wargaAkanDihapus = warga;
            document.getElementById('hapusNama').textContent = warga.nama || '-';
            document.getElementById('hapusNik').textContent = warga.nik || '-';
            bukaModal('modalHapus');
        }

        function konfirmasiHapus() {
            if (!wargaAkanDihapus) return;

            const id = wargaAkanDihapus.id_warga;
            const namaDihapus = wargaAkanDihapus.nama || 'Warga';

            fetch(`/admin/pages/warga/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(async (res) => {
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Gagal menghapus data.');
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalHapus');
                    showToast('Data warga berhasil dihapus.', namaDihapus + ' telah dihapus dari data warga.');
                    wargaAkanDihapus = null;
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => {
                    showToast('Gagal menghapus', err.message, 'error');
                });
        }
