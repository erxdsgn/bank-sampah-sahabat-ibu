/**
 * Render pagination & info untuk tabel riwayat/manapun.
 * @param {object} opts
 * @param {string} opts.infoId       - id elemen info
 * @param {string} opts.buttonId     - id container tombol
 * @param {number} opts.currentPage
 * @param {number} opts.totalPages
 * @param {(page:number)=>void} opts.onPageChange
 * @param {string} [opts.unit='data']
 */
export function renderPagination({ infoId, buttonId, currentPage, totalPages, onPageChange, totalItems, perPage, unit = 'data' }) {
    const info = document.getElementById(infoId);
    const box = document.getElementById(buttonId);

    if (info && typeof totalItems === 'number') {
        const start = totalItems === 0 ? 0 : (currentPage - 1) * perPage + 1;
        const end = Math.min(currentPage * perPage, totalItems);
        info.innerHTML = totalItems === 0
            ? 'Tidak ada data'
            : `Menampilkan <strong>${start}</strong> - <strong>${end}</strong> dari <strong>${totalItems}</strong> ${unit}`;
    }
    if (!box) return;

    let html = `<button class="btn btn--ghost" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>‹</button>`;
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += `<button class="btn ${i === currentPage ? 'btn--primary' : 'btn--ghost'}" data-page="${i}">${i}</button>`;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            html += `<span class="pagination-dots">…</span>`;
        }
    }
    html += `<button class="btn btn--ghost" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>›</button>`;
    box.innerHTML = html;

    box.onclick = (e) => {
        const btn = e.target.closest('button[data-page]');
        if (!btn || btn.disabled) return;
        onPageChange(parseInt(btn.dataset.page, 10));
    };
}
