/**
 * Card database page: live search, filters and view switching without full page reloads.
 * The server returns {html, count} for AJAX requests (see CardsController@index).
 */
import { initFilters, appendFilterParams, applyFiltersFromURL } from "./filter.js";
import { debounce } from "./utils.js";

const viewSelectors = document.querySelectorAll(".viewtype-selector");
const searchForm = document.getElementById("search-form");
const searchInput = document.getElementById("search-input");
const cardsInner = document.getElementById("cards-inner");
const cardsLoader = document.getElementById("cards-loader");
const resultCount = document.getElementById("result-count");

const initialParams = new URLSearchParams(window.location.search);
let currentView = initialParams.get("view") || document.querySelector('.viewtype-selector[aria-pressed="true"]')?.dataset.view || "full";
let currentSearch = initialParams.get("search") || "";
let activeRequest = null;

function buildURL() {
    const params = new URLSearchParams();
    if (currentView) params.set("view", currentView);
    if (currentSearch) params.set("search", currentSearch);
    appendFilterParams(params);
    return `${window.location.pathname}?${params}`;
}

function setLoading(loading) {
    cardsLoader?.classList.toggle("opacity-0", !loading);
    cardsLoader?.classList.toggle("pointer-events-none", !loading);
}

async function loadCards(url, { push = true } = {}) {
    if (push) history.pushState({}, "", url);

    // Cancel an older request so slow responses never overwrite newer results.
    activeRequest?.abort();
    activeRequest = new AbortController();
    setLoading(true);

    try {
        const response = await fetch(url, {
            headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
            signal: activeRequest.signal,
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const data = await response.json();
        cardsInner.innerHTML = data.html;
        if (resultCount) resultCount.innerHTML = data.count;
    } catch (error) {
        if (error.name === "AbortError") return;
        console.error(error);
        window.location.href = url;
    } finally {
        setLoading(false);
    }
}

const updateCards = () => loadCards(buildURL());

function highlightView() {
    viewSelectors.forEach((button) => {
        const active = button.dataset.view === currentView;
        button.setAttribute("aria-pressed", String(active));
        button.classList.toggle("bg-blue-600", active);
        button.classList.toggle("text-white", active);
        button.classList.toggle("shadow-sm", active);
        button.classList.toggle("text-gray-700", !active);
        button.classList.toggle("hover:bg-white", !active);
    });
}

function setupEventListeners() {
    viewSelectors.forEach((button) =>
        button.addEventListener("click", () => {
            currentView = button.dataset.view;
            highlightView();
            updateCards();
        })
    );

    searchInput?.addEventListener(
        "input",
        debounce(() => {
            const value = searchInput.value.trim();
            if (value.length >= 3 || value.length === 0) {
                currentSearch = value;
                updateCards();
            }
        }, 350)
    );

    searchForm?.addEventListener("submit", (event) => {
        event.preventDefault();
        currentSearch = searchInput.value.trim();
        updateCards();
    });

    // Pagination links inside the results are loaded in place too.
    cardsInner?.addEventListener("click", (event) => {
        const link = event.target.closest("nav[role=navigation] a[href]");
        if (!link) return;
        event.preventDefault();
        loadCards(link.href);
        cardsInner.scrollIntoView({ behavior: "smooth" });
    });

    window.addEventListener("popstate", () => {
        const params = new URLSearchParams(window.location.search);
        currentView = params.get("view") || "full";
        currentSearch = params.get("search") || "";
        if (searchInput) searchInput.value = currentSearch;
        applyFiltersFromURL(params);
        highlightView();
        loadCards(window.location.href, { push: false });
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initFilters({
        selects: document.querySelectorAll(".filter-select"),
        button: document.getElementById("filter-button"),
        details: document.getElementById("filterDetails"),
        clearButton: document.getElementById("clear-filter-button"),
        onChange: updateCards,
    });

    setupEventListeners();
});
