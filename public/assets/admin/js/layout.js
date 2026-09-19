import { renderSidebar } from "./sidebar.js";
import { renderTopbar } from "./topbar.js";
import { renderFooter } from "./footer.js";
import { initUIInteractions } from "./ui-interactions.js";

export function mountLayout() {
    const body = document.body;
    const activeKey = body.getAttribute("data-active") || "";
    const crumbs = body.getAttribute("data-crumbs") || "";

    const sidebarEl = document.querySelector("[data-shell-sidebar]");
    const topbarEl = document.querySelector("[data-shell-topbar]");
    const footerEl = document.querySelector("[data-shell-footer]");

    if (sidebarEl) sidebarEl.outerHTML = renderSidebar(activeKey);
    if (topbarEl) topbarEl.outerHTML = renderTopbar(crumbs);
    if (footerEl) footerEl.outerHTML = renderFooter();
}

document.addEventListener("DOMContentLoaded", () => {
    mountLayout();
    initUIInteractions();
});
