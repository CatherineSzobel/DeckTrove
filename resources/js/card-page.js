/**
 * Card page (resources/views/cards/card.blade.php): flipping double-faced cards, tilting the card, showing all
 * printings, previewing the related cards on hover or focus, and the collection row's counter and wishlist heart.
 */
import { cardFaces } from "./cards/faces.js";
import { printList } from "./cards/print-list.js";
import { cardTilt } from "./cards/tilt.js";
import { collectionCounter, collectionHeart, collectionRow } from "./collection/counter.js";
import { cardPreview } from "./deckbuilder/preview.js";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("cardFaces", cardFaces);
    window.Alpine.data("cardTilt", cardTilt);
    window.Alpine.data("cardPreview", cardPreview);
    window.Alpine.data("printList", printList);
    window.Alpine.data("collectionCounter", () => collectionCounter());
    window.Alpine.data("collectionHeart", () => collectionHeart());
    window.Alpine.data("collectionRow", () => collectionRow());
});
