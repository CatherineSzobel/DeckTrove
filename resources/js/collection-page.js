/**
 * Collection and wishlist pages (resources/views/collection/index.blade.php): the cards' − n + change in place.
 */
import { collectionCounter } from "./collection/counter.js";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("collectionCounter", () => collectionCounter());
});
