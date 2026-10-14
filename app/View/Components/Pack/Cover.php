<?php

namespace App\View\Components\Pack;

use App\ViewModels\PackViewModel;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * A pack's cover (resources/views/components/pack/cover.blade.php): its art, its set symbol on a tile, or a tile
 * with its code. `size` is xs (List view), sm (Full view) or lg (pack page); `.pack-cover` in app.css sizes it.
 */
class Cover extends Component
{
    public string $kind;

    public function __construct(public PackViewModel $pack, public string $size = 'sm')
    {
        $this->kind = $pack->coverKind();
    }

    public function render(): View
    {
        return view('components.pack.cover');
    }
}
