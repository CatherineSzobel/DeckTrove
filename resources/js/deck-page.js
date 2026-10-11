/**
 * Deck page (resources/views/decks/deck.blade.php): the shared card preview, so it opens beside a card and stays
 * inside the window (to the left for cards near the right edge).
 */
import { cardPreview } from "./deckbuilder/preview.js";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("cardPreview", cardPreview);
});
