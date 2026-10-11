/**
 * Where to show a card preview next to a card (all values in viewport pixels): to the card's right, or to its left
 * when it would run off the window, and kept inside the window vertically.
 *
 * @param {{ left: number, top: number, right: number }} rect  the card's getBoundingClientRect()
 * @param {{ width: number, height: number }} viewport
 * @param {{ width: number, height: number }} size  the preview's size
 */
export function previewPosition(rect, viewport, size, gap = 8) {
    const fitsRight = rect.right + gap + size.width <= viewport.width - gap;
    const left = fitsRight ? rect.right + gap : Math.max(gap, rect.left - gap - size.width);
    const top = Math.min(Math.max(gap, rect.top), Math.max(gap, viewport.height - size.height - gap));

    return { left, top };
}
