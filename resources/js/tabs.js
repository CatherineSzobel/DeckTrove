// resources/js/tabs.js
export function initTabs(container) {
    const active = ["active", "text-blue-600"];
    const inactive = ["inactive", "text-gray-500"];

    const tabs = container.querySelectorAll(".tab-link");
    const panels = container.querySelectorAll(".tab-panel");

    if (!tabs.length || !panels.length) return;

    // Hide all panels initially
    panels.forEach((panel) => panel.classList.add("hidden"));

    // Activate first tab by default
    tabs[0].classList.add(...active);
    tabs[0].setAttribute("aria-selected", "true");
    panels[0].classList.remove("hidden");

    tabs.forEach((tab) => {
        tab.addEventListener("click", () => {
            // Deactivate all tabs
            tabs.forEach((t) => {
                t.classList.remove(...active);
                t.classList.add(...inactive);
                t.setAttribute("aria-selected", "false");
            });

            // Hide all panels
            panels.forEach((panel) => panel.classList.add("hidden"));

            // Activate clicked tab
            tab.classList.remove(...inactive);
            tab.classList.add(...active);
            tab.setAttribute("aria-selected", "true");

            // Show target panel
            const target = container.querySelector(`#${tab.dataset.tabTarget}`);
            if (target) target.classList.remove("hidden");
        });
    });
}

// Initialize all tab containers on DOMContentLoaded
export function initAllTabs() {
    document.querySelectorAll(".tab-container").forEach(initTabs);
}
