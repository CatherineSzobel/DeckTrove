/**
 * The pack page's deal-in (app.css, [data-deal] > :nth-child(-n+12), the grid's tiles): the first tiles deal in once on load. Afterwards the
 * grid loses data-deal, so a tile that filtering hides and shows again doesn't deal in a second time.
 */
// 12 tiles, 40 ms apart, 0.4 s each: the last ends at 840 ms.
export const DEAL_MS = 1000;

export function endDeal(grid, wait = setTimeout) {
    wait(() => grid.removeAttribute("data-deal"), DEAL_MS);
}
