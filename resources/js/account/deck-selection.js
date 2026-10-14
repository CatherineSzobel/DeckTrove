/**
 * The Edit profile page's "Your decks" form (x-data="deckSelection(total)" @change="sync($el)"): how many decks are
 * ticked, "Select all", and whether the action buttons can be used.
 */
const boxes = (form) => [...form.querySelectorAll('input[name="decks[]"]')];

export function deckSelection(total) {
    return {
        count: 0,
        total,

        get summary() {
            return this.count ? `${this.count} selected` : "";
        },

        get allSelected() {
            return this.total > 0 && this.count === this.total;
        },

        sync(form) {
            this.count = boxes(form).filter((box) => box.checked).length;
        },

        toggleAll(event) {
            const { checked, form } = event.target;
            boxes(form).forEach((box) => (box.checked = checked));
            this.sync(form);
        },
    };
}
