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
     * The dashboard's stat tiles: total decks, then one per series.
     *
     * @return list<array{label: string, value: int, accent: bool}>
     */
    public function stats(User $user): array
    {
        $counts = $user->decks()
            ->selectRaw('game, COUNT(*) as count')
            ->groupBy('game')
            ->pluck('count', 'game');

        return [
            ['label' => 'Total decks', 'value' => (int) $counts->sum(), 'accent' => true],
            ...collect(config('series'))
                ->map(fn (array $config, string $series) => ['label' => $config['label'], 'value' => (int) ($counts[$series] ?? 0), 'accent' => false])
                ->values()
                ->all(),
        ];
    }

    /**
     * The "Your decks" series switch: All, then one link per series.
     *
     * @return list<array{label: string, href: string, current: bool}>
     */
    public function seriesLinks(?string $selected): array
    {
        return [
            ['label' => 'All', 'href' => route('dashboard'), 'current' => $selected === null],
            ...collect(config('series'))
                ->map(fn (array $config, string $series) => ['label' => $config['label'], 'href' => route('dashboard', ['tcg' => $series]), 'current' => $selected === $series])
                ->values()
                ->all(),
        ];
    }

    /**
     * The "Discover random cards" series switch: one link per series back to the section, keeping the deck filter.
     *
     * @return list<array{label: string, href: string, current: bool}>
     */
    public function randomSeriesLinks(string $current, ?string $deckFilter): array
    {
        return collect(config('series'))
            ->map(fn (array $config, string $series) => [
                'label' => $config['label'],
                'href' => route('dashboard', array_filter(['tcg' => $deckFilter, 'cards' => $series])).'#discover',
                'current' => $series === $current,
            ])
            ->values()
            ->all();
    }

    /**
     * The title of the empty "Your decks" panel.
     */
    public function emptyTitle(int $totalDecks, ?string $series): string
    {
        return $totalDecks > 0 && $series
            ? 'No '.config("series.$series.label").' decks yet'
            : 'You don’t have any decks yet';
    }

    /**
     * The dashboard's shortcuts: each series' cards, public decks and the user's public profile.
     *
     * @return list<array{href: string, title: string}>
     */
    public function quickLinks(User $user): array
    {
        return [
            ...collect(config('series'))
                ->map(fn (array $config, string $series) => ['href' => route('cards.index', $series), 'title' => $config['label'].' cards'])
                ->values()
                ->all(),
            ['href' => route('decks.index'), 'title' => 'Public decks'],
            ['href' => route('users.show', $user), 'title' => 'Your public profile'],
        ];
    }

    /**
     * Random cards from one series for "Discover random cards", refreshed every 10 minutes.
     *
     * @return Collection<int, CardViewModel>
     */
    public function randomCards(string $series, int $count = 10): Collection
    {
        // Only the raw card data is cached; view models are rebuilt from it.
        $cards = Cache::remember("dashboard-random-cards-$series-$count", 600,
            fn () => $this->cards->for($series)->random($count)->values()->all());

        return collect($cards)->map(fn (array $card) => new CardViewModel($card, config("series.$series")));
    }
}
