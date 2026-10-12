import { describe, expect, test, vi } from "vitest";
import { likeButton, sendLike } from "./likes.js";

/** A fetch stand-in that records its calls and answers with the given status and body. */
function fakeFetch(status, body = {}) {
    return vi.fn(async () => ({ ok: status >= 200 && status < 300, status, json: async () => body }));
}

describe("sendLike", () => {
    test("likes with a POST and unlikes with a DELETE, asking for JSON", async () => {
        const fetch = fakeFetch(200, { liked: true, likes_count: 3 });

        expect(await sendLike("/decks/7/like", false, { fetch, token: "csrf" })).toEqual({ liked: true, likes_count: 3 });
        expect(fetch).toHaveBeenCalledWith("/decks/7/like", {
            method: "POST",
            headers: { Accept: "application/json", "X-CSRF-TOKEN": "csrf", "X-Requested-With": "XMLHttpRequest" },
        });

        await sendLike("/decks/7/like", true, { fetch, token: "csrf" });
        expect(fetch.mock.calls[1][1].method).toBe("DELETE");
    });

    test("fails when the server says no", async () => {
        await expect(sendLike("/decks/7/like", false, { fetch: fakeFetch(429), token: "csrf" })).rejects.toMatchObject({ status: 429 });
    });
});

describe("like button", () => {
    test("toggling takes the state and count from the server", async () => {
        const send = vi.fn(async () => ({ liked: true, likes_count: 13 }));
        const button = likeButton({ liked: false, count: 12, url: "/decks/7/like", send });

        await button.toggle({ submit: vi.fn() });

        expect(send).toHaveBeenCalledWith("/decks/7/like", false);
        expect(button).toMatchObject({ liked: true, count: 13, busy: false });
    });

    test("falls back to the plain form when the request fails, keeping the old state", async () => {
        const form = { submit: vi.fn() };
        const button = likeButton({ liked: true, count: 4, url: "/decks/7/like", send: async () => { throw new Error("offline"); } });

        await button.toggle(form);

        expect(form.submit).toHaveBeenCalledOnce();
        expect(button).toMatchObject({ liked: true, count: 4, busy: false });
    });

    test("when liking too fast, keeps the old state and doesn't send the form either", async () => {
        const form = { submit: vi.fn() };
        const tooMany = Object.assign(new Error("HTTP 429"), { status: 429 });
        const button = likeButton({ liked: false, count: 2, url: "/decks/7/like", send: async () => { throw tooMany; } });

        await button.toggle(form);

        expect(form.submit).not.toHaveBeenCalled();
        expect(button).toMatchObject({ liked: false, count: 2, busy: false });
    });

    test("names the button after the deck, its state and the count", () => {
        const button = likeButton({ liked: false, count: 3, url: "/decks/7/like", name: "Burn" });
        expect(button.label).toBe("Like Burn, 3 likes");

        Object.assign(button, { liked: true, count: 1 });
        expect(button.label).toBe("Unlike Burn, 1 like");
    });

    test("ignores clicks while a request is on its way", async () => {
        let finish;
        const send = vi.fn(() => new Promise((resolve) => (finish = resolve)));
        const button = likeButton({ liked: false, count: 0, url: "/decks/7/like", send });

        const first = button.toggle({ submit: vi.fn() });
        await button.toggle({ submit: vi.fn() });
        finish({ liked: true, likes_count: 1 });
        await first;

        expect(send).toHaveBeenCalledOnce();
        expect(button.count).toBe(1);
    });
});
