<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PackService;
use App\ViewModels\CardCollectionViewModel;
use App\ViewModels\CardViewModel;
use App\ViewModels\PackViewModel;

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
        $packs->getCollection()->transform(
            fn($pack) => new PackViewModel((array) $pack, config("series.$series.pack"))
        );

        return view('packs.packs', array_merge(
            compact('packs', 'series'),
            ['currentView' => $currentView]
        ));
    }

    public function show($series, $id)
    {
        $seriesConfig = config("series.$series");
        $packConfig = $seriesConfig['pack'] ?? [];

        $rawPack = $this->packService->getPackByIdBySeries($series, $id);

        $rawCards = $this->packService->getCardsFromSetBySeries($series, $id);
        $cards = collect($rawCards)->map(
            fn($card) => new CardViewModel((array) $card, $seriesConfig)
        );

        $cardCollection = new CardCollectionViewModel($cards);

        return view('packs.pack', array_merge(
            compact('cards', 'series', 'cardCollection', 'seriesConfig'),
            ['pack' => new PackViewModel((array) $rawPack, $packConfig)]
        ));
    }
}
