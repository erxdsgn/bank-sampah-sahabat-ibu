/**
 * layout.js
 * ---------------------------------------------------------
 * Entry point utama. Meng-import dan men-mount:
 *   - sidebar.js    -> render sidebar
 *   - topbar.js     -> render topbar + semua event
 *   - footer.js     -> render footer
 *   - ui-interactions.js, charts.js, calendar.js, vector-map.js
 * ---------------------------------------------------------
 */

import { renderSidebar, initSidebarEvents } from "./sidebar.js";
import { renderTopbar, initTopbarEvents } from "./topbar.js";
import { renderFooter } from "./footer.js";
import { initUIInteractions } from "./ui-interactions.js";
import { initCharts } from "./charts.js";
import { initCalendar } from "./calendar.js";
import { initVectorMaps } from "./vector-map.js";

export function mountLayout() {
  const body = document.body;
  const activeKey = body.getAttribute("data-active") || "";
  const crumbs = body.getAttribute("data-crumbs") || "";

  const sidebarEl = document.querySelector("[data-shell-sidebar]");
  const topbarEl = document.querySelector("[data-shell-topbar]");
  const footerEl = document.querySelector("[data-shell-footer]");

  // 1. Mount Sidebar
  if (sidebarEl) {
    sidebarEl.innerHTML = renderSidebar(activeKey);
    if (typeof initSidebarEvents === "function") {
      initSidebarEvents(sidebarEl);
    }
  }

  // 2. Mount Topbar
  if (topbarEl) {
    topbarEl.innerHTML = renderTopbar(crumbs);
    if (typeof initTopbarEvents === "function") {
      initTopbarEvents(topbarEl);
    }
  }

  // 3. Mount Footer
  if (footerEl && typeof renderFooter === "function") {
    footerEl.innerHTML = renderFooter();
  }

  // 4. Init interaksi & widget halaman
  initUIInteractions();
  initCharts();
  initCalendar();
  initVectorMaps();
}

// Menjalankan mounting saat DOM siap
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", mountLayout);
} else {
  mountLayout();
}
