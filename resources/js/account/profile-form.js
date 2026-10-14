/**
 * The Edit profile form (x-data="profileForm(length)"): the About counter and the picture preview.
 * `urls` is the URL object (createObjectURL / revokeObjectURL), passed in so tests can fake it.
 */
export function profileForm(initialLength, max = 500, urls = URL) {
    return {
        length: initialLength,
        preview: null,

        get counter() {
            return `${this.length} / ${max}`;
        },

        count(event) {
            this.length = event.target.value.length;
        },

        pick(event) {
            if (this.preview) urls.revokeObjectURL(this.preview);
            const file = event.target.files?.[0];
            this.preview = file ? urls.createObjectURL(file) : null;
        },
    };
}
