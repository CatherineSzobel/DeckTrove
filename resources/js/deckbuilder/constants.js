export const DECK_IDS = {
    main: "main",
    extra: "extra",
    side: "side",
};

export const DeckBuilder = {
    state: {
        activeDeckContainer: null,
        zones: [],
        ruleSet: null,
        deckLimits: null,
        game: null
    }
};

export const gameRules = {
    yugioh: {
        deckLimits:
        {
            main: { min: 40, max: 60 },
            extra: { min: 0, max: 15 },
            side: { min: 0, max: 15 }
        }, extraTypes: ["Fusion", "Synchro", "Xyz", "Link"]
    },
    magic: {
        deckLimits:
        {
            main: { min: 60, max: 60 },
            side: { min: 0, max: 15 }
        }, extraTypes: []
    }
};

const cardCounts = new Map();

export function getCardCount(cardId) {
    return cardCounts.get(cardId) || 0;
}
export function setCardCount(cardId, count) {
    cardCounts.set(cardId, count);
}

export function deleteCardCount(cardId) {
    cardCounts.delete(cardId);
}

export function clearCardCounts() {
    cardCounts.clear();
}

export const countCardCopies = (deckZone, cardId, movingCard) => {
    const count = deckZone?.querySelectorAll(`.card-wrapper[data-card-id='${cardId}']`)?.length || 0;
    return movingCard?.parentElement === deckZone ? count - 1 : count;
};