        // Variabel penampung data item saat modal invoice dibuka
        let activeInvoiceItem = null;

        document.addEventListener('DOMContentLoaded', function() {
            if (window.PAGE_CONFIG?.success) showToast('Berhasil', window.PAGE_CONFIG.success);

            const searchInput = document.getElementById('searchPencairan');
            const table = document.getElementById('pencairanTable');
            const perPageSelect = document.getElementById('perPageSelect');
            const paginationContainer = document.getElementById('paginationButtons');
            const tableInfo = document.getElementById('tableInfoPagination');
            const kosongCari = document.getElementById('pencairanKosongCari');

            let currentPage = 1;

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
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);

                rows.forEach(r => r.style.display = 'none');

                const start = (currentPage - 1) * perPage;
                const end = start + perPage;
                filtered.slice(start, end).forEach(r => r.style.display = '');

                if (tableInfo) {
                    tableInfo.innerHTML = filtered.length === 0 ?
                        'Tidak ada data yang ditampilkan' :
                        `Menampilkan permohonan <strong>${start + 1}</strong> - <strong>${Math.min(end, filtered.length)}</strong> dari <strong>${filtered.length}</strong>`;
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

            initComboWarga();
            initComboSelect('tambahMetode');
            initComboSelect('editStatus');
            initComboSelect('perPageSelect', function() {
                currentPage = 1;
                render();
            });

            render();
        });

        /* Combobox untuk <select> tersembunyi (pilihan tetap, tanpa teks bebas) */
        const comboSelectRegistry = {};

        function syncComboSelect(selectId) {
            if (comboSelectRegistry[selectId]) comboSelectRegistry[selectId]();
        }

        function initComboSelect(selectId, onChange) {
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

            const kosong = Array.from(select.options).find(o => o.value === '');
            if (kosong) input.placeholder = teks(kosong);

            function sync() {
                const o = select.options[select.selectedIndex];
                input.value = (o && o.value !== '') ? teks(o) : '';
            }

            function buka() {
                list.innerHTML = Array.from(select.options).filter(o => o.value !== '').map(o =>
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
                e.preventDefault();
                const item = e.target.closest('.combo-item');
                if (item) pilih(item.dataset.value);
            });

            comboSelectRegistry[selectId] = sync;
            sync();
        }

        function formatStatusRekening(status) {
            return status === 'verified' ? 'Terverifikasi' : 'Belum Verifikasi';
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

            function escapeHtml(s) {
                return String(s).replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));
            }

            function render(keyword) {
                const k = (keyword || '').toLowerCase().trim();
                const hasil = data.filter(d => d.label.toLowerCase().includes(k));
                aktif = -1;

                list.innerHTML = hasil.length ?
                    hasil.map(d => `<div class="combo-item" data-value="${d.value}">${escapeHtml(d.label)}</div>`).join(
                        '') :
                    `<div class="combo-empty">Warga tidak ditemukan</div>`;
            }

            function pilih(value) {
                const d = data.find(x => x.value === String(value));
                if (!d) return;

                select.value = d.value;
                input.value = d.label;
                info.textContent = formatRupiah(d.saldo);

                if (rekeningInfo) {
                    rekeningInfo.textContent = d.bank ?
                        `${d.bank} - ${d.rekening || '-'}` :
                        'Tunai / Lainnya';
                }
                list.classList.remove('show');
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

            // Kembalikan teks input ke warga yang terpilih (kosong jika belum memilih)
            function tutup() {
                list.classList.remove('show');
                const d = data.find(x => x.value === String(select.value));
                input.value = d ? d.label : '';
            }

            input.addEventListener('focus', function() {
                this.select();
                render('');
                list.classList.add('show');
            });

            // Mengetik hanya menyaring daftar; warga baru tersimpan setelah dipilih
            input.addEventListener('input', function() {
                render(this.value);
                list.classList.add('show');
            });

            input.addEventListener('blur', tutup);

            input.addEventListener('keydown', function(e) {
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
                    const items = list.querySelectorAll('.combo-item');
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
                e.preventDefault(); // jaga fokus agar list tidak tertutup saat scroll/klik
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
                syncComboSelect('tambahMetode');

                // Otomatis isi tanggal hari ini dan kunci (readonly)
                const inputTanggal = document.getElementById('tambahTanggal');
                if (inputTanggal) {
                    const today = new Date().toISOString().split('T')[0];
                    inputTanggal.value = today;
                }
            }
            document.getElementById(id).classList.add('active');
        }

        function tutupModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function formatRupiah(angka) {
            return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
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
                <button class="toast-close" type="button" aria-label="Tutup">&times;</button>
            `;

            toast.querySelector('.toast-close').addEventListener('click', () => toast.remove());
            wrap.appendChild(toast);
            setTimeout(() => toast.remove(), 3500);
        }

        function simpanTambah(event) {
            event.preventDefault();
            const selectWarga = document.getElementById('tambahWarga');
            const idWarga = selectWarga.value;

            if (!idWarga) {
                showToast('Gagal menyimpan', 'Silakan pilih warga terlebih dahulu.', 'error');
                document.getElementById('cariWarga').focus();
                return false;
            }

            const selectedOption = selectWarga.options[selectWarga.selectedIndex];
            const saldoTersedia = Number(selectedOption.dataset.saldo || 0);
            const jumlahDitarik = Number(document.getElementById('tambahJumlah').value);

            if (jumlahDitarik > saldoTersedia) {
                showToast('Gagal menyimpan', 'Jumlah penarikan melebihi saldo tersedia warga!', 'error');
                document.getElementById('tambahJumlah').focus();
                return false;
            }

            if (!document.getElementById('tambahMetode').value) {
                showToast('Gagal menyimpan', 'Silakan pilih metode pencairan.', 'error');
                document.getElementById('cari_tambahMetode')?.focus();
                return false;
            }

            const payload = {
                id_warga: idWarga,
                jumlah: jumlahDitarik,
                metode_transfer: document.getElementById('tambahMetode').value,
                tanggal_pencairan: document.getElementById('tambahTanggal').value,
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
                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        throw new Error(err.message || 'Gagal menyimpan permohonan.');
                    }
                    return res.json();
                })
                .then(() => {
                    tutupModal('modalTambah');
                    showToast('Berhasil', 'Permohonan pencairan telah dibuat.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => showToast('Gagal menyimpan', err.message, 'error'));

            return false;
        }

        function bukaInvoiceCard(item) {
            activeInvoiceItem = item; // Simpan data item saat ini untuk kebutuhan WA

            document.getElementById('invId').textContent = `#INV-${item.id_pencairan}`;
            document.getElementById('invTanggal').textContent = item.tanggal_pencairan ? new Date(item.tanggal_pencairan)
                .toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                }) : '-';

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

        // Fungsi untuk mengirim pesan WhatsApp berisi detail invoice pencairan
        function kirimInvoiceWhatsApp() {
            if (!activeInvoiceItem) {
                showToast('Gagal', 'Data invoice tidak ditemukan.', 'error');
                return;
            }

            const noHp = activeInvoiceItem.warga?.no_hp;
            if (!noHp) {
                showToast('Gagal', 'Nomor HP warga tidak tersedia.', 'error');
                return;
            }

            let formattedPhone = noHp.replace(/\D/g, '');
            if (formattedPhone.startsWith('0')) {
                formattedPhone = '62' + formattedPhone.slice(1);
            }

            const namaWarga = activeInvoiceItem.warga?.nama || 'Warga';
            const idInv = `#INV-${activeInvoiceItem.id_pencairan}`;
            const nominal = formatRupiah(activeInvoiceItem.jumlah);
            const status = activeInvoiceItem.status.toUpperCase();
            const metodePencairan = activeInvoiceItem.warga?.nama_bank_ewallet ? activeInvoiceItem.warga.nama_bank_ewallet :
                'Tunai';

            const tanggal = activeInvoiceItem.tanggal_pencairan ? new Date(activeInvoiceItem.tanggal_pencairan)
                .toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                }) : '-';

            // Menggunakan Unicode Escape Sequence untuk mencegah error encoding emoji
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
            document.getElementById('editStatusRekening').value = item.warga?.nama_bank_ewallet ? formatStatusRekening(item
                .warga?.status_rekening) : '-';
            document.getElementById('editStatus').value = item.status;
            syncComboSelect('editStatus');

            bukaModal('modalProses');
        }

        function simpanProses(event) {
            event.preventDefault();
            const id = document.getElementById('editId').value;
            const status = document.getElementById('editStatus').value;
            const baseUrl = window.PAGE_CONFIG.routes.index;

            fetch(`${baseUrl}/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status
                    })
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
                    showToast('Berhasil disimpan', 'Status pencairan berhasil diperbarui.');
                    setTimeout(() => window.location.reload(), 800);
                })
                .catch((err) => showToast('Gagal menyimpan', err.message, 'error'));

            return false;
        }
