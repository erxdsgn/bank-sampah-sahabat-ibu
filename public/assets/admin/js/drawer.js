/**
 * drawer.js
 * ---------------------------------------------------------
 * Logic buka/tutup sidebar sebagai drawer di mobile.
 * - Klik tombol hamburger [data-drawer-open] -> buka drawer
 * - Klik backdrop / klik link menu / tekan Escape -> tutup drawer
 */
export function initDrawer() {
  const body = document.body;
  if (!body) return;

  function closeDrawer() {
    body.classList.remove("has-drawer-open");
  }

  // Buat backdrop kalau belum ada
  if (!document.querySelector(".drawer-backdrop")) {
    const backdrop = document.createElement("div");
    backdrop.className = "drawer-backdrop";
    backdrop.setAttribute("aria-hidden", "true");
    body.appendChild(backdrop);
    backdrop.addEventListener("click", closeDrawer);
  }

  document.addEventListener("click", (e) => {
    if (e.target.closest("[data-drawer-open]")) {
      e.preventDefault();
      body.classList.add("has-drawer-open");
      return;
    }

    // Tutup drawer otomatis kalau user klik link menu di sidebar (mobile)
    const clickedNavLink = e.target.closest(
      '.d-sidebar a[href]:not([href^="#"]):not([href="javascript:void(0)"])'
    );
    if (body.classList.contains("has-drawer-open") && clickedNavLink) {
      closeDrawer();
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && body.classList.contains("has-drawer-open")) {
      closeDrawer();
    }
  });
}
