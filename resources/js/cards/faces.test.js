import { describe, expect, test } from "vitest";
import { HALF_FLIP_MS, cardFaces } from "./faces.js";

/** cardFaces with the browser hooks faked: waits are kept in `waits` until run, preloads land in `loaded`. */
function flipping(faces, { reducedMotion = false } = {}) {
    const waits = [];
    const loaded = [];
    const card = cardFaces(faces, {
        reducedMotion: () => reducedMotion,
        wait: (ms, callback) => waits.push({ ms, callback }),
        preload: (src) => loaded.push(src),
    });

    return { card, waits, loaded };
}

describe("card faces", () => {
    test("starts on the front and flips back and forth", () => {
        const { card, waits } = flipping(["front.jpg", "back.jpg"]);

        expect(card.current).toBe("front.jpg");
        card.next();
        waits.shift().callback();
        expect(card.current).toBe("back.jpg");
        card.next();
        waits.shift().callback();
        expect(card.current).toBe("front.jpg");
    });

    test("labels the button for two faces or more", () => {
        expect(flipping(["a", "b"]).card.label).toBe("Show other face");
        expect(flipping(["a", "b", "c"]).card.label).toBe("Show next face");
    });

    test("loads the other faces up front", () => {
        expect(flipping(["a", "b", "c"]).loaded).toEqual(["b", "c"]);
    });

    test("turns edge-on first and changes the face halfway", () => {
        const { card, waits } = flipping(["front.jpg", "back.jpg"]);

        card.next();
        expect(card.flipping).toBe(true);
        expect(card.current).toBe("front.jpg");
        expect(waits).toHaveLength(1);
        expect(waits[0].ms).toBe(HALF_FLIP_MS);

        waits[0].callback();
        expect(card.flipping).toBe(false);
        expect(card.current).toBe("back.jpg");
    });

    test("a click during a flip is ignored", () => {
        const { card, waits } = flipping(["a", "b", "c"]);

        card.next();
        card.next();
        expect(waits).toHaveLength(1);
        waits[0].callback();
        expect(card.current).toBe("b");
    });

    test("with reduced motion the face changes at once, without turning", () => {
        const { card, waits } = flipping(["front.jpg", "back.jpg"], { reducedMotion: true });

        card.next();
        expect(card.current).toBe("back.jpg");
        expect(card.flipping).toBe(false);
        expect(waits).toHaveLength(0);
    });
});
