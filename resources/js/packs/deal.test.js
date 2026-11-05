import { afterEach, describe, expect, test, vi } from "vitest";
import { DEAL_MS, endDeal } from "./deal.js";

afterEach(() => vi.useRealTimers());

describe("pack deal", () => {
    test("the deal is switched off once it has played", () => {
        vi.useFakeTimers();
        const removed = [];
        const grid = { removeAttribute: (name) => removed.push(name) };

        endDeal(grid);
        vi.advanceTimersByTime(DEAL_MS - 1);
        expect(removed).toEqual([]);

        vi.advanceTimersByTime(1);
        expect(removed).toEqual(["data-deal"]);
    });

    test("waits longer than the last tile's deal", () => {
        // 12 tiles, 40 ms apart, 400 ms each: the last ends at 840 ms.
        expect(DEAL_MS).toBeGreaterThan(11 * 40 + 400);
    });
});
