document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("deckSearch");
    const seriesFilter = document.getElementById("seriesFilter");
    const cards = [...document.querySelectorAll(".deck-card")];

    const applyFilters = () => {
        const query = searchInput.value.trim().toLowerCase();
        const selectedSeries = seriesFilter.value;

        cards.forEach((card) => {
            const title =
                card.querySelector("h3")?.textContent.toLowerCase() || "";
            const desc =
                card.querySelector("p")?.textContent.toLowerCase() || "";
            const series = card.dataset.series?.toLowerCase() || "";

            const matchesSearch =
                !query || title.includes(query) || desc.includes(query);

            const matchesSeries =
                selectedSeries === "all" || series === selectedSeries;

            card.style.display = matchesSearch && matchesSeries ? "" : "none";
        });
    };

    searchInput?.addEventListener("input", applyFilters);
    seriesFilter?.addEventListener("change", applyFilters);
});
