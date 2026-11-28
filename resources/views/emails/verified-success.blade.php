<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Terkonfirmasi - {{ $appName }}</title>
</head>
<body style="margin: 0; padding: 32px 16px; background-color: #f4f6f5; font-family: Arial, sans-serif; text-align: center; color: #1f2937;">
    <div style="max-width: 440px; margin: 40px auto; background: #ffffff; padding: 32px 24px; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">

        <!-- Ikon Centang Sukses -->
        <div style="margin-bottom: 20px;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #16a34a;">
                <path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M22 4L12 14.01L9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <h2 style="margin: 0 0 10px 0; color: #14532d;">Email Berhasil Dikonfirmasi</h2>

        <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #4b5563;">
            Akun <strong>{{ $appName }}</strong> Anda sudah aktif. Silakan kembali ke aplikasi untuk melanjutkan.
        </p>

        <!-- Tombol untuk Buka Aplikasi (Opsional) -->
        <div style="margin-top: 24px;">
            <a href="banksampahgemi://login" style="display: inline-block; background-color: #16a34a; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; font-size: 14px;">
                Buka Aplikasi
            </a>
        </div>
    </div>
</body>
</html>
