<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PackService;

class PacksController extends Controller
{
    public function __construct(protected PackService $packService) {}

    public function index(Request $request, $series)
    {
        $currentView = $request->input('view', 'full');
        $page = (int) $request->input('page', 1);
        $search = $request->input('search', '');
        $params = [
            'view' => $currentView,
            'page' => $page,
            'search' => $search,
        ];

        $packs = $this->packService->getPaginatedPacksBySeries($series, $params);
        return view('packs.packs', [
            'packs' => $packs,
            'series' => $series,
            'currentView' => $currentView,
            'seriesConfig' => config("series.$series"),
        ]);
    }

    public function show($series, $id)
    {

        $set = $this->packService->getPackByIdBySeries($series, $id);

        $cards = $this->packService->getCardsFromSetBySeries($series, $id);

        return view('packs.pack', [
            'pack' => $set,
            'cards' => $cards,
            'series' => $series,
            'seriesConfig' => config("series.$series"),
        ]);
    }
}
