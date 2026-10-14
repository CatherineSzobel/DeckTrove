/** The card page's printings (x-print-list): the extra ones open and close behind "Show N more". */
export function printList(hidden) {
    return {
        open: false,

        get label() {
            return this.open ? "Show less" : `Show ${hidden} more`;
        },

        toggle() {
            this.open = !this.open;
        },
    };
}
