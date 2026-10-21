<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * The screenshots that take turns on a soft accent glow (resources/views/components/showcase-shots.blade.php): the
 * log in / create account showcase and the home page hero. The CSS loop in app.css is written for exactly four.
 */
class ShowcaseShots extends Component
{
    /** In turn order: the first starts in front, the second behind it. */
    public const SHOTS = [
        ['path' => 'resources/img/screenshots/card-database.png', 'alt' => 'DeckTrove card database'],
        ['path' => 'resources/img/screenshots/deck-builder.png', 'alt' => 'DeckTrove deck builder'],
        ['path' => 'resources/img/screenshots/deck-page.png', 'alt' => 'DeckTrove deck page'],
        ['path' => 'resources/img/screenshots/pack-page.png', 'alt' => 'DeckTrove pack page'],
    ];

    /** @var list<array{number: int, path: string, alt: string}> */
    public array $shots;

    public function __construct()
    {
        $this->shots = collect(self::SHOTS)
            ->map(fn (array $shot, int $index) => ['number' => $index + 1, ...$shot])
            ->all();
    }

    public function render(): View
    {
        return view('components.showcase-shots');
    }
}
