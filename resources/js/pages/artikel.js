import { initComboSelect } from '../utils/combo-select';
import { escapeHtml } from '../utils/helpers';
import { renderPagination } from '../utils/paginations';
import { showToast } from '../utils/toast';

const dashArtikelData = window.__ARTIKEL_DATA__ || {};
const dataArtikel = Array.isArray(dashArtikelData.items) ? dashArtikelData.items : [];
const urls = dashArtikelData.urls || {};

const bannerMeta = {
    edukasi: { label: '🌱 Edukasi Sampah', gradClass: 'banner-grad--edukasi' },
    event: { label: '📅 Event & Agenda', gradClass: 'banner-grad--event' },
    artikel: { label: '📰 Artikel Pilihan', gradClass: 'banner-grad--artikel' },
};

let myEditor = null;
let hapusCtx = null;

function tanggalHariIniLokal() {
    const now = new Date();
    return new Date(now.getTime() - now.getTimezoneOffset() * 60000)
        .toISOString()
        .substring(0, 10);
}

function setDropdownJenisForm(value = '') {
    const selectEl = document.getElementById('artikelJenis');
    const inputEl = document.getElementById('cari_artikelJenis');

    if (selectEl) {
        if (value) {
            selectEl.value = value;
        } else {
            selectEl.selectedIndex = -1;
            selectEl.value = '';
        }
    }

    initComboSelect('artikelJenis', { readonly: true, placeholderKosong: true });

    if (inputEl) {
        inputEl.readOnly = true;
        if (!value) {
            inputEl.value = '';
        } else {
            const selectedOpt = Array.from(selectEl?.options || []).find((o) => o.value === value);
            inputEl.value = selectedOpt ? selectedOpt.textContent : '';
        }
    }
}

function clearFormErrors(fields) {
    fields.forEach((f) => {
        const el = document.getElementById('err_' + f);
        if (el) el.textContent = '';
    });
}

function tampilkanFormErrors(errors, fields) {
    fields.forEach((f) => {
        const el = document.getElementById('err_' + f);
        if (el && errors[f]) el.textContent = errors[f][0];
    });
}

function extractSingleUrl(html) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html || '';
    const text = (tmp.textContent || tmp.innerText || '').trim();
    return /^https?:\/\/[^\s]+$/i.test(text) ? text : null;
}

function pilihSumberKonten(sumber) {
    const inputSumber = document.getElementById('sumberKonten');
    if (inputSumber) inputSumber.value = sumber;

    document.querySelectorAll('[data-sumber]').forEach((btn) => {
        btn.classList.toggle('active', btn.dataset.sumber === sumber);
    });

    const panelManual = document.getElementById('panelKontenManual');
    const panelLink = document.getElementById('panelKontenLink');

    if (panelManual && panelLink) {
        if (sumber === 'link') {
            panelManual.style.display = 'none';
            panelLink.style.display = 'block';
        } else {
            panelManual.style.display = 'block';
            panelLink.style.display = 'none';
        }
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
                <div style="font-size:11.5px; color:#5D736B; margin-top:4px;">Di aplikasi mobile, kartu akan langsung menampilkan judul & ringkasan tanpa kotak gambar kosong.</div>
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

function pilihModeMedia(mode) {
    const modeInput = document.getElementById('modeMedia');
    if (modeInput) modeInput.value = mode;

    document.querySelectorAll('#mediaModeGrid .media-mode-card').forEach((btn) => {
        btn.classList.toggle('active', btn.dataset.mode === mode);
    });

    const panelUpload = document.getElementById('panelUploadGambar');
    const panelBanner = document.getElementById('panelBannerPreview');

    if (panelUpload && panelBanner) {
        if (mode === 'gambar') {
            panelUpload.style.display = 'block';
            panelBanner.style.display = 'none';
        } else {
            panelUpload.style.display = 'none';
            panelBanner.style.display = 'block';
            updateFormMediaPreview();
        }
    }
}

function isiFormEdit(id) {
    const artikel = dataArtikel.find((item) => Number(item.id_artikel) === Number(id));
    if (!artikel) return;

    const hariIni = tanggalHariIniLokal();

    document.getElementById('artikelId').value = artikel.id_artikel;
    document.getElementById('artikelJudul').value = artikel.judul ?? '';

    const jenisNilai = (artikel.jenis === 'acara' ? 'event' : artikel.jenis) ?? 'edukasi';
    setDropdownJenisForm(jenisNilai);

    const tanggalEl = document.getElementById('artikelTanggal');
    if (tanggalEl) {
        tanggalEl.min = hariIni;
        tanggalEl.removeAttribute('max');
        const tglTersimpan = artikel.tanggal_publish ? artikel.tanggal_publish.split('T')[0].split(' ')[0] : '';
        tanggalEl.value = (tglTersimpan && tglTersimpan >= hariIni) ? tglTersimpan : hariIni;
    }

    const detectedUrl = extractSingleUrl(artikel.konten ?? '');
    if (detectedUrl) {
        pilihSumberKonten('link');
        document.getElementById('artikelLinkUrl').value = detectedUrl;
        if (myEditor) myEditor.setData('');
    } else {
        pilihSumberKonten('manual');
        document.getElementById('artikelLinkUrl').value = '';
        if (myEditor) myEditor.setData(artikel.konten ?? '');
    }

    const gambarLama = document.getElementById('gambarLamaInfo');
    const baseImgUrl = urls.gambar || '';

    if (artikel.gambar && artikel.gambar.startsWith('banner:')) {
        const tema = artikel.gambar.replace('banner:', '');
        pilihModeMedia('banner_' + tema);
        if (gambarLama) gambarLama.innerHTML = '';
    } else if (artikel.gambar) {
        pilihModeMedia('gambar');
        const imgSrc = artikel.gambar.startsWith('http') ? artikel.gambar : `${baseImgUrl}/${encodeURIComponent(artikel.gambar)}`;
        if (gambarLama) {
            gambarLama.innerHTML = `<img src="${imgSrc}" alt="Gambar lama"><small>Gambar saat ini. Pilih file baru jika ingin menggantinya.</small>`;
        }
    } else {
        pilihModeMedia('tanpa_gambar');
        if (gambarLama) gambarLama.innerHTML = '';
    }
}

function alihKeForm(mode, id = null) {
    document.getElementById('viewDaftar').style.display = 'none';
    document.getElementById('wrapperSummary').style.display = 'none';
    document.getElementById('heroActionBtn').style.display = 'none';
    document.getElementById('viewForm').style.display = 'block';

    const form = document.getElementById('formArtikel');
    form.reset();
    clearFormErrors(['judul', 'jenis', 'gambar', 'konten', 'link_url', 'tanggal']);
    document.getElementById('artikelGambarUrl').value = '';
    document.getElementById('artikelLinkUrl').value = '';
    document.getElementById('gambarPreviewWrap').style.display = 'none';
    document.getElementById('gambarLamaInfo').innerHTML = '';

    const hariIni = tanggalHariIniLokal();
    const tanggalEl = document.getElementById('artikelTanggal');
    if (tanggalEl) {
        tanggalEl.min = hariIni;
        tanggalEl.removeAttribute('max');
        tanggalEl.value = hariIni;
    }

    if (myEditor) myEditor.setData('');

    if (mode === 'tambah') {
        document.getElementById('formTitleText').textContent = 'Tambah Konten Artikel & Edukasi Baru';
        document.getElementById('artikelId').value = '';
        setDropdownJenisForm('');
        pilihModeMedia('gambar');
        pilihSumberKonten('manual');
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
    document.getElementById('heroActionBtn').style.display = '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function editArtikelForm(id) {
    alihKeForm('edit', id);
}

function simpanArtikel(aksi) {
    aksi = aksi === 'draft' ? 'draft' : 'publish';
    const id = document.getElementById('artikelId').value;
    clearFormErrors(['judul', 'jenis', 'gambar', 'konten', 'link_url', 'tanggal']);

    const jenis = document.getElementById('artikelJenis').value;
    if (!jenis) {
        const errJenis = document.getElementById('err_jenis');
        if (errJenis) errJenis.textContent = 'Silakan pilih jenis / kategori konten.';
        showToast('Gagal', 'Silakan pilih jenis / kategori konten terlebih dahulu.', 'error');
        document.getElementById('cari_artikelJenis')?.focus();
        return;
    }

    const hariIni = tanggalHariIniLokal();
    const tanggalInputVal = document.getElementById('artikelTanggal')?.value || '';

    if (aksi === 'draft' && tanggalInputVal && tanggalInputVal < hariIni) {
        const errTgl = document.getElementById('err_tanggal');
        if (errTgl) errTgl.textContent = 'Tanggal tidak boleh lebih awal dari hari ini.';
        showToast('Gagal', 'Tanggal tidak boleh memilih hari sebelumnya.', 'error');
        document.getElementById('artikelTanggal')?.focus();
        return;
    }

    const modeMedia = document.getElementById('modeMedia').value;
    const sumberKonten = document.getElementById('sumberKonten').value;
    const linkUrl = document.getElementById('artikelLinkUrl').value.trim();

    const formData = new FormData();
    formData.append('judul', document.getElementById('artikelJudul').value.trim());
    formData.append('jenis', jenis);
    formData.append('mode_media', modeMedia);
    formData.append('sumber_konten', sumberKonten);

    if (sumberKonten === 'link') {
        formData.append('link_url', linkUrl);
        formData.append('konten', linkUrl);
    } else {
        formData.append('konten', myEditor ? myEditor.getData() : '');
    }

    // Jika dipublikasikan otomatis tanggal hari ini; jika draft kosongkan tanggal_publish (atau kirim jadwal jika backend mendukung)
    const tanggalPublish = (aksi === 'publish') ? hariIni : '';
    formData.append('tanggal_publish', tanggalPublish);
    if (aksi === 'draft' && tanggalInputVal) {
        formData.append('tanggal_rencana', tanggalInputVal);
    }

    if (modeMedia === 'gambar') {
        const file = document.getElementById('artikelGambar').files[0];
        const urlGambarFetched = document.getElementById('artikelGambarUrl').value;
        if (file) formData.append('gambar', file);
        else if (urlGambarFetched) formData.append('gambar_url', urlGambarFetched);
    }

    let url = urls.store;
    if (id) {
        formData.append('_method', 'PUT');
        url = (urls.update || '').replace(':id', id);
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    fetch(url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: formData,
    })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                if (data.errors) tampilkanFormErrors(data.errors, ['judul', 'jenis', 'gambar', 'konten', 'link_url', 'tanggal']);
                throw new Error(data.message || 'Gagal menyimpan artikel.');
            }
            return data;
        })
        .then(() => {
            showToast(aksi === 'draft' ? 'Draft Disimpan' : 'Dipublikasikan', 'Data artikel berhasil disimpan.');
            setTimeout(() => window.location.reload(), 800);
        })
        .catch((err) => {
            showToast('Gagal', err.message || 'Terjadi kesalahan sistem.', 'error');
        });
}

function jalankanFilter(resetPage = true) {
    const keyword = (document.getElementById('searchArtikel')?.value || '').toLowerCase().trim();
    const status = document.getElementById('statusArtikel')?.value || 'semua';
    const kategori = document.getElementById('kategoriArtikel')?.value || 'semua';
    const rows = Array.from(document.querySelectorAll('#artikelTableBody tr[data-judul]'));

    const filtered = rows.filter((row) => {
        const judul = row.dataset.judul || '';
        const rowStatus = row.dataset.status || '';
        const rowKategori = row.dataset.kategori || '';
        return (!keyword || judul.includes(keyword))
            && (status === 'semua' || rowStatus === status)
            && (kategori === 'semua' || rowKategori === kategori);
    });

    const perPage = parseInt(document.getElementById('perPageSelect')?.value || 10, 10) || 10;
    if (typeof window.artikelCurrentPage !== 'number' || resetPage) {
        window.artikelCurrentPage = 1;
    }

    const totalPages = Math.ceil(filtered.length / perPage) || 1;
    window.artikelCurrentPage = Math.min(Math.max(window.artikelCurrentPage, 1), totalPages);

    rows.forEach((r) => {
        r.style.display = 'none';
    });

    const start = (window.artikelCurrentPage - 1) * perPage;
    filtered.slice(start, start + perPage).forEach((r) => {
        r.style.display = '';
    });

    const emptyCari = document.getElementById('artikelKosongCari');
    if (emptyCari) {
        emptyCari.style.display = (rows.length > 0 && filtered.length === 0) ? 'block' : 'none';
    }

    renderPagination({
        infoId: 'tableInfoPagination',
        buttonId: 'paginationButtons',
        currentPage: window.artikelCurrentPage,
        totalPages,
        totalItems: filtered.length,
        perPage,
        unit: 'konten',
        onPageChange: (p) => {
            window.artikelCurrentPage = p;
            jalankanFilter(false);
        },
    });
}

function previewGambar(input) {
    const wrap = document.getElementById('gambarPreviewWrap');
    const img = document.getElementById('gambarPreviewImg');
    if (!wrap || !img) return;

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            img.src = e.target.result;
            wrap.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        wrap.style.display = 'none';
    }
}

function bukaModal(id) {
    document.getElementById(id)?.classList.add('active');
}

function tutupModal(id) {
    document.getElementById(id)?.classList.remove('active');
}

function bukaDetail(id) {
    const item = dataArtikel.find((a) => Number(a.id_artikel) === Number(id));
    if (!item) return;

    document.getElementById('detailJudul').textContent = item.judul;
    document.getElementById('detailTanggal').textContent = (item.jenis || 'edukasi').toUpperCase() + ' • ' + (item.tanggal_publish ? 'Dipublikasikan' : 'Draft');

    const detailUrl = extractSingleUrl(item.konten || '');
    if (detailUrl) {
        document.getElementById('detailKonten').innerHTML = `
            <div style="padding:12px 14px;border-radius:10px;border:1px solid var(--border);background:var(--surface-secondary);">
                <div style="font-weight:700;color:#1E9E66;margin-bottom:4px;">🔗 Tautan Artikel Web Eksternal</div>
                <a href="${escapeHtml(detailUrl)}" target="_blank" rel="noopener noreferrer" style="color:var(--text);word-break:break-all;text-decoration:underline;">
                    ${escapeHtml(detailUrl)}
                </a>
            </div>
        `;
    } else {
        document.getElementById('detailKonten').innerHTML = item.konten || '-';
    }

    const baseImgUrl = urls.gambar || '';
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
        const src = item.gambar.startsWith('http') ? item.gambar : `${baseImgUrl}/${encodeURIComponent(item.gambar)}`;
        mediaHtml = `<img src="${src}" class="detail-image" alt="${escapeHtml(item.judul)}">`;
    }

    document.getElementById('detailGambar').innerHTML = mediaHtml;
    bukaModal('modalDetail');
}

function hapusArtikel(id, judul) {
    hapusCtx = { id, judul };
    document.getElementById('hapusJudul').textContent = judul;
    bukaModal('modalHapus');
}

function konfirmasiHapus() {
    if (!hapusCtx) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const deleteUrl = (urls.destroy || '').replace(':id', hapusCtx.id);

    fetch(deleteUrl, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    })
        .then((res) => {
            if (!res.ok) throw new Error('Gagal menghapus');
            return res.json();
        })
        .then(() => {
            showToast('Berhasil', 'Konten berhasil dihapus.');
            setTimeout(() => window.location.reload(), 800);
        })
        .catch((err) => showToast('Gagal', err.message, 'error'));
}

Object.assign(window, {
    alihKeForm,
    tutupFormFull,
    editArtikelForm,
    simpanArtikel,
    pilihSumberKonten,
    pilihModeMedia,
    updateFormMediaPreview,
    previewGambar,
    bukaModal,
    tutupModal,
    bukaDetail,
    hapusArtikel,
    konfirmasiHapus,
});

document.addEventListener('DOMContentLoaded', () => {
    initComboSelect('perPageSelect', { readonly: true });
    initComboSelect('kategoriArtikel', { readonly: true });
    initComboSelect('statusArtikel', { readonly: true });
    setDropdownJenisForm('');

    const tanggalEl = document.getElementById('artikelTanggal');
    if (tanggalEl) {
        const hariIni = tanggalHariIniLokal();
        tanggalEl.min = hariIni;
        tanggalEl.value = hariIni;
    }

    if (typeof window.ClassicEditor !== 'undefined' && document.querySelector('#editorKonten')) {
        window.ClassicEditor
            .create(document.querySelector('#editorKonten'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo'],
            })
            .then((editor) => {
                myEditor = editor;
            })
            .catch((error) => {
                console.error('Gagal memuat CKEditor:', error);
            });
    }

    if (dashArtikelData.flashSuccess) {
        showToast('Berhasil', dashArtikelData.flashSuccess);
    }

    const searchInput = document.getElementById('searchArtikel');
    const statusSelect = document.getElementById('statusArtikel');
    const kategoriSelect = document.getElementById('kategoriArtikel');
    const perPageSelect = document.getElementById('perPageSelect');

    if (searchInput) searchInput.addEventListener('keyup', () => jalankanFilter(true));
    if (statusSelect) statusSelect.addEventListener('change', () => jalankanFilter(true));
    if (kategoriSelect) kategoriSelect.addEventListener('change', () => jalankanFilter(true));
    if (perPageSelect) perPageSelect.addEventListener('change', () => jalankanFilter(true));

    jalankanFilter(true);
});
