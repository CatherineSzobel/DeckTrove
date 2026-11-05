import { describe, expect, test } from "vitest";
import { SETTLE_MS, totalsRefresher } from "./totals.js";

describe("collection totals", () => {
    const setup = (responses) => {
        const el = { innerHTML: "old" };
        const timers = [];
        const urls = [];
        const refresh = totalsRefresher({
            fetch: (url) => {
                urls.push(url);
                return responses.shift();
            },
            url: () => "/collection/wishlist?series=yugioh",
            target: () => el,
            wait: (callback, ms) => timers.push({ callback, ms, cancelled: false }) - 1,
            cancel: (id) => {
                if (id !== null) timers[id].cancelled = true;
            },
        });
        const fire = () => Promise.all(timers.filter((t) => !t.cancelled && !t.fired).map((t) => {
            t.fired = true;
            return t.callback();
        }));
        return { el, timers, urls, refresh, fire };
    };

    const ok = (html) => Promise.resolve({ ok: true, text: () => Promise.resolve(html) });

    test("a change swaps in the server's totals once it settles", async () => {
        const { el, timers, urls, refresh, fire } = setup([ok("new")]);
        refresh();
        expect(timers[0].ms).toBe(SETTLE_MS);
        await fire();
        await Promise.resolve();
        expect(urls).toEqual(["/collection/wishlist?series=yugioh"]);
        expect(el.innerHTML).toBe("new");
    });

    test("quick clicks make one request", async () => {
        const { urls, refresh, fire } = setup([ok("new")]);
        refresh();
        refresh();
        refresh();
        await fire();
        expect(urls).toHaveLength(1);
    });

    test("a failed answer keeps the totals", async () => {
        const { el, refresh, fire } = setup([Promise.resolve({ ok: false, text: () => Promise.resolve("error page") })]);
        refresh();
        await fire();
        expect(el.innerHTML).toBe("old");
    });

    test("an answer overtaken by a newer one is dropped", async () => {
        let resolveFirst;
        const first = new Promise((resolve) => (resolveFirst = resolve));
        const { el, refresh, fire } = setup([first, ok("second")]);
        refresh();
        const firstLoad = fire();
        refresh();
        await fire();
        resolveFirst({ ok: true, text: () => Promise.resolve("first") });
        await firstLoad;
        expect(el.innerHTML).toBe("second");
    });
});
