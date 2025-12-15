<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\MagicService;
use Illuminate\Http\Request;

class MagicController extends Controller
{

    protected MagicService $magic;
    public function index(Request $request, MagicService $magic)
    {
        $page = $request->get('page', 1);
        $view = $request->get('view', 'full');
        $search = $request->get('search', '');

        $apiResponse = $magic->fetchCards([
            'page' => $page,
            'view' => $view,
            'search' => $search,
        ]);

        $cards = array_slice($apiResponse['data'] ?? [], 0, $magic->itemsPerPage($view));
        $paginated = new LengthAwarePaginator(
            $cards,
            $apiResponse['total_cards'] ?? 10000,
            $magic->itemsPerPage($view),
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('cards.cards', ['cards' => $paginated, 'series' => 'magic']);
    }

    public function __construct(MagicService $magic)
    {
        $this->magic = $magic;
    }

    /**
     * Show a single Magic card by ID
     */
    public function show(string $id)
    {
        // Fetch main card
        $card = $this->magic->fetchCardById($id);

        // Fetch related set cards (optional)
        $setCards = $this->magic->fetchRelatedSetCards($card);

        return view('cards.card', [
            'card' => $card,
            'setCards' => $setCards,
            'series' => 'magic'
        ]);
    }
}
