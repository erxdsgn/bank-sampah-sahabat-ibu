import { escapeHtml } from './helpers';

export function initComboSelect(selectId) {
    const select = document.getElementById(selectId);
    const input  = document.getElementById('cari_' + selectId);
    const list   = document.getElementById('list_' + selectId);
    if (!select || !input || !list) return;

    input.readOnly = true;
    let aktif = -1;

    const teks = (o) => o.textContent.replace(/\s+/g, ' ').trim();
    const sync = () => {
        const o = select.options[select.selectedIndex];
        input.value = o ? teks(o) : '';
    };
    const buka = () => {
        list.innerHTML = Array.from(select.options).map((o) =>
            `<div class="combo-item${o.value === select.value ? ' is-selected' : ''}" data-value="${escapeHtml(o.value)}">${escapeHtml(teks(o))}</div>`
        ).join('');
        aktif = -1;
        list.classList.add('show');
        list.querySelector('.is-selected')?.scrollIntoView({ block: 'nearest' });
    };
    const tutup = () => list.classList.remove('show');
    const pilih = (value) => {
        const berubah = select.value !== String(value);
        select.value = value;
        sync();
        tutup();
        if (berubah) select.dispatchEvent(new Event('change', { bubbles: true }));
    };
    const sorot = (arah) => {
        const items = list.querySelectorAll('.combo-item');
        if (!items.length) return;
        aktif = (aktif + arah + items.length) % items.length;
        items.forEach((el, i) => el.classList.toggle('is-active', i === aktif));
        items[aktif].scrollIntoView({ block: 'nearest' });
    };

    input.addEventListener('focus', buka);
    input.addEventListener('click', () => { if (!list.classList.contains('show')) buka(); });
    input.addEventListener('blur', tutup);

    input.addEventListener('keydown', (e) => {
        const items = list.querySelectorAll('.combo-item');
        if (e.key === 'ArrowDown') { e.preventDefault(); if (!list.classList.contains('show')) buka(); sorot(1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); sorot(-1); }
        else if (e.key === 'Enter' && list.classList.contains('show') && aktif > -1 && items[aktif]) {
            e.preventDefault();
            pilih(items[aktif].dataset.value);
        }
        else if (e.key === 'Escape' && list.classList.contains('show')) { e.stopPropagation(); tutup(); }
    });

    list.addEventListener('mousedown', (e) => {
        e.preventDefault();
        const item = e.target.closest('.combo-item');
        if (item) pilih(item.dataset.value);
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest(`#combo_${selectId}`)) tutup();
    });

    sync();
}
