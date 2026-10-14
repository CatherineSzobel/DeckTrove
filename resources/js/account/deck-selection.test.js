import { describe, expect, test } from "vitest";
import { deckSelection } from "./deck-selection.js";

/** A stand-in for the form: its decks[] checkboxes. */
const fakeForm = (...checked) => {
    const boxes = checked.map((value) => ({ checked: value }));
    return { boxes, querySelectorAll: () => boxes };
};

describe("deck selection", () => {
    test("starts with nothing selected", () => {
        const selection = deckSelection(3);

        expect(selection.count).toBe(0);
        expect(selection.summary).toBe("");
        expect(selection.allSelected).toBe(false);
    });

    test("counts the ticked decks", () => {
        const selection = deckSelection(3);

        selection.sync(fakeForm(true, false, true));
        expect(selection.count).toBe(2);
        expect(selection.summary).toBe("2 selected");
        expect(selection.allSelected).toBe(false);

        selection.sync(fakeForm(true, true, true));
        expect(selection.allSelected).toBe(true);
    });

    test("select all ticks and unticks every deck", () => {
        const selection = deckSelection(3);
        const form = fakeForm(true, false, false);

        selection.toggleAll({ target: { checked: true, form } });
        expect(form.boxes.every((box) => box.checked)).toBe(true);
        expect(selection.count).toBe(3);

        selection.toggleAll({ target: { checked: false, form } });
        expect(form.boxes.some((box) => box.checked)).toBe(false);
        expect(selection.count).toBe(0);
    });

    test("without decks nothing counts as all selected", () => {
        expect(deckSelection(0).allSelected).toBe(false);
    });
});
