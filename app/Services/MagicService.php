<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class MagicService
{
    private string $apiPath = 'https://api.scryfall.com/cards/search';
    private int $cacheTTL = 600; // default cache TTL in seconds


    private function scryfall()
    {
        return Http::timeout(15)
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => 'YourAppName/1.0 (contact@yourapp.com)',
            ]);
    }

    /**
     * Fetch a card by its Scryfall ID.
     */
    public function fetchCardById(string $id): array
    {
        $response = $this->scryfall()
            ->get("https://api.scryfall.com/cards/{$id}");

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
        if (!$uri)
            return collect();

        $response = $this->scryfall()->get($uri);

        return $response->ok()
            ? collect($response->json('data', []))
            : collect();
    }

    /**
     * Fetch other cards from the same set, excluding the current card.
     */
    public function fetchRelatedSetCards(array $card, int $limit = 6): Collection
    {
        $setCode = $card['set'] ?? null;
        if (!$setCode)
            return collect();

        $cacheKey = "scryfall_set_{$setCode}_cards";

        $setCards = Cache::remember($cacheKey, $this->cacheTTL, function () use ($setCode) {
            $response = $this->scryfall()->get($this->apiPath, [
                'q' => "set:{$setCode} game:paper",
                'order' => 'name',
            ]);

            return $response->ok()
                ? $response->json('data', [])
                : [];
        });

        return collect($setCards)
            ->reject(
                fn($c) => ($c['oracle_id'] ?? null) === ($card['oracle_id'] ?? null)
            )
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
        $searchTerms = [];

        // Base search: paper cards only
        $searchTerms[] = 'game:paper';

        // Text search
        if (!empty($params['search'])) {
            $searchTerms[] = trim($params['search']);
        }

        // Filter by type
        if (!empty($params['type'])) {
            $searchTerms[] = "type:{$params['type']}";
        }

        // Filter by color
        if (!empty($params['color'])) {
            // Accept multiple colors as array or single string
            $colors = is_array($params['color']) ? implode('', $params['color']) : $params['color'];
            $searchTerms[] = "color:{$colors}";
        }

        // Filter by rarity
        if (!empty($params['rarity'])) {
            $searchTerms[] = "rarity:{$params['rarity']}";
        }

        // Filter by set (use Scryfall set code)
        if (!empty($params['set_name'])) {
            $searchTerms[] = "set:{$params['set_name']}";
        }

        // Combine all search terms
        $query = implode(' ', $searchTerms);

        // Generate cache key
        $cacheKey = 'magic-cards-' . md5($query . ($params['page'] ?? 1) . ($params['view'] ?? 'full'));

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($query, $params) {
            $response = $this->scryfall()->get($this->apiPath, [
                'q' => $query,
                'order' => 'name',
                'page' => $params['page'] ?? 1,
            ]);

            if ($response->status() === 404) {
                return [
                    'data' => [],
                    'has_more' => false,
                    'total_cards' => 0,
                ];
            }

            if ($response->failed()) {
                logger()->error('Scryfall API failure', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                abort(502, 'Scryfall API unavailable');
            }

            return $response->json();
        });
    }


    public function getFilterOptions(): array
    {
        return Cache::remember('magic-filter-options', 3600, function () {

            // 1. Card types (API-backed)
            $types = [];
            $typesResponse = $this->scryfall()->get('https://api.scryfall.com/catalog/card-types');
            if ($typesResponse->ok()) {
                $types = $typesResponse->json('data', []);
                sort($types);
            }

            // 2. Colors (STATIC)
            $colors = ['W', 'U', 'B', 'R', 'G', 'C'];

            // 3. Rarities (STATIC)
            $rarities = [
                'Common',
                'Uncommon',
                'Rare',
                'Mythic',
                'Special',
                'Bonus',
            ];

            // 4. Sets (API-backed)
            $sets = [];
            $setsResponse = $this->scryfall()->get('https://api.scryfall.com/sets');
            if ($setsResponse->ok()) {
                foreach ($setsResponse->json('data', []) as $s) {
                    $sets[$s['code']] = $s['name'];
                }
                ksort($sets);
            }

            return [
                'type' => $types,
                'color' => $colors,
                'rarity' => $rarities,
                'set_name' => $sets,
            ];
        });
    }
}
