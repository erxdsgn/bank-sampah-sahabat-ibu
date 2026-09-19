"use strict";

(self.webpackChunk = self.webpackChunk || []).push([
    [1],
    {
        939(e, t, a) {
            // =========================================================
            // SIDEBAR MENU
            // =========================================================

            const n = [
                {
                    label: "Workspace",
                    items: [
                        {
                            key: "dashboard",
                            text: "Dashboard",
                            href: "index.html",
                            icon: `
                                <path d="M3 12 12 3l9 9"/>
                                <path d="M5 10v10h14V10"/>
                            `
                        },
                        {
                            key: "pro",
                            text: "Go Pro",
                            href: "#",
                            badge: {
                                kind: "pro",
                                text: "PRO"
                            },
                            icon: `
                                <path d="M12 2 15 8l6.5 1-4.8 4.6L18 20l-6-3-6 3 1.3-6.4L2.5 9 9 8z"/>
                            `
                        }
                    ]
                },

                {
                    label: "Communications",
                    items: [
                        {
                            key: "email",
                            text: "Email",
                            href: "email.html",
                            icon: `
                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                <path d="m3 7 9 6 9-6"/>
                            `
                        },
                        {
                            key: "compose",
                            text: "Compose",
                            href: "compose.html",
                            icon: `
                                <path d="M12 20h9"/>
                                <path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4z"/>
                            `
                        },
                        {
                            key: "calendar",
                            text: "Calendar",
                            href: "calendar.html",
                            icon: `
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <path d="M16 2v4"/>
                                <path d="M8 2v4"/>
                                <path d="M3 10h18"/>
                            `
                        },
                        {
                            key: "chat",
                            text: "Chat",
                            href: "chat.html",
                            icon: `
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            `
                        }
                    ]
                },

                {
                    label: "Components",
                    items: [
                        {
                            key: "charts",
                            text: "Charts",
                            href: "charts.html",
                            badge: {
                                kind: "new",
                                text: "NEW"
                            },
                            icon: `
                                <path d="M3 20V4"/>
                                <path d="M7 20v-6"/>
                                <path d="M11 20v-10"/>
                                <path d="M15 20v-4"/>
                                <path d="M19 20V8"/>
                            `
                        },

                        {
                            key: "forms",
                            text: "Forms",
                            href: "forms.html",
                            icon: `
                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                <path d="M7 10h10"/>
                                <path d="M7 14h7"/>
                            `
                        },

                        {
                            key: "ui",
                            text: "UI Elements",
                            href: "ui.html",
                            icon: `
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M8 12h8"/>
                                <path d="M12 8v8"/>
                            `
                        },

                        {
                            key: "buttons",
                            text: "Buttons",
                            href: "buttons.html",
                            icon: `
                                <rect x="3" y="8" width="18" height="8" rx="4"/>
                            `
                        },

                        {
                            key: "tables",
                            text: "Tables",
                            icon: `
                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                <path d="M3 10h18"/>
                                <path d="M3 16h18"/>
                                <path d="M9 4v16"/>
                            `,
                            children: [
                                {
                                    key: "basic-table",
                                    text: "Basic Table",
                                    href: "basic-table.html"
                                },
                                {
                                    key: "datatable",
                                    text: "Data Table",
                                    href: "datatable.html"
                                }
                            ]
                        },

                        {
                            key: "maps",
                            text: "Maps",
                            icon: `
                                <path d="M9 20V4l6 4v16z"/>
                                <path d="M3 7l6-3v16l-6 3z"/>
                                <path d="M15 8l6-3v16l-6 3"/>
                            `,
                            children: [
                                {
                                    key: "google-maps",
                                    text: "Google Map",
                                    href: "google-maps.html"
                                },
                                {
                                    key: "vector-maps",
                                    text: "Vector Map",
                                    href: "vector-maps.html"
                                }
                            ]
                        }
                    ]
                }
            ];


            // =========================================================
            // TOPBAR
            // =========================================================

            function o(e) {
                return `
                    <header class="d-topbar">

                        <div class="crumbs">

                            <button
                                class="hamburger"
                                data-drawer-open
                                aria-label="Open navigation"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <line x1="3" y1="6" x2="21" y2="6"/>
                                    <line x1="3" y1="12" x2="21" y2="12"/>
                                    <line x1="3" y1="18" x2="21" y2="18"/>
                                </svg>
                            </button>

                            ${function (e) {
                                if (!e) return "";

                                const t = e
                                    .split("|")
                                    .map(e => e.trim())
                                    .filter(Boolean);

                                return t
                                    .map((e, a) => `
                                        ${
                                            a > 0
                                                ? `
                                                    <svg
                                                        class="sep"
                                                        width="10"
                                                        height="10"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                    >
                                                        <path d="m9 18 6-6-6-6"/>
                                                    </svg>
                                                `
                                                : ""
                                        }

                                        <span${a === t.length - 1 ? ' class="current"' : ""}>
                                            ${e}
                                        </span>
                                    `)
                                    .join("");
                            }(e)}

                        </div>


                        <div class="topbar-actions">

                            <!-- SEARCH -->
                            <button class="cmd" data-palette-open>
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                >
                                    <circle cx="11" cy="11" r="7"/>
                                    <path d="m21 21-4.3-4.3"/>
                                </svg>

                                <span>Search...</span>

                                <kbd class="kbd">⌘K</kbd>
                            </button>


                            <!-- NOTIFICATION -->
                            <div class="dd-wrap">

                                <button
                                    class="icon-btn"
                                    data-dropdown
                                    aria-label="Notifications"
                                >
                                    <svg viewBox="0 0 24 24">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                    </svg>

                                    <span class="count danger">
                                        3
                                    </span>
                                </button>

                                <div class="dd-menu" role="menu">

                                    <div class="dd-head">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                        </svg>

                                        Notifications
                                    </div>

                                    <div class="dd-list">

                                        <a class="dd-item" href="#">
                                            <div class="dd-avatar a1">
                                                JD
                                            </div>

                                            <div class="dd-body">
                                                <div class="dd-text">
                                                    <strong>John Doe</strong>
                                                    liked your <em>post</em>
                                                </div>

                                                <div class="dd-time">
                                                    5 MIN AGO
                                                </div>
                                            </div>
                                        </a>

                                    </div>

                                    <a class="dd-footer" href="#">
                                        View all notifications →
                                    </a>

                                </div>
                            </div>


                            <!-- MESSAGES -->
                            <div class="dd-wrap">

                                <button
                                    class="icon-btn"
                                    data-dropdown
                                    aria-label="Messages"
                                > 
                                    <svg viewBox="0 0 24 24">
                                        <rect
                                            x="3"
                                            y="5"
                                            width="18"
                                            height="14"
                                            rx="2"
                                        />

                                        <path d="m3 7 9 6 9-6"/>
                                    </svg>

                                    <span class="count info">
                                        3
                                    </span>
                                </button>

                            </div>


                            <!-- THEME TOGGLE -->
                            <button
                                class="icon-btn"
                                id="themeToggle"
                                aria-label="Toggle theme"
                            ></button>


                            <!-- PROFILE -->
                            <div class="dd-wrap">

                                <div
                                    class="avatar"
                                    data-dropdown
                                    tabindex="0"
                                    role="button"
                                    aria-label="Account menu"
                                >
                                    JD
                                </div>

                            </div>

                        </div>

                    </header>
                `;
            }


            // =========================================================
            // SIDEBAR + TOPBAR + FOOTER
            // =========================================================

            function s() {

                const body = document.body;

                const active =
                    body.getAttribute("data-active") || "";

                const crumbs =
                    body.getAttribute("data-crumbs") || "";

                const sidebar =
                    document.querySelector("[data-shell-sidebar]");

                const topbar =
                    document.querySelector("[data-shell-topbar]");

                const footer =
                    document.querySelector("[data-shell-footer]");


                // SIDEBAR
                if (sidebar) {

                    sidebar.outerHTML = `
                        <aside class="d-sidebar">

                            <div class="brand">

                                <div class="brand-logo">
                                    <svg
                                        viewBox="0 0 36 36"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            fill="#ffffff"
                                            d="M14.747 9.125c.527-1.426 1.736-2.573 3.317-2.573c1.643 0 2.792 1.085 3.318 2.573l6.077 16.867c.186.496.248.931.248 1.147c0 1.209-.992 2.046-2.139 2.046c-1.303 0-1.954-.682-2.264-1.611l-.931-2.915h-8.62l-.93 2.884c-.31.961-.961 1.642-2.232 1.642c-1.24 0-2.294-.93-2.294-2.17c0-.496.155-.868.217-1.023l6.233-16.867zm.34 11.256h5.891l-2.883-8.992h-.062l-2.946 8.992z"
                                        />
                                    </svg>
                                </div>

                                <div class="brand-text">

                                    <div class="brand-name">
                                        Adminator
                                    </div>

                                    <div class="brand-tag">
                                        v4.1.2 · preview
                                    </div>

                                </div>

                            </div>

                            <!-- NAVIGATION -->
                            ...
                        </aside>
                    `;
                }


                // TOPBAR
                if (topbar) {
                    topbar.outerHTML = o(crumbs);
                }


                // FOOTER
                if (footer) {

                    footer.outerHTML = `
                        <footer class="d-footer">

                            <div>
                                © 2026 · by
                                <span>
                                    <b>Tim Kecebong</b>
                                </span>
                            </div>

                            <div class="d-footer-meta">
                                <span>v4.1.2</span>
                                <span>preview build</span>
                            </div>

                        </footer>
                    `;
                }
            }


            // =========================================================
            // MOBILE DRAWER
            // =========================================================

            function r() {

                const body = document.body;

                if (!body) return;


                if (!document.querySelector(".drawer-backdrop")) {

                    const backdrop =
                        document.createElement("div");

                    backdrop.className =
                        "drawer-backdrop";

                    backdrop.setAttribute(
                        "aria-hidden",
                        "true"
                    );

                    body.appendChild(backdrop);

                    backdrop.addEventListener(
                        "click",
                        closeDrawer
                    );
                }


                document.addEventListener("click", event => {

                    if (
                        event.target.closest(
                            "[data-drawer-open]"
                        )
                    ) {

                        event.preventDefault();

                        body.classList.add(
                            "has-drawer-open"
                        );

                        return;
                    }


                    const link =
                        event.target.closest(
                            '.d-sidebar a[href]:not([href^="#"]):not([href="javascript:void(0)"])'
                        );


                    if (
                        body.classList.contains(
                            "has-drawer-open"
                        ) &&
                        link
                    ) {
                        closeDrawer();
                    }

                });


                document.addEventListener(
                    "keydown",
                    event => {

                        if (
                            event.key === "Escape" &&
                            body.classList.contains(
                                "has-drawer-open"
                            )
                        ) {
                            closeDrawer();
                        }

                    }
                );


                function closeDrawer() {

                    body.classList.remove(
                        "has-drawer-open"
                    );

                }
            }


            // =========================================================
            // THEME TOGGLE
            // =========================================================

            function initThemeToggle() {

                const html =
                    document.documentElement;

                const themeToggle =
                    document.getElementById(
                        "themeToggle"
                    );


                if (!themeToggle) return;


                function updateIcon() {

                    const isDark =
                        html.getAttribute(
                            "data-theme"
                        ) === "dark";


                    themeToggle.innerHTML = isDark

                        ? `
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="4"
                                />

                                <path d="M12 2v2"/>
                                <path d="M12 20v2"/>
                                <path d="M4.93 4.93l1.41 1.41"/>
                                <path d="M17.66 17.66l1.41 1.41"/>
                                <path d="M2 12h2"/>
                                <path d="M20 12h2"/>
                                <path d="M4.93 19.07l1.41-1.41"/>
                                <path d="M17.66 6.34l1.41-1.41"/>
                            </svg>
                        `

                        : `
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"
                                />
                            </svg>
                        `;
                }


                // Set icon initially
                updateIcon();


                // Toggle theme
                themeToggle.addEventListener(
                    "click",
                    () => {

                        const currentTheme =
                            html.getAttribute(
                                "data-theme"
                            );


                        const newTheme =
                            currentTheme === "dark"
                                ? "light"
                                : "dark";


                        html.setAttribute(
                            "data-theme",
                            newTheme
                        );


                        try {

                            localStorage.setItem(
                                "dash26-theme",
                                newTheme
                            );

                        } catch (error) {
                            // Ignore localStorage errors
                        }


                        updateIcon();

                    }
                );
            }


            // =========================================================
            // DATE
            // =========================================================

            function initHeroDate() {

                const element =
                    document.getElementById(
                        "heroDate"
                    );

                if (!element) return;


                const date =
                    new Intl.DateTimeFormat(
                        "en-US",
                        {
                            weekday: "long",
                            month: "long",
                            day: "numeric",
                            year: "numeric"
                        }
                    )
                    .format(new Date)
                    .replace(/,/g, "")
                    .split(" ");


                element.textContent =
                    `${date[0]} · ${date[1]} ${date[2]} · ${date[3]}`;
            }


            // =========================================================
            // NAVIGATION ACCORDION
            // =========================================================

            function initNavigation() {

                document
                    .querySelectorAll("[data-nav-toggle]")
                    .forEach(toggle => {

                        toggle.addEventListener(
                            "click",
                            () => {

                                const group =
                                    toggle.closest(
                                        "[data-nav-group]"
                                    );

                                if (group) {
                                    group.classList.toggle(
                                        "is-open"
                                    );
                                }

                            }
                        );

                    });
            }


            // =========================================================
            // DROPDOWN
            // =========================================================

            function initDropdowns() {

                function closeOthers(current) {

                    document
                        .querySelectorAll(
                            ".dd-wrap.is-open"
                        )
                        .forEach(dropdown => {

                            if (
                                dropdown !== current
                            ) {
                                dropdown.classList.remove(
                                    "is-open"
                                );
                            }

                        });
                }


                document
                    .querySelectorAll("[data-dropdown]")
                    .forEach(trigger => {

                        const wrapper =
                            trigger.closest(
                                ".dd-wrap"
                            );

                        if (!wrapper) return;


                        trigger.addEventListener(
                            "click",
                            event => {

                                event.stopPropagation();


                                const shouldOpen =
                                    !wrapper.classList.contains(
                                        "is-open"
                                    );


                                closeOthers(wrapper);


                                wrapper.classList.toggle(
                                    "is-open",
                                    shouldOpen
                                );

                            }
                        );

                    });


                document.addEventListener(
                    "click",
                    event => {

                        if (
                            !event.target.closest(
                                ".dd-wrap"
                            )
                        ) {
                            closeOthers();
                        }

                    }
                );


                document.addEventListener(
                    "keydown",
                    event => {

                        if (event.key === "Escape") {
                            closeOthers();
                        }

                    }
                );
            }


            // =========================================================
            // TODO CHECKBOX
            // =========================================================

            function initTodo() {

                document
                    .querySelectorAll(".todo-check")
                    .forEach(checkbox => {

                        checkbox.addEventListener(
                            "change",
                            () => {

                                const item =
                                    checkbox.closest(
                                        ".todo-item"
                                    );

                                if (item) {

                                    item.classList.toggle(
                                        "is-done",
                                        checkbox.checked
                                    );

                                }

                            }
                        );

                    });
            }


            // =========================================================
            // ACCORDION
            // =========================================================

            function initAccordion() {

                document
                    .querySelectorAll(
                        "[data-accordion-trigger]"
                    )
                    .forEach(trigger => {

                        trigger.addEventListener(
                            "click",
                            () => {

                                const accordion =
                                    trigger.closest(
                                        "[data-accordion]"
                                    );

                                if (accordion) {

                                    accordion.classList.toggle(
                                        "is-open"
                                    );

                                }

                            }
                        );

                    });
            }


            // =========================================================
            // TABS
            // =========================================================

            function initTabs() {

                document
                    .querySelectorAll(
                        "[data-tab-group]"
                    )
                    .forEach(group => {

                        const tabs =
                            group.querySelectorAll(
                                ".tab"
                            );

                        const panels =
                            group.querySelectorAll(
                                ".tab-panel"
                            );


                        tabs.forEach(tab => {

                            tab.addEventListener(
                                "click",
                                event => {

                                    event.preventDefault();


                                    const target =
                                        tab.getAttribute(
                                            "data-tab-target"
                                        );


                                    tabs.forEach(item => {

                                        item.classList.toggle(
                                            "is-active",
                                            item === tab
                                        );

                                    });


                                    panels.forEach(panel => {

                                        panel.classList.toggle(
                                            "is-active",
                                            panel.getAttribute(
                                                "data-tab-id"
                                            ) === target
                                        );

                                    });

                                }
                            );

                        });

                    });
            }


            // =========================================================
            // INITIALIZATION
            // =========================================================

            function i() {

                initThemeToggle();
                initHeroDate();
                initNavigation();
                initDropdowns();
                initTodo();
                initAccordion();
                initTabs();
                r();
            }


            // =========================================================
            // APPLICATION START
            // =========================================================

            function O() {

                s();
                i();

                // Prevent duplicate event listeners
                // ...
            }


            if (
                document.readyState ===
                "loading"
            ) {

                document.addEventListener(
                    "DOMContentLoaded",
                    O
                );

            } else {

                O();

            }
        }
    }
]);
