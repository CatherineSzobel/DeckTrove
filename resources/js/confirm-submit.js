/**
 * Asks in the confirm dialog before a form submits. The text comes from data-confirm (message), data-confirm-title
 * and data-confirm-button on the clicked submit button, or else on the form. On yes the form is submitted again
 * with the same button, so its name/value (e.g. action=delete) is sent; that second submit is let through.
 *
 * `ask` is window.confirmDialog (resolves to true or false).
 */
export function confirmSubmit(ask) {
    const confirmed = new WeakSet();

    return async (event) => {
        const form = event.target;

        if (confirmed.has(form)) {
            confirmed.delete(form);
            return;
        }

        const source = [event.submitter, form].find((element) => element?.dataset.confirm !== undefined);
        if (!source) return;

        event.preventDefault();
        const yes = await ask({
            title: source.dataset.confirmTitle,
            message: source.dataset.confirm,
            confirmLabel: source.dataset.confirmButton,
        });

        if (yes) {
            confirmed.add(form);
            form.requestSubmit(event.submitter ?? undefined);
        }
    };
}
