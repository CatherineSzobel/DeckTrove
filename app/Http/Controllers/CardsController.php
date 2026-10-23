<?php

namespace App\Http\Controllers;

use App\Cards\CardQuery;
use App\Cards\FilterChoices;
use App\Collection\CardCollectionPanel;
use App\Collection\TileCounts;
use App\Services\CardService;
use App\ViewModels\CardViewModel;
use Illuminate\Http\Request;

class CardsController extends Controller
{
    public function __construct(protected CardService $cardService) {}

    /**
     * The card database. AJAX requests (resources/js/card-database.js) get just the results region as HTML.
     */
    public function index(Request $request, string $series)
    {
        $config = config("series.$series");
        $provider = $this->cardService->for($series);
        $filterChoices = FilterChoices::for($series, $provider->filterOptions());
        $query = CardQuery::fromRequest($request, $series, $filterChoices);

        $cards = $provider->search($query->params())->through(fn ($card) => new CardViewModel($card, $config));
        // The owned count under every tile, in one query (none for guests).
        $tileCounts = TileCounts::for($request->user(), $series, $cards->getCollection());

        if ($request->ajax()) {
            return response()->view('cards.partials.results', compact('cards', 'query', 'series', 'tileCounts'))->header('Vary', 'X-Requested-With');
        }

        return response()->view('cards.cards', [
            'cards' => $cards,
            'query' => $query,
            'series' => $series,
            'tileCounts' => $tileCounts,
            'seriesLabel' => $config['label'],
            'filterChoices' => $filterChoices,
        ])->header('Vary', 'X-Requested-With');
    }

    public function show(string $series, string $id)
    {
        $config = config("series.$series");
        $provider = $this->cardService->for($series);

        $card = $provider->find($id);

        $viewModel = new CardViewModel($card, $config);
        $collection = CardCollectionPanel::for(request()->user(), $series, $viewModel, $provider);
        // A player's printings carry counters; guests (or a Scryfall outage) get the usual list of packs.
        $printRows = $collection?->printRows($series);

        return view('cards.card', [
            'series' => $series,
            'seriesLabel' => $config['label'],
            'relatedLabel' => $config['related_label'],
            'card' => $viewModel,
            'collection' => $collection,
            'printList' => $printRows ?? $viewModel->printPacks($series),
            'printCounters' => $printRows ? ['game' => $series, 'card' => $viewModel->id()] : null,
            'setCards' => $provider->related($card)->map(fn ($c) => new CardViewModel($c, $config)),
        ]);
    }
}
