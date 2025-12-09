import { initTabs } from "./tabs.js"; // correct relative path

// ------- Print List Toggle -------
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

// ------- Init -------
document.addEventListener("DOMContentLoaded", () => {
    // Init Tabs
    document.querySelectorAll(".tab-container").forEach(initTabs);

    // Init Print Toggle Buttons
    document.querySelectorAll("[data-toggle-target]").forEach((btn) => {
        btn.addEventListener("click", () => {
            togglePrintList(btn.dataset.toggleTarget);
        });
    });
});
