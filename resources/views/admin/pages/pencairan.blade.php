@extends('admin.layouts.app')

@section('title', 'Pencairan Saldo')
@section('active', 'pencairan')
@section('crumbs', 'Transaksi Sampah | Pencairan Saldo')

@section('content')

    <section class="hero" style="margin-bottom: 24px;">
        <div class="hero-text">
            <span class="eyebrow" style="color: #16a34a; font-weight: 600; font-size: 12px; letter-spacing: 1px;">TRANSAKSI SAMPAH</span>
            <h1 class="hero-title" style="font-size: 28px; font-weight: 800; margin-top: 4px;">
                Pencairan <span style="color: #16a34a;">Saldo</span>
            </h1>
            <p class="hero-sub" style="color: #6b7280; margin-top: 8px;">
                Kelola data permohonan penarikan dana dari tabungan warga.
            </p>
        </div>
    </section>

    <section class="card" style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #f3f4f6;">

        <!-- SEARCH BAR & TOMBOL AKSI -->
        <div class="table-toolbar" style="display: flex; justify-content: space-between; padding: 20px;">
            <div class="table-search" style="position: relative; width: 400px;">
                <svg viewBox="0 0 24 24" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; fill: none; stroke: #9ca3af; stroke-width: 2;">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchPencairan" placeholder="Cari NIK, nama, nomor HP, atau alamat..." autocomplete="off" style="width: 100%; padding: 10px 10px 10px 40px; border-radius: 10px; border: 1px solid #e5e7eb; outline: none; font-size: 14px; transition: 0.2s;">
            </div>
            
            <div style="display: flex; gap: 10px; align-items: center;">
                <button onclick="window.location.reload()" style="background: transparent; border: 1px solid #e5e7eb; padding: 8px 16px; border-radius: 8px; font-weight: 600; color: #4b5563; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                    Refresh
                </button>
                
                <!-- TOMBOL BARU: RIWAYAT -->
                <a href="/admin/riwayat-pencairan" style="background: #f3f4f6; border: 1px solid #d1d5db; padding: 8px 16px; border-radius: 8px; font-weight: 600; color: #374151; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; transition: 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                    Lihat Riwayat
                </a>

                <!-- TOMBOL PILIH WARGA -->
                <a href="/admin/pilih-warga" style="background: #16a34a; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; color: #fff; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.2); transition: 0.2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                    <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2;"><path d="M12 5v14m-7-7h14"></path></svg>
                    Pilih Warga
                </a>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-responsive" style="overflow-x: auto; width: 100%;">
            <table class="data-table" id="pencairanTable" style="width: 100%; border-collapse: collapse; text-align: left; min-width: 1100px;">
                <thead>
                    <tr style="background: #f9fafb; border-y: 1px solid #e5e7eb;">
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">No</th>
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">NIK</th>
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">Nama</th>
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">No. HP</th>
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">Alamat</th>
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">Nominal</th>
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">Status</th>
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pencairan as $index =>$item)
                        <tr style="border-bottom: 1px solid #f3f4f6; transition: 0.2s; font-size: 13px;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 16px 20px; color: #4b5563;">{{ $index + 1 }}</td>
                            <td style="padding: 16px 20px; font-family: monospace; color: #4b5563;">{{ $item->warga->nik ?? '-' }}</td>
                            <td style="padding: 16px 20px; font-weight: 700; color: #1f2937;">{{ $item->warga->nama ?? 'Anonim' }}</td>
                            <td style="padding: 16px 20px; color: #4b5563;">{{ $item->warga->no_hp ?? '-' }}</td>
                            <td style="padding: 16px 20px; color: #4b5563;">{{ $item->warga->alamat ?? '-' }}</td>
                            <td style="padding: 16px 20px; font-weight: 700; color: #16a34a; white-space: nowrap;">Rp {{ number_format($item->nominal ?? 0, 0, ',', '.') }}</td>
                            <td style="padding: 16px 20px;">
                                @if($item->status == 'menunggu')
                                    <span style="background: #fef3c7; color: #d97706; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">Menunggu</span>
                                @elseif($item->status == 'selesai')
                                    <span style="background: #dcfce7; color: #15803d; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">Selesai</span>
                                @else
                                    <span style="background: #fee2e2; color: #b91c1c; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">Ditolak</span>
                                @endif
                            </td>
                            <td style="padding: 16px 20px; text-align: center;">
                                <button onclick='prosesPencairan(@json($item))' style="background: #eff6ff; color: #2563eb; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                                    <svg viewBox="0 0 24 24" style="width: 14px; height: 14px; fill: none; stroke: currentColor; stroke-width: 2;"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"></path></svg>
                                    Proses
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 60px 20px; color: #6b7280;">Belum Ada Request Pencairan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- MODAL: PROSES PENCAIRAN -->
    <div id="modalProses" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: #fff; width: 100%; max-width: 450px; border-radius: 14px; overflow: hidden; box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);">
            
            <div style="padding: 20px 24px; border-bottom: 1px solid #edf0f2; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1f2937;">Proses Pencairan Saldo</h3>
                <button onclick="tutupModal()" style="background: transparent; border: none; font-size: 24px; cursor: pointer; color: #6b7280; line-height: 1;">&times;</button>
            </div>

            <form onsubmit="return simpanProses(event)" style="padding: 24px;">
                <input type="hidden" id="editId">
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; color: #374151;">Nama Warga</label>
                    <input type="text" id="editNama" disabled style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #dfe3e8; background: #f3f4f6; color: #6b7280; font-size: 13px;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; color: #374151;">Nominal Penarikan</label>
                    <input type="text" id="editNominal" disabled style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #dfe3e8; background: #f3f4f6; font-weight: 700; color: #16a34a; font-size: 13px;">
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; color: #374151;">Status Pencairan</label>
                    <select id="editStatus" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #dfe3e8; outline: none; font-size: 13px;">
                        <option value="menunggu">Menunggu</option>
                        <option value="selesai">Selesai (Uang Diserahkan)</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="tutupModal()" style="padding: 8px 16px; border-radius: 8px; background: transparent; color: #374151; border: 1px solid #dfe3e8; font-weight: 600; cursor: pointer; font-size: 13px;">Batal</button>
                    <button type="submit" style="padding: 8px 16px; border-radius: 8px; background: #16a34a; color: #fff; border: none; font-weight: 600; cursor: pointer; font-size: 13px;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TOAST NOTIFIKASI -->
    <div id="toastWrap" style="position: fixed; top: 20px; right: 20px; z-index: 1100;"></div>

    <script>
        // Fitur Pencarian Dinamis (Cari NIK, Nama, HP, Alamat sekaligus)
        document.getElementById('searchPencairan').addEventListener('keyup', function() {
            let keyword = this.value.toLowerCase().trim();
            let rows = document.querySelectorAll('#pencairanTable tbody tr');
            
            rows.forEach(row => {
                // Tampilkan baris jika teks di baris tersebut mengandung keyword
                let barisTeks = row.textContent.toLowerCase();
                row.style.display = barisTeks.includes(keyword) ? '' : 'none';
            });
        });

        // Buka Pop up Modal
        function prosesPencairan(item) {
            document.getElementById('editId').value = item.id_pencairan;
            document.getElementById('editNama').value = item.warga.nama + ' (NIK: ' + item.warga.nik + ')';
            document.getElementById('editNominal').value = 'Rp ' + Number(item.nominal).toLocaleString('id-ID');
            document.getElementById('editStatus').value = item.status;
            
            let modal = document.getElementById('modalProses');
            modal.style.display = 'flex';
        }

        function tutupModal() {
            document.getElementById('modalProses').style.display = 'none';
        }

        // Animasi Notifikasi (Toast) mirip template asli
        function simpanProses(event) {
            event.preventDefault();
            let statusBaru = document.getElementById('editStatus').value;
            tutupModal();
            showToast('Berhasil disimpan', 'Status pencairan berhasil diperbarui menjadi: ' + statusBaru);
            return false;
        }

        function showToast(title, message) {
            const wrap = document.getElementById('toastWrap');
            const toast = document.createElement('div');
            toast.style.cssText = 'background: #fff; border-left: 4px solid #16a34a; box-shadow: 0 12px 30px rgba(15,23,42,0.15); padding: 14px 16px; border-radius: 10px; margin-bottom: 10px; display: flex; align-items: flex-start; gap: 12px; min-width: 300px;';
            toast.innerHTML = `
                <div style="width: 22px; height: 22px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg viewBox="0 0 24 24" style="width: 13px; height: 13px; fill: none; stroke: currentColor; stroke-width: 2.4;"><path d="M20 6 9 17l-5-5"></path></svg>
                </div>
                <div style="flex: 1;">
                    <p style="margin: 0 0 2px; font-size: 13px; font-weight: 700; color: #1f2937;">${title}</p>
                    <p style="margin: 0; font-size: 12.5px; color: #6b7280; line-height: 1.5;">${message}</p>
                </div>
                <button onclick="this.parentElement.remove()" style="background: transparent; border: none; font-size: 16px; color: #9ca3af; cursor: pointer; padding: 0;">&times;</button>
            `;
            wrap.appendChild(toast);
            setTimeout(() => toast.remove(), 3500);
        }
    </script>
@endsection