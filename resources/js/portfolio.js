// resources/js/portfolio.js
import { initAllTabs } from "./tabs.js";

document.addEventListener("DOMContentLoaded", () => {
    // Initialize all tab groups
    initAllTabs();

    // Modal logic (optional)
    document.querySelectorAll("[data-modal-target]").forEach((button) => {
        button.addEventListener("click", () => {
            const modal = document.getElementById(button.dataset.modalTarget);
            modal.classList.remove("hidden");
            modal.classList.add("flex");
        });
    });

    document.querySelectorAll("[data-close-modal]").forEach((el) => {
        el.addEventListener("click", () => {
            const modal = el.closest("[id]");
            modal.classList.add("hidden");
            modal.classList.remove("flex");
        });
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            document.querySelectorAll("[id]").forEach((modal) => {
                if (!modal.classList.contains("hidden")) {
                    modal.classList.add("hidden");
                    modal.classList.remove("flex");
                }
            });
        }
    });
});
