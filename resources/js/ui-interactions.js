/**
 * ui-interactions.js
 * ---------------------------------------------------------
 * Hanya berisi interaksi yang TIDAK ada di topbar.js dan sidebar.js.
 * Theme toggle & dropdown sudah di-handle topbar.js.
 * Toggle submenu sidebar di-handle sidebar.js.
 *
 * draw.js sudah dihapus dari project — drawer behavior
 * di-handle oleh topbar.js (toggleSidebar).
 */

/** Tampilkan tanggal hari ini di elemen #heroDate */
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

/** Centang item todo -> toggle is-done */
export function initTodoChecklist() {
  document.querySelectorAll(".todo-check").forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
      const item = checkbox.closest(".todo-item");
      if (item) item.classList.toggle("is-done", checkbox.checked);
    });
  });
}

/** Klik trigger accordion -> toggle is-open */
export function initAccordions() {
  document.querySelectorAll("[data-accordion-trigger]").forEach((trigger) => {
    trigger.addEventListener("click", () => {
      const wrap = trigger.closest("[data-accordion]");
      if (wrap) wrap.classList.toggle("is-open");
    });
  });
}

/** Tab group */
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

/** Jalankan semua interaksi yang TIDAK dimiliki topbar.js atau sidebar.js */
export function initUIInteractions() {
  initHeroDate();
  initTodoChecklist();
  initAccordions();
  initTabs();
}
