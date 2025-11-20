document.addEventListener("DOMContentLoaded", () => {
    const returnButton = document.querySelector(".return_button");
    if (returnButton) {
        returnButton.addEventListener("click", (e) => {
            e.preventDefault();
            const selectedSeries =
                localStorage.getItem("selectedSeries") || "yugioh";
            window.location.href = `/${selectedSeries}/cards`;
        });
    }
    const tooltip = document.getElementById("global-tooltip");

    document.querySelectorAll(".card-with-tooltip").forEach((card) => {
        const desc = card.dataset.desc;

        card.addEventListener("mouseenter", () => {
            // CREATE TOOLTIP BOX SAFELY
            const box = document.createElement("div");
            box.className =
                "bg-black bg-opacity-90 text-white text-xs p-2 rounded max-w-xs shadow-lg";
            box.textContent = desc;

            tooltip.innerHTML = "";
            tooltip.appendChild(box);
            tooltip.style.opacity = "1";

            const rect = card.getBoundingClientRect();
            const tRect = box.getBoundingClientRect();

            // Default position → right of card
            let top = rect.top + window.scrollY;
            let left = rect.right + 8 + window.scrollX;

            // If overflow right → flip left
            if (left + tRect.width > window.innerWidth) {
                left = rect.left - tRect.width - 8 + window.scrollX;
            }

            // If overflow bottom → bump up
            if (top + tRect.height > window.innerHeight + window.scrollY) {
                top = window.innerHeight + window.scrollY - tRect.height - 8;
            }

            tooltip.style.top = `${top}px`;
            tooltip.style.left = `${left}px`;
        });

        card.addEventListener("mouseleave", () => {
            tooltip.style.opacity = "0";
        });
    });
});
