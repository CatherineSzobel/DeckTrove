document.addEventListener("DOMContentLoaded", () => {
    // Modal Logic
    document.querySelectorAll("[data-modal-target]").forEach((button) => {
        button.addEventListener("click", () => {
            const modalId = button.getAttribute("data-modal-target");
            const modal = document.getElementById(modalId);
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

    // Tabs Logic
    const active = ["active", "text-blue-600"];
    const inactive = ["active", "text-blue-600"];

    function initTabs(container) {
        const tabs = container.querySelectorAll(".tab-link");
        const panels = container.querySelectorAll(".tab-panel");

        // Default tab
        tabs[0].classList.add(...active);
        tabs[0].setAttribute("aria-selected", "true");
        panels[0].classList.remove("hidden");

        tabs.forEach((tab) => {
            tab.addEventListener("click", () => {
                tabs.forEach((t) => {
                    t.classList.remove(...inactive);
                    t.setAttribute("aria-selected", "false");
                });

                panels.forEach((panel) => panel.classList.add("hidden"));

                tab.classList.add(...active);
                tab.setAttribute("aria-selected", "true");

                const target = container.querySelector(
                    `#${tab.dataset.tabTarget}`
                );
                target.classList.remove("hidden");
            });
        });
    }

    // Apply to all tab groups
    document.querySelectorAll(".tab-container").forEach(initTabs);
});
