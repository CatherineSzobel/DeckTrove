<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MagicService
{
    private string $apiPath = 'https://api.scryfall.com/cards/search';
    private int $cacheTTL = 600; // default cache TTL in seconds


    private function scryfall()
    {
        return Http::timeout(15)
            ->acceptJson()
            ->withHeaders(['User-Agent' => 'YourAppName/1.0 (contact@yourapp.com)']);
    }

    public function fetchCardById(string $id): array
    {
        $response = $this->scryfall()->get("https://api.scryfall.com/cards/{$id}");

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
        if (!$setCode) return collect();

        $cacheKey = "scryfall_set_{$setCode}_all_cards";

        $allSetCards = Cache::remember($cacheKey, $this->cacheTTL, function () use ($setCode) {
            $response = $this->scryfall()->get($this->apiPath, [
                'q' => "set:{$setCode} game:paper",
                'order' => 'name',
                'page' => 1,
            ]);

            return $response->ok() ? $response->json('data', []) : [];
        });

        return collect($allSetCards)
            ->reject(fn($c) => ($c['oracle_id'] ?? null) === ($card['oracle_id'] ?? null))
            ->shuffle()
            ->take($limit);
    }

    public function itemsPerPage(string $view): int
    {
        return match ($view) {
            'list' => 50,
            'image' => 48,
            default => 24,
        };
    }

    private function cacheKey(array $params): string
    {
        return 'magic-cards-' . md5(json_encode($params));
    }

    public function fetchCards(array $params): array
    {
        // Build Scryfall query
        $queryParts = collect([
            'game:paper',
            $this->search($params),
            $this->type($params),
            $this->color($params),
            $this->rarity($params),
            $this->set($params),
        ])->filter()->implode(' ');

        $cacheKey = $this->cacheKey($params);

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($queryParts, $params) {
            // Ensure page is at least 1
            $page = max((int)($params['page'] ?? 1), 1);

            $response = $this->scryfall()->get($this->apiPath, [
                'q' => $queryParts,
                'order' => 'name',
                'page' => $page,
            ]);

            // If 404 → treat as no results
            if ($response->status() === 404) {
                return [
                    'data' => [],
                    'has_more' => false,
                    'total_cards' => 0,
                ];
            }

            // If other 4xx → likely bad filter/query → return empty results
            if ($response->clientError()) {
                logger()->warning('Scryfall client error', [
                    'status' => $response->status(),
                    'query' => $queryParts,
                    'params' => $params,
                ]);
                return [
                    'data' => [],
                    'has_more' => false,
                    'total_cards' => 0,
                ];
            }

            // 5xx → API/server unavailable → abort
            if ($response->serverError()) {
                logger()->error('Scryfall API server error', [
                    'status' => $response->status(),
                    'query' => $queryParts,
                    'params' => $params,
                ]);
                abort(502, 'Scryfall API unavailable');
            }

            // Otherwise return successful response
            return $response->json();
        });
    }


    protected function search(array $params): ?string
    {
        $term = $params['search'] ?? null;

        if (!filled($term)) {
            return null;
        }

        return Str::of($term)
            ->limit(100) // equivalent to mb_substr
            ->replaceMatches('/[^a-zA-Z0-9\s\-\',:.!]/u', '') // remove unwanted characters
            ->trim()
            ->toString();
    }


    protected function type(array $params): ?string
    {
        return filled($params['type'] ?? null) ? "type:{$params['type']}" : null;
    }

    protected function color(array $params): ?string
    {
        if (empty($params['color'])) return null;
        $colors = is_array($params['color']) ? implode('', $params['color']) : $params['color'];
        return "color:{$colors}";
    }

    protected function rarity(array $params): ?string
    {
        return filled($params['rarity'] ?? null) ? "rarity:{$params['rarity']}" : null;
    }

    protected function set(array $params): ?string
    {
        return filled($params['set_name'] ?? null) ? "set:{$params['set_name']}" : null;
    }

    public function getFilterOptions(): array
    {
        return Cache::remember('magic-filter-options', 3600, function () {
            $types = [];
            $typesResponse = $this->scryfall()->get('https://api.scryfall.com/catalog/card-types');
            if ($typesResponse->ok()) {
                $types = $typesResponse->json('data', []);
                sort($types);
            }

            $colors = ['W', 'U', 'B', 'R', 'G', 'C'];
            $rarities = ['Common', 'Uncommon', 'Rare', 'Mythic', 'Special', 'Bonus'];

            $sets = [];
            $setsResponse = $this->scryfall()->get('https://api.scryfall.com/sets');
            if ($setsResponse->ok()) {
                foreach ($setsResponse->json('data', []) as $s) $sets[$s['code']] = $s['name'];
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
