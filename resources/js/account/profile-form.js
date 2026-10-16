import { charCounter } from "../char-counter.js";

/**
 * The Edit profile form (x-data="profileForm(length)"): the About counter and the picture preview.
 * `urls` is the URL object (createObjectURL / revokeObjectURL), passed in so tests can fake it.
 */
export function profileForm(initialLength, max = 500, urls = URL) {
    return Object.assign(charCounter(initialLength, max), {
        preview: null,

        pick(event) {
            if (this.preview) urls.revokeObjectURL(this.preview);
            const file = event.target.files?.[0];
            this.preview = file ? urls.createObjectURL(file) : null;
        },
    });
}
