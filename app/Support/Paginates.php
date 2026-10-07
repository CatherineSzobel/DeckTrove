<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

trait Paginates
{
    /**
     * Cards per page for each card database view.
     */
    protected function perPage(?string $view): int
    {
        return match ($view) {
            'list' => 50,
            'images' => 48,
            default => 24,
        };
    }

    protected function currentPage(array $params): int
    {
        return max((int) ($params['page'] ?? 1), 1);
    }

    protected function paginator(array $items, int $total, int $perPage, int $page): LengthAwarePaginator
    {
        return new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'query' => request()->except('page'),
        ]);
    }
}
