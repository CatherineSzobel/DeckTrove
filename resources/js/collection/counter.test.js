import { describe, expect, test } from "vitest";
import { COULD_NOT_CHANGE, ERROR_MS, collectionCounter, collectionHeart, collectionRow } from "./counter.js";

const reply = (status, body) => ({ ok: status >= 200 && status < 300, status, json: async () => (body === undefined ? Promise.reject(new Error("no json")) : body) });

/** A counter on fake forms; sends are recorded, timers wait in `timers` until run. */
function counting(respond, start = { count: 2, removable: true }) {
    const sent = [];
    const timers = [];
    const counter = collectionCounter({
        send: async (url, options) => {
            sent.push({ url, options });
            return respond();
        },
        formData: (form) => ({ form, token: "t0ken" }),
        wait: (callback, ms) => timers.push({ callback, ms }),
    });
    Object.assign(counter, start);
    const form = { action: "https://decktrove.test/collection/add" };
    const event = { currentTarget: form, preventDefault: () => {} };

    return { counter, sent, timers, form, event };
}

describe("collection counter", () => {
    test("starts from the server-rendered count", () => {
        const { counter } = counting(() => reply(200, {}), {});

        counter.start({ dataset: { count: "3", removable: "0" } });

        expect([counter.count, counter.removable]).toEqual([3, false]);
    });

    test("posts in place as JSON with the form's token and shows the new count", async () => {
        const { counter, sent, form, event } = counting(() => reply(200, { message: "Added to your collection.", count: 3, removable: true }));

        await counter.submit(event);

        expect(sent).toHaveLength(1);
        expect(sent[0].url).toBe(form.action);
        expect(sent[0].options.method).toBe("POST");
        expect(sent[0].options.headers).toEqual({ Accept: "application/json", "X-Requested-With": "XMLHttpRequest" });
        expect(sent[0].options.body).toEqual({ form, token: "t0ken" });
        expect([counter.count, counter.removable, counter.error, counter.busy]).toEqual([3, true, "", false]);
    });

    test("turns minus off when nothing is left to take", async () => {
        const { counter, event } = counting(() => reply(200, { message: "Removed.", count: 1, removable: false }));

        await counter.submit(event);

        expect([counter.count, counter.removable]).toEqual([1, false]);
    });

    test("ignores a press while a change is on its way", async () => {
        const { counter, sent, event } = counting(() => reply(200, { count: 3, removable: true }));
        counter.busy = true;

        await counter.submit(event);

        expect(sent).toHaveLength(0);
    });

    test("shows a refused change for a few seconds and keeps the count", async () => {
        const { counter, timers, event } = counting(() => reply(422, { message: "You can keep at most 999 copies of a card." }));

        await counter.submit(event);

        expect([counter.count, counter.error]).toEqual([2, "You can keep at most 999 copies of a card."]);
        expect(timers[0].ms).toBe(ERROR_MS);
        timers[0].callback();
        expect(counter.error).toBe("");
    });

    test("an older error's timer doesn't clear a newer one", async () => {
        const { counter, timers, event } = counting(() => reply(422, { message: "First." }));
        await counter.submit(event);
        await counter.submit(event);

        timers[0].callback();

        expect(counter.error).toBe("First.");
    });

    test("says it couldn't change the card when the answer isn't JSON or the request fails", async () => {
        const html = counting(() => reply(500));
        await html.counter.submit(html.event);
        expect(html.counter.error).toBe(COULD_NOT_CHANGE);

        const offline = counting(() => Promise.reject(new TypeError("Failed to fetch")));
        await offline.counter.submit(offline.event);
        expect([offline.counter.error, offline.counter.busy]).toEqual([COULD_NOT_CHANGE, false]);
    });
});

describe("wishlist heart", () => {
    test("starts from the server-rendered state", () => {
        const heart = collectionHeart({ send: async () => reply(200, {}), formData: () => ({}) });

        heart.start({ dataset: { wanted: "1" } });

        expect(heart.wanted).toBe(true);
    });

    test("toggles in place from the server's answer", async () => {
        const heart = collectionHeart({ send: async () => reply(200, { message: "Added to your wishlist.", wanted: true }), formData: () => ({}) });
        heart.wanted = false;

        await heart.submit({ currentTarget: { action: "/collection/want" }, preventDefault: () => {} });

        expect(heart.wanted).toBe(true);
    });

    test("keeps its state when the request fails", async () => {
        const heart = collectionHeart({ send: async () => Promise.reject(new TypeError("offline")), formData: () => ({}) });
        heart.wanted = true;

        await heart.submit({ currentTarget: { action: "/collection/want" }, preventDefault: () => {} });

        expect(heart.wanted).toBe(true);
    });
});

describe("counters on one page follow each other", () => {
    const totals = { owned: 3, removable: true, wanted: 0, note: "2 by printing · 1 any printing" };
    const fromPrinting = { game: "yugioh", card: "46986414", list: "owned", printing: "LOB-EN005", count: 2, removable: true, card_totals: totals };

    test("a change is announced with the card and its totals", async () => {
        const announced = [];
        const counter = collectionCounter({
            send: async () => reply(200, { message: "Added.", count: 2, removable: true, card: totals }),
            formData: () => ({}),
            wait: () => {},
            notify: (detail) => announced.push(detail),
        });
        counter.start({ dataset: { count: "1", removable: "1", game: "yugioh", card: "46986414", list: "owned", printing: "LOB-EN005" } });

        await counter.submit({ currentTarget: { action: "/collection/add" }, preventDefault: () => {} });

        expect(announced).toEqual([fromPrinting]);
    });

    test("the card-level counter takes the card's new total; other cards and printing counters don't", () => {
        const make = (dataset) => {
            const counter = collectionCounter({ send: async () => reply(200, {}), formData: () => ({}), wait: () => {}, notify: () => {} });
            counter.start({ dataset: { count: "1", removable: "1", list: "owned", printing: "", ...dataset } });
            return counter;
        };
        const row = make({ game: "yugioh", card: "46986414" });
        const other = make({ game: "yugioh", card: "89631139" });
        const printing = make({ game: "yugioh", card: "46986414", printing: "SDY-006" });

        [row, other, printing].forEach((counter) => counter.sync(fromPrinting));

        expect([row.count, row.removable]).toEqual([3, true]);
        expect(other.count).toBe(1);
        expect(printing.count).toBe(1);
    });

    test("the heart and the row follow the card's totals", () => {
        const heart = collectionHeart({ send: async () => reply(200, {}), formData: () => ({}), wait: () => {} });
        heart.start({ dataset: { wanted: "1", game: "yugioh", card: "46986414" } });
        const row = collectionRow();
        row.start({ dataset: { game: "yugioh", card: "46986414", owned: "0", note: "Not in your collection yet" } });

        heart.sync(fromPrinting);
        row.sync(fromPrinting);

        expect(heart.wanted).toBe(false);
        expect([row.owned, row.note]).toEqual([3, "2 by printing · 1 any printing"]);
    });
});

describe("wishlist heart errors", () => {
    test("shows why the heart couldn't change, for a few seconds", async () => {
        const timers = [];
        const heart = collectionHeart({
            send: async () => reply(429, { message: "Too many changes. Wait a moment." }),
            formData: () => ({}),
            wait: (callback, ms) => timers.push({ callback, ms }),
        });

        await heart.submit({ currentTarget: { action: "/collection/want" }, preventDefault: () => {} });

        expect(heart.error).toBe("Too many changes. Wait a moment.");
        expect(timers[0].ms).toBe(ERROR_MS);
        timers[0].callback();
        expect(heart.error).toBe("");
    });

    test("says it couldn't change when offline", async () => {
        const heart = collectionHeart({ send: async () => Promise.reject(new TypeError("offline")), formData: () => ({}), wait: () => {} });

        await heart.submit({ currentTarget: { action: "/collection/want" }, preventDefault: () => {} });

        expect(heart.error).toBe(COULD_NOT_CHANGE);
    });
});
