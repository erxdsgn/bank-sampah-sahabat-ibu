@extends('admin.layouts.app')

@section('title', 'Pilih Warga')
@section('active', 'pencairan')
@section('crumbs', 'Transaksi Sampah | Pencairan Saldo | Pilih Warga')

@section('content')
    <section class="hero" style="margin-bottom: 24px;">
        <div class="hero-text">
            <h1 class="hero-title" style="font-size: 24px; font-weight: 800; margin-top: 4px;">
                Pilih <span style="color: #16a34a;">Warga</span>
            </h1>
            <p class="hero-sub" style="color: #6b7280; margin-top: 8px;">
                Silakan pilih warga yang ingin dicairkan saldonya.
            </p>
        </div>
    </section>

    <section class="card" style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #f3f4f6; padding: 20px;">
        <div class="table-responsive" style="overflow-x: auto; width: 100%;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f9fafb; border-y: 1px solid #e5e7eb;">
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">No</th>
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">Nama Warga</th>
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase;">Total Saldo Saat Ini</th>
                        <th style="padding: 16px 20px; font-size: 12px; color: #6b7280; text-transform: uppercase; text-align: center;">Aksi</th>
                    </tr>
                </thead>
<tbody>
                    <!-- Ini contoh data sementara (dummy) -->
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 16px 20px; color: #4b5563;">1</td>
                        <td style="padding: 16px 20px; font-weight: 700; color: #1f2937;">Ani (NIK: 3509...)</td>
                        <td style="padding: 16px 20px; font-weight: 700; color: #16a34a;">Rp 150.000</td>
                        <td style="padding: 16px 20px; text-align: center;">
                            <a href="#" style="background: #16a34a; color: #fff; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px;">
                                Pilih & Cairkan
                            </a>
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 16px 20px; color: #4b5563;">2</td>
                        <td style="padding: 16px 20px; font-weight: 700; color: #1f2937;">Budi (NIK: 3509...)</td>
                        <td style="padding: 16px 20px; font-weight: 700; color: #16a34a;">Rp 75.000</td>
                        <td style="padding: 16px 20px; text-align: center;">
                            <a href="#" style="background: #16a34a; color: #fff; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px;">
                                Pilih & Cairkan
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="/admin/pencairan-saldo" style="color: #6b7280; text-decoration: none; font-weight: 600; font-size: 14px;">&larr; Kembali ke Riwayat</a>
        </div>
    </section>
@endsection