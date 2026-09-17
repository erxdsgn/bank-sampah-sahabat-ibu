/**
 * theme.js
 * ---------------------------------------------------------
 * Toggle tema light/dark. Preferensi disimpan di localStorage
 * dengan key "dash26-theme" supaya tetap dark/light saat reload.
 */
const ICON_SUN =
  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>';

const ICON_MOON =
  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>';

export function initThemeToggle() {
  const html = document.documentElement;
  const toggleBtn = document.getElementById("themeToggle");
  if (!toggleBtn) return;

  function syncIcon() {
    const isDark = html.getAttribute("data-theme") === "dark";
    toggleBtn.innerHTML = isDark ? ICON_SUN : ICON_MOON;
  }

  syncIcon();

  toggleBtn.addEventListener("click", () => {
    const next = html.getAttribute("data-theme") === "dark" ? "light" : "dark";
    html.setAttribute("data-theme", next);
    try {
      localStorage.setItem("dash26-theme", next);
    } catch {
      // localStorage bisa diblokir (mode privat dll), abaikan saja
    }
    syncIcon();
  });
}
