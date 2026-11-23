import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/pages/dashboard.css',
                'resources/js/pages/dashboard.js',
                'resources/css/pages/artikel.css',
                'resources/js/pages/artikel.js',
                'resources/css/pages/kategori-harga.css',
                'resources/js/pages/kategori-harga.js',
                'resources/css/pages/keuangan.css',
                'resources/js/pages/keuangan.js',
                'resources/css/pages/pencairan.css',
                'resources/js/pages/pencairan.js',
                'resources/css/pages/penjadwalan-setoran.css',
                'resources/js/pages/penjadwalan-setoran.js',
                'resources/css/pages/verifikasi-setoran.css',
                'resources/js/pages/verifikasi-setoran.js',
                'resources/css/pages/warga.css',
                'resources/js/pages/warga.js',
                'resources/css/pages/penjualan-pengepul.css',
                'resources/js/pages/penjualan-pengepul.js',
                'resources/css/pages/profil-sesi.css',
                'resources/js/pages/profil-sesi.js',
                'resources/css/pages/laporan.css',
                'resources/js/pages/laporan.js',
                'resources/css/pages/login.css',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
