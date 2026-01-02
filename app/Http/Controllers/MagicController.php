<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\MagicService;
use Illuminate\Http\Request;

class MagicController extends Controller
{

    protected MagicService $magic;

    public function __construct(MagicService $magic)
    {
        $this->magic = $magic;
    }
    public function index(Request $request)
    {
        $view = $request->get('view', 'full');
        $page = max((int) $request->get('page', 1), 1);

        $filters = $request->only(['search', 'type', 'color', 'rarity', 'set_name']);
        $filters['page'] = $page;
        $filters['view'] = $view;

        $result = $this->magic->fetchCards($filters);

        $cards = $result['data'] ?? [];
        $total = $result['total_cards'] ?? count($cards);
        $perPage = $this->magic->itemsPerPage($view);

        $paginated = new LengthAwarePaginator(
            array_slice($cards, 0, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $filterOptions = $this->magic->getFilterOptions();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('cards.partials.cards-inner', [
                    'cards' => $paginated,
                    'series' => 'magic',
                    'currentView' => $view,
                ])->render(),
                'count' => $paginated->total()
                    ? "Showing {$paginated->firstItem()}-{$paginated->lastItem()} of {$paginated->total()} cards"
                    : "No cards found",
            ]);
        }

        return view('cards.cards', [
            'cards' => $paginated,
            'series' => 'magic',
            'options' => [
                $filterOptions['type'] ?? [],
                $filterOptions['color'] ?? [],
                $filterOptions['rarity'] ?? [],
                $filterOptions['set_name'] ?? [],
            ],
        ]);
    }

    public function show(string $id)
    {
        $card = $this->magic->fetchCardById($id);
        $setCards = $this->magic->fetchRelatedSetCards($card);

        return view('cards.card', [
            'card' => $card,
            'setCards' => $setCards,
            'series' => 'magic'
        ]);
    }
}
