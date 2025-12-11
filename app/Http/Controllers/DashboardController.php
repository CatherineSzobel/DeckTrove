<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Authenticated user
        $user = Auth::user();
        $game = 'yugioh';
        $totalDecks = 0;
        $recentDecks = collect();
        $yugiohDecks = 0;
        $magicDecks = 0;

        if ($user) {
            $decks = Deck::where('user_id', $user->id)->get();

            $totalDecks = $decks->count();
            $recentDecks = $decks->take(3);

            $yugiohDecks = $decks->where('game', 'yugioh')->count();
            $magicDecks = $decks->where('game', 'magic')->count();
        }

        // Get 10 random Yu-Gi-Oh cards
        $randomCards = $this->getRandomCards(10);
        return view('dashboard', [
            'totalDecks' => $totalDecks,
            'recentDecks' => $recentDecks,
            'yugiohDecks' => $yugiohDecks,
            'magicDecks' => $magicDecks,
            'randomCards' => $randomCards,
        ]);
    }
    private function getRandomCards($count)
    {
        // 70% Yu-Gi-Oh, 30% Magic
        $weight = 0.7;
        $ygoCount   = (int) round($count * $weight);
        $magicCount = $count - $ygoCount;

        $ygoCards   = $this->getRandomYugiohCards($ygoCount);
        $magicCards = $this->getRandomMagicCards($magicCount);

        return $ygoCards
            ->merge($magicCards)
            ->shuffle()
            ->values();
    }
    private function getRandomYugiohCards($count)
    {
        $jsonPath = public_path('json/yugioh-cards.json');

        if (!File::exists($jsonPath)) {
            return collect();
        }

        $json = File::get($jsonPath);
        $allCards = json_decode($json, true)['data'] ?? [];

        if (empty($allCards)) {
            return collect();
        }

        $randomIndices = array_rand($allCards, min($count, count($allCards)));

        if (!is_array($randomIndices)) {
            $randomIndices = [$randomIndices];
        }

        $randomCards = array_values(
            array_filter(
                array_map(
                    fn($index) => $allCards[$index] ?? null, $randomIndices)));


        return collect($randomCards);
    }
    private function getRandomMagicCards($count)
    {
        $cards = [];

        for ($i = 0; $i < $count; $i++) {
            try {
                $response = Http::get('https://api.scryfall.com/cards/random');

                // Ensure the request was successful
                if ($response->successful()) {
                    $cards[] = $response->json();
                } else {
                    // Handle non-200 responses gracefully
                    $cards[] = [
                        'error' => 'Failed to fetch card',
                        'status' => $response->status()
                    ];
                }
            } catch (\Exception $e) {
                // Handle network or SSL errors
                $cards[] = [
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $cards;
    }
}
