/**
 * vector-map.js
 * ---------------------------------------------------------
 * Peta dunia (jsVectorMap) dengan marker kota.
 * Dipasang otomatis ke setiap elemen [data-vmap] di halaman.
 *
 * jsVectorMap di-load via <script src="..."> (UMD global),
 * bukan ESM import, karena tidak pakai bundler.
 *
 * Kalau jsVectorMap tidak ada di halaman, fungsi ini
 * langsung return tanpa error (safe untuk halaman non-peta).
 */

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

/** Simpan instance map per elemen */
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

/** Ambil constructor jsVectorMap dari global (UMD) */
function getVectorMapConstructor() {
  // Beberapa build UMD expose sebagai window.jsVectorMap,
  // sebagian lagi window.jsVectorMap.default, sebagian lagi window.VectorMap.
  const candidates = [
    window.jsVectorMap,
    window.jsVectorMap?.default,
    window.VectorMap,
    window.VectorMap?.default,
  ];
  for (const c of candidates) {
    if (typeof c === "function") return c;
  }
  return null;
}

/** Render (atau re-render) peta pada 1 elemen */
function renderMap(el) {
  const VectorMap = getVectorMapConstructor();
  if (!VectorMap) {
    console.warn(
      "[vector-map] jsVectorMap belum tersedia. " +
      "Pastikan script jsvectormap (UMD) sudah di-load di layout."
    );
    return;
  }

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

/** Render semua peta di halaman */
export function renderAllMaps() {
  document.querySelectorAll("[data-vmap]").forEach(renderMap);
}

/**
 * Inisialisasi peta + watcher tema.
 * Kalau tidak ada [data-vmap] di halaman, langsung return (aman).
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
