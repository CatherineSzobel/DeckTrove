import { describe, expect, test } from "vitest";
import { charCounter } from "./char-counter.js";

describe("char counter", () => {
    test("starts from the given length", () => {
        expect(charCounter(12).counter).toBe("12 / 500");
    });

    test("counts what is typed", () => {
        const counter = charCounter(0);

        counter.count({ target: { value: "Hello" } });
        expect(counter.length).toBe(5);
        expect(counter.counter).toBe("5 / 500");
    });

    test("takes another maximum", () => {
        expect(charCounter(3, 140).counter).toBe("3 / 140");
    });
});
