<?php

namespace App\Services;

use App\Contracts\CardProvider;
use App\Exceptions\ScryfallUnavailableException;
use App\Support\Paginates;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Magic: The Gathering cards from the Scryfall API (https://scryfall.com/docs/api).
 */
class MagicService implements CardProvider
{
    use Paginates;

    /** Scryfall returns search results in fixed pages of this size. */
    private const SCRYFALL_PAGE_SIZE = 175;

    /** Maximum identifiers accepted by /cards/collection per request. */
    private const COLLECTION_CHUNK = 75;

    private const CACHE_TTL = 600;

    public function scryfall(): PendingRequest
    {
        return Http::baseUrl('https://api.scryfall.com')
            ->timeout(15)
            ->acceptJson()
            ->withUserAgent(config('services.scryfall.user_agent'));
    }

    public function search(array $params): LengthAwarePaginator
    {
        $perPage = $this->perPage($params['view'] ?? null);
        $page = $this->currentPage($params);
        $query = $this->buildQuery($params);

        // Our pages don't line up with Scryfall's, so a page may span two Scryfall pages.
        $offset = ($page - 1) * $perPage;
        $firstScryfallPage = intdiv($offset, self::SCRYFALL_PAGE_SIZE) + 1;
        $lastScryfallPage = intdiv($offset + $perPage - 1, self::SCRYFALL_PAGE_SIZE) + 1;

        $cards = [];
        $total = 0;

        for ($scryfallPage = $firstScryfallPage; $scryfallPage <= $lastScryfallPage; $scryfallPage++) {
            $result = $this->searchPage($query, $scryfallPage);
            $total = $result['total_cards'] ?? 0;
            array_push($cards, ...($result['data'] ?? []));

            if (! ($result['has_more'] ?? false)) {
                break;
            }
        }

        $start = $offset - ($firstScryfallPage - 1) * self::SCRYFALL_PAGE_SIZE;

        return $this->paginator(array_slice($cards, $start, $perPage), $total, $perPage, $page);
    }

    public function find(string $id): array
    {
        if (! Str::isUuid($id)) {
            abort(404, 'Card not found');
        }

        return Cache::remember("scryfall-card-$id", self::CACHE_TTL, function () use ($id) {
            $response = $this->get("/cards/$id");

            if ($response->notFound()) {
                abort(404, 'Card not found');
            }

            return $this->json($response);
        });
    }

    public function findMany(array $ids): Collection
    {
        $ids = array_values(array_unique(array_filter(array_map('strval', $ids), Str::isUuid(...))));

        if (! $ids) {
            return collect();
        }

        sort($ids);

        return Cache::remember('scryfall-collection-'.md5(implode(',', $ids)), self::CACHE_TTL, function () use ($ids) {
            return collect(array_chunk($ids, self::COLLECTION_CHUNK))
                ->flatMap(function (array $chunk) {
                    $response = $this->request(fn () => $this->scryfall()->post('/cards/collection', [
                        'identifiers' => array_map(fn ($id) => ['id' => $id], $chunk),
                    ]));

                    return $this->json($response)['data'] ?? [];
                })
                ->keyBy('id');
        });
    }

    public function related(array $card, int $limit = 6): Collection
    {
        $setCode = $card['set'] ?? null;

        if (! $setCode) {
            return collect();
        }

        $setCards = $this->searchPage("set:$setCode game:paper", 1)['data'] ?? [];

        return collect($setCards)
            ->reject(fn ($c) => ($c['oracle_id'] ?? null) === ($card['oracle_id'] ?? null))
            ->shuffle()
            ->take($limit)
            ->values();
    }

    /**
     * Every card matching a raw Scryfall query, following pagination up to $maxPages pages.
     */
    public function searchAll(string $query, int $maxPages = 5): array
    {
        $cards = [];

        for ($page = 1; $page <= $maxPages; $page++) {
            $result = $this->searchPage($query, $page);
            array_push($cards, ...($result['data'] ?? []));

            if (! ($result['has_more'] ?? false)) {
                break;
            }
        }

        return $cards;
    }

    public function random(int $count): Collection
    {
        $responses = Http::pool(fn ($pool) => array_map(
            fn ($i) => $pool->as("card$i")
                ->baseUrl('https://api.scryfall.com')
                ->timeout(5)
                ->acceptJson()
                ->withUserAgent(config('services.scryfall.user_agent'))
                ->get('/cards/random'),
            range(1, $count)
        ));

        return collect($responses)
            ->filter(fn ($response) => $response instanceof Response && $response->successful())
            ->map(fn (Response $response) => $response->json())
            ->values();
    }

    public function filterOptions(): array
    {
        return Cache::remember('magic-filter-options', 3600, function () {
            $types = $this->json($this->get('/catalog/card-types'))['data'] ?? [];
            sort($types);

            $sets = collect($this->json($this->get('/sets'))['data'] ?? [])
                ->pluck('name', 'code')
                ->sortKeys()
                ->all();

            return [
                'type' => $types,
                'color' => ['W', 'U', 'B', 'R', 'G', 'C'],
                'rarity' => ['Common', 'Uncommon', 'Rare', 'Mythic', 'Special', 'Bonus'],
                'set_name' => $sets,
            ];
        });
    }

    /**
     * One page of raw Scryfall search results. A search without matches returns an empty page.
     */
    private function searchPage(string $query, int $page): array
    {
        return Cache::remember('scryfall-search-'.md5("$query|$page"), self::CACHE_TTL, function () use ($query, $page) {
            $response = $this->get('/cards/search', ['q' => $query, 'order' => 'name', 'page' => $page]);

            // Scryfall answers "no cards matched" with a 404.
            if ($response->notFound()) {
                return ['data' => [], 'total_cards' => 0, 'has_more' => false];
            }

            return $this->json($response);
        });
    }

    private function buildQuery(array $params): string
    {
        $search = trim($params['search'] ?? '');
        $search = mb_strlen($search) >= 3
            ? Str::of($search)->limit(100, '')->replaceMatches('/[^\p{L}\p{N}\s\-\',:.!]/u', '')->trim()->toString()
            : '';

        $colors = $params['color'] ?? null;
        $colors = is_array($colors) ? implode('', $colors) : $colors;

        return collect([
            'game:paper',
            $search,
            $this->term('type', $params['type'] ?? null),
            $this->term('color', $colors),
            $this->term('rarity', $params['rarity'] ?? null),
            $this->term('set', $params['set_name'] ?? null),
        ])->filter()->implode(' ');
    }

    /**
     * A quoted Scryfall search term, so user input cannot inject extra query syntax.
     */
    private function term(string $keyword, ?string $value): ?string
    {
        $value = trim(str_replace('"', '', (string) $value));

        return $value === '' ? null : sprintf('%s:"%s"', $keyword, $value);
    }

    private function get(string $path, array $query = []): Response
    {
        return $this->request(fn () => $this->scryfall()->get($path, $query));
    }

    private function request(callable $send): Response
    {
        $start = microtime(true);

        try {
            $response = $send();
        } catch (ConnectionException $e) {
            Log::warning('Scryfall API unreachable', ['message' => $e->getMessage()]);

            throw new ScryfallUnavailableException;
        }

        Log::debug('Scryfall API call', [
            'url' => (string) $response->effectiveUri(),
            'status' => $response->status(),
            'duration_ms' => round((microtime(true) - $start) * 1000),
        ]);

        return $response;
    }

    private function json(Response $response): array
    {
        if ($response->serverError() || $response->status() === 429) {
            Log::warning('Scryfall API error', ['status' => $response->status()]);

            throw new ScryfallUnavailableException;
        }

        if ($response->failed()) {
            Log::warning('Scryfall API rejected request', ['status' => $response->status(), 'body' => $response->json('details')]);

            return [];
        }

        return $response->json() ?? [];
    }
}
