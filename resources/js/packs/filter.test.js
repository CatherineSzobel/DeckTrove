import { describe, expect, test } from "vitest";
import { countLabel, matchesCard } from "./filter.js";

const dark = { name: "dark magician", rarity: "Ultra Rare" };

describe("pack card filter", () => {
    test("matches part of the name, ignoring case and spaces around it", () => {
        expect(matchesCard(dark, { name: "  MAGIC ", rarity: "" })).toBe(true);
        expect(matchesCard(dark, { name: "dragon", rarity: "" })).toBe(false);
    });

    test("matches the rarity exactly", () => {
        expect(matchesCard(dark, { name: "", rarity: "Ultra Rare" })).toBe(true);
        expect(matchesCard(dark, { name: "", rarity: "Ultra" })).toBe(false);
    });

    test("needs both to match", () => {
        expect(matchesCard(dark, { name: "dark", rarity: "Common" })).toBe(false);
    });

    test("words the count like the server", () => {
        expect(countLabel(2, 2, false)).toBe("2 cards");
        expect(countLabel(1, 1, false)).toBe("1 card");
        expect(countLabel(1200, 1200, false)).toBe("1,200 cards");
        expect(countLabel(1, 2, true)).toBe("1 of 2 cards");
        expect(countLabel(0, 1, true)).toBe("0 of 1 card");
    });
});
