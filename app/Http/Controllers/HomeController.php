<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
    public function index()
    {
        $totalDecks = 0;
        $recentDecks = collect();
        $totalCards = 0;
        $yugiohDecks = 0;
        $magicDecks = 0;

        if (Auth::check()) {
            $user = Auth::user();
            $decks = Deck::where('user_id', $user->id)->get();
            
            $totalDecks = $decks->count();
            $recentDecks = $decks->take(3);
            
            $yugiohDecks = $decks->where('series', 'yugioh')->count();
            $magicDecks = $decks->where('series', 'magic')->count();
            
            // Count total cards in all decks
            foreach ($decks as $deck) {
                $totalCards += $deck->cards->count();
            }
        }

        // Get 10 random Yu-Gi-Oh cards
        $randomCards = $this->getRandomCards(10);
        //dd($randomCards);
        return view('home', [
            'totalDecks' => $totalDecks,
            'recentDecks' => $recentDecks,
            'totalCards' => $totalCards,
            'yugiohDecks' => $yugiohDecks,
            'magicDecks' => $magicDecks,
            'randomCards' => $randomCards,
        ]);
    }

    private function getRandomCards($count = 10)
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

        // Get random indices
        $randomIndices = array_rand($allCards, min($count, count($allCards)));
        
        // Ensure we always have an array
        if (!is_array($randomIndices)) {
            $randomIndices = [$randomIndices];
        }

        $randomCards = [];
        foreach ($randomIndices as $index) {
            if (isset($allCards[$index])) {
                $randomCards[] = $allCards[$index];
            }
        }

        return collect($randomCards);
    }
}