/**
 * Collection and wishlist pages (resources/views/collection/index.blade.php): the cards' − n + change in place; a card
 * joining or leaving the list reloads the page, so it shows in (or drops out of) the list and the totals.
 */
import { CHANGED_EVENT, collectionCounter } from "./collection/counter.js";
import { reloadWhenListChanges } from "./collection/suggestions.js";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("collectionCounter", () => collectionCounter());
});

window.addEventListener(CHANGED_EVENT, (event) => reloadWhenListChanges(event.detail));
