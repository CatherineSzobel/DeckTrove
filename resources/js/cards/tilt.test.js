import { describe, expect, test } from "vitest";
import { MAX_TILT, cardTilt, tiltFor } from "./tilt.js";

const rect = { left: 100, top: 200, width: 200, height: 400 };

/** A cardTilt on a fake element whose custom properties land in `props`; frames wait in `frames` until run. */
function tilting({ reducedMotion = false } = {}) {
    const frames = [];
    const props = {};
    const tilt = cardTilt({ reducedMotion: () => reducedMotion, nextFrame: (callback) => frames.push(callback) });
    tilt.$el = { style: { setProperty: (name, value) => (props[name] = value) }, getBoundingClientRect: () => rect };

    return { tilt, frames, props };
}

const mouse = (clientX, clientY) => ({ pointerType: "mouse", clientX, clientY });

describe("card tilt", () => {
    test("the centre is flat with the shine in the middle", () => {
        expect(tiltFor(200, 400, rect)).toEqual({ tiltX: 0, tiltY: 0, shineX: 50, shineY: 50 });
    });

    test("the card leans toward the pointer, at most 8 degrees", () => {
        expect(MAX_TILT).toBe(8);
        expect(tiltFor(300, 400, rect)).toEqual({ tiltX: 0, tiltY: 8, shineX: 100, shineY: 50 });
        expect(tiltFor(100, 400, rect)).toEqual({ tiltX: 0, tiltY: -8, shineX: 0, shineY: 50 });
        expect(tiltFor(200, 200, rect)).toEqual({ tiltX: 8, tiltY: 0, shineX: 50, shineY: 0 });
        expect(tiltFor(250, 500, rect)).toEqual({ tiltX: -4, tiltY: 4, shineX: 75, shineY: 75 });
    });

    test("a pointer outside the card counts as its edge", () => {
        expect(tiltFor(900, -50, rect)).toEqual({ tiltX: 8, tiltY: 8, shineX: 100, shineY: 0 });
    });

    test("moves are applied once a frame, from the last position", () => {
        const { tilt, frames, props } = tilting();

        tilt.move(mouse(150, 400));
        tilt.move(mouse(300, 400));
        expect(frames).toHaveLength(1);
        expect(props).toEqual({});

        frames[0]();
        expect(props).toEqual({ "--tilt-x": "0deg", "--tilt-y": "8deg", "--shine-x": "100%", "--shine-y": "50%" });

        tilt.move(mouse(200, 400));
        expect(frames).toHaveLength(2);
    });

    test("touch and reduced motion leave the card alone", () => {
        const touch = tilting();
        touch.tilt.move({ pointerType: "touch", clientX: 300, clientY: 400 });

        const reduced = tilting({ reducedMotion: true });
        reduced.tilt.move(mouse(300, 400));

        expect(touch.frames).toHaveLength(0);
        expect(reduced.frames).toHaveLength(0);
    });

    test("leaving goes back to flat, and a frame still pending does nothing", () => {
        const { tilt, frames, props } = tilting();

        tilt.move(mouse(300, 400));
        tilt.reset();
        frames[0]();

        expect(props).toEqual({ "--tilt-x": "0deg", "--tilt-y": "0deg", "--shine-x": "50%", "--shine-y": "50%" });
    });
});
