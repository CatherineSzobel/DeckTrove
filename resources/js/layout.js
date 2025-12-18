const dashboardLinks = document.querySelectorAll(".dashboard-link");
const seriesList = ["yugioh", "magic", "pokemon"]; // Add more if needed

async function urlExists(url) {
    try {
        const response = await fetch(url, { method: "HEAD" });
        return response.ok;
    } catch (err) {
        console.error("Error checking URL:", err);
        return false;
    }
}

function seriesSelectorSetup() {
    const selectors = document.querySelectorAll(".series-selector");
    const dashboardLogos = document.querySelectorAll(".dashboard-logo");
    let selectedSeries = localStorage.getItem("selectedSeries");

    // Redirect to homepage if no series selected and not on homepage
    if (!selectedSeries && window.location.pathname !== "/") {
        window.location.href = "/";
        return;
    }

    selectors.forEach(async (selector) => {
        if (selectedSeries) selector.value = selectedSeries;

        const buttonText = selector
            .closest(".group")
            ?.querySelector(".viewtype_text");
        if (buttonText && selectedSeries) {
            buttonText.textContent =
                selectedSeries.charAt(0).toUpperCase() +
                selectedSeries.slice(1);
        }

        // Disable invalid options
        for (const option of selector.options) {
            const url = `/${option.value}/cards`;
            const exists = await urlExists(url);
            option.disabled = !exists;
            if (!exists && option.value === selector.value) {
                // If the current selected series doesn't exist, show a warning
                if (buttonText) buttonText.textContent = "Unavailable";
            }
        }

        selector.addEventListener("change", async () => {
            const series = selector.value;
            if (!series) {
                localStorage.removeItem("selectedSeries");
                window.location.href = "/";
                return;
            }

            const url = `/${series}/cards`;
            const exists = await urlExists(url);
            if (!exists) {
                alert("This series page does not exist."); // Optional user feedback
                return; // Do not redirect
            }

            localStorage.setItem("selectedSeries", series);
            updateLinks(series);

            if (buttonText) {
                buttonText.textContent =
                    series.charAt(0).toUpperCase() + series.slice(1);
            }

            window.location.href = url;
        });
    });

    dashboardLogos.forEach((img) => {
        img.addEventListener("click", async () => {
            const series = img.dataset.series;
            if (!series) return;

            const url = `/${series}/cards`;
          
            localStorage.setItem("selectedSeries", series);
            updateLinks(series);
            window.location.href = url;
        });
    });

    if (selectedSeries) updateLinks(selectedSeries);
}

// Utility to clean URLs from existing series prefix
function cleanPath(path) {
    const pattern = new RegExp(`^/(${seriesList.join("|")})(/|$)`);
    return path.replace(pattern, "/");
}

// Update all dashboard/nav links with selected series
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

    // Toggle menu on click
    btn.addEventListener("click", (e) => {
        e.preventDefault();
        menu.classList.toggle("hidden");
    });

    // Close menu if click outside
    document.addEventListener("click", (e) => {
        if (!container.contains(e.target)) {
            menu.classList.add("hidden");
        }
    });
}

// Initialize on DOM load
document.addEventListener("DOMContentLoaded", () => {
    seriesSelectorSetup();
    initUserAvatarDropdown();
    const showcaseButton = document.getElementById("showcase_button");
    const showcaseDetails = document.getElementById("showcaseDetails");

    if (showcaseButton && showcaseDetails) {
        // Prepare for smooth height transition
        showcaseDetails.style.maxHeight = "0px";
        showcaseDetails.style.overflow = "hidden";
        showcaseDetails.style.transition =
            "max-height 0.5s ease, opacity 0.5s ease";
        showcaseDetails.style.opacity = 0;

        showcaseButton.addEventListener("click", (e) => {
            e.preventDefault();

            if (showcaseDetails.style.maxHeight === "0px") {
                // Expand dynamically based on content
                showcaseDetails.style.maxHeight =
                    showcaseDetails.scrollHeight + "px";
                showcaseDetails.style.opacity = 1;
            } else {
                // Collapse
                showcaseDetails.style.maxHeight = "0px";
                showcaseDetails.style.opacity = 0;
            }
        });
    }
});
