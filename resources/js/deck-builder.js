/**
 * Deck builder (resources/views/decks/deck-builder.blade.php): one Alpine component holding the whole deck.
 * Everything on screen (counts, prices, format problems, copies and limit tags) is derived from its state; the rules
 * themselves live in ./deckbuilder/rules.js so they match the server.
 */
import * as deckRules from "./deckbuilder/rules.js";
import { previewPosition } from "./deckbuilder/preview.js";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("deckBuilder", () => ({
        rules: { zones: {}, formats: {}, limit_labels: {}, extra_types: [], unlimited_types: [] },
        zones: {},
        active: "main",
        format: "",
        cover: "",
        title: "",
        description: "",
        isPublic: false,
        currency: { symbol: "€" },
        notice: null,
        showImport: false,
        showCoverPicker: false,
        showTooSmall: false,
        dirty: false,
        nextUid: 0,

        // One card preview for deck cards and search results, placed next to the hovered or focused card.
        preview: null,
        previewPos: { left: 0, top: 0 },

        // Card search (results are server-rendered HTML; see resources/views/decks/partials/cards-view.blade.php).
        searchUrl: "",
        search: "",
        filters: {},
        showFilters: false,
        nextPage: null,
        total: 0,
        shown: 0,
        loading: false,

        init() {
            const state = JSON.parse(document.getElementById("deck-builder-state").textContent);

            this.rules = state.rules;
            this.zones = Object.fromEntries(
                Object.keys(state.rules.zones).map((zone) => [zone, (state.zones[zone] ?? []).map((card) => this.copyOf(card))])
            );
            this.active = Object.keys(state.rules.zones)[0];
            this.format = state.format ?? "";
            this.cover = state.cover ?? "";
            this.title = state.title ?? "";
            this.description = state.description ?? "";
            this.isPublic = Boolean(state.isPublic);
            this.currency = state.currency ?? this.currency;
            this.showImport = Boolean(state.showImport);

            ["title", "description", "format", "cover", "isPublic"].forEach((key) => this.$watch(key, () => (this.dirty = true)));
            window.addEventListener("beforeunload", (event) => {
                if (this.dirty) event.preventDefault();
            });

            this.initSearch(state);
        },

        // --- Search --------------------------------------------------------------------------------

        initSearch(state) {
            const params = new URLSearchParams(window.location.search);

            this.searchUrl = state.searchUrl;
            this.nextPage = state.nextPage;
            this.total = state.total;
            this.search = params.get("search") ?? "";
            this.filters = Object.fromEntries((state.filters ?? []).map((key) => [key, params.get(key) ?? ""]));
            // Open the filters straight away when the page was loaded with filters applied.
            this.showFilters = Object.values(this.filters).some(Boolean);
            this.$nextTick(() => (this.shown = this.countResults()));
        },

        countResults() {
            return this.$refs.results?.querySelectorAll("[data-card-id]").length ?? 0;
        },

        get resultSummary() {
            return this.total ? `Showing ${this.shown} of ${this.total.toLocaleString()} cards` : "No cards found";
        },

        runSearch() {
            this.$refs.results.scrollTop = 0;
            this.loadPage(1, { replace: true });
        },

        clearSearch() {
            this.search = "";
            Object.keys(this.filters).forEach((key) => (this.filters[key] = ""));
            this.runSearch();
        },

        onResultsScroll(event) {
            const list = event.target;
            if (this.nextPage && list.scrollTop + list.clientHeight >= list.scrollHeight - 150) this.loadPage(this.nextPage);
        },

        async loadPage(page, { replace = false } = {}) {
            if (this.loading) return;
            this.loading = true;

            const params = new URLSearchParams({ page });
            if (this.search.trim()) params.set("search", this.search.trim());
            Object.entries(this.filters).forEach(([key, value]) => value && params.set(key, value));

            try {
                const response = await fetch(`${this.searchUrl}?${params}`, { headers: { "X-Requested-With": "XMLHttpRequest" } });
                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                // Alpine picks up the new results (and their x-data) as soon as they are in the page.
                const template = document.createElement("template");
                template.innerHTML = await response.text();
                replace ? this.$refs.results.replaceChildren(template.content) : this.$refs.results.append(template.content);

                this.nextPage = Number(response.headers.get("X-Next-Page")) || null;
                if (replace) this.total = Number(response.headers.get("X-Total") || 0);
                this.shown = this.countResults();
            } catch (error) {
                console.error("Error loading cards:", error);
                this.notice = "The card search failed. Please try again.";
            } finally {
                this.loading = false;
            }
        },

        /** A copy of a card for the deck; `uid` keeps x-for keys unique when a card is in the deck several times. */
        copyOf(card) {
            return { ...card, uid: ++this.nextUid };
        },

        // --- Card preview --------------------------------------------------------------------------

        showPreview(card, element) {
            this.preview = card;

            // Measure the preview once it shows this card, then place it beside the card.
            this.$nextTick(() => {
                const panel = this.$refs.preview;
                this.previewPos = previewPosition(
                    element.getBoundingClientRect(),
                    { width: window.innerWidth, height: window.innerHeight },
                    { width: panel.offsetWidth, height: panel.offsetHeight },
                );
            });
        },

        hidePreview() {
            this.preview = null;
        },

        // --- Zones ---------------------------------------------------------------------------------

        get zoneKeys() {
            return Object.keys(this.rules.zones);
        },

        count(zone) {
            return this.zones[zone]?.length ?? 0;
        },

        range(zone) {
            const { min, max } = this.rules.zones[zone];
            return min > 0 ? `${min}–${max}` : `max ${max}`;
        },

        isShort(zone) {
            return this.count(zone) < this.rules.zones[zone].min;
        },

        progress(zone) {
            const { min, max } = this.rules.zones[zone];
            return Math.round(Math.min(this.count(zone) / (min || max), 1) * 100);
        },

        zoneHint(zone) {
            const { min, max } = this.rules.zones[zone];

            if (this.isShort(zone)) return `Needs ${min - this.count(zone)} more cards before it can be shared publicly.`;
            if (zone === "main") return `Ready to share — up to ${max} cards.`;
            if (zone === "extra" && this.rules.extra_types.length) return `${this.rules.extra_types.join(", ")} monsters only.`;

            return `Optional — up to ${max} cards.`;
        },

        // --- Prices --------------------------------------------------------------------------------

        euro(value) {
            return `${this.currency.symbol}${value.toFixed(2)}`;
        },

        zonePrice(zone) {
            return deckRules.deckPrice({ [zone]: this.zones[zone] ?? [] });
        },

        get deckPrice() {
            return deckRules.deckPrice(this.zones);
        },

        get unpricedHint() {
            const count = deckRules.unpricedCount(this.zones);
            return count ? `${count} ${count === 1 ? "card" : "cards"} unpriced` : "Cardmarket prices";
        },

        // --- Format --------------------------------------------------------------------------------

        get formatLabel() {
            return this.format ? (this.rules.formats[this.format]?.label ?? this.format) : "Casual";
        },

        get formatHint() {
            return this.format
                ? `${this.formatLabel} banlist applies on top of the normal deck rules.`
                : "No banlist. Only the normal deck rules apply.";
        },

        get problems() {
            return deckRules.formatProblems(this.rules, this.zones, this.format);
        },

        get problemsTitle() {
            const count = this.problems.length;
            return `${count} ${count === 1 ? "card breaks" : "cards break"} ${this.formatLabel}`;
        },

        problemFor(id) {
            return this.problems.find((problem) => problem.id === id)?.message ?? null;
        },

        // --- Cards in the search results -----------------------------------------------------------

        copiesOf(card) {
            return deckRules.copiesOf(this.zones, card.id);
        },

        copiesLabel(card) {
            const allowed = deckRules.allowedCopies(this.rules, card, this.format);
            return allowed === Infinity ? `×${this.copiesOf(card)}` : `${this.copiesOf(card)}/${allowed}`;
        },

        isOverLimit(card) {
            return this.copiesOf(card) > deckRules.allowedCopies(this.rules, card, this.format);
        },

        /** The current format's limit for a card, or null when the format doesn't limit it. */
        limitOf(card) {
            const limit = this.format ? card.limits?.[this.format] : undefined;
            return limit === undefined ? null : limit;
        },

        /** The format's limit in the game's words ("Limited · 1"), or null. */
        limitTag(card) {
            const limit = this.limitOf(card);
            if (limit === null) return null;

            const label = this.rules.limit_labels[limit] ?? "";
            return limit > 0 ? `${label} · ${limit}` : label;
        },

        canRemove(card) {
            return (this.zones[this.active] ?? []).some((copy) => copy.id === card.id);
        },

        add(card) {
            const error = deckRules.placementError(this.rules, this.zones, this.active, card, this.format);
            if (error) {
                this.notice = error;
                return;
            }

            this.zones[this.active].push(this.copyOf(card));
            this.notice = null;
            this.dirty = true;
        },

        /** Removes the last copy of the card from the active zone. */
        removeOne(card) {
            const list = this.zones[this.active];
            const index = list.findLastIndex((copy) => copy.id === card.id);
            if (index === -1) return;

            list.splice(index, 1);
            this.notice = null;
            this.dirty = true;
        },

        removeAt(zone, index) {
            // The removed card can't send mouseleave any more.
            this.hidePreview();
            this.zones[zone].splice(index, 1);
            this.notice = null;
            this.dirty = true;
        },

        setCover(card) {
            this.cover = card.image;
        },

        clearCover() {
            this.cover = "";
            this.showCoverPicker = false;
        },

        /** The cards the cover picker offers: each card in the deck once. */
        get coverChoices() {
            return deckRules.uniqueCards(this.zones);
        },

        pickCover(card) {
            this.setCover(card);
            this.showCoverPicker = false;
        },

        async reset() {
            const confirmed = await window.confirmDialog({
                title: "Reset this deck?",
                message: "This removes all cards and the cover from the deck. Nothing is saved until you press Save.",
                confirmLabel: "Reset deck",
            });
            if (!confirmed) return;

            this.zoneKeys.forEach((zone) => (this.zones[zone] = []));
            this.cover = "";
            this.notice = null;
            this.dirty = true;
        },

        /** The edit page's import replaces the deck, so it asks first. */
        async confirmReplace(form) {
            const confirmed = await window.confirmDialog({
                title: "Replace this deck?",
                message: "This replaces all cards in this deck with the imported list.",
                confirmLabel: "Replace cards",
            });
            if (!confirmed) return;

            this.dirty = false;
            form.submit();
        },

        // --- Saving --------------------------------------------------------------------------------

        get mainMin() {
            return this.rules.zones.main?.min ?? 0;
        },

        get publicReady() {
            return this.count("main") >= this.mainMin;
        },

        get publicHint() {
            return this.publicReady ? "Main deck is big enough" : `Needs ${this.mainMin}+ main deck cards`;
        },

        get cardsPayload() {
            return deckRules.payload(this.zones);
        },

        save() {
            if (this.problems.length) {
                this.notice = `This deck breaks ${this.formatLabel}. Remove the marked cards, or switch the format to Casual.`;
                return;
            }

            if (this.isPublic && !this.publicReady) {
                this.showTooSmall = true;
                return;
            }

            this.submit();
        },

        savePrivate() {
            this.isPublic = false;
            this.showTooSmall = false;
            this.submit();
        },

        submit() {
            this.dirty = false;
            // Wait for Alpine to write the hidden inputs before the browser reads them.
            this.$nextTick(() => this.$refs.saveForm.submit());
        },
    }));
});
