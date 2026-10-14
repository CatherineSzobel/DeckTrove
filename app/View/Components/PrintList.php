<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

/**
 * The sets a card was printed in (CardViewModel::printPacks()), on the card page: the first few, the rest behind
 * "Show N more" (resources/js/cards/print-list.js). Without JS all of them are shown.
 */
class PrintList extends Component
{
    public const SHOWN = 4;

    /** @var Collection<int, array{set_name: string, code: string, url: string}> */
    public Collection $first;

    /** @var Collection<int, array{set_name: string, code: string, url: string}> */
    public Collection $more;

    public function __construct(Collection $packs)
    {
        $this->first = $packs->take(self::SHOWN);
        $this->more = $packs->slice(self::SHOWN)->values();
    }

    public function render(): View
    {
        return view('components.print-list');
    }
}
