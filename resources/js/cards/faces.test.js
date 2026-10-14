import { describe, expect, test } from "vitest";
import { cardFaces } from "./faces.js";

describe("card faces", () => {
    test("starts on the front and flips back and forth", () => {
        const faces = cardFaces(["front.jpg", "back.jpg"]);

        expect(faces.current).toBe("front.jpg");
        faces.next();
        expect(faces.current).toBe("back.jpg");
        faces.next();
        expect(faces.current).toBe("front.jpg");
    });

    test("labels the button for two faces or more", () => {
        expect(cardFaces(["a", "b"]).label).toBe("Show other face");
        expect(cardFaces(["a", "b", "c"]).label).toBe("Show next face");
    });
});
