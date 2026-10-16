/**
 * Public profile (resources/views/users/show.blade.php): the comment form's live counter.
 */
import { charCounter } from "./char-counter.js";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("charCounter", (length) => charCounter(length));
});
