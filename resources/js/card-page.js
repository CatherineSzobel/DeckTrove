/**
 * Card page (resources/views/cards/card.blade.php): flipping double-faced cards, tilting the card and showing all
 * printings.
 */
import { cardFaces } from "./cards/faces.js";
import { printList } from "./cards/print-list.js";
import { cardTilt } from "./cards/tilt.js";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("cardFaces", cardFaces);
    window.Alpine.data("cardTilt", cardTilt);
    window.Alpine.data("printList", printList);
});
