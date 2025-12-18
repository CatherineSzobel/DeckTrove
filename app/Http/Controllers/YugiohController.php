<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\YugiohService;

class YugiohController extends Controller
{
    protected $yugiohService;
    public function __construct(YugiohService $yugiohService) {
        $this->yugiohService = $yugiohService;
    }
    public function index(Request $request)
    {
        $series = 'yugioh';
        $view = $request->get('view', 'full');

        $cards = $this->yugiohService->loadCards();
        $filtered = $this->yugiohService->applyFilters($cards, $request);
        $paginated = $this->yugiohService->paginate($filtered, $request, $view);
        $filterOptions = $this->yugiohService->generateFilterOptions($cards);

        return view('cards.cards', [
            'cards' => $paginated,
            'series' => $series,
            'options' => [
                $filterOptions['type'] ?? [],
                $filterOptions['attribute'] ?? [],
                $filterOptions['race'] ?? [],
                $filterOptions['archetype'] ?? [],
            ],
        ]);
    }

    public function show(int $id)
    {
        $card = $this->yugiohService->findCard($id);
        $related = $this->yugiohService->findRelatedCards($card);

        return view('cards.card', [
            'card' => $card,
            'series' => 'yugioh',
            'archetypeCards' => $related
        ]);
    }
}
