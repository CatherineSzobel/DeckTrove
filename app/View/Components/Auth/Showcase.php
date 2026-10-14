<?php

namespace App\View\Components\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * The "About DeckTrove" side of Log in and Create account (resources/views/components/auth/showcase.blade.php):
 * two screenshots and three numbered selling points, which differ per page.
 */
class Showcase extends Component
{
    private const POINTS = [
        'login' => [
            'Track and edit your decks',
            'Build with rules checked as you go',
            'Share decks with the community',
        ],
        'register' => [
            'Save decks and come back to them any time',
            'Publish decks for the community to browse',
            'Pick up ideas from other players’ builds',
        ],
    ];

    /** @var list<array{number: string, text: string}> */
    public array $points;

    public function __construct(string $variant = 'login')
    {
        $this->points = collect(self::POINTS[$variant] ?? self::POINTS['login'])
            ->map(fn (string $text, int $index) => ['number' => sprintf('%02d', $index + 1), 'text' => $text])
            ->all();
    }

    public function render(): View
    {
        return view('components.auth.showcase');
    }
}
