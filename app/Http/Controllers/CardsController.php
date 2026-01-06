<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ViewModels\CardViewModel;
use App\Services\CardService;

class CardsController extends Controller
{
    public function __construct(protected CardService $cardService) {}

    public function index(Request $request, $series)
    {
        $seriesConfig = config("series.$series");

        $view = $request->get('view', 'full');
        $page = max((int) $request->get('page', 1), 1);

        $params = $request->only($seriesConfig['filter_fields'] ?? []);
        $params['page'] = $page;
        $params['view'] = $view;

        $cards = $this->cardService->getCardsBySeries($series, $params);

        $cards = $cards->through(
            fn($card) => new CardViewModel($card, $seriesConfig)
        );

        $filters = $this->cardService->getFiltersBySeries($series);

        return view('cards.cards', array_merge(
            compact('cards', 'series'),
            [
                'currentView' => $view,
                'options' => array_values($filters),
            ]
        ));
    }

    public function show($series, $id)
    {
        $seriesConfig = config("series.$series");

        $card = $this->cardService->fetchCardById($id, $series);

        $setCards = $this->cardService
            ->getSetCardsBySeries($series, $card)
            ->map(fn($c) => new CardViewModel($c, $seriesConfig));

        return view('cards.card', array_merge(
            compact('setCards', 'series'),
            [
                'card' => new CardViewModel($card, $seriesConfig),
            ]
        ));
    }
}
