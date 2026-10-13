<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Switches to the same section in another game (resources/views/components/game-switch.blade.php): cards → cards,
 * packs → packs, deck builder → deck builder.
 */
class GameSwitch extends Component
{
    /** The route each game's link goes to. */
    public string $target;

    public function __construct(public string $series)
    {
        $route = request()->route()?->getName() ?? '';

        $this->target = match (true) {
            str_starts_with($route, 'packs.') => 'packs.index',
            $route === 'decks.builder' => 'decks.builder',
            default => 'cards.index',
        };
    }

    public function render(): View
    {
        return view('components.game-switch');
    }
}
