<?php

namespace App\View\Components;

use App\Support\Series;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Switches to the same section in another game (resources/views/components/game-switch.blade.php): cards → cards,
 * packs → packs, deck builder → deck builder.
 */
class GameSwitch extends Component
{
    /** @var list<array{key: string, label: string, href: string, current: bool}> */
    public array $links;

    public function __construct(public string $series)
    {
        $route = request()->route()?->getName() ?? '';

        $target = match (true) {
            str_starts_with($route, 'packs.') => 'packs.index',
            $route === 'decks.builder' => 'decks.builder',
            default => 'cards.index',
        };

        $this->links = Series::links(fn (string $key) => route($target, $key), $series);
    }

    public function render(): View
    {
        return view('components.game-switch');
    }
}
