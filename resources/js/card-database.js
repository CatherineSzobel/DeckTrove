/**
 * Card database (resources/views/cards/cards.blade.php). The page works without JS (GET form and links); this loads
 * results in place: filters apply on change, the search on submit, chips and Clear all without a reload, Back and
 * Forward restore the form, and the page links swap pages in place.
 * The server answers AJAX requests with cards/partials/results.blade.php.
 */
import { buildUrl, countFilters, syncValues, viewUrl } from "./cards/query.js";
import { collectionCounter } from "./collection/counter.js";
import { cardPreview } from "./deckbuilder/preview.js";
import { fetchPartial } from "./http.js";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("collectionCounter", () => collectionCounter());

    window.Alpine.data("cardDatabase", () => ({
        ...cardPreview(),
        showFilters: false,
        loading: false,
        activeCount: 0,
        request: null,

        init() {
            // $refs aren't registered yet inside init(), so the panel's server state is read through $el.
            this.showFilters = !this.$el.querySelector("#filters").hasAttribute("data-collapsed");
            this.activeCount = countFilters(new URLSearchParams(location.search), this.filterKeys(this.$el));
            window.addEventListener("popstate", () => this.refresh(location.href));
        },

        // Outside init(), $el is the element that fired the event (a select, a chip link), not the component's root,
        // so methods find their elements through $refs. init() passes its root, as $refs aren't ready there.
        filterKeys(root = this.$refs.form) {
            return [...root.querySelectorAll("#filters select[name]")].map((select) => select.name);
        },

        /** Shows the results for a link (chip, Clear all) or, without one, for the search form. */
        apply(href = null) {
            const url = href ?? buildUrl(location.pathname, new FormData(this.$refs.form));
            history.pushState({}, "", url);
            this.refresh(url);
        },

        async refresh(url) {
            this.hidePreview();
            this.request?.abort();
            const request = (this.request = new AbortController());
            this.loading = true;

            try {
                this.$refs.region.innerHTML = await this.fetchHtml(url, request.signal);
                this.hidePreview();
                this.syncForm(new URL(url, location.origin).searchParams);
            } catch (error) {
                if (error.name !== "AbortError") location.href = url;
            } finally {
                if (this.request === request) this.loading = false;
            }
        },

        async fetchHtml(url, signal) {
            const response = await fetchPartial(url, { signal });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.text();
        },

        syncForm(params) {
            const fields = this.$refs.form.elements;
            const values = syncValues(params, [...this.filterKeys(), "search"]);

            Object.entries(values).forEach(([name, value]) => (fields.namedItem(name).value = value));
            this.activeCount = countFilters(params, this.filterKeys());

            // The view switch sits outside the results, so its links are rewritten to keep the new filters.
            this.$refs.views.querySelectorAll("a[data-view]").forEach((link) => (link.href = viewUrl(location.pathname, params, link.dataset.view)));
        },

        onRegionClick(event) {
            const pageLink = event.target.closest('nav[aria-label="Pagination"] a');
            const link = pageLink ?? event.target.closest("a[data-apply-link]");
            if (!link || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

            event.preventDefault();
            this.apply(link.href);
            // A page link sits below the cards: go back up to the start of the new page.
            if (pageLink) this.$refs.region.scrollIntoView({ block: "start" });
        },
    }));
});
