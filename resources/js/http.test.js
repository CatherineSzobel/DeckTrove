import { describe, expect, it, vi } from "vitest";
import { fetchPartial, JSON_HEADERS } from "./http.js";

describe("http", () => {
    it("asks for partial HTML as an AJAX request, passing the abort signal", async () => {
        const fetch = vi.fn().mockResolvedValue("response");
        const signal = new AbortController().signal;

        await expect(fetchPartial("/cards", { fetch, signal })).resolves.toBe("response");
        expect(fetch).toHaveBeenCalledWith("/cards", { headers: { "X-Requested-With": "XMLHttpRequest" }, signal });
    });

    it("asks for JSON as an AJAX request", () => {
        expect(JSON_HEADERS).toEqual({ "X-Requested-With": "XMLHttpRequest", Accept: "application/json" });
    });
});
