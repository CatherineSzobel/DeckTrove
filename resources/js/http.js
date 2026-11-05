/**
 * What every in-page request sends. X-Requested-With makes Laravel treat it as AJAX (partial HTML, 401 instead of a
 * login redirect); Accept: application/json gets JSON back, validation errors included.
 */
export const AJAX_HEADERS = { "X-Requested-With": "XMLHttpRequest" };
export const JSON_HEADERS = { ...AJAX_HEADERS, Accept: "application/json" };

/** The page's CSRF token, from the layout's <meta name="csrf-token">. */
export function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? "";
}

/** GETs a page's partial HTML (the server answers AJAX requests with just the part that changes). */
export function fetchPartial(url, { fetch = globalThis.fetch, signal } = {}) {
    return fetch(url, { headers: AJAX_HEADERS, signal });
}
