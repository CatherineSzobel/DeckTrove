import { describe, expect, test } from "vitest";
import { reloadWhenListChanges } from "./suggestions.js";

describe("collection page reloads", () => {
    const run = (detail, suggestion) => {
        const reloads = [];
        reloadWhenListChanges(detail, { isSuggestion: () => suggestion, reload: (hash) => reloads.push(hash) });
        return reloads;
    };

    test("a card added from the suggestions reloads the page at its tile", () => {
        expect(run({ card: "46986414", count: 1 }, true)).toEqual(["#card-46986414"]);
    });

    test("a card in the list taken down to 0 reloads the page, so it drops out", () => {
        expect(run({ card: "46986414", count: 0 }, false)).toEqual([""]);
    });

    test("a change that keeps a card in the list stays in place", () => {
        expect(run({ card: "46986414", count: 2 }, false)).toEqual([]);
    });
});
