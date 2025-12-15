<?php

namespace App\Services;

use App\Models\Deck;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Get user deck statistics
     */
    public function getDeckStats($userId, $tcg = 'all')
    {
        $query = Deck::where('user_id', $userId);

        if ($tcg !== 'all') {
            $query->where('game', $tcg); // 'yugioh' or 'magic'
        }

        $recent = $query->orderBy('created_at', 'desc')->take(6)->get();

        $allDecks = Deck::where('user_id', $userId)->get();

        return [
            'total' => $allDecks->count(),
            'recent' => $recent,
            'yugioh' => $allDecks->where('game', 'yugioh')->count(),
            'magic' => $allDecks->where('game', 'magic')->count(),
        ];
    }


    /**
     * Get random cards (70% YGO, 30% Magic)
     */
    public function getRandomCards(int $count): Collection
    {
        $weight = 0.7;
        $ygoCount = (int) round($count * $weight);
        $magicCount = $count - $ygoCount;

        return $this->getRandomYugiohCards($ygoCount)
            ->merge($this->getRandomMagicCards($magicCount))
            ->shuffle()
            ->values();
    }

    /**
     * Random Yu-Gi-Oh cards from JSON
     */
    private function getRandomYugiohCards(int $count): Collection
    {
        $jsonPath = public_path('json/yugioh-cards.json');
        if (!File::exists($jsonPath)) return collect();

        $allCards = json_decode(File::get($jsonPath), true)['data'] ?? [];
        if (empty($allCards)) return collect();

        $randomIndices = array_rand($allCards, min($count, count($allCards)));
        if (!is_array($randomIndices)) $randomIndices = [$randomIndices];

        $cards = array_map(fn($i) => $allCards[$i] ?? null, $randomIndices);
        return collect(array_filter($cards));
    }

    /**
     * Random Magic cards from Scryfall API
     */
    private function getRandomMagicCards(int $count): Collection
    {
        $cards = [];

        for ($i = 0; $i < $count; $i++) {
            try {
                $response = Http::get('https://api.scryfall.com/cards/random');
                if ($response->successful()) {
                    $cards[] = $response->json();
                } else {
                    $cards[] = ['error' => 'Failed to fetch card', 'status' => $response->status()];
                }
            } catch (\Exception $e) {
                $cards[] = ['error' => $e->getMessage()];
            }
        }

        return collect($cards);
    }
}
