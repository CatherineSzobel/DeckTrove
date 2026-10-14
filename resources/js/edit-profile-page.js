/**
 * Edit profile page (resources/views/account/edit-profile.blade.php): the picture preview, the live About counter and
 * the "Your decks" selection.
 */
import { deckSelection } from "./account/deck-selection.js";
import { profileForm } from "./account/profile-form.js";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("profileForm", (length) => profileForm(length));
    window.Alpine.data("deckSelection", deckSelection);
});
