/**
 * topbar.js
 * ---------------------------------------------------------
 * Topbar Admin (Hamburger, Search, Dropdown, Theme, Logout)
 */

import { updateSidebarPengaduanBadge } from "./sidebar.js";

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
                ? `<svg class="sep" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>`
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
 * ESCAPE HTML & CSRF
 * ========================================================= */

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute("content") || "" : "";
}

/* =========================================================
 * PENGADUAN MAILBOX SYNC
 * ========================================================= */

async function syncPengaduanMailbox() {
    const countEl = document.querySelector("[data-pengaduan-count]");
    const listEl = document.querySelector("[data-pengaduan-list]");
    if (!countEl && !listEl) return;

    try {
        const response = await fetch("/admin/pages/pengaduan/summary", {
            headers: {
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        });
        if (!response.ok) return;

        const payload = await response.json();
        const total = Number(payload.menunggu_count) || 0;
        const items = Array.isArray(payload.terbaru) ? payload.terbaru : [];

        if (countEl) {
            countEl.textContent = total > 99 ? "99+" : String(total);
            countEl.classList.toggle("is-hidden", total <= 0);
        }

        updateSidebarPengaduanBadge(total);

        if (listEl) {
            if (items.length === 0) {
                listEl.innerHTML = `
                    <div class="dd-empty">
                        <span style="font-size: 22px; opacity: .75; margin-bottom: 2px;">📭</span>
                        <strong>Tidak ada pengaduan menunggu</strong>
                        <small>Semua laporan warga sudah ditangani.</small>
                    </div>
                `;
            } else {
                listEl.innerHTML = items.map((item) => `
                    <a class="dd-item" href="${escapeHtml(item.url || "/admin/pages/pengaduan")}">
                        <div class="dd-avatar a1">${escapeHtml(item.inisial || "W")}</div>
                        <div class="dd-body">
                            <div class="dd-row-head"><strong>${escapeHtml(item.nama || "Warga")}</strong></div>
                            <div class="dd-text">${escapeHtml(item.judul || "")}</div>
                        </div>
                    </a>
                `).join("");
            }
        }
    } catch (_) {
        // Abaikan jika gagal memuat saat offline atau berpindah halaman
    }
}

/* =========================================================
 * RENDER TOPBAR
 * ========================================================= */

export function renderTopbar(crumbsString) {
    return `
        <header class="d-topbar">
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

            <div class="topbar-actions">
                <button class="cmd" type="button" data-palette-open aria-label="Open search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <span>Search...</span>
                    <kbd class="kbd">⌘K</kbd>
                </button>

                <div class="dd-wrap">
                    <button class="icon-btn" type="button" data-dropdown aria-label="Pengaduan Masuk" title="Pengaduan Masuk">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>
                        </svg>
                        <span class="count info is-hidden" data-pengaduan-count>0</span>
                    </button>
                    <div class="dd-menu" role="menu">
                        <div class="dd-head">Pengaduan Masuk</div>
                        <div class="dd-list" data-pengaduan-list>
                            <div class="dd-empty">Memuat pengaduan...</div>
                        </div>
                        <a class="dd-footer" href="/admin/pages/pengaduan">Lihat semua pengaduan →</a>
                    </div>
                </div>

                <button class="icon-btn" id="themeToggle" type="button" aria-label="Toggle theme" title="Ganti tema"></button>

                <form action="/logout" method="POST" class="logout-form" title="Logout">
                    <input type="hidden" name="_token" value="${getCsrfToken()}">
                    <button type="submit" class="icon-btn logout-button" aria-label="Logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>
                        </svg>
                    </button>
                </form>
            </div>
        </header>
    `;
}

/* =========================================================
 * ICONS & THEME
 * ========================================================= */

const ICON_MOON = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>`;
const ICON_SUN = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>`;

function updateThemeIcon() {
    const button = document.querySelector("#themeToggle");
    if (!button) return;
    const isDark = document.documentElement.getAttribute("data-theme") === "dark";
    button.innerHTML = isDark ? ICON_SUN : ICON_MOON;
}

function initTheme() {
    let savedTheme = null;
    try { savedTheme = localStorage.getItem("dash26-theme"); } catch (error) { savedTheme = null; }
    if (savedTheme === "dark" || savedTheme === "light") {
        document.documentElement.setAttribute("data-theme", savedTheme);
    }
    updateThemeIcon();
}

function toggleTheme() {
    const current = document.documentElement.getAttribute("data-theme") || "light";
    const next = current === "dark" ? "light" : "dark";
    document.documentElement.setAttribute("data-theme", next);
    try { localStorage.setItem("dash26-theme", next); } catch (error) {}
    updateThemeIcon();
}

/* =========================================================
 * DROPDOWN
 * ========================================================= */

function closeAllDropdowns() {
    document.querySelectorAll(".dd-wrap.is-open").forEach((wrap) => {
        wrap.classList.remove("is-open");
    });
}

function toggleDropdown(trigger) {
    const wrapper = trigger.closest(".dd-wrap");
    if (!wrapper) return;

    const wasOpen = wrapper.classList.contains("is-open");
    closeAllDropdowns();
    if (!wasOpen) wrapper.classList.add("is-open");
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
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
    const filtered = search ? links.filter((item) => item.text.toLowerCase().includes(search)) : links;
    const unique = [];
    const used = new Set();
    filtered.forEach((item) => {
        const key = `${item.text}|${item.href}`;
        if (!used.has(key)) { used.add(key); unique.push(item); }
    });
    if (unique.length === 0) {
        container.innerHTML = `<div class="topbar-search-empty">Tidak ada menu yang ditemukan.</div>`;
        return;
    }
    container.innerHTML = unique.slice(0, 15).map((item) => `
        <a class="topbar-search-item" href="${escapeHtml(item.href)}">
            <span>${escapeHtml(item.text)}</span><span class="search-arrow">→</span>
        </a>
    `).join("");
}

function openSearch() {
    const overlay = createSearchOverlay();
    overlay.classList.add("active");
    document.body.classList.add("search-open");
    renderSearchResults("");
    const input = overlay.querySelector("#topbarSearchInput");
    if (input) setTimeout(() => input.focus(), 50);
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
 * LOGOUT CONFIRM
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
            </div>
            <h3 class="logout-confirm-title">Keluar dari Akun?</h3>
            <p class="logout-confirm-text">Anda akan keluar dari sesi admin ini dan perlu login kembali untuk melanjutkan.</p>
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
 * HAMBURGER (MOBILE DRAWER)
 * ========================================================= */

function toggleSidebar() {
    const body = document.body;
    if (!document.querySelector(".drawer-backdrop")) {
        const backdrop = document.createElement("div");
        backdrop.className = "drawer-backdrop";
        backdrop.setAttribute("aria-hidden", "true");
        body.appendChild(backdrop);
        backdrop.addEventListener("click", () => {
            body.classList.remove("has-drawer-open");
        });
    }
    body.classList.toggle("has-drawer-open");
}

/* =========================================================
 * INIT TOPBAR EVENTS
 * ========================================================= */

export function initTopbarEvents() {
    syncPengaduanMailbox();

    if (window.__topbarEventsInitialized) {
        updateThemeIcon();
        return;
    }
    window.__topbarEventsInitialized = true;
    initTheme();

    document.addEventListener("click", function (event) {
        const themeButton = event.target.closest("#themeToggle");
        if (themeButton) { event.preventDefault(); toggleTheme(); return; }

        const searchButton = event.target.closest("[data-palette-open]");
        if (searchButton) { event.preventDefault(); openSearch(); return; }

        const closeSearchButton = event.target.closest("[data-search-close]");
        if (closeSearchButton) { event.preventDefault(); closeSearch(); return; }

        if (event.target.classList && event.target.classList.contains("topbar-search-backdrop")) {
            closeSearch(); return;
        }

        const dropdownButton = event.target.closest("[data-dropdown]");
        if (dropdownButton) {
            event.preventDefault(); event.stopPropagation(); toggleDropdown(dropdownButton); return;
        }

        const drawerButton = event.target.closest("[data-drawer-open]");
        if (drawerButton) { event.preventDefault(); toggleSidebar(); return; }

        const logoutConfirmButton = event.target.closest("[data-logout-confirm]");
        if (logoutConfirmButton) {
            event.preventDefault();
            if (pendingLogoutForm) {
                logoutConfirmButton.disabled = true;
                HTMLFormElement.prototype.submit.call(pendingLogoutForm);
            }
            return;
        }

        const logoutCancelButton = event.target.closest("[data-logout-cancel]");
        if (logoutCancelButton) { event.preventDefault(); closeLogoutConfirm(); return; }

        if (event.target.id === "logoutConfirmOverlay") { closeLogoutConfirm(); return; }

        if (!event.target.closest(".dd-wrap")) { closeAllDropdowns(); }
    });

    document.addEventListener("keydown", function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "k") {
            event.preventDefault(); openSearch(); return;
        }
        if (event.key === "Escape") {
            closeSearch(); closeAllDropdowns(); closeLogoutConfirm();
        }
    });

    document.addEventListener("input", handleSearchInput);

    document.addEventListener("submit", function (event) {
        const form = event.target.closest(".logout-form");
        if (!form) return;
        event.preventDefault();
        openLogoutConfirm(form);
    });

    document.addEventListener("palette:open", openSearch);
    updateThemeIcon();
}
