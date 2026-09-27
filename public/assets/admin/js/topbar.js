/**
 * topbar.js
 * ---------------------------------------------------------
 * Topbar Admin
 *
 * Fitur:
 * - Breadcrumb
 * - Hamburger mobile
 * - Search
 * - Ctrl + K / Cmd + K
 * - Dropdown notifikasi
 * - Dropdown pesan
 * - Dark / Light mode
 * - Button Logout
 */

/* =========================================================
 * BREADCRUMB
 * ========================================================= */

function renderBreadcrumbs(crumbsString) {
    if (!crumbsString) return "";

    const parts = crumbsString
        .split("|")
        .map((item) => item.trim())
        .filter(Boolean);

    return parts
        .map((label, index) => {
            const separator = index > 0
                ? `<svg class="sep" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                       <path d="m9 18 6-6-6-6"/>
                   </svg>`
                : "";

            const isLast = index === parts.length - 1;

            return `
                ${separator}
                <span${isLast ? ' class="current"' : ""}>${escapeHtml(label)}</span>
            `;
        })
        .join("");
}

/* =========================================================
 * ESCAPE HTML
 * ========================================================= */

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/* =========================================================
 * CSRF
 * ========================================================= */

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute("content") || "" : "";
}

/* =========================================================
 * RENDER TOPBAR
 * ========================================================= */

export function renderTopbar(crumbsString) {
    return `
        <header class="d-topbar">

            <!-- BREADCRUMB -->
            <div class="crumbs">
                <button class="hamburger" data-drawer-open aria-label="Open navigation" type="button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>

                ${renderBreadcrumbs(crumbsString)}
            </div>

            <!-- TOPBAR ACTIONS -->
            <div class="topbar-actions">

                <!-- SEARCH -->
                <button class="cmd" type="button" data-palette-open aria-label="Open search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m21 21-4.3-4.3"/>
                    </svg>
                    <span>Search...</span>
                    <kbd class="kbd">⌘K</kbd>
                </button>

                <!-- NOTIFICATIONS -->
                <div class="dd-wrap">
                    <button class="icon-btn" type="button" data-dropdown aria-label="Notifications">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span class="count danger">3</span>
                    </button>

                    <div class="dd-menu" role="menu">
                        <div class="dd-head">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>
                            Notifications
                        </div>

                        <div class="dd-list">
                            <a class="dd-item" href="#">
                                <div class="dd-avatar a1">JD</div>
                                <div class="dd-body">
                                    <div class="dd-text"><strong>John Doe</strong> liked your <em>post</em></div>
                                    <div class="dd-time">5 MIN AGO</div>
                                </div>
                            </a>

                            <a class="dd-item" href="#">
                                <div class="dd-avatar a2">MD</div>
                                <div class="dd-body">
                                    <div class="dd-text"><strong>Moo Doe</strong> liked your <em>cover image</em></div>
                                    <div class="dd-time">7 MIN AGO</div>
                                </div>
                            </a>

                            <a class="dd-item" href="#">
                                <div class="dd-avatar a3">LD</div>
                                <div class="dd-body">
                                    <div class="dd-text"><strong>Lee Doe</strong> commented on your <em>video</em></div>
                                    <div class="dd-time">10 MIN AGO</div>
                                </div>
                            </a>
                        </div>

                        <a class="dd-footer" href="#">View all notifications →</a>
                    </div>
                </div>

                <!-- MESSAGES -->
                <div class="dd-wrap">
                    <button class="icon-btn" type="button" data-dropdown aria-label="Messages">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="m3 7 9 6 9-6"/>
                        </svg>
                        <span class="count info">3</span>
                    </button>

                    <div class="dd-menu" role="menu">
                        <div class="dd-head">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                <path d="m3 7 9 6 9-6"/>
                            </svg>
                            Messages
                        </div>

                        <div class="dd-list">
                            <a class="dd-item" href="#">
                                <div class="dd-avatar a1">JD</div>
                                <div class="dd-body">
                                    <div class="dd-row-head">
                                        <strong>John Doe</strong>
                                        <span class="dd-time">5 MIN</span>
                                    </div>
                                    <div class="dd-preview">Want to create your own customized data generator for your app…</div>
                                </div>
                            </a>

                            <a class="dd-item" href="#">
                                <div class="dd-avatar a2">MD</div>
                                <div class="dd-body">
                                    <div class="dd-row-head">
                                        <strong>Moo Doe</strong>
                                        <span class="dd-time">15 MIN</span>
                                    </div>
                                    <div class="dd-preview">Want to create your own customized data generator for your app…</div>
                                </div>
                            </a>

                            <a class="dd-item" href="#">
                                <div class="dd-avatar a3">LD</div>
                                <div class="dd-body">
                                    <div class="dd-row-head">
                                        <strong>Lee Doe</strong>
                                        <span class="dd-time">25 MIN</span>
                                    </div>
                                    <div class="dd-preview">Want to create your own customized data generator for your app…</div>
                                </div>
                            </a>
                        </div>

                        <a class="dd-footer" href="#">View all messages →</a>
                    </div>
                </div>

                <!-- THEME -->
                <button class="icon-btn" id="themeToggle" type="button" aria-label="Toggle theme" title="Ganti tema"></button>

                <!-- ACCOUNT -> LANGSUNG LOGOUT -->
                <form action="/logout" method="POST" class="logout-form" title="Logout">
                    <input type="hidden" name="_token" value="${getCsrfToken()}">
                    <button type="submit" class="icon-btn logout-button" aria-label="Logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <path d="M16 17l5-5-5-5"/>
                            <path d="M21 12H9"/>
                        </svg>
                    </button>
                </form>

            </div>

        </header>
    `;
}

/* =========================================================
 * THEME ICON
 * ========================================================= */

const ICON_MOON = `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
    </svg>
`;

const ICON_SUN = `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="5"/>
        <path d="M12 1v2"/>
        <path d="M12 21v2"/>
        <path d="M4.22 4.22l1.42 1.42"/>
        <path d="M18.36 18.36l1.42 1.42"/>
        <path d="M1 12h2"/>
        <path d="M21 12h2"/>
        <path d="M4.22 19.78l1.42-1.42"/>
        <path d="M18.36 5.64l1.42-1.42"/>
    </svg>
`;

/* =========================================================
 * THEME
 * ========================================================= */

function updateThemeIcon() {
    const button = document.querySelector("#themeToggle");
    if (!button) return;

    const isDark = document.documentElement.getAttribute("data-theme") === "dark";
    button.innerHTML = isDark ? ICON_SUN : ICON_MOON;
}

function initTheme() {
    let savedTheme = null;

    try {
        savedTheme = localStorage.getItem("dash26-theme");
    } catch (error) {
        savedTheme = null;
    }

    if (savedTheme === "dark" || savedTheme === "light") {
        document.documentElement.setAttribute("data-theme", savedTheme);
    }

    updateThemeIcon();
}

function toggleTheme() {
    const current = document.documentElement.getAttribute("data-theme") || "light";
    const next = current === "dark" ? "light" : "dark";

    document.documentElement.setAttribute("data-theme", next);

    try {
        localStorage.setItem("dash26-theme", next);
    } catch (error) {
        // Abaikan jika localStorage tidak tersedia
    }

    updateThemeIcon();
}

/* =========================================================
 * DROPDOWN (notifikasi & pesan)
 * ========================================================= */

function closeAllDropdowns() {
    document.querySelectorAll(".dd-menu.show").forEach((menu) => {
        menu.classList.remove("show");
    });
}

function toggleDropdown(trigger) {
    const wrapper = trigger.closest(".dd-wrap");
    if (!wrapper) return;

    const menu = wrapper.querySelector(".dd-menu");
    if (!menu) return;

    const wasOpen = menu.classList.contains("show");
    closeAllDropdowns();

    if (!wasOpen) {
        menu.classList.add("show");
    }
}

/* =========================================================
 * SEARCH
 * ========================================================= */

function createSearchOverlay() {
    const existing = document.querySelector("#topbarSearchOverlay");
    if (existing) return existing;

    const overlay = document.createElement("div");
    overlay.id = "topbarSearchOverlay";

    overlay.innerHTML = `
        <div class="topbar-search-backdrop"></div>

        <div class="topbar-search-card" role="dialog" aria-modal="true" aria-label="Search">
            <div class="topbar-search-header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>

                <input id="topbarSearchInput" type="text" placeholder="Cari menu..." autocomplete="off">

                <button type="button" data-search-close aria-label="Close search">ESC</button>
            </div>

            <div id="topbarSearchResults" class="topbar-search-results"></div>
        </div>
    `;

    document.body.appendChild(overlay);
    return overlay;
}

function getNavigationLinks() {
    const links = [];

    document.querySelectorAll(".sidebar a[href], nav a[href]").forEach((link) => {
        const href = link.getAttribute("href");
        const text = link.textContent.trim().replace(/\s+/g, " ");

        if (!href || href === "#" || href.startsWith("javascript:") || !text) return;

        links.push({ text, href });
    });

    return links;
}

function renderSearchResults(keyword = "") {
    const container = document.querySelector("#topbarSearchResults");
    if (!container) return;

    const search = keyword.trim().toLowerCase();
    const links = getNavigationLinks();
    const filtered = search
        ? links.filter((item) => item.text.toLowerCase().includes(search))
        : links;

    const unique = [];
    const used = new Set();

    filtered.forEach((item) => {
        const key = `${item.text}|${item.href}`;

        if (!used.has(key)) {
            used.add(key);
            unique.push(item);
        }
    });

    if (unique.length === 0) {
        container.innerHTML = `<div class="topbar-search-empty">Tidak ada menu yang ditemukan.</div>`;
        return;
    }

    container.innerHTML = unique
        .slice(0, 15)
        .map(
            (item) => `
                <a class="topbar-search-item" href="${escapeHtml(item.href)}">
                    <span>${escapeHtml(item.text)}</span>
                    <span class="search-arrow">→</span>
                </a>
            `
        )
        .join("");
}

function openSearch() {
    const overlay = createSearchOverlay();

    overlay.classList.add("active");
    document.body.classList.add("search-open");

    renderSearchResults("");

    const input = overlay.querySelector("#topbarSearchInput");
    if (input) {
        setTimeout(() => input.focus(), 50);
    }
}

function closeSearch() {
    const overlay = document.querySelector("#topbarSearchOverlay");
    if (!overlay) return;

    overlay.classList.remove("active");
    document.body.classList.remove("search-open");
}

function handleSearchInput(event) {
    if (event.target && event.target.id === "topbarSearchInput") {
        renderSearchResults(event.target.value);
    }
}

/* =========================================================
 * KONFIRMASI LOGOUT (modal, selaras dengan modal Verifikasi Setoran / Data Warga)
 * ========================================================= */

let pendingLogoutForm = null;

function ensureLogoutConfirmModal() {
    const existing = document.querySelector("#logoutConfirmOverlay");
    if (existing) return existing;

    const overlay = document.createElement("div");
    overlay.id = "logoutConfirmOverlay";
    overlay.className = "logout-confirm-overlay";

    overlay.innerHTML = `
        <div class="logout-confirm-box">
            <div class="logout-confirm-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <path d="M16 17l5-5-5-5"/>
                    <path d="M21 12H9"/>
                </svg>
            </div>
            <h3 class="logout-confirm-title">Keluar dari Akun?</h3>
            <p class="logout-confirm-text">
                Anda akan keluar dari sesi admin ini dan perlu login kembali untuk melanjutkan.
            </p>
            <div class="logout-confirm-actions">
                <button type="button" class="logout-confirm-cancel" data-logout-cancel>Batal</button>
                <button type="button" class="logout-confirm-submit" data-logout-confirm>Ya, Keluar</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);
    return overlay;
}

function openLogoutConfirm(form) {
    pendingLogoutForm = form;
    ensureLogoutConfirmModal().classList.add("active");
}

function closeLogoutConfirm() {
    const overlay = document.querySelector("#logoutConfirmOverlay");
    if (overlay) overlay.classList.remove("active");
    pendingLogoutForm = null;
}

/* =========================================================
 * HAMBURGER
 * ========================================================= */

function toggleSidebar() {
    document.body.classList.toggle("sidebar-open");

    /*
     * Jika sidebar menggunakan drawer-open,
     * class ini juga ditambahkan agar kompatibel.
     */
    document.body.classList.toggle("drawer-open");
}

/* =========================================================
 * INIT TOPBAR EVENTS
 *
 * PENTING:
 * Fungsi ini sengaja di-export karena layout.js mengimpornya.
 * ========================================================= */

export function initTopbarEvents() {
    // Hindari event listener terpasang dua kali.
    if (window.__topbarEventsInitialized) {
        updateThemeIcon();
        return;
    }

    window.__topbarEventsInitialized = true;

    initTheme();

    /* ----- CLICK DELEGATION ----- */
    document.addEventListener("click", function (event) {

        const themeButton = event.target.closest("#themeToggle");
        if (themeButton) {
            event.preventDefault();
            toggleTheme();
            return;
        }

        const searchButton = event.target.closest("[data-palette-open]");
        if (searchButton) {
            event.preventDefault();
            openSearch();
            return;
        }

        const closeSearchButton = event.target.closest("[data-search-close]");
        if (closeSearchButton) {
            event.preventDefault();
            closeSearch();
            return;
        }

        if (event.target.classList && event.target.classList.contains("topbar-search-backdrop")) {
            closeSearch();
            return;
        }

        const dropdownButton = event.target.closest("[data-dropdown]");
        if (dropdownButton) {
            event.preventDefault();
            event.stopPropagation();
            toggleDropdown(dropdownButton);
            return;
        }

        const drawerButton = event.target.closest("[data-drawer-open]");
        if (drawerButton) {
            event.preventDefault();
            toggleSidebar();
            return;
        }

        const logoutConfirmButton = event.target.closest("[data-logout-confirm]");
        if (logoutConfirmButton) {
            event.preventDefault();
            if (pendingLogoutForm) {
                logoutConfirmButton.disabled = true;
                // submit() asli tidak memicu event "submit", jadi tidak akan membuka modal lagi.
                HTMLFormElement.prototype.submit.call(pendingLogoutForm);
            }
            return;
        }

        const logoutCancelButton = event.target.closest("[data-logout-cancel]");
        if (logoutCancelButton) {
            event.preventDefault();
            closeLogoutConfirm();
            return;
        }

        if (event.target.id === "logoutConfirmOverlay") {
            closeLogoutConfirm();
            return;
        }

        if (!event.target.closest(".dd-wrap")) {
            closeAllDropdowns();
        }
    });

    /* ----- KEYBOARD ----- */
    document.addEventListener("keydown", function (event) {

        // Ctrl + K / Cmd + K
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "k") {
            event.preventDefault();
            openSearch();
            return;
        }

        // Escape
        if (event.key === "Escape") {
            closeSearch();
            closeAllDropdowns();
            closeLogoutConfirm();
        }
    });

    /* ----- SEARCH INPUT ----- */
    document.addEventListener("input", handleSearchInput);

    /* ----- LOGOUT ----- */
    document.addEventListener("submit", function (event) {
        const form = event.target.closest(".logout-form");
        if (!form) return;

        // Jangan langsung submit - tampilkan modal konfirmasi dulu.
        event.preventDefault();
        openLogoutConfirm(form);
    });

    /*
     * EVENT PALETTE COMPATIBILITY
     * Jika modul command-palette lama masih mengirim palette:open, tetap didukung.
     */
    document.addEventListener("palette:open", function () {
        openSearch();
    });

    updateThemeIcon();
}

/* =========================================================
 * CSS SEARCH DINAMIS
 *
 * Tidak perlu menambahkan CSS tambahan ke Blade.
 * ========================================================= */

function injectSearchStyles() {
    if (document.querySelector("#topbar-search-dynamic-style")) return;

    const style = document.createElement("style");
    style.id = "topbar-search-dynamic-style";

    style.textContent = `
        #topbarSearchOverlay {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: none;
        }

        #topbarSearchOverlay.active {
            display: block;
        }

        .topbar-search-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            backdrop-filter: blur(4px);
        }

        .topbar-search-card {
            position: relative;
            width: min(620px, calc(100vw - 32px));
            max-height: min(650px, calc(100vh - 80px));
            margin: 80px auto 0;
            overflow: hidden;
            border-radius: 16px;
            background: var(--card-bg, #ffffff);
            color: var(--text-color, #222222);
            box-shadow: 0 25px 80px rgba(0, 0, 0, .25);
            border: 1px solid rgba(0, 0, 0, .08);
        }

        .topbar-search-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid rgba(0, 0, 0, .08);
        }

        .topbar-search-header svg {
            width: 20px;
            height: 20px;
            flex: 0 0 auto;
        }

        .topbar-search-header input {
            width: 100%;
            border: 0;
            outline: none;
            background: transparent;
            color: inherit;
            font-size: 16px;
        }

        .topbar-search-header button {
            flex: 0 0 auto;
            border: 0;
            border-radius: 7px;
            padding: 5px 8px;
            cursor: pointer;
            background: rgba(0, 0, 0, .07);
            color: inherit;
            font-size: 11px;
        }

        .topbar-search-results {
            max-height: 520px;
            overflow-y: auto;
            padding: 8px;
        }

        .topbar-search-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 13px 14px;
            border-radius: 10px;
            color: inherit;
            text-decoration: none;
            transition: background .15s ease;
        }

        .topbar-search-item:hover {
            background: rgba(0, 0, 0, .06);
        }

        .search-arrow {
            opacity: .55;
        }

        .topbar-search-empty {
            padding: 30px 20px;
            text-align: center;
            opacity: .65;
        }

        body.search-open {
            overflow: hidden;
        }

        [data-theme="dark"] .topbar-search-card {
            background: #1e1f23;
            color: #f1f1f1;
            border-color: rgba(255, 255, 255, .1);
        }

        [data-theme="dark"] .topbar-search-item:hover {
            background: rgba(255, 255, 255, .08);
        }

        [data-theme="dark"] .topbar-search-header {
            border-bottom-color: rgba(255, 255, 255, .1);
        }

        @media (max-width: 600px) {
            .topbar-search-card {
                width: calc(100vw - 20px);
                margin-top: 55px;
            }
        }

        .logout-form {
            margin: 0;
            display: inline-flex;
        }

        .logout-button svg {
            width: 18px;
            height: 18px;
        }

        .logout-button:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        /* ===== Modal Konfirmasi Logout ===== */

        .logout-confirm-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .5);
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 100000;
        }

        .logout-confirm-overlay.active {
            display: flex;
        }

        .logout-confirm-box {
            width: 100%;
            max-width: 380px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, .18);
            padding: 28px 24px 22px;
            text-align: center;
            animation: modalPop .15s ease-out;
        }

        .logout-confirm-icon {
            width: 52px;
            height: 52px;
            margin: 0 auto 14px;
            border-radius: 50%;
            background: #fef2f2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-confirm-icon svg {
            width: 24px;
            height: 24px;
        }

        .logout-confirm-title {
            margin: 0 0 6px;
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }

        .logout-confirm-text {
            margin: 0 0 20px;
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }

        .logout-confirm-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .logout-confirm-actions button {
            height: 38px;
            padding: 0 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all .15s ease;
        }

        .logout-confirm-cancel {
            background: #ffffff;
            border-color: #dfe3e8;
            color: #374151;
        }

        .logout-confirm-cancel:hover {
            background: #f8fafc;
        }

        .logout-confirm-submit {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
        }

        .logout-confirm-submit:hover {
            background: #b91c1c;
            border-color: #b91c1c;
        }

        .logout-confirm-submit:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        @keyframes modalPop {
            from { opacity: 0; transform: translateY(8px) scale(.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
    `;

    document.head.appendChild(style);
}

/* =========================================================
 * INIT
 * ========================================================= */

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
        injectSearchStyles();
    });
} else {
    injectSearchStyles();
}
