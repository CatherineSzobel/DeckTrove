/**
 * Deck building rules for the deck builder, mirroring the server (DeckService::validateRules() and copyErrors()) so
 * the builder refuses exactly what the server would, with the same messages. Pure functions: no DOM, no Alpine.
 *
 * - `rules` is the series' `deck` config (zones, max_copies, extra_types, unlimited_types, formats, limit_labels).
 * - `zones` maps a zone key to its cards, one entry per copy. A card is CardViewModel::builderCard():
 *   { id, name, type, price, limits, ... }.
 * - `format` is a key of rules.formats, or "" for Casual.
 */

export const matchesAny = (type, needles) =>
    needles.some((needle) => type.toLowerCase().includes(needle.toLowerCase()));

const allCards = (zones) => Object.values(zones).flat();

/** Copies of a card across every zone. */
export function copiesOf(zones, id) {
    return allCards(zones).filter((card) => card.id === id).length;
}

const normalLimit = (rules, card) => (matchesAny(card.type, rules.unlimited_types) ? Infinity : rules.max_copies);

/** The lower of the normal limit and the format limit; unlimited_types only lifts the normal limit. */
export function allowedCopies(rules, card, format) {
    const limit = format ? card.limits?.[format] : undefined;

    return limit === undefined || limit === null ? normalLimit(rules, card) : Math.min(normalLimit(rules, card), limit);
}

/** Why a card can't have another copy, worded like the server. */
export function copyMessage(rules, card, format) {
    const allowed = allowedCopies(rules, card, format);

    if (format && allowed < normalLimit(rules, card)) {
        const label = rules.formats?.[format]?.label ?? format;

        return allowed === 0
            ? `${card.name} is not allowed in ${label}.`
            : `You can only have ${allowed} ${allowed === 1 ? "copy" : "copies"} of ${card.name} in ${label}.`;
    }

    return `You can only have ${rules.max_copies} copies of ${card.name}.`;
}

/** Why `card` can't be added to `zone`, or null when it can. Same order of checks as the server. */
export function placementError(rules, zones, zone, card, format) {
    const zoneRules = rules.zones[zone];
    if (!zoneRules) return "Select a deck zone first.";

    if ((zones[zone]?.length ?? 0) >= zoneRules.max) {
        return `${zoneRules.label} cannot have more than ${zoneRules.max} cards.`;
    }

    if (rules.extra_types.length) {
        const isExtra = matchesAny(card.type, rules.extra_types);
        // The side deck may hold anything.
        if (zone === "extra" && !isExtra) return `${card.name} does not belong in the ${zoneRules.label}.`;
        if (zone === "main" && isExtra) return `${card.name} belongs in the Extra Deck.`;
    }

    if (copiesOf(zones, card.id) >= allowedCopies(rules, card, format)) return copyMessage(rules, card, format);

    return null;
}

/** Cards with more copies than allowed (all zones together), once each: [{ id, message }]. */
export function formatProblems(rules, zones, format) {
    const unique = new Map();
    allCards(zones).forEach((card) => unique.set(card.id, card));

    return [...unique.values()]
        .filter((card) => copiesOf(zones, card.id) > allowedCopies(rules, card, format))
        .map((card) => ({ id: card.id, message: copyMessage(rules, card, format) }));
}

/** Each card in the deck once, in deck order (zone by zone), e.g. to pick a cover from. */
export function uniqueCards(zones) {
    const unique = new Map();
    allCards(zones).forEach((card) => unique.has(card.id) || unique.set(card.id, card));

    return [...unique.values()];
}

/** The price of every copy; cards without a price add nothing (see unpricedCount). */
export function deckPrice(zones) {
    return allCards(zones).reduce((sum, card) => sum + (card.price ?? 0), 0);
}

export function unpricedCount(zones) {
    return allCards(zones).filter((card) => card.price === null || card.price === undefined).length;
}

/** The save form's `cards` field: { zone: [{ id }, ...] } (see SaveDeckRequest). */
export function payload(zones) {
    return Object.fromEntries(
        Object.entries(zones).map(([zone, cards]) => [zone, cards.map((card) => ({ id: card.id }))])
    );
}

/** The accessible name of a card in a zone, which removes it when clicked; with its format problem when it has one. */
export function removeLabel(cardName, zoneLabel, problem) {
    return `Remove ${cardName} from the ${zoneLabel}` + (problem ? `. ${problem}` : "");
}

/** The tooltip of a card in a zone. */
export function removeTitle(problem) {
    return problem ? `${problem} Click to remove.` : "Click to remove";
}

/** What an empty zone says: the active zone takes cards from the search, others have to be chosen first. */
export function emptyZoneHint(isActive) {
    return isActive ? "Use + on a card in the search to add it here." : "Choose “Add cards here”, then use + in the search.";
}

/** The colour of a format limit tag: red when forbidden, amber at 1, blue above; none without a limit. */
export function limitTone(limit) {
    if (limit === null || limit === undefined) return "";

    return limit === 0 ? "text-danger" : limit === 1 ? "text-amber-300" : "text-sky-300";
}
