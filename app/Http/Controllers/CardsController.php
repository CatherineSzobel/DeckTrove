<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $filters = $this->cardService->getFiltersBySeries($series);

        return view('cards.cards', [
            'cards' => $cards,
            'series' => $series,
            'currentView' => $view,
            'options' => array_values($filters),
            'seriesConfig' => $seriesConfig,
        ]);
    }

    public function show($series, $id)
    {
        $seriesConfig = config("series.$series");

        $card = $this->cardService->fetchCardById($id, $series);
        $setCards = $this->cardService->getSetCardsBySeries($series, $card);
        return view('cards.card', [
            'card' => $card,
            'setCards' => $setCards,
            'series' => $series,
            'seriesConfig' => $seriesConfig,
        ]);
    }
}
