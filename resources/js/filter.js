/**
 * Shared filter dropdown behaviour for the card database and the deck builder.
 */
import { debounce } from "./utils.js";

let filterSelects = [];

export function initFilters({ selects = [], button, details, clearButton, onChange }) {
    filterSelects = [...selects];
    const debouncedChange = debounce(() => onChange?.(), 250);

    filterSelects.forEach((select) => select.addEventListener("change", debouncedChange));

    // Open the filter panel straight away when the page was loaded with filters applied.
    if (filterSelects.some((select) => select.value)) toggleFilterDetails(button, details, true);

    button?.addEventListener("click", (event) => {
        event.preventDefault();
        toggleFilterDetails(button, details);
    });

    clearButton?.addEventListener("click", (event) => {
        event.preventDefault();
        filterSelects.forEach((select) => (select.value = ""));
        onChange?.();
    });
}

export function appendFilterParams(params) {
    filterSelects.forEach((select) => {
        if (select.value) params.set(select.name, select.value);
    });
}

export function applyFiltersFromURL(params) {
    filterSelects.forEach((select) => (select.value = params.get(select.name) ?? ""));
}

function toggleFilterDetails(button, details, forceOpen) {
    if (!button || !details) return;

    const open = forceOpen ?? details.classList.contains("hidden");
    details.classList.toggle("hidden", !open);
    // Only update the label so the button's icon stays in place.
    (button.querySelector("[data-label]") ?? button).textContent = open ? "Hide Filters" : "Filter";
    button.setAttribute("aria-expanded", String(open));
}
