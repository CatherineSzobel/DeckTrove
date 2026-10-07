/**
 * DOM helpers for the deck builder. Card elements carry their data in data-card-* attributes
 * (see CardViewModel::deckBuilderData() and resources/views/decks/partials/deck-slot.blade.php).
 */
import { validatePlacement } from "./rules.js";

const CARD_DATA_KEYS = ["cardId", "cardName", "cardImage", "cardType", "cardDesc"];

/** A deck slot for the given card, matching the deck-slot Blade partial. */
export function createDeckCard(source) {
    const card = document.createElement("div");
    card.className = "relative w-20 h-28 mb-2 mx-auto group card-wrapper cursor-pointer";
    card.draggable = true;
    CARD_DATA_KEYS.forEach((key) => (card.dataset[key] = source.dataset[key] ?? ""));

    const img = document.createElement("img");
    img.src = source.querySelector("img")?.src || source.dataset.cardImage || "";
    img.alt = source.dataset.cardName || "";
    img.className = "w-full h-full object-cover rounded card";

    const overlay = document.createElement("div");
    overlay.className =
        "absolute inset-0 bg-black/70 text-white opacity-0 group-hover:opacity-100 transition-opacity rounded p-1 flex flex-col justify-center items-center text-center";

    const title = document.createElement("h3");
    title.className = "font-bold text-[10px] leading-snug";
    title.textContent = source.dataset.cardName || "Unknown";

    const type = document.createElement("p");
    type.className = "text-[8px] mt-1 pointer-events-none";
    type.textContent = source.dataset.cardType || "";

    overlay.append(title, type);
    card.append(img, overlay);
    return card;
}

export function flashInvalid(zone, message) {
    const container = zone.closest(".deckContainer");
    if (!container) return;

    container.querySelector(".deck-error-msg")?.remove();
    container.classList.add("drop-invalid");

    const error = document.createElement("p");
    error.className = "deck-error-msg text-red-600 text-sm mb-2";
    error.setAttribute("role", "alert");
    error.textContent = message;
    container.prepend(error);

    setTimeout(() => error.remove(), 3000);
    setTimeout(() => container.classList.remove("drop-invalid"), 800);
}

export function updateCounts(rules) {
    Object.entries(rules.zones).forEach(([zoneId, zoneRules]) => {
        const zone = document.getElementById(zoneId);
        const counter = document.querySelector(`[data-zone-count="${zoneId}"]`);
        if (!zone || !counter) return;

        const count = zone.children.length;
        const valid = count >= zoneRules.min && count <= zoneRules.max;
        counter.textContent = `${count}/${zoneRules.min === zoneRules.max ? zoneRules.max : `${zoneRules.min}–${zoneRules.max}`}`;
        counter.classList.toggle("text-red-600", !valid);
        counter.classList.toggle("text-green-700", valid && count > 0);
    });
}

/** Enable/disable the +/- buttons on the search results for the active zone. */
export function refreshSearchPool(rules, activeZone) {
    document.querySelectorAll(".search-pool .card-wrapper").forEach((card) => {
        const plus = card.querySelector(".plus-btn");
        const minus = card.querySelector(".minus-btn");
        const inZone = activeZone?.querySelector(`.card-wrapper[data-card-id="${CSS.escape(card.dataset.cardId)}"]`);

        if (plus) plus.disabled = !validatePlacement(rules, activeZone, card).valid;
        if (minus) minus.disabled = !inZone;
    });
}

/** The deck as submitted to the server: { main: [{ id }, ...], ... }. Only ids are trusted server side. */
export function collectDeck(rules) {
    return Object.fromEntries(
        Object.keys(rules.zones).map((zoneId) => [
            zoneId,
            [...(document.getElementById(zoneId)?.children ?? [])].map((card) => ({ id: card.dataset.cardId })),
        ])
    );
}
