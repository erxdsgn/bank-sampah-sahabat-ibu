document.addEventListener('DOMContentLoaded', function() {

            /* ================= COMBOBOX (Custom Select) ================= */
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
                    const terpilih = list.querySelector('.is-selected');
                    if (terpilih) terpilih.scrollIntoView({
                        block: 'nearest'
                    });
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
                    items[aktif].scrollIntoView({
                        block: 'nearest'
                    });
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

                sync();
            }

            initComboSelect('satuanTotalSampah');
            initComboSelect('satuanBulanan');
            initComboSelect('satuanJenis');

            /* ================= PAGINASI RIWAYAT SETORAN ================= */

            let riwayatPage = 1;

            function renderRiwayatTable() {
                const rows = Array.from(document.querySelectorAll('#riwayatTableBody tr[data-row-riwayat]'));
                const perPageSelect = document.getElementById('perPageRiwayat');
                const paginationContainer = document.getElementById('paginationButtonsRiwayat');
                const tableInfo = document.getElementById('riwayatInfoPagination');

                if (!rows.length || !perPageSelect || !paginationContainer) return;

                const perPage = parseInt(perPageSelect.value, 10) || 10;
                const totalPages = Math.ceil(rows.length / perPage) || 1;
                riwayatPage = Math.min(Math.max(riwayatPage, 1), totalPages);

                rows.forEach(r => r.style.display = 'none');

                const start = (riwayatPage - 1) * perPage;
                const end = start + perPage;
                rows.slice(start, end).forEach((r, i) => {
                    r.style.display = '';
                    const selNo = r.querySelector('.riwayat-no');
                    if (selNo) selNo.textContent = start + i + 1;
                });

                if (tableInfo) {
                    tableInfo.innerHTML = `Menampilkan <strong>${start + 1}</strong> - <strong>${Math.min(end, rows.length)}</strong> dari <strong>${rows.length}</strong> transaksi`;
                }

                let html = `<button class="btn btn--ghost" type="button" data-page="${riwayatPage - 1}" ${riwayatPage === 1 ? 'disabled' : ''}>‹</button>`;

                for (let i = 1; i <= totalPages; i++) {
                    if (i === 1 || i === totalPages || (i >= riwayatPage - 1 && i <= riwayatPage + 1)) {
                        html += `<button class="btn ${i === riwayatPage ? 'btn--primary' : 'btn--ghost'}" type="button" data-page="${i}">${i}</button>`;
                    } else if (i === riwayatPage - 2 || i === riwayatPage + 2) {
                        html += '<span class="pagination-dots">…</span>';
                    }
                }

                html += `<button class="btn btn--ghost" type="button" data-page="${riwayatPage + 1}" ${riwayatPage === totalPages ? 'disabled' : ''}>›</button>`;
                paginationContainer.innerHTML = html;
            }

            const paginationButtonsRiwayat = document.getElementById('paginationButtonsRiwayat');
            if (paginationButtonsRiwayat) {
                paginationButtonsRiwayat.addEventListener('click', function (e) {
                    const btn = e.target.closest('button[data-page]');
                    if (!btn || btn.disabled) return;
                    riwayatPage = parseInt(btn.dataset.page, 10);
                    renderRiwayatTable();
                });
            }

            const perPageRiwayat = document.getElementById('perPageRiwayat');
            if (perPageRiwayat) {
                perPageRiwayat.addEventListener('change', function () {
                    riwayatPage = 1;
                    renderRiwayatTable();
                });
            }

            initComboSelect('perPageRiwayat');
            renderRiwayatTable();

            /* ================= DATA DARI CONTROLLER ================= */
            const opsiSatuan = window.DashboardPageConfig.opsiSatuan;
            const dataBulananPerSatuan = window.DashboardPageConfig.dataBulananPerSatuan;
            const dataJenisPerSatuan = window.DashboardPageConfig.dataJenisPerSatuan;
            const dataTotalSampahPerSatuan = window.DashboardPageConfig.dataTotalSampahPerSatuan;

            const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            const WARNA = [
                '#16a34a', '#0284c7', '#ea580c', '#7c3aed', '#0d9488',
                '#4338ca', '#d97706', '#dc2626', '#64748b', '#84cc16'
            ];

            /* ================= HELPER ================= */
            function keArray(value) {
                if (Array.isArray(value)) return value;
                return Object.values(value || {});
            }

            function labelSatuan(key) {
                const o = opsiSatuan.find(function(x) {
                    return x.key === key;
                });
                return o ? o.label : '';
            }

            function formatAngka(value, key) {
                const angka = (Number(value) || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                });
                const label = labelSatuan(key);
                return label ? angka + ' ' + label : angka;
            }

            function satuanAktif(selectId) {
                const el = document.getElementById(selectId);
                if (el) return el.value;
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

            /* ================= UPDATE KARTU TOTAL SAMPAH ================= */
            const selectTotalSampah = document.getElementById('satuanTotalSampah');
            const elNilaiTotalSampah = document.getElementById('nilaiTotalSampah');

            function updateTotalSampahCard() {
                if (!selectTotalSampah || !elNilaiTotalSampah) return;
                const key = selectTotalSampah.value;
                const nilaiRaw = dataTotalSampahPerSatuan[key] || 0;
                elNilaiTotalSampah.textContent = (Number(nilaiRaw) || 0).toLocaleString('id-ID', {
                    maximumFractionDigits: 2
                });
            }

            if (selectTotalSampah) {
                selectTotalSampah.addEventListener('change', updateTotalSampahCard);
                updateTotalSampahCard();
            }

            /* ================= INISIALISASI GRAFIK ================= */
            const canvasBulanan = document.getElementById('setoranBulananChart');
            const canvasJenis = document.getElementById('jenisSampahChart');
            const kosongBulanan = document.getElementById('bulananKosong');
            const kosongJenis = document.getElementById('jenisKosong');

            if (typeof Chart === 'undefined') {
                const pesan = 'Grafik tidak dapat dimuat. Periksa koneksi internet.';
                tampilPesan(canvasBulanan, kosongBulanan, pesan);
                tampilPesan(canvasJenis, kosongJenis, pesan);
                return;
            }

            function getThemeColors() {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                return {
                    text: isDark ? '#aab6c8' : '#6b7280',
                    grid: isDark ? '#2c3950' : '#e5e7eb',
                    border: isDark ? '#151d2f' : '#ffffff'
                };
            }

            function hitungBulanan(key) {
                const hasil = Array(12).fill(0);
                keArray(dataBulananPerSatuan[key]).forEach(function(item) {
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
                    nama: items.map(function(i) {
                        return i.nama || 'Tanpa nama';
                    }),
                    nilai: items.map(function(i) {
                        return Number(i.total) || 0;
                    })
                };
            }

            let chartBulanan = null;
            let chartJenis = null;

            if (canvasBulanan) {
                const key = satuanAktif('satuanBulanan');
                const warnaTema = getThemeColors();

                chartBulanan = new Chart(canvasBulanan, {
                    type: 'bar',
                    data: {
                        labels: BULAN,
                        datasets: [{
                            label: 'Jumlah (' + labelSatuan(key) + ')',
                            data: key ? hitungBulanan(key) : Array(12).fill(0),
                            backgroundColor: '#16a34a',
                            borderRadius: 6,
                            maxBarThickness: 32
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        return formatAngka(ctx.parsed.y, satuanAktif('satuanBulanan'));
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: warnaTema.text
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: warnaTema.grid
                                },
                                ticks: {
                                    color: warnaTema.text,
                                    callback: function(value) {
                                        return Number(value).toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });

                if (!key) {
                    tampilPesan(canvasBulanan, kosongBulanan, 'Belum ada data setoran untuk ditampilkan.');
                }
            }

            function updateBulanan() {
                if (!chartBulanan) return;
                const key = satuanAktif('satuanBulanan');
                if (!key) return;

                const data = hitungBulanan(key);
                const adaData = data.some(function(v) {
                    return v > 0;
                });

                chartBulanan.data.datasets[0].data = data;
                chartBulanan.data.datasets[0].label = 'Jumlah (' + labelSatuan(key) + ')';
                chartBulanan.update();

                if (adaData) {
                    tampilCanvas(canvasBulanan, kosongBulanan);
                } else {
                    tampilPesan(canvasBulanan, kosongBulanan, 'Belum ada setoran ' + labelSatuan(key) +
                        ' pada tahun ini.');
                }
            }

            function warnaJenis(nama) {
                return nama.map(function(_, i) {
                    return WARNA[i % WARNA.length];
                });
            }

            if (canvasJenis) {
                const key = satuanAktif('satuanJenis');
                const awal = key ? hitungJenis(key) : {
                    nama: [],
                    nilai: []
                };
                const warnaTema = getThemeColors();

                chartJenis = new Chart(canvasJenis, {
                    type: 'doughnut',
                    data: {
                        labels: awal.nama,
                        datasets: [{
                            label: 'Jumlah',
                            data: awal.nilai,
                            backgroundColor: warnaJenis(awal.nama),
                            borderColor: warnaTema.border,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: warnaTema.text,
                                    boxWidth: 12,
                                    padding: 14
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        return ' ' + ctx.label + ': ' + formatAngka(ctx.parsed,
                                            satuanAktif('satuanJenis'));
                                    }
                                }
                            }
                        }
                    }
                });

                updateJenis();
            }

            function updateJenis() {
                if (!chartJenis) return;
                const key = satuanAktif('satuanJenis');
                const hasil = key ? hitungJenis(key) : {
                    nama: [],
                    nilai: []
                };
                const total = hasil.nilai.reduce(function(a, b) {
                    return a + b;
                }, 0);

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

            const selectBulanan = document.getElementById('satuanBulanan');
            const selectJenis = document.getElementById('satuanJenis');

            if (selectBulanan) selectBulanan.addEventListener('change', updateBulanan);
            if (selectJenis) selectJenis.addEventListener('change', updateJenis);

            function updateChartsTheme() {
                const colors = getThemeColors();
                if (chartBulanan) {
                    chartBulanan.options.scales.x.ticks.color = colors.text;
                    chartBulanan.options.scales.y.ticks.color = colors.text;
                    chartBulanan.options.scales.y.grid.color = colors.grid;
                    chartBulanan.update('none');
                }
                if (chartJenis) {
                    chartJenis.options.plugins.legend.labels.color = colors.text;
                    chartJenis.data.datasets[0].borderColor = colors.border;
                    chartJenis.update('none');
                }
                Chart.defaults.color = colors.text;
            }

            updateChartsTheme();

            const themeObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
                        updateChartsTheme();
                    }
                });
            });

            themeObserver.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-theme']
            });

        });
