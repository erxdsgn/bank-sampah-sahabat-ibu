@extends('admin.layouts.app')

@section('title', 'Riwayat Pencairan')
@section('active', 'riwayat-pencairan')
@section('crumbs', 'Transaksi Sampah | Riwayat Pencairan')

@section('content')
    <section class="hero" style="margin-bottom: 24px;">
        <div class="hero-text">
            <span class="eyebrow" style="color: #16a34a; font-weight: 600; font-size: 12px; letter-spacing: 1px;">DATA ARSIP</span>
            <h1 class="hero-title" style="font-size: 28px; font-weight: 800; margin-top: 4px;">
                Riwayat <span style="color: #16a34a;">Pencairan</span>
            </h1>
            <p class="hero-sub" style="color: #6b7280; margin-top: 8px;">
                Daftar lengkap seluruh transaksi penarikan saldo warga yang sudah selesai atau ditolak.
            </p>
        </div>
    </section>

    <section class="card" style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #f3f4f6;">
        
        <!-- SEARCH BAR -->
        <div class="table-toolbar" style="display: flex; justify-content: space-between; padding: 20px;">
            <div class="table-search" style="position: relative; width: 400px;">
                <svg viewBox="0 0 24 24" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; fill: none; stroke: #9ca3af; stroke-width: 2;">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchRiwayat" placeholder="Cari nama, NIK, atau status..." autocomplete="off" style="width: 100%; padding: 10px 10px 10px 40px; border-radius: 10px; border: 1px solid #e5e7eb; outline: none; font-size: 14px; transition: 0.2s;">
            </div>
            
            <button onclick="window.location.reload()" style="background: transparent; border: 1px solid #e5e7eb; padding: 8px 16px; border-radius: 8px; font-weight: 600; color: #4b5563; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                Refresh
            </button>
        </div>

        <!-- TABLE -->
        <div class="table-responsive" style="overflow-x: auto; width: 100%;">
            <table class="data-table" id="riwayatTable" style="width: 100%; border-collapse: collapse; text-align: left; min-width: 1100px;">
                <thead>
                    <tr style="background: #f9fafb; border-y: 1px solid #e5e7eb;">
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">Tgl Pencairan</th>
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">NIK</th>
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">Nama Warga</th>
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">Nominal</th>
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">Status Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data Dummy Sementara -->
                    <tr style="border-bottom: 1px solid #f3f4f6; transition: 0.2s; font-size: 13px;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 16px 20px; color: #4b5563;">19 Sep 2026</td>
                        <td style="padding: 16px 20px; font-family: monospace; color: #4b5563;">350912345678</td>
                        <td style="padding: 16px 20px; font-weight: 700; color: #1f2937;">ijut</td>
                        <td style="padding: 16px 20px; font-weight: 700; color: #16a34a;">Rp 100.000</td>
                        <td style="padding: 16px 20px;">
                            <span style="background: #dcfce7; color: #15803d; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">Selesai Diserahkan</span>
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f3f4f6; transition: 0.2s; font-size: 13px;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 16px 20px; color: #4b5563;">15 Sep 2026</td>
                        <td style="padding: 16px 20px; font-family: monospace; color: #4b5563;">350987654321</td>
                        <td style="padding: 16px 20px; font-weight: 700; color: #1f2937;">Budi</td>
                        <td style="padding: 16px 20px; font-weight: 700; color: #16a34a;">Rp 25.000</td>
                        <td style="padding: 16px 20px;">
                            <span style="background: #fee2e2; color: #b91c1c; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">Ditolak (Saldo Kurang)</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div style="padding: 20px; border-top: 1px solid #f3f4f6;">
            <a href="/admin/pencairan-saldo" style="color: #6b7280; text-decoration: none; font-weight: 600; font-size: 14px;">&larr; Kembali ke Halaman Utama</a>
        </div>
    </section>

    <script>
        document.getElementById('searchRiwayat').addEventListener('keyup', function() {
            let keyword = this.value.toLowerCase().trim();
            let rows = document.querySelectorAll('#riwayatTable tbody tr');
            
            rows.forEach(row => {
                let barisTeks = row.textContent.toLowerCase();
                row.style.display = barisTeks.includes(keyword) ? '' : 'none';
            });
        });
    </script>
@endsection