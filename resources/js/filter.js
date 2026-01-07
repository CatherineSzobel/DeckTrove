let filterSelects = [];
let filterButton;
let filterDetails;
let clearFilterButton;
let onChangeCallback;

export function initFilters({
    selects = [],
    button,
    details,
    clearButton,
    onChange,
}) {
    filterSelects = [...selects];
    filterButton = button;
    filterDetails = details;
    clearFilterButton = clearButton;
    onChangeCallback = onChange;

    setupFilterListeners();
}

function setupFilterListeners() {
    filterSelects.forEach((filter) => {
        const debouncedChange = debounce(() => {
            onChangeCallback?.();
        }, 250);

        filter.addEventListener("change", debouncedChange);
    });

    filterButton?.addEventListener("click", (e) => {
        e.preventDefault();
        toggleFilterDetails();
    });

    clearFilterButton?.addEventListener("click", (e) => {
        e.preventDefault();
        clearFilters();
        onChangeCallback?.();
    });
}

export function appendFilterParams(params) {
    filterSelects.forEach((filter) => {
        if (filter.value) {
            params.set(filter.name, filter.value);
        }
    });
}

export function applyFiltersFromURL(params) {
    filterSelects.forEach((filter) => {
        filter.value = params.get(filter.name) ?? "";
    });
}

function clearFilters() {
    filterSelects.forEach((filter) => {
        filter.value = "";
    });
}

function toggleFilterDetails() {
    if (!filterDetails || !filterButton) return;

    const isHidden = filterDetails.classList.toggle("hidden");

    filterButton.textContent = isHidden ? "Filter" : "Hide Filters";
    filterButton.classList.toggle("bg-blue-500", !isHidden);
    filterButton.classList.toggle("text-white", !isHidden);
}

function debounce(fn, delay = 300) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}
