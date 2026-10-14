/**
 * Card database (resources/views/cards/cards.blade.php). The page works without JS (GET form and links); this loads
 * results in place: filters apply on change, the search on submit, chips and Clear all without a reload, Back and
 * Forward restore the form, and the next page is appended when the "Load more cards" link comes into view.
 * The server answers AJAX requests with cards/partials/results.blade.php.
 */
import { buildUrl, countFilters, syncValues, viewUrl } from "./cards/query.js";
import { cardPreview } from "./deckbuilder/preview.js";

document.addEventListener("alpine:init", () => {
    window.Alpine.data("cardDatabase", () => ({
        ...cardPreview(),
        showFilters: false,
        loading: false,
        loadingMore: false,
        activeCount: 0,
        request: null,
        moreRequest: null,
        observer: null,

        init() {
            // $refs aren't registered yet inside init(), so the panel's server state is read through $el, and the
            // load-more link is watched on the next tick.
            this.showFilters = !this.$el.querySelector("#filters").hasAttribute("data-collapsed");
            this.activeCount = countFilters(new URLSearchParams(location.search), this.filterKeys(this.$el));
            this.observer = new IntersectionObserver(
                (entries) => entries.some((entry) => entry.isIntersecting) && this.loadMore(),
                { rootMargin: "400px" }
            );
            this.$nextTick(() => this.watchLoadMore());
            window.addEventListener("popstate", () => this.refresh(location.href));
        },

        /** Scrolling hides the preview, except when the scroll comes from keyboard focus on a tile: then it follows the tile. */
        hideOnScroll() {
            const tile = document.activeElement;

            if (tile?.matches(".card-tile:focus-visible")) {
                if (this.preview) this.showPreview(this.preview, tile);
                return;
            }

            this.hidePreview();
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
            this.moreRequest?.abort();
            this.loadingMore = false;
            const request = (this.request = new AbortController());
            this.loading = true;

            try {
                this.$refs.region.innerHTML = await this.fetchHtml(url, request.signal);
                this.hidePreview();
                this.syncForm(new URL(url, location.origin).searchParams);
                this.watchLoadMore();
            } catch (error) {
                if (error.name !== "AbortError") location.href = url;
            } finally {
                if (this.request === request) this.loading = false;
            }
        },

        async fetchHtml(url, signal) {
            const response = await fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" }, signal });
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
            const link = event.target.closest("a[data-apply-link], a[data-load-more]");
            if (!link || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

            event.preventDefault();
            link.hasAttribute("data-load-more") ? this.loadMore() : this.apply(link.href);
        },

        watchLoadMore() {
            this.observer.disconnect();
            const link = this.$refs.region.querySelector("[data-load-more]");
            if (link) this.observer.observe(link);
        },

        /** Appends the next page's cards, and takes its count and next-page link. */
        async loadMore() {
            const link = this.$refs.region.querySelector("[data-load-more]");
            const results = this.$refs.region.querySelector("[data-results]");
            if (!link || !results || this.loadingMore) return;
            this.loadingMore = true;
            const request = (this.moreRequest = new AbortController());

            try {
                const page = document.createElement("template");
                page.innerHTML = await this.fetchHtml(link.href, request.signal);

                // The results were replaced (new search or filter) while this page loaded: drop it.
                if (!results.isConnected) return;

                results.append(...page.content.querySelector("[data-results]").children);
                this.$refs.region.querySelector("[data-result-count]").replaceWith(page.content.querySelector("[data-result-count]"));
                const next = page.content.querySelector("[data-load-more]");
                next ? link.replaceWith(next) : link.remove();
                this.watchLoadMore();
            } catch (error) {
                if (error.name !== "AbortError") location.href = link.href;
            } finally {
                if (this.moreRequest === request) this.loadingMore = false;
            }
        },
    }));
});
