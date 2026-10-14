/**
 * URL helpers for the card database (resources/js/card-database.js). Pure functions, tested with Vitest.
 */

/** The page URL for the search form's fields. Empty values and `page` are left out, so a new search starts at page one. */
export function buildUrl(path, entries) {
    const params = new URLSearchParams();

    for (const [name, value] of entries) {
        const text = String(value).trim();
        if (name !== "page" && text !== "") params.append(name, text);
    }

    const query = params.toString();
    return query ? `${path}?${query}` : path;
}

/** The value each named field should show for these URL parameters ("" when missing), e.g. after Back. */
export function syncValues(params, names) {
    return Object.fromEntries(names.map((name) => [name, params.get(name) ?? ""]));
}

/** How many of the filter keys have a value (the search doesn't count). */
export function countFilters(params, keys) {
    return keys.filter((key) => (params.get(key) ?? "") !== "").length;
}

/** The URL for these parameters with another view (the default view and the page are left out). */
export function viewUrl(path, params, view, defaultView = "full") {
    const entries = [...params.entries()].filter(([name]) => name !== "view");
    if (view !== defaultView) entries.push(["view", view]);

    return buildUrl(path, entries);
}
