/** My decks page: client-side search and series filter. */
document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("deckSearch");
    const seriesFilter = document.getElementById("seriesFilter");
    const noMatches = document.getElementById("noDeckMatches");
    const cards = [...document.querySelectorAll(".deck-card")];

    const applyFilters = () => {
        const query = searchInput.value.trim().toLowerCase();
        const series = seriesFilter.value;
        let visible = 0;

        cards.forEach((card) => {
            const matches =
                (!query || card.dataset.search.includes(query)) &&
                (series === "all" || card.dataset.series === series);

            card.hidden = !matches;
            if (matches) visible++;
        });

        noMatches?.classList.toggle("hidden", visible > 0);
    };

    searchInput?.addEventListener("input", applyFilters);
    seriesFilter?.addEventListener("change", applyFilters);
});
