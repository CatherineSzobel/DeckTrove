/**
 * My decks (resources/views/decks/mydecks.blade.php): search and the series switch filter the cards in place.
 * Alpine component: x-data="deckSearch(decks)", where decks lists each card's { game, text } in the grid's order.
 */
export function deckSearch(decks) {
    return {
        q: "",
        series: "all",

        /** Whether the card at this position in the grid matches the search and the series. */
        matches(index) {
            const deck = decks[index];
            const query = this.q.trim().toLowerCase();

            return (this.series === "all" || deck.game === this.series) && deck.text.includes(query);
        },

        /** How many cards match, for the count and the "no matches" box. */
        get shown() {
            return decks.filter((deck, index) => this.matches(index)).length;
        },
    };
}
