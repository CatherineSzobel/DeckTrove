<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\YugiohService;

class YugiohController extends Controller
{
    public function index(Request $request, YugiohService $service)
    {
        $series = 'yugioh';
        $view = $request->get('view', 'full');

        $cards = $service->loadCards();
        $filtered = $service->applyFilters($cards, $request);
        $paginated = $service->paginate($filtered, $request, $view);
        $filterOptions = $service->generateFilterOptions($cards);

        return view('cards.cards', [
            'cards' => $paginated,
            'series' => $series,
            'filterOptions' => $filterOptions
        ]);
    }

    public function show(int $id, YugiohService $service)
    {
        $card = $service->findCard($id);
        $related = $service->findRelatedCards($card);

        return view('cards.card', [
            'card' => $card,
            'series' => 'yugioh',
            'archetypeCards' => $related
        ]);
    }
}
