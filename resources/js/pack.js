document.addEventListener("DOMContentLoaded", () => {
    const nameFilter = document.getElementById("card-filter");
    const rarityFilter = document.getElementById("rarity-filter");
    const grid = document.getElementById("cards-grid");

    if (!nameFilter || !rarityFilter || !grid) return;

    function applyFilters() {
        const nameTerm = nameFilter.value.trim().toLowerCase();
        const rarityTerm = rarityFilter.value.trim().toLowerCase();
        const items = grid.querySelectorAll(".card-item");

        items.forEach((i) => {
            const name = i.getAttribute("data-name") || "";
            const rarity = i.getAttribute("data-rarity") || "";

            const matchesName = !nameTerm || name.includes(nameTerm);
            const matchesRarity = !rarityTerm || rarity === rarityTerm;

            i.style.display = matchesName && matchesRarity ? "" : "none";
        });
    }

    nameFilter.addEventListener("input", applyFilters);
    rarityFilter.addEventListener("change", applyFilters);
});
