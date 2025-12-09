<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class MagicController extends Controller
{
    private string $apiPath = 'https://api.scryfall.com/cards/search';

    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $view = $request->get('view', 'full');
        $search = $request->get('search', '');

        // Set items per page based on view type
        $perPage = $this->getItemsPerPage($view);

        // Build query parameters for Scryfall API
        $queryParams = [
            'order' => 'name',
            'page' => $page,
        ];

        // Add search query if provided
        if ($search) {
            $queryParams['q'] = $search . ' game:paper';
        } else {
            $queryParams['q'] = 'game:paper';
        }

        // Create cache key based on all parameters
        $cacheKey = "magic-cards-page-{$page}-view-{$view}" . ($search ? "-search-{$search}" : '');

        // Fetch cards with caching
        $apiResponse = cache()->remember($cacheKey, 600, function () use ($queryParams) {
            $response = Http::get($this->apiPath, $queryParams);

            if ($response->failed()) {
                abort(500, 'Failed to fetch cards from API');
            }

            return $response->json();
        });

        $cards = $apiResponse['data'] ?? [];
        $total = $apiResponse['total_cards'] ?? 10000;

        // Scryfall returns 175 cards per page by default, so we need to slice for our pagination
        $paginatedCards = array_slice($cards, 0, $perPage);

        // Create paginator
        $paginated = new LengthAwarePaginator(
            $paginatedCards,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query()
            ]
        );

        return view('cards.cards', [
            'cards' => $paginated,
            'series' => 'magic' // Pass series for the template
        ]);
    }

    public function show($id)
    {
        $response = Http::get("https://api.scryfall.com/cards/{$id}");

        if ($response->failed()) {
            abort(500, 'Failed to fetch card details from API');
        }

        $card = $response->json() ?? null;

        if (!$card || isset($card['object']) && $card['object'] === 'error') {
            abort(404, 'Card not found');
        }

        // Fetch prints from Scryfall
        $prints = [];
        if (!empty($card['prints_search_uri'])) {
            $printsResponse = Http::get($card['prints_search_uri']);
            if ($printsResponse->ok()) {
                $prints = $printsResponse->json()['data'] ?? [];
            }
        }

        // Also fetch other cards from the same set (server-side thumbnails)
        $setCards = collect();
        $setCode = $card['set'] ?? null;
        if ($setCode) {
            $cacheKey = "scryfall_set_{$setCode}_cards";
            $setResults = cache()->remember($cacheKey, 300, function () use ($setCode) {
                $resp = Http::get($this->apiPath, ['q' => "set:{$setCode} game:paper", 'order' => 'name']);
                if ($resp->ok()) {
                    return $resp->json()['data'] ?? [];
                }
                return [];
            });

            $setCards = collect($setResults)
                ->reject(function ($c) use ($card) {
                    // exclude same oracle_id if present
                    return (isset($c['oracle_id']) && isset($card['oracle_id']) && $c['oracle_id'] === $card['oracle_id']);
                })
                ->shuffle()
                ->take(12);
        }

        return view('cards.card', [
            'card'   => $card,
            'prints' => $prints,
            'series' => 'magic',
            'setCards' => $setCards,
        ]);
    }

    /**
     * Get items per page based on view type
     */
    private function getItemsPerPage(string $view): int
    {
        return match ($view) {
            'list' => 50,    // More items in compact list view
            'image' => 48,  // Grid of images
            default => 24    // Detailed card view (full)
        };
    }
}
