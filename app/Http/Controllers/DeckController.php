<?php

namespace App\Http\Controllers;

use App\ViewModels\CardViewModel;
use Illuminate\Http\Request;
use App\Models\Deck;
use App\Services\DeckService;
use App\Services\DeckBuilderService;
use Illuminate\Support\Facades\Auth;

class DeckController extends Controller
{
    public function __construct(
        protected DeckService $deckService,
        protected DeckBuilderService $deckBuilder
    ) {}

    public function index()
    {
        $decks = Deck::with('user')
            ->where('is_public', true)
            ->get();

        return view('decks.public-deck', compact('decks'));
    }

    public function filter(Request $request)
    {
        $game = $request->query('game', 'all');

        $decks = Deck::with('user')
            ->where('is_public', true)
            ->when($game !== 'all', fn($q) => $q->where('game', $game))
            ->get();

        return response()->json([
            'html' => view('decks.partials.deck-cards', compact('decks'))->render()
        ]);
    }

    public function builder(Request $request)
    {
        $game = match (true) {
            $request->routeIs('magic.deck.builder') => 'magic',
            $request->routeIs('yugioh.deck.builder') => 'yugioh',
            default => abort(400, 'Unsupported game')
        };

        $params = match ($game) {
            'magic' => $request->only([
                'search',
                'type',
                'color',
                'rarity',
                'set_name',
                'page'
            ]),
            'yugioh' => $request->only([
                'search',
                'type',
                'attribute',
                'race',
                'archetype',
                'page'
            ]),
        };
        $rawCards = $this->deckBuilder->search($game, $params);
        $seriesConfig = config("series.$game");
        $cards = $rawCards->through(
            fn($card) => new CardViewModel($card, $seriesConfig)
        );
        $filters = $this->deckBuilder->filters($game);

        return view(
            'decks.deck-builder',
            array_merge(
                compact('cards', 'game', 'seriesConfig'),
                ['options' => array_values($filters)]
            )
        );
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'cards' => 'required|string',
            'deck_title' => 'nullable|string|max:255',
            'deck_description' => 'nullable|string',
            'game' => 'required|string',
            'image' => 'nullable|string',
            'is_public' => 'nullable|boolean',

        ]);
        $data['cards'] = json_decode($data['cards'], true);

        $this->deckService->save($data);

        return redirect()->route('decks')->with('success', 'Deck saved successfully!');
    }
    public function show(int $id)
    {
        $deck = Deck::with('cards')->findOrFail($id);
        $sections = $this->deckService->buildSections($deck);
        $seriesConfig = config("series.$deck->game");
        return view('decks.deck', compact('deck', 'sections', 'seriesConfig'));
    }

    public function edit(Deck $deck, Request $request)
    {
        $this->authorize('update', $deck);

        $game = $deck->game;
        $params = match ($game) {
            'magic' => $request->only([
                'search',
                'type',
                'color',
                'rarity',
                'set_name',
                'page'
            ]),
            'yugioh' => $request->only([
                'search',
                'type',
                'attribute',
                'race',
                'archetype',
                'page'
            ]),
        };

        $deckCards = $this->deckService->buildSections($deck);
        $filters = $this->deckBuilder->filters($game);
        $seriesConfig = config("series.$game");

        $rawCards = $this->deckBuilder->search($game, $params, $request->input('view', 'default'));
        $cards = $rawCards->through(
            fn($card) => new CardViewModel($card, $seriesConfig)
        );

        return view('decks.edit', compact('deck', 'game', 'deckCards', 'cards', 'filters'));
    }
    public function update(Deck $deck, Request $request)
    {
        $request->merge([
            'cards' => $request->input('cards') ? json_decode($request->input('cards'), true) : [],
        ]);

        $data = $request->validate([
            'deck_title' => ['nullable', 'min:3'],
            'deck_description' => ['nullable', 'min:3'],
            'cards' => ['nullable', 'array'],
            'is_public' => ['nullable', 'boolean'],
            'image' => ['nullable', 'string'],
        ]);

        $deck->update([
            'name' => $data['deck_title'] ?? $deck->name,
            'description' => $data['deck_description'] ?? $deck->description,
            'is_public' => $data['is_public'] ?? $deck->is_public,
            'image' => $data['image'] ?? $deck->image,
        ]);

        $this->deckService->updateCards($deck, $data['cards'] ?? []);
        return redirect()->route('decks.show', $deck);
    }
    public function destroy(Deck $deck)
    {
        $deck->delete();
        return redirect()->route('decks');
    }
}
