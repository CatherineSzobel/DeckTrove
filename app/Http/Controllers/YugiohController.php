<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class YugiohController extends Controller
{
    public $jsonPath;

    public function __construct()
    {
        $this->jsonPath = public_path('/json/yugioh-cards.json');
    }

    public function index(Request $request)
    {
        $series = 'yugioh';
        $view = $request->get('view', 'full');

        if (!file_exists($this->jsonPath)) {
            abort(404, 'Cards file not found');
        }

        $json = file_get_contents($this->jsonPath);
        $cards = json_decode($json, true)['data'] ?? [];

        // Apply filters if they exist in the request
        $filteredCards = $this->applyFilters($cards, $request);

        $page = $request->get('page', 1);
        $perPage = $this->getItemsPerPage($view);
        $collection = collect($filteredCards);

        $paginated = new LengthAwarePaginator(
            $collection->forPage($page, $perPage)->values()->all(),
            $collection->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Generate filter options from ALL cards (not filtered)
        $filterOptions = $this->generateFilterOptions($cards);

        return view('cards.cards', [
            'cards' => $paginated,
            'series' => $series,
            'filterOptions' => $filterOptions
        ]);
    }

    /**
     * Apply filters to the card collection
     */
    private function applyFilters(array $cards, Request $request): array
    {
        $filteredCards = $cards;

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = strtolower($request->search);
            $filteredCards = array_filter($filteredCards, function ($card) use ($searchTerm) {
                return str_contains(strtolower($card['name'] ?? ''), $searchTerm) ||
                    str_contains(strtolower($card['type'] ?? ''), $searchTerm) ||
                    str_contains(strtolower($card['race'] ?? ''), $searchTerm) ||
                    str_contains(strtolower($card['archetype'] ?? ''), $searchTerm) ||
                    str_contains(strtolower($card['desc'] ?? ''), $searchTerm);
            });
        }

        // Type filter
        if ($request->has('type') && !empty($request->type)) {
            $filteredCards = array_filter($filteredCards, function ($card) use ($request) {
                return ($card['type'] ?? '') === $request->type;
            });
        }

        // Attribute filter
        if ($request->has('attribute') && !empty($request->attribute)) {
            $filteredCards = array_filter($filteredCards, function ($card) use ($request) {
                return ($card['attribute'] ?? '') === $request->attribute;
            });
        }

        // Race filter
        if ($request->has('race') && !empty($request->race)) {
            $filteredCards = array_filter($filteredCards, function ($card) use ($request) {
                return ($card['race'] ?? '') === $request->race;
            });
        }

        // Archetype filter
        if ($request->has('archetype') && !empty($request->archetype)) {
            $filteredCards = array_filter($filteredCards, function ($card) use ($request) {
                return ($card['archetype'] ?? '') === $request->archetype;
            });
        }

        return array_values($filteredCards);
    }

    /**
     * Generate filter options from card data
     */
    private function generateFilterOptions($cards): array
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
     * Get items per page based on view type
     */
    private function getItemsPerPage(string $view): int
    {
        return match ($view) {
            'list' => 50,
            'images' => 48,
            default => 24
        };
    }

    public function show($id)
    {
        if (!file_exists($this->jsonPath)) {
            abort(404, 'Card data file not found.');
        }

        $json = json_decode(file_get_contents($this->jsonPath), true);

        if (!isset($json['data'])) {
            abort(500, 'Invalid JSON format.');
        }

        $card = collect($json['data'])->firstWhere('id', (int) $id);
        $card = json_decode(json_encode($card));

        if (!$card) {
            abort(404, 'Card not found.');
        }

        return view(
            'cards.card',
            [
                'card' => $card,
                'series' => 'yugioh'
            ]
        );
    }
}
