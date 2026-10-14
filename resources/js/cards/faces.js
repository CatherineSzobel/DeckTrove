/** Double-faced cards on the card page (x-data="cardFaces([...])"): the face shown and the button's label. */
export function cardFaces(faces) {
    return {
        faces,
        index: 0,

        get current() {
            return this.faces[this.index];
        },

        get label() {
            return this.faces.length > 2 ? "Show next face" : "Show other face";
        },

        next() {
            this.index = (this.index + 1) % this.faces.length;
        },
    };
}
