<?php

namespace App\View\Components;

use App\ViewModels\CardViewModel;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * "2 faces" on a double-faced card's tile (resources/views/components/card-faces-badge.blade.php); nothing for a
 * single-faced card. $corner puts it on the image's corner (the Images view, where the name only shows on hover).
 */
class CardFacesBadge extends Component
{
    public function __construct(public CardViewModel $card, public bool $corner = false) {}

    public function shouldRender(): bool
    {
        return $this->card->hasFaces();
    }

    public function label(): string
    {
        return count($this->card->faces()).' faces';
    }

    public function classes(): string
    {
        return $this->corner ? 'pointer-events-none absolute right-2 top-2 z-10 shadow-md' : 'self-start';
    }

    public function render(): View
    {
        return view('components.card-faces-badge');
    }
}
