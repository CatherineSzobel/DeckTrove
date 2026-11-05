/**
 * The collection and wishlist pages reload when a card joins or leaves the list, so the list and the totals follow,
 * as the no-JS redirect does: a card added from the quick add (resources/views/collection/partials/suggestions.blade.php)
 * comes back at its tile (#card-<id>); a card in the list taken down to 0 drops out (the browser keeps the scroll).
 */
export function reloadWhenListChanges(detail, {
    isSuggestion = (card) => document.getElementById(`card-${card}`)?.hasAttribute("data-suggestion") ?? false,
    reload = (hash) => {
        history.replaceState(null, "", `${location.pathname}${location.search}${hash}`);
        location.reload();
    },
} = {}) {
    if (!detail) return;

    if (isSuggestion(detail.card)) {
        if (detail.count > 0) reload(`#card-${detail.card}`);
    } else if (detail.count === 0) {
        reload("");
    }
}
