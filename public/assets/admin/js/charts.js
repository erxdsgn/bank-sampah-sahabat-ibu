/**
 * charts.js
 * ---------------------------------------------------------
 * Semua konfigurasi Chart.js. Chart dipasang otomatis ke
 * setiap <canvas data-chart-key="..."> yang ditemukan di halaman.
 *
 * npm install chart.js
 */
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

/** Ambil warna dari CSS custom properties (biar ngikut tema light/dark) */
function getThemeColors() {
  const style = getComputedStyle(document.documentElement);
  const v = (name) => style.getPropertyValue(name).trim();

  return {
    primary: v("--primary"),
    success: v("--success"),
    danger: v("--danger"),
    warning: v("--warning"),
    info: v("--info"),
    purple: v("--purple"),
    pink: v("--pink"),
    orange: v("--orange"),
    teal: v("--teal"),
    text: v("--t-base"),
    muted: v("--t-muted"),
    light: v("--t-light"),
    border: v("--border"),
    soft: v("--border-soft"),
    bg: v("--bg-card"),
  };
}

/**
 * Kumpulan factory config chart, keyed sesuai data-chart-key di HTML.
 * Tiap fungsi menerima palet warna tema dan mengembalikan config Chart.js.
 */
const chartConfigs = {
  "revenue-line": (c) => ({
    type: "line",
    data: {
      labels: [
        "Jan", "Feb", "Mar", "Apr", "May", "Jun",
        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
      ],
      datasets: [
        {
          label: "2026",
          data: [42, 56, 50, 78, 88, 96, 110, 124, 118, 142, 158, 184],
          borderColor: c.primary,
          backgroundColor: `${c.primary}20`,
          tension: 0.35,
          fill: true,
          pointRadius: 0,
          pointHoverRadius: 5,
          borderWidth: 2.5,
        },
        {
          label: "2025",
          data: [38, 44, 46, 60, 70, 74, 82, 90, 92, 102, 110, 118],
          borderColor: c.muted,
          backgroundColor: "transparent",
          tension: 0.35,
          fill: false,
          pointRadius: 0,
          pointHoverRadius: 5,
          borderWidth: 2,
          borderDash: [4, 4],
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { grid: { color: c.soft, drawBorder: false }, ticks: { color: c.light } },
        x: { grid: { display: false }, ticks: { color: c.light } },
      },
    },
  }),

  "channels-bar": (c) => ({
    type: "bar",
    data: {
      labels: ["Direct", "Search", "Social", "Email", "Affiliate", "Display", "Other"],
      datasets: [
        {
          label: "Visitors",
          data: [124, 88, 72, 54, 36, 28, 18],
          backgroundColor: [c.primary, c.success, c.purple, c.info, c.warning, c.pink, c.muted],
          borderRadius: 6,
          borderSkipped: false,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { grid: { color: c.soft, drawBorder: false }, ticks: { color: c.light } },
        x: { grid: { display: false }, ticks: { color: c.muted } },
      },
    },
  }),

  "devices-doughnut": (c) => ({
    type: "doughnut",
    data: {
      labels: ["Desktop", "Mobile", "Tablet"],
      datasets: [
        {
          data: [62, 30, 8],
          backgroundColor: [c.primary, c.purple, c.info],
          borderColor: c.bg,
          borderWidth: 3,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: "68%",
      plugins: { legend: { position: "right" } },
    },
  }),

  "sources-radar": (c) => ({
    type: "radar",
    data: {
      labels: ["Speed", "UX", "Reliability", "Pricing", "Support", "Features"],
      datasets: [
        {
          label: "Adminator",
          data: [85, 92, 88, 76, 80, 95],
          borderColor: c.primary,
          backgroundColor: `${c.primary}30`,
          pointBackgroundColor: c.primary,
        },
        {
          label: "Competitor",
          data: [70, 65, 75, 82, 60, 70],
          borderColor: c.muted,
          backgroundColor: `${c.muted}20`,
          pointBackgroundColor: c.muted,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        r: {
          angleLines: { color: c.soft },
          grid: { color: c.soft },
          pointLabels: { color: c.muted, font: { size: 11 } },
          ticks: { display: false },
        },
      },
    },
  }),

  "mrr-stacked": (c) => ({
    type: "bar",
    data: {
      labels: ["Q1", "Q2", "Q3", "Q4"],
      datasets: [
        { label: "Starter", data: [12, 18, 22, 28], backgroundColor: c.info, borderRadius: 4, stack: "a" },
        { label: "Pro", data: [38, 48, 56, 64], backgroundColor: c.primary, borderRadius: 4, stack: "a" },
        { label: "Team", data: [22, 28, 36, 44], backgroundColor: c.purple, borderRadius: 4, stack: "a" },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { stacked: true, grid: { color: c.soft, drawBorder: false }, ticks: { color: c.light } },
        x: { stacked: true, grid: { display: false }, ticks: { color: c.muted } },
      },
    },
  }),

  "dashboard-monthly": (c) => ({
    type: "line",
    data: {
      labels: [
        "Jan", "Feb", "Mar", "Apr", "May", "Jun",
        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
      ],
      datasets: [
        {
          label: "Revenue",
          data: [42, 38, 56, 50, 78, 70, 96, 88, 118, 102, 144, 168],
          borderColor: c.primary,
          backgroundColor: `${c.primary}24`,
          tension: 0.4,
          fill: true,
          pointRadius: 0,
          pointHoverRadius: 5,
          pointHoverBackgroundColor: c.primary,
          pointHoverBorderColor: c.bg,
          pointHoverBorderWidth: 3,
          borderWidth: 2.5,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { grid: { color: c.soft, drawBorder: false }, ticks: { color: c.light, maxTicksLimit: 4 } },
        x: { grid: { display: false }, ticks: { color: c.light, font: { size: 10 } } },
      },
    },
  }),

  "sessions-area": (c) => ({
    type: "line",
    data: {
      labels: Array.from({ length: 30 }, (_, i) => `${i + 1}`),
      datasets: [
        {
          label: "Sessions",
          data: [
            120, 132, 110, 145, 162, 158, 175, 188, 172, 195,
            210, 224, 218, 240, 256, 248, 272, 290, 282, 308,
            322, 318, 340, 358, 352, 376, 392, 388, 410, 432,
          ],
          borderColor: c.success,
          backgroundColor: `${c.success}24`,
          tension: 0.4,
          fill: true,
          pointRadius: 0,
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { grid: { color: c.soft, drawBorder: false }, ticks: { color: c.light } },
        x: { grid: { display: false }, ticks: { color: c.light, maxTicksLimit: 6 } },
      },
    },
  }),
};

/** Simpan instance chart per elemen canvas, supaya bisa di-destroy sebelum re-render (mis. ganti tema) */
const chartInstances = new Map();

/** Set default global Chart.js (font, warna, legend, tooltip) sesuai tema aktif */
function applyChartDefaults(colors) {
  Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
  Chart.defaults.font.size = 12;
  Chart.defaults.color = colors.muted;
  Chart.defaults.borderColor = colors.soft;
  Chart.defaults.plugins.legend.position = "bottom";
  Chart.defaults.plugins.legend.labels.usePointStyle = true;
  Chart.defaults.plugins.legend.labels.padding = 16;
  Chart.defaults.plugins.legend.labels.boxWidth = 8;
  Chart.defaults.plugins.legend.labels.boxHeight = 8;
  Chart.defaults.plugins.tooltip.backgroundColor = colors.text;
  Chart.defaults.plugins.tooltip.titleColor = colors.bg;
  Chart.defaults.plugins.tooltip.bodyColor = colors.bg;
  Chart.defaults.plugins.tooltip.padding = 10;
  Chart.defaults.plugins.tooltip.cornerRadius = 6;
  Chart.defaults.plugins.tooltip.displayColors = false;
}

/** Render ulang semua chart di halaman (dipanggil saat load & saat ganti tema) */
export function renderCharts() {
  const colors = getThemeColors();
  applyChartDefaults(colors);

  document.querySelectorAll("canvas[data-chart-key]").forEach((canvas) => {
    const key = canvas.getAttribute("data-chart-key");
    const configFactory = chartConfigs[key];
    if (!configFactory) return;

    const existing = chartInstances.get(canvas);
    if (existing) existing.destroy();

    chartInstances.set(canvas, new Chart(canvas, configFactory(colors)));
  });
}

/**
 * Inisialisasi chart + pasang watcher supaya chart otomatis
 * di-render ulang setiap kali atribut data-theme di <html> berubah.
 */
export function initCharts() {
  if (!document.querySelector("canvas[data-chart-key]")) return;

  renderCharts();

  new MutationObserver((mutations) => {
    if (mutations.some((m) => m.attributeName === "data-theme")) {
      renderCharts();
    }
  }).observe(document.documentElement, { attributes: true });
}
