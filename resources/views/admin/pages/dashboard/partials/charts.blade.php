{{-- GRAFIK --}}
    <div class="dashboard-columns">

        {{-- GRAFIK SETORAN BULANAN --}}
        <div class="card chart-card chart-card--large">

            <div class="card-head card-head--row">
                <div>
                    <h2 class="card-title">Setoran Sampah per Bulan</h2>
                    <p class="card-sub">Total jumlah sampah tahun {{ now()->year }}</p>
                </div>

                @if ($opsi->count() > 1)
                    <div class="combo combo--arrow" id="combo_satuanBulanan">
                        <input type="text" id="cari_satuanBulanan" class="combo-input" readonly autocomplete="off"
                            aria-label="Satuan grafik bulanan">
                        <div class="combo-list" id="list_satuanBulanan"></div>
                    </div>
                    <select id="satuanBulanan" class="combo-hidden" tabindex="-1" aria-hidden="true">
                        @foreach ($opsi as $o)
                            <option value="{{ $o['key'] }}">{{ $o['label'] }}</option>
                        @endforeach
                    </select>
                @elseif ($opsi->count() === 1)
                    <span class="unit-badge">{{ $opsi->first()['label'] }}</span>
                @endif
            </div>

            <div class="chart-container">
                <canvas id="setoranBulananChart"></canvas>
                <div class="chart-empty" id="bulananKosong" style="display:none;"></div>
            </div>

        </div>


        {{-- STATISTIK JENIS SAMPAH --}}
        <div class="card chart-card chart-card--small">

            <div class="card-head card-head--row">
                <div>
                    <h2 class="card-title">Statistik Jenis Sampah</h2>
                    <p class="card-sub">Berdasarkan total jumlah</p>
                </div>

                @if ($opsi->count() > 1)
                    <div class="combo combo--arrow" id="combo_satuanJenis">
                        <input type="text" id="cari_satuanJenis" class="combo-input" readonly autocomplete="off"
                            aria-label="Satuan statistik jenis sampah">
                        <div class="combo-list" id="list_satuanJenis"></div>
                    </div>
                    <select id="satuanJenis" class="combo-hidden" tabindex="-1" aria-hidden="true">
                        @foreach ($opsi as $o)
                            <option value="{{ $o['key'] }}">{{ $o['label'] }}</option>
                        @endforeach
                    </select>
                @elseif ($opsi->count() === 1)
                    <span class="unit-badge">{{ $opsi->first()['label'] }}</span>
                @endif
            </div>

            <div class="chart-container">
                <canvas id="jenisSampahChart"></canvas>
                <div class="chart-empty" id="jenisKosong" style="display:none;"></div>
            </div>

        </div>

    </div>
