// ----- State -----
let currentView = 'full';
let currentSearch = '';

// ----- Core Getters / Setters -----
function getCurrentSeries() {
    const seriesElement = document.querySelector('[data-series]');
    if (seriesElement && seriesElement.dataset.series) {
        return seriesElement.dataset.series;
    }
    return 'yugioh';
}

const series = getCurrentSeries();

// ----- Filter Functions -----
function updateFilters() {
    const params = new URLSearchParams();
    
    // Add current view and search
    params.set('view', currentView);
    if (currentSearch) {
        params.set('search', currentSearch);
    }
    
    // Add filter values from all filter selects
    document.querySelectorAll('.filter-select').forEach(select => {
        if (select.value) {
            params.set(select.name, select.value);
        }
    });
    
    // Reload page with filters
    window.location.href = `${window.location.pathname}?${params}`;
}

function initializeFilters() {
    // Set current filter values from URL on page load
    const urlParams = new URLSearchParams(window.location.search);
    
    document.querySelectorAll('.filter-select').forEach(select => {
        const filterValue = urlParams.get(select.name);
        if (filterValue) {
            select.value = filterValue;
        }
    });
}

function clearFilters() {
    // Clear all filter dropdowns
    document.querySelectorAll('.filter-select').forEach(select => {
        select.value = '';
    });
    
    // Reload page without filter parameters
    const params = new URLSearchParams();
    if (currentView) params.set('view', currentView);
    if (currentSearch) params.set('search', currentSearch);
    
    window.location.href = `${window.location.pathname}?${params}`;
}

// ----- Event Listeners -----
function setupCardDatabaseEventListeners() {
    // View type selector
    document.querySelectorAll('.viewtype-selector').forEach(sel => {
        sel.addEventListener('change', e => {
            currentView = e.target.value;
            localStorage.setItem('viewType', currentView);
            updateURL();
        });
        const savedView = localStorage.getItem('viewType');
        if (savedView) sel.value = currentView = savedView;
    });

    // Filter select changes
    document.querySelectorAll('.filter-select').forEach(select => {
        select.addEventListener('change', updateFilters);
    });

    // Search
    document.getElementById('search-form')?.addEventListener('submit', e => {
        e.preventDefault();
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            currentSearch = searchInput.value;
        }
        updateURL();
    });

    // Filter toggle
    document.getElementById('filter-button')?.addEventListener('click', e => {
        e.preventDefault();
        document.getElementById('filterDetails')?.classList.toggle('hidden');
        
        // Update button text
        const filterButton = document.getElementById('filter-button');
        if (filterButton) {
            const isHidden = document.getElementById('filterDetails').classList.contains('hidden');
            filterButton.textContent = isHidden ? 'Show Filters' : 'Hide Filters';
            filterButton.classList.toggle('bg-blue-500', !isHidden);
            filterButton.classList.toggle('text-white', !isHidden);
        }
    });

    // Clear filters button
    document.getElementById('clear-filter-button')?.addEventListener('click', e => {
        e.preventDefault();
        clearFilters();
    });
}

// ----- URL Update -----
function updateURL() {
    const params = new URLSearchParams();
    
    if (currentView) params.set('view', currentView);
    if (currentSearch) params.set('search', currentSearch);
    
    // Add filter parameters
    document.querySelectorAll('.filter-select').forEach(select => {
        if (select.value) {
            params.set(select.name, select.value);
        }
    });
    
    // Reload page with new URL parameters
    window.location.href = `${window.location.pathname}?${params}`;
}

// ----- Initialize -----
function initializeCardDatabase() {
    setupCardDatabaseEventListeners();
    initializeFilters();
    
    // Set current search value from URL
    const urlParams = new URLSearchParams(window.location.search);
    currentSearch = urlParams.get('search') || '';
    
    // Update search input if it exists
    const searchInput = document.getElementById('search-input');
    if (searchInput && currentSearch) {
        searchInput.value = currentSearch;
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', initializeCardDatabase);