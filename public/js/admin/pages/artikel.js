const { dataArtikel, jsLabelJenis, urlArtikelStore, urlArtikelUpdate, urlArtikelDestroy, urlGambarArtikel, flashSuccess } = window.ArtikelPageConfig;

        const bannerMeta = {
            edukasi: { label: '🌱 Edukasi Sampah', gradClass: 'banner-grad--edukasi' },
            event:   { label: '📅 Event & Agenda',  gradClass: 'banner-grad--event' },
            artikel: { label: '📰 Artikel Pilihan', gradClass: 'banner-grad--artikel' },
        };

        let myEditor = null;

        document.addEventListener('DOMContentLoaded', function() {
            ClassicEditor
                .create(document.querySelector('#editorKonten'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'underline', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo']
                })
                .then(editor => {
                    myEditor = editor;
                })
                .catch(error => {
                    console.error('Gagal memuat CKEditor:', error);
                });

            if (flashSuccess) showToast('Berhasil', flashSuccess);

            const searchInput = document.getElementById('searchArtikel');
            const statusSelect = document.getElementById('statusArtikel');
            const kategoriSelect = document.getElementById('kategoriArtikel');
            const perPageSelect = document.getElementById('perPageSelect');

            if (searchInput) searchInput.addEventListener('keyup', () => jalankanFilter(true));
            if (statusSelect) statusSelect.addEventListener('change', () => jalankanFilter(true));
            if (kategoriSelect) kategoriSelect.addEventListener('change', () => jalankanFilter(true));
            if (perPageSelect) perPageSelect.addEventListener('change', () => jalankanFilter(true));

            document.getElementById('paginationButtons')?.addEventListener('click', function(e) {
                const btn = e.target.closest('button[data-page]');
                if (!btn || btn.disabled) return;
                window.artikelCurrentPage = parseInt(btn.dataset.page, 10) || 1;
                jalankanFilter(false);
            });

            jalankanFilter(true);
            initComboSelect('perPageSelect');
            initComboSelect('kategoriArtikel');
            initComboSelect('statusArtikel');
            initComboPreview();

            const previewSelector = document.getElementById('previewSelector');
            renderPreview(previewSelector?.value || '');
        });


        /* PILIH MODE MEDIA (GAMBAR / BANNER WARNA / TANPA GAMBAR) */
        function pilihModeMedia(mode) {
            document.getElementById('modeMedia').value = mode;

            document.querySelectorAll('#mediaModeGrid .media-mode-card').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.mode === mode);
            });

            const panelUpload = document.getElementById('panelUploadGambar');
            const panelBanner = document.getElementById('panelBannerPreview');

            if (mode === 'gambar') {
                panelUpload.style.display = 'block';
                panelBanner.style.display = 'none';
            } else {
                panelUpload.style.display = 'none';
                panelBanner.style.display = 'block';
                updateFormMediaPreview();
            }
        }

        function updateFormMediaPreview() {
            const mode = document.getElementById('modeMedia')?.value || 'gambar';
            const container = document.getElementById('formLiveCardPreview');
            if (!container || mode === 'gambar') return;

            const judulInput = (document.getElementById('artikelJudul')?.value || '').trim() || 'Judul Konten Anda Akan Tampil di Sini';

            if (mode === 'tanpa_gambar') {
                container.innerHTML = `
                    <div style="max-width: 380px; background: #ffffff; border: 1px solid #D8E6DF; border-left: 4px solid #1E9E66; border-radius: 10px; padding: 14px; color: #182B24;">
                        <span style="display:inline-block; font-size:10px; font-weight:700; color:#157A4E; background:#D7EDE2; padding:2px 8px; border-radius:99px; margin-bottom:6px;">Mode Teks Saja (Tanpa Gambar)</span>
                        <div style="font-size:14px; font-weight:700; line-height:1.35;">${escapeHtml(judulInput)}</div>
                        <div style="font-size:11.5px; color:#5D736B; margin-top:4px;">Di aplikasi mobile, kartu akan langsung menampilkan judul & ringkasan teks tanpa kotak gambar kosong.</div>
                    </div>
                `;
                return;
            }

            const tema = mode.replace('banner_', '');
            const meta = bannerMeta[tema] || bannerMeta.edukasi;

            container.innerHTML = `
                <div class="${meta.gradClass}" style="max-width: 380px; border-radius: 12px; padding: 16px 18px; box-shadow: 0 6px 18px rgba(24, 43, 36, .12);">
                    <span style="display:inline-block; font-size:10px; font-weight:700; background:rgba(255,255,255,.20); padding:3px 9px; border-radius:99px; letter-spacing:.3px;">${meta.label}</span>
                    <div style="font-size:16px; font-weight:800; line-height:1.35; margin-top:10px;">${escapeHtml(judulInput)}</div>
                    <div style="font-size:11px; opacity:.88; margin-top:6px;">Pratinjau header banner warna otomatis di aplikasi mobile</div>
                </div>
            `;
        }


        /* NAVIGASI ANTARA DAFTAR & FORM FULL PAGE */
        function alihKeForm(mode, id = null) {
            document.getElementById('viewDaftar').style.display = 'none';
            document.getElementById('wrapperSummary').style.display = 'none';
            document.getElementById('heroActionBtn').style.display = 'none';
            document.getElementById('viewForm').style.display = 'block';

            const form = document.getElementById('formArtikel');
            form.reset();
            clearFormErrors(['judul', 'jenis', 'gambar', 'konten', 'tanggal']);
            document.getElementById('artikelGambarUrl').value = '';
            document.getElementById('gambarPreviewWrap').style.display = 'none';
            document.getElementById('gambarLamaInfo').innerHTML = '';

            if (myEditor) myEditor.setData('');

            if (mode === 'tambah') {
                document.getElementById('formTitleText').textContent = 'Tambah Konten Artikel & Edukasi Baru';
                document.getElementById('artikelId').value = '';
                pilihModeMedia('gambar');
            } else if (mode === 'edit') {
                document.getElementById('formTitleText').textContent = 'Edit Konten Artikel & Edukasi';
                isiFormEdit(id);
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function tutupFormFull() {
            document.getElementById('viewForm').style.display = 'none';
            document.getElementById('viewDaftar').style.display = 'grid';
            document.getElementById('wrapperSummary').style.display = 'grid';
            document.getElementById('heroActionBtn').style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }


        /* EDIT ARTIKEL */
        function editArtikelForm(id) {
            alihKeForm('edit', id);
        }

        function isiFormEdit(id) {
            const artikel = dataArtikel.find(item => Number(item.id_artikel) === Number(id));
            if (!artikel) return;

            document.getElementById('artikelId').value = artikel.id_artikel;
            document.getElementById('artikelJudul').value = artikel.judul ?? '';
            document.getElementById('artikelJenis').value = (artikel.jenis === 'acara' ? 'event' : artikel.jenis) ?? 'edukasi';
            document.getElementById('artikelTanggal').value = artikel.tanggal_publish ? artikel.tanggal_publish.split('T')[0].split(' ')[0] : '';

            if (myEditor) myEditor.setData(artikel.konten ?? '');

            const gambarLama = document.getElementById('gambarLamaInfo');

            if (artikel.gambar && artikel.gambar.startsWith('banner:')) {
                const tema = artikel.gambar.replace('banner:', '');
                pilihModeMedia('banner_' + tema);
                gambarLama.innerHTML = '';
            } else if (artikel.gambar) {
                pilihModeMedia('gambar');
                const imgSrc = artikel.gambar.startsWith('http') ? artikel.gambar : `${urlGambarArtikel}/${encodeURIComponent(artikel.gambar)}`;
                gambarLama.innerHTML = `<img src="${imgSrc}" alt="Gambar lama"><small>Gambar saat ini. Pilih file baru jika ingin menggantinya.</small>`;
            } else {
                pilihModeMedia('tanpa_gambar');
                gambarLama.innerHTML = '';
            }
        }


        /* SIMPAN ARTIKEL */
        function simpanArtikel(aksi) {
            aksi = aksi === 'draft' ? 'draft' : 'publish';
            const id = document.getElementById('artikelId').value;
            clearFormErrors(['judul', 'jenis', 'gambar', 'konten', 'tanggal']);

            const modeMedia = document.getElementById('modeMedia').value;
            const formData = new FormData();
            formData.append('judul', document.getElementById('artikelJudul').value.trim());
            formData.append('jenis', document.getElementById('artikelJenis').value);
            formData.append('mode_media', modeMedia);
            formData.append('konten', myEditor ? myEditor.getData() : '');

            let tanggalPublish = '';
            if (aksi === 'publish') {
                tanggalPublish = document.getElementById('artikelTanggal').value;
                if (!tanggalPublish) {
                    const d = new Date();
                    tanggalPublish = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                }
            }
            formData.append('tanggal_publish', tanggalPublish);

            if (modeMedia === 'gambar') {
                const file = document.getElementById('artikelGambar').files[0];
                const urlGambarFetched = document.getElementById('artikelGambarUrl').value;
                if (file) formData.append('gambar', file);
                else if (urlGambarFetched) formData.append('gambar_url', urlGambarFetched);
            }

            let url = urlArtikelStore;
            if (id) {
                formData.append('_method', 'PUT');
                url = urlArtikelUpdate.replace(':id', id);
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    if (data.errors) tampilkanFormErrors(data.errors, ['judul', 'jenis', 'gambar', 'konten', 'tanggal']);
                    throw new Error(data.message || 'Gagal menyimpan artikel.');
                }
                return data;
            })
            .then(() => {
                showToast(aksi === 'draft' ? 'Draft Disimpan' : 'Dipublikasikan', 'Data artikel berhasil disimpan.');
                setTimeout(() => window.location.reload(), 800);
            })
            .catch(err => {
                showToast('Gagal', err.message || 'Terjadi kesalahan sistem.', 'error');
            });
        }


        /* FILTER TABEL */
        function jalankanFilter(resetPage = true) {
            const keyword = (document.getElementById('searchArtikel')?.value || '').toLowerCase().trim();
            const status = document.getElementById('statusArtikel')?.value || 'semua';
            const kategori = document.getElementById('kategoriArtikel')?.value || 'semua';
            const rows = Array.from(document.querySelectorAll('#artikelTableBody tr[data-judul]'));

            const filtered = rows.filter(row => {
                const judul = row.dataset.judul || '';
                const rowStatus = row.dataset.status || '';
                const rowKategori = row.dataset.kategori || '';
                return (!keyword || judul.includes(keyword)) && (status === 'semua' || rowStatus === status) && (kategori === 'semua' || rowKategori === kategori);
            });

            const perPage = parseInt(document.getElementById('perPageSelect')?.value || 10, 10);
            if (typeof window.artikelCurrentPage !== 'number' || resetPage) window.artikelCurrentPage = 1;

            const totalPages = Math.ceil(filtered.length / perPage) || 1;
            window.artikelCurrentPage = Math.min(Math.max(window.artikelCurrentPage, 1), totalPages);

            rows.forEach(r => r.style.display = 'none');
            const start = (window.artikelCurrentPage - 1) * perPage;
            filtered.slice(start, start + perPage).forEach(r => r.style.display = '');

            const tampil = filtered.length;
            document.getElementById('artikelKosongCari').style.display = (rows.length > 0 && tampil === 0) ? 'block' : 'none';

            const tableInfo = document.getElementById('tableInfoPagination');
            if (tableInfo) {
                tableInfo.innerHTML = tampil === 0 ? 'Tidak ada data' : `Menampilkan konten <strong>${start + 1}</strong> - <strong>${Math.min(start + perPage, tampil)}</strong> dari <strong>${tampil}</strong>`;
            }

            const pContainer = document.getElementById('paginationButtons');
            if (pContainer) {
                let html = `<button class="btn btn--ghost" type="button" data-page="${window.artikelCurrentPage - 1}" ${window.artikelCurrentPage === 1 ? 'disabled' : ''}>‹</button>`;
                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= window.artikelCurrentPage - 1 && i <= window.artikelCurrentPage + 1)) {
                        html += `<button class="btn ${i === window.artikelCurrentPage ? 'btn--primary' : 'btn--ghost'}" type="button" data-page="${i}">${i}</button>`;
                    }
                }
                html += `<button class="btn btn--ghost" type="button" data-page="${window.artikelCurrentPage + 1}" ${window.artikelCurrentPage === totalPages ? 'disabled' : ''}>›</button>`;
                pContainer.innerHTML = html;
            }
        }


        /* PREVIEW GAMBAR & MOBILE */
        function previewGambar(input) {
            const wrap = document.getElementById('gambarPreviewWrap');
            const img = document.getElementById('gambarPreviewImg');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => { img.src = e.target.result; wrap.style.display = 'block'; };
                reader.readAsDataURL(input.files[0]);
            } else {
                wrap.style.display = 'none';
            }
        }

        function renderPreview(id) {
            const body = document.getElementById('phonePreviewBody');
            if (!body) return;
            const item = dataArtikel.find(a => String(a.id_artikel) === String(id));
            if (!item) {
                body.innerHTML = '<div class="phone-empty">Belum ada konten untuk ditampilkan.</div>';
                return;
            }

            let headerMediaHtml = '';
            if (item.gambar && item.gambar.startsWith('banner:')) {
                const tema = item.gambar.replace('banner:', '');
                const meta = bannerMeta[tema] || bannerMeta.edukasi;
                headerMediaHtml = `
                    <div class="phone-banner-box ${meta.gradClass}">
                        <span class="phone-banner-pill">${meta.label}</span>
                        <div class="phone-banner-title">${escapeHtml(item.judul)}</div>
                    </div>
                `;
            } else if (item.gambar) {
                const src = item.gambar.startsWith('http') ? item.gambar : `${urlGambarArtikel}/${encodeURIComponent(item.gambar)}`;
                headerMediaHtml = `<img src="${src}" class="phone-image">`;
            } else {
                // Mode teks saja: tidak ada kotak kosong, langsung garis aksen editorial
                headerMediaHtml = `<div class="phone-textonly-bar"></div>`;
            }

            const jenisLabel = jsLabelJenis[item.jenis] || 'Edukasi Sampah';
            let metaHtml = '♻ ' + escapeHtml(jenisLabel);
            if (item.tanggal_publish) {
                metaHtml += ' &nbsp;•&nbsp; ' + item.tanggal_publish.split('T')[0].split(' ')[0];
            } else {
                metaHtml += ' <span class="phone-badge-draft">Draft</span>';
            }

            body.innerHTML = `
                ${headerMediaHtml}
                <div class="phone-title">${escapeHtml(item.judul)}</div>
                <div class="phone-meta">${metaHtml}</div>
                <div class="phone-content">${item.konten || ''}</div>
                <div class="phone-tip">💡 <strong>Tips Warga</strong><br>Pilah sampah dari rumah untuk lingkungan yang lebih bersih.</div>
            `;
        }


        /* COMBO & HELPER MODAL */
        function initComboSelect(selectId) {
            const select = document.getElementById(selectId);
            const input = document.getElementById('cari_' + selectId);
            const list = document.getElementById('list_' + selectId);
            if (!select || !input || !list) return;
            input.readOnly = true;

            const teks = o => o.textContent.trim();
            const sync = () => { const o = select.options[select.selectedIndex]; input.value = o ? teks(o) : ''; };

            input.addEventListener('click', e => {
                e.stopPropagation();
                list.innerHTML = Array.from(select.options).map(o => `<div class="combo-item" data-value="${o.value}">${teks(o)}</div>`).join('');
                list.classList.toggle('show');
            });

            list.addEventListener('mousedown', e => {
                e.preventDefault();
                const item = e.target.closest('.combo-item');
                if (item) {
                    select.value = item.dataset.value;
                    sync();
                    list.classList.remove('show');
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            document.addEventListener('click', () => list.classList.remove('show'));
            sync();
        }

        function initComboPreview() {
            const select = document.getElementById('previewSelector');
            const input = document.getElementById('cariPreview');
            const list = document.getElementById('listPreview');
            if (!select || !input || !list) return;

            const data = Array.from(select.options).filter(o => o.value !== '').map(o => ({ value: o.value, label: o.textContent.trim() }));

            input.addEventListener('focus', () => {
                list.innerHTML = data.map(d => `<div class="combo-item" data-value="${d.value}">${escapeHtml(d.label)}</div>`).join('');
                list.classList.add('show');
            });

            list.addEventListener('mousedown', e => {
                const item = e.target.closest('.combo-item');
                if (item) {
                    e.preventDefault();
                    select.value = item.dataset.value;
                    input.value = item.textContent.trim();
                    list.classList.remove('show');
                    renderPreview(item.dataset.value);
                }
            });

            document.addEventListener('click', e => { if (!e.target.closest('#comboPreview')) list.classList.remove('show'); });
            if (select.selectedOptions[0]) input.value = select.selectedOptions[0].textContent.trim();
        }

        function bukaModal(id) { document.getElementById(id)?.classList.add('active'); }
        function tutupModal(id) { document.getElementById(id)?.classList.remove('active'); }

        function bukaDetail(id) {
            const item = dataArtikel.find(a => Number(a.id_artikel) === Number(id));
            if (!item) return;
            document.getElementById('detailJudul').textContent = item.judul;
            document.getElementById('detailTanggal').textContent = (item.jenis || 'edukasi').toUpperCase() + ' • ' + (item.tanggal_publish ? 'Dipublikasikan' : 'Draft');
            document.getElementById('detailKonten').innerHTML = item.konten || '-';

            let mediaHtml = '';
            if (item.gambar && item.gambar.startsWith('banner:')) {
                const tema = item.gambar.replace('banner:', '');
                const meta = bannerMeta[tema] || bannerMeta.edukasi;
                mediaHtml = `
                    <div class="detail-banner ${meta.gradClass}">
                        <span style="font-size:11px; font-weight:700; opacity:.9;">${meta.label}</span>
                        <strong style="font-size:18px;">${escapeHtml(item.judul)}</strong>
                    </div>
                `;
            } else if (item.gambar) {
                const src = item.gambar.startsWith('http') ? item.gambar : `${urlGambarArtikel}/${encodeURIComponent(item.gambar)}`;
                mediaHtml = `<img src="${src}" class="detail-image">`;
            }
            document.getElementById('detailGambar').innerHTML = mediaHtml;
            bukaModal('modalDetail');
        }

        let hapusCtx = null;
        function hapusArtikel(id, judul) {
            hapusCtx = { id, judul };
            document.getElementById('hapusJudul').textContent = judul;
            bukaModal('modalHapus');
        }

        function konfirmasiHapus() {
            if (!hapusCtx) return;
            fetch(urlArtikelDestroy.replace(':id', hapusCtx.id), {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(res => { if (!res.ok) throw new Error('Gagal menghapus'); return res.json(); })
            .then(() => {
                showToast('Berhasil', 'Konten berhasil dihapus.');
                setTimeout(() => window.location.reload(), 800);
            })
            .catch(err => showToast('Gagal', err.message, 'error'));
        }

        function showToast(title, text, type = 'success') {
            const wrap = document.getElementById('toastWrap');
            if (!wrap) return;
            const toast = document.createElement('div');
            toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');
            toast.innerHTML = `<div class="toast-body"><p class="toast-title">${escapeHtml(title)}</p><p class="toast-text">${escapeHtml(text)}</p></div>`;
            wrap.appendChild(toast);
            setTimeout(() => toast.remove(), 3500);
        }

        function escapeHtml(text) {
            const d = document.createElement('div');
            d.textContent = text || '';
            return d.innerHTML;
        }

        function clearFormErrors(fields) {
            fields.forEach(f => { const el = document.getElementById('err_' + f); if (el) el.textContent = ''; });
        }

        function tampilkanFormErrors(errors, fields) {
            fields.forEach(f => { if (errors[f]) document.getElementById('err_' + f).textContent = errors[f][0]; });
        }
