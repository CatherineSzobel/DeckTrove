/**
 * Collection and wishlist pages (resources/views/collection/index.blade.php): the cards' − n + change in place and the
 * totals follow; a card joining or leaving the list reloads the page, so it shows in (or drops out of) the list.
 */
import { CHANGED_EVENT, collectionCounter } from "./collection/counter.js";
import { reloadWhenListChanges } from "./collection/suggestions.js";
import { totalsRefresher } from "./collection/totals.js";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("collectionCounter", () => collectionCounter());
});

const refreshTotals = totalsRefresher();

window.addEventListener(CHANGED_EVENT, (event) => {
    reloadWhenListChanges(event.detail);
    refreshTotals();
});
