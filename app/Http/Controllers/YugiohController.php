<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\YugiohService;

class YugiohController extends Controller
{
    public function __construct(protected YugiohService $yugiohService)
    {}
    public function index(Request $request)
    {
        $series = 'yugioh';
        $view = $request->get('view', 'full');

        $params = [
            'search'    => $request->input('search'),
            'type'      => $request->input('type'),
            'attribute' => $request->input('attribute'),
            'race'      => $request->input('race'),
            'archetype' => $request->input('archetype'),
            'page'      => $request->input('page', 1),
        ];

        $paginated = $this->yugiohService->searchForDeckBuilder($params, $view);
        $filters   = $this->yugiohService->getFilterOptions();

        return view('cards.cards', [
            'cards' => $paginated,
            'series' => $series,
            'options' => [
                $filters['type'] ?? [],
                $filters['attribute'] ?? [],
                $filters['race'] ?? [],
                $filters['archetype'] ?? [],
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
