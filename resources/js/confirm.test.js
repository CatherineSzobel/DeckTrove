import { describe, expect, test } from "vitest";
import { createConfirmStore } from "./confirm.js";

describe("confirm dialog store", () => {
    test("asking opens the dialog with the given text", () => {
        const store = createConfirmStore();
        store.ask({ title: "Delete this deck?", message: "This cannot be undone.", confirmLabel: "Delete deck" });

        expect(store).toMatchObject({ open: true, title: "Delete this deck?", message: "This cannot be undone.", confirmLabel: "Delete deck" });
    });

    test("the answer resolves the question and closes the dialog", async () => {
        const store = createConfirmStore();
        const confirmed = store.ask({ message: "Sure?" });
        store.answer(true);

        expect(await confirmed).toBe(true);
        expect(store.open).toBe(false);

        const cancelled = store.ask({ message: "Sure?" });
        store.answer(false);
        expect(await cancelled).toBe(false);
    });

    test("a new question cancels one that was still open", async () => {
        const store = createConfirmStore();
        const first = store.ask({ message: "First?" });
        store.ask({ message: "Second?" });

        expect(await first).toBe(false);
        expect(store.message).toBe("Second?");
    });

    test("falls back to a generic title and button", () => {
        const store = createConfirmStore();
        store.ask({ message: "Sure?" });

        expect(store).toMatchObject({ title: "Are you sure?", confirmLabel: "Confirm" });
    });
});
