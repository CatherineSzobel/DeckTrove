<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use App\Models\Deck;
use App\Models\Card;
use App\Models\DeckCard;
use Illuminate\Support\Facades\Auth;

class DeckController extends Controller
{
    public function index()
    {
        // Load all public decks initially
        $decks = Deck::with('user')->where('is_public', true)->get();
        return view('decks.public-deck', compact('decks'));
    }

    // AJAX filter method
    public function filter(Request $request)
    {
        $game = $request->query('game', 'all');

        $query = Deck::with('user')->where('is_public', true);

        if ($game !== 'all') {
            $query->where('game', $game);
        }

        $decks = $query->get();

        // Return HTML for the deck cards
        $html = view('decks.partials.deck-cards', compact('decks'))->render();

        return response()->json(['html' => $html]);
    }

    public function builder(Request $request)
    {
        if ($request->routeIs('yugioh.deck.builder')) {
            return $this->generateYugiohCards($request);
        }

        if ($request->routeIs('magic.deck.builder')) {
            return $this->generateMagicCards($request);
        }

        abort(404);
    }

    /**
     * Generate Yugioh cards with filters, search, and pagination
     */
    public function generateYugiohCards(Request $request)
    {
        $jsonPath = public_path('json/yugioh-cards.json');
        if (!file_exists($jsonPath)) abort(404, 'Cards file not found');

        $json = file_get_contents($jsonPath);
        $cards = json_decode($json, true)['data'] ?? [];

        // Apply filters and search
        $filteredCards = $this->applyYugiohFilters($cards, $request);

        // Pagination
        $page = $request->get('page', 1);
        $perPage = 20;
        $collection = collect($filteredCards);
        $paginated = new LengthAwarePaginator(
            $collection->forPage($page, $perPage)->values()->all(),
            $collection->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Generate filter options from ALL cards
        $filterOptions = $this->generateYugiohFilterOptions($cards);

        if ($request->ajax()) {
            return view('decks.yugioh.cards-view', ['cards' => $paginated])->render();
        }

        return view('decks.deck-builder', [
            'cards' => $paginated,
            'game' => 'yugioh',
            'filterOptions' => $filterOptions,
        ]);
    }

    /**
     * Apply filters and search for Yugioh cards
     */
    private function applyYugiohFilters(array $cards, Request $request): array
    {
        $filtered = $cards;

        // General search across multiple fields
        if ($request->filled('search')) {
            $term = strtolower($request->search);
            $filtered = array_filter(
                $filtered,
                fn($card) =>
                str_contains(strtolower($card['name'] ?? ''), $term) ||
                    str_contains(strtolower($card['type'] ?? ''), $term) ||
                    str_contains(strtolower($card['race'] ?? ''), $term) ||
                    str_contains(strtolower($card['archetype'] ?? ''), $term) ||
                    str_contains(strtolower($card['desc'] ?? ''), $term)
            );
        }

        // Individual filters
        foreach (['type', 'attribute', 'race', 'archetype'] as $field) {
            if ($request->filled($field)) {
                $filtered = array_filter($filtered, fn($card) => ($card[$field] ?? '') === $request->$field);
            }
        }

        return array_values($filtered);
    }

    /**
     * Generate filter options for Yugioh
     */
    private function generateYugiohFilterOptions(array $cards): array
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
     * Generate Magic cards with search, pagination, and optional filters
     */
    public function generateMagicCards(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = 20;
        $search = $request->get('search', null);
        $view = $request->get('view', 'default');

        $apiPath = 'https://api.scryfall.com/cards/search';
        $queryParams = [
            'order' => 'name',
            'page' => $page,
            'q' => $search ? ($search . ' game:paper') : 'game:paper',
        ];

        // Cache the API response for 10 minutes
        $cacheKey = "magic-cards-page-{$page}-view-{$view}" . ($search ? "-search-{$search}" : '');
        $apiResponse = cache()->remember($cacheKey, 600, function () use ($apiPath, $queryParams) {
            $response = Http::get($apiPath, $queryParams);
            if ($response->failed()) abort(500, 'Failed to fetch cards from API');
            return $response->json();
        });

        $cards = $apiResponse['data'] ?? [];
        $total = $apiResponse['total_cards'] ?? count($cards);

        $paginatedCards = array_slice($cards, 0, $perPage);
        $paginated = new LengthAwarePaginator(
            $paginatedCards,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('decks.deck-builder', [
            'cards' => $paginated,
            'game' => 'magic',
        ]);
    }
    public function save(Request $request)
    {
        $request->validate([
            'cards' => 'required|string',
            'deck_title' => 'nullable|string|max:255',
            'deck_description' => 'nullable|string',
            'game' => 'required|string',
        ]);
        $cardsData = json_decode($request->cards, true);

        if (!$cardsData || !is_array($cardsData)) {
            return back()->withErrors(['cards' => 'Invalid deck data']);
        }
        if (Auth::guest()) {
            return back()->withErrors(['cards' => 'You must be logged in to save a deck.']);
        }

        // Create the deck entry
        $deck = Deck::create([
            'user_id' => Auth::id(),
            'game' => $request->game,
            'name' => $request->deck_title ?? 'New Deck',
            'description' => $request->deck_description,
            'image' => $request->image
        ]);

        // Loop through each zone (main, extra, side)
        foreach ($cardsData as $zone => $cards) {

            // count duplicates BY external_id
            $grouped = collect($cards)->groupBy('id');

            foreach ($grouped as $externalId => $cardGroup) {

                $first = $cardGroup->first(); // contains full card data

                // Save or retrieve the card in DB
                $card = Card::firstOrCreate(
                    [
                        'external_id' => $externalId,
                        'game' => $request->game,
                    ],
                    [
                        'name' => $first['name'] ?? 'Unknown Card',
                        'image_url' => $first['image_uris']['normal'] ?? null,
                    ]
                );

                // Save pivot (deck → card)
                DeckCard::create([
                    'deck_id' => $deck->id,
                    'card_id' => $card->id,
                    'zone' => $zone,
                    'count' => $cardGroup->count(),
                ]);
            }
        }

        return redirect()->route('decks')
            ->with('success', 'Deck saved successfully!');
    }
    public function show($id)
    {
        $deck = Deck::with('cards')->findOrFail($id);
        //get game name from deck
        $game = $deck->game;
        switch ($game) {
            case 'yugioh':
                // Load local JSON once
                $jsonPath = public_path('json/yugioh-cards.json');
                if (!file_exists($jsonPath)) abort(404, 'Cards file not found');
                $json = file_get_contents($jsonPath);
                $allCards = collect(json_decode($json, true)['data'] ?? []);

                // Expand deck cards according to pivot count using JSON data
                $expandedCards = $deck->cards->flatMap(function ($card) use ($allCards) {
                    $deckCard = $card->pivot ?? null;
                    $count = $deckCard->count ?? 1;

                    // Find card in JSON by external_id
                    $cardData = $allCards->firstWhere('id', $card->external_id);

                    return $cardData ? array_fill(0, $count, $cardData) : [];
                });
                break;
            case 'magic':
                $expandedCards = collect();

                $cardsById = cache()->remember(
                    "magic-deck-{$deck->id}",
                    3600,
                    function () use ($deck) {

                        $identifiers = $deck->cards->map(fn($card) => [
                            'id' => $card->external_id,
                        ])->values()->all();

                        $response = Http::post(
                            'https://api.scryfall.com/cards/collection',
                            ['identifiers' => $identifiers]
                        );

                        if ($response->failed()) {
                            throw new \Exception('Failed to fetch Magic cards');
                        }

                        return collect($response->json('data'))->keyBy('id');
                    }
                );

                foreach ($deck->cards as $card) {
                    $count = $card->pivot->count ?? 1;
                    $cardData = $cardsById->get($card->external_id);

                    if ($cardData) {
                        $expandedCards = $expandedCards->merge(
                            array_fill(0, $count, $cardData)
                        );
                    }
                }

                break;

            default:
                abort(404, 'Game not found');
        }

        return view('decks.deck', [
            'deck' => $deck,
            'cards' => $expandedCards
        ]);
    }
    public function edit(Deck $deck)
    {
        $jsonPath = public_path('json/yugioh-cards.json');
        $json = file_get_contents($jsonPath);
        $allCards = collect(json_decode($json, true)['data'] ?? []);
        $expandedCards = $deck->cards->flatMap(function ($card) use ($allCards) {
            $deckCard = $card->pivot ?? null;
            $count = $deckCard->count ?? 1;

            // Find card in JSON by external_id
            $cardData = $allCards->firstWhere('id', $card->external_id);

            return $cardData ? array_fill(0, $count, $cardData) : [];
        });

        //Gate::authorize('update-job', $job);
        return view('decks.edit', ['deck' => $deck, 'cards' => $expandedCards]);
    }

    public function update(Deck $deck)
    {
        // validate
        request()->validate([
            'title' => ['nullable', 'min:3'],
            'description' => ['nullable', 'min:3'],
            'cards' => ['nullable', 'array'],
        ]);

        // authorize

        // update the cards
        $deck->update([
            'name' => request('title'),
            'description' => request('description'),
            'cards' => [
                'main' => request('cards.main') ?? [],
                'extra' => request('cards.extra') ?? [],
                'side' => request('cards.side') ?? [],
            ]
        ]);

        // redirect
        return redirect('/decks/' . $deck->id);
    }
    public function destroy(Deck $deck)
    {
        $deck->delete();

        return redirect('/decks');
    }
}
