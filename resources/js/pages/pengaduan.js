import { initComboSelect } from '../utils/combo-select';

document.addEventListener("DOMContentLoaded", () => {
    const filterForm = document.getElementById("formFilterPengaduan");
    const searchInput = document.getElementById("searchPengaduan");
    const filterStatus = document.getElementById("filterStatus");
    const filterKategori = document.getElementById("filterKategori");
    const resetBtn = document.getElementById("btnResetPengaduan");

    let isInitializing = true;
    initComboSelect("filterStatus", { readonly: true });
    initComboSelect("filterKategori", { readonly: true });
    isInitializing = false;

    // ---------- Pencarian & filter tanpa refresh ----------
    const FILTER_FIELDS = ["q", "status", "kategori"];
    let filterRequest = null;

    function urlFilter(base) {
        const url = new URL(base || (filterForm ? filterForm.action : window.location.href), window.location.href);
        FILTER_FIELDS.forEach((name) => {
            const el = filterForm?.elements[name];
            const nilai = el ? String(el.value).trim() : "";
            if (nilai) url.searchParams.set(name, nilai);
            else url.searchParams.delete(name);
        });
        return url;
    }

    async function muatData(url) {
        if (filterRequest) filterRequest.abort();
        const ctrl = new AbortController();
        filterRequest = ctrl;

        try {
            const res = await fetch(url.toString(), {
                headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "text/html" },
                signal: ctrl.signal,
            });
            if (!res.ok) throw new Error("Gagal memuat data");

            const doc = new DOMParser().parseFromString(await res.text(), "text/html");
            const tbodyBaru = doc.querySelector(".pengaduan-page .data-table tbody");
            const pagBaru = doc.getElementById("pengaduanPagination");
            const tbody = document.querySelector(".pengaduan-page .data-table tbody");
            const pag = document.getElementById("pengaduanPagination");
            if (!tbodyBaru || !tbody) throw new Error("Tabel tidak ditemukan");

            tbody.innerHTML = tbodyBaru.innerHTML;
            if (pag) pag.innerHTML = pagBaru ? pagBaru.innerHTML : "";

            window.history.replaceState({}, "", url.toString());
        } catch (err) {
            if (err.name === "AbortError") return;
            window.location.href = url.toString(); // fallback: muat ulang biasa
        } finally {
            if (filterRequest === ctrl) filterRequest = null;
        }
    }

    function submitFilter() {
        if (isInitializing || !filterForm) return;
        muatData(urlFilter());
    }

    if (filterForm) {
        // Enter: cari langsung tanpa refresh
        filterForm.addEventListener("submit", (event) => {
            event.preventDefault();
            submitFilter();
        });
    }

    if (filterStatus) {
        filterStatus.addEventListener("change", submitFilter);
    }

    if (filterKategori) {
        filterKategori.addEventListener("change", submitFilter);
    }

    if (searchInput) {
        searchInput.addEventListener("input", submitFilter);
    }

    // Pindah halaman tanpa refresh (filter yang aktif ikut terbawa)
    document.addEventListener("click", (event) => {
        const link = event.target.closest("#pengaduanPagination a[href]");
        if (!link) return;
        event.preventDefault();
        muatData(urlFilter(link.href));
    });

    if (resetBtn) {
        resetBtn.addEventListener("click", () => {
            if (searchInput) searchInput.value = "";

            [["filterStatus", "cari_filterStatus"], ["filterKategori", "cari_filterKategori"]].forEach(([selId, inpId]) => {
                const sel = document.getElementById(selId);
                const inp = document.getElementById(inpId);
                if (!sel) return;
                sel.value = "";
                if (inp) inp.value = sel.options[sel.selectedIndex]?.textContent.trim() || "";
            });

            submitFilter();
        });
    }

    // Modal detail & tanggapan
    const modal = document.getElementById("modalPengaduan");
    const form = document.getElementById("formTanggapanPengaduan");
    const closeBtn = document.getElementById("closeModalPengaduan");
    const cancelBtn = document.getElementById("cancelModalPengaduan");

    if (!modal || !form) return;

    const kategoriEl = document.getElementById("mpKategori");
    const judulEl = document.getElementById("mpJudul");
    const metaEl = document.getElementById("mpMeta");
    const deskripsiEl = document.getElementById("mpDeskripsi");
    const fotoWrapEl = document.getElementById("mpFotoWrap");
    const fotoImgEl = document.getElementById("mpFoto");
    const statusEl = document.getElementById("mpStatus");
    const statusInputEl = document.getElementById("cari_mpStatus");
    const statusComboEl = document.getElementById("combo_mpStatus");
    const tanggapanEl = document.getElementById("mpTanggapan");
    const btnSimpanEl = document.getElementById("btnSimpanPengaduan");
    const lockNoticeEl = document.getElementById("mpLockNotice");

    let modalComboInitialized = false;
    let lastFocusedTrigger = null;
    let isCurrentLocked = false;

    function setStatusValue(value) {
        if (!statusEl) return;
        statusEl.value = value;
        if (statusInputEl) {
            const matchedOption = Array.from(statusEl.options).find(
                (opt) => opt.value === value
            );
            statusInputEl.value = matchedOption ? matchedOption.textContent.trim() : value;
        }
    }

    function setModalReadOnly(locked) {
        isCurrentLocked = locked;

        if (statusEl) statusEl.disabled = locked;
        if (statusInputEl) {
            statusInputEl.disabled = locked;
            statusInputEl.style.pointerEvents = locked ? "none" : "";
            statusInputEl.style.opacity = locked ? "0.7" : "";
        }
        if (statusComboEl) {
            statusComboEl.style.pointerEvents = locked ? "none" : "";
        }
        if (tanggapanEl) {
            tanggapanEl.readOnly = locked;
            tanggapanEl.style.opacity = locked ? "0.8" : "";
        }
        if (btnSimpanEl) {
            btnSimpanEl.classList.toggle("is-hidden", locked);
        }
        if (lockNoticeEl) {
            lockNoticeEl.classList.toggle("is-hidden", !locked);
        }
        if (cancelBtn) {
            cancelBtn.textContent = locked ? "Tutup" : "Batal";
        }
    }

    function openModal(trigger) {
        const actionUrl = trigger.getAttribute("data-action-url") || "";
        const kategori = trigger.getAttribute("data-kategori") || "Lainnya";
        const judul = trigger.getAttribute("data-judul") || "-";
        const nama = trigger.getAttribute("data-nama") || "Warga";
        const nik = trigger.getAttribute("data-nik") || "-";
        const tanggal = trigger.getAttribute("data-tanggal") || "-";
        const deskripsi = trigger.getAttribute("data-deskripsi") || "-";
        const fotoUrl = trigger.getAttribute("data-foto-url") || "";
        const status = trigger.getAttribute("data-status") || "menunggu";
        const tanggapan = trigger.getAttribute("data-tanggapan") || "";

        lastFocusedTrigger = trigger;
        form.setAttribute("action", actionUrl);

        if (kategoriEl) kategoriEl.textContent = kategori;
        if (judulEl) judulEl.textContent = judul;
        if (metaEl) metaEl.textContent = `${nama} (NIK: ${nik}) • ${tanggal}`;
        if (deskripsiEl) deskripsiEl.textContent = deskripsi;
        if (tanggapanEl) tanggapanEl.value = tanggapan;

        if (fotoWrapEl && fotoImgEl) {
            if (fotoUrl) {
                fotoImgEl.src = fotoUrl;
                fotoWrapEl.classList.remove("is-hidden");
            } else {
                fotoImgEl.removeAttribute("src");
                fotoWrapEl.classList.add("is-hidden");
            }
        }

        modal.classList.add("is-open");
        modal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";

        if (!modalComboInitialized) {
            initComboSelect("mpStatus", { readonly: true });
            modalComboInitialized = true;
        }
        setStatusValue(status);

        const isFinalStatus = status === "selesai" || status === "ditolak";
        setModalReadOnly(isFinalStatus);
    }

    function closeModal() {
        modal.classList.remove("is-open");
        modal.setAttribute("aria-hidden", "true");
        document.body.style.overflow = "";

        if (lastFocusedTrigger && typeof lastFocusedTrigger.focus === "function") {
            lastFocusedTrigger.focus();
            lastFocusedTrigger = null;
        }
    }

    form.addEventListener("submit", (event) => {
        if (isCurrentLocked) {
            event.preventDefault();
        }
    });

    document.addEventListener("click", (event) => {
        const trigger = event.target.closest("[data-open-pengaduan]");
        if (trigger) {
            event.preventDefault();
            openModal(trigger);
            return;
        }

        if (event.target === modal) {
            closeModal();
        }
    });

    if (closeBtn) closeBtn.addEventListener("click", closeModal);
    if (cancelBtn) cancelBtn.addEventListener("click", closeModal);

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && modal.classList.contains("is-open")) {
            closeModal();
        }
    });
});
