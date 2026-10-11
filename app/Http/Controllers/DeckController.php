<?php

namespace App\Http\Controllers;

use App\Decks\DeckStats;
use App\Http\Requests\SaveDeckRequest;
use App\Models\Deck;
use App\Services\CardService;
use App\Services\DeckService;
use App\ViewModels\CardViewModel;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class DeckController extends Controller
{
    public function __construct(
        protected DeckService $deckService,
        protected CardService $cardService,
    ) {}

    /**
     * Public decks, optionally filtered by game. AJAX requests get just the deck grid.
     */
    public function index(Request $request)
    {
        $game = $request->query('game');

        $decks = Deck::public()
            ->with('user')
            ->withCardCount()
            ->when(array_key_exists((string) $game, config('series')), fn ($query) => $query->where('game', $game))
            ->latest()
            ->paginate(18)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json(['html' => view('decks.partials.deck-card', compact('decks'))->render()]);
        }

        return view('decks.public-deck', compact('decks'));
    }

    public function mine(Request $request)
    {
        $decks = $request->user()->decks()->withCardCount()->latest()->get();

        return view('decks.mydecks', compact('decks'));
    }

    public function show(Deck $deck, DeckStats $deckStats)
    {
        Gate::authorize('view', $deck);

        $deck->load('user');
        $sections = $this->deckService->buildSections($deck);
        $zones = config("series.{$deck->game}.deck.zones");
        $stats = $deckStats->fromSections($sections, config("series.{$deck->game}"));
        $formatErrors = $this->deckService->formatErrors($deck);

        return view('decks.deck', compact('deck', 'sections', 'zones', 'stats', 'formatErrors'));
    }

    /**
     * The deck builder. AJAX requests (infinite scroll / search) get only the card results.
     */
    public function builder(Request $request, string $series)
    {
        $cards = $this->searchCards($request, $series);

        if ($request->ajax()) {
            return response()
                ->view('decks.partials.cards-view', compact('cards'))
                ->header('X-Next-Page', $cards->hasMorePages() ? $cards->currentPage() + 1 : '')
                ->header('X-Total', $cards->total());
        }

        return view('decks.deck-builder', [
            'deck' => null,
            'game' => $series,
            'cards' => $cards,
            'state' => $this->builderState($series, null, [], $cards),
            'options' => $this->cardService->for($series)->filterOptions(),
        ]);
    }

    public function store(SaveDeckRequest $request, string $series)
    {
        $deck = $request->user()->decks()->make(['game' => $series]);

        $this->deckService->save($deck, $request->validated(), $request->cardCounts());

        return redirect()->route('decks')->with('success', $this->savedMessage($deck, $request));
    }

    public function edit(Request $request, Deck $deck)
    {
        $cards = $this->searchCards($request, $deck->game);

        return view('decks.deck-builder', [
            'deck' => $deck,
            'game' => $deck->game,
            'cards' => $cards,
            'state' => $this->builderState($deck->game, $deck, $this->deckService->buildSections($deck), $cards),
            'options' => $this->cardService->for($deck->game)->filterOptions(),
        ]);
    }

    /**
     * The deck builder's starting state (read by resources/js/deck-builder.js). After a failed save the submitted
     * values win over the deck's, as with any form.
     *
     * @param  array<string, Collection<int, CardViewModel>>  $sections  the deck's cards per zone, one per copy
     */
    private function builderState(string $series, ?Deck $deck, array $sections, LengthAwarePaginator $cards): array
    {
        $rules = config("series.$series.deck");

        return [
            'rules' => $rules,
            'zones' => collect($rules['zones'])
                ->map(fn ($zone, string $key) => collect($sections[$key] ?? [])->map(fn (CardViewModel $card) => $card->builderCard())->values()->all())
                ->all(),
            'title' => (string) old('deck_title', $deck?->name ?? ''),
            'description' => (string) old('deck_description', $deck?->description ?? ''),
            'format' => (string) old('format', $deck?->format ?? ''),
            'cover' => (string) old('image', $deck?->image ?? ''),
            'isPublic' => (bool) old('is_public', $deck?->is_public ?? false),
            'editing' => $deck !== null,
            // Reopen the import panel after a failed import.
            'showImport' => session('errors')?->hasAny(['list', 'file']) ?? false,
            'filters' => array_keys(config("series.$series.filters")),
            'searchUrl' => route('decks.builder', $series),
            'nextPage' => $cards->hasMorePages() ? $cards->currentPage() + 1 : null,
            'total' => $cards->total(),
            'currency' => config('app.currency'),
        ];
    }

    public function update(SaveDeckRequest $request, Deck $deck)
    {
        $this->deckService->save($deck, $request->validated(), $request->cardCounts());

        return redirect()->route('decks.show', $deck)->with('success', $this->savedMessage($deck, $request));
    }

    public function destroy(Deck $deck)
    {
        $deck->delete();

        return redirect()->route('decks')->with('success', 'Deck deleted.');
    }

    private function searchCards(Request $request, string $series)
    {
        $config = config("series.$series");
        $params = $request->only([...array_keys($config['filters']), 'search', 'page']);

        return $this->cardService->for($series)
            ->search($params)
            ->through(fn ($card) => new CardViewModel($card, $config));
    }

    private function savedMessage(Deck $deck, SaveDeckRequest $request): string
    {
        return $request->boolean('is_public') && ! $deck->is_public
            ? 'Deck saved as private: it needs at least '.config("series.{$deck->game}.deck.zones.main.min").' main deck cards to be public.'
            : 'Deck saved successfully!';
    }
}
