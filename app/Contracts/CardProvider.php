<?php

namespace App\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * A source of raw card data for one series (an external API or a local dataset).
 */
interface CardProvider
{
    /**
     * Search cards. $params holds the series' filter keys plus `search`, `page` and `view`.
     */
    public function search(array $params): LengthAwarePaginator;

    /**
     * Fetch one card, or abort with a 404 if it does not exist.
     */
    public function find(string $id): array;

    /**
     * Fetch many cards at once, keyed by their id. Unknown ids are left out.
     */
    public function findMany(array $ids): Collection;

    /**
     * A few cards related to the given card, for "more like this" sections.
     */
    public function related(array $card, int $limit = 6): Collection;

    /**
     * The options for each filter dropdown, keyed by filter name.
     * A list of values, or a value => label map.
     */
    public function filterOptions(): array;
}
