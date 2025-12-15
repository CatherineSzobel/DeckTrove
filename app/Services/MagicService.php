<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class MagicService
{
    private string $apiPath = 'https://api.scryfall.com/cards/search';
    private int $cacheTTL = 600; // default cache TTL in seconds

    /**
     * Fetch a card by its Scryfall ID.
     */
    public function fetchCardById(string $id): array
    {
        $response = Http::timeout(5)->get("https://api.scryfall.com/cards/{$id}");

        if ($response->failed() || ($response->json('object') ?? '') === 'error') {
            abort(404, 'Card not found');
        }

        return $response->json();
    }

    /**
     * Fetch all printings of a card.
     */
    public function fetchCardPrints(array $card): Collection
    {
        $uri = $card['prints_search_uri'] ?? null;
        if (!$uri) return collect();

        $response = Http::timeout(5)->get($uri);
        return $response->ok() ? collect($response->json('data', [])) : collect();
    }

    /**
     * Fetch other cards from the same set, excluding the current card.
     */
    public function fetchRelatedSetCards(array $card, int $limit = 6): Collection
    {
        $setCode = $card['set'] ?? null;
        if (!$setCode) return collect();

        $cacheKey = "scryfall_set_{$setCode}_cards";

        $setCards = Cache::remember($cacheKey, $this->cacheTTL, function () use ($setCode) {
            $resp = Http::timeout(5)->get($this->apiPath, [
                'q' => "set:{$setCode} game:paper",
                'order' => 'name'
            ]);

            return $resp->ok() ? $resp->json('data', []) : [];
        });

        return collect($setCards)
            ->reject(fn($c) => ($c['oracle_id'] ?? null) === ($card['oracle_id'] ?? null))
            ->shuffle()
            ->take($limit);
    }

    /**
     * Determine items per page based on view type.
     */
    public function itemsPerPage(string $view): int
    {
        return match ($view) {
            'list' => 50,
            'image' => 48,
            default => 24,
        };
    }

    /**
     * Fetch paginated cards with optional search term.
     */
    public function fetchCards(array $params): array
    {
        $searchKey = $params['search'] ? md5($params['search']) : '';
        $cacheKey = "magic-cards-page-{$params['page']}-view-{$params['view']}" . ($searchKey ? "-search-{$searchKey}" : '');

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($params) {
            $queryParams = [
                'order' => 'name',
                'page' => $params['page'],
                'q' => $params['search'] ? $params['search'] . ' game:paper' : 'game:paper',
            ];

            $response = Http::timeout(5)->get($this->apiPath, $queryParams);

            if ($response->failed()) {
                abort(500, 'Failed to fetch cards from API');
            }

            return $response->json();
        });
    }
}
