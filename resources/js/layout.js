/**
 * Behaviour shared by every page: Alpine.js (header menus now, page components later), the theme toggle and
 * small form helpers.
 */
import Alpine from "alpinejs";

// Let Vite process the images referenced with Vite::asset() in Blade, so they exist in production builds.
import.meta.glob(["../img/**"]);

/** Light/dark mode. The initial theme is applied by an inline script in the layout's <head>. */
function initThemeToggle() {
    const root = document.documentElement;

    const update = () => {
        document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
            button.setAttribute("aria-pressed", String(root.classList.contains("dark")));
        });
    };

    document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
        button.addEventListener("click", () => {
            const dark = root.classList.toggle("dark");
            try {
                localStorage.setItem("theme", dark ? "dark" : "light");
            } catch {
                // Storage can be unavailable (e.g. private mode); the toggle still works for this page.
            }
            update();
        });
    });

    update();
}

function initFormHelpers() {
    // <form data-confirm="Are you sure?"> asks before submitting.
    document.querySelectorAll("form[data-confirm]").forEach((form) => {
        form.addEventListener("submit", (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });

    // <select data-autosubmit> submits its form when changed.
    document.querySelectorAll("select[data-autosubmit]").forEach((select) => {
        select.addEventListener("change", () => select.form?.requestSubmit());
    });
}

window.Alpine = Alpine;
Alpine.start();

document.addEventListener("DOMContentLoaded", () => {
    initThemeToggle();
    initFormHelpers();
});
