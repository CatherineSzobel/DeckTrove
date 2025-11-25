// =================== GAME RULES ENGINE ===================

const gameRules = {
    yugioh: {
        deckLimits: {
            mainDeck: { min: 40, max: 60 },
            extraDeck: { min: 0, max: 15 },
            sideDeck: { min: 0, max: 15 }
        },

        extraTypes: ["Fusion", "Synchro", "Xyz", "Link"],

        validate(deckZone, cardId, cardType, deckLimits, extraTypes) {
            const maxCards = deckLimits[deckZone.id].max;

            if (deckZone.children.length >= maxCards)
                return false;

            if (countCardCopies(deckZone, cardId, null) >= 3)
                return false;

            // Extra Deck restrictions
            if (deckZone.id === "extraDeck" &&
                !extraTypes.some(type => cardType.includes(type)))
                return false;

            // Main Deck cannot hold extra deck monsters
            if (deckZone.id === "mainDeck" &&
                extraTypes.some(type => cardType.includes(type)))
                return false;

            return true;
        }
    },

    magic: {
        deckLimits: {
            mainDeck: { min: 60, max: 60 },
            sideDeck: { min: 0, max: 15 }
        },

        extraTypes: [], // Magic does not use an extra deck

        validate(deckZone, cardId, cardType, deckLimits) {
            const maxCards = deckLimits[deckZone.id].max;

            const isBasicLand = cardType.toLowerCase().includes("basic");
            const maxCopies = isBasicLand ? 99 : 4;

            if (deckZone.children.length >= maxCards)
                return false;

            if (countCardCopies(deckZone, cardId, null) >= maxCopies)
                return false;

            return true;
        }
    }
};

// =================== HELPERS ===================

function countCardCopies(deckZone, cardId, movingCard) {
    const cards = deckZone.querySelectorAll(
        `.card-wrapper[data-card-id='${cardId}']`
    );
    let count = cards.length;

    if (movingCard && movingCard.parentElement === deckZone)
        count -= 1;

    return count;
}

function countCardCopiesAllDecks(cardId) {
    let count = 0;
    document.querySelectorAll(".dropzone .card-wrapper").forEach(card => {
        if (card.dataset.cardId === cardId) count++;
    });
    return count;
}

function flashInvalid(zone) {
    zone.classList.add("drop-invalid");
    setTimeout(() => zone.classList.remove("drop-invalid"), 800);
}

function highlightDeck(deckElement, limits) {
    const count = deckElement.children.length;
    deckElement.classList.remove("deck-invalid", "deck-valid");
    if (count < limits.min || count > limits.max)
        deckElement.classList.add("deck-invalid");
    else
        deckElement.classList.add("deck-valid");
}

function stripButtons(cardWrapper) {
    cardWrapper.querySelectorAll(".plus-btn, .minus-btn")
        .forEach(btn => btn.remove());
}

// =================== MAIN SCRIPT ===================

document.addEventListener("DOMContentLoaded", () => {

    const zones = document.querySelectorAll(".dropzone");

    const deckApp = document.getElementById("deckApp");
    const game = deckApp.dataset.game;

    // Load proper rule set
    const ruleSet = gameRules[game];
    const deckLimits = ruleSet.deckLimits;
    const extraDeckTypes = ruleSet.extraTypes;

    let activeDeckContainer = document.querySelector(".deckContainer");
    if (activeDeckContainer) {
        activeDeckContainer.classList.add("active");
        activeDeckContainer
            .querySelector("h2")
            .classList.replace("bg-white", "bg-red-500");
    }

    const spinner = document.getElementById("loadingSpinner");
    const cardContainer = document.querySelector(".search-pool");
    if (!cardContainer || !spinner) return;

    let currentPage = 1;
    let isLoading = false;
    let lastPage = false;

    // ---------------- RESET ----------------
    document.getElementById("resetButton").addEventListener("click", () => {
        document.querySelectorAll(".dropzone")
            .forEach(zone => (zone.innerHTML = ""));
        updateCounts();
        updateSearchPoolButtons();
        localStorage.setItem(
            "deckState",
            JSON.stringify({ mainDeck: [], extraDeck: [], sideDeck: [] })
        );
    });

    // ---------------- COUNTS & STATE ----------------
    function updateCounts() {
        const mainDeck = document.getElementById("mainDeck");
        const extraDeck = document.getElementById("extraDeck");
        const sideDeck = document.getElementById("sideDeck");

        document.getElementById("mainCount").textContent =
            mainDeck.children.length;
        document.getElementById("extraCount").textContent =
            extraDeck.children.length;
        document.getElementById("sideCount").textContent =
            sideDeck.children.length;

        if (deckLimits.mainDeck) highlightDeck(mainDeck, deckLimits.mainDeck);
        if (deckLimits.extraDeck) highlightDeck(extraDeck, deckLimits.extraDeck);
        if (deckLimits.sideDeck) highlightDeck(sideDeck, deckLimits.sideDeck);

        saveDeckState();
    }

    function updateSearchPoolButtons() {
        const activeDeck = activeDeckContainer?.querySelector(".dropzone");

        document.querySelectorAll(".search-pool .card-wrapper").forEach(wrapper => {
            const cardId = wrapper.dataset.cardId;
            const plusBtn = wrapper.querySelector(".plus-btn");
            const minusBtn = wrapper.querySelector(".minus-btn");

            const totalCopies = countCardCopiesAllDecks(cardId);
            const deckCopies = activeDeck
                ? countCardCopies(activeDeck, cardId)
                : 0;

            // Global limit — YGO = 3, Magic = 4 or 99 for basic lands
            plusBtn.disabled =
                !ruleSet.validate(activeDeck, cardId,
                    wrapper.querySelector("p")?.textContent ?? "Unknown",
                    deckLimits,
                    extraDeckTypes
                );

            plusBtn.style.opacity = plusBtn.disabled ? 0.5 : 1;
            minusBtn.disabled = deckCopies === 0;
        });
    }

    function saveDeckState() {
        const state = {
            mainDeck: Array.from(document.getElementById("mainDeck").children)
                .map(c => c.dataset.cardId),
            extraDeck: Array.from(document.getElementById("extraDeck").children)
                .map(c => c.dataset.cardId),
            sideDeck: Array.from(document.getElementById("sideDeck").children)
                .map(c => c.dataset.cardId)
        };

        document.getElementById("cardData").value = JSON.stringify(state);

        const mainValid =
            (!deckLimits.mainDeck ||
                (state.mainDeck.length >= deckLimits.mainDeck.min &&
                    state.mainDeck.length <= deckLimits.mainDeck.max));

        const extraValid =
            (!deckLimits.extraDeck ||
                state.extraDeck.length <= deckLimits.extraDeck.max);

        const sideValid =
            (!deckLimits.sideDeck ||
                state.sideDeck.length <= deckLimits.sideDeck.max);

        document.querySelector("#saveForm button").disabled = !(
            mainValid && extraValid && sideValid
        );

        localStorage.setItem("deckState", JSON.stringify(state));
    }

    function restoreDeckState() {
        let state =
            document.getElementById("cardData").value ||
            localStorage.getItem("deckState");
        if (!state) return;

        try {
            state = JSON.parse(state);
        } catch { return; }

        const deckMap = {
            mainDeck: document.getElementById("mainDeck"),
            extraDeck: document.getElementById("extraDeck"),
            sideDeck: document.getElementById("sideDeck")
        };

        Object.keys(deckMap).forEach(deckId => {
            const deckZone = deckMap[deckId];
            state[deckId]?.forEach(cardId => {
                const card = document.querySelector(
                    `.search-pool .card-wrapper[data-card-id='${cardId}']`
                );
                if (card) {
                    const clone = card.cloneNode(true);
                    stripButtons(clone);
                    clone.id = "card_" + Math.random().toString(36).slice(2);
                    deckZone.appendChild(clone);
                }
            });
        });
    }

    // ---------------- DECK SELECTION ----------------
    document.querySelectorAll(".deckContainer").forEach(deck => {
        deck.addEventListener("click", () => {
            document.querySelectorAll(".deckContainer").forEach(d => {
                d.classList.remove("active");
                d.querySelector("h2").classList.replace("bg-red-500", "bg-white");
            });
            deck.classList.add("active");
            deck.querySelector("h2").classList.replace("bg-white", "bg-red-500");
            activeDeckContainer = deck;
            updateSearchPoolButtons();
        });
    });

    // ---------------- PLUS / MINUS BUTTONS ----------------
    cardContainer.addEventListener("click", e => {
        const card = e.target.closest(".card-wrapper");
        if (!card) return;

        const plus = e.target.closest(".plus-btn");
        const minus = e.target.closest(".minus-btn");

        const activeDeck = activeDeckContainer?.querySelector(".dropzone");
        if (!activeDeck) return;

        const cardId = card.dataset.cardId;
        const cardType = card.querySelector("p")?.textContent ?? "Unknown";

        if (plus) {
            if (!ruleSet.validate(activeDeck, cardId, cardType, deckLimits, extraDeckTypes)) {
                flashInvalid(activeDeck);
                return;
            }

            const clone = card.cloneNode(true);
            stripButtons(clone);
            clone.id = "card_" + Math.random().toString(36).slice(2);
            activeDeck.appendChild(clone);

            updateCounts();
            updateSearchPoolButtons();
        }

        if (minus) {
            const target = Array.from(activeDeck.querySelectorAll(".card-wrapper"))
                .find(c => c.dataset.cardId === cardId);

            if (target) {
                activeDeck.removeChild(target);
                updateCounts();
                updateSearchPoolButtons();
            }
        }
    });

    // ---------------- DRAG & DROP ----------------
    document.addEventListener("dragstart", e => {
        const link = e.target.closest(".drag-link");
        if (!link) return;

        const wrapper = link.closest(".card-wrapper");
        if (!wrapper) return;

        if (!wrapper.id)
            wrapper.id = "card_" + Math.random().toString(36).slice(2);

        const cardId = wrapper.dataset.cardId;

        // Enforce global copy limit (3 YGO, 4 Magic)
        const cardType = wrapper.querySelector("p")?.textContent ?? "Unknown";

        if (!wrapper.closest(".dropzone")) {
            const activeDeck = activeDeckContainer?.querySelector(".dropzone");

            if (
                !ruleSet.validate(activeDeck, cardId, cardType, deckLimits, extraDeckTypes)
            ) {
                e.preventDefault();
                return;
            }
        }

        e.dataTransfer.setData("cardWrapperId", wrapper.id);
        e.dataTransfer.setData(
            "fromDeck",
            wrapper.closest(".dropzone") ? "1" : "0"
        );
        e.dataTransfer.setData("cardId", cardId);
        e.dataTransfer.setData("cardType", cardType);
    });

    zones.forEach(zone => {
        zone.addEventListener("dragenter", e => {
            e.preventDefault();
            zone.classList.add("drop-hover");
        });
        zone.addEventListener("dragover", e => e.preventDefault());
        zone.addEventListener("dragleave", () =>
            zone.classList.remove("drop-hover")
        );

        zone.addEventListener("drop", e => {
            e.preventDefault();
            zone.classList.remove("drop-hover");

            const wrapperId = e.dataTransfer.getData("cardWrapperId");
            const fromDeck = e.dataTransfer.getData("fromDeck") === "1";
            const cardId = e.dataTransfer.getData("cardId");
            const cardWrapper = document.getElementById(wrapperId);
            if (!cardWrapper) return;

            const cardType =
                cardWrapper.querySelector("p")?.textContent ?? "Unknown";

            if (!ruleSet.validate(zone, cardId, cardType, deckLimits, extraDeckTypes)) {
                flashInvalid(zone);
                return;
            }

            if (fromDeck) zone.appendChild(cardWrapper);
            else {
                const clone = cardWrapper.cloneNode(true);
                stripButtons(clone);
                clone.id = "card_" + Math.random().toString(36).slice(2);
                zone.appendChild(clone);
            }

            updateCounts();
            updateSearchPoolButtons();
        });
    });

    // ---------------- INFINITE SCROLL ----------------
    const loadNextPage = async () => {
        if (isLoading || lastPage) return;
        isLoading = true;
        spinner.classList.remove("hidden");
        currentPage++;

        try {
            const url = `/${game}/deck-builder?page=${currentPage}`;
            const response = await fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });

            if (!response.ok) throw new Error("Network error");

            const html = await response.text();
            if (!html.trim()) {
                lastPage = true;
                return;
            }

            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = html;

            const newCards = tempDiv.querySelectorAll(".card-wrapper");
            if (newCards.length === 0) lastPage = true;
            else newCards.forEach(card => cardContainer.appendChild(card));

            updateSearchPoolButtons();
        } catch (err) {
            console.error("Error loading next page:", err);
            currentPage--;
        } finally {
            spinner.classList.add("hidden");
            isLoading = false;
        }
    };

    cardContainer.addEventListener("scroll", () => {
        const scrollPosition =
            cardContainer.scrollTop + cardContainer.clientHeight;
        const threshold = cardContainer.scrollHeight - 100;
        if (scrollPosition >= threshold) loadNextPage();
    });

    // ---------------- INITIALIZE ----------------
    restoreDeckState();
    updateCounts();
    updateSearchPoolButtons();
});
