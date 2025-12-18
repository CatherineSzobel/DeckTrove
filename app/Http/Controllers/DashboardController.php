<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index(DashboardService $service)
    {
        if (!Auth::check()) return redirect()->route('login');

        $user = Auth::user();
        $tcg = request('tcg', 'all');
        $stats = $service->getDeckAmountCount($user->id, $tcg);
        $randomCards = $service->getRandomCards(5);
        $tcgs = [
            ['name' => 'Total', 'count' => $stats['total'] ?? 0, 'color' => 'amber-400'],
            ['name' => 'Yu-Gi-Oh!', 'count' => $stats['yugioh'] ?? 0, 'color' => 'blue-400'],
            ['name' => 'Magic', 'count' => $stats['magic'] ?? 0, 'color' => 'purple-400'],
            ['name' => 'Pokemon', 'count' => $stats['pokemon'] ?? 0, 'color' => 'green-400'],
            ['name' => 'Digimon', 'count' => $stats['digimon'] ?? 0, 'color' => 'red-400'],
        ];
        return view('dashboard', [
            'totalDecks' => $stats['total'],
            'recentDecks' => $service->getRecentDecks($user->id, $tcg),
            'yugiohDecks' => $stats['yugioh'],
            'magicDecks' => $stats['magic'],
            'randomCards' => $randomCards,
            'selectedTcg' => $tcg,
            'tcgs' => $tcgs,
        ]);
    }
}
