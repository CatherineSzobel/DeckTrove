<?php

namespace App\Http\Controllers;

use App\Packs\PackCardFilter;
use App\Services\PackService;
use App\ViewModels\CardCollectionViewModel;
use App\ViewModels\CardViewModel;
use App\ViewModels\PackViewModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PacksController extends Controller
{
    private const VIEWS = ['full' => 'Full', 'list' => 'List'];

    public function __construct(protected PackService $packService) {}

    public function index(Request $request, string $series)
    {
        $currentView = array_key_exists((string) $request->query('view'), self::VIEWS) ? $request->query('view') : 'full';
        $search = is_string($request->query('search')) ? mb_substr(trim($request->query('search')), 0, 100) : '';
        $packConfig = config("series.$series.pack");

        $packs = $this->packService->for($series)
            ->paginate($search, max((int) $request->query('page', 1), 1))
            ->withQueryString()
            ->onEachSide(1)
            ->through(fn ($pack) => new PackViewModel((array) $pack, $packConfig));

        return view('packs.packs', [
            'packs' => $packs,
            'series' => $series,
            'seriesLabel' => config("series.$series.label"),
            'currentView' => $currentView,
            'search' => $search,
            'hiddenFields' => $currentView === 'list' ? ['view' => 'list'] : [],
            'viewLinks' => $this->viewLinks($series, $currentView, $search),
            'clearUrl' => route('packs.index', array_filter([$series, 'view' => $currentView === 'list' ? 'list' : null])),
            'showType' => isset($packConfig['type']),
            'resultLine' => $search === ''
                ? null
                : number_format($packs->total()).' '.Str::plural('pack', $packs->total())." matching “{$search}”",
        ]);
    }

    public function show(Request $request, string $series, string $setCode, ?string $slug = null)
    {
        $config = config("series.$series");
        $provider = $this->packService->for($series);

        $pack = new PackViewModel($provider->find($setCode, $slug), $config['pack']);
        $cards = $provider->cards($setCode, $slug)->map(fn ($card) => new CardViewModel((array) $card, $config));
        $filter = PackCardFilter::fromRequest($request);
        $shown = $filter->count($cards);

        return view('packs.pack', [
            'pack' => $pack,
            'cards' => $cards,
            'series' => $series,
            'seriesLabel' => $config['label'],
            'rarities' => (new CardCollectionViewModel($cards))->rarityOptions(),
            'filter' => $filter,
            'countLabel' => $filter->countLabel($shown, $cards->count()),
            'noMatches' => $cards->isNotEmpty() && $shown === 0,
        ]);
    }

    /**
     * The Full / List switch; each link keeps the search and starts at page one.
     *
     * @return list<array{key: string, label: string, href: string, current: bool}>
     */
    private function viewLinks(string $series, string $currentView, string $search): array
    {
        return collect(self::VIEWS)
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'href' => route('packs.index', array_filter([$series, 'view' => $key === 'full' ? null : $key, 'search' => $search ?: null])),
                'current' => $key === $currentView,
            ])
            ->values()
            ->all();
    }
}
