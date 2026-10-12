import { describe, expect, test } from "vitest";
import { previewMeta, previewPosition } from "./preview.js";

const viewport = { width: 1200, height: 800 };
const size = { width: 288, height: 200 };
const rect = (left, top, width = 80, height = 116) => ({ left, top, right: left + width, bottom: top + height });

describe("previewPosition", () => {
    test("opens to the right of the card when there is room", () => {
        expect(previewPosition(rect(100, 300), viewport, size)).toEqual({ left: 188, top: 300 });
    });

    test("opens to the left near the right edge", () => {
        expect(previewPosition(rect(1050, 300), viewport, size)).toEqual({ left: 754, top: 300 });
    });

    test("stays inside the window at the top and the bottom", () => {
        expect(previewPosition(rect(100, -50), viewport, size).top).toBe(8);
        expect(previewPosition(rect(100, 750), viewport, size).top).toBe(592);
    });
});

describe("previewMeta", () => {
    test("joins the type and the stats that are there", () => {
        expect(previewMeta({ type: "Effect Monster", stats: "ATK 2500 / DEF 2100" })).toBe("Effect Monster · ATK 2500 / DEF 2100");
        expect(previewMeta({ type: "Spell Card", stats: "" })).toBe("Spell Card");
        expect(previewMeta(null)).toBe("");
    });
});
