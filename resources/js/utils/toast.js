import { escapeHtml } from './helpers';

export function showToast(title, text, type = 'success') {
    let wrap = document.getElementById('toastWrap');
    if (!wrap) {
        wrap = document.createElement('div');
        wrap.id = 'toastWrap';
        wrap.className = 'toast-wrap';
        document.body.appendChild(wrap);
    }
    const toast = document.createElement('div');
    toast.className = 'toast' + (type === 'error' ? ' toast--error' : type === 'info' ? ' toast--info' : '');
    toast.innerHTML = `<div class="toast-body"><p class="toast-title">${escapeHtml(title)}</p><p class="toast-text">${escapeHtml(text)}</p></div>`;
    wrap.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}
