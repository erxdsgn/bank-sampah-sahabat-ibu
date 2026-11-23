document.addEventListener('DOMContentLoaded', function() {

                if (window.KatalogPageConfig.flashSuccess) showToast('Berhasil', window.KatalogPageConfig.flashSuccess);

                const searchInput = document.getElementById('searchKatalog');

                if (searchInput) {
                    searchInput.addEventListener('keyup', function() {
                        filterKatalog(this.value, true);
                    });
                }

                const perPageSelect = document.getElementById('perPageSelect');
                const paginationContainer = document.getElementById('paginationButtons');

                initComboSelect('perPageSelect');

                if (perPageSelect) {
                    perPageSelect.addEventListener('change', function() {
                        filterKatalog(undefined, true);
                    });
                }

                if (paginationContainer) {
                    paginationContainer.addEventListener('click', function(e) {
                        const btn = e.target.closest('button[data-page]');
                        if (!btn || btn.disabled) return;
                        window.katalogCurrentPage = parseInt(btn.dataset.page, 10);
                        filterKatalog();
                    });
                }

                filterKatalog(undefined, true);

                // Tutup modal saat klik area gelap di luar box
                document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
                    overlay.addEventListener('click', function(e) {
                        if (e.target === overlay) {
                            overlay.classList.remove('active');
                        }
                    });
                });

                // Tutup modal dengan tombol Escape
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        document.querySelectorAll('.modal-overlay.active').forEach(function(overlay) {
                            overlay.classList.remove('active');
                        });
                    }
                });

            });


            /* Helper: buka / tutup modal */

            function bukaModal(id) {
                document.getElementById(id).classList.add('active');
            }

            function tutupModal(id) {
                document.getElementById(id).classList.remove('active');
            }

            function formatRupiah(angka) {
                return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
            }

            function escapeHtml(v) {
                return String(v).replace(/[&<>"']/g, c => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c]));
            }


            /* Toast notifikasi */

            function showToast(title, text, type = 'success') {

                const wrap = document.getElementById('toastWrap');
                const toast = document.createElement('div');

                toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');

                const iconPath = type === 'error' ?
                    '<path d="M18 6 6 18M6 6l12 12"></path>' :
                    '<path d="M20 6 9 17l-5-5"></path>';

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


            /* Pencarian produk + pagination */

            window.katalogCurrentPage = 1;

            function filterKatalog(keyword, resetPage) {

                const searchInput = document.getElementById('searchKatalog');
                const perPageSelect = document.getElementById('perPageSelect');
                const paginationContainer = document.getElementById('paginationButtons');
                const tableInfo = document.getElementById('tableInfoPagination');

                const k = (keyword !== undefined ? keyword : (searchInput ? searchInput.value : ''))
                    .toLowerCase().trim();

                const items = Array.from(document.querySelectorAll('#catalogGrid .catalog-item'));

                const filtered = items.filter(function(item) {
                    return item.dataset.nama.includes(k);
                });

                const perPage = parseInt(perPageSelect ? perPageSelect.value : 8, 10) || 8;
                const totalPages = Math.ceil(filtered.length / perPage) || 1;

                if (resetPage) {
                    window.katalogCurrentPage = 1;
                }

                window.katalogCurrentPage = Math.min(Math.max(window.katalogCurrentPage, 1), totalPages);

                const currentPage = window.katalogCurrentPage;

                items.forEach(function(item) {
                    item.style.display = 'none';
                });

                const start = (currentPage - 1) * perPage;
                const end = start + perPage;

                filtered.slice(start, end).forEach(function(item) {
                    item.style.display = '';
                });

                const kosongCari = document.getElementById('catalogKosongCari');

                if (kosongCari) {
                    kosongCari.style.display = (items.length > 0 && filtered.length === 0) ? 'block' : 'none';
                }

                if (tableInfo) {
                    tableInfo.innerHTML = filtered.length === 0 ?
                        'Tidak ada data yang ditampilkan' :
                        `Menampilkan produk <strong>${start + 1}</strong> - <strong>${Math.min(end, filtered.length)}</strong> dari <strong>${filtered.length}</strong>`;
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


            /* Combo select (Produk per halaman) */

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


            /* Helper: ambil pesan error dari response Laravel */

            async function ambilPesanError(res, fallback) {
                const data = await res.json().catch(() => ({}));

                if (data.errors) {
                    return Object.values(data.errors).flat().join(' ');
                }

                return data.message || fallback;
            }


            /* Tambah produk */

            function bukaModalTambah() {
                const form = document.getElementById('formTambahProduk');
                if (form) form.reset();
                bukaModal('modalTambah');
            }

            function simpanTambah(event) {

                event.preventDefault();

                const fotoInput = document.getElementById('tambahfoto');

                if (!fotoInput.files || fotoInput.files.length === 0) {
                    showToast('Gagal', 'Foto produk wajib dipilih.', 'error');
                    return false;
                }

                const foto = fotoInput.files[0];

                if (foto.size > 2 * 1024 * 1024) {
                    showToast('Gagal', 'Ukuran foto maksimal 2MB.', 'error');
                    return false;
                }

                const formData = new FormData();

                formData.append('foto', foto);
                formData.append('nama_produk', document.getElementById('tambahNama').value.trim());
                formData.append('deskripsi', document.getElementById('tambahDeskripsi').value.trim());
                formData.append('harga', document.getElementById('tambahHarga').value);
                formData.append('stok', document.getElementById('tambahStok').value);

                fetch(window.KatalogPageConfig.urlStore, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    })
                    .then(async (res) => {
                        if (!res.ok) {
                            throw new Error(await ambilPesanError(res, 'Gagal menyimpan produk.'));
                        }
                        return res.json();
                    })
                    .then((data) => {
                        tutupModal('modalTambah');
                        showToast('Berhasil', data.message || 'Produk baru telah ditambahkan.');
                        setTimeout(() => window.location.reload(), 800);
                    })
                    .catch((err) => {
                        showToast('Gagal menyimpan', err.message || 'Terjadi kesalahan saat menyimpan produk.', 'error');
                    });

                return false;

            }


            /* Edit produk */

            function bukaModalEdit(produk) {

                document.getElementById('editId').value = produk.id_produk;
                document.getElementById('editNama').value = produk.nama_produk || '';
                document.getElementById('editDeskripsi').value = produk.deskripsi || '';
                document.getElementById('editHarga').value = produk.harga || 0;
                document.getElementById('editStok').value = produk.stok || 0;
                document.getElementById('editfoto').value = '';

                const imgPreview = document.getElementById('editfotoPreview');
                const divKosong = document.getElementById('editfotoKosong');

                if (produk.foto) {
                    imgPreview.src = `/storage/${produk.foto}`;
                    imgPreview.style.display = 'block';
                    divKosong.style.display = 'none';
                } else {
                    imgPreview.style.display = 'none';
                    divKosong.style.display = 'flex';
                }

                bukaModal('modalEdit');

            }

            function simpanEdit(event) {

                event.preventDefault();

                const id = document.getElementById('editId').value;
                const file = document.getElementById('editfoto').files[0];

                if (file && file.size > 2 * 1024 * 1024) {
                    showToast('Gagal', 'Ukuran foto maksimal 2MB.', 'error');
                    return false;
                }

                const formData = new FormData();

                formData.append('_method', 'PUT');

                if (file) {
                    formData.append('foto', file);
                }

                formData.append('nama_produk', document.getElementById('editNama').value.trim());
                formData.append('deskripsi', document.getElementById('editDeskripsi').value.trim());
                formData.append('harga', document.getElementById('editHarga').value);
                formData.append('stok', document.getElementById('editStok').value);

                const baseUrl = window.KatalogPageConfig.urlIndex;

                fetch(`${baseUrl}/${id}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    })
                    .then(async (res) => {
                        if (!res.ok) {
                            throw new Error(await ambilPesanError(res, 'Gagal memperbarui produk.'));
                        }
                        return res.json();
                    })
                    .then(() => {
                        tutupModal('modalEdit');
                        showToast('Berhasil disimpan', 'Perubahan data produk telah tersimpan.');
                        setTimeout(() => window.location.reload(), 800);
                    })
                    .catch((err) => {
                        showToast('Gagal menyimpan', err.message, 'error');
                    });

                return false;

            }


            /* Detail produk */

            function lihatDetail(item) {

                document.getElementById('detailNama').textContent = item.nama_produk || '-';
                document.getElementById('detailDeskripsi').textContent = item.deskripsi || 'Tidak ada deskripsi.';
                document.getElementById('detailHarga').textContent = formatRupiah(item.harga);
                document.getElementById('detailStok').textContent = item.stok ?? '-';

                const foto = document.getElementById('detailfoto');
                const kosong = document.getElementById('detailfotoKosong');

                if (item.foto) {
                    foto.src = `/storage/${item.foto}`;
                    foto.style.display = '';
                    kosong.style.display = 'none';
                } else {
                    foto.style.display = 'none';
                    kosong.style.display = 'flex';
                }

                bukaModal('modalDetail');

            }


            /* Hapus produk */

            let produkAkanDihapus = null;

            function hapusProduk(produk) {
                produkAkanDihapus = produk;
                document.getElementById('hapusNama').textContent = produk.nama_produk || '-';
                bukaModal('modalHapus');
            }

            function konfirmasiHapus() {

                if (!produkAkanDihapus) return;

                const id = produkAkanDihapus.id_produk;
                const namaDihapus = produkAkanDihapus.nama_produk || 'Produk';
                const baseUrl = window.KatalogPageConfig.urlIndex;

                fetch(`${baseUrl}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(async (res) => {
                        if (!res.ok) {
                            throw new Error(await ambilPesanError(res, 'Gagal menghapus produk.'));
                        }
                        return res.json();
                    })
                    .then(() => {
                        tutupModal('modalHapus');
                        showToast('Produk berhasil dihapus.', namaDihapus + ' telah dihapus dari katalog.');
                        produkAkanDihapus = null;
                        setTimeout(() => window.location.reload(), 800);
                    })
                    .catch((err) => {
                        showToast('Gagal menghapus', err.message, 'error');
                    });

            }
