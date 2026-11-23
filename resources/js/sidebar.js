/**
 * sidebar.js
 * ---------------------------------------------------------
 * Render <aside class="d-sidebar"> lengkap: logo brand,
 * daftar menu (dari navigation.js), dan footer workspace.
 */
import { navigation } from "./navigation.js";

/** Render 1 link menu biasa (tanpa children) */
function renderNavLink(item, activeKey) {
  const activeClass = item.key === activeKey ? " is-active" : "";
  const badge = item.badge
    ? `<span class="nav-badge ${item.badge.kind}">${item.badge.text}</span>`
    : "";

  return `
    <a class="nav-link${activeClass}" href="${item.href}">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${item.icon}</svg>
      <span>${item.text}</span>
      ${badge}
    </a>`;
}

/** Render 1 grup menu collapsible (item yang punya children) */
function renderNavGroup(item, activeKey) {
  const isOpen = item.children && item.children.some((child) => child.key === activeKey)
    ? " is-open"
    : "";

  const childrenHtml = item.children
    ? item.children
        .map((child) => {
          const isChildActive = child.key === activeKey ? ' class="is-active"' : '';
          return `<a href="${child.href}"${isChildActive}>${child.text}</a>`;
        })
        .join("")
    : "";

  return `
    <div class="nav-item-group${isOpen}" data-nav-group>
      <a class="nav-link" href="javascript:void(0)" data-nav-toggle>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${item.icon}</svg>
        <span>${item.text}</span>
        <svg class="chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <div class="nav-submenu">${childrenHtml}</div>
    </div>`;
}

/** Render 1 section/grup label (mis. "Sistem Pengelolaan") beserta isinya */
function renderNavSection(section, activeKey) {
  if (!section.items || !Array.isArray(section.items)) return "";

  const itemsHtml = section.items
    .map((item) =>
      item.children && item.children.length > 0
        ? renderNavGroup(item, activeKey)
        : renderNavLink(item, activeKey)
    )
    .join("");

  return `
    <nav class="nav-section">
      <div class="nav-label">${section.label}</div>
      ${itemsHtml}
    </nav>`;
}

/**
 * Render keseluruhan sidebar.
 * @param {string} activeKey - key menu yang sedang aktif (dari body[data-active])
 */
export function renderSidebar(activeKey = "") {
  if (!navigation || !Array.isArray(navigation)) return "";

  const sectionsHtml = navigation
    .map((section) => renderNavSection(section, activeKey))
    .join("");

  return `
    <aside class="d-sidebar">
      <div class="brand">
        <div class="brand-logo">
          <!-- Logo Daur Ulang / Bank Sampah -->
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M7 19H4.815a1.83 1.83 0 0 1-1.57-.881 1.785 1.785 0 0 1-.004-1.784L7.196 9.5"/>
            <path d="M11 19h8.205a1.83 1.83 0 0 0 1.57-.881 1.785 1.785 0 0 0 .004-1.784L16.82 9.5"/>
            <path d="M12 5.5V3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v2.5"/>
            <path d="M12 3 8 9.5h8L12 3z"/>
            <path d="m5 14 3.5 6h7L19 14"/>
          </svg>
        </div>
        <div class="brand-text">
          <div class="brand-name">Sahabat Ibu</div>
          <div class="brand-tag">Bank Sampah v1.0</div>
        </div>
      </div>
      ${sectionsHtml}
    </aside>`;
}

/**
 * Inisialisasi event listener toggle untuk submenu collapsible
 */
export function initSidebarEvents() {
  const toggles = document.querySelectorAll("[data-nav-toggle]");

  toggles.forEach((toggle) => {
    toggle.addEventListener("click", (e) => {
      e.preventDefault();
      const parentGroup = toggle.closest("[data-nav-group]");
      if (parentGroup) {
        parentGroup.classList.toggle("is-open");
      }
    });
  });
}
