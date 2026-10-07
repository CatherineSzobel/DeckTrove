/**
 * Deck builder: add cards with +/- or drag and drop, search with infinite scroll, and save.
 * Used both for new decks and for editing (resources/views/decks/deck-builder.blade.php).
 */
import { validatePlacement } from "./deckbuilder/rules.js";
import { collectDeck, createDeckCard, flashInvalid, refreshSearchPool, updateCounts } from "./deckbuilder/dom.js";
import { initFilters, appendFilterParams } from "./filter.js";

const app = document.getElementById("deckApp");

if (app) {
    const rules = JSON.parse(app.dataset.rules);
    const zones = Object.keys(rules.zones).map((id) => document.getElementById(id)).filter(Boolean);
    const searchPool = document.querySelector(".search-pool");
    const coverPreview = document.getElementById("coverPreview");
    const deckImageInput = document.getElementById("deckImage");
    const saveForm = document.getElementById("saveForm");

    let activeZone = document.querySelector(".deckContainer.deck-active .dropzone") ?? zones[0];
    let dirty = false;

    const refresh = () => {
        updateCounts(rules);
        refreshSearchPool(rules, activeZone);
    };

    const changed = () => {
        dirty = true;
        refresh();
    };

    // --- Zones -------------------------------------------------------------------------------

    function setActiveZone(container) {
        document.querySelectorAll(".deckContainer").forEach((c) => c.classList.toggle("deck-active", c === container));
        activeZone = container.querySelector(".dropzone");
        refreshSearchPool(rules, activeZone);
    }

    function addCard(zone, source) {
        const validation = validatePlacement(rules, zone, source);
        if (!validation.valid) return flashInvalid(zone, validation.reason);

        zone.appendChild(createDeckCard(source));
        changed();
    }

    function moveCard(card, from, to) {
        if (from === to) return;

        const validation = validatePlacement(rules, to, card, from);
        if (!validation.valid) return flashInvalid(to, validation.reason);

        to.appendChild(card);
        changed();
    }

    document.querySelectorAll(".deckContainer").forEach((container) =>
        container.addEventListener("click", () => setActiveZone(container))
    );

    // Clicking a card inside a deck zone removes it.
    zones.forEach((zone) =>
        zone.addEventListener("click", (event) => {
            const card = event.target.closest(".card-wrapper");
            if (!card) return;
            card.remove();
            changed();
        })
    );

    // --- Search results: +/- buttons ---------------------------------------------------------

    searchPool.addEventListener("click", (event) => {
        const card = event.target.closest(".card-wrapper");
        if (!card || !activeZone) return;

        if (event.target.closest(".plus-btn")) {
            addCard(activeZone, card);
        } else if (event.target.closest(".minus-btn")) {
            const copy = activeZone.querySelector(`.card-wrapper[data-card-id="${CSS.escape(card.dataset.cardId)}"]`);
            if (copy) {
                copy.remove();
                changed();
            }
        }
    });

    // --- Drag and drop -----------------------------------------------------------------------

    let dragged = null;

    document.addEventListener("dragstart", (event) => {
        dragged = event.target.closest?.(".card-wrapper") ?? null;
        if (dragged) event.dataTransfer.effectAllowed = "copyMove";
    });
    document.addEventListener("dragend", () => (dragged = null));

    zones.forEach((zone) => {
        zone.addEventListener("dragover", (event) => {
            if (!dragged) return;
            event.preventDefault();
            zone.classList.add("drop-hover");
        });
        zone.addEventListener("dragleave", () => zone.classList.remove("drop-hover"));
        zone.addEventListener("drop", (event) => {
            event.preventDefault();
            zone.classList.remove("drop-hover");
            if (!dragged) return;

            const from = dragged.closest(".dropzone");
            from ? moveCard(dragged, from, zone) : addCard(zone, dragged);
        });
    });

    // --- Cover image -------------------------------------------------------------------------

    const coverDropArea = document.getElementById("coverDropArea");

    function setCover(src) {
        coverPreview.src = src;
        coverPreview.classList.toggle("hidden", !src);
        deckImageInput.value = src;
        dirty = true;
    }

    coverDropArea.addEventListener("dragover", (event) => {
        if (!dragged) return;
        event.preventDefault();
        coverDropArea.classList.add("border-blue-500", "bg-blue-50");
    });
    coverDropArea.addEventListener("dragleave", () => coverDropArea.classList.remove("border-blue-500", "bg-blue-50"));
    coverDropArea.addEventListener("drop", (event) => {
        event.preventDefault();
        coverDropArea.classList.remove("border-blue-500", "bg-blue-50");
        if (dragged) setCover(dragged.dataset.cardImage);
    });
    coverPreview.addEventListener("click", () => setCover(""));

    document.getElementById("resetButton").addEventListener("click", () => {
        if (!window.confirm("Remove all cards and the cover from this deck?")) return;
        zones.forEach((zone) => zone.replaceChildren());
        setCover("");
        changed();
    });

    // --- Search with infinite scroll ---------------------------------------------------------

    const searchInput = document.getElementById("searchInput");
    const spinner = document.getElementById("loadingSpinner");
    const resultCount = document.getElementById("result-count");
    let nextPage = app.dataset.nextPage ? Number(app.dataset.nextPage) : null;
    let loading = false;

    async function loadPage(page, { replace = false } = {}) {
        if (loading) return;
        loading = true;
        spinner.classList.remove("hidden");

        const params = new URLSearchParams({ page });
        if (searchInput.value.trim()) params.set("search", searchInput.value.trim());
        appendFilterParams(params);

        try {
            const response = await fetch(`${app.dataset.searchUrl}?${params}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const template = document.createElement("template");
            template.innerHTML = await response.text();

            if (replace) searchPool.replaceChildren();
            searchPool.append(template.content);

            nextPage = response.headers.get("X-Next-Page") ? Number(response.headers.get("X-Next-Page")) : null;
            if (replace && resultCount) {
                const total = Number(response.headers.get("X-Total") || 0);
                resultCount.textContent = total ? `${total.toLocaleString()} cards found` : "No cards found";
            }

            refreshSearchPool(rules, activeZone);
        } catch (error) {
            console.error("Error loading cards:", error);
        } finally {
            spinner.classList.add("hidden");
            loading = false;
        }
    }

    const newSearch = () => {
        searchPool.scrollTop = 0;
        loadPage(1, { replace: true });
    };

    searchInput.addEventListener("keydown", (event) => {
        if (event.key !== "Enter") return;
        event.preventDefault();
        newSearch();
    });

    searchPool.addEventListener("scroll", () => {
        const nearBottom = searchPool.scrollTop + searchPool.clientHeight >= searchPool.scrollHeight - 150;
        if (nearBottom && nextPage) loadPage(nextPage);
    });

    initFilters({
        selects: document.querySelectorAll(".filter-select"),
        button: document.getElementById("filter-button"),
        details: document.getElementById("filterDetails"),
        clearButton: document.getElementById("clear-filter-button"),
        onChange: newSearch,
    });

    // --- Saving ------------------------------------------------------------------------------

    const modal = document.getElementById("deckWarningModal");
    const isPublicCheckbox = document.getElementById("isPublicCheckbox");
    isPublicCheckbox.addEventListener("change", () => (dirty = true));
    ["deckTitleInput", "deckDescInput"].forEach((id) =>
        document.getElementById(id).addEventListener("input", () => (dirty = true))
    );

    function submitDeck(isPublic) {
        document.getElementById("cards").value = JSON.stringify(collectDeck(rules));
        document.getElementById("deckTitle").value = document.getElementById("deckTitleInput").value.trim();
        document.getElementById("deckDescription").value = document.getElementById("deckDescInput").value.trim();
        document.getElementById("isPublic").value = isPublic ? "1" : "0";

        const button = saveForm.querySelector('[type="submit"]');
        button.disabled = true;
        button.textContent = "Saving...";

        dirty = false;
        saveForm.submit();
    }

    saveForm.addEventListener("submit", (event) => {
        event.preventDefault();

        const mainCount = document.getElementById("main")?.children.length ?? 0;
        if (isPublicCheckbox.checked && mainCount < rules.zones.main.min) {
            modal.classList.remove("hidden");
            return;
        }

        submitDeck(isPublicCheckbox.checked);
    });

    document.getElementById("cancelSaveBtn").addEventListener("click", () => modal.classList.add("hidden"));
    document.getElementById("confirmSaveBtn").addEventListener("click", () => {
        modal.classList.add("hidden");
        isPublicCheckbox.checked = false;
        submitDeck(false);
    });

    window.addEventListener("beforeunload", (event) => {
        if (dirty) event.preventDefault();
    });

    refresh();
}
