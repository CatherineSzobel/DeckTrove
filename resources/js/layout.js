const dashboardLinks = document.querySelectorAll(".dashboard-link");
const SERIES_CONFIG = {
    magic: { label: "Magic: The Gathering", base: "/magic" },
    yugioh: { label: "Yu-Gi-Oh!", base: "/yugioh" },
    //pokemon: { label: "Pokémon", base: "/pokemon" },
    // digimon: { label: "Digimon", base: "/digimon" } 
};

const SERIES_KEYS = Object.keys(SERIES_CONFIG);

function seriesSelectorSetup() {
    const selectors = document.querySelectorAll(".series-selector");
    const dashboardLogos = document.querySelectorAll(".dashboard-logo");
    let selectedSeries = localStorage.getItem("selectedSeries");

    if (selectedSeries && !SERIES_CONFIG[selectedSeries]) {
        localStorage.removeItem("selectedSeries");
        selectedSeries = null;
    }

    if (!selectedSeries && window.location.pathname !== "/") {
        window.location.href = "/";
        return;
    }

    selectors.forEach((selector) => {
        Array.from(selector.options).forEach((option) => {
            option.disabled = !SERIES_CONFIG[option.value];
        });

        if (selectedSeries) {
            selector.value = selectedSeries;
            updateButtonText(selector, selectedSeries);
        }

        selector.addEventListener("change", () => {
            directThroughSelector(selector);
        });
    });

    dashboardLogos.forEach((img) => {
        img.addEventListener("click", () => {
            directThroughLogo(img);
        });
    });

    if (selectedSeries) updateLinks(selectedSeries);
}
function directThroughSelector(selector) {
    const series = selector.value;

    if (!SERIES_CONFIG[series]) return;

    localStorage.setItem("selectedSeries", series);
    updateLinks(series);
    window.location.href = `/${series}/cards`;
}
function directThroughLogo(img) {
    const series = img.dataset.series;
    if (!SERIES_CONFIG[series]) return;

    localStorage.setItem("selectedSeries", series);
    updateLinks(series);
    window.location.href = `/${series}/cards`;
}

function updateButtonText(selector, series) {
    const buttonText = selector
        .closest(".group")
        ?.querySelector(".viewtype_text");
    if (buttonText) buttonText.textContent = SERIES_CONFIG[series].label;
}

function cleanPath(path) {
    const pattern = new RegExp(`^/(${SERIES_KEYS.join("|")})(/|$)`);
    return path.replace(pattern, "/");
}

function updateLinks(series) {
    dashboardLinks.forEach((link) => {
        const href = link.getAttribute("href");
        if (!href || href.startsWith("#")) return;

        const cleanHref = cleanPath(href);
        const newHref =
            cleanHref === "/" ? `/${series}/cards` : `/${series}${cleanHref}`;
        link.setAttribute("href", newHref);
    });
}

function initUserAvatarDropdown() {
    const container = document.getElementById("userDropdown-container");
    const btn = document.getElementById("userDropdown-btn");
    const menu = document.getElementById("userDropdown-menu");

    if (!container || !btn || !menu) return;

    btn.addEventListener("click", (event) => {
        event.preventDefault();
        menu.classList.toggle("hidden");
    });

    document.addEventListener("click", (event) => {
        if (!container.contains(event.target)) menu.classList.add("hidden");
    });
}

function initShowcaseToggle() {
    const button = document.getElementById("showcase_button");
    const details = document.getElementById("showcaseDetails");
    if (!button || !details) return;

    let isOpen = false;

    details.style.overflow = "hidden";
    details.style.transition = "max-height 0.5s ease, opacity 0.5s ease";
    details.style.maxHeight = "0px";
    details.style.opacity = 0;

    button.addEventListener("click", (event) => {
        event.preventDefault();
        isOpen = !isOpen;

        if (isOpen) {
            details.style.maxHeight = details.scrollHeight + "px";
            details.style.opacity = 1;
        } else {
            details.style.maxHeight = "0px";
            details.style.opacity = 0;
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    seriesSelectorSetup();
    initUserAvatarDropdown();
    initShowcaseToggle();
});
