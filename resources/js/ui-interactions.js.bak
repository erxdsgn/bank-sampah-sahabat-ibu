/**
 * ui-interactions.js
 * ---------------------------------------------------------
 * Kumpulan interaksi kecil yang dipakai di berbagai halaman:
 * - tanggal di hero dashboard (#heroDate)
 * - buka/tutup submenu sidebar ([data-nav-toggle])
 * - dropdown generik (notifikasi, pesan, akun) ([data-dropdown])
 * - checklist todo (.todo-check)
 * - accordion ([data-accordion-trigger])
 * - tab group (.tab / .tab-panel)
 */
import { initThemeToggle } from "./theme.js";
import { initDrawer } from "./drawer.js";

/** Tampilkan tanggal hari ini di elemen #heroDate, format "Senin · Januari 1 · 2026" style */
export function initHeroDate() {
  const el = document.getElementById("heroDate");
  if (!el) return;

  const formatted = new Intl.DateTimeFormat("en-US", {
    weekday: "long",
    month: "long",
    day: "numeric",
    year: "numeric",
  })
    .format(new Date())
    .replace(/,/g, "")
    .split(" ");

  el.textContent = `${formatted[0]} · ${formatted[1]} ${formatted[2]} · ${formatted[3]}`;
}

/** Klik header submenu -> toggle class "is-open" pada [data-nav-group] induknya */
export function initNavToggles() {
  document.querySelectorAll("[data-nav-toggle]").forEach((toggle) => {
    toggle.addEventListener("click", () => {
      const group = toggle.closest("[data-nav-group]");
      if (group) group.classList.toggle("is-open");
    });
  });
}

/** Dropdown generik: notifikasi/pesan/akun. Klik luar atau Escape untuk menutup semua. */
export function initDropdowns() {
  function closeAllExcept(exceptWrap) {
    document.querySelectorAll(".dd-wrap.is-open").forEach((wrap) => {
      if (wrap !== exceptWrap) wrap.classList.remove("is-open");
    });
  }

  document.querySelectorAll("[data-dropdown]").forEach((trigger) => {
    const wrap = trigger.closest(".dd-wrap");
    if (!wrap) return;

    trigger.addEventListener("click", (e) => {
      e.stopPropagation();
      const willOpen = !wrap.classList.contains("is-open");
      closeAllExcept(wrap);
      wrap.classList.toggle("is-open", willOpen);
    });
  });

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".dd-wrap")) closeAllExcept(null);
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeAllExcept(null);
  });
}

/** Centang item todo -> tambah/hapus class "is-done" pada .todo-item */
export function initTodoChecklist() {
  document.querySelectorAll(".todo-check").forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
      const item = checkbox.closest(".todo-item");
      if (item) item.classList.toggle("is-done", checkbox.checked);
    });
  });
}

/** Klik trigger accordion -> toggle class "is-open" pada [data-accordion] induknya */
export function initAccordions() {
  document.querySelectorAll("[data-accordion-trigger]").forEach((trigger) => {
    trigger.addEventListener("click", () => {
      const wrap = trigger.closest("[data-accordion]");
      if (wrap) wrap.classList.toggle("is-open");
    });
  });
}

/** Tab group: klik .tab -> aktifkan .tab dan .tab-panel yang cocok data-tab-target/data-tab-id */
export function initTabs() {
  document.querySelectorAll("[data-tab-group]").forEach((group) => {
    const tabs = group.querySelectorAll(".tab");
    const panels = group.querySelectorAll(".tab-panel");

    tabs.forEach((tab) => {
      tab.addEventListener("click", (e) => {
        e.preventDefault();
        const targetId = tab.getAttribute("data-tab-target");

        tabs.forEach((t) => t.classList.toggle("is-active", t === tab));
        panels.forEach((panel) =>
          panel.classList.toggle(
            "is-active",
            panel.getAttribute("data-tab-id") === targetId
          )
        );
      });
    });
  });
}

/** Jalankan semua interaksi UI umum sekaligus */
export function initUIInteractions() {
  initThemeToggle();
  initHeroDate();
  initNavToggles();
  initDropdowns();
  initTodoChecklist();
  initAccordions();
  initTabs();
  initDrawer();
}
