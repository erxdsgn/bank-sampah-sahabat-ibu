import { initComboSelect } from '../utils/combo-select';
import { formatAngka } from '../utils/helpers';
import { renderPagination } from '../utils/paginations';
import { getChartTheme, observeThemeChange } from '../utils/chart-theme';

document.addEventListener('DOMContentLoaded', () => {
    [
        'filterBulanAwal',
        'filterBulanAkhir',
        'filterTahun',
        'satuanFilter',
        'satuanBulanan',
        'selectBulanStatistik',
        'perPageRiwayat',
    ].forEach((id) => {
        if (document.getElementById(id)) {
            initComboSelect(id, { readonly: true });
        }
    });

    const dashData = window.__DASHBOARD_DATA__ || {};
    const opsiSatuan = Array.isArray(dashData.opsiSatuan) ? dashData.opsiSatuan : [];
    const dataBulananPerSatuan = dashData.setoranPerBulan || {};
    const dataJenisPerSatuan = dashData.statistikJenisSampah || {};
    const dataRentangPerSatuan = dashData.sampahMasukRentangPerSatuan || {};
    const dataTersediaPerSatuan = dashData.sampahTersediaPerSatuan || {};
    const tahunAktif = dashData.tahunDipilih || new Date().getFullYear();

    const namaBulanMap = dashData.namaBulan || {};
    const rangeBulanAwal = Number(dashData.bulanAwal) || 1;
    const rangeBulanAkhir = Number(dashData.bulanAkhir) || 12;

    const WARNA = [
        '#16a34a', '#0284c7', '#ea580c', '#7c3aed', '#0d9488',
        '#4338ca', '#d97706', '#dc2626', '#64748b', '#84cc16',
    ];

    function keArray(value) {
        if (Array.isArray(value)) return value;
        return Object.values(value || {});
    }

    function temukanKeySatuan(key, sumberObjek = null) {
        if (!key) return null;
        const targetLower = String(key).trim().toLowerCase();

        const dariOpsi = opsiSatuan.find(
            (item) => String(item.key).trim().toLowerCase() === targetLower
        );
        if (dariOpsi) return dariOpsi.key;

        if (sumberObjek && typeof sumberObjek === 'object') {
            const dariObj = Object.keys(sumberObjek).find(
                (k) => String(k).trim().toLowerCase() === targetLower
            );
            if (dariObj) return dariObj;
        }

        return key;
    }

    function labelSatuan(key) {
        if (!key) return '';
        const targetLower = String(key).trim().toLowerCase();
        const found = opsiSatuan.find(
            (item) => String(item.key).trim().toLowerCase() === targetLower
        );
        return found ? found.label : String(key).toUpperCase();
    }

    function formatNilaiDenganSatuan(nilai, satuanKey) {
        const num = Number(nilai) || 0;
        const angkaStr = num.toLocaleString('id-ID', {
            maximumFractionDigits: 2,
        });
        const lbl = labelSatuan(satuanKey);
        return lbl ? `${angkaStr} ${lbl}` : angkaStr;
    }

    const filterSatuan = document.getElementById('satuanFilter');
    const selectBulanan = document.getElementById('satuanBulanan');

    function sinkronComboDariSelect(selectId, displayText) {
        const displayEl = document.getElementById(`cari_${selectId}`);
        if (displayEl && displayText) {
            displayEl.value = displayText;
        }
    }

    function setSatuanBulanan(keyBaru, triggerUpdate = true) {
        if (!keyBaru) return;
        const matchedKey = temukanKeySatuan(keyBaru, dataBulananPerSatuan) || keyBaru;

        if (selectBulanan) {
            const opsiCocok = Array.from(selectBulanan.options).find(
                (opt) => String(opt.value).trim().toLowerCase() === String(matchedKey).trim().toLowerCase()
            );
            if (opsiCocok) {
                selectBulanan.value = opsiCocok.value;
                sinkronComboDariSelect('satuanBulanan', opsiCocok.text.trim());
            } else {
                sinkronComboDariSelect('satuanBulanan', labelSatuan(matchedKey));
            }
        }

        if (triggerUpdate) {
            updateBulanan();
        }
    }

    function satuanAktif(selectId) {
        const el = document.getElementById(selectId);
        if (el && el.value) {
            return temukanKeySatuan(el.value, dataBulananPerSatuan) || el.value;
        }
        return opsiSatuan.length ? opsiSatuan[0].key : null;
    }

    function tampilPesan(canvas, kotak, teks) {
        if (canvas) canvas.style.display = 'none';
        if (kotak) {
            kotak.textContent = teks;
            kotak.style.display = 'flex';
        }
    }

    function tampilCanvas(canvas, kotak) {
        if (canvas) canvas.style.display = '';
        if (kotak) kotak.style.display = 'none';
    }

    function hubungkanNilaiSatuanFilter(targetId, sumberData, descId) {
        const targetEl = document.getElementById(targetId);
        if (!targetEl || !filterSatuan) return;

        const descEl = descId ? document.getElementById(descId) : null;
        const descAsli = descEl ? descEl.textContent : '';

        const setDesc = (teks) => {
            if (descEl) descEl.textContent = teks;
        };

        const perbarui = () => {
            const filterVal = filterSatuan.value || 'semua';

            if (filterVal === 'semua') {
                const entries = Object.entries(sumberData || {});
                // Hanya satuan yang benar-benar punya data (> 0)
                const berdata = entries.filter(([, v]) => Number(v) > 0);

                if (berdata.length === 0) {
                    const fallbackSatuan = opsiSatuan[0]?.key || 'kg';
                    targetEl.textContent = formatNilaiDenganSatuan(0, fallbackSatuan);
                    setDesc(descAsli);
                    return;
                }
                if (berdata.length === 1) {
                    const [k, v] = berdata[0];
                    targetEl.textContent = formatNilaiDenganSatuan(v, k);
                    setDesc(descAsli);
                    return;
                }
                // 2+ satuan: ringkas jadi jumlah satuan agar tidak menumpuk
                targetEl.textContent = `${berdata.length} Satuan`;
                setDesc('Pilih satuan untuk lihat detail');
            } else {
                const keyCocok = temukanKeySatuan(filterVal, sumberData) || filterVal;
                const nilai = Number((sumberData || {})[keyCocok] ?? 0);
                targetEl.textContent = formatNilaiDenganSatuan(nilai, keyCocok);
                setDesc(descAsli);
            }
        };

        filterSatuan.addEventListener('change', () => {
            const filterVal = filterSatuan.value || 'semua';
            if (filterVal !== 'semua') {
                setSatuanBulanan(filterVal, false);
            }
            perbarui();
            updateBulanan();
            renderListJenis();
            if (modeAktif === 'donut' && chartJenis) {
                updateJenis();
            }
        });
        perbarui();
    }

    if (filterSatuan) {
        hubungkanNilaiSatuanFilter('nilaiSampahRentang', dataRentangPerSatuan, 'descSampahRentang');
        hubungkanNilaiSatuanFilter('nilaiSampahTersedia', dataTersediaPerSatuan, 'descSampahTersedia');
    }

    const selectBulanStatistik = document.getElementById('selectBulanStatistik');
    const hiddenBulanStatistik = document.getElementById('hiddenBulanStatistik');
    const filterForm = document.getElementById('dashboardFilterForm');
    if (selectBulanStatistik && hiddenBulanStatistik && filterForm) {
        selectBulanStatistik.addEventListener('change', () => {
            hiddenBulanStatistik.value = selectBulanStatistik.value;
            filterForm.submit();
        });
    }

    // ---------------------------------------------------------
    // PAGINATION RIWAYAT SETORAN
    // ---------------------------------------------------------
    let page = 1;

    function renderTable() {
        const tbody = document.getElementById('riwayatTableBody');
        const rows = [...document.querySelectorAll('#riwayatTableBody tr[data-row-riwayat]')];
        const perPageEl = document.getElementById('perPageRiwayat');
        if (!perPageEl || !tbody) return;

        const tinggiSebelum = tbody.getBoundingClientRect().height;
        if (tinggiSebelum > 0) tbody.style.minHeight = tinggiSebelum + 'px';

        const perPage = parseInt(perPageEl.value, 10) || 10;
        const totalPages = Math.ceil(rows.length / perPage) || 1;
        page = Math.min(Math.max(page, 1), totalPages);

        const start = (page - 1) * perPage;
        const akhir = start + perPage;

        rows.forEach((r, i) => {
            const tampil = i >= start && i < akhir;
            r.style.display = tampil ? '' : 'none';
            if (tampil) {
                const noCell = r.querySelector('.riwayat-no');
                if (noCell) noCell.textContent = i + 1;
            }
        });

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                tbody.style.minHeight = '';
            });
        });

        renderPagination({
            infoId: 'riwayatInfoPagination',
            buttonId: 'paginationButtonsRiwayat',
            currentPage: page,
            totalPages,
            totalItems: rows.length,
            perPage,
            unit: 'transaksi',
            onPageChange: (p) => {
                page = p;
                renderTable();
            },
        });
    }

    const perPageRiwayat = document.getElementById('perPageRiwayat');
    if (perPageRiwayat) {
        perPageRiwayat.addEventListener('change', () => {
            page = 1;
            renderTable();
        });
    }

    requestAnimationFrame(() => renderTable());

    // ---------------------------------------------------------
    // CHART BULANAN (BAR CHART)
    // ---------------------------------------------------------
    const canvasBulanan = document.getElementById('setoranBulananChart');
    const kosongBulanan = document.getElementById('bulananKosong');
    const subBulananEl = document.querySelector('.chart-card--large .card-sub');
    let chartBulanan = null;

    function getBulanRange() {
        const hasil = [];
        for (let b = rangeBulanAwal; b <= rangeBulanAkhir; b++) {
            hasil.push(b);
        }
        return hasil;
    }

    function getLabelBulan() {
        return getBulanRange().map((b) => namaBulanMap[b] || `B${b}`);
    }

    function getDataBulananBySatuan(key) {
        if (!key) return [];
        if (dataBulananPerSatuan[key]) return dataBulananPerSatuan[key];

        const targetLower = String(key).trim().toLowerCase();
        const foundKey = Object.keys(dataBulananPerSatuan).find(
            (k) => String(k).trim().toLowerCase() === targetLower
        );
        return foundKey ? dataBulananPerSatuan[foundKey] : [];
    }

    function hitungBulanan(key) {
        const bulanRange = getBulanRange();
        const mapBulan = {};

        keArray(getDataBulananBySatuan(key)).forEach((item, idx) => {
            const bulan = Number(item?.bulan ?? idx + 1);
            const total = Number(item?.total ?? item ?? 0) || 0;
            mapBulan[bulan] = (mapBulan[bulan] || 0) + total;
        });

        return bulanRange.map((b) => mapBulan[b] ?? 0);
    }

    function getPeriodeLabel() {
        const awal = namaBulanMap[rangeBulanAwal] || '';
        const akhir = namaBulanMap[rangeBulanAkhir] || '';
        if (rangeBulanAwal === rangeBulanAkhir) {
            return `${awal} ${tahunAktif}`;
        }
        return `${awal} – ${akhir} ${tahunAktif}`;
    }

    function updateSubJudulBulanan() {
        if (!subBulananEl) return;
        subBulananEl.textContent = `Total jumlah sampah ${getPeriodeLabel()}`;
    }

    function initChartBulanan() {
        if (!canvasBulanan || typeof window.Chart === 'undefined') return;
        const key = satuanAktif('satuanBulanan');
        const tema = getChartTheme();

        chartBulanan = new window.Chart(canvasBulanan, {
            type: 'bar',
            data: {
                labels: getLabelBulan(),
                datasets: [{
                    label: `Jumlah (${labelSatuan(key)})`,
                    data: key ? hitungBulanan(key) : getBulanRange().map(() => 0),
                    backgroundColor: '#16a34a',
                    borderRadius: 8,
                    maxBarThickness: 26,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) =>
                                formatAngka(
                                    ctx.parsed.y,
                                    satuanAktif('satuanBulanan'),
                                    opsiSatuan
                                ),
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: tema.text } },
                    y: {
                        beginAtZero: true,
                        grid: { color: tema.grid },
                        ticks: {
                            color: tema.text,
                            callback: (value) => Number(value).toLocaleString('id-ID'),
                        },
                    },
                },
            },
        });

        updateSubJudulBulanan();
        updateBulanan();
    }

    function updateBulanan() {
        if (!chartBulanan) return;
        const key = satuanAktif('satuanBulanan');
        if (!key) {
            tampilPesan(canvasBulanan, kosongBulanan, 'Belum ada data setoran untuk ditampilkan.');
            return;
        }

        const data = hitungBulanan(key);
        const adaData = data.some((v) => v > 0);

        chartBulanan.data.labels = getLabelBulan();
        chartBulanan.data.datasets[0].data = data;
        chartBulanan.data.datasets[0].label = `Jumlah (${labelSatuan(key)})`;
        chartBulanan.update();

        updateSubJudulBulanan();

        if (adaData) {
            tampilCanvas(canvasBulanan, kosongBulanan);
        } else {
            tampilPesan(
                canvasBulanan,
                kosongBulanan,
                `Belum ada setoran ${labelSatuan(key)} pada periode ${getPeriodeLabel()}.`
            );
        }
    }

    const listBulananEl = document.getElementById('list_satuanBulanan');
    if (listBulananEl && selectBulanan) {
        listBulananEl.addEventListener('click', (e) => {
            const optionEl = e.target.closest('[data-value], .combo-item, .combo-option, li, div');
            if (!optionEl) return;

            const rawVal = (optionEl.dataset?.value ?? optionEl.textContent ?? '').trim();
            if (!rawVal) return;

            const matchedOpt = Array.from(selectBulanan.options).find(
                (opt) =>
                    String(opt.value).trim().toLowerCase() === rawVal.toLowerCase() ||
                    String(opt.text).trim().toLowerCase() === rawVal.toLowerCase()
            );

            if (matchedOpt) {
                setSatuanBulanan(matchedOpt.value, true);
            }
        });
    }

    if (typeof window.Chart !== 'undefined') {
        initChartBulanan();
        if (selectBulanan) {
            sinkronComboDariSelect('satuanBulanan', labelSatuan(selectBulanan.value));
            selectBulanan.addEventListener('change', () => {
                sinkronComboDariSelect('satuanBulanan', labelSatuan(selectBulanan.value));
                updateBulanan();
            });
        }
    } else if (canvasBulanan) {
        tampilPesan(canvasBulanan, kosongBulanan, 'Grafik tidak dapat dimuat.');
    }

    // ---------------------------------------------------------
    // STATISTIK JENIS SAMPAH (DAFTAR & DONUT)
    // ---------------------------------------------------------
    let chartJenis = null;
    let modeAktif = 'daftar';

    function getJenisData() {
        const filterVal = filterSatuan ? (filterSatuan.value || 'semua') : 'semua';
        let items = [];

        if (filterVal === 'semua') {
            Object.entries(dataJenisPerSatuan).forEach(([satuan, arr]) => {
                keArray(arr).forEach((item) => {
                    items.push({
                        nama: item.nama,
                        total: Number(item.total) || 0,
                        satuan,
                    });
                });
            });
        } else {
            const keyCocok = temukanKeySatuan(filterVal, dataJenisPerSatuan) || filterVal;
            keArray(dataJenisPerSatuan[keyCocok]).forEach((item) => {
                items.push({
                    nama: item.nama,
                    total: Number(item.total) || 0,
                    satuan: keyCocok,
                });
            });
        }

        items.sort((a, b) => b.total - a.total);
        return { items, filterVal };
    }

    function renderListJenis() {
        const listEl = document.getElementById('jenisList');
        const totalBadge = document.getElementById('jenisTotalBadge');
        const searchInput = document.getElementById('cariJenisSampah');
        if (!listEl || !totalBadge) return;

        const { items, filterVal } = getJenisData();

        const totalPerSatuan = {};
        items.forEach((it) => {
            totalPerSatuan[it.satuan] = (totalPerSatuan[it.satuan] || 0) + it.total;
        });

        const entriesTotal = Object.entries(totalPerSatuan);
        if (entriesTotal.length === 0) {
            const defaultSatuan = filterVal !== 'semua' ? filterVal : (opsiSatuan[0]?.key || 'kg');
            totalBadge.textContent = `Total: ${formatNilaiDenganSatuan(0, defaultSatuan)}`;
        } else {
            const totalText = entriesTotal
                .map(([sat, val]) => formatNilaiDenganSatuan(val, sat))
                .join(' · ');
            totalBadge.textContent = `Total: ${totalText}`;
        }

        const keyword = (searchInput?.value || '').toLowerCase().trim();
        let html = '';
        let adaHasil = false;

        items.forEach((item) => {
            if (keyword && !String(item.nama || '').toLowerCase().includes(keyword)) return;
            adaHasil = true;

            const totalSatuanItem = totalPerSatuan[item.satuan] || item.total || 1;
            const persen = totalSatuanItem > 0 ? Math.min(100, (item.total / totalSatuanItem) * 100) : 0;
            const label = labelSatuan(item.satuan) || 'Kg';
            const nilaiFormatted = formatNilaiDenganSatuan(item.total, item.satuan);

            html += `
                <div class="jenis-item">
                    <div class="jenis-item-head">
                        <div class="jenis-item-name">
                            <span>${item.nama}</span>
                            <span class="jenis-badge">${label}</span>
                        </div>
                        <div class="jenis-item-val">${nilaiFormatted}</div>
                    </div>
                    <div class="jenis-item-bar">
                        <div class="jenis-item-bar-inner" style="width: ${persen}%;"></div>
                    </div>
                </div>
            `;
        });

        if (!adaHasil) {
            const msg = items.length === 0
                ? 'Belum ada data.'
                : `Tidak ada hasil untuk "${keyword}".`;
            html = `<div style="text-align:center; padding: 20px; color: var(--t-muted); font-size:13px;">${msg}</div>`;
        }

        listEl.innerHTML = html;
    }

    function renderCenterLabelJenis(total, key) {
        const elValue = document.getElementById('jenisCenterValue');
        if (!elValue) return;
        const satuanEfektif = key || (opsiSatuan[0]?.key || 'kg');
        elValue.textContent = formatNilaiDenganSatuan(total, satuanEfektif);
    }

    function renderLegendJenis(items, key) {
        const legend = document.getElementById('jenisLegend');
        if (!legend) return;

        const total = items.reduce((a, b) => a + b.total, 0);
        if (!items.length || total <= 0) {
            legend.hidden = true;
            legend.innerHTML = '';
            return;
        }

        legend.hidden = false;
        legend.innerHTML = items
            .map((item, i) => {
                const warna = WARNA[i % WARNA.length];
                const namaAman = String(item.nama || '').replace(/"/g, '&quot;');
                const satuanItem = key || item.satuan || (opsiSatuan[0]?.key || 'kg');
                return `
                    <div class="chart-legend-item">
                        <span class="chart-legend-dot" style="background:${warna}"></span>
                        <span class="chart-legend-name" title="${namaAman}">${namaAman}</span>
                        <span class="chart-legend-value">${formatNilaiDenganSatuan(item.total, satuanItem)}</span>
                    </div>
                `;
            })
            .join('');
    }

    function initChartJenis() {
        const canvasJenis = document.getElementById('jenisSampahChart');
        if (!canvasJenis || typeof window.Chart === 'undefined') return;

        const { items, filterVal } = getJenisData();
        const key = filterVal === 'semua' ? null : filterVal;
        const nama = items.map((i) => i.nama);
        const nilai = items.map((i) => i.total);

        chartJenis = new window.Chart(canvasJenis, {
            type: 'doughnut',
            data: {
                labels: nama,
                datasets: [{
                    label: 'Jumlah',
                    data: nilai,
                    backgroundColor: nama.map((_, i) => WARNA[i % WARNA.length]),
                    borderColor: 'transparent',
                    borderWidth: 0,
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const it = items[ctx.dataIndex];
                                const sat = key || it?.satuan || (opsiSatuan[0]?.key || 'kg');
                                return ` ${ctx.label}: ${formatNilaiDenganSatuan(ctx.parsed, sat)}`;
                            },
                        },
                    },
                },
            },
        });

        updateJenis();
    }

    function updateJenis() {
        const canvasJenis = document.getElementById('jenisSampahChart');
        const kosongJenis = document.getElementById('jenisKosong');
        if (!chartJenis) return;

        const { items, filterVal } = getJenisData();
        const key = filterVal === 'semua' ? null : filterVal;
        const nama = items.map((i) => i.nama);
        const nilai = items.map((i) => i.total);
        const total = nilai.reduce((a, b) => a + b, 0);

        chartJenis.data.labels = nama;
        chartJenis.data.datasets[0].data = nilai;
        chartJenis.data.datasets[0].backgroundColor = nama.map(
            (_, i) => WARNA[i % WARNA.length]
        );
        chartJenis.update();

        renderCenterLabelJenis(total, key);
        renderLegendJenis(items, key);

        if (total > 0) {
            tampilCanvas(canvasJenis, kosongJenis);
        } else {
            tampilPesan(canvasJenis, kosongJenis, 'Belum ada data setoran untuk ditampilkan.');
            renderLegendJenis([], key);
        }
    }

    renderListJenis();

    const cariJenisSampah = document.getElementById('cariJenisSampah');
    if (cariJenisSampah) {
        cariJenisSampah.addEventListener('input', renderListJenis);
    }

    const btnModeDaftar = document.getElementById('btnModeDaftar');
    const btnModeDonut = document.getElementById('btnModeDonut');
    const modeDaftarEl = document.getElementById('jenisModeDaftar');
    const modeDonutEl = document.getElementById('jenisModeDonut');

    function setMode(mode) {
        modeAktif = mode;

        if (btnModeDaftar) btnModeDaftar.classList.toggle('active', mode === 'daftar');
        if (btnModeDonut) btnModeDonut.classList.toggle('active', mode === 'donut');

        if (modeDaftarEl) modeDaftarEl.style.display = mode === 'daftar' ? '' : 'none';
        if (modeDonutEl) modeDonutEl.style.display = mode === 'donut' ? '' : 'none';

        if (mode === 'daftar') {
            renderListJenis();
        } else {
            if (!chartJenis) {
                initChartJenis();
            } else {
                updateJenis();
            }
            requestAnimationFrame(() => {
                if (chartJenis) chartJenis.resize();
            });
        }
    }

    if (btnModeDaftar) {
        btnModeDaftar.addEventListener('click', () => setMode('daftar'));
    }
    if (btnModeDonut) {
        btnModeDonut.addEventListener('click', () => setMode('donut'));
    }

    // ---------------------------------------------------------
    // TEMA CHART
    // ---------------------------------------------------------
    function terapkanTemaChart() {
        const tema = getChartTheme();
        if (chartBulanan) {
            chartBulanan.options.scales.x.ticks.color = tema.text;
            chartBulanan.options.scales.y.ticks.color = tema.text;
            chartBulanan.options.scales.y.grid.color = tema.grid;
            chartBulanan.update('none');
        }
        if (chartJenis) {
            chartJenis.data.datasets[0].borderColor = 'transparent';
            chartJenis.update('none');
        }
        if (window.Chart) window.Chart.defaults.color = tema.text;
    }

    terapkanTemaChart();
    if (typeof observeThemeChange === 'function') {
        observeThemeChange(terapkanTemaChart);
    }
});
