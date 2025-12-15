// =================== GLOBAL VARIABLES ===================
let activeDeckContainer = null;
let zones = [];
let ruleSet = null;
let deckLimits = null;
let game = null;

// =================== GAME RULES ENGINE ===================
const gameRules = {
    yugioh: {
        deckLimits: {
            mainDeck: { min: 40, max: 60 },
            extraDeck: { min: 0, max: 15 },
            sideDeck: { min: 0, max: 15 },
        },
        extraTypes: ["Fusion", "Synchro", "Xyz", "Link"],
    },
    magic: {
        deckLimits: {
            mainDeck: { min: 60, max: 60 },
            sideDeck: { min: 0, max: 15 },
        },
        extraTypes: [],
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
// const DeckStorage = {
//     save(game, state) {
//         switch (game) {
//             case "yugioh":
//                 localStorage.setItem(
//                     `deckState_${game}`,
//                     JSON.stringify(state)
//                 );
//             case "magic":
//                 break;
//             default:
//                 console.warn(`DeckStorage: Unsupported game "${game}"`);
//                 return;
//         }
//     },
//     load(game) {
//         const raw = localStorage.getItem(`deckState_${game}`);
//         if (!raw) return null;
//         try {
//             switch (game) {
//                 case "yugioh":
//                     return JSON.parse(raw);
//                 case "magic":
//                     break;
//                 default:
//                     console.warn(`DeckStorage: Unsupported game "${game}"`);
//                     return null;
//             }
//         } catch {
//             return null;
//         }
//     },
// };

// =================== VALIDATION ===================
function validateCardPlacement(
    ruleSet,
    deckZone,
    cardId,
    cardType,
    movingCard = null
) {
    const limits = ruleSet.deckLimits[deckZone.id];
    if (!limits) return false;

    if (deckZone.children.length >= limits.max) return false;

    const isBasic = cardType.toLowerCase().includes("basic");
    const maxCopies = ruleSet.extraTypes.length === 0 ? (isBasic ? 99 : 4) : 3;

    let total = countCardCopiesAllDecks(cardId);
    if (movingCard && movingCard.parentElement) total--;

    if (total >= maxCopies) return false;

    if (
        deckZone.id === "extraDeck" &&
        ruleSet.extraTypes.length &&
        !ruleSet.extraTypes.some((t) => cardType.includes(t))
    )
        return false;

    if (
        deckZone.id === "mainDeck" &&
        ruleSet.extraTypes.length &&
        ruleSet.extraTypes.some((t) => cardType.includes(t))
    )
        return false;

    return true;
}

// =================== COUNTS & STATE ===================
function updateCounts() {
    const mainDeck = document.getElementById("mainDeck");
    const extraDeck = document.getElementById("extraDeck");
    const sideDeck = document.getElementById("sideDeck");

    if (mainDeck) {
        document.getElementById("mainCount").textContent =
            mainDeck.children.length + "/" + deckLimits.mainDeck.max;
        if (deckLimits.mainDeck) highlightDeck(mainDeck, deckLimits.mainDeck);
    }

    if (extraDeck) {
        document.getElementById("extraCount").textContent =
            extraDeck.children.length + "/" + deckLimits.extraDeck.max;
        if (deckLimits.extraDeck)
            highlightDeck(extraDeck, deckLimits.extraDeck);
    }

    if (sideDeck) {
        document.getElementById("sideCount").textContent =
            sideDeck.children.length + "/" + deckLimits.sideDeck.max;
        if (deckLimits.sideDeck) highlightDeck(sideDeck, deckLimits.sideDeck);
    }

    enableSaveButton();
}

function updateSearchPoolButtons() {
    const activeDeck = activeDeckContainer?.querySelector(".dropzone");
    if (!activeDeck) return;

    document
        .querySelectorAll(".search-pool .card-wrapper")
        .forEach((wrapper) => {
            const cardId = wrapper.dataset.cardId;
            const cardType =
                wrapper.querySelector("p")?.textContent ?? "Unknown";
            const plusBtn = wrapper.querySelector(".plus-btn");
            const minusBtn = wrapper.querySelector(".minus-btn");

            const deckCopies = activeDeck
                ? countCardCopies(activeDeck, cardId)
                : 0;

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

// // ---------------- SAVE DECK STATE ----------------
// async function saveDeckState() {
//     const state = {};
//     Object.keys(deckLimits).forEach((deckId) => {
//         const deck = document.getElementById(deckId);
//         state[deckId] = deck
//             ? Array.from(deck.children).map((c) => ({
//                   id: c.dataset.cardId,
//                   name: c.querySelector("h3")?.textContent ?? "Unknown",
//                   type: c.querySelector("p")?.textContent ?? "Unknown",
//                   image: c.querySelector("img")?.src ?? null,
//                   desc: c.querySelector("div > p")?.textContent ?? null,
//               }))
//             : [];
//     });
//     DeckStorage.save(game, state);
// }

// ---------------- RESTORE DECK STATE ----------------
// function restoreDeckState() {
//      const state = DeckStorage.load(game);
//      if (!state) return;

//     Object.keys(state).forEach((deckId) => {
//         const deckZone = document.getElementById(deckId);
//         if (!deckZone) return;

//         state[deckId].forEach((cardData) => {
//             // Try to find the card in search pool first
//             let card = document.querySelector(
//                 `.search-pool .card-wrapper[data-card-id='${cardData.id}']`
//             );

//             // If not found, create a new card element using the deck layout (no plus/minus)
//             if (!card) {
//                 card = document.createElement("div");
//                 card.classList.add(
//                     "relative",
//                     "w-32",
//                     "h-44",
//                     "mb-4",
//                     "mx-auto",
//                     "group",
//                     "card-wrapper",
//                     "cursor-pointer"
//                 );
//                 card.dataset.cardId = cardData.id;

//                 // Card Image
//                 const img = document.createElement("img");
//                 img.src = cardData.image ?? ""; // you may need to store image URL in saved state
//                 img.alt = cardData.name;
//                 img.classList.add(
//                     "w-full",
//                     "h-full",
//                     "object-cover",
//                     "rounded",
//                     "card"
//                 );
//                 card.appendChild(img);

//                 // Hover overlay
//                 const overlay = document.createElement("div");
//                 overlay.classList.add(
//                     "absolute",
//                     "inset-0",
//                     "bg-black",
//                     "bg-opacity-70",
//                     "text-white",
//                     "opacity-0",
//                     "group-hover:opacity-100",
//                     "transition-opacity",
//                     "rounded",
//                     "p-2",
//                     "flex",
//                     "flex-col",
//                     "justify-center",
//                     "items-center",
//                     "text-center",
//                     "pointer-events-auto"
//                 );

//                 const link = document.createElement("a");
//                 link.href = cardData.url ?? "#";
//                 link.classList.add(
//                     "drag-link",
//                     "pointer-events-auto",
//                     "text-white"
//                 );
//                 link.draggable = true;
//                 link.dataset.cardId = cardData.id;
//                 link.dataset.cardName = cardData.name;
//                 link.dataset.cardImage = cardData.image ?? "";
//                 link.dataset.cardType = cardData.type ?? "";
//                 link.dataset.cardRace = cardData.race ?? "";
//                 link.dataset.cardDesc = cardData.desc ?? "";

//                 const title = document.createElement("h3");
//                 title.classList.add("font-bold", "text-xs");
//                 title.textContent = cardData.name;
//                 link.appendChild(title);

//                 overlay.appendChild(link);

//                 const typeText = document.createElement("p");
//                 typeText.classList.add(
//                     "text-[10px]",
//                     "mt-1",
//                     "pointer-events-none"
//                 );
//                 typeText.textContent = cardData.type ?? "Unknown";
//                 overlay.appendChild(typeText);

//                 card.appendChild(overlay);

//                 // Optional: description box
//                 const descBox = document.createElement("div");
//                 descBox.classList.add(
//                     "absolute",
//                     "top-0",
//                     "left-full",
//                     "ml-2",
//                     "w-48",
//                     "bg-black",
//                     "bg-opacity-80",
//                     "text-white",
//                     "text-xs",
//                     "p-2",
//                     "rounded",
//                     "opacity-0",
//                     "group-hover:opacity-100",
//                     "transition-opacity",
//                     "z-10",
//                     "pointer-events-none"
//                 );
//                 const descText = document.createElement("p");
//                 descText.textContent =
//                     cardData.desc ?? "No description available";
//                 descBox.appendChild(descText);

//                 card.appendChild(descBox);
//             }

//             const clone = card.cloneNode(true);
//             stripButtons(clone);
//             clone.id = "card_" + Math.random().toString(36).slice(2);
//             deckZone.appendChild(clone);
//         });
//     });

//     updateCounts();
//     updateSearchPoolButtons();
// }

// =================== COVER IMAGE HANDLING ===================
function setCoverImage(cardWrapper, coverPreview, deckImageInput) {
    const img = cardWrapper.querySelector("img");
    if (!img) return;

    if (img.complete && img.naturalWidth > 0) {
        coverPreview.src = img.src;
        coverPreview.classList.remove("hidden");
        deckImageInput.value = img.src;
    } else {
        img.addEventListener("load", () => {
            coverPreview.src = img.src;
            coverPreview.classList.remove("hidden");
            deckImageInput.value = img.src;
        });
    }
}

function handleCoverDrop(
    coverDropArea,
    coverPreview,
    deckImageInput,
    deckZones
) {
    const hoverClasses = ["border-blue-500", "bg-blue-50"];

    // Handle all drag events
    coverDropArea.addEventListener("dragenter", toggleHover);
    coverDropArea.addEventListener("dragover", toggleHover);
    coverDropArea.addEventListener("dragleave", toggleHover);
    coverDropArea.addEventListener("drop", toggleHover);

    function toggleHover(e) {
        e.preventDefault();
        e.stopPropagation();

        if (e.type === "dragenter" || e.type === "dragover") {
            coverDropArea.classList.add(...hoverClasses);
        } else {
            coverDropArea.classList.remove(...hoverClasses);
        }
    }

    // Handle drop logic
    coverDropArea.addEventListener("drop", (e) => {
        const cardWrapperId = e.dataTransfer.getData("cardWrapperId");
        if (!cardWrapperId) return;

        const cardWrapper = document.getElementById(cardWrapperId);
        if (!cardWrapper) return;

        if (!isCardInDeck(cardWrapper, deckZones)) {
            flashInvalid(coverDropArea);
            return;
        }

        setCoverImage(cardWrapper, coverPreview, deckImageInput);
    });

    // Clicking preview clears cover
    coverPreview.addEventListener("click", () => {
        coverPreview.src = "";
        coverPreview.classList.add("hidden");
        deckImageInput.value = "";
    });
}

function isCardInDeck(cardWrapper, deckZones) {
    return deckZones.some((zone) => zone.contains(cardWrapper));
}

// =================== RESET ===================
function handleReset() {
    const resetButton = document.getElementById("resetButton");
    if (!resetButton) return;

    resetButton.addEventListener("click", () => {
        document
            .querySelectorAll(".dropzone")
            .forEach((zone) => (zone.innerHTML = ""));
        updateCounts();
        updateSearchPoolButtons();
    });
}

// =================== ACTIVE DECK ===================
function highlightActiveDeck() {
    document.querySelectorAll(".deckContainer").forEach((deck) => {
        deck.addEventListener("click", () => setActiveDeck(deck));
    });
}

function setActiveDeck(deck) {
    document
        .querySelectorAll(".deckContainer")
        .forEach((d) => d.classList.remove("deck-active"));
    deck.classList.add("deck-active");
    activeDeckContainer = deck;

    updateSearchPoolButtons();
}

// =================== PLUS / MINUS HANDLING ===================
function handleAddingPlusMinusFunction(cardContainer) {
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
            if (!validateCardPlacement(ruleSet, activeDeck, cardId, cardType)) {
                flashInvalid(activeDeck);
                return;
            }

            const clone = card.cloneNode(true);
            stripButtons(clone);
            clone.id = "card_" + Math.random().toString(36).slice(2);
            activeDeck.appendChild(clone);

            updateCounts();
            updateSearchPoolButtons();
            //  await saveDeckState();
        }

        if (minus) {
            const target = Array.from(
                activeDeck.querySelectorAll(".card-wrapper")
            ).find((c) => c.dataset.cardId === cardId);
            if (target) {
                activeDeck.removeChild(target);
                updateCounts();
                updateSearchPoolButtons();
                //  await saveDeckState();
            }
        }
    });
}

// =================== DRAG & DROP ===================
function handleDragAndDrop() {
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

        let deckZoneToValidate =
            activeDeckContainer?.querySelector(".dropzone");

        if (
            game === "yugioh" &&
            ruleSet.extraTypes.some((t) => cardType.includes(t))
        ) {
            deckZoneToValidate = document.getElementById("extraDeck");
        }

        if (!fromDeck && deckZoneToValidate) {
            if (
                !validateCardPlacement(
                    ruleSet,
                    deckZoneToValidate,
                    cardId,
                    cardType
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
        zone.addEventListener("drop", (e) => {
            e.preventDefault();
            zone.classList.remove("drop-hover");

            const wrapperId = e.dataTransfer.getData("cardWrapperId");
            const fromDeck = e.dataTransfer.getData("fromDeck") === "1";
            const cardId = e.dataTransfer.getData("cardId");
            const cardWrapper = document.getElementById(wrapperId);
            if (!cardWrapper) return;

            const cardType =
                cardWrapper.querySelector("p")?.textContent ?? "Unknown";

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
        });
    });
}

// =================== SEARCH & INFINITE SCROLL ===================
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
        currentPageRef.value.number++;

        try {
            const params = new URLSearchParams({
                page: currentPageRef.value.number,
            });
            if (currentSearchRef.value)
                params.append("search", currentSearchRef.value);

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

            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = html;

            const newCards = tempDiv.querySelectorAll(".card-wrapper");
            if (newCards.length === 0) lastPageRef.value = true;
            else newCards.forEach((card) => cardContainer.appendChild(card));

            updateSearchPoolButtons();
        } catch (err) {
            console.error("Error loading next page:", err);
            currentPageRef.value.number--;
        } finally {
            spinner.classList.add("hidden");
            currentPageRef.value.loading = false;
        }
    };
}

function handleSearchInput(
    searchInput,
    cardContainer,
    currentPageRef,
    currentSearchRef,
    lastPageRef,
    loadNextPageFn
) {
    searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            currentSearchRef.value = searchInput.value.trim();
            currentPageRef.value.number = 0;
            lastPageRef.value = false;
            cardContainer.innerHTML = "";
            loadNextPageFn();
        }
    });
}

function handleInfiniteScroll(cardContainer, loadNextPageFn) {
    cardContainer.addEventListener("scroll", () => {
        const scrollPosition =
            cardContainer.scrollTop + cardContainer.clientHeight;
        const threshold = cardContainer.scrollHeight - 100;
        if (scrollPosition >= threshold) loadNextPageFn();
    });
}

// =================== SAVE DECK LOGIC ===================
function saveDeckData(zones) {
    const state = {};
    const cardsArray = []; // Flattened array for Laravel's 'cards' field

    zones.forEach((zoneId) => {
        const list = document.getElementById(zoneId);
        if (!list) return;

        state[zoneId] = Array.from(list.children).map((card) => {
            const cardData = {
                id: card.dataset.cardId,
                name: card.dataset.cardName,
                image_url: card.dataset.cardImage,
                type: card.dataset.cardType,
                race: card.dataset.cardRace,
                desc: card.dataset.cardDesc,
            };

            // Push into a flat array for 'cards'
            cardsArray.push(cardData);
            return cardData;
        });
    });

    // Set both cardData (legacy) and cards (Laravel validation)
    const cardDataInput = document.getElementById("cardData");
    if (cardDataInput) cardDataInput.value = JSON.stringify(state);

    const cardsInput = document.getElementById("cards");
    if (cardsInput) cardsInput.value = JSON.stringify(cardsArray);

    // Deck title and description
    const deckTitleInput = document.getElementById("deckTitleInput");
    const deckDescInput = document.getElementById("deckDescInput");
    if (deckTitleInput)
        document.getElementById("deckTitle").value = deckTitleInput.value;
    if (deckDescInput)
        document.getElementById("deckDescription").value = deckDescInput.value;

    // Determine if deck is valid (example: mainDeck must have >= 40 cards)
    const mainDeckCount = document.getElementById("mainDeck").children.length;
    const isValid = mainDeckCount >= 40;
    document.getElementById("isPublic").value = isValid ? 1 : 0;

    return isValid;
}

function handleSaveForm(saveForm, zones) {
    const modal = document.getElementById("deckWarningModal");
    const cancelBtn = document.getElementById("cancelSaveBtn");
    const confirmBtn = document.getElementById("confirmSaveBtn");

    saveForm.addEventListener("submit", function (e) {
        if (!saveDeckData(zones)) {
            e.preventDefault();
            modal.classList.remove("hidden");
        }
    });

    cancelBtn.addEventListener("click", () => modal.classList.add("hidden"));
    confirmBtn.addEventListener("click", () => {
        modal.classList.add("hidden");
        saveDeckData(zones);
        saveForm.submit();
    });
}

// =================== ENABLE SAVE BUTTON ===================
function enableSaveButton() {
    const saveBtn = document.querySelector("#saveForm button");
    if (!saveBtn) return;

    const totalCards =
        document.getElementById("mainDeck").children.length +
        (document.getElementById("extraDeck")?.children.length || 0) +
        (document.getElementById("sideDeck")?.children.length || 0);

    saveBtn.disabled = totalCards === 0;
}

// =================== INITIALIZATION ===================
document.addEventListener("DOMContentLoaded", () => {
    zones = Array.from(document.querySelectorAll(".dropzone"));
    const deckApp = document.getElementById("deckApp");
    if (!deckApp) return;

    game = deckApp.dataset.game;
    if (!game || !gameRules[game]) return;

    ruleSet = gameRules[game];
    deckLimits = ruleSet.deckLimits;

    const cardContainer = document.querySelector(".search-pool");
    if (!cardContainer) return;

    const coverDropArea = document.getElementById("coverDropArea");
    const coverPreview = document.getElementById("coverPreview");
    const deckImageInput = document.getElementById("deckImage");

    const deckZones = [
        document.getElementById("mainDeck"),
        document.getElementById("extraDeck"),
        document.getElementById("sideDeck"),
    ];

    handleCoverDrop(coverDropArea, coverPreview, deckImageInput, deckZones);
    handleReset();
    highlightActiveDeck();
    handleAddingPlusMinusFunction(cardContainer);
    handleDragAndDrop();

    const defaultDeck = document.querySelector(".deckContainer");
    if (defaultDeck) setActiveDeck(defaultDeck);

    updateCounts();
    updateSearchPoolButtons();

    // Search & infinite scroll
    const spinner = document.getElementById("loadingSpinner");
    const searchInput = document.getElementById("searchInput");
    const currentPageRef = { value: { number: 0, loading: false } };
    const currentSearchRef = { value: "" };
    const lastPageRef = { value: false };
    const loadNextPageFn = loadNextPage(
        cardContainer,
        spinner,
        currentPageRef,
        currentSearchRef,
        lastPageRef,
        game
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
    handleInfiniteScroll(cardContainer, loadNextPageFn);
    //restoreDeckState();
    // Save deck form
    const saveForm = document.getElementById("saveForm");
    if (saveForm)
        handleSaveForm(saveForm, ["mainDeck", "extraDeck", "sideDeck"]);
});
