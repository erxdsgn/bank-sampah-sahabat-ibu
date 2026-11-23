import { initComboSelect } from '../utils/combo-select';
import { formatAngka } from '../utils/helpers';
import { renderPagination } from '../utils/paginations';
import { getChartTheme, observeThemeChange } from '../utils/chart-theme';

document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi combo select
    ['satuanTotalSampah', 'satuanBulanan', 'satuanJenis', 'perPageRiwayat']
        .forEach(initComboSelect);

    // Data dari server
    const dashData = window.__DASHBOARD_DATA__ || {};
    const opsiSatuan = Array.isArray(dashData.opsiSatuan) ? dashData.opsiSatuan : [];
    const dataBulananPerSatuan = dashData.setoranPerBulan || {};
    const dataJenisPerSatuan = dashData.statistikJenisSampah || {};
    const dataTotalSampahPerSatuan = dashData.totalJumlahSampahPerSatuan || {};

    const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const WARNA = [
        '#16a34a', '#0284c7', '#ea580c', '#7c3aed', '#0d9488',
        '#4338ca', '#d97706', '#dc2626', '#64748b', '#84cc16'
    ];

    function keArray(value) {
        if (Array.isArray(value)) return value;
        return Object.values(value || {});
    }

    function labelSatuan(key) {
        const found = opsiSatuan.find((item) => item.key === key);
        return found ? found.label : '';
    }

    function satuanAktif(selectId) {
        const el = document.getElementById(selectId);
        if (el && el.value) return el.value;
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

    // Update kartu Total Sampah Disetor
    const selectTotalSampah = document.getElementById('satuanTotalSampah');
    const elNilaiTotalSampah = document.getElementById('nilaiTotalSampah');

    function updateTotalSampahCard() {
        if (!selectTotalSampah || !elNilaiTotalSampah) return;
        const key = selectTotalSampah.value;
        const nilaiRaw = dataTotalSampahPerSatuan[key] || 0;
        elNilaiTotalSampah.textContent = (Number(nilaiRaw) || 0).toLocaleString('id-ID', {
            maximumFractionDigits: 2,
        });
    }

    if (selectTotalSampah) {
        selectTotalSampah.addEventListener('change', updateTotalSampahCard);
        updateTotalSampahCard();
    }

    // Pagination riwayat setoran
    let page = 1;

    function renderTable() {
        const rows = [...document.querySelectorAll('#riwayatTableBody tr[data-row-riwayat]')];
        const perPageEl = document.getElementById('perPageRiwayat');
        if (!perPageEl) return;

        const perPage = parseInt(perPageEl.value, 10) || 10;
        const totalPages = Math.ceil(rows.length / perPage) || 1;
        page = Math.min(Math.max(page, 1), totalPages);

        rows.forEach((r) => {
            r.style.display = 'none';
        });

        const start = (page - 1) * perPage;
        rows.slice(start, start + perPage).forEach((r, i) => {
            r.style.display = '';
            const noCell = r.querySelector('.riwayat-no');
            if (noCell) noCell.textContent = start + i + 1;
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

    renderTable();

    // Grafik bulanan & jenis sampah
    const canvasBulanan = document.getElementById('setoranBulananChart');
    const canvasJenis = document.getElementById('jenisSampahChart');
    const kosongBulanan = document.getElementById('bulananKosong');
    const kosongJenis = document.getElementById('jenisKosong');

    if (typeof window.Chart === 'undefined') {
        const pesan = 'Grafik tidak dapat dimuat.';
        tampilPesan(canvasBulanan, kosongBulanan, pesan);
        tampilPesan(canvasJenis, kosongJenis, pesan);
        return;
    }

    function hitungBulanan(key) {
        const hasil = Array(12).fill(0);
        keArray(dataBulananPerSatuan[key]).forEach((item) => {
            const idx = Number(item.bulan) - 1;
            if (idx >= 0 && idx < 12) {
                hasil[idx] = Number(item.total) || 0;
            }
        });
        return hasil;
    }

    function hitungJenis(key) {
        const items = keArray(dataJenisPerSatuan[key]);
        return {
            nama: items.map((i) => i.nama || 'Tanpa nama'),
            nilai: items.map((i) => Number(i.total) || 0),
        };
    }

    function warnaJenis(nama) {
        return nama.map((_, i) => WARNA[i % WARNA.length]);
    }

    let chartBulanan = null;
    let chartJenis = null;

    if (canvasBulanan) {
        const key = satuanAktif('satuanBulanan');
        const tema = getChartTheme();

        chartBulanan = new window.Chart(canvasBulanan, {
            type: 'bar',
            data: {
                labels: BULAN,
                datasets: [{
                    label: `Jumlah (${labelSatuan(key)})`,
                    data: key ? hitungBulanan(key) : Array(12).fill(0),
                    backgroundColor: '#16a34a',
                    borderRadius: 6,
                    maxBarThickness: 32,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => formatAngka(ctx.parsed.y, satuanAktif('satuanBulanan'), opsiSatuan),
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: tema.text },
                    },
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

        chartBulanan.data.datasets[0].data = data;
        chartBulanan.data.datasets[0].label = `Jumlah (${labelSatuan(key)})`;
        chartBulanan.update();

        if (adaData) {
            tampilCanvas(canvasBulanan, kosongBulanan);
        } else {
            tampilPesan(canvasBulanan, kosongBulanan, `Belum ada setoran ${labelSatuan(key)} pada tahun ini.`);
        }
    }

    if (canvasJenis) {
        const key = satuanAktif('satuanJenis');
        const awal = key ? hitungJenis(key) : { nama: [], nilai: [] };
        const tema = getChartTheme();

        chartJenis = new window.Chart(canvasJenis, {
            type: 'doughnut',
            data: {
                labels: awal.nama,
                datasets: [{
                    label: 'Jumlah',
                    data: awal.nilai,
                    backgroundColor: warnaJenis(awal.nama),
                    borderColor: tema.border,
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: tema.text,
                            boxWidth: 12,
                            padding: 14,
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.label}: ${formatAngka(ctx.parsed, satuanAktif('satuanJenis'), opsiSatuan)}`,
                        },
                    },
                },
            },
        });
    }

    function updateJenis() {
        if (!chartJenis) return;
        const key = satuanAktif('satuanJenis');
        const hasil = key ? hitungJenis(key) : { nama: [], nilai: [] };
        const total = hasil.nilai.reduce((a, b) => a + b, 0);

        chartJenis.data.labels = hasil.nama;
        chartJenis.data.datasets[0].data = hasil.nilai;
        chartJenis.data.datasets[0].backgroundColor = warnaJenis(hasil.nama);
        chartJenis.update();

        if (total > 0) {
            tampilCanvas(canvasJenis, kosongJenis);
        } else {
            tampilPesan(canvasJenis, kosongJenis, 'Belum ada data setoran untuk ditampilkan.');
        }
    }

    updateBulanan();
    updateJenis();

    const selectBulanan = document.getElementById('satuanBulanan');
    const selectJenis = document.getElementById('satuanJenis');
    if (selectBulanan) selectBulanan.addEventListener('change', updateBulanan);
    if (selectJenis) selectJenis.addEventListener('change', updateJenis);

    function terapkanTemaChart() {
        const tema = getChartTheme();
        if (chartBulanan) {
            chartBulanan.options.scales.x.ticks.color = tema.text;
            chartBulanan.options.scales.y.ticks.color = tema.text;
            chartBulanan.options.scales.y.grid.color = tema.grid;
            chartBulanan.update('none');
        }
        if (chartJenis) {
            chartJenis.options.plugins.legend.labels.color = tema.text;
            chartJenis.data.datasets[0].borderColor = tema.border;
            chartJenis.update('none');
        }
        window.Chart.defaults.color = tema.text;
    }

    terapkanTemaChart();
    if (typeof observeThemeChange === 'function') {
        observeThemeChange(terapkanTemaChart);
    }
});
