import { initTabs } from "./tabs.js";

function togglePrintList(id) {
    const list = document.getElementById(id);
    if (!list) return;

    list.classList.toggle("hidden");

    const btn = document.querySelector(`[data-toggle-target="${id}"]`);
    if (!btn) return;

    btn.textContent = list.classList.contains("hidden")
        ? "Show More"
        : "Show Less";
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".tab-container").forEach(initTabs);

    document.querySelectorAll("[data-toggle-target]").forEach((btn) => {
        btn.addEventListener("click", () => {
            togglePrintList(btn.dataset.toggleTarget);
        });
    });
});
