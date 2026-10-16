import { describe, expect, test } from "vitest";
import { searchFailedNotice } from "./search.js";

describe("searchFailedNotice", () => {
    test("asks to slow down when the search is rate limited", () => {
        expect(searchFailedNotice(429)).toBe("You’re searching very fast. Wait a minute, then try again.");
    });

    test("falls back to the generic message for other failures", () => {
        expect(searchFailedNotice(500)).toBe("The card search failed. Please try again.");
        expect(searchFailedNotice(undefined)).toBe("The card search failed. Please try again.");
    });
});
