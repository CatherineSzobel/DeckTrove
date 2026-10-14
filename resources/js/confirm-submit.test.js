import { describe, expect, test, vi } from "vitest";
import { confirmSubmit } from "./confirm-submit.js";

const fakeForm = (dataset = {}) => ({ dataset, requestSubmit: vi.fn() });
const submitEvent = (form, submitter = null) => ({ target: form, submitter, preventDefault: vi.fn() });

describe("confirm before submit", () => {
    test("a form without data-confirm submits as usual", async () => {
        const ask = vi.fn();
        const event = submitEvent(fakeForm());

        await confirmSubmit(ask)(event);

        expect(ask).not.toHaveBeenCalled();
        expect(event.preventDefault).not.toHaveBeenCalled();
    });

    test("a form with data-confirm asks with its text, and submits on yes", async () => {
        const ask = vi.fn().mockResolvedValue(true);
        const form = fakeForm({ confirm: "Sure?", confirmTitle: "Delete?", confirmButton: "Delete" });
        const event = submitEvent(form);

        await confirmSubmit(ask)(event);

        expect(event.preventDefault).toHaveBeenCalled();
        expect(ask).toHaveBeenCalledWith({ title: "Delete?", message: "Sure?", confirmLabel: "Delete" });
        expect(form.requestSubmit).toHaveBeenCalledWith(undefined);
    });

    test("a button with data-confirm asks with its own text and submits with that button", async () => {
        const ask = vi.fn().mockResolvedValue(true);
        const form = fakeForm();
        const button = { dataset: { confirm: "Gone for good.", confirmTitle: "Delete the selected decks?", confirmButton: "Delete decks" } };
        const event = submitEvent(form, button);

        await confirmSubmit(ask)(event);

        expect(ask).toHaveBeenCalledWith({ title: "Delete the selected decks?", message: "Gone for good.", confirmLabel: "Delete decks" });
        expect(form.requestSubmit).toHaveBeenCalledWith(button);
    });

    test("another button in that form submits without asking", async () => {
        const ask = vi.fn();
        const event = submitEvent(fakeForm(), { dataset: {} });

        await confirmSubmit(ask)(event);

        expect(ask).not.toHaveBeenCalled();
        expect(event.preventDefault).not.toHaveBeenCalled();
    });

    test("no stops the submit", async () => {
        const ask = vi.fn().mockResolvedValue(false);
        const form = fakeForm({ confirm: "Sure?" });
        const event = submitEvent(form);

        await confirmSubmit(ask)(event);

        expect(event.preventDefault).toHaveBeenCalled();
        expect(form.requestSubmit).not.toHaveBeenCalled();
    });

    test("the confirmed resubmit goes through without asking again", async () => {
        const ask = vi.fn().mockResolvedValue(true);
        const handler = confirmSubmit(ask);
        const form = fakeForm({ confirm: "Sure?" });

        await handler(submitEvent(form));
        const resubmit = submitEvent(form);
        await handler(resubmit);

        expect(ask).toHaveBeenCalledTimes(1);
        expect(resubmit.preventDefault).not.toHaveBeenCalled();
    });
});
