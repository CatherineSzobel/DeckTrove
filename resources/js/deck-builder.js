import {
    getActiveDeckZone,
    incrementCardCount,
    decrementCardCount,
    getCardDataFromWrapper,
    createDeckCard,
    enableSaveButton,
    isCardInDeck,
    setCoverImage,
    resetDecks,
    highlightActiveDeck,
    updateCounts,
    setActiveDeck,
    resetCoverImage
} from './deckbuilder/helpers.js';

import {
    flashInvalid,
    sanitizeHTML,
    validateCardPlacement,
    updateSearchPoolButtons
} from './deckbuilder/validation.js';

import {
    DECK_IDS,
    DeckBuilder,
    gameRules
} from './deckbuilder/constants.js';

import {
    initFilters,
    appendFilterParams
} from './filter.js';


const main = document.getElementById(DECK_IDS.main);
const extra = document.getElementById(DECK_IDS.extra);
const side = document.getElementById(DECK_IDS.side);
const decks = [main, extra, side];

const resetButton = document.getElementById("resetButton");
const coverDropArea = document.getElementById("coverDropArea");
const coverPreview = document.getElementById("coverPreview");
const deckImageInput = document.getElementById("deckImage");

const spinner = document.getElementById("loadingSpinner");
const searchInput = document.getElementById("searchInput");
const saveForm = document.getElementById("saveForm");

function refreshUI() {
    updateCounts();
    updateSearchPoolButtons(getActiveDeckZone(), document.querySelectorAll(".search-pool .card-wrapper"), DeckBuilder.state.ruleSet);
}
function handleCoverDrop(coverDropArea, coverPreview, deckImageInput, decks) {
    const hoverClasses = ["border-blue-500", "bg-blue-50"];
    const toggleHover = (event) => {
        event.preventDefault();
        event.stopPropagation();
        coverDropArea.classList.toggle(hoverClasses[0], event.type === "dragenter" || event.type === "dragover");
        coverDropArea.classList.toggle(hoverClasses[1], event.type === "dragenter" || event.type === "dragover");
    };

    ["dragenter", "dragover", "dragleave", "drop"].forEach(evt => coverDropArea.addEventListener(evt, toggleHover));

    coverDropArea.addEventListener("drop", (event) => {
        event.preventDefault();
        event.stopPropagation();
        const cardWrapper = document.getElementById(event.dataTransfer.getData("cardWrapperId"));
        if (!cardWrapper || !isCardInDeck(cardWrapper, decks)) return flashInvalid(coverDropArea, "Card must be in a deck");
        setCoverImage(cardWrapper, coverPreview, deckImageInput);
    });

    coverPreview.addEventListener("click", () => { coverPreview.src = ""; coverPreview.classList.add("hidden"); deckImageInput.value = ""; });
}

function handlePlusMin(cardContainer) {
    cardContainer.addEventListener("click", async (event) => {
        const card = event.target.closest(".card-wrapper");
        if (!card) return;

        const plus = event.target.closest(".plus-btn");
        const minus = event.target.closest(".minus-btn");
        const active = getActiveDeckZone();
        if (!active) return;

        const cardId = card.dataset.cardId;
        const cardType = card.querySelector("p")?.textContent ?? "Unknown";

        if (plus) {
            const validation = validateCardPlacement(DeckBuilder.state.ruleSet, active, cardId, cardType);
            if (!validation.valid) return flashInvalid(active, validation.reason);

            const cardData = getCardDataFromWrapper(card);
            const deckCard = createDeckCard(cardData);

            active.appendChild(deckCard);
            incrementCardCount(cardData.id);

        }

        if (minus) {
            const target = Array.from(active.children).find(c => c.dataset.cardId === cardId);
            if (target) {
                active.removeChild(target);
                decrementCardCount(cardId);
            }
        }
        refreshUI();
    });
}
function enableClickToRemoveFromDeck() {
    document.addEventListener("click", (event) => {
        if (event.target.closest(".plus-btn, .minus-btn, a")) return;

        const card = event.target.closest(".card-wrapper");
        if (!card) return;
        const deckZone = card.closest(".dropzone");
        if (!deckZone) return;

        deckZone.removeChild(card);
        decrementCardCount(card.dataset.cardId);
        refreshUI();
    });
}

function handleDragAndDrop() {
    document.addEventListener("dragstart", (event) => {
        const wrapper = event.target.closest(".card-wrapper");
        if (!wrapper) return;
        if (!wrapper.id) wrapper.id = "card_" + Math.random().toString(36).slice(2);

        event.dataTransfer.setData("cardWrapperId", wrapper.id);
        event.dataTransfer.setData(
            "fromDeckId",
            wrapper.closest(".dropzone")?.id || "searchPool"
        );
        event.dataTransfer.setData("cardId", wrapper.dataset.cardId);
        event.dataTransfer.setData(
            "cardType",
            wrapper.dataset.cardType ||
            wrapper.querySelector("p")?.textContent ||
            "Unknown"
        );
    });

    DeckBuilder.state.zones.forEach((zone) => {
        zone.addEventListener("dragenter", (event) => {
            event.preventDefault();
            zone.classList.add("drop-hover");
        });

        zone.addEventListener("dragover", (event) => event.preventDefault());
        zone.addEventListener("dragleave", () => zone.classList.remove("drop-hover"));

        zone.addEventListener("drop", (event) => {
            event.preventDefault();
            zone.classList.remove("drop-hover");

            const wrapperId = event.dataTransfer.getData("cardWrapperId");
            const fromDeckId = event.dataTransfer.getData("fromDeckId");
            const cardId = event.dataTransfer.getData("cardId");
            const cardType = event.dataTransfer.getData("cardType");

            const cardWrapper = document.getElementById(wrapperId);
            if (!cardWrapper) return;

            const target = zone;
            const from = DeckBuilder.state.zones.find((z) => z.id === fromDeckId);
            const movingCard = from && from !== target ? cardWrapper : null;

            const validation = validateCardPlacement(DeckBuilder.state.ruleSet, target, cardId, cardType, movingCard);
            if (!validation.valid) return flashInvalid(target, validation.reason);
            if (fromDeckId === "searchPool") {
                const cardData = getCardDataFromWrapper(cardWrapper);
                const deckCard = createDeckCard(cardData);
                target.appendChild(deckCard);
                incrementCardCount(cardData.id);

            } else if (from && from !== target) {
                from.removeChild(cardWrapper);
                target.appendChild(cardWrapper);
                decrementCardCount(cardId);
                incrementCardCount(cardId);
            }
            refreshUI();
        });
    });
}
function loadExistingDeckCards(zones) {
    zones.forEach(zoneId => {
        const zone = document.getElementById(zoneId);
        if (!zone) return;

        Array.from(zone.children).forEach(card => {
            const cardId = card.dataset.cardId;
            incrementCardCount(cardId);
        });
    });
    console.log(zones);
    refreshUI();
}
function loadNextPage(
    cardContainer,
    spinner,
    currentPageRef,
    currentSearchRef,
    lastPageRef,
    game
) {
    return async function () {
        if (currentPageRef.value.loading || lastPageRef.value) return;

        currentPageRef.value.loading = true;
        spinner.classList.remove("hidden");

        try {
            const pageToLoad = currentPageRef.value.number + 1;

            const params = new URLSearchParams({ page: pageToLoad });
            if (currentSearchRef.value)
                params.append("search", currentSearchRef.value);

            appendFilterParams(params);

            const url = `/${game}/deck-builder?${params.toString()}`;
            const response = await fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });

            if (!response.ok) throw new Error("Network error");

            const html = await response.text();

            if (!html.trim()) {
                lastPageRef.value = true;
                return;
            }

            const fragment = sanitizeHTML(html);
            const newCards = fragment.querySelectorAll(".card-wrapper");

            if (newCards.length === 0) lastPageRef.value = true;
            else newCards.forEach((card) => cardContainer.appendChild(card));

            currentPageRef.value.number = pageToLoad;

            updateSearchPoolButtons();
        } catch (err) {
            console.error("Error loading next page:", err);
        } finally {
            spinner.classList.add("hidden");
            currentPageRef.value.loading = false;
        }
    };
}

function handleSearchInput(searchInput, cardContainer, currentPageRef, currentSearchRef, lastPageRef, loadNextPageFn) {
    searchInput.addEventListener("keypress", (event) => {
        if (event.key !== "Enter") return;

        event.preventDefault();
        currentSearchRef.value = searchInput.value.trim();
        currentPageRef.value.number = 0;
        lastPageRef.value = false;
        cardContainer.innerHTML = "";
        loadNextPageFn();

    });
}

function handleInfiniteScroll(cardContainer, loadNextPageFn, currentPageRef) {
    let ticking = false;
    cardContainer.addEventListener("scroll", () => {
        if (ticking) return;

        ticking = true;
        requestAnimationFrame(() => {
            if (cardContainer.scrollTop + cardContainer.clientHeight >= cardContainer.scrollHeight - 100) loadNextPageFn();
            ticking = false;
        });
    });

    const cardsPerPage = 20;
    currentPageRef.value.number = Math.floor(cardContainer.children.length / cardsPerPage);
}
function saveDeckData(zones) {
    const deckTitleInput = document.getElementById("deckTitleInput");
    const deckDescInput = document.getElementById("deckDescInput");
    const deckImageInput = document.getElementById("deckImage");
    const cardsInput = document.getElementById("cards");
    const deckTitleHidden = document.getElementById("deckTitle");
    const deckDescHidden = document.getElementById("deckDescription");
    const isPublicCheckbox = document.getElementById("isPublicCheckbox");
    const isPublicHidden = document.getElementById("isPublic");

    const state = {};
    zones.forEach((zoneId) => {
        const list = document.getElementById(zoneId);
        if (!list) return;

        state[zoneId] = Array.from(list.children).map((card) => ({
            id: card.dataset.cardId,
            name: card.dataset.cardName,
            image_url: card.dataset.cardImage,
            type: card.dataset.cardType,
            race: card.dataset.cardRace,
            desc: card.dataset.cardDesc,
        }));
    });

    deckTitleHidden.value = deckTitleInput.value.trim();
    deckDescHidden.value = deckDescInput.value.trim();
    cardsInput.value = JSON.stringify(state);
    deckImageInput.value = deckImageInput.value || "";
    isPublicHidden.value = isPublicCheckbox.checked ? 1 : 0;

    return state.main && state.main.length >= 40;
}

function handleSaveForm(saveForm, zones) {
    if (!saveForm) return;

    const modal = document.getElementById("deckWarningModal");
    const cancelBtn = document.getElementById("cancelSaveBtn");
    const confirmBtn = document.getElementById("confirmSaveBtn");

    let isSubmitting = false;

    saveForm.addEventListener("submit", function (event) {
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        const isValid = saveDeckData(zones);

        if (!isValid && document.getElementById("isPublicCheckbox").checked) {
            event.preventDefault();
            if (modal) modal.classList.remove("hidden");
            return;
        }
        disableSubmit();
        isSubmitting = true;
    });

    if (cancelBtn) cancelBtn.addEventListener("click", () => modal.classList.add("hidden"));
    if (confirmBtn) {
        confirmBtn.addEventListener("click", () => {
            saveDeckData(zones);
            isSubmitting = true;
            disableSubmit();
            modal.classList.add("hidden");
            saveForm.submit();
        });
    }
}

function disableSubmit() {
    const confirmBtn = document.getElementById("confirmSaveBtn");
    if (confirmBtn) confirmBtn.disabled = true;

    const submitBtn = document.querySelector('#saveForm [type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = "Saving...";
    }
}

function setupDeck(cardContainer, decks) {
    handleCoverDrop(coverDropArea, coverPreview, deckImageInput, decks);
    highlightActiveDeck();
    handlePlusMin(cardContainer);
    handleDragAndDrop();
    enableClickToRemoveFromDeck();

    const defaultDeck = document.querySelector(".deckContainer");
    if (defaultDeck) setActiveDeck(defaultDeck);

    if (resetButton) {
        resetButton.addEventListener("click", () => {
            resetDecks(decks);
            resetCoverImage(coverPreview, deckImageInput);
            refreshUI();
        });
    }

    refreshUI();
}

function reloadSearchWithFilters(
    cardContainer,
    loadNextPageFn,
    currentPageRef,
    lastPageRef
) {
    currentPageRef.value.number = 0;
    currentPageRef.value.loading = false;
    lastPageRef.value = false;

    cardContainer.innerHTML = "";
    loadNextPageFn();
}


function setupSearch(cardContainer) {
    const currentPageRef = { value: { number: 0, loading: false } };
    const currentSearchRef = { value: "" };
    const lastPageRef = { value: false };

    const loadNextPageFn = loadNextPage(
        cardContainer,
        spinner,
        currentPageRef,
        currentSearchRef,
        lastPageRef,
        DeckBuilder.state.game
    );

    if (searchInput)
        handleSearchInput(
            searchInput,
            cardContainer,
            currentPageRef,
            currentSearchRef,
            lastPageRef,
            loadNextPageFn
        );

    handleInfiniteScroll(cardContainer, loadNextPageFn, currentPageRef);

    initFilters({
        selects: document.querySelectorAll(".filter-select"),
        button: document.getElementById("filter-button"),
        details: document.getElementById("filterDetails"),
        clearButton: document.getElementById("clear-filter-button"),
        onChange: () =>
            reloadSearchWithFilters(
                cardContainer,
                loadNextPageFn,
                currentPageRef,
                currentSearchRef,
                lastPageRef
            ),
    });
}


document.addEventListener("DOMContentLoaded", () => {

    const deckApp = document.getElementById("deckApp");
    const mode = deckApp?.dataset.mode || "create";
    if (!deckApp) return;

    DeckBuilder.state.game = deckApp.dataset.game;
    if (!DeckBuilder.state.game || !gameRules[DeckBuilder.state.game]) return;

    DeckBuilder.state.ruleSet = gameRules[DeckBuilder.state.game];
    DeckBuilder.state.deckLimits = DeckBuilder.state.ruleSet.deckLimits;
    DeckBuilder.state.zones = Array.from(document.querySelectorAll(".dropzone"));

    const cardContainer = document.querySelector(".search-pool");
    if (!cardContainer) return;

    setupDeck(cardContainer, decks);
    loadExistingDeckCards(["main", "extra", "side"]);
    setupSearch(cardContainer);

    if (saveForm)
        handleSaveForm(saveForm, ["main", "extra", "side"]);
});