<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

/**
 * The sets a card was printed in on the card page: the first few, the rest behind "Show N more"
 * (resources/js/cards/print-list.js). Without JS all of them are shown. Rows come from CardViewModel::printRows() for everyone;
 * for a player, `counters` (the card's game and id) gives each row its − n +.
 */
class PrintList extends Component
{
    public const SHOWN = 4;

    /** @var Collection<int, array<string, mixed>> */
    public Collection $first;

    /** @var Collection<int, array<string, mixed>> */
    public Collection $more;

    /** @param  array{game: string, card: string}|null  $counters */
    public function __construct(Collection $packs, public ?array $counters = null)
    {
        $this->first = $packs->take(self::SHOWN);
        $this->more = $packs->slice(self::SHOWN)->values();
    }

    public function render(): View
    {
        return view('components.print-list');
    }
}
