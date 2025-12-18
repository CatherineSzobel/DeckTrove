const filter = document.getElementById("game-filter");
const deckGrid = document.getElementById("deck-grid");

filter.addEventListener("change", async () => {
    const value = filter.value;

    try {
        const response = await fetch(
            `{{ route('public-deck.filter') }}?game=${value}`
        );
        const data = await response.json();
        deckGrid.innerHTML = data.html;
    } catch (error) {
        console.error("Error fetching decks:", error);
    }
});
