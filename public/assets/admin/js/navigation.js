/**
 * navigation.js
 * ---------------------------------------------------------
 * Data struktur menu sidebar Admin Bank Sampah.
 */

export const navigation = [
  {
    label: "Utama",
    items: [
      {
        key: "dashboard",
        text: "Dashboard",
        href: "/admin",
        icon: '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
      },
    ],
  },

  {
    label: "Master Data",
    items: [
      {
        key: "kategori-harga",
        text: "Kategori & Harga Sampah",
        href: "kategori-harga.html",
        icon: '<path d="M20 12v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"/><path d="M16 3h5v5"/><path d="m21 3-9 9"/><path d="M8 8h3M8 12h3M8 16h8"/>',
      },
      {
        key: "warga",
        text: "Data Warga",
        href: "/admin/pages/warga",
        icon: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
      },
    ],
  },

  {
    label: "Transaksi Sampah",
    items: [
      {
        key: "verifikasi-setoran",
        text: "Verifikasi Data Setoran",
        href: "verifikasi-setoran.html",
        icon: '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/><path d="M7 8h4"/>',
      },
      {
        key: "pencairan-saldo",
        text: "Pencairan Saldo",
        href: "pencairan-saldo.html",
        icon: '<circle cx="12" cy="12" r="9"/><path d="M15 8.5c-.6-.7-1.6-1.1-2.8-1.1-1.7 0-2.8.9-2.8 2.2 0 1.2 1 1.8 2.8 2.2 1.8.4 2.8 1 2.8 2.2 0 1.3-1.1 2.2-2.9 2.2-1.3 0-2.4-.5-3.1-1.3M12 5v14"/>',
      },
      {
        key: "penjualan-pengepul",
        text: "Penjualan ke Pengepul",
        href: "penjualan-pengepul.html",
        icon: '<path d="M3 7h13v11H3z"/><path d="M16 10h3l3 3v5h-6z"/><circle cx="7" cy="20" r="2"/><circle cx="18" cy="20" r="2"/>',
      },
    ],
  },

  {
    label: "Keuangan & Produk",
    items: [
      {
        key: "keuangan",
        text: "Keuangan",
        href: "/admin/pages/keuangan",
        icon: '<path d="M3 10h18v10H3z"/><path d="M5 10V7l7-4 7 4v3"/><path d="M7 14h.01M12 14h.01M17 14h.01"/>',
      },
      {
        key: "katalog",
        text: "Katalog Barang",
        href: "katalog.html",
        icon: '<path d="M6 2h9l5 5v15H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h6"/>',
      },
    ],
  },

  {
    label: "Konten & Laporan",
    items: [
      {
        key: "artikel",
        text: "Artikel & Edukasi",
        href: "artikel.html",
        icon: '<path d="M4 4h16v16H4z"/><path d="M8 8h8M8 12h8M8 16h5"/>',
      },
      {
        key: "laporan-riwayat",
        text: "Laporan & Riwayat",
        href: "laporan-riwayat.html",
        icon: '<path d="M4 19V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14"/><path d="M8 17v-4M12 17V8M16 17v-6"/><path d="M3 21h18"/>',
      },
    ],
  },

  {
    label: "Akun",
    items: [
      {
        key: "pengaturan-akun",
        text: "Pengaturan Akun Admin",
        href: "pengaturan-akun.html",
        icon: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-1.4 1.4-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-2v-.2a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L9 17l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H7v-2h.8a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L9 9l1.4-1.4.1.1a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.6v-.2h2v.2a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 9l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v2H21a1.7 1.7 0 0 0-1.6 1z"/>',
      },
    ],
  },
];
