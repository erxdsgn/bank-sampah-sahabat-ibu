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
      <svg viewBox="0 0 24 24">${item.icon}</svg>
      <span>${item.text}</span>
      ${badge}
    </a>`;
}

/** Render 1 grup menu collapsible (item yang punya children) */
function renderNavGroup(item, activeKey) {
  const isOpen = item.children.some((child) => child.key === activeKey)
    ? " is-open"
    : "";

  const childrenHtml = item.children
    .map((child) => `<a href="${child.href}">${child.text}</a>`)
    .join("");

  return `
    <div class="nav-item-group${isOpen}" data-nav-group>
      <a class="nav-link" href="javascript:void(0)" data-nav-toggle>
        <svg viewBox="0 0 24 24">${item.icon}</svg>
        <span>${item.text}</span>
        <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      <div class="nav-submenu">${childrenHtml}</div>
    </div>`;
}

/** Render 1 section/grup label (mis. "Workspace") beserta isinya */
function renderNavSection(section, activeKey) {
  const itemsHtml = section.items
    .map((item) =>
      item.children
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
export function renderSidebar(activeKey) {
  const sectionsHtml = navigation
    .map((section) => renderNavSection(section, activeKey))
    .join("");

  return `
    <aside class="d-sidebar">
      <div class="brand">
        <div class="brand-logo"><svg viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
  <path fill="#ffffff" d="M14.747 9.125c.527-1.426 1.736-2.573 3.317-2.573c1.643 0 2.792 1.085 3.318 2.573l6.077 16.867c.186.496.248.931.248 1.147c0 1.209-.992 2.046-2.139 2.046c-1.303 0-1.954-.682-2.264-1.611l-.931-2.915h-8.62l-.93 2.884c-.31.961-.961 1.642-2.232 1.642c-1.24 0-2.294-.93-2.294-2.17c0-.496.155-.868.217-1.023l6.233-16.867zm.34 11.256h5.891l-2.883-8.992h-.062l-2.946 8.992z"/>
</svg></div>
        <div class="brand-text">
          <div class="brand-name">Adminator</div>
          <div class="brand-tag">v4.1.2 · preview</div>
        </div>
      </div>
      ${sectionsHtml}
      <div class="sidebar-footer">
        <div class="workspace">
          <div class="workspace-avatar">JD</div>
          <div class="workspace-text">
            <div class="workspace-name">John Doe</div>
            <div class="workspace-role">admin</div>
          </div>
          <svg class="workspace-chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="m7 9 5-5 5 5"/><path d="m7 15 5 5 5-5"/>
          </svg>
        </div>
      </div>
    </aside>`;
}
