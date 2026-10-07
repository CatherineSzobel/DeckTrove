<?php

namespace App\Services;

use App\Contracts\PackProvider;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Magic sets from the Scryfall API.
 */
class MagicPackService implements PackProvider
{
    public function __construct(private readonly MagicService $magic) {}

    public function paginate(string $search = '', int $page = 1, int $perPage = 24): LengthAwarePaginator
    {
        $sets = $this->sets();

        if ($search !== '') {
            $sets = $sets->filter(fn ($set) => stripos($set['name'], $search) !== false
                || stripos($set['code'], $search) !== false);
        }

        return new LengthAwarePaginator(
            $sets->forPage($page, $perPage)->values(),
            $sets->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()],
        );
    }

    public function find(string $code): array
    {
        $set = $this->sets()->firstWhere('code', strtolower($code));

        return $set ?? abort(404, 'Set not found');
    }

    public function cards(string $code): Collection
    {
        return collect($this->magic->searchAll('set:'.strtolower($code).' game:paper'));
    }

    private function sets(): Collection
    {
        return collect(Cache::remember('magic-sets', 3600, function () {
            return $this->magic->scryfall()->get('/sets')->throw()->json('data', []);
        }));
    }
}
