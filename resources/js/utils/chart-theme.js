export function getChartTheme() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    return {
        text:   isDark ? '#aab6c8' : '#6b7280',
        grid:   isDark ? '#2c3950' : '#e5e7eb',
        border: isDark ? '#151d2f' : '#ffffff',
    };
}

export function observeThemeChange(callback) {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((m) => {
            if (m.type === 'attributes' && m.attributeName === 'data-theme') callback();
        });
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    return observer;
}
