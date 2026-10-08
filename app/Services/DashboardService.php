<?php

namespace App\Services;

use App\Models\User;
use App\ViewModels\CardViewModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function __construct(private readonly CardService $cards) {}

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
     * Random cards from every series for the dashboard carousel, refreshed every 10 minutes.
     *
     * @return Collection<int, CardViewModel>
     */
    public function randomCards(int $perSeries = 4): Collection
    {
        return collect(config('series'))
            ->flatMap(function (array $config, string $series) use ($perSeries) {
                // Only the raw card data is cached; view models are rebuilt from it.
                $cards = Cache::remember("dashboard-random-cards-$series-$perSeries", 600,
                    fn () => $this->cards->for($series)->random($perSeries)->values()->all());

                return array_map(fn (array $card) => new CardViewModel($card, $config), $cards);
            })
            ->shuffle()
            ->values();
    }
}
