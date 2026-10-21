/**
 * Card page (resources/views/cards/card.blade.php): flipping double-faced cards, tilting the card, showing all
 * printings, and previewing the related cards on hover or focus.
 */
import { cardFaces } from "./cards/faces.js";
import { printList } from "./cards/print-list.js";
import { cardTilt } from "./cards/tilt.js";
import { cardPreview } from "./deckbuilder/preview.js";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("cardFaces", cardFaces);
    window.Alpine.data("cardTilt", cardTilt);
    window.Alpine.data("cardPreview", cardPreview);
    window.Alpine.data("printList", printList);
});
