/**
 * vector-map.js
 * ---------------------------------------------------------
 * Peta dunia (jsVectorMap) dengan marker kota. Dipasang otomatis
 * ke setiap elemen [data-vmap] yang ditemukan di halaman.
 *
 * npm install jsvectormap
 * (jangan lupa import peta dunianya juga, lihat contoh di dashboard/index.js)
 */
import VectorMap from "jsvectormap";

/** Daftar kota yang ditandai di peta */
const markers = [
  { name: "Riga", coords: [56.95, 24.1] },
  { name: "New York", coords: [40.71, -74] },
  { name: "San Francisco", coords: [37.77, -122.42] },
  { name: "London", coords: [51.5, -0.12] },
  { name: "Berlin", coords: [52.52, 13.4] },
  { name: "Tokyo", coords: [35.68, 139.69] },
  { name: "Sydney", coords: [-33.86, 151.21] },
  { name: "São Paulo", coords: [-23.55, -46.63] },
  { name: "Cape Town", coords: [-33.92, 18.42] },
  { name: "Dubai", coords: [25.27, 55.3] },
];

/** Simpan instance map per elemen, supaya bisa di-destroy saat re-render (ganti tema) */
const mapInstances = new Map();

function getMapColors() {
  const style = getComputedStyle(document.documentElement);
  const v = (name) => style.getPropertyValue(name).trim();

  return {
    primary: v("--primary"),
    purple: v("--purple"),
    soft: v("--bg-muted"),
    border: v("--border"),
    text: v("--t-base"),
    bg: v("--bg-card"),
  };
}

/** Render (atau re-render) peta pada 1 elemen */
function renderMap(el) {
  const existing = mapInstances.get(el);
  if (existing) {
    try {
      existing.destroy();
    } catch {
      // ignore
    }
    el.innerHTML = "";
  }

  const colors = getMapColors();

  const instance = new VectorMap({
    selector: el,
    map: "world",
    backgroundColor: "transparent",
    zoomOnScroll: false,
    regionStyle: {
      initial: { fill: colors.soft, stroke: colors.border, strokeWidth: 0.4, fillOpacity: 1 },
      hover: { fill: colors.primary, fillOpacity: 0.5 },
    },
    markers,
    markerStyle: {
      initial: { fill: colors.primary, stroke: colors.bg, strokeWidth: 2, r: 5 },
      hover: { fill: colors.purple, stroke: colors.bg, strokeWidth: 2, r: 7 },
    },
    labels: {
      markers: { render: (marker) => marker.name },
    },
  });

  mapInstances.set(el, instance);
}

/** Render semua peta di halaman (dipanggil saat load & saat ganti tema) */
export function renderAllMaps() {
  document.querySelectorAll("[data-vmap]").forEach(renderMap);
}

/**
 * Inisialisasi peta + pasang watcher supaya peta otomatis
 * di-render ulang setiap kali atribut data-theme di <html> berubah.
 */
export function initVectorMaps() {
  if (!document.querySelector("[data-vmap]")) return;

  renderAllMaps();

  new MutationObserver((mutations) => {
    if (mutations.some((m) => m.attributeName === "data-theme")) {
      renderAllMaps();
    }
  }).observe(document.documentElement, { attributes: true });
}
