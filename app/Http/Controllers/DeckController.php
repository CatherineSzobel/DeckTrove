<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

class DeckController extends Controller
{
    public function builder(Request $request)
    {

        if ($request->routeIs('yugioh.deck.builder')) {
            return $this->generateYugiohCards($request);
        } elseif ($request->routeIs('magic.deck.builder')) {
            return $this->generateMagicCards($request);
        }

        abort(404);
    }

    public function generateYugiohCards(Request $request)
    {
        // JSON file path
        $jsonPath = public_path('json/yugioh-cards.json');

        if (!file_exists($jsonPath)) {
            abort(404, 'Cards file not found');
        }
        // Read and decode JSON
        $json = file_get_contents($jsonPath);
        $cards = json_decode($json, true)['data'] ?? [];

        // Pagination settings
        $page = $request->get('page', 1);
        $perPage = 20; // or $this->getItemsPerPage($request) if dynamic

        // Paginate the cards array
        $paginated = new LengthAwarePaginator(
            array_slice($cards, ($page - 1) * $perPage, $perPage),
            count($cards),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('decks.deck-builder', [
            'cards' => $paginated,
            'game' => 'yugioh'
        ]);
    }
    public function generateMagicCards(Request $request)
    {
        $page = $request->get('page', 1);
        $search = $request->get('search', null);
        $perPage = 20; // Items per page
        $view = $request->get('view', 'default'); // optional, for cache key

        $apiPath = 'https://api.scryfall.com/cards/search';

        // Build query parameters
        $queryParams = [
            'order' => 'name',
            'page' => $page,
            'q' => $search ? ($search . ' game:paper') : 'game:paper',
        ];

        // Cache key
        $cacheKey = "magic-cards-page-{$page}-view-{$view}" . ($search ? "-search-{$search}" : '');

        // Fetch cards from API with caching
        $apiResponse = cache()->remember($cacheKey, 600, function () use ($apiPath, $queryParams) {
            $response = Http::get($apiPath, $queryParams);

            if ($response->failed()) {
                abort(500, 'Failed to fetch cards from API');
            }

            return $response->json();
        });

        $cards = $apiResponse['data'] ?? [];
        $total = $apiResponse['total_cards'] ?? count($cards);

        // Slice cards for pagination (in case Scryfall returns more than perPage)
        $paginatedCards = array_slice($cards, 0, $perPage);

        // Create paginator
        $paginated = new LengthAwarePaginator(
            $paginatedCards,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('decks.deck-builder', [
            'cards' => $paginated,
            'game' => 'magic',
        ]);
    }
}
