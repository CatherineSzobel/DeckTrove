import { DECK_IDS, getCardCount, countCardCopies } from './constants.js';

export const MAX_COPIES_STANDARD = 4;
export const MAX_COPIES_BASIC = 99;
export const MAX_COPIES_EXTRA = 3;

export const getTotalCopies = (cardId) => getCardCount(cardId);

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

export function sanitizeHTML(html) {
    const template = document.createElement("template");
    template.innerHTML = html;
    return template.content;
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