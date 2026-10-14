import { describe, expect, test } from "vitest";
import { printList } from "./print-list.js";

describe("print list", () => {
    test("starts closed and says how many more there are", () => {
        const list = printList(3);

        expect(list.open).toBe(false);
        expect(list.label).toBe("Show 3 more");
    });

    test("toggles open and closed", () => {
        const list = printList(3);

        list.toggle();
        expect([list.open, list.label]).toEqual([true, "Show less"]);
        list.toggle();
        expect([list.open, list.label]).toEqual([false, "Show 3 more"]);
    });
});
