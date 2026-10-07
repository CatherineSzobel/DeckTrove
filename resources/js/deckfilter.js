/** Public decks page: filter by game without reloading. Falls back to a normal form submit. */
const filter = document.getElementById("game-filter");
const deckGrid = document.getElementById("deck-grid");

filter?.addEventListener("change", async () => {
    const url = new URL(filter.form.action);
    if (filter.value) url.searchParams.set("game", filter.value);

    try {
        const response = await fetch(url, {
            headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        deckGrid.innerHTML = (await response.json()).html;
        history.replaceState({}, "", url);
    } catch (error) {
        console.error("Error fetching decks:", error);
        filter.form.submit();
    }
});
