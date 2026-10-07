<?php

namespace App\Http\Controllers;

use App\Services\CardService;
use App\ViewModels\CardViewModel;
use Illuminate\Http\Request;

class CardsController extends Controller
{
    private const VIEWS = ['full', 'images', 'list'];

    public function __construct(protected CardService $cardService) {}

    /**
     * The card database. AJAX requests (live search/filter) get just the results as JSON.
     */
    public function index(Request $request, string $series)
    {
        $config = config("series.$series");
        $provider = $this->cardService->for($series);

        $currentView = in_array($request->query('view'), self::VIEWS, true) ? $request->query('view') : 'full';

        $params = $request->only([...array_keys($config['filters']), 'search', 'page']);
        $params['view'] = $currentView;

        $cards = $provider->search($params)->through(fn ($card) => new CardViewModel($card, $config));

        if ($request->ajax()) {
            return response()->json([
                'html' => view('cards.partials.cards-inner', compact('cards', 'series', 'currentView'))->render(),
                'count' => view('cards.partials.result-count', compact('cards'))->render(),
            ]);
        }

        return view('cards.cards', [
            'cards' => $cards,
            'series' => $series,
            'currentView' => $currentView,
            'options' => $provider->filterOptions(),
        ]);
    }

    public function show(string $series, string $id)
    {
        $config = config("series.$series");
        $provider = $this->cardService->for($series);

        $card = $provider->find($id);

        return view('cards.card', [
            'series' => $series,
            'card' => new CardViewModel($card, $config),
            'setCards' => $provider->related($card)->map(fn ($c) => new CardViewModel($c, $config)),
        ]);
    }
}
