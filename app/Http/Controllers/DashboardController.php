<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Support\Series;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $service)
    {
        $user = $request->user();
        $selectedTcg = Series::orNull($request->query('tcg'));
        // Picking a series for the random cards also makes it the remembered series (New deck, header links).
        if (Series::isKnown($request->query('cards'))) {
            session(['series' => $request->query('cards')]);
        }
        $builderSeries = Series::orDefault(session('series'));
        $stats = $service->stats($user);

        return view('dashboard', [
            'username' => $user->username,
            'builderSeries' => $builderSeries,
            'stats' => $stats,
            'seriesLinks' => $service->seriesLinks($selectedTcg),
            'recentDecks' => $service->recentDecks($user, $selectedTcg),
            'emptyTitle' => $service->emptyTitle($stats[0]['value'], $selectedTcg),
            'quickLinks' => $service->quickLinks($user),
            'randomCards' => $service->randomCards($builderSeries),
            'randomSeriesLinks' => $service->randomSeriesLinks($builderSeries, $selectedTcg),
        ]);
    }
}
