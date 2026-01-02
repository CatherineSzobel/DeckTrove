<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;


class MagicService
{
    private string $apiPath = 'https://api.scryfall.com/cards/search';
    private int $cacheTTL = 600;


    private function scryfall()
    {
        return Http::timeout(15)
            ->acceptJson()
            ->withHeaders(
                ['User-Agent' => 'YourAppName/1.0 (contact@yourapp.com)']
            )
            ->beforeSending(function ($request, $options) {
                Log::info('Scryfall API request', [
                    'method' => $request->method(),
                    'url' => (string) $request->url(),
                    'query' => $options['query'] ?? null,
                ]);
            });
    }

    protected function getWithLogging(string $url, array $params = [])
    {
        $start = microtime(true);

        $response = $this->scryfall()->get($url, $params);

        Log::info('Scryfall API call', [
            'url' => $url,
            'params' => $params,
            'status' => $response->status(),
            'duration_ms' => round((microtime(true) - $start) * 1000),
        ]);

        return $response;
    }


    public function fetchCardById(string $id): array
    {
        $response = $this->scryfall()->get("https://api.scryfall.com/cards/{$id}");

        if ($response->failed() || ($response->json('object') ?? '') === 'error') {
            abort(404, 'Card not found');
        }

        return $response->json();
    }
    public function fetchCards(array $params): array
    {
        // Ensure page is at least 1
        $page = max((int)($params['page'] ?? 1), 1);
        $view = $params['view'] ?? 'default';

        // Use the same logic as deck-builder search
        $result = $this->searchCards($params);

        return [
            'data' => $result['data'] ?? [],
            'total_cards' => $result['total_cards'] ?? count($result['data'] ?? []),
            'page' => $page,
            'per_page' => $this->itemsPerPage($view),
        ];
    }

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

    public function fetchCollection(array $ids): Collection
    {
        if (empty($ids)) return collect();

        return Cache::remember('magic-collection-' . md5(json_encode($ids)), 3600, function () use ($ids) {
            $response = $this->scryfall()->post('https://api.scryfall.com/cards/collection', [
                'identifiers' => collect($ids)->map(fn($id) => ['id' => $id])
            ]);
            return $response->ok() ? collect($response->json('data'))->keyBy('id') : collect();
        });
    }

    public function searchCards(array $params): array
    {

        $query = collect([
            'game:paper',
            $this->search($params),
            $this->type($params),
            $this->color($params),
            $this->rarity($params),
            $this->set($params),
        ])->filter()->implode(' ');

        return Cache::remember('magic-search-' . md5(json_encode($params)), 
        $this->cacheTTL, function () use ($query, $params) {
            $page = max((int)($params['page'] ?? 1), 1);

            $response = $this->getWithLogging($this->apiPath, [
                'q' => $query,
                'order' => 'name',
                'page' => $page,
            ]);

            if ($response->status() === 404 || $response->clientError()) {

                                logger()->warning('Scryfall client error', [
                    'status' => $response->status(),
                    'params' => $params,
                ]);
                return ['data' => [], 'total_cards' => 0, 'has_more' => false];
            }

            if ($response->serverError()) {
                abort(502, 'Scryfall API unavailable');
            }

            return $response->json();
        });
    }

    public function searchForDeckBuilder(array $params, string $view): LengthAwarePaginator
    {
        $page = max((int)($params['page'] ?? 1), 1);
        $perPage = $this->itemsPerPage($view);

        try {
            $result = $this->searchCards($params);

            return new LengthAwarePaginator(
                $result['data'] ?? [],
                $result['total_cards'] ?? 0,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } catch (RequestException $e) {
            Log::warning('Magic API search failed', [
                'message' => $e->getMessage(),
                'params' => $params,
                'status' => optional($e->response)->status(),
            ]);

            return new LengthAwarePaginator(
                [],
                0,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }
    }

    protected function search(array $params): ?string
    {
        $search = trim($params['search'] ?? '');

        if (strlen($search) < 3) {
            return null;
        }

        return Str::of($params['search'])
            ->limit(100)
            ->replaceMatches('/[^a-zA-Z0-9\s\-\',:.!]/u', '')
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
        return 'color:' . (is_array($params['color']) ? implode('', $params['color']) : $params['color']);
    }

    protected function rarity(array $params): ?string
    {
        return filled($params['rarity'] ?? null) ? "rarity:{$params['rarity']}" : null;
    }

    protected function set(array $params): ?string
    {
        return filled($params['set_name'] ?? null) ? "set:{$params['set_name']}" : null;
    }

    public function itemsPerPage(string $view): int
    {
        return match ($view) {
            'list' => 50,
            'image' => 48,
            default => 24,
        };
    }

    public function getFilterOptions(): array
    {
        return Cache::remember('magic-filter-options', 3600, function () {
            $types = $this->scryfall()
                ->get('https://api.scryfall.com/catalog/card-types')
                ->json('data', []);

            sort($types);
            $sets = [];
            $setsResponse = $this->scryfall()->get('https://api.scryfall.com/sets');
            if ($setsResponse->ok()) {
                foreach ($setsResponse->json('data', []) as $s)
                    $sets[$s['code']] = $s['name'];
                ksort($sets);
            }

            return [
                'type' => $types,
                'color' => ['W', 'U', 'B', 'R', 'G', 'C'],
                'rarity' => ['Common', 'Uncommon', 'Rare', 'Mythic', 'Special', 'Bonus'],
                'set_name' => $sets,
            ];
        });
    }
}
