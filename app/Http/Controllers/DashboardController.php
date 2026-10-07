<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $service)
    {
        $user = $request->user();
        $selectedTcg = array_key_exists((string) $request->query('tcg'), config('series')) ? $request->query('tcg') : null;

        return view('dashboard', [
            'deckCounts' => $service->deckCounts($user),
            'recentDecks' => $service->recentDecks($user, $selectedTcg),
            'randomCards' => $service->randomCards(),
            'selectedTcg' => $selectedTcg,
        ]);
    }
}
