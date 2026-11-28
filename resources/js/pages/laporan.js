import { initComboSelect } from '../utils/combo-select';
import { escapeHtml } from '../utils/helpers';

function fmtDate(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

function parseIndoDate(text) {
    const m = text.match(/(\d{1,2})\/(\d{1,2})\/(\d{4})/);
    if (!m) return null;
    return new Date(parseInt(m[3], 10), parseInt(m[2], 10) - 1, parseInt(m[1], 10));
}

function tutupModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('active');
}

function updateCetakInfo() {
    const jenisEl = document.querySelector('input[name="jenis_laporan"]:checked');
    const infoEl = document.getElementById('cetakInfoJumlah');
    if (!jenisEl || !infoEl) return;

    const jenis = jenisEl.value;
    let label = 'ringkasan laporan';

    if (jenis === 'penjualan') {
        const n = document.querySelectorAll('[data-panel="penjualan"] tbody tr:not(.empty-row)').length;
        label = `${n} transaksi penjualan`;
    } else if (jenis === 'setoran') {
        const n = document.querySelectorAll('[data-panel="setoran"] tbody tr:not(.empty-row)').length;
        label = `${n} transaksi setoran`;
    }

    infoEl.textContent = label;
}

function bukaModalCetak() {
    const now = new Date();
    const start = new Date(now.getFullYear(), now.getMonth(), 1);
    const mulaiEl = document.getElementById('cetakTanggalMulai');
    const akhirEl = document.getElementById('cetakTanggalAkhir');
    const modalEl = document.getElementById('modalCetak');

    if (mulaiEl) mulaiEl.value = fmtDate(start);
    if (akhirEl) akhirEl.value = fmtDate(now);
    updateCetakInfo();
    if (modalEl) modalEl.classList.add('active');
}

function setPrintOrientation(orientasi) {
    let style = document.getElementById('print-orientation-style');
    if (!style) {
        style = document.createElement('style');
        style.id = 'print-orientation-style';
        document.head.appendChild(style);
    }
    style.textContent = `@media print { @page { size: A4 ${orientasi}; margin: 0; } }`;
}

function extractRows(panel, tglMulai, tglAkhir) {
    const rows = Array.from(panel.querySelectorAll('tbody tr')).filter(r => !r.classList.contains('empty-row'));
    const start = tglMulai ? new Date(`${tglMulai}T00:00:00`) : null;
    const end = tglAkhir ? new Date(`${tglAkhir}T23:59:59`) : null;

    const headers = Array.from(panel.querySelectorAll('table thead th'));
    const numericIdx = new Set();
    headers.forEach((th, i) => {
        if (th.classList.contains('col-num')) numericIdx.add(i);
    });

    const result = [];
    rows.forEach(row => {
        const dateText = row.cells[0]?.textContent.trim() || '';
        const cellDate = parseIndoDate(dateText);
        if (start && cellDate && cellDate < start) return;
        if (end && cellDate && cellDate > end) return;

        const cells = Array.from(row.cells).map((cell, idx) => {
            const party = cell.querySelector('.party-info');
            if (party) {
                const nama = party.querySelector('strong')?.textContent.trim() || '';
                const nik = party.querySelector('.mono')?.textContent.trim() || '';
                return { text: nik ? `${nama} (${nik})` : nama, numeric: numericIdx.has(idx) };
            }
            return {
                text: cell.textContent.trim().replace(/\s+/g, ' '),
                numeric: numericIdx.has(idx),
            };
        });
        result.push(cells);
    });
    return result;
}

function buildRingkasan() {
    const cards = document.querySelectorAll('.laporan-page .summary-grid .summary-card');
    let html = '<div class="print-summary">';
    cards.forEach(card => {
        const label = card.querySelector('.summary-label')?.textContent?.trim() || '';
        const valueEl = card.querySelector('.summary-value, .summary-value-badges');
        let value = '';
        if (valueEl) {
            if (valueEl.classList.contains('summary-value-badges')) {
                value = Array.from(valueEl.querySelectorAll('.volume-badge'))
                    .map(b => b.textContent.trim().replace(/\s+/g, ' ')).join(' · ');
            } else {
                value = valueEl.textContent.trim().replace(/\s+/g, ' ');
            }
        }
        if (label && value) {
            html += `<div class="print-summary-card"><div class="label">${escapeHtml(label)}</div><div class="value">${escapeHtml(value)}</div></div>`;
        }
    });
    html += '</div>';

    const np = document.querySelectorAll('[data-panel="penjualan"] tbody tr:not(.empty-row)').length;
    const ns = document.querySelectorAll('[data-panel="setoran"] tbody tr:not(.empty-row)').length;

    html += `
        <h3 class="print-section-title">Ringkasan Aktivitas (Halaman Ini)</h3>
        <table class="print-table">
            <thead>
                <tr>
                    <th>Aktivitas</th>
                    <th class="col-num">Jumlah Data</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Penjualan ke Pengepul</td><td class="col-num">${np} transaksi</td></tr>
                <tr><td>Setoran Warga</td><td class="col-num">${ns} transaksi</td></tr>
            </tbody>
        </table>`;
    return html;
}

function buildTabel(panelKey, tglMulai, tglAkhir) {
    const panel = document.querySelector(`[data-panel="${panelKey}"]`);
    if (!panel) return '<p class="empty-print">Data tidak tersedia.</p>';

    const rows = extractRows(panel, tglMulai, tglAkhir);
    if (!rows.length) return '<p class="empty-print">Tidak ada data pada periode ini.</p>';

    const headerCells = Array.from(panel.querySelectorAll('table thead th'));
    const judulTabel = panelKey === 'penjualan' ? 'Detail Penjualan' : 'Detail Setoran Warga';

    let html = `<h3 class="print-section-title">${escapeHtml(judulTabel)}</h3>`;
    html += '<table class="print-table"><thead><tr>';
    headerCells.forEach(th => {
        const isNum = th.classList.contains('col-num');
        html += `<th${isNum ? ' class="col-num"' : ''}>${escapeHtml(th.textContent.trim())}</th>`;
    });
    html += '</tr></thead><tbody>';
    rows.forEach(r => {
        html += '<tr>';
        r.forEach(cell => {
            const isNum = cell.numeric;
            const content = isNum ? `<strong>${escapeHtml(cell.text)}</strong>` : escapeHtml(cell.text);
            html += `<td${isNum ? ' class="col-num"' : ''}>${content}</td>`;
        });
        html += '</tr>';
    });
    html += '</tbody></table>';
    return html;
}

function buildPrintHTML(jenis, tglMulai, tglAkhir) {
    const tanggalCetak = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
    const fmtIndo = d => d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
    let periodeText = 'Semua Periode';
    if (tglMulai && tglAkhir) periodeText = `${fmtIndo(new Date(`${tglMulai}T00:00:00`))} s/d ${fmtIndo(new Date(`${tglAkhir}T00:00:00`))}`;
    else if (tglMulai) periodeText = `Sejak ${fmtIndo(new Date(`${tglMulai}T00:00:00`))}`;
    else if (tglAkhir) periodeText = `Hingga ${fmtIndo(new Date(`${tglAkhir}T00:00:00`))}`;

    let judul = '';
    let konten = '';
    if (jenis === 'ringkasan') {
        judul = 'LAPORAN RINGKASAN';
        konten = buildRingkasan();
    } else if (jenis === 'penjualan') {
        judul = 'LAPORAN PENJUALAN';
        konten = buildTabel('penjualan', tglMulai, tglAkhir);
    } else {
        judul = 'LAPORAN SETORAN WARGA';
        konten = buildTabel('setoran', tglMulai, tglAkhir);
    }

    return `
        <div class="print-header">
            <h1>BANK SAMPAH</h1>
            <h2>${escapeHtml(judul)}</h2>
        </div>
        <div class="print-meta">
            Periode: ${escapeHtml(periodeText)} &nbsp;·&nbsp; Dicetak: ${escapeHtml(tanggalCetak)}
        </div>
        ${konten}
        <div class="print-signature">
            <div class="sig-block">
                <div class="sig-date">${escapeHtml(tanggalCetak)}</div>
                <div>Mengetahui,</div>
                <div class="sig-line">Ketua Bank Sampah</div>
            </div>
        </div>
        <div class="print-footer">
            Dicetak dari Sistem Informasi Bank Sampah
        </div>`;
}

function cetakLaporan() {
    const jenis = document.querySelector('input[name="jenis_laporan"]:checked')?.value || 'ringkasan';
    const orientasi = document.querySelector('input[name="orientasi"]:checked')?.value || 'portrait';
    const tglMulai = document.getElementById('cetakTanggalMulai')?.value || '';
    const tglAkhir = document.getElementById('cetakTanggalAkhir')?.value || '';

    setPrintOrientation(orientasi);
    const area = document.getElementById('printArea');
    if (area) area.innerHTML = buildPrintHTML(jenis, tglMulai, tglAkhir);
    tutupModal('modalCetak');
    setTimeout(() => window.print(), 150);
}

function initTabs() {
    const tabs = document.querySelectorAll('.laporan-page .report-tab[data-tab]');
    const panels = document.querySelectorAll('.laporan-page .report-panel[data-panel]');
    if (!tabs.length || !panels.length) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            tabs.forEach(t => {
                const active = t === tab;
                t.classList.toggle('tab-active', active);
                t.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            panels.forEach(panel => {
                panel.hidden = panel.dataset.panel !== target;
            });

            const url = new URL(window.location.href);
            url.searchParams.set('tab', target);
            window.history.replaceState({}, '', url);
        });
    });
}

function initPerPageCombo(selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;

    // Saat nilai berubah: muat ulang halaman dengan jumlah baris baru
    select.addEventListener('change', () => {
        const url = new URL(window.location.href);
        const navParam = select.dataset.navParam;
        const pageParam = select.dataset.navPageParam;
        if (navParam) url.searchParams.set(navParam, select.value);
        if (pageParam) url.searchParams.delete(pageParam);
        if (selectId === 'perPagePenjualan') url.searchParams.set('tab', 'penjualan');
        if (selectId === 'perPageSetoran') url.searchParams.set('tab', 'setoran');
        window.location.href = url.toString();
    });

    // Dropdown yang sama dengan halaman Keuangan
    initComboSelect(selectId, { readonly: true });
}

// ---------- Pencarian & paginasi tanpa refresh ----------
// Data dipaginasi di server, jadi pencarian tetap mencari ke seluruh data:
// halaman diambil lewat fetch lalu hanya tabel, info, pagination, dan jumlah tab yang diganti.
const panelRequest = {};

async function muatPanel(panel, urlStr) {
    const key = panel.dataset.panel;

    if (panelRequest[key]) panelRequest[key].abort();
    const ctrl = new AbortController();
    panelRequest[key] = ctrl;
    panel.classList.add('is-loading');

    try {
        const res = await fetch(urlStr, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            signal: ctrl.signal,
        });
        if (!res.ok) throw new Error('Gagal memuat data');

        const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
        const baru = doc.querySelector(`[data-panel="${key}"]`);
        if (!baru) throw new Error('Panel tidak ditemukan');

        const ganti = (selector) => {
            const dari = baru.querySelector(selector);
            const ke = panel.querySelector(selector);
            if (dari && ke) ke.innerHTML = dari.innerHTML;
        };
        ganti('tbody');
        ganti('.table-info');
        ganti('.pagination-wrap');

        // Jumlah di label tab
        const countBaru = doc.querySelector(`.report-tab[data-tab="${key}"] .tab-count`);
        const countLama = document.querySelector(`.report-tab[data-tab="${key}"] .tab-count`);
        if (countBaru && countLama) countLama.textContent = countBaru.textContent;

        // Tombol Reset hanya ada saat sedang mencari
        const toolbar = panel.querySelector('.toolbar-left');
        const resetBaru = baru.querySelector('.toolbar-left a.btn');
        const resetLama = toolbar?.querySelector('a.btn');
        if (toolbar) {
            if (resetBaru && !resetLama) toolbar.appendChild(document.importNode(resetBaru, true));
            if (!resetBaru && resetLama) resetLama.remove();
        }

        window.history.replaceState({}, '', urlStr);
    } catch (err) {
        if (err.name === 'AbortError') return;
        window.location.href = urlStr; // fallback: muat ulang biasa
    } finally {
        if (panelRequest[key] === ctrl) panel.classList.remove('is-loading');
    }
}

function urlPencarian(panel, input) {
    const key = panel.dataset.panel;
    const url = new URL(window.location.href);
    url.searchParams.set('tab', key);

    const nilai = input.value.trim();
    if (nilai) url.searchParams.set(input.name, nilai);
    else url.searchParams.delete(input.name);

    const perPage = input.closest('form')?.querySelector(`input[name="${key}_per_page"]`);
    if (perPage) url.searchParams.set(perPage.name, perPage.value);

    url.searchParams.delete(`${key}_page`);
    return url.toString();
}

function initLiveSearch(inputId) {
    const input = document.getElementById(inputId);
    const form = input?.closest('form');
    const panel = input?.closest('[data-panel]');
    if (!input || !form || !panel) return;

    let timer = null;
    let terakhir = input.value.trim();

    function cari() {
        const nilai = input.value.trim();
        if (nilai === terakhir) return;
        terakhir = nilai;
        muatPanel(panel, urlPencarian(panel, input));
    }

    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(cari, 250);
    });

    // Enter: cari langsung tanpa menunggu jeda & tanpa refresh
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        clearTimeout(timer);
        cari();
    });

    // Tombol Reset (dibuat ulang tiap respons, jadi pakai event delegation)
    panel.querySelector('.toolbar-left')?.addEventListener('click', (e) => {
        const reset = e.target.closest('a.btn');
        if (!reset) return;
        e.preventDefault();
        input.value = '';
        clearTimeout(timer);
        cari();
        input.focus();
    });

    // Pagination tanpa refresh
    panel.addEventListener('click', (e) => {
        const link = e.target.closest('.pagination-wrap a');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href || href === '#' || link.getAttribute('aria-disabled') === 'true') {
            e.preventDefault();
            return;
        }
        e.preventDefault();
        muatPanel(panel, link.href);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    initLiveSearch('searchPenjualan');
    initLiveSearch('searchSetoran');
    initPerPageCombo('perPagePenjualan');
    initPerPageCombo('perPageSetoran');

    document.querySelectorAll('.laporan-page .print-radio-card').forEach(card => {
        card.addEventListener('click', function() {
            const grid = this.closest('.print-radio-grid');
            if (!grid) return;
            grid.querySelectorAll('.print-radio-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            const input = this.querySelector('input[type="radio"]');
            if (input) input.checked = true;
            updateCetakInfo();
        });
    });

    document.querySelectorAll('.laporan-page .modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) overlay.classList.remove('active');
        });
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.laporan-page .modal-overlay.active').forEach(m => m.classList.remove('active'));
        }
    });
});

Object.assign(window, {
    bukaModalCetak,
    tutupModal,
    cetakLaporan
});
