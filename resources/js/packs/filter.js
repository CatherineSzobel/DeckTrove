/**
 * The pack page's card filter (resources/js/pack-page.js), with the same rules as App\Packs\PackCardFilter.
 */

/** Whether a tile ({ name, rarity } from its data attributes; the name is already lower case) matches the filter. */
export function matchesCard(card, { name = "", rarity = "" }) {
    return card.name.includes(name.trim().toLowerCase()) && (rarity === "" || card.rarity === rarity);
}

/** "126 cards", or "12 of 126 cards" while filtering (worded like PackCardFilter::countLabel()). */
export function countLabel(shown, total, filtering) {
    const cards = `${total.toLocaleString("en")} ${total === 1 ? "card" : "cards"}`;

    return filtering ? `${shown.toLocaleString("en")} of ${cards}` : cards;
}
