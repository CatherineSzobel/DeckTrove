/**
 * A live "12 / 500" counter for a textarea: x-data="charCounter(length)", @input="count($event)" on the textarea,
 * x-text="counter" on the counter. Used by Edit profile's About and the profile and deck comment forms.
 */
export function charCounter(initialLength, max = 500) {
    return {
        length: initialLength,

        get counter() {
            return `${this.length} / ${max}`;
        },

        count(event) {
            this.length = event.target.value.length;
        },
    };
}
