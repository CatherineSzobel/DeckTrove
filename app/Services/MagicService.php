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
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Sleep;
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

    /** Site-wide limiter keys and Scryfall's rate limits (https://scryfall.com/docs/api, "Rate Limits"). */
    public const SEARCH_LIMITER = 'scryfall-search';

    public const LIMITER = 'scryfall';

    public const SEARCH_PER_SECOND = 2;

    public const PER_SECOND = 10;

    /** How long one call may wait for a free slot before giving up with a 503. */
    private const MAX_WAIT_SECONDS = 1;

    /** Endpoints under Scryfall's stricter limit. */
    private const SEARCH_PATHS = ['/cards/search', '/cards/named', '/cards/random', '/cards/collection'];

    public function scryfall(): PendingRequest
    {
        return Http::baseUrl(config('services.scryfall.url'))
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
                    $response = $this->request('/cards/collection', fn () => $this->scryfall()->post('/cards/collection', [
                        'identifiers' => array_map(fn ($id) => ['id' => $id], $chunk),
                    ]));

                    return $this->json($response)['data'] ?? [];
                })
                ->keyBy('id');
        });
    }

    /**
     * Looks up cards from a deck list, by name or by set code and collector number.
     *
     * @param  list<array{name?: string, set?: string, collector_number?: string}>  $identifiers
     * @return list<array|null> the matching card for each identifier (same order), or null if not found
     */
    public function findByIdentifiers(array $identifiers): array
    {
        if (! $identifiers) {
            return [];
        }

        $found = collect(array_chunk($identifiers, self::COLLECTION_CHUNK))
            ->flatMap(function (array $chunk) {
                $response = $this->request('/cards/collection', fn () => $this->scryfall()->post('/cards/collection', ['identifiers' => $chunk]));

                return $this->json($response)['data'] ?? [];
            });

        return array_map(
            fn (array $identifier) => $found->first(fn (array $card) => $this->matchesIdentifier($card, $identifier)),
            $identifiers,
        );
    }

    private function matchesIdentifier(array $card, array $identifier): bool
    {
        if (isset($identifier['set'], $identifier['collector_number'])) {
            return strcasecmp($card['set'] ?? '', $identifier['set']) === 0
                && strcasecmp($card['collector_number'] ?? '', $identifier['collector_number']) === 0;
        }

        // Deck lists name double-faced cards by their front face ("Delver of Secrets").
        $names = [$card['name'] ?? '', ...explode(' // ', $card['name'] ?? '')];

        return collect($names)->contains(fn ($name) => strcasecmp($name, $identifier['name'] ?? '') === 0);
    }

    public function printings(array $card): array
    {
        $oracleId = $card['oracle_id'] ?? data_get($card, 'card_faces.0.oracle_id');
        // 10 pages of 175 cover the most-printed cards (basic lands); each page is cached.
        $prints = $oracleId ? $this->searchAll("oracleid:$oracleId unique:prints", 10) : [$card];

        return collect($prints)
            ->map(fn ($print) => [
                'key' => (string) $print['id'],
                'label' => ($print['set_name'] ?? 'Unknown').' ('.Str::upper($print['set'] ?? '').' #'.($print['collector_number'] ?? '?').')',
                'set_name' => $print['set_name'] ?? 'Unknown',
                'code' => Str::upper($print['set'] ?? '').' #'.($print['collector_number'] ?? '?'),
                'pack_code' => (string) ($print['set'] ?? ''),
            ])
            ->values()
            ->all();
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
        // One call at a time through our limiter (Scryfall allows 2/s here); stops early rather than wait long.
        $cards = collect();

        try {
            for ($i = 0; $i < $count; $i++) {
                $card = $this->json($this->get('/cards/random'));

                if (! isset($card['id'])) {
                    break;
                }

                $cards->push($card);
            }
        } catch (ScryfallUnavailableException) {
            // Show the cards fetched so far.
        }

        return $cards;
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
        return $this->request($path, fn () => $this->scryfall()->get($path, $query));
    }

    private function request(string $path, callable $send): Response
    {
        $this->throttle($path);

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

    /**
     * Keeps the whole site under Scryfall's rate limits: waits for a free slot (at most MAX_WAIT_SECONDS), then
     * counts the call. Still full after waiting → ScryfallUnavailableException, without calling Scryfall.
     */
    private function throttle(string $path): void
    {
        [$key, $perSecond] = in_array($path, self::SEARCH_PATHS, true)
            ? [self::SEARCH_LIMITER, self::SEARCH_PER_SECOND]
            : [self::LIMITER, self::PER_SECOND];

        if (RateLimiter::tooManyAttempts($key, $perSecond)) {
            Sleep::for(min(RateLimiter::availableIn($key), self::MAX_WAIT_SECONDS))->seconds();
        }

        if (RateLimiter::tooManyAttempts($key, $perSecond)) {
            Log::warning('Scryfall call skipped: our own rate limit is full', ['path' => $path]);

            throw new ScryfallUnavailableException;
        }

        RateLimiter::hit($key, 1);
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
