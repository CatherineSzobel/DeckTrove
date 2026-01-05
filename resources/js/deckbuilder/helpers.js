
import {
    DECK_IDS,
    DeckBuilder,
    getCardCount,
    setCardCount,
    deleteCardCount,
    clearCardCounts,
    countCardCopies
} from './constants.js';

import { validateCardPlacement } from './validation.js';

const saveBtn = document.querySelector("#saveForm button");

export function incrementCardCount(cardId) {
    setCardCount(cardId, (getCardCount(cardId) || 0) + 1);
}

export function decrementCardCount(cardId) {
    const count = getCardCount(cardId);
    if (!count) return;
    count === 1 ? deleteCardCount(cardId) : setCardCount(cardId, count - 1);
}

export function getActiveDeckZone() {
    return DeckBuilder.state.activeDeckContainer?.querySelector(".dropzone") || null;
}

export const isCardInDeck = (cardWrapper, deckZones) =>
    deckZones.some((zone) => zone.contains(cardWrapper));

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

const getChildCount = (id) =>
    document.getElementById(id)?.children.length ?? 0;

export function enableSaveButton() {
    if (!saveBtn) return;

    const totalCards =
        getChildCount(DECK_IDS.main) +
        getChildCount(DECK_IDS.extra) +
        getChildCount(DECK_IDS.side);

    saveBtn.disabled = totalCards === 0;
}

export function resetDecks(decks = []) {
    decks.forEach((element) => {
        if (!element) return;
        element.innerHTML = "";
    });
    clearCardCounts();
    updateCounts();
}

export function resetCoverImage(coverPreview, deckImageInput) {
    if (coverPreview) {
        coverPreview.src = "";
        coverPreview.classList.add("hidden");
    }
    if (deckImageInput) deckImageInput.value = "";
}

export function highlightActiveDeck() {
    document.querySelectorAll(".deckContainer").forEach((deck) => {
        deck.addEventListener("click", () => setActiveDeck(deck));
    });
}

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
