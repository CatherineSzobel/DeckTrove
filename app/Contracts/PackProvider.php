<?php

namespace App\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * A source of packs/sets for one series.
 */
interface PackProvider
{
    public function paginate(string $search = '', int $page = 1, int $perPage = 24): LengthAwarePaginator;

    /**
     * Fetch one pack, or abort with a 404 if it does not exist.
     *
     * $slug picks between products that share a code (Yu-Gi-Oh!); series with unique codes ignore it.
     */
    public function find(string $code, ?string $slug = null): array;

    /**
     * The pack's cards. Each card may carry a `printing` key with its set name, print code and
     * rarity in this pack, which the card mappers prefer over the card's first printing.
     */
    public function cards(string $code, ?string $slug = null): Collection;
}
