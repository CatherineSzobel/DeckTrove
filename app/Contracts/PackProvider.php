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
     */
    public function find(string $code): array;

    public function cards(string $code): Collection;
}
