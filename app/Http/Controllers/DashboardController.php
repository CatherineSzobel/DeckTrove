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
        $stats = $service->getDeckStats($user->id, $tcg);
        $randomCards = $service->getRandomCards(10);

        return view('dashboard', [
            'totalDecks' => $stats['total'],
            'recentDecks' => $stats['recent'],
            'yugiohDecks' => $stats['yugioh'],
            'magicDecks' => $stats['magic'],
            'randomCards' => $randomCards,
            'selectedTcg' => $tcg,
        ]);
    }
}
