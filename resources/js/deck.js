/** Collapsible zones on the deck detail page. */
document.querySelectorAll("[data-zone-toggle]").forEach((button) => {
    button.addEventListener("click", () => {
        const grid = document.getElementById(`${button.dataset.zoneToggle}-cards`);
        if (!grid) return;

        const collapsed = grid.classList.toggle("hidden");
        button.setAttribute("aria-expanded", String(!collapsed));

        const icon = button.querySelector("[data-toggle-icon]");
        if (icon) icon.textContent = collapsed ? "+" : "−";
    });
});
