import { describe, expect, test } from "vitest";
import { buildUrl, countFilters, syncValues, viewUrl } from "./query.js";

describe("card database URLs", () => {
    test("keeps filled fields and drops empty ones and the page", () => {
        const url = buildUrl("/yugioh/cards", [["view", "list"], ["search", " dark "], ["race", ""], ["page", "3"], ["type", "Effect Monster"]]);

        expect(url).toBe("/yugioh/cards?view=list&search=dark&type=Effect+Monster");
    });

    test("is the bare path when nothing is filled", () => {
        expect(buildUrl("/yugioh/cards", [["search", ""], ["race", ""]])).toBe("/yugioh/cards");
    });

    test("gives every field its value from the URL, empty when missing", () => {
        const params = new URLSearchParams("search=dark&race=Dragon");

        expect(syncValues(params, ["search", "race", "type"])).toEqual({ search: "dark", race: "Dragon", type: "" });
    });

    test("counts the filters that have a value", () => {
        const params = new URLSearchParams("search=dark&race=Dragon&type=&attribute=DARK");

        expect(countFilters(params, ["type", "race", "attribute", "archetype"])).toBe(2);
    });

    test("builds a view link from the current parameters, without the page and the default view", () => {
        const params = new URLSearchParams("search=dark&race=Dragon&page=3&view=list");

        expect(viewUrl("/yugioh/cards", params, "images")).toBe("/yugioh/cards?search=dark&race=Dragon&view=images");
        expect(viewUrl("/yugioh/cards", params, "full")).toBe("/yugioh/cards?search=dark&race=Dragon");
        expect(viewUrl("/yugioh/cards", new URLSearchParams(), "list")).toBe("/yugioh/cards?view=list");
    });
});
