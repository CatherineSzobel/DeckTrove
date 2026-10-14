/**
 * Behaviour shared by every page: Alpine.js (header menus now, page components later), the theme toggle and
 * small form helpers.
 */
import Alpine from "alpinejs";
import { createConfirmStore } from "./confirm.js";
import { confirmSubmit } from "./confirm-submit.js";
import { deckSearch } from "./deck-search.js";
import { likeButton } from "./likes.js";

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
    // data-confirm="Message" data-confirm-title="Title?" data-confirm-button="Label" on a <form> or on one of its
    // submit buttons asks in the confirm dialog before submitting.
    document.addEventListener("submit", confirmSubmit((options) => window.confirmDialog(options)));

    // <select data-autosubmit> submits its form when changed.
    document.querySelectorAll("select[data-autosubmit]").forEach((select) => {
        select.addEventListener("change", () => select.form?.requestSubmit());
    });
}

// Started once every module script has run, so page scripts can register components in `alpine:init`.
window.Alpine = Alpine;
Alpine.store("confirm", createConfirmStore());
window.confirmDialog = (options) => Alpine.store("confirm").ask(options);
// Like buttons appear on deck cards in several lists and on the deck page.
Alpine.data("likeButton", likeButton);
Alpine.data("deckSearch", deckSearch);
document.addEventListener("DOMContentLoaded", () => Alpine.start());

document.addEventListener("DOMContentLoaded", () => {
    initThemeToggle();
    initFormHelpers();
});
