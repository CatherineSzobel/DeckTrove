// ----- State -----
let currentView = "full";
let currentSearch = "";

// ----- Cached DOM Elements -----
const viewSelectors = document.querySelectorAll(".viewtype-selector");
const filterSelects = document.querySelectorAll(".filter-select");
const searchForm = document.getElementById("search-form");
const searchInput = document.getElementById("search-input");
const filterButton = document.getElementById("filter-button");
const filterDetails = document.getElementById("filterDetails");
const clearFilterButton = document.getElementById("clear-filter-button");
const cardsInner = document.getElementById("cards-inner"); // container for AJAX updates
const cardsLoader = document.getElementById("cards-loader");

// ----- Core Getters -----
function getCurrentSeries() {
    return document.querySelector("[data-series]")?.dataset.series || "yugioh";
}
const series = getCurrentSeries();

// ----- URL / Filter Helpers -----
function buildURLParams() {
    const params = new URLSearchParams();
    if (currentView) params.set("view", currentView);
    if (currentSearch) params.set("search", currentSearch);
    filterSelects.forEach((select) => {
        if (select.value) params.set(select.name, select.value);
    });
    return params;
}

// Modified updateURL to use AJAX
async function updateURL() {
    const url = `${window.location.pathname}?${buildURLParams()}`;
    history.pushState({}, "", url);

    // Show loader
    if (cardsLoader) {
        cardsLoader.classList.remove("opacity-0", "pointer-events-none");
        cardsLoader.classList.add("opacity-100");
    }

    try {
        const res = await fetch(url, {
            headers: { "X-Requested-With": "XMLHttpRequest" },
        });

        if (!res.ok) throw new Error("Network error");

        // Get the HTML text from the response
        const data = await res.text();

        // Replace the cards-inner content with the returned HTML
        if (cardsInner) cardsInner.innerHTML = data;
    } catch (err) {
        console.error("Failed to fetch cards:", err);
        window.location.href = url; // fallback to full page reload
    } finally {
        // Hide loader
        if (cardsLoader) {
            cardsLoader.classList.remove("opacity-100");
            cardsLoader.classList.add("opacity-0", "pointer-events-none");
        }
    }
}

// ----- Filter Functions -----
function updateFilters() {
    updateURL();
}

function initializeFilters() {
    const urlParams = new URLSearchParams(window.location.search);
    filterSelects.forEach((select) => {
        const value = urlParams.get(select.name);
        if (value) select.value = value;
    });
}

function clearFilters() {
    filterSelects.forEach((select) => (select.value = ""));
    currentSearch = "";
    if (searchInput) searchInput.value = "";
    updateURL();
}

// ----- Event Listeners -----
function setupEventListeners() {
    // Initialize saved view
    const savedView = localStorage.getItem("viewType");
    if (savedView) currentView = savedView;

    viewSelectors.forEach((selection) => {
        selection.value = currentView;
        selection.addEventListener("change", (event) => {
            currentView = event.target.value;
            localStorage.setItem("viewType", currentView);
            updateURL();
        });
    });

    // Filter selects
    filterSelects.forEach((filter) =>
        filter.addEventListener("change", updateFilters)
    );

    // Search form
    searchForm?.addEventListener("submit", (event) => {
        event.preventDefault();
        currentSearch = searchInput?.value || "";
        updateURL();
    });

    // Filter toggle
    filterButton?.addEventListener("click", (event) => {
        event.preventDefault();
        filterDetails?.classList.toggle("hidden");

        if (filterButton && filterDetails) {
            const isHidden = filterDetails.classList.contains("hidden");
            filterButton.textContent = isHidden
                ? "Show Filters"
                : "Hide Filters";
            filterButton.classList.toggle("bg-blue-500", !isHidden);
            filterButton.classList.toggle("text-white", !isHidden);
        }
    });

    // Clear filters
    clearFilterButton?.addEventListener("click", (event) => {
        event.preventDefault();
        clearFilters();
    });

    // Handle browser back/forward
    window.addEventListener("popstate", () => {
        const params = new URLSearchParams(window.location.search);
        currentView = params.get("view") || currentView;
        currentSearch = params.get("search") || "";
        viewSelectors.forEach((sel) => (sel.value = currentView));
        filterSelects.forEach((select) => {
            const value = params.get(select.name);
            select.value = value || "";
        });
        if (searchInput) searchInput.value = currentSearch;
        updateURL();
    });
}

// ----- Initialize -----
function initializeCardDatabase() {
    setupEventListeners();
    initializeFilters();

    // Set current search value from URL
    currentSearch = new URLSearchParams(window.location.search).get("search") || "";
    if (searchInput && currentSearch) searchInput.value = currentSearch;
}

document.addEventListener("DOMContentLoaded", initializeCardDatabase);
