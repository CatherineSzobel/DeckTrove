/**
 * Like buttons on deck cards and the deck page (resources/views/components/deck/like-button.blade.php). The button
 * is a real form, so it works without JS; with JS it toggles in place from the server's answer.
 */

/** The page's CSRF token, from the layout's <meta name="csrf-token">. */
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? "";
}

/**
 * Likes (POST) or unlikes (DELETE) a deck. Resolves to the server's { liked, likes_count }; throws when the request
 * fails, so the caller can fall back to submitting the form.
 */
export async function sendLike(url, liked, { fetch = globalThis.fetch, token = csrfToken() } = {}) {
    const response = await fetch(url, {
        method: liked ? "DELETE" : "POST",
        headers: { Accept: "application/json", "X-CSRF-TOKEN": token, "X-Requested-With": "XMLHttpRequest" },
    });

    if (!response.ok) throw Object.assign(new Error(`HTTP ${response.status}`), { status: response.status });

    return response.json();
}

/** Alpine component: x-data="likeButton({ liked, count, url })", with @submit.prevent="toggle($el)" on the form. */
export function likeButton({ liked = false, count = 0, url, send = sendLike }) {
    return {
        liked,
        count,
        busy: false,

        async toggle(form) {
            if (this.busy) return;
            this.busy = true;

            try {
                const result = await send(url, this.liked);
                this.liked = result.liked;
                this.count = result.likes_count;
            } catch (error) {
                // Too many likes in a minute: keep the button as it was rather than showing the error page.
                if (error.status === 429) return;

                // Anything else: let the server handle it the plain way (a redirect back, or the login page).
                form.submit();
            } finally {
                this.busy = false;
            }
        },
    };
}
