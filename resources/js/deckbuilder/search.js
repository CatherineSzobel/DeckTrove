/** The builder's notice when a card search fails; 429 = over the card-search rate limit (AppServiceProvider). */
export function searchFailedNotice(status) {
    return status === 429
        ? "You’re searching very fast. Wait a minute, then try again."
        : "The card search failed. Please try again.";
}
