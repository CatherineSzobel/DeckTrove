import { describe, expect, test } from "vitest";
import {
    allowedCopies,
    copiesOf,
    copyMessage,
    deckPrice,
    emptyZoneHint,
    formatProblems,
    limitTone,
    payload,
    placementError,
    removeLabel,
    removeTitle,
    uniqueCards,
    unpricedCount,
} from "./rules.js";

// Mirrors config/series.php.
const YGO = {
    zones: {
        main: { label: "Main Deck", min: 40, max: 60 },
        extra: { label: "Extra Deck", min: 0, max: 15 },
        side: { label: "Side Deck", min: 0, max: 15 },
    },
    max_copies: 3,
    extra_types: ["Fusion", "Synchro", "XYZ", "Link"],
    unlimited_types: [],
    formats: { tcg: { label: "TCG Advanced" }, ocg: { label: "OCG" } },
    limit_labels: { 0: "Forbidden", 1: "Limited", 2: "Semi-Limited" },
};

const MTG = {
    zones: { main: { label: "Main Deck", min: 60, max: 100 }, side: { label: "Sideboard", min: 0, max: 15 } },
    max_copies: 4,
    extra_types: [],
    unlimited_types: ["Basic Land"],
    formats: { standard: { label: "Standard" }, modern: { label: "Modern" } },
    limit_labels: { 0: "Not legal", 1: "Restricted" },
};

const card = (id, name, type, limits = {}, price = 0.1) => ({ id, name, type, limits, price });

const darkMagician = card("1", "Dark Magician", "Normal Monster Spellcaster");
const charity = card("2", "Graceful Charity", "Spell Card Normal", { tcg: 1, ocg: 2 });
const pot = card("3", "Pot of Greed", "Spell Card Normal", { tcg: 0 });
const utopia = card("4", "Number 39: Utopia", "XYZ Monster Warrior");
const forest = card("5", "Forest", "Basic Land — Forest");
const bannedLand = card("6", "Odd Land", "Basic Land — Odd", { modern: 0 });

const copies = (c, n) => Array.from({ length: n }, () => c);

describe("allowedCopies", () => {
    test("uses the normal limit without a format", () => {
        expect(allowedCopies(YGO, charity, "")).toBe(3);
    });

    test("takes the lower of the normal and the format limit", () => {
        expect(allowedCopies(YGO, charity, "tcg")).toBe(1);
        expect(allowedCopies(YGO, charity, "ocg")).toBe(2);
        expect(allowedCopies(YGO, pot, "tcg")).toBe(0);
    });

    test("a format without an entry for the card adds no limit", () => {
        expect(allowedCopies(YGO, darkMagician, "tcg")).toBe(3);
        expect(allowedCopies(YGO, pot, "ocg")).toBe(3);
    });

    test("unlimited types lift only the normal limit", () => {
        expect(allowedCopies(MTG, forest, "")).toBe(Infinity);
        expect(allowedCopies(MTG, forest, "modern")).toBe(Infinity);
        expect(allowedCopies(MTG, bannedLand, "modern")).toBe(0);
    });
});

describe("copyMessage", () => {
    test("uses the server's wording", () => {
        expect(copyMessage(YGO, pot, "tcg")).toBe("Pot of Greed is not allowed in TCG Advanced.");
        expect(copyMessage(YGO, charity, "tcg")).toBe("You can only have 1 copy of Graceful Charity in TCG Advanced.");
        expect(copyMessage(YGO, charity, "ocg")).toBe("You can only have 2 copies of Graceful Charity in OCG.");
        expect(copyMessage(YGO, darkMagician, "")).toBe("You can only have 3 copies of Dark Magician.");
        expect(copyMessage(YGO, darkMagician, "tcg")).toBe("You can only have 3 copies of Dark Magician.");
    });
});

describe("placementError", () => {
    const empty = () => ({ main: [], extra: [], side: [] });

    test("allows a card that fits", () => {
        expect(placementError(YGO, empty(), "main", darkMagician, "")).toBeNull();
    });

    test("refuses a full zone", () => {
        const zones = { ...empty(), side: copies(darkMagician, 15) };
        expect(placementError(YGO, zones, "side", charity, "")).toBe("Side Deck cannot have more than 15 cards.");
    });

    test("keeps extra deck monsters and other cards apart", () => {
        expect(placementError(YGO, empty(), "extra", charity, "")).toBe("Graceful Charity does not belong in the Extra Deck.");
        expect(placementError(YGO, empty(), "main", utopia, "")).toBe("Number 39: Utopia belongs in the Extra Deck.");
        expect(placementError(YGO, empty(), "side", utopia, "")).toBeNull();
    });

    test("counts copies across zones", () => {
        expect(placementError(YGO, { ...empty(), main: copies(darkMagician, 3) }, "side", darkMagician, ""))
            .toBe("You can only have 3 copies of Dark Magician.");
        expect(placementError(YGO, { ...empty(), main: [charity] }, "side", charity, "tcg"))
            .toBe("You can only have 1 copy of Graceful Charity in TCG Advanced.");
    });

    test("refuses a banned card in its format", () => {
        expect(placementError(YGO, empty(), "main", pot, "tcg")).toBe("Pot of Greed is not allowed in TCG Advanced.");
    });

    test("an unknown zone asks to pick one", () => {
        expect(placementError(YGO, empty(), "commander", darkMagician, "")).toBe("Select a deck zone first.");
    });
});

describe("formatProblems", () => {
    test("lists each card over its limit once, counting all zones", () => {
        const zones = { main: [charity, pot, darkMagician], extra: [], side: [charity] };

        expect(formatProblems(YGO, zones, "tcg")).toEqual([
            { id: "2", message: "You can only have 1 copy of Graceful Charity in TCG Advanced." },
            { id: "3", message: "Pot of Greed is not allowed in TCG Advanced." },
        ]);
    });

    test("is empty for a casual deck within the normal limits", () => {
        expect(formatProblems(YGO, { main: [charity, charity, pot], extra: [], side: [] }, "")).toEqual([]);
    });
});

describe("prices", () => {
    test("adds up every copy and counts cards without a price", () => {
        const unpriced = card("7", "Unpriced", "Spell Card", {}, null);
        const zones = { main: [...copies(darkMagician, 3), unpriced], side: [card("8", "Pricey", "Trap Card", {}, 2.5)] };

        expect(deckPrice(zones)).toBeCloseTo(2.8);
        expect(unpricedCount(zones)).toBe(1);
        expect(deckPrice({ main: [], side: [] })).toBe(0);
    });
});

describe("uniqueCards", () => {
    test("lists each card in the deck once, in deck order", () => {
        const zones = { main: [darkMagician, charity, darkMagician], extra: [utopia], side: [charity] };

        expect(uniqueCards(zones).map((c) => c.id)).toEqual(["1", "2", "4"]);
        expect(uniqueCards({ main: [], side: [] })).toEqual([]);
    });
});

describe("copiesOf and payload", () => {
    test("builds what the save form sends", () => {
        const zones = { main: [darkMagician, darkMagician], extra: [], side: [charity] };

        expect(copiesOf(zones, "1")).toBe(2);
        expect(payload(zones)).toEqual({ main: [{ id: "1" }, { id: "1" }], extra: [], side: [{ id: "2" }] });
    });
});

describe("zone texts", () => {
    test("a card in a zone is named for removal, with its problem when it has one", () => {
        expect(removeLabel("Dark Magician", "Main Deck", null)).toBe("Remove Dark Magician from the Main Deck");
        expect(removeLabel("Pot of Greed", "Main Deck", "Pot of Greed is not allowed in TCG Advanced."))
            .toBe("Remove Pot of Greed from the Main Deck. Pot of Greed is not allowed in TCG Advanced.");
        expect(removeTitle(null)).toBe("Click to remove");
        expect(removeTitle("Too many copies.")).toBe("Too many copies. Click to remove.");
    });

    test("an empty zone explains how to add cards, depending on whether it's the active one", () => {
        expect(emptyZoneHint(true)).toBe("Use + on a card in the search to add it here.");
        expect(emptyZoneHint(false)).toBe("Choose “Add cards here”, then use + in the search.");
    });

    test("limit tags are red when forbidden, amber at one and blue above", () => {
        expect([limitTone(0), limitTone(1), limitTone(2), limitTone(null)]).toEqual(["text-danger", "text-amber-300", "text-sky-300", ""]);
    });
});
