import { describe, expect, test } from "vitest";
import { deckSearch } from "./deck-search.js";

const decks = [
    { game: "yugioh", text: "dragon rush big dragons" },
    { game: "magic", text: "mono red burn" },
    { game: "yugioh", text: "spellcasters" },
];

describe("deck search", () => {
    test("shows every deck at first", () => {
        const search = deckSearch(decks);

        expect([0, 1, 2].map((index) => search.matches(index))).toEqual([true, true, true]);
        expect(search.shown).toBe(3);
    });

    test("filters by series", () => {
        const search = deckSearch(decks);
        search.series = "yugioh";

        expect([0, 1, 2].map((index) => search.matches(index))).toEqual([true, false, true]);
        expect(search.shown).toBe(2);
    });

    test("searches case-insensitively, ignoring spaces around the query", () => {
        const search = deckSearch(decks);
        search.q = "  DRAGON ";

        expect(search.shown).toBe(1);
        expect(search.matches(0)).toBe(true);
    });

    test("tells the series switch which button is pressed, as an aria-pressed value", () => {
        const search = deckSearch(decks);

        expect([search.pressed("all"), search.pressed("magic")]).toEqual(["true", "false"]);
        search.series = "magic";
        expect([search.pressed("all"), search.pressed("magic")]).toEqual(["false", "true"]);
    });

    test("combines the search with the series", () => {
        const search = deckSearch(decks);
        search.q = "red";
        search.series = "yugioh";

        expect(search.shown).toBe(0);
    });
});
