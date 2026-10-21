/**
 * The card page's big card (x-data="cardTilt" on .card-holo): it leans toward a mouse or pen, and the glare follows,
 * through four CSS custom properties set at most once a frame. Touch and reduced motion leave it flat.
 */
export const MAX_TILT = 8;

const FLAT = { tiltX: 0, tiltY: 0, shineX: 50, shineY: 50 };
const clamp = (value) => Math.min(Math.max(value, 0), 1);
const round = (value) => Math.round(value * 100) / 100 || 0;

/** The tilt (degrees) and the shine's position (%) for a pointer at (clientX, clientY) over a card's rect. */
export function tiltFor(clientX, clientY, rect) {
    const x = clamp((clientX - rect.left) / rect.width);
    const y = clamp((clientY - rect.top) / rect.height);

    return {
        tiltX: round((0.5 - y) * 2 * MAX_TILT),
        tiltY: round((x - 0.5) * 2 * MAX_TILT),
        shineX: round(x * 100),
        shineY: round(y * 100),
    };
}

function apply(style, { tiltX, tiltY, shineX, shineY }) {
    style.setProperty("--tilt-x", `${tiltX}deg`);
    style.setProperty("--tilt-y", `${tiltY}deg`);
    style.setProperty("--shine-x", `${shineX}%`);
    style.setProperty("--shine-y", `${shineY}%`);
}

/** Alpine data; the two browser hooks are arguments so tests can run without a DOM. */
export function cardTilt({
    reducedMotion = () => window.matchMedia("(prefers-reduced-motion: reduce)").matches,
    nextFrame = (callback) => window.requestAnimationFrame(callback),
} = {}) {
    return {
        // The latest pointer position waiting for the next frame, or null.
        pointer: null,

        move(event) {
            if (event.pointerType === "touch" || reducedMotion()) return;

            const scheduled = this.pointer !== null;
            this.pointer = { x: event.clientX, y: event.clientY };
            if (scheduled) return;

            nextFrame(() => {
                if (this.pointer === null) return;

                apply(this.$el.style, tiltFor(this.pointer.x, this.pointer.y, this.$el.getBoundingClientRect()));
                this.pointer = null;
            });
        },

        reset() {
            this.pointer = null;
            apply(this.$el.style, FLAT);
        },
    };
}
