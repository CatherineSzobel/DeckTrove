<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class YugiohService
{
    private string $jsonPath;

    public function __construct()
    {
        $this->jsonPath = public_path('/json/yugioh-cards.json');
    }

    public function loadCards(): array
    {
        if (!file_exists($this->jsonPath)) {
            abort(404, 'Cards file not found');
        }

        $json = file_get_contents($this->jsonPath);
        return json_decode($json, true)['data'] ?? [];
    }

    public function applyFilters(array $cards, array $params): array
    {
        $filtered = $cards;

        if (!empty($params['search'])) {
            $term = strtolower($params['search']);
            $filtered = array_filter(
                $filtered,
                fn($card) =>
                str_contains(strtolower($card['name'] ?? ''), $term)
                    || str_contains(strtolower($card['type'] ?? ''), $term)
                    || str_contains(strtolower($card['race'] ?? ''), $term)
                    || str_contains(strtolower($card['archetype'] ?? ''), $term)
                    || str_contains(strtolower($card['desc'] ?? ''), $term)
            );
        }

        foreach (['type', 'attribute', 'race', 'archetype'] as $key) {
            if (!empty($params[$key])) {
                $filtered = array_filter($filtered, fn($card) => ($card[$key] ?? '') === $params[$key]);
            }
        }

        return array_values($filtered);
    }

    public function paginate(array $cards,  int $page = 1,  string $view = 'full'): LengthAwarePaginator
    {
        $perPage = $this->itemsPerPage($view);

        $collection = collect($cards);

        return new LengthAwarePaginator(
            $collection->forPage($page, $perPage)->values()->all(),
            $collection->count(),
            $perPage,
            $page
        );
    }

    public function fetchCardsByPagination(array $params): LengthAwarePaginator
    {
        $allCards = $this->loadCards();
        $filtered = $this->applyFilters($allCards, $params);
        $page = (int)($params['page'] ?? 1);
        $view = $params['view'] ?? 'full';
        return $this->paginate($filtered, $page, $view);
    }

    public function getFilterOptions(): array
    {
        $cards = $this->loadCards();
        $collection = collect($cards);

        return [
            'type' => $collection->pluck('type')->filter()->unique()->sort()->values()->all(),
            'attribute' => $collection->pluck('attribute')->filter()->unique()->sort()->values()->all(),
            'race' => $collection->pluck('race')->filter()->unique()->sort()->values()->all(),
            'archetype' => $collection->pluck('archetype')->filter()->unique()->sort()->values()->all(),
        ];
    }

    public function itemsPerPage(string $view): int
    {
        return match ($view) {
            'list' => 50,
            'images' => 48,
            default => 24
        };
    }

    public function fetchCardById(int $id): array
    {
        $cards = $this->loadCards();
        $card = collect($cards)->firstWhere('id', $id);

        if (!$card) {
            abort(404, 'Card not found');
        }

        return $card;
    }

    public function fetchRelatedSetCards(array $card, int $limit = 6): Collection
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
