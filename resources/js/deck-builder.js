// =================== GAME RULES ENGINE ===================

const gameRules = {
    yugioh: {
        deckLimits: {
            mainDeck: { min: 40, max: 60 },
            extraDeck: { min: 0, max: 15 },
            sideDeck: { min: 0, max: 15 },
        },

        extraTypes: ["Fusion", "Synchro", "Xyz", "Link"],

        validate(deckZone, cardId, cardType, deckLimits, extraTypes) {
            const maxCards = deckLimits[deckZone.id].max;

            if (deckZone.children.length >= maxCards) return false;

            if (countCardCopies(deckZone, cardId, null) >= 3) return false;

            // Extra Deck restrictions
            if (deckZone.id === "extraDeck" &&
                !extraTypes.some(type => cardType.includes(type)))
                return false;

            // Main Deck cannot hold extra deck monsters
            if (deckZone.id === "mainDeck" &&
                extraTypes.some(type => cardType.includes(type)))
                return false;

            return true;
        },
    },
    magic: {
        deckLimits: {
            mainDeck: { min: 60, max: 60 },
            sideDeck: { min: 0, max: 15 },
        },

        extraTypes: [], // Magic does not use an extra deck

        validate(deckZone, cardId, cardType, deckLimits) {
            const maxCards = deckLimits[deckZone.id].max;

            const isBasicLand = cardType.toLowerCase().includes("basic");
            const maxCopies = isBasicLand ? 99 : 4;

            if (deckZone.children.length >= maxCards) return false;

            if (countCardCopies(deckZone, cardId, null) >= maxCopies)
                return false;

            return true;
        },
    },
};

// =================== HELPERS ===================
function countCardCopies(deckZone, cardId, movingCard) {
    const cards = deckZone.querySelectorAll(
        `.card-wrapper[data-card-id='${cardId}']`
    );
    let count = cards.length;
    if (movingCard && movingCard.parentElement === deckZone) count--;
    return count;
}

function countCardCopiesAllDecks(cardId) {
    let count = 0;
    document.querySelectorAll(".dropzone .card-wrapper").forEach((card) => {
        if (card.dataset.cardId === cardId) count++;
    });
    return count;
}

// =================== VALIDATION ===================
function validateCardPlacement(
    ruleSet,
    deckZone,
    cardId,
    cardType,
    movingCard = null
) {
    if (!deckZone) return false;
    const limits = ruleSet.deckLimits[deckZone.id] || {};

    // Check deck max size
    if (deckZone.children.length >= (limits.max || Infinity)) return false;

    // Determine max copies
    let maxCopies =
        ruleSet.extraTypes.length === 0
            ? cardType.toLowerCase().includes("basic")
                ? 99
                : 4
            : 3;

    // Global check across all decks
    let totalCopies = countCardCopiesAllDecks(cardId);

    // If the card is being moved from a deck, ignore it in the count
    if (movingCard && movingCard.parentElement) totalCopies--;

    if (totalCopies >= maxCopies) return false;

    // Extra deck restrictions
    if (
        deckZone.id === "extraDeck" &&
        ruleSet.extraTypes.length &&
        !ruleSet.extraTypes.some((t) => cardType.includes(t))
    )
        return false;

    // Main deck cannot hold extra deck monsters
    if (
        deckZone.id === "mainDeck" &&
        ruleSet.extraTypes.length &&
        ruleSet.extraTypes.some((t) => cardType.includes(t))
    )
        return false;

    // Side deck can hold any non-extra cards (counts toward global limit)
    return true;
}

function flashInvalid(zone) {
    zone.classList.add("drop-invalid");
    setTimeout(() => zone.classList.remove("drop-invalid"), 800);
}

function highlightDeck(deckElement, limits) {
    if (!deckElement || !limits) return;
    const count = deckElement.children.length;
    deckElement.classList.remove("deck-invalid", "deck-valid");
    if (count < limits.min || count > limits.max)
        deckElement.classList.add("deck-invalid");
    else deckElement.classList.add("deck-valid");
}

function stripButtons(cardWrapper) {
    cardWrapper
        .querySelectorAll(".plus-btn, .minus-btn")
        .forEach((btn) => btn.remove());
}

// =================== DECK STORAGE ===================
const DeckStorage = {
    save(game, state) {
        switch (game) {
            case "yugioh":
                localStorage.setItem(
                    `deckState_${game}`,
                    JSON.stringify(state)
                );
            case "magic":
                break;
            default:
                console.warn(`DeckStorage: Unsupported game "${game}"`);
                return;
        }
    },
    load(game) {
        const raw = localStorage.getItem(`deckState_${game}`);
        if (!raw) return null;
        try {
            switch (game) {
                case "yugioh":
                    return JSON.parse(raw);
                case "magic":
                    break;
                default:
                    console.warn(`DeckStorage: Unsupported game "${game}"`);
                    return null;
            }
        } catch {
            return null;
        }
    },
};

// =================== MAIN SCRIPT ===================
document.addEventListener("DOMContentLoaded", () => {
    const zones = document.querySelectorAll(".dropzone");

    const deckApp = document.getElementById("deckApp");
    if (!deckApp) return;

    const game = deckApp.dataset.game;
    if (!game || !gameRules[game]) return;

    const ruleSet = gameRules[game];
    const deckLimits = ruleSet.deckLimits;
    const extraDeckTypes = ruleSet.extraTypes;

    const cardContainer = document.querySelector(".search-pool");
    const spinner = document.getElementById("loadingSpinner");
    if (!cardContainer || !spinner) return;

    let activeDeckContainer =
        document.querySelector(".deckContainer.active") ||
        document.querySelector(".deckContainer");
    if (activeDeckContainer) {
        activeDeckContainer.classList.add("active");
        activeDeckContainer
            .querySelector("h2")
            ?.classList.replace("bg-white", "bg-red-500");
    }

    let currentPage = 1;
    let currentSearch = "";
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
        if (!activeDeck) return;

        document.querySelectorAll(".search-pool .card-wrapper").forEach(wrapper => {
                const cardId = wrapper.dataset.cardId;
                const cardType =
                    wrapper.querySelector("p")?.textContent ?? "Unknown";
                const plusBtn = wrapper.querySelector(".plus-btn");
                const minusBtn = wrapper.querySelector(".minus-btn");

                const deckCopies = activeDeck
                    ? countCardCopies(activeDeck, cardId)
                    : 0;

                // Global limit — YGO = 3, Magic = 4 or 99 for basic lands
                plusBtn.disabled = !validateCardPlacement(
                    ruleSet,
                    activeDeck,
                    cardId,
                    cardType
                );

                plusBtn.style.opacity = plusBtn.disabled ? 0.5 : 1;
                minusBtn.disabled = deckCopies === 0;
            });
    }

    // ---------------- SAVE DECK STATE ----------------
    async function saveDeckState() {
        const state = {};
        Object.keys(deckLimits).forEach((deckId) => {
            const deck = document.getElementById(deckId);
            state[deckId] = deck
                ? Array.from(deck.children).map((c) => ({
                      id: c.dataset.cardId,
                      name: c.querySelector("h3")?.textContent ?? "Unknown",
                      type: c.querySelector("p")?.textContent ?? "Unknown",
                      image: c.querySelector("img")?.src ?? null,
                      desc: c.querySelector("div > p")?.textContent ?? null,
                  }))
                : [];
        });
        DeckStorage.save(game, state);
    }

    // ---------------- RESTORE DECK STATE ----------------
    function restoreDeckState() {
        const state = DeckStorage.load(game);
        if (!state) return;

        Object.keys(state).forEach((deckId) => {
            const deckZone = document.getElementById(deckId);
            if (!deckZone) return;

            state[deckId].forEach((cardData) => {
                // Try to find the card in search pool first
                let card = document.querySelector(
                    `.search-pool .card-wrapper[data-card-id='${cardData.id}']`
                );

                // If not found, create a new card element using the deck layout (no plus/minus)
                if (!card) {
                    card = document.createElement("div");
                    card.classList.add(
                        "relative",
                        "w-32",
                        "h-44",
                        "mb-4",
                        "mx-auto",
                        "group",
                        "card-wrapper",
                        "cursor-pointer"
                    );
                    card.dataset.cardId = cardData.id;

                    // Card Image
                    const img = document.createElement("img");
                    img.src = cardData.image ?? ""; // you may need to store image URL in saved state
                    img.alt = cardData.name;
                    img.classList.add(
                        "w-full",
                        "h-full",
                        "object-cover",
                        "rounded",
                        "card"
                    );
                    card.appendChild(img);

                    // Hover overlay
                    const overlay = document.createElement("div");
                    overlay.classList.add(
                        "absolute",
                        "inset-0",
                        "bg-black",
                        "bg-opacity-70",
                        "text-white",
                        "opacity-0",
                        "group-hover:opacity-100",
                        "transition-opacity",
                        "rounded",
                        "p-2",
                        "flex",
                        "flex-col",
                        "justify-center",
                        "items-center",
                        "text-center",
                        "pointer-events-auto"
                    );

                    const link = document.createElement("a");
                    link.href = cardData.url ?? "#";
                    link.classList.add(
                        "drag-link",
                        "pointer-events-auto",
                        "text-white"
                    );
                    link.draggable = true;
                    link.dataset.cardId = cardData.id;
                    link.dataset.cardName = cardData.name;
                    link.dataset.cardImage = cardData.image ?? "";
                    link.dataset.cardType = cardData.type ?? "";
                    link.dataset.cardRace = cardData.race ?? "";
                    link.dataset.cardDesc = cardData.desc ?? "";

                    const title = document.createElement("h3");
                    title.classList.add("font-bold", "text-xs");
                    title.textContent = cardData.name;
                    link.appendChild(title);

                    overlay.appendChild(link);

                    const typeText = document.createElement("p");
                    typeText.classList.add(
                        "text-[10px]",
                        "mt-1",
                        "pointer-events-none"
                    );
                    typeText.textContent = cardData.type ?? "Unknown";
                    overlay.appendChild(typeText);

                    card.appendChild(overlay);

                    // Optional: description box
                    const descBox = document.createElement("div");
                    descBox.classList.add(
                        "absolute",
                        "top-0",
                        "left-full",
                        "ml-2",
                        "w-48",
                        "bg-black",
                        "bg-opacity-80",
                        "text-white",
                        "text-xs",
                        "p-2",
                        "rounded",
                        "opacity-0",
                        "group-hover:opacity-100",
                        "transition-opacity",
                        "z-10",
                        "pointer-events-none"
                    );
                    const descText = document.createElement("p");
                    descText.textContent =
                        cardData.desc ?? "No description available";
                    descBox.appendChild(descText);

                    card.appendChild(descBox);
                }

                const clone = card.cloneNode(true);
                stripButtons(clone);
                clone.id = "card_" + Math.random().toString(36).slice(2);
                deckZone.appendChild(clone);
            });
        });

        updateCounts();
        updateSearchPoolButtons();
    }

    // ---------------- DECK SELECTION ----------------
    document.querySelectorAll(".deckContainer").forEach((deck) => {
        deck.addEventListener("click", () => {
            document.querySelectorAll(".deckContainer").forEach((d) => {
                d.classList.remove("active");
                d.querySelector("h2")?.classList.replace(
                    "bg-red-500",
                    "bg-white"
                );
            });
            deck.classList.add("active");
            deck.querySelector("h2")?.classList.replace(
                "bg-white",
                "bg-red-500"
            );
            activeDeckContainer = deck;
            updateSearchPoolButtons();
        });
    });

    // ---------------- PLUS / MINUS ----------------
    cardContainer.addEventListener("click", async (e) => {
        const card = e.target.closest(".card-wrapper");
        if (!card) return;

        const plus = e.target.closest(".plus-btn");
        const minus = e.target.closest(".minus-btn");
        const activeDeck = activeDeckContainer?.querySelector(".dropzone");
        if (!activeDeck) return;

        const cardId = card.dataset.cardId;
        const cardType = card.querySelector("p")?.textContent ?? "Unknown";

        if (plus) {
            if (
                !validateCardPlacement(
                    ruleSet,
                    activeDeck,
                    cardId,
                    cardType,
                    null
                )
            ) {
                flashInvalid(activeDeck);
                return;
            }

            const clone = card.cloneNode(true);
            stripButtons(clone);
            clone.id = "card_" + Math.random().toString(36).slice(2);
            activeDeck.appendChild(clone);

            updateCounts();
            updateSearchPoolButtons();
            await saveDeckState();
        }

        if (minus) {
            const target = Array.from(
                activeDeck.querySelectorAll(".card-wrapper")
            ).find((c) => c.dataset.cardId === cardId);
            if (target) {
                activeDeck.removeChild(target);
                updateCounts();
                updateSearchPoolButtons();
                await saveDeckState();
            }
        }
    });

    // ---------------- DRAG & DROP ----------------
    document.addEventListener("dragstart", (e) => {
        const link = e.target.closest(".drag-link");
        if (!link) return;
        const wrapper = link.closest(".card-wrapper");
        if (!wrapper) return;
        if (!wrapper.id)
            wrapper.id = "card_" + Math.random().toString(36).slice(2);

        const cardId = wrapper.dataset.cardId;
        const cardType = wrapper.querySelector("p")?.textContent ?? "Unknown";
        const fromDeck = wrapper.closest(".dropzone");

        // Determine which deck zone to validate against
        let deckZoneToValidate = activeDeckContainer?.querySelector(".dropzone");

        // Special case: extra deck cards can always be dragged to extraDeck
        if (game === "yugioh" &&
            ruleSet.extraTypes.some(t => cardType.includes(t)))
        {
            deckZoneToValidate = document.getElementById("extraDeck");
        }

        // Validate placement
        if (!fromDeck && deckZoneToValidate) {
            if (
                !validateCardPlacement(
                    ruleSet,
                    deckZoneToValidate,
                    cardId,
                    cardType,
                    null
                )
            ) {
                e.preventDefault();
                return;
            }
        }

        e.dataTransfer.setData("cardWrapperId", wrapper.id);
        e.dataTransfer.setData("fromDeck", fromDeck ? "1" : "0");
        e.dataTransfer.setData("cardId", cardId);
        e.dataTransfer.setData("cardType", cardType);
    });

    zones.forEach((zone) => {
        zone.addEventListener("dragenter", (e) => {
            e.preventDefault();
            zone.classList.add("drop-hover");
        });
        zone.addEventListener("dragover", (e) => e.preventDefault());
        zone.addEventListener("dragleave", () =>
            zone.classList.remove("drop-hover")
        );
        zone.addEventListener("drop", async (e) => {
            e.preventDefault();
            zone.classList.remove("drop-hover");

            const wrapperId = e.dataTransfer.getData("cardWrapperId");
            const fromDeck = e.dataTransfer.getData("fromDeck") === "1";
            const cardId = e.dataTransfer.getData("cardId");
            const cardWrapper = document.getElementById(wrapperId);
            if (!cardWrapper) return;

            const cardType =
                cardWrapper.querySelector("p")?.textContent ?? "Unknown";

            // Pass the moving card for proper global count
            if (
                !validateCardPlacement(
                    ruleSet,
                    zone,
                    cardId,
                    cardType,
                    fromDeck ? cardWrapper : null
                )
            ) {
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
            await saveDeckState();
        });
    });

    // ---------------- SEARCH ----------------
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
        searchInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                currentSearch = searchInput.value.trim();
                currentPage = 0;
                lastPage = false;
                cardContainer.innerHTML = "";
                loadNextPage();
            }
        });
    }
    // ---------------- INFINITE SCROLL ----------------
    const loadNextPage = async () => {
        if (isLoading || lastPage) return;
        isLoading = true;
        spinner.classList.remove("hidden");
        currentPage++;

        try {
            const params = new URLSearchParams({ page: currentPage });
            if (currentSearch) params.append("search", currentSearch);

            const url = `/${game}/deck-builder?${params.toString()}`;
            const response = await fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
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
            else newCards.forEach((card) => cardContainer.appendChild(card));

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
//@TODO After gotta make a check that u can only have a minimum of 40 cards in a deck for yugioh (magic need to check)
//else add a warning asking this isnt a usable deck are u sure u wanna add? (cannot be added to the public deck)

// Enable Save Deck button if there's something in the deck
function enableSaveButton() {
    const saveBtn = document.querySelector("#saveForm button");
    const mainDeckCount = document.getElementById("mainDeck").children.length;
    const extraDeckCount =
        document.getElementById("extraDeck")?.children.length || 0;
    const sideDeckCount =
        document.getElementById("sideDeck")?.children.length || 0;

    saveBtn.disabled =
        mainDeckCount === 0 && extraDeckCount === 0 && sideDeckCount === 0;
}

// Update save button state after deck changes
function updateCounts() {
    const mainDeck = document.getElementById("mainDeck");
    const extraDeck = document.getElementById("extraDeck");
    const sideDeck = document.getElementById("sideDeck");

    document.getElementById("mainCount").textContent = mainDeck.children.length;
    document.getElementById("extraCount").textContent =
        extraDeck?.children.length ?? 0;
    document.getElementById("sideCount").textContent =
        sideDeck?.children.length ?? 0;

    if (deckLimits.mainDeck) highlightDeck(mainDeck, deckLimits.mainDeck);
    if (deckLimits.extraDeck) highlightDeck(extraDeck, deckLimits.extraDeck);
    if (deckLimits.sideDeck) highlightDeck(sideDeck, deckLimits.sideDeck);

    saveDeckState();
    enableSaveButton();
}

const saveForm = document.getElementById("saveForm");
const mainDeck = document.getElementById("mainDeck");

const modal = document.getElementById("deckWarningModal");
const cancelBtn = document.getElementById("cancelSaveBtn");
const confirmBtn = document.getElementById("confirmSaveBtn");

saveForm.addEventListener("submit", function (e) {
    const mainDeckCount = mainDeck.children.length;

    if (mainDeckCount < 40) {
        e.preventDefault(); // Stop the form from submitting
        modal.classList.remove("hidden"); // Show modal
        return false;
    }

    saveDeckData(); // gather hidden inputs as before
});

// Cancel button just closes modal
cancelBtn.addEventListener("click", () => {
    modal.classList.add("hidden");
});

// Confirm button submits the form
confirmBtn.addEventListener("click", () => {
    modal.classList.add("hidden");
    saveDeckData(); // gather hidden inputs again
    saveForm.submit(); // force submit
});

// Function to gather deck data into hidden inputs
function saveDeckData() {
    const zones = ["mainDeck", "extraDeck", "sideDeck"];
    const state = {};

    zones.forEach((zone) => {
        const list = document.getElementById(zone);
        if (!list) return;

        state[zone] = Array.from(list.children).map((card) => ({
            id: card.dataset.cardId,
            name: card.dataset.cardName,
            image_url: card.dataset.cardImage,
            type: card.dataset.cardType,
            race: card.dataset.cardRace,
            desc: card.dataset.cardDesc,
        }));
    });

    document.getElementById("cardData").value = JSON.stringify(state);
    document.getElementById("deckTitle").value =
        document.getElementById("deckTitleInput").value;
    document.getElementById("deckDescription").value =
        document.getElementById("deckDescInput").value;
}
