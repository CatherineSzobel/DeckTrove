<?php

namespace App\Services;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class DashboardService
{

    public function getRecentDecks(User $user, string $tcg = 'all', int $limit = 3): Collection
    {
        return Deck::where('user_id', $user->id)
            ->when($tcg !== 'all', fn($q) => $q->where('game', $tcg))
            ->latest()
            ->take($limit)
            ->get();
    }
    /**
     * Get user deck statistics
     */
    public function countDecksByTcg(User $user): array
    {
        $counts = Deck::where('user_id', $user->id)
            ->selectRaw('game, COUNT(*) as count')
            ->groupBy('game')
            ->pluck('count', 'game');

        return [
            'total'   => $counts->sum(),
            'yugioh'  => $counts['yugioh'] ?? 0,
            'magic'   => $counts['magic'] ?? 0,
            'pokemon' => $counts['pokemon'] ?? 0,
            'digimon' => $counts['digimon'] ?? 0,
        ];
    }

    /**
     * @TODO refactor into a cardservice so for the future if there are more tcg series
     */
    public function getRandomCards(int $count): Collection
    {
        return $this->getRandomMagicCards($count)
            ->shuffle()
            ->values();
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
