/**
 * Behaviour shared by every page: navigation menus, the showcase banner and small form helpers.
 */

// Let Vite process the images referenced with Vite::asset() in Blade, so they exist in production builds.
import.meta.glob(["../img/**"]);

function initSeriesSelect() {
    // Each option's value is the URL of that series' card database.
    document.querySelectorAll("[data-series-select]").forEach((select) => {
        select.addEventListener("change", () => {
            if (select.value) window.location.href = select.value;
        });
    });
}

function initToggle(button, panel, { closeOnOutsideClick = false } = {}) {
    if (!button || !panel) return;

    const setOpen = (open) => {
        panel.classList.toggle("hidden", !open);
        button.setAttribute("aria-expanded", String(open));
    };

    button.addEventListener("click", (event) => {
        event.stopPropagation();
        setOpen(panel.classList.contains("hidden"));
    });

    if (closeOnOutsideClick) {
        document.addEventListener("click", (event) => {
            if (!panel.contains(event.target)) setOpen(false);
        });
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") setOpen(false);
        });
    }
}

function initShowcaseToggle() {
    const button = document.getElementById("showcase_button");
    const details = document.getElementById("showcaseDetails");
    if (!button || !details) return;

    button.addEventListener("click", () => {
        const open = button.getAttribute("aria-expanded") !== "true";
        button.setAttribute("aria-expanded", String(open));
        details.style.maxHeight = open ? `${details.scrollHeight}px` : "0px";
        details.classList.toggle("opacity-0", !open);
    });
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

document.addEventListener("DOMContentLoaded", () => {
    initSeriesSelect();
    initToggle(document.getElementById("userDropdown-btn"), document.getElementById("userDropdown-menu"), { closeOnOutsideClick: true });
    initToggle(document.getElementById("mobileMenuButton"), document.getElementById("mobileMenu"));
    initShowcaseToggle();
    initFormHelpers();
});
