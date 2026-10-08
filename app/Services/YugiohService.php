<?php

namespace App\Services;

use App\Contracts\CardProvider;
use App\Models\YugiohCard;
use App\Support\Paginates;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Yu-Gi-Oh! cards from the yugioh_cards table (filled by `php artisan yugioh:import`).
 */
class YugiohService implements CardProvider
{
    use Paginates;

    public const FILTER_CACHE_KEY = 'yugioh-filter-options';

    private const FILTER_KEYS = ['type', 'attribute', 'race', 'archetype'];

    public function search(array $params): LengthAwarePaginator
    {
        $term = trim($params['search'] ?? '');

        return YugiohCard::query()
            ->select(['data', 'images_hosted_at'])
            ->when($term !== '', function ($query) use ($term) {
                // Case-insensitive on every database: LIKE on SQLite/MySQL, ILIKE on Postgres.
                $like = "%$term%";

                $query->where(fn ($q) => $q
                    ->whereLike('name', $like)
                    ->orWhereLike('type', $like)
                    ->orWhereLike('race', $like)
                    ->orWhereLike('archetype', $like)
                    ->orWhereLike('desc', $like));
            })
            ->where(function ($query) use ($params) {
                foreach (self::FILTER_KEYS as $key) {
                    if (filled($params[$key] ?? null)) {
                        $query->where($key, $params[$key]);
                    }
                }
            })
            ->orderBy('name')
            ->paginate($this->perPage($params['view'] ?? null), page: $this->currentPage($params))
            ->withQueryString()
            ->through(fn (YugiohCard $card) => $card->toCardArray());
    }

    public function find(string $id): array
    {
        if (! ctype_digit($id)) {
            abort(404, 'Card not found');
        }

        return YugiohCard::find($id)?->toCardArray() ?? abort(404, 'Card not found');
    }

    public function findMany(array $ids): Collection
    {
        $ids = array_filter(array_map('strval', $ids), 'ctype_digit');

        if (! $ids) {
            return collect();
        }

        return YugiohCard::whereIn('id', $ids)->get()->mapWithKeys(fn (YugiohCard $card) => [$card->id => $card->toCardArray()]);
    }

    public function related(array $card, int $limit = 6): Collection
    {
        if (empty($card['archetype'])) {
            return collect();
        }

        return YugiohCard::where('archetype', $card['archetype'])
            ->whereKeyNot($card['id'])
            ->inRandomOrder()
            ->limit($limit)
            ->get()
            ->map(fn (YugiohCard $card) => $card->toCardArray());
    }

    public function random(int $count): Collection
    {
        return YugiohCard::inRandomOrder()
            ->limit($count)
            ->get()
            ->map(fn (YugiohCard $card) => $card->toCardArray());
    }

    public function filterOptions(): array
    {
        return Cache::rememberForever(self::FILTER_CACHE_KEY, fn () => collect(self::FILTER_KEYS)
            ->mapWithKeys(fn ($key) => [$key => YugiohCard::whereNotNull($key)->where($key, '!=', '')
                ->distinct()->orderBy($key)->pluck($key)->all()])
            ->all());
    }
}
