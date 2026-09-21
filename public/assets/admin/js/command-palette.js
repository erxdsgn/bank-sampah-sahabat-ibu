/**
 * command-palette.js
 * ⌘K / Ctrl+K search palette — extracted from 2026.js
 *
 * This module was NOT present as a separate file yet (only the bundled,
 * minified version existed inside 2026.js). All other logic in that bundle
 * already maps to existing files (navigation.js, topbar.js, layout.js,
 * drawer.js, theme.js, ui-interactions.js, charts.js, vector-map.js,
 * calendar.js, runtime.js) and was left untouched.
 *
 * Assumptions (adjust to match your actual module setup):
 *  - `NAV_SECTIONS` is the navigation data array defined in navigation.js
 *    (the `n` array in the bundle). Exposed globally or imported — update
 *    the reference below if your setup differs.
 *  - This file exposes `initCommandPalette()`, meant to be called once
 *    from runtime.js during app bootstrap (where `O()` used to call it
 *    inline in the bundle).
 */

(function (global) {
  let backdropEl = null;
  let modalEl = null;
  let inputEl = null;
  let resultsEl = null;

  let allEntries = [];
  let filteredResults = [];
  let selectedIndex = 0;

  // --- scoring / filtering -------------------------------------------------

  function scoreEntry(entry, query) {
    if (!query) return 1;
    const q = query.toLowerCase();
    const label = entry.label.toLowerCase();
    if (label === q) return 100;
    if (label.startsWith(q)) return 50;
    if (label.includes(q)) return 20;
    if (entry.section.toLowerCase().includes(q)) return 5;
    return 0;
  }

  function renderResults() {
    resultsEl.innerHTML =
      filteredResults.length === 0
        ? '<div class="palette-empty">No results</div>'
        : filteredResults
            .map(
              (entry, i) => `
      <div class="palette-result${i === selectedIndex ? ' is-selected' : ''}" role="option" data-index="${i}" aria-selected="${i === selectedIndex}">
        <span class="palette-result-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">${entry.icon || ''}</svg></span>
        <span class="palette-result-label">${entry.label}</span>
        <span class="palette-result-section">${entry.section}</span>
      </div>
    `
            )
            .join('');
  }

  function filterEntries(query) {
    filteredResults = allEntries
      .map((entry) => ({ entry, score: scoreEntry(entry, query) }))
      .filter(({ score }) => score > 0)
      .sort((a, b) => b.score - a.score)
      .slice(0, 12)
      .map(({ entry }) => entry);
    selectedIndex = 0;
    renderResults();
  }

  function scrollSelectedIntoView() {
    const el = resultsEl.querySelector('.palette-result.is-selected');
    if (el && typeof el.scrollIntoView === 'function') {
      el.scrollIntoView({ block: 'nearest' });
    }
  }

  // --- selection / navigation ----------------------------------------------

  function selectEntry(entry) {
    if (!entry) return;
    closePalette();
    if (entry.kind === 'action' && typeof entry.action === 'function') {
      entry.action();
    } else if (entry.href) {
      if (entry.target === '_blank') {
        window.open(entry.href, '_blank', 'noopener');
      } else {
        window.location.href = entry.href;
      }
    }
  }

  // --- building the searchable index ---------------------------------------

  function buildEntries() {
    const entries = [];
    const navSections = global.NAV_SECTIONS || [];

    for (const section of navSections) {
      for (const item of section.items) {
        if (item.children) {
          for (const child of item.children) {
            entries.push({
              kind: 'page',
              label: child.text,
              section: `${section.label} › ${item.text}`,
              href: child.href,
              icon: item.icon,
            });
          }
        } else if (item.href && item.href !== '#') {
          entries.push({
            kind: 'page',
            label: item.text,
            section: section.label,
            href: item.href,
            icon: item.icon,
          });
        }
      }
    }

    entries.push({
      kind: 'action',
      label: 'Toggle theme (light / dark)',
      section: 'Action',
      action: () => {
        const html = document.documentElement;
        const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        try {
          localStorage.setItem('dash26-theme', next);
        } catch (e) {}
        const toggleBtn = document.getElementById('themeToggle');
        if (toggleBtn) toggleBtn.click();
      },
      icon: '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>',
    });

    entries.push({
      kind: 'link',
      label: 'View on GitHub',
      section: 'External',
      href: 'https://github.com/puikinsh/Adminator-admin-dashboard',
      target: '_blank',
      icon: '<path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/>',
    });

    entries.push({
      kind: 'link',
      label: 'Documentation',
      section: 'External',
      href: 'https://puikinsh.github.io/Adminator-admin-dashboard/',
      target: '_blank',
      icon: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>',
    });

    return entries;
  }

  // --- modal lifecycle -------------------------------------------------------

  function openPalette() {
    if (modalEl && !document.contains(modalEl)) {
      // stale reference from a previous DOM wipe — rebuild
      backdropEl = null;
      modalEl = null;
      inputEl = null;
      resultsEl = null;
    }

    if (!backdropEl) {
      backdropEl = document.createElement('div');
      backdropEl.className = 'palette-backdrop';
      backdropEl.innerHTML = `
  <div class="palette-modal" role="dialog" aria-modal="true" aria-label="Command palette">
    <div class="palette-input-row">
      <svg viewBox="0 0 24 24" class="palette-icon" aria-hidden="true">
        <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/>
        <path d="m21 21-4.3-4.3" fill="none" stroke="currentColor" stroke-width="2"/>
      </svg>
      <input class="palette-input" type="text" placeholder="Search pages, actions…" autocomplete="off" spellcheck="false">
      <kbd class="palette-esc">esc</kbd>
    </div>
    <div class="palette-results" role="listbox"></div>
    <div class="palette-foot">
      <span><kbd>↑</kbd><kbd>↓</kbd> navigate</span>
      <span><kbd>↵</kbd> select</span>
      <span><kbd>esc</kbd> close</span>
    </div>
  </div>
`;
      document.body.appendChild(backdropEl);
      modalEl = backdropEl.querySelector('.palette-modal');
      inputEl = backdropEl.querySelector('.palette-input');
      resultsEl = backdropEl.querySelector('.palette-results');

      backdropEl.addEventListener('click', (e) => {
        if (e.target === backdropEl) closePalette();
      });
      inputEl.addEventListener('input', () => filterEntries(inputEl.value));
      inputEl.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          selectedIndex = Math.min(selectedIndex + 1, filteredResults.length - 1);
          renderResults();
          scrollSelectedIntoView();
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          selectedIndex = Math.max(selectedIndex - 1, 0);
          renderResults();
          scrollSelectedIntoView();
        } else if (e.key === 'Enter') {
          e.preventDefault();
          selectEntry(filteredResults[selectedIndex]);
        } else if (e.key === 'Escape') {
          e.preventDefault();
          closePalette();
        }
      });
      resultsEl.addEventListener('click', (e) => {
        const row = e.target.closest('.palette-result');
        if (row) selectEntry(filteredResults[Number(row.getAttribute('data-index'))]);
      });
    }

    if (allEntries.length === 0) {
      allEntries = buildEntries();
    }

    inputEl.value = '';
    filterEntries('');
    document.body.classList.add('has-palette-open');
    setTimeout(() => inputEl.focus(), 0);
  }

  function closePalette() {
    if (backdropEl) {
      document.body.classList.remove('has-palette-open');
    }
  }

  function isPaletteOpen() {
    return document.body.classList.contains('has-palette-open');
  }

  // --- public init -----------------------------------------------------------

  function initCommandPalette() {
    document.addEventListener('click', (e) => {
      if (e.target.closest('[data-palette-open]')) {
        e.preventDefault();
        openPalette();
      }
    });

    document.addEventListener('keydown', (e) => {
      if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        isPaletteOpen() ? closePalette() : openPalette();
        return;
      }
      if (e.key === '/' && !isPaletteOpen()) {
        const activeTag = document.activeElement && document.activeElement.tagName;
        const isEditable = document.activeElement && document.activeElement.isContentEditable;
        if (activeTag !== 'INPUT' && activeTag !== 'TEXTAREA' && activeTag !== 'SELECT' && !isEditable) {
          e.preventDefault();
          openPalette();
        }
      }
    });
  }

  global.initCommandPalette = initCommandPalette;
})(window);
