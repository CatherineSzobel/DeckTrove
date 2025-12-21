<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MagicPackService;
use Illuminate\Pagination\LengthAwarePaginator;

class MagicPackController extends Controller
{
    protected MagicPackService $packService;

    public function __construct(MagicPackService $packService)
    {
        $this->packService = $packService;
    }


    public function index(Request $request)
    {
        $currentView = $request->input('view', 'full');
        $series = 'magic';
        $page = (int) $request->input('page', 1);
        $search = $request->input('search', '');

        $sets = $this->packService->getSets();
        $sets = $this->packService->searchSets($search, $sets);

        $perPage = 30;
        $offset = ($page - 1) * $perPage;
        $paged = array_slice($sets, $offset, $perPage);

        $paginator = new LengthAwarePaginator(
            $paged,
            count($sets),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('packs.packs', 
        ['packs' => $paginator, 
        'series' => $series, 
        'currentView' => $currentView]);
    }

    public function show(string $code)
    {

        $set = $this->packService->getSet($code);
        $cards = $this->packService->getSetCards($code);

        return view('packs.pack', [
            'set' => $set,
            'cards' => $cards,
            'series' => 'magic'
        ]);
    }
}
