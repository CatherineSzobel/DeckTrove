/**
 * Deck building rules. The rules object comes from config/series.php (`deck` key) via the
 * #deckApp data-rules attribute, so the server and the builder always agree:
 *   { zones: { main: { label, min, max }, ... }, max_copies, extra_types, unlimited_types }
 */

const matchesAny = (type, needles) =>
    needles.some((needle) => type.toLowerCase().includes(needle.toLowerCase()));

/** Copies of a card across every zone of the deck. */
export function countCopies(cardId) {
    return document.querySelectorAll(`.dropzone .card-wrapper[data-card-id="${CSS.escape(cardId)}"]`).length;
}

/**
 * Can `card` be placed in `zone`? `fromZone` is set when the card is being moved out of a zone,
 * so it doesn't count against limits twice.
 */
export function validatePlacement(rules, zone, card, fromZone = null) {
    const zoneRules = rules.zones[zone?.id];
    if (!zoneRules) return { valid: false, reason: "Select a deck zone first." };

    if (zone.children.length >= zoneRules.max) {
        return { valid: false, reason: `${zoneRules.label} cannot have more than ${zoneRules.max} cards.` };
    }

    const type = card.dataset.cardType || "";
    const copies = countCopies(card.dataset.cardId) - (fromZone ? 1 : 0);

    if (copies >= rules.max_copies && !matchesAny(type, rules.unlimited_types)) {
        return { valid: false, reason: `You can only have ${rules.max_copies} copies of a card.` };
    }

    if (rules.extra_types.length) {
        const isExtra = matchesAny(type, rules.extra_types);

        if (zone.id === "extra" && !isExtra) {
            return { valid: false, reason: `Only ${rules.extra_types.join(", ")} monsters belong in the ${zoneRules.label}.` };
        }
        if (zone.id === "main" && isExtra) {
            return { valid: false, reason: `${rules.extra_types.join(", ")} monsters belong in the Extra Deck.` };
        }
    }

    return { valid: true };
}
