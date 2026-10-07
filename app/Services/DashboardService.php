<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function __construct(private readonly MagicService $magic) {}

    public function recentDecks(User $user, ?string $game = null, int $limit = 3): Collection
    {
        return $user->decks()
            ->withCardCount()
            ->when($game, fn ($query) => $query->where('game', $game))
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Deck counts for every supported series, plus a total.
     *
     * @return array<string, int>
     */
    public function deckCounts(User $user): array
    {
        $counts = $user->decks()
            ->selectRaw('game, COUNT(*) as count')
            ->groupBy('game')
            ->pluck('count', 'game');

        return collect(config('series'))
            ->map(fn ($config, $series) => (int) ($counts[$series] ?? 0))
            ->prepend((int) $counts->sum(), 'total')
            ->all();
    }

    /**
     * A handful of random Magic cards for the dashboard carousel, refreshed every 10 minutes.
     */
    public function randomCards(int $count = 6): Collection
    {
        return Cache::remember("dashboard-random-cards-$count", 600, fn () => $this->magic->random($count));
    }
}
