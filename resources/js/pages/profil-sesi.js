import { escapeHtml } from '../utils/helpers';

const pageData = window.__PROFIL_SESI_DATA__ || {};

function showToast(title, text, type = 'success') {
    const wrap = document.getElementById('toastWrap');
    if (!wrap) return;

    const toast = document.createElement('div');
    toast.className = 'toast' + (type === 'error' ? ' toast--error' : '');

    const iconPath = type === 'error'
        ? '<path d="M18 6 6 18M6 6l12 12"></path>'
        : '<path d="M20 6 9 17l-5-5"></path>';

    toast.innerHTML = `
        <div class="toast-icon">
            <svg viewBox="0 0 24 24">
                ${iconPath}
            </svg>
        </div>

        <div class="toast-body">
            <p class="toast-title">${escapeHtml(title)}</p>
            <p class="toast-text">${escapeHtml(text)}</p>
        </div>

        <button
            class="toast-close"
            type="button"
            aria-label="Tutup"
        >
            &times;
        </button>
    `;

    function hapusToast() {
        toast.classList.add('toast--leaving');
        setTimeout(function() {
            toast.remove();
        }, 180);
    }

    const closeButton = toast.querySelector('.toast-close');
    if (closeButton) {
        closeButton.addEventListener('click', hapusToast);
    }

    wrap.appendChild(toast);
    setTimeout(hapusToast, 3500);
}

document.addEventListener('DOMContentLoaded', function() {
    if (pageData.flashSuccess) {
        showToast('Berhasil', pageData.flashSuccess);
    }

    if (pageData.flashError) {
        showToast('Gagal', pageData.flashError, 'error');
    }

    if (pageData.firstError) {
        showToast('Gagal', pageData.firstError, 'error');
    }

    document
        .querySelectorAll('.profil-page .form-logout-device')
        .forEach(function(form) {
            form.addEventListener('submit', function(event) {
                const yakin = confirm(
                    'Apakah Anda yakin ingin mengeluarkan perangkat ini?'
                );

                if (!yakin) {
                    event.preventDefault();
                }
            });
        });
});
