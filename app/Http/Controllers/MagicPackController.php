<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MagicPackService;
use Illuminate\Pagination\LengthAwarePaginator;

class MagicPackController extends Controller
{
    public function index(Request $request, MagicPackService $packService)
    {
        $series = 'magic';
        $page = $request->get('page', 1);
        $search = $request->get('search', '');

        $sets = $packService->getSets();
        $sets = $packService->searchSets($search, $sets);

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

        return view('packs.packs', ['packs' => $paginator, 'series' => $series]);
    }

    public function show($code, MagicPackService $packService)
    {
        $set = $packService->getSet($code);
        $cards = $packService->getSetCards($code);

        return view('packs.pack', [
            'set' => $set,
            'cards' => $cards,
            'series' => 'magic'
        ]);
    }
}
