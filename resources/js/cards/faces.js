/**
 * Double-faced cards on the card page (x-data="cardFaces([...])"): the face shown and the button's label. A flip turns
 * the card edge-on (`flipping`, .card-face in app.css), shows the next face and turns it back; with reduced motion the
 * face just changes.
 */
export const HALF_FLIP_MS = 150;

/** Alpine data; the browser hooks are options so tests can run without a DOM. */
export function cardFaces(
    faces,
    {
        reducedMotion,
        wait = (ms, callback) => window.setTimeout(callback, ms),
        preload = (src) => (new Image().src = src),
    } = {}
) {
    if (!reducedMotion) {
        const query = window.matchMedia("(prefers-reduced-motion: reduce)");
        reducedMotion = () => query.matches;
    }

    // Loaded now, so the other face isn't blank when the card turns back.
    faces.slice(1).forEach(preload);

    return {
        faces,
        index: 0,
        flipping: false,

        get current() {
            return this.faces[this.index];
        },

        get label() {
            return this.faces.length > 2 ? "Show next face" : "Show other face";
        },

        next() {
            if (this.flipping) return;

            if (reducedMotion()) {
                this.turn();
                return;
            }

            // Edge-on halfway through: the face changes while it can't be seen.
            this.flipping = true;
            wait(HALF_FLIP_MS, () => {
                this.turn();
                this.flipping = false;
            });
        },

        turn() {
            this.index = (this.index + 1) % this.faces.length;
        },
    };
}
