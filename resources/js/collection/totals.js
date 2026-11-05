/**
 * The collection and wishlist pages' totals (#collection-totals) follow the − / +: after a change settles, the page
 * asks the server for just the totals (CollectionController answers AJAX with collection/partials/totals) and swaps
 * them in. Quick clicks make one request; an answer that's been overtaken is dropped.
 */
import { fetchPartial } from "../http.js";

export const SETTLE_MS = 400;

export function totalsRefresher({
    fetch = (url, options) => fetchPartial(url, options),
    url = () => location.href,
    target = () => document.getElementById("collection-totals"),
    wait = (callback, ms) => setTimeout(callback, ms),
    cancel = (id) => clearTimeout(id),
} = {}) {
    let timer = null;
    let latest = 0;

    async function load(id) {
        try {
            const response = await fetch(url());
            if (!response.ok) return;
            const html = await response.text();
            const el = target();
            if (el && id === latest) el.innerHTML = html;
        } catch {
            // The totals stay as they were; a refresh shows them.
        }
    }

    return () => {
        cancel(timer);
        timer = wait(() => load(++latest), SETTLE_MS);
    };
}
