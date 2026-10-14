/**
 * Pack page (resources/views/packs/pack.blade.php): filters the card tiles as you type or pick a rarity, and keeps
 * the URL in sync, so the server's ?name=&rarity= filter and this one always agree. Hovering or focusing a tile shows
 * the card preview.
 */
import { buildUrl } from "./cards/query.js";
import { cardPreview } from "./deckbuilder/preview.js";
import { countLabel, matchesCard } from "./packs/filter.js";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("packCards", () => ({
        ...cardPreview(),
        shown: 0,
        total: 0,
        filtering: false,

        init() {
            // $refs aren't registered yet inside init().
            this.$nextTick(() => this.filter());
        },

        get countLabel() {
            return countLabel(this.shown, this.total, this.filtering);
        },

        filter() {
            const form = this.$refs.form;
            const query = { name: form.elements.namedItem("name").value, rarity: form.elements.namedItem("rarity").value };
            const tiles = [...this.$refs.grid.children];

            tiles.forEach((tile) => (tile.hidden = !matchesCard(tile.dataset, query)));
            this.total = tiles.length;
            this.shown = tiles.filter((tile) => !tile.hidden).length;
            this.filtering = query.name.trim() !== "" || query.rarity !== "";
            this.$refs.noMatches.hidden = this.shown > 0;

            history.replaceState({}, "", buildUrl(location.pathname, new FormData(form)));
        },
    }));
});
