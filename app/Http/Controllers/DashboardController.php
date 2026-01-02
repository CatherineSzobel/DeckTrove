<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request, DashboardService $service)
    {
        if (!Auth::check()) return redirect()->route('login');

        $user = Auth::user();
        $tcg = $request->query('tcg', 'all');
        $stats = $service->countDecksByTcg($user);
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
            'recentDecks' => $service->getRecentDecks($user, $tcg),
            'yugiohDecks' => $stats['yugioh'],
            'magicDecks' => $stats['magic'],
            'randomCards' => $randomCards,
            'selectedTcg' => $tcg,
            'tcgs' => $tcgs,
        ]);
    }
}
