export const MAX_COPIES_STANDARD = 4;
export const MAX_COPIES_BASIC = 99;
export const MAX_COPIES_EXTRA = 3;

export const DECK_IDS = {
    main: "mainDeck",
    extra: "extraDeck",
    side: "sideDeck",
};

const saveBtn = document.querySelector("#saveForm button");

export const cardCounts = new Map();
export const getTotalCopies = (cardId) => cardCounts.get(cardId) || 0;

export function incrementCardCount(cardId) {
    cardCounts.set(cardId, (cardCounts.get(cardId) || 0) + 1);
}

export function decrementCardCount(cardId) {
    const count = cardCounts.get(cardId);
    if (!count) return;
    count === 1 ? cardCounts.delete(cardId) : cardCounts.set(cardId, count - 1);
}

/** Returns currently active deck dropzone */
export function getActiveDeckZone() {
    return DeckBuilder.state.activeDeckContainer?.querySelector(".dropzone") || null;
}

/** Count copies of a card in a deck, adjusting for moving card */
export const countCardCopies = (deckZone, cardId, movingCard) => {
    const count = deckZone?.querySelectorAll(`.card-wrapper[data-card-id='${cardId}']`)?.length || 0;
    return movingCard?.parentElement === deckZone ? count - 1 : count;
};

/** Check if a card is in any of the given deck zones */
export const isCardInDeck = (cardWrapper, deckZones) =>
    deckZones.some((zone) => zone.contains(cardWrapper));

/** Extract card data from a wrapper element */
export function getCardDataFromWrapper(wrapper) {
    return {
        id: wrapper.dataset.cardId,
        type: wrapper.querySelector("p")?.textContent || "Unknown",
        name: wrapper.dataset.cardName,
        image: wrapper.dataset.cardImage,
        race: wrapper.dataset.cardRace,
        desc: wrapper.dataset.cardDesc,
    };
}

/** Create a deck card element from data */
export function createDeckCard(cardData) {
    const card = document.createElement("div");
    card.id = "card_" + Math.random().toString(36).slice(2);
    card.className = "relative w-20 h-28 mb-2 mx-auto group card-wrapper cursor-pointer";
    card.setAttribute("draggable", "true");
    Object.assign(card.dataset, {
        cardId: cardData.id,
        cardName: cardData.name || "",
        cardImage: cardData.image || "",
        cardType: cardData.type || "",
        cardRace: cardData.race || "",
        cardDesc: cardData.desc || "",
    });

    const img = document.createElement("img");
    img.src = cardData.image || "";
    img.alt = cardData.name || "";
    img.className = "w-full h-full object-cover rounded card";
    card.appendChild(img);

    const overlay = document.createElement("div");
    overlay.className =
        "absolute inset-0 bg-black bg-opacity-70 text-white opacity-0 " +
        "group-hover:opacity-100 transition-opacity rounded p-1 flex flex-col justify-center items-center text-center";

    const title = document.createElement("h3");
    title.className = "font-bold text-[10px] leading-snug";
    title.textContent = cardData.name || "Unknown";
    overlay.appendChild(title);

    const type = document.createElement("p");
    type.className = "text-[8px] mt-1 pointer-events-none";
    type.textContent = cardData.type || "Unknown";
    overlay.appendChild(type);

    card.appendChild(overlay);
    return card;
}

/** Flash deck invalid state with optional message */
export function flashInvalid(deckZone, message = "") {
    const deckContainer = deckZone.closest(".deckContainer");
    if (!deckContainer) return;

    deckContainer.classList.add("drop-invalid");

    const existingError = deckContainer.querySelector(".deck-error-msg");
    if (existingError) existingError.remove();

    if (message) {
        const errorMsg = document.createElement("p");
        errorMsg.className = "deck-error-msg text-red-600 text-sm mb-2";
        errorMsg.textContent = message;
        deckContainer.prepend(errorMsg);
        setTimeout(() => errorMsg.remove(), 3000);
    }

    setTimeout(() => deckContainer.classList.remove("drop-invalid"), 800);
}

/** Sanitize HTML string to DocumentFragment */
export function sanitizeHTML(html) {
    const template = document.createElement("template");
    template.innerHTML = html;
    return template.content;
}

/** Set cover image from a card wrapper */
export function setCoverImage(cardWrapper, coverPreview, deckImageInput) {
    const img = cardWrapper.querySelector("img");
    if (!img) return;

    const applyCover = () => {
        coverPreview.src = img.src;
        coverPreview.classList.remove("hidden");
        deckImageInput.value = img.src;
    };

    img.complete && img.naturalWidth > 0
        ? applyCover()
        : img.addEventListener("load", applyCover, { once: true });
}

/** Enable or disable save button based on deck content */
export function enableSaveButton() {
    if (!saveBtn) return;
    const totalCards =
        (document.getElementById(DECK_IDS.main)?.children.length || 0) +
        (document.getElementById(DECK_IDS.extra)?.children.length || 0) +
        (document.getElementById(DECK_IDS.side)?.children.length || 0);
    saveBtn.disabled = totalCards === 0;
}

/** Check if a card can be placed in a deck */
export function validateCardPlacement(ruleSet, deckZone, cardId, cardType, movingCard = null) {
    if (!deckZone) return { valid: false, reason: "No target deck found." };
    const limits = ruleSet.deckLimits[deckZone.id];
    if (!limits) return { valid: false, reason: "Deck limits not found." };

    if (deckZone.children.length >= limits.max)
        return { valid: false, reason: `Deck cannot exceed ${limits.max} cards.` };

    const isBasic = cardType.toLowerCase().includes("basic");
    const maxCopies =
        ruleSet.extraTypes.length === 0
            ? isBasic
                ? MAX_COPIES_BASIC
                : MAX_COPIES_STANDARD
            : MAX_COPIES_EXTRA;

    let total = getTotalCopies(cardId);
    if (movingCard && movingCard.parentElement) total--;

    if (total >= maxCopies)
        return { valid: false, reason: `Cannot have more than ${maxCopies} copies of this card.` };

    const isExtraType = ruleSet.extraTypes.some((t) => cardType.includes(t));

    if (deckZone.id === DECK_IDS.extra && !isExtraType)
        return { valid: false, reason: "Only Extra Deck cards (Fusion, Synchro, Xyz, Link) allowed here." };

    if (deckZone.id === DECK_IDS.main && isExtraType)
        return { valid: false, reason: "Extra Deck cards cannot be placed in the Main Deck." };

    return { valid: true };
}

/** Reset all decks and card counts */
export function resetDecks(decks = []) {
    decks.forEach((element) => {
        if (!element) return;  // <== skip invalid elements
        element.innerHTML = "";
    });
    cardCounts.clear();
    updateCounts();
}

/** Highlight decks and attach click listeners */
export function highlightActiveDeck() {
    document.querySelectorAll(".deckContainer").forEach((deck) => {
        deck.addEventListener("click", () => setActiveDeck(deck));
    });
}

/** Set the currently active deck */
export function setActiveDeck(deck) {
    document.querySelectorAll(".deckContainer").forEach((d) => d.classList.remove("deck-active"));
    deck.classList.add("deck-active");
    DeckBuilder.state.activeDeckContainer = deck;

    const activeDeck = getActiveDeckZone();
    updateSearchPoolButtons(
        activeDeck,
        document.querySelectorAll(".search-pool .card-wrapper"),
        DeckBuilder.state.ruleSet
    );
}

// Return current decks with limits and counter elements
function getDeckState() {
    return [
        {
            element: document.getElementById(DECK_IDS.main),
            limits: DeckBuilder?.state?.deckLimits?.mainDeck ?? { min: 0, max: 60 },
            counter: document.getElementById("mainCount")
        },
        {
            element: document.getElementById(DECK_IDS.extra),
            limits: DeckBuilder?.state?.deckLimits?.extraDeck ?? { min: 0, max: 15 },
            counter: document.getElementById("extraCount")
        },
        {
            element: document.getElementById(DECK_IDS.side),
            limits: DeckBuilder?.state?.deckLimits?.sideDeck ?? { min: 0, max: 15 },
            counter: document.getElementById("sideCount")
        }
    ];
}

export function updateCounts(decks, saveButton = saveBtn) {
    const currentDecks = decks ?? getDeckState();

    currentDecks.forEach(({ element, limits, counter }) => {
        if (!element || !limits || !counter) return;

        const count = element.children.length;
        counter.textContent = `${count}/${limits.max}`;
        element.classList.remove("deck-invalid", "deck-valid");
        element.classList.add(count < limits.min || count > limits.max ? "deck-invalid" : "deck-valid");
    });

    if (saveButton) {
        const totalCards = currentDecks.reduce((sum, d) => sum + (d.element?.children.length || 0), 0);
        saveButton.disabled = totalCards === 0;
    }
}

export function updateSearchPoolButtons(activeDeck, searchPoolCards, ruleSet) {
    if (!activeDeck || !searchPoolCards || !ruleSet) return;

    searchPoolCards.forEach((wrapper) => {
        const cardId = wrapper.dataset.cardId;
        const cardType = wrapper.querySelector("p")?.textContent || "Unknown";
        const plusBtn = wrapper.querySelector(".plus-btn");
        const minusBtn = wrapper.querySelector(".minus-btn");
        const deckCopies = countCardCopies(activeDeck, cardId);

        const validation = validateCardPlacement(ruleSet, activeDeck, cardId, cardType);

        if (plusBtn) {
            plusBtn.disabled = !validation.valid;
            plusBtn.style.opacity = plusBtn.disabled ? 0.5 : 1;
        }

        if (minusBtn) minusBtn.disabled = deckCopies === 0;
    });
}
