<?php

namespace App\Http\Controllers;

use App\Services\PackService;
use App\ViewModels\CardCollectionViewModel;
use App\ViewModels\CardViewModel;
use App\ViewModels\PackViewModel;
use Illuminate\Http\Request;

class PacksController extends Controller
{
    private const VIEWS = ['full', 'list'];

    public function __construct(protected PackService $packService) {}

    public function index(Request $request, string $series)
    {
        $currentView = in_array($request->query('view'), self::VIEWS, true) ? $request->query('view') : 'full';
        $search = trim((string) $request->query('search', ''));
        $packConfig = config("series.$series.pack");

        $packs = $this->packService->for($series)
            ->paginate($search, max((int) $request->query('page', 1), 1))
            ->withQueryString()
            ->through(fn ($pack) => new PackViewModel((array) $pack, $packConfig));

        return view('packs.packs', compact('packs', 'series', 'currentView', 'search'));
    }

    public function show(string $series, string $setCode, ?string $slug = null)
    {
        $config = config("series.$series");
        $provider = $this->packService->for($series);

        $pack = new PackViewModel($provider->find($setCode, $slug), $config['pack']);
        $cards = $provider->cards($setCode, $slug)->map(fn ($card) => new CardViewModel((array) $card, $config));
        $cardCollection = new CardCollectionViewModel($cards);

        return view('packs.pack', compact('pack', 'cards', 'series', 'cardCollection'));
    }
}
