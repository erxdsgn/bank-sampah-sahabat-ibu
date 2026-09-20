{{-- resources/views/admin/pages/laporan-form.blade.php (dipakai untuk tambah & ubah) --}}
@extends('admin.layouts.app')

@section('title', 'Laporan & Riwayat')
@section('crumbs', $transaksi->exists ? 'Laporan & Riwayat | Ubah transaksi' : 'Laporan & Riwayat | Tambah transaksi')
@section('active', 'laporan-riwayat')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/admin/css/laporan.css') }}">

@php
    $isEdit    = $transaksi->exists;
    $kembali   = $isEdit ? route('admin.laporan.show', $transaksi) : route('admin.laporan.index');

    $initialItems = old('items', $isEdit
        ? $transaksi->items->map(fn ($i) => [
            'nama_item'    => $i->nama_item,
            'berat'        => $i->berat,
            'harga_per_kg' => $i->harga_per_kg,
        ])->values()->all()
        : []);

    $nominal = old('nominal', $isEdit && $transaksi->jenis === 'pencairan' ? $transaksi->total : '');
@endphp

<div class="lp lp--narrow">

    <div class="lp-head">
        <div>
            <a href="{{ $kembali }}" class="lp-back">&larr; Kembali</a>
            <h1 class="lp-title">{{ $isEdit ? 'Ubah transaksi ' . $transaksi->kode : 'Tambah transaksi' }}</h1>
        </div>
    </div>

    @if ($errors->any())
        <div class="lp-alert lp-alert--error" role="alert">
            <p>Data belum bisa disimpan. Periksa isian berikut:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $isEdit ? route('admin.laporan.update', $transaksi) : route('admin.laporan.store') }}"
          class="lp">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        {{-- Informasi umum --}}
        <div class="lp-card lp-card__pad">
            <div class="lp-grid">
                <div>
                    <label for="jenis" class="lp-label">Jenis transaksi</label>
                    <select id="jenis" name="jenis" class="lp-input">
                        @foreach (\App\Models\Transaksi::JENIS as $value => $label)
                            <option value="{{ $value }}" @selected(old('jenis', $transaksi->jenis) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tanggal" class="lp-label">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal"
                           value="{{ old('tanggal', optional($transaksi->tanggal)->format('Y-m-d')) }}"
                           class="lp-input">
                </div>

                <div>
                    <label for="warga_id" class="lp-label">Warga <span id="warga-hint" class="lp-hint"></span></label>
                    <select id="warga_id" name="warga_id" class="lp-input">
                        <option value="">Pilih warga</option>
                        @foreach ($wargas as $warga)
                            <option value="{{ $warga->getKey() }}" @selected((string) old('warga_id', $transaksi->warga_id) === (string) $warga->getKey())>
                                {{ \App\Models\Transaksi::namaDariWarga($warga) }}
                            </option>
                        @endforeach
                    </select>
                    @if ($wargas->isEmpty())
                        <p class="lp-hint" style="margin:6px 0 0">
                            Belum ada data warga.
                            <a href="{{ url('/admin/pages/warga') }}" class="lp-link">Tambah warga dulu</a>.
                        </p>
                    @endif
                </div>

                <div>
                    <label for="status" class="lp-label">Status</label>
                    <select id="status" name="status" class="lp-input">
                        @foreach (\App\Models\Transaksi::STATUS as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $transaksi->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lp-grid__full">
                    <label for="keterangan" class="lp-label">Catatan</label>
                    <textarea id="keterangan" name="keterangan" rows="2" maxlength="500"
                              placeholder="Contoh: nama pengepul, nomor nota, atau keterangan lain"
                              class="lp-input">{{ old('keterangan', $transaksi->keterangan) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Rincian sampah (setoran & penjualan) --}}
        <div id="items-section" class="lp-card lp-card--clip">
            <div class="lp-card__head">
                <h2 class="lp-card__title">Rincian sampah</h2>
                <button type="button" id="add-row" class="lp-btn">+ Tambah item</button>
            </div>
            <div class="lp-card__pad">
                <div class="lp-table-wrap">
                    <table class="lp-items">
                        <thead>
                            <tr>
                                <th class="lp-items__nama">Jenis sampah</th>
                                <th class="lp-items__berat">Berat (kg)</th>
                                <th class="lp-items__harga">Harga per kg</th>
                                <th class="lp-items__sub">Subtotal</th>
                                <th class="lp-items__act"></th>
                            </tr>
                        </thead>
                        <tbody id="item-rows"></tbody>
                    </table>
                </div>
                <div class="lp-total">
                    <span>Total</span>
                    <strong id="grand-total">Rp 0</strong>
                </div>
            </div>
        </div>

        {{-- Nominal (pencairan) --}}
        <div id="nominal-section" class="lp-card lp-card__pad lp-hidden">
            <label for="nominal" class="lp-label">Nominal pencairan (Rp)</label>
            <input type="number" id="nominal" name="nominal" min="1" step="1" value="{{ $nominal }}"
                   class="lp-input lp-input--short" placeholder="0">
        </div>

        <div class="lp-form-actions">
            <a href="{{ $kembali }}" class="lp-btn">Batal</a>
            <button type="submit" class="lp-btn lp-btn--primary">{{ $isEdit ? 'Simpan perubahan' : 'Simpan transaksi' }}</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // old('items') bisa berkunci tidak berurutan (0, 2, 5), jadi diratakan ke array.
    const initialItems = Object.values(@json($initialItems));

    const jenisEl      = document.getElementById('jenis');
    const itemsSection = document.getElementById('items-section');
    const nominalBox   = document.getElementById('nominal-section');
    const nominalInput = document.getElementById('nominal');
    const wargaHint    = document.getElementById('warga-hint');
    const tbody        = document.getElementById('item-rows');
    const totalEl      = document.getElementById('grand-total');
    const rupiah       = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
    let rowIndex = 0;

    function recalc() {
        let total = 0;
        tbody.querySelectorAll('tr').forEach((tr) => {
            const berat = parseFloat(tr.querySelector('[data-f="berat"]').value) || 0;
            const harga = parseFloat(tr.querySelector('[data-f="harga"]').value) || 0;
            const sub   = berat * harga;
            tr.querySelector('[data-f="subtotal"]').textContent = rupiah(sub);
            total += sub;
        });
        totalEl.textContent = rupiah(total);
    }

    function addRow(item = {}) {
        const i  = rowIndex++;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="lp-items__nama"><input type="text" name="items[${i}][nama_item]" data-f="nama" maxlength="100" placeholder="Contoh: Botol plastik" class="lp-input"></td>
            <td class="lp-items__berat"><input type="number" name="items[${i}][berat]" data-f="berat" min="0.01" step="0.01" placeholder="0" class="lp-input"></td>
            <td class="lp-items__harga"><input type="number" name="items[${i}][harga_per_kg]" data-f="harga" min="0" step="1" placeholder="0" class="lp-input"></td>
            <td class="lp-items__sub" data-f="subtotal">Rp 0</td>
            <td class="lp-items__act"><button type="button" data-f="remove" class="lp-remove" aria-label="Hapus baris">&times;</button></td>`;

        // Nilai diisi lewat properti (bukan template string) agar aman dari karakter khusus.
        tr.querySelector('[data-f="nama"]').value  = item.nama_item ?? '';
        tr.querySelector('[data-f="berat"]').value = item.berat ?? '';
        tr.querySelector('[data-f="harga"]').value = item.harga_per_kg ?? '';

        tr.addEventListener('input', recalc);
        tr.querySelector('[data-f="remove"]').addEventListener('click', () => {
            if (tbody.children.length > 1) { tr.remove(); recalc(); }
        });

        tbody.appendChild(tr);
        recalc();
    }

    function toggleJenis() {
        const isPencairan = jenisEl.value === 'pencairan';
        itemsSection.classList.toggle('lp-hidden', isPencairan);
        nominalBox.classList.toggle('lp-hidden', !isPencairan);
        // Input yang disabled tidak ikut terkirim ke server.
        itemsSection.querySelectorAll('input').forEach((el) => el.disabled = isPencairan);
        nominalInput.disabled = !isPencairan;
        wargaHint.textContent = jenisEl.value === 'penjualan' ? '(opsional untuk penjualan)' : '';
    }

    document.getElementById('add-row').addEventListener('click', () => addRow());
    jenisEl.addEventListener('change', toggleJenis);

    (initialItems.length ? initialItems : [{}]).forEach(addRow);
    toggleJenis();
});
</script>
@endsection