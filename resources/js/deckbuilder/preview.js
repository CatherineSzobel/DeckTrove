/**
 * Where to show a card preview next to a card (all values in viewport pixels): to the card's right, or to its left
 * when it would run off the window, and kept inside the window vertically.
 *
 * @param {{ left: number, top: number, right: number }} rect  the card's getBoundingClientRect()
 * @param {{ width: number, height: number }} viewport
 * @param {{ width: number, height: number }} size  the preview's size
 */
/**
 * Alpine state for the shared card preview (resources/views/components/deck/card-preview.blade.php): one panel,
 * shown next to the hovered or focused card. Used by the deck builder, the deck page, the card database and
 * the pack page.
 */
export function cardPreview() {
    return {
        preview: null,
        previewPos: { left: 0, top: 0 },
        previewFor: null,

        showPreview(card, element) {
            this.preview = card;
            this.previewFor = element;

            // Measure the panel once it shows this card, then place it beside the card.
            this.$nextTick(() => {
                const panel = this.$refs.preview;
                this.previewPos = previewPosition(
                    element.getBoundingClientRect(),
                    { width: window.innerWidth, height: window.innerHeight },
                    { width: panel.offsetWidth, height: panel.offsetHeight },
                );
            });
        },

        hidePreview() {
            this.preview = null;
        },

        /** Scrolling hides the preview, unless keyboard focus is on its card (focus scrolls it into view): then it follows. */
        hideOnScroll() {
            const element = document.activeElement;

            if (this.preview && element === this.previewFor && element.matches(":focus-visible")) {
                this.showPreview(this.preview, element);
                return;
            }

            this.hidePreview();
        },

        // A method, not a getter: the deck builder spreads this object into its own, which would freeze a getter.
        previewLine() {
            return previewMeta(this.preview);
        },
    };
}

export function previewPosition(rect, viewport, size, gap = 8) {
    const fitsRight = rect.right + gap + size.width <= viewport.width - gap;
    const left = fitsRight ? rect.right + gap : Math.max(gap, rect.left - gap - size.width);
    const top = Math.min(Math.max(gap, rect.top), Math.max(gap, viewport.height - size.height - gap));

    return { left, top };
}

/** The preview's type line, e.g. "Effect Monster · ATK 2500 / DEF 2100"; empty without a card. */
export function previewMeta(card) {
    return card ? [card.type, card.stats].filter(Boolean).join(" · ") : "";
}
