/**
 * The page's confirm dialog (resources/views/components/confirm-dialog.blade.php), kept as an Alpine store so any
 * script can ask through window.confirmDialog() instead of the browser's confirm() pop-up.
 */
export function createConfirmStore() {
    return {
        open: false,
        title: "",
        message: "",
        confirmLabel: "",
        resolve: null,

        /** Opens the dialog; the promise resolves to true (confirmed) or false (cancelled). */
        ask({ title = "Are you sure?", message = "", confirmLabel = "Confirm" } = {}) {
            // Only one question at a time: an unanswered one counts as cancelled.
            this.resolve?.(false);
            Object.assign(this, { open: true, title, message, confirmLabel });

            return new Promise((resolve) => (this.resolve = resolve));
        },

        answer(confirmed) {
            const resolve = this.resolve;
            this.open = false;
            this.resolve = null;
            resolve?.(confirmed);
        },
    };
}
