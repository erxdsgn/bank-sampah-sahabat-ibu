@extends('admin.layouts.app')

@section('title', 'Pengaturan Sistem')
@section('active', 'pengaturan')
@section('crumbs', 'Pengaturan Sistem')

@section('content')
    <section class="hero" style="margin-bottom: 24px;">
        <div class="hero-text">
            <span class="eyebrow" style="color: #16a34a; font-weight: 600; font-size: 12px; letter-spacing: 1px;">KONFIGURASI</span>
            <h1 class="hero-title" style="font-size: 28px; font-weight: 800; margin-top: 4px;">
                Pengaturan <span style="color: #16a34a;">Sistem</span>
            </h1>
            <p class="hero-sub" style="color: #6b7280; margin-top: 8px;">
                Kelola informasi profil admin dan pengaturan dasar aplikasi Bank Sampah.
            </p>
        </div>
    </section>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">
        
        <!-- CARD: PROFIL ADMIN -->
        <section class="card" style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #f3f4f6;">
            <div style="padding: 20px 24px; border-bottom: 1px solid #edf0f2;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1f2937;">Profil Admin</h3>
                <p style="margin: 4px 0 0; font-size: 13px; color: #6b7280;">Perbarui nama, email, atau password akun Anda.</p>
            </div>
            
            <form style="padding: 24px;">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; color: #374151;">Nama Lengkap</label>
                    <input type="text" value="Admin Sahabat Ibu" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #dfe3e8; outline: none; font-size: 14px; transition: 0.2s;" onfocus="this.style.borderColor='#16a34a'" onblur="this.style.borderColor='#dfe3e8'">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; color: #374151;">Email</label>
                    <input type="email" value="admin@sahabatibu.com" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #dfe3e8; outline: none; font-size: 14px; transition: 0.2s;" onfocus="this.style.borderColor='#16a34a'" onblur="this.style.borderColor='#dfe3e8'">
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; color: #374151;">Password Baru (Opsional)</label>
                    <input type="password" placeholder="Kosongkan jika tidak ingin mengubah password" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #dfe3e8; outline: none; font-size: 14px; transition: 0.2s;" onfocus="this.style.borderColor='#16a34a'" onblur="this.style.borderColor='#dfe3e8'">
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="button" onclick="showToast()" style="padding: 10px 20px; border-radius: 8px; background: #16a34a; color: #fff; border: none; font-weight: 600; cursor: pointer; font-size: 14px; transition: 0.2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                        Simpan Profil
                    </button>
                </div>
            </form>
        </section>

        <!-- CARD: PENGATURAN APLIKASI -->
        <section class="card" style="background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid #f3f4f6;">
            <div style="padding: 20px 24px; border-bottom: 1px solid #edf0f2;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1f2937;">Pengaturan Aplikasi</h3>
                <p style="margin: 4px 0 0; font-size: 13px; color: #6b7280;">Sesuaikan identitas dari sistem Bank Sampah.</p>
            </div>
            
            <form style="padding: 24px;">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; color: #374151;">Nama Bank Sampah</label>
                    <input type="text" value="Bank Sampah Sahabat Ibu" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #dfe3e8; outline: none; font-size: 14px; background: #f9fafb;" readonly>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; color: #374151;">Alamat Kantor / Pusat</label>
                    <textarea rows="3" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #dfe3e8; outline: none; font-size: 14px; transition: 0.2s; resize: vertical;" onfocus="this.style.borderColor='#16a34a'" onblur="this.style.borderColor='#dfe3e8'">Jl. Contoh Alamat No. 123, Desa Sejahtera</textarea>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; color: #374151;">Nomor HP / WhatsApp Admin</label>
                    <input type="text" value="081234567890" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #dfe3e8; outline: none; font-size: 14px; transition: 0.2s;" onfocus="this.style.borderColor='#16a34a'" onblur="this.style.borderColor='#dfe3e8'">
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="button" onclick="showToast()" style="padding: 10px 20px; border-radius: 8px; background: #1f2937; color: #fff; border: none; font-weight: 600; cursor: pointer; font-size: 14px; transition: 0.2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </section>

    </div>

    <!-- TOAST NOTIFIKASI -->
    <div id="toastWrap" style="position: fixed; top: 20px; right: 20px; z-index: 1100;"></div>

    <script>
        // Animasi Notifikasi
        function showToast() {
            const wrap = document.getElementById('toastWrap');
            const toast = document.createElement('div');
            toast.style.cssText = 'background: #fff; border-left: 4px solid #16a34a; box-shadow: 0 12px 30px rgba(15,23,42,0.15); padding: 14px 16px; border-radius: 10px; margin-bottom: 10px; display: flex; align-items: flex-start; gap: 12px; min-width: 300px;';
            toast.innerHTML = `
                <div style="width: 22px; height: 22px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg viewBox="0 0 24 24" style="width: 13px; height: 13px; fill: none; stroke: currentColor; stroke-width: 2.4;"><path d="M20 6 9 17l-5-5"></path></svg>
                </div>
                <div style="flex: 1;">
                    <p style="margin: 0 0 2px; font-size: 13px; font-weight: 700; color: #1f2937;">Berhasil disimpan</p>
                    <p style="margin: 0; font-size: 12.5px; color: #6b7280; line-height: 1.5;">Perubahan pengaturan berhasil diperbarui.</p>
                </div>
                <button onclick="this.parentElement.remove()" style="background: transparent; border: none; font-size: 16px; color: #9ca3af; cursor: pointer; padding: 0;">&times;</button>
            `;
            wrap.appendChild(toast);
            setTimeout(() => toast.remove(), 3500);
        }
    </script>
@endsection