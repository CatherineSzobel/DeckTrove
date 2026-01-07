/* @TODO
- [ ] Consider increasing debounce time or disable live search for very short input (<3 chars)
- [ ] Show a "searching…" spinner for long queries (>1s)
- [ ] Optionally log search duration for analytics

*/
import {
    initFilters,
    appendFilterParams,
    applyFiltersFromURL,
} from "./filter.js";


let currentView = "full";
let currentSearch = "";

const viewSelectors = document.querySelectorAll(".viewtype-selector");
const filterSelects = document.querySelectorAll(".filter-select");
const searchForm = document.getElementById("search-form");
const searchInput = document.getElementById("search-input");
const filterButton = document.getElementById("filter-button");
const filterDetails = document.getElementById("filterDetails");
const clearFilterButton = document.getElementById("clear-filter-button");
const cardsInner = document.getElementById("cards-inner");
const cardsLoader = document.getElementById("cards-loader");
const resultCountDiv = document.querySelector(".text-sm.text-gray-600");

let debounceTimeout;

function debounce(fn, delay = 300) {
    return (...args) => {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => fn(...args), delay);
    };
}

function debounceSearch() {
    searchInput?.addEventListener(
        "input",
        debounce(() => {
            const value = searchInput.value.trim();

            if (value.length >= 3 || value.length === 0) {
                currentSearch = value;
                updateCards();
            }
        }, 300)
    );
}

function buildURLParams() {
    const params = new URLSearchParams();
    if (currentView) params.set("view", currentView);
    if (currentSearch) params.set("search", currentSearch);
    appendFilterParams(params);
    return params;
}

async function updateCards() {
    const url = `${window.location.pathname}?${buildURLParams()}`;
    history.pushState({}, "", url);

    if (cardsLoader) {
        cardsLoader.classList.remove("opacity-0", "pointer-events-none");
        cardsLoader.classList.add("opacity-100");
    }

    try {
        const res = await fetch(url, {
            headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        const data = await res.json();

        if (cardsInner) cardsInner.innerHTML = data.html;
        if (resultCountDiv && data.count)
            resultCountDiv.textContent = data.count;
    } catch (err) {
        console.error(err);
        window.location.href = url;
    } finally {
        if (cardsLoader) {
            cardsLoader.classList.remove("opacity-100");
            cardsLoader.classList.add("opacity-0", "pointer-events-none");
        }
    }
}

function browseNavigation() {

    const params = new URLSearchParams(window.location.search);
    currentView = params.get("view") || currentView;
    currentSearch = params.get("search") || "";
    window.currentSearch = currentSearch;

    viewSelectors.forEach((b) =>
        b.dataset.view === currentView ? b.click() : null
    );

    applyFiltersFromURL(params);

    if (searchInput) searchInput.value = currentSearch;

    updateCards();
}

function handleViewChange(button) {
    currentView = button.dataset.view;
    localStorage.setItem("viewType", currentView);

    viewSelectors.forEach((b) => {
        b.classList.remove("bg-blue-600", "text-white", "shadow-sm");
        b.classList.add("text-gray-700", "hover:bg-white");
    });
    button.classList.add("bg-blue-600", "text-white", "shadow-sm");
    button.classList.remove("text-gray-700", "hover:bg-white");

    updateCards();

}
function setupEventListeners() {

    viewSelectors.forEach((btn) =>
        btn.addEventListener("click", () => handleViewChange(btn))
    );

    debounceSearch();

    searchForm?.addEventListener("submit", (e) => e.preventDefault());

    window.addEventListener("popstate", browseNavigation);
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
