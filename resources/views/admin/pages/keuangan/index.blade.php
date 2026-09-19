@extends('admin.layouts.app')

@section('title', 'Keuangan')
@section('active', 'keuangan')
@section('crumbs', 'Keuangan & Produk | Keuangan')

@section('content')

<style>
    .keuangan-page {
        width: 100%;
    }

    /* =========================
       HEADER
    ========================= */

    .keuangan-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 24px;
    }

    .keuangan-header-left {
        min-width: 0;
    }

    .keuangan-eyebrow {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--primary, #2ecc71);
        margin-bottom: 6px;
    }

    .keuangan-title {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        line-height: 1.2;
        color: var(--text, #111827);
    }

    .keuangan-description {
        margin: 7px 0 0;
        font-size: 14px;
        color: var(--muted, #6b7280);
    }

    .keuangan-add-btn {
        border: 0;
        background: var(--primary, #2ecc71);
        color: #fff;
        min-height: 42px;
        padding: 0 17px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: .2s ease;
        white-space: nowrap;
    }

    .keuangan-add-btn:hover {
        transform: translateY(-1px);
        filter: brightness(.95);
    }

    .keuangan-add-btn svg {
        width: 18px;
        height: 18px;
    }

    /* =========================
       SUMMARY
    ========================= */

    .keuangan-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .keuangan-summary-card {
        border: 1px solid var(--border, #e5e7eb);
        background: var(--card, #fff);
        border-radius: 14px;
        padding: 18px;
        min-width: 0;
    }

    .keuangan-summary-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .keuangan-summary-label {
        font-size: 13px;
        color: var(--muted, #6b7280);
        margin-bottom: 8px;
    }

    .keuangan-summary-value {
        font-size: 21px;
        font-weight: 800;
        line-height: 1.25;
        color: var(--text, #111827);
    }

    .keuangan-summary-icon {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: color-mix(
            in srgb,
            var(--primary, #2ecc71) 12%,
            transparent
        );
        color: var(--primary, #2ecc71);
    }

    .keuangan-summary-icon svg {
        width: 20px;
        height: 20px;
    }

    .keuangan-summary-info {
        margin-top: 12px;
        font-size: 12px;
        color: var(--muted, #6b7280);
    }

    /* =========================
       TRANSACTION CARD
    ========================= */

    .keuangan-card {
        border: 1px solid var(--border, #e5e7eb);
        background: var(--card, #fff);
        border-radius: 14px;
        overflow: hidden;
    }

    .keuangan-card-header {
        padding: 18px 20px;
        border-bottom: 1px solid var(--border, #e5e7eb);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
    }

    .keuangan-card-title {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: var(--text, #111827);
    }

    .keuangan-card-subtitle {
        margin: 4px 0 0;
        font-size: 12px;
        color: var(--muted, #6b7280);
    }

    /* =========================
       FILTER
    ========================= */

    .keuangan-filter {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border, #e5e7eb);
        display: grid;
        grid-template-columns: minmax(220px, 1fr) 180px 180px;
        gap: 12px;
    }

    .keuangan-search {
        position: relative;
    }

    .keuangan-search svg {
        position: absolute;
        width: 17px;
        height: 17px;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--muted, #6b7280);
        pointer-events: none;
    }

    .keuangan-input,
    .keuangan-select {
        width: 100%;
        height: 42px;
        border: 1px solid var(--border, #d1d5db);
        border-radius: 9px;
        background: var(--input-bg, #fff);
        color: var(--text, #111827);
        outline: none;
        font-size: 13px;
        padding: 0 13px;
    }

    .keuangan-search .keuangan-input {
        padding-left: 39px;
    }

    .keuangan-input:focus,
    .keuangan-select:focus {
        border-color: var(--primary, #2ecc71);
        box-shadow: 0 0 0 3px color-mix(
            in srgb,
            var(--primary, #2ecc71) 12%,
            transparent
        );
    }

    /* =========================
       TABLE
    ========================= */

    .keuangan-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .keuangan-table {
        width: 100%;
        min-width: 850px;
        border-collapse: collapse;
    }

    .keuangan-table th {
        padding: 13px 20px;
        text-align: left;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--muted, #6b7280);
        background: var(--table-head, #f9fafb);
        border-bottom: 1px solid var(--border, #e5e7eb);
        white-space: nowrap;
    }

    .keuangan-table td {
        padding: 15px 20px;
        font-size: 13px;
        color: var(--text, #111827);
        border-bottom: 1px solid var(--border, #e5e7eb);
        vertical-align: middle;
    }

    .keuangan-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .keuangan-table tbody tr:hover {
        background: var(--hover, #f9fafb);
    }

    .transaction-date {
        white-space: nowrap;
    }

    .transaction-category {
        font-weight: 700;
    }

    .transaction-description {
        color: var(--muted, #6b7280);
        max-width: 250px;
    }

    .transaction-amount {
        font-weight: 800;
        white-space: nowrap;
    }

    .amount-income {
        color: #16a34a;
    }

    .amount-expense {
        color: #dc2626;
    }

    /* =========================
       BADGE
    ========================= */

    .keuangan-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 27px;
        padding: 0 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-income {
        color: #15803d;
        background: #dcfce7;
    }

    .badge-expense {
        color: #b91c1c;
        background: #fee2e2;
    }

    /* =========================
       ACTION
    ========================= */

    .keuangan-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .keuangan-action {
        width: 32px;
        height: 32px;
        border: 1px solid var(--border, #e5e7eb);
        background: var(--card, #fff);
        color: var(--muted, #6b7280);
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: .2s ease;
    }

    .keuangan-action:hover {
        color: var(--text, #111827);
        background: var(--hover, #f3f4f6);
    }

    .keuangan-action.delete:hover {
        color: #dc2626;
        background: #fee2e2;
        border-color: #fecaca;
    }

    .keuangan-action svg {
        width: 15px;
        height: 15px;
    }

    /* =========================
       EMPTY
    ========================= */

    .keuangan-empty {
        display: none;
        padding: 50px 20px;
        text-align: center;
        color: var(--muted, #6b7280);
    }

    .keuangan-empty svg {
        width: 40px;
        height: 40px;
        margin-bottom: 10px;
    }

    .keuangan-empty strong {
        display: block;
        color: var(--text, #111827);
        margin-bottom: 5px;
    }

    /* =========================
       MODAL
    ========================= */

    .keuangan-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(0, 0, 0, .48);
    }

    .keuangan-modal.show {
        display: flex;
    }

    .keuangan-modal-box {
        width: 100%;
        max-width: 560px;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
        background: var(--card, #fff);
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .18);
    }

    .keuangan-modal-header {
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        border-bottom: 1px solid var(--border, #e5e7eb);
    }

    .keuangan-modal-title {
        margin: 0;
        font-size: 17px;
        font-weight: 800;
        color: var(--text, #111827);
    }

    .keuangan-modal-close {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: var(--muted, #6b7280);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .keuangan-modal-close:hover {
        background: var(--hover, #f3f4f6);
        color: var(--text, #111827);
    }

    .keuangan-modal-close svg {
        width: 18px;
        height: 18px;
    }

    .keuangan-modal-body {
        padding: 20px;
    }

    .keuangan-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .keuangan-form-group {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .keuangan-form-group.full {
        grid-column: 1 / -1;
    }

    .keuangan-form-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--text, #111827);
    }

    .keuangan-form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 20px;
        padding-top: 17px;
        border-top: 1px solid var(--border, #e5e7eb);
    }

    .keuangan-btn-secondary,
    .keuangan-btn-primary {
        height: 40px;
        padding: 0 15px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }

    .keuangan-btn-secondary {
        border: 1px solid var(--border, #d1d5db);
        background: var(--card, #fff);
        color: var(--text, #111827);
    }

    .keuangan-btn-primary {
        border: 0;
        background: var(--primary, #2ecc71);
        color: #fff;
    }

    /* =========================
       DARK MODE
    ========================= */

    [data-theme="dark"] .keuangan-summary-card,
    [data-theme="dark"] .keuangan-card,
    [data-theme="dark"] .keuangan-modal-box {
        background: var(--card, #16181d);
    }

    [data-theme="dark"] .keuangan-input,
    [data-theme="dark"] .keuangan-select,
    [data-theme="dark"] .keuangan-action,
    [data-theme="dark"] .keuangan-btn-secondary {
        background: var(--card, #16181d);
        color: var(--text, #f3f4f6);
    }

    [data-theme="dark"] .keuangan-table th {
        background: rgba(255, 255, 255, .03);
    }

    [data-theme="dark"] .keuangan-table tbody tr:hover {
        background: rgba(255, 255, 255, .03);
    }

    /* =========================
       RESPONSIVE
    ========================= */

    @media (max-width: 1100px) {
        .keuangan-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .keuangan-filter {
            grid-template-columns: 1fr 1fr;
        }

        .keuangan-search {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 700px) {
        .keuangan-header {
            flex-direction: column;
            align-items: stretch;
        }

        .keuangan-title {
            font-size: 23px;
        }

        .keuangan-add-btn {
            width: 100%;
        }

        .keuangan-summary {
            grid-template-columns: 1fr;
        }

        .keuangan-filter {
            grid-template-columns: 1fr;
        }

        .keuangan-search {
            grid-column: auto;
        }

        .keuangan-card-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .keuangan-form-grid {
            grid-template-columns: 1fr;
        }

        .keuangan-form-group.full {
            grid-column: auto;
        }

        .keuangan-modal {
            padding: 12px;
        }

        .keuangan-modal-box {
            max-height: calc(100vh - 24px);
        }
    }
</style>

<div class="keuangan-page">

    {{-- HEADER --}}
    <div class="keuangan-header">
        <div class="keuangan-header-left">
            <div class="keuangan-eyebrow">
                Keuangan & Produk
            </div>

            <h1 class="keuangan-title">
                Keuangan
            </h1>

            <p class="keuangan-description">
                Kelola pemasukan, pengeluaran, dan transaksi keuangan bank sampah.
            </p>
        </div>

        <button
            type="button"
            class="keuangan-add-btn"
            id="openKeuanganModal"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14"/>
                <path d="M5 12h14"/>
            </svg>

            Tambah Transaksi
        </button>
    </div>

    {{-- SUMMARY --}}
    <div class="keuangan-summary">

        <div class="keuangan-summary-card">
            <div class="keuangan-summary-top">
                <div>
                    <div class="keuangan-summary-label">
                        Total Pemasukan
                    </div>

                    <div class="keuangan-summary-value">
                        Rp 12.500.000
                    </div>
                </div>

                <div class="keuangan-summary-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3v18"/>
                        <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"/>
                    </svg>
                </div>
            </div>

            <div class="keuangan-summary-info">
                Akumulasi seluruh transaksi pemasukan
            </div>
        </div>

        <div class="keuangan-summary-card">
            <div class="keuangan-summary-top">
                <div>
                    <div class="keuangan-summary-label">
                        Total Pengeluaran
                    </div>

                    <div class="keuangan-summary-value">
                        Rp 4.200.000
                    </div>
                </div>

                <div class="keuangan-summary-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3v18"/>
                        <path d="M17 7c0-2-2.2-3-5-3s-5 1-5 3 2.2 3 5 3 5 1 5 3-2.2 3-5 3-5-1-5-3"/>
                    </svg>
                </div>
            </div>

            <div class="keuangan-summary-info">
                Akumulasi seluruh transaksi pengeluaran
            </div>
        </div>

        <div class="keuangan-summary-card">
            <div class="keuangan-summary-top">
                <div>
                    <div class="keuangan-summary-label">
                        Saldo Kas
                    </div>

                    <div class="keuangan-summary-value">
                        Rp 8.300.000
                    </div>
                </div>

                <div class="keuangan-summary-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <path d="M3 10h18"/>
                        <path d="M16 15h2"/>
                    </svg>
                </div>
            </div>

            <div class="keuangan-summary-info">
                Pemasukan dikurangi pengeluaran
            </div>
        </div>

        <div class="keuangan-summary-card">
            <div class="keuangan-summary-top">
                <div>
                    <div class="keuangan-summary-label">
                        Jumlah Transaksi
                    </div>

                    <div class="keuangan-summary-value">
                        128
                    </div>
                </div>

                <div class="keuangan-summary-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 6h13"/>
                        <path d="M8 12h13"/>
                        <path d="M8 18h13"/>
                        <path d="M3 6h.01"/>
                        <path d="M3 12h.01"/>
                        <path d="M3 18h.01"/>
                    </svg>
                </div>
            </div>

            <div class="keuangan-summary-info">
                Total transaksi yang tercatat
            </div>
        </div>

    </div>

    {{-- TRANSACTION --}}
    <div class="keuangan-card">

        <div class="keuangan-card-header">
            <div>
                <h2 class="keuangan-card-title">
                    Riwayat Transaksi
                </h2>

                <p class="keuangan-card-subtitle">
                    Daftar pemasukan dan pengeluaran bank sampah.
                </p>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="keuangan-filter">

            <div class="keuangan-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-4-4"/>
                </svg>

                <input
                    type="text"
                    id="keuanganSearch"
                    class="keuangan-input"
                    placeholder="Cari transaksi..."
                >
            </div>

            <select
                id="keuanganTypeFilter"
                class="keuangan-select"
            >
                <option value="">Semua Jenis</option>
                <option value="Pemasukan">Pemasukan</option>
                <option value="Pengeluaran">Pengeluaran</option>
            </select>

            <select
                id="keuanganPeriodFilter"
                class="keuangan-select"
            >
                <option value="">Semua Periode</option>
                <option value="September 2026">September 2026</option>
                <option value="Agustus 2026">Agustus 2026</option>
                <option value="Juli 2026">Juli 2026</option>
            </select>

        </div>

        {{-- TABLE --}}
        <div class="keuangan-table-wrapper">

            <table class="keuangan-table">

                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody id="keuanganTableBody">

                    <tr data-type="Pemasukan" data-period="September 2026">
                        <td class="transaction-date">
                            19 Sep 2026
                        </td>

                        <td>
                            <span class="keuangan-badge badge-income">
                                Pemasukan
                            </span>
                        </td>

                        <td class="transaction-category">
                            Penjualan Sampah
                        </td>

                        <td class="transaction-description">
                            Penjualan sampah ke pengepul
                        </td>

                        <td class="transaction-amount amount-income">
                            + Rp 2.500.000
                        </td>

                        <td>
                            <div class="keuangan-actions">

                                <button
                                    type="button"
                                    class="keuangan-action"
                                    title="Detail"
                                    onclick="detailKeuangan(this)"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="M12 10v6"/>
                                        <path d="M12 7h.01"/>
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="keuangan-action"
                                    title="Edit"
                                    onclick="editKeuangan(this)"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="keuangan-action delete"
                                    title="Hapus"
                                    onclick="deleteKeuangan(this)"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18"/>
                                        <path d="M8 6V4h8v2"/>
                                        <path d="M19 6l-1 14H6L5 6"/>
                                    </svg>
                                </button>

                            </div>
                        </td>
                    </tr>

                    <tr data-type="Pengeluaran" data-period="September 2026">
                        <td class="transaction-date">
                            18 Sep 2026
                        </td>

                        <td>
                            <span class="keuangan-badge badge-expense">
                                Pengeluaran
                            </span>
                        </td>

                        <td class="transaction-category">
                            Operasional
                        </td>

                        <td class="transaction-description">
                            Biaya operasional bank sampah
                        </td>

                        <td class="transaction-amount amount-expense">
                            - Rp 750.000
                        </td>

                        <td>
                            <div class="keuangan-actions">

                                <button
                                    type="button"
                                    class="keuangan-action"
                                    title="Detail"
                                    onclick="detailKeuangan(this)"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="M12 10v6"/>
                                        <path d="M12 7h.01"/>
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="keuangan-action"
                                    title="Edit"
                                    onclick="editKeuangan(this)"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="keuangan-action delete"
                                    title="Hapus"
                                    onclick="deleteKeuangan(this)"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18"/>
                                        <path d="M8 6V4h8v2"/>
                                        <path d="M19 6l-1 14H6L5 6"/>
                                    </svg>
                                </button>

                            </div>
                        </td>
                    </tr>

                    <tr data-type="Pemasukan" data-period="September 2026">
                        <td class="transaction-date">
                            15 Sep 2026
                        </td>

                        <td>
                            <span class="keuangan-badge badge-income">
                                Pemasukan
                            </span>
                        </td>

                        <td class="transaction-category">
                            Donasi
                        </td>

                        <td class="transaction-description">
                            Donasi untuk kegiatan bank sampah
                        </td>

                        <td class="transaction-amount amount-income">
                            + Rp 1.000.000
                        </td>

                        <td>
                            <div class="keuangan-actions">

                                <button
                                    type="button"
                                    class="keuangan-action"
                                    title="Detail"
                                    onclick="detailKeuangan(this)"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="M12 10v6"/>
                                        <path d="M12 7h.01"/>
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="keuangan-action"
                                    title="Edit"
                                    onclick="editKeuangan(this)"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="keuangan-action delete"
                                    title="Hapus"
                                    onclick="deleteKeuangan(this)"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18"/>
                                        <path d="M8 6V4h8v2"/>
                                        <path d="M19 6l-1 14H6L5 6"/>
                                    </svg>
                                </button>

                            </div>
                        </td>
                    </tr>

                    <tr data-type="Pengeluaran" data-period="Agustus 2026">
                        <td class="transaction-date">
                            30 Agu 2026
                        </td>

                        <td>
                            <span class="keuangan-badge badge-expense">
                                Pengeluaran
                            </span>
                        </td>

                        <td class="transaction-category">
                            Transportasi
                        </td>

                        <td class="transaction-description">
                            Biaya transportasi pengambilan sampah
                        </td>

                        <td class="transaction-amount amount-expense">
                            - Rp 450.000
                        </td>

                        <td>
                            <div class="keuangan-actions">

                                <button
                                    type="button"
                                    class="keuangan-action"
                                    title="Detail"
                                    onclick="detailKeuangan(this)"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="M12 10v6"/>
                                        <path d="M12 7h.01"/>
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="keuangan-action"
                                    title="Edit"
                                    onclick="editKeuangan(this)"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="keuangan-action delete"
                                    title="Hapus"
                                    onclick="deleteKeuangan(this)"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18"/>
                                        <path d="M8 6V4h8v2"/>
                                        <path d="M19 6l-1 14H6L5 6"/>
                                    </svg>
                                </button>

                            </div>
                        </td>
                    </tr>

                </tbody>

            </table>

            <div
                class="keuangan-empty"
                id="keuanganEmpty"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-4-4"/>
                </svg>

                <strong>Data tidak ditemukan</strong>
                Tidak ada transaksi yang sesuai dengan pencarian atau filter.
            </div>

        </div>

    </div>

</div>


{{-- =========================
     MODAL TAMBAH TRANSAKSI
========================= --}}

<div
    class="keuangan-modal"
    id="keuanganModal"
    aria-hidden="true"
>
    <div class="keuangan-modal-box">

        <div class="keuangan-modal-header">

            <h2 class="keuangan-modal-title">
                Tambah Transaksi
            </h2>

            <button
                type="button"
                class="keuangan-modal-close"
                id="closeKeuanganModal"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6 6 18"/>
                    <path d="m6 6 12 12"/>
                </svg>
            </button>

        </div>

        <div class="keuangan-modal-body">

            <form id="keuanganForm">

                <div class="keuangan-form-grid">

                    <div class="keuangan-form-group">

                        <label class="keuangan-form-label">
                            Jenis Transaksi
                        </label>

                        <select
                            class="keuangan-select"
                            name="jenis"
                            required
                        >
                            <option value="">
                                Pilih jenis transaksi
                            </option>

                            <option value="Pemasukan">
                                Pemasukan
                            </option>

                            <option value="Pengeluaran">
                                Pengeluaran
                            </option>
                        </select>

                    </div>

                    <div class="keuangan-form-group">

                        <label class="keuangan-form-label">
                            Tanggal
                        </label>

                        <input
                            type="date"
                            class="keuangan-input"
                            name="tanggal"
                            value="{{ date('Y-m-d') }}"
                            required
                        >

                    </div>

                    <div class="keuangan-form-group">

                        <label class="keuangan-form-label">
                            Kategori
                        </label>

                        <input
                            type="text"
                            class="keuangan-input"
                            name="kategori"
                            placeholder="Contoh: Operasional"
                            required
                        >

                    </div>

                    <div class="keuangan-form-group">

                        <label class="keuangan-form-label">
                            Jumlah
                        </label>

                        <input
                            type="number"
                            class="keuangan-input"
                            name="jumlah"
                            placeholder="0"
                            min="0"
                            required
                        >

                    </div>

                    <div class="keuangan-form-group full">

                        <label class="keuangan-form-label">
                            Keterangan
                        </label>

                        <textarea
                            class="keuangan-input"
                            name="keterangan"
                            rows="4"
                            placeholder="Tambahkan keterangan transaksi..."
                            style="height:auto;padding-top:11px;padding-bottom:11px;resize:vertical;"
                        ></textarea>

                    </div>

                </div>

                <div class="keuangan-form-footer">

                    <button
                        type="button"
                        class="keuangan-btn-secondary"
                        id="cancelKeuanganModal"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="keuangan-btn-primary"
                    >
                        Simpan Transaksi
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('keuanganModal');
    const openButton = document.getElementById('openKeuanganModal');
    const closeButton = document.getElementById('closeKeuanganModal');
    const cancelButton = document.getElementById('cancelKeuanganModal');
    const form = document.getElementById('keuanganForm');

    function openModal() {
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    openButton.addEventListener('click', openModal);
    closeButton.addEventListener('click', closeModal);
    cancelButton.addEventListener('click', closeModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('show')) {
            closeModal();
        }
    });

    /* =========================
       SEARCH + FILTER
    ========================= */

    const searchInput = document.getElementById('keuanganSearch');
    const typeFilter = document.getElementById('keuanganTypeFilter');
    const periodFilter = document.getElementById('keuanganPeriodFilter');
    const tableBody = document.getElementById('keuanganTableBody');
    const emptyState = document.getElementById('keuanganEmpty');

    function filterTable() {

        const search = searchInput.value.toLowerCase().trim();
        const type = typeFilter.value;
        const period = periodFilter.value;

        const rows = tableBody.querySelectorAll('tr');
        let visibleRows = 0;

        rows.forEach(function (row) {

            const text = row.textContent.toLowerCase();
            const rowType = row.dataset.type || '';
            const rowPeriod = row.dataset.period || '';

            const matchSearch = !search || text.includes(search);
            const matchType = !type || rowType === type;
            const matchPeriod = !period || rowPeriod === period;

            if (matchSearch && matchType && matchPeriod) {
                row.style.display = '';
                visibleRows++;
            } else {
                row.style.display = 'none';
            }

        });

        emptyState.style.display =
            visibleRows === 0 ? 'block' : 'none';
    }

    searchInput.addEventListener('input', filterTable);
    typeFilter.addEventListener('change', filterTable);
    periodFilter.addEventListener('change', filterTable);

    /* =========================
       DUMMY SUBMIT
    ========================= */

    form.addEventListener('submit', function (event) {

        event.preventDefault();

        alert(
            'Form transaksi berhasil diisi.\n\n' +
            'Backend akan kita hubungkan pada tahap berikutnya.'
        );

        form.reset();

        closeModal();
    });

});


/* =========================
   DUMMY ACTION
========================= */

function detailKeuangan(button) {

    const row = button.closest('tr');

    alert(
        'Detail transaksi:\n\n' +
        row.innerText.replace(/\n+/g, '\n')
    );
}


function editKeuangan(button) {

    alert(
        'Fitur edit masih frontend prototype.\n' +
        'Backend akan kita hubungkan setelah UI selesai.'
    );
}


function deleteKeuangan(button) {

    const row = button.closest('tr');

    const confirmed = confirm(
        'Hapus transaksi ini dari prototype?'
    );

    if (confirmed) {
        row.remove();

        const rows = document.querySelectorAll(
            '#keuanganTableBody tr'
        );

        if (rows.length === 0) {
            document.getElementById('keuanganEmpty').style.display = 'block';
        }
    }
}
</script>

@endsection
