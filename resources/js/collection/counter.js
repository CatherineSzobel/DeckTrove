/**
 * The quiet − n + (resources/views/components/collection/counter.blade.php), the card page's wishlist heart and its
 * collection row. Without JS their forms post and the page comes back to the card. With JS they post in place: the
 * counter shows the count the server answers, a refused change shows its message for a few seconds, and every change
 * is announced (`collection-changed` on window) with the card's totals, so the page's other counters for the same card
 * (the card page row, the heart) follow it.
 */
export const COULD_NOT_CHANGE = "Couldn’t change that card. Try again.";
export const ERROR_MS = 4000;
export const CHANGED_EVENT = "collection-changed";

const JSON_HEADERS = { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" };

/** POSTs a form as the server's JSON routes expect; resolves to { ok, body } (body null when it isn't JSON). */
async function post(send, formData, form) {
    const response = await send(form.action, { method: "POST", headers: JSON_HEADERS, body: formData(form) });
    let body = null;
    try {
        body = await response.json();
    } catch {
        body = null;
    }
    return { ok: response.ok, body };
}

/** Shows a message in `error` for ERROR_MS; an older message's timer doesn't clear a newer one. */
function errors(wait) {
    return {
        error: "",
        errorId: 0,

        showError(message) {
            const id = ++this.errorId;
            this.error = message;
            wait(() => {
                if (this.errorId === id) this.error = "";
            }, ERROR_MS);
        },
    };
}

const messageOf = (body) => (typeof body?.message === "string" && body.message !== "" ? body.message : COULD_NOT_CHANGE);

/** Whether an announced change is about this card. */
const sameCard = (self, detail) => detail?.game === self.game && detail?.card === self.card && detail?.card_totals;

/** Alpine data for x-collection.counter; the browser hooks are arguments so tests can run without a DOM. */
export function collectionCounter({
    send = (url, options) => window.fetch(url, options),
    formData = (form) => new FormData(form),
    wait = (callback, ms) => setTimeout(callback, ms),
    notify = (detail) => globalThis.dispatchEvent?.(new CustomEvent(CHANGED_EVENT, { detail })),
} = {}) {
    return {
        ...errors(wait),
        count: 0,
        removable: false,
        busy: false,
        game: "",
        card: "",
        list: "owned",
        printing: "",

        /** Reads the server-rendered state (data-count, data-removable, and which card, list and printing). */
        start(el) {
            const data = el.dataset;
            this.count = Number(data.count ?? 0);
            this.removable = data.removable === "1";
            this.game = data.game ?? "";
            this.card = data.card ?? "";
            this.list = data.list ?? "owned";
            this.printing = data.printing ?? "";
        },

        async submit(event) {
            event.preventDefault();
            if (this.busy) return;
            this.busy = true;

            try {
                const { ok, body } = await post(send, formData, event.currentTarget);

                if (ok && typeof body?.count === "number") {
                    this.count = body.count;
                    this.removable = body.removable === true;
                    notify({ game: this.game, card: this.card, list: this.list, printing: this.printing, count: this.count, removable: this.removable, card_totals: body.card });
                } else {
                    this.showError(messageOf(body));
                }
            } catch {
                this.showError(COULD_NOT_CHANGE);
            } finally {
                this.busy = false;
            }
        },

        /** The card-level owned counter (the card page row) takes the card's new total after any change to the card. */
        sync(detail) {
            if (!sameCard(this, detail) || this.list !== "owned" || this.printing !== "") return;

            this.count = detail.card_totals.owned;
            this.removable = detail.card_totals.removable === true;
        },
    };
}

/** Alpine data for the card page's "Want it" heart (a form with a hidden want=1|0, flipped on the client). */
export function collectionHeart({
    send = (url, options) => window.fetch(url, options),
    formData = (form) => new FormData(form),
    wait = (callback, ms) => setTimeout(callback, ms),
} = {}) {
    return {
        ...errors(wait),
        wanted: false,
        busy: false,
        game: "",
        card: "",

        start(el) {
            this.wanted = el.dataset.wanted === "1";
            this.game = el.dataset.game ?? "";
            this.card = el.dataset.card ?? "";
        },

        async submit(event) {
            event.preventDefault();
            if (this.busy) return;
            this.busy = true;

            try {
                const { ok, body } = await post(send, formData, event.currentTarget);

                if (ok && typeof body?.wanted === "boolean") {
                    this.wanted = body.wanted;
                } else {
                    this.showError(messageOf(body));
                }
            } catch {
                this.showError(COULD_NOT_CHANGE);
            } finally {
                this.busy = false;
            }
        },

        /** An owned copy takes the card off the wishlist on the server, so the heart follows the card's totals. */
        sync(detail) {
            if (sameCard(this, detail)) this.wanted = detail.card_totals.wanted > 0;
        },
    };
}

/** Alpine data for the card page's "In your collection" row: its note and accent border follow the card's totals. */
export function collectionRow() {
    return {
        game: "",
        card: "",
        owned: 0,
        note: "",

        start(el) {
            this.game = el.dataset.game ?? "";
            this.card = el.dataset.card ?? "";
            this.owned = Number(el.dataset.owned ?? 0);
            this.note = el.dataset.note ?? "";
        },

        sync(detail) {
            if (!sameCard(this, detail)) return;

            this.owned = detail.card_totals.owned;
            this.note = detail.card_totals.note;
        },
    };
}
