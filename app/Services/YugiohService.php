<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class YugiohService
{
    private string $jsonPath;

    public function __construct()
    {
        $this->jsonPath = public_path('/json/yugioh-cards.json');
    }

    /**
     * Load all cards from JSON
     */
    public function loadCards(): array
    {
        if (!file_exists($this->jsonPath)) {
            abort(404, 'Cards file not found');
        }

        $json = file_get_contents($this->jsonPath);
        return json_decode($json, true)['data'] ?? [];
    }

    /**
     * Apply filters based on request parameters
     */
    public function applyFilters(array $cards, Request $request): array
    {
        $filtered = $cards;

        // Search filter
        if ($request->filled('search')) {
            $term = strtolower($request->search);
            $filtered = array_filter($filtered, function ($card) use ($term) {
                return str_contains(strtolower($card['name'] ?? ''), $term)
                    || str_contains(strtolower($card['type'] ?? ''), $term)
                    || str_contains(strtolower($card['race'] ?? ''), $term)
                    || str_contains(strtolower($card['archetype'] ?? ''), $term)
                    || str_contains(strtolower($card['desc'] ?? ''), $term);
            });
        }

        // Type, Attribute, Race, Archetype filters
        foreach (['type', 'attribute', 'race', 'archetype'] as $key) {
            if ($request->filled($key)) {
                $filtered = array_filter($filtered, fn($card) => ($card[$key] ?? '') === $request->$key);
            }
        }

        return array_values($filtered);
    }

    /**
     * Generate filter options from all cards
     */
    public function generateFilterOptions(array $cards): array
    {
        $collection = collect($cards);

        return [
            'type' => $collection->pluck('type')->filter()->unique()->sort()->values()->all(),
            'attribute' => $collection->pluck('attribute')->filter()->unique()->sort()->values()->all(),
            'race' => $collection->pluck('race')->filter()->unique()->sort()->values()->all(),
            'archetype' => $collection->pluck('archetype')->filter()->unique()->sort()->values()->all(),
        ];
    }

    /**
     * Paginate an array of cards
     */
    public function paginate(array $cards, Request $request, string $view = 'full'): LengthAwarePaginator
    {
        $page = $request->get('page', 1);
        $perPage = $this->itemsPerPage($view);

        $collection = collect($cards);

        return new LengthAwarePaginator(
            $collection->forPage($page, $perPage)->values()->all(),
            $collection->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /**
     * Get items per page based on view type
     */
    public function itemsPerPage(string $view): int
    {
        return match ($view) {
            'list' => 50,
            'images' => 48,
            default => 24
        };
    }

    /**
     * Find a single card by ID
     */
    public function findCard(int $id): array
    {
        $cards = $this->loadCards();
        $card = collect($cards)->firstWhere('id', $id);

        if (!$card) {
            abort(404, 'Card not found');
        }

        return $card;
    }

    /**
     * Find related cards by archetype
     */
    public function findRelatedCards(array $card, int $limit = 6): Collection
    {
        $cards = $this->loadCards();
        $archetype = $card['archetype'] ?? null;

        if (!$archetype) return collect();

        return collect($cards)
            ->filter(fn($c) => ($c['archetype'] ?? null) === $archetype)
            ->shuffle()
            ->take($limit);
    }
}
