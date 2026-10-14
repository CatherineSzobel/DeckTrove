<?php

namespace App\Home;

use App\Models\User;

/**
 * What the home page (resources/views/index.blade.php) shows: the games from config/series.php and
 * config/coming_soon.php, the deck builder link, the steps and a call to action for guests or logged-in users.
 */
final class HomePage
{
    private const STEPS = [
        ['title' => 'Browse the database', 'text' => 'Look up full card details, filter by type, and flip through every pack and set — including the newest releases.'],
        ['title' => 'Build against the rules', 'text' => 'Add cards to your deck and DeckTrove checks it against the game’s rules as you build, so you know it’s legal before you save.'],
        ['title' => 'Share with the community', 'text' => 'Publish your decks for others to browse, and pick up new ideas from theirs in the public deck list.'],
    ];

    private function __construct(private ?User $user, private string $builderSeries) {}

    public static function for(?User $user, ?string $sessionSeries): self
    {
        $series = array_key_exists((string) $sessionSeries, config('series')) ? $sessionSeries : array_key_first(config('series'));

        return new self($user, $series);
    }

    public function eyebrow(): string
    {
        $labels = collect(config('series'))->pluck('label');

        return $labels->when(config('coming_soon') !== [], fn ($labels) => $labels->push('more on the way'))->implode(' · ');
    }

    /**
     * @return list<array{number: string, label: string, tagline: string, live: bool, href: ?string}>
     */
    public function games(): array
    {
        $live = collect(config('series'))->map(fn (array $config, string $key) => [
            'label' => $config['label'],
            'tagline' => $config['tagline'],
            'live' => true,
            'href' => route('cards.index', $key),
        ]);

        $soon = collect(config('coming_soon'))->map(fn (array $config) => [
            'label' => $config['label'],
            'tagline' => $config['tagline'],
            'live' => false,
            'href' => null,
        ]);

        return $live->values()->merge($soon->values())
            ->map(fn (array $game, int $index) => ['number' => sprintf('%02d', $index + 1)] + $game)
            ->all();
    }

    public function builderUrl(): string
    {
        return route('decks.builder', $this->builderSeries);
    }

    public function showGuestNote(): bool
    {
        return $this->user === null;
    }

    /**
     * @return array{title: string, text: string, primary: array{label: string, href: string}, secondary: array{label: string, href: string}}
     */
    public function cta(): array
    {
        return $this->user === null
            ? [
                'title' => 'Your next deck starts here',
                'text' => 'Create an account to save decks and share them.',
                'primary' => ['label' => 'Create account', 'href' => route('register')],
                'secondary' => ['label' => 'Log in', 'href' => route('login')],
            ]
            : [
                'title' => 'Your next deck starts here',
                'text' => 'Pick up where you left off, or start something new.',
                'primary' => ['label' => 'New deck', 'href' => $this->builderUrl()],
                'secondary' => ['label' => 'Go to your dashboard', 'href' => route('dashboard')],
            ];
    }

    /**
     * @return list<array{number: string, title: string, text: string}>
     */
    public function steps(): array
    {
        return collect(self::STEPS)
            ->map(fn (array $step, int $index) => ['number' => sprintf('%02d', $index + 1)] + $step)
            ->all();
    }
}
