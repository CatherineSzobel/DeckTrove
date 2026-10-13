<?php

namespace App\Http\Controllers;

use App\Cards\FilterChoices;
use App\Decks\DeckStats;
use App\Decks\PublicDeckQuery;
use App\Decks\StatsCharts;
use App\Http\Requests\SaveDeckRequest;
use App\Models\Deck;
use App\Services\CardService;
use App\Services\DeckService;
use App\ViewModels\CardViewModel;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class DeckController extends Controller
{
    public function __construct(
        protected DeckService $deckService,
        protected CardService $cardService,
    ) {}

    /**
     * Public decks: search, game and format filters, your liked decks, and three sort orders (see PublicDeckQuery).
     * AJAX requests get just the deck grid.
     */
    public function index(Request $request)
    {
        $query = PublicDeckQuery::fromRequest($request);
        $decks = $query->paginate(18);

        if ($request->ajax()) {
            return response()->json(['html' => view('decks.partials.deck-card', compact('decks'))->render()]);
        }

        return view('decks.public-deck', [
            'decks' => $decks,
            'query' => $query,
            'filters' => $query->filters(),
            'sorts' => PublicDeckQuery::SORTS,
            'formats' => $query->formatOptions(),
        ]);
    }

    /**
     * The signed-in user's decks, all on one page: the stat chips, and per card what the in-place search looks at.
     */
    public function mine(Request $request)
    {
        $decks = $request->user()->decks()->withCardCount()->withCount('likes')->latest()->get();
        $public = $decks->where('is_public', true);

        return view('decks.mydecks', [
            'decks' => $decks,
            'stats' => [
                'decks' => $decks->count(),
                'series' => collect(config('series'))->map(fn ($config, $series) => $decks->where('game', $series)->count())->all(),
                'public' => $public->count(),
                'likes_received' => (int) $public->sum('likes_count'),
            ],
            // In the grid's order, for deckSearch (resources/js/deck-search.js).
            'searchData' => $decks->map(fn (Deck $deck) => [
                'game' => $deck->game,
                'text' => Str::lower(trim($deck->name.' '.$deck->description)),
            ])->values()->all(),
            'builderSeries' => session('series', array_key_first(config('series'))),
        ]);
    }

    public function show(Request $request, Deck $deck, DeckStats $deckStats)
    {
        Gate::authorize('view', $deck);

        $deck->load('user')->loadCount('likes');
        $sections = $this->deckService->buildSections($deck);
        $zones = config("series.{$deck->game}.deck.zones");
        $stats = $deckStats->fromSections($sections, config("series.{$deck->game}"));
        $formatErrors = $this->deckService->formatErrors($deck);

        return view('decks.deck', [
            'deck' => $deck,
            'sections' => $sections,
            'zones' => $zones,
            'stats' => $stats,
            'charts' => StatsCharts::for($stats, $deck->game),
            'formatErrors' => $formatErrors,
            // The message for each card that breaks the format, by external id, for its red outline.
            'cardProblems' => array_combine($formatErrors['card_ids'], $formatErrors['messages']),
            'isOwner' => Gate::allows('update', $deck),
            'exportExtension' => app(config("series.{$deck->game}.deck_format"))->extension(),
            // Your own deck and guests can't like.
            'liked' => $request->user() !== null && $deck->likes()->where('user_id', $request->user()->id)->exists(),
            'canLike' => Gate::allows('like', $deck),
        ]);
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

        return $this->builderView($series, null, $cards);
    }

    public function store(SaveDeckRequest $request, string $series)
    {
        $deck = $request->user()->decks()->make(['game' => $series]);

        $this->deckService->save($deck, $request->validated(), $request->cardCounts());

        return redirect()->route('decks')->with('success', $this->savedMessage($deck, $request));
    }

    public function edit(Request $request, Deck $deck)
    {
        return $this->builderView($deck->game, $deck, $this->searchCards($request, $deck->game));
    }

    /**
     * The deck builder page, for a new deck ($deck null) or editing one.
     */
    private function builderView(string $series, ?Deck $deck, LengthAwarePaginator $cards)
    {
        $state = $this->builderState($series, $deck, $deck ? $this->deckService->buildSections($deck) : [], $cards);

        return view('decks.deck-builder', [
            'deck' => $deck,
            'editing' => $deck !== null,
            'game' => $series,
            'cards' => $cards,
            'state' => $state,
            'rules' => $state['rules'],
            'filterChoices' => FilterChoices::for($series, $this->cardService->for($series)->filterOptions()),
            'deckFormat' => app(config("series.$series.deck_format")),
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
