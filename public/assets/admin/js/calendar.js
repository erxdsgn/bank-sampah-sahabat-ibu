/**
 * calendar.js
 * ---------------------------------------------------------
 * Kalender pakai FullCalendar. Dipasang ke elemen [data-fc].
 * Tombol navigasi (.cal-nav-btn), tombol "Today" (.cal-today-btn),
 * dan tab view (.cal-view-tab) dicari di dalam container ".cal-main"
 * terdekat dari elemen [data-fc].
 *
 * npm install @fullcalendar/core @fullcalendar/daygrid
 *             @fullcalendar/timegrid @fullcalendar/list
 *             @fullcalendar/interaction
 */
import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";
import interactionPlugin from "@fullcalendar/interaction";

/** Data event contoh. Ganti / fetch dari backend Laravel sesuai kebutuhan. */
const events = [
  { title: "Q2 kickoff", start: "2026-04-01T09:00", classNames: ["fc-cat-work"] },
  { title: "Design review", start: "2026-04-02T11:00", classNames: ["fc-cat-team"] },
  { title: "Lunch w/ Marcus", start: "2026-04-03T13:00", classNames: ["fc-cat-personal"] },
  { title: "🎂 Sara birthday", start: "2026-04-05", allDay: true, classNames: ["fc-cat-birthday"] },
  { title: "Standup", start: "2026-04-07T10:00", classNames: ["fc-cat-work"] },
  { title: "Brand workshop", start: "2026-04-07T14:00", classNames: ["fc-cat-team"] },
  { title: "All-hands", start: "2026-04-08T15:00", classNames: ["fc-cat-work"] },
  { title: "✈ Lisbon trip", start: "2026-04-09", end: "2026-04-13", allDay: true, classNames: ["fc-cat-travel"] },
  { title: "Investor sync", start: "2026-04-14T16:00", classNames: ["fc-cat-work"] },
  { title: "📑 Tax deadline", start: "2026-04-15", allDay: true, classNames: ["fc-cat-finance"] },
  { title: "Invoice approval", start: "2026-04-17T12:00", classNames: ["fc-cat-finance"] },
  { title: "Run with Mira", start: "2026-04-20T07:00", classNames: ["fc-cat-personal"] },
  { title: "Earth day talk", start: "2026-04-22T14:00", classNames: ["fc-cat-team"] },
  { title: "✓ Dependency merge", start: "2026-04-23", allDay: true, classNames: ["fc-cat-work"] },
  { title: "Coffee w/ Rita", start: "2026-04-24T10:00", classNames: ["fc-cat-personal"] },
  { title: "PR reviews", start: "2026-04-24T15:00", classNames: ["fc-cat-work"] },
  { title: "Run · 5K", start: "2026-04-25T07:00", classNames: ["fc-cat-personal"] },
  { title: "Dinner @ Carla's", start: "2026-04-25T20:00", classNames: ["fc-cat-personal"] },
  { title: "Sprint planning", start: "2026-04-27T10:00", classNames: ["fc-cat-work"] },
  { title: "Board review", start: "2026-04-28T14:00", classNames: ["fc-cat-work"] },
  { title: "Eng review", start: "2026-04-28T17:00", classNames: ["fc-cat-team"] },
  { title: "Anya 1:1", start: "2026-04-29T11:30", classNames: ["fc-cat-team"] },
  { title: "Newsletter goes out", start: "2026-04-30T09:00", classNames: ["fc-cat-team"] },
  { title: "Yoga", start: "2026-04-30T19:00", classNames: ["fc-cat-personal"] },
];

/** Mapping label tab -> nama view FullCalendar */
const viewMap = {
  Day: "timeGridDay",
  Week: "timeGridWeek",
  Month: "dayGridMonth",
  Agenda: "listWeek",
};

let calendarInstance = null;

/** Pasang tombol prev/next/today + tab view + label bulan berjalan */
function bindCalendarControls(rootEl) {
  const container = rootEl.closest(".cal-main") || document;
  const monthLabelEl = container.querySelector(".cal-month");

  function updateMonthLabel() {
    if (!monthLabelEl || !calendarInstance) return;
    const date = calendarInstance.getDate();
    const month = date.toLocaleString("en-US", { month: "long" });
    const year = date.getFullYear();
    monthLabelEl.innerHTML = `${month} <span class="yr">${year}</span>`;
  }

  container.querySelectorAll(".cal-nav-btn").forEach((btn, index) => {
    btn.addEventListener("click", () => {
      if (!calendarInstance) return;
      if (index === 0) calendarInstance.prev();
      if (index === 1) calendarInstance.next();
      updateMonthLabel();
    });
  });

  const todayBtn = container.querySelector(".cal-today-btn");
  if (todayBtn) {
    todayBtn.addEventListener("click", () => {
      calendarInstance.today();
      updateMonthLabel();
    });
  }

  container.querySelectorAll(".cal-view-tab").forEach((tab) => {
    tab.addEventListener("click", () => {
      const label = tab.textContent.trim();
      const viewName = viewMap[label] || "dayGridMonth";

      container
        .querySelectorAll(".cal-view-tab")
        .forEach((t) => t.classList.toggle("is-active", t === tab));

      calendarInstance.changeView(viewName);
      updateMonthLabel();
    });
  });

  setTimeout(updateMonthLabel, 0);
}

/** Inisialisasi FullCalendar pada elemen [data-fc] */
export function initCalendar() {
  const el = document.querySelector("[data-fc]");
  if (!el) return;

  if (calendarInstance) {
    try {
      calendarInstance.destroy();
    } catch {
      // ignore
    }
  }

  calendarInstance = new Calendar(el, {
    plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
    initialView: "dayGridMonth",
    initialDate: "2026-04-25",
    headerToolbar: false,
    height: "100%",
    expandRows: true,
    dayMaxEvents: 3,
    fixedWeekCount: false,
    firstDay: 0,
    nowIndicator: true,
    selectable: true,
    editable: true,
    events,
    dayHeaderFormat: { weekday: "short" },
  });

  calendarInstance.render();
  bindCalendarControls(el);

  // Re-render kalender saat tema berubah (biar warna re-computed)
  new MutationObserver((mutations) => {
    if (mutations.some((m) => m.attributeName === "data-theme") && calendarInstance) {
      calendarInstance.render();
    }
  }).observe(document.documentElement, { attributes: true });
}
