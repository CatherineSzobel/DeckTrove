<?php

namespace App\View\Components\Collection;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * The quiet − n + for one card on one list, or one printing (resources/views/components/collection/counter.blade.php):
 * two plain POST forms around the count, upgraded in place by collectionCounter (resources/js/collection/counter.js).
 * $anchor names the card page section the redirect goes back to without JS (CollectionCardRequest::ANCHORS); without
 * it the redirect goes to the card's tile.
 */
class Counter extends Component
{
    public bool $removable;

    public function __construct(
        public string $game,
        public string $card,
        public string $name,
        public int $count,
        public string $list = 'owned',
        public string $printing = '',
        ?bool $removable = null,
        public string $size = 'tile',
        public ?string $anchor = null,
    ) {
        $this->removable = $removable ?? $count > 0;
    }

    public function canRemove(): bool
    {
        return $this->removable && $this->count > 0;
    }

    public function addLabel(): string
    {
        return "Add a copy of {$this->name}";
    }

    public function removeLabel(): string
    {
        return "Remove a copy of {$this->name}";
    }

    /** Why "−" is off although there are copies: they are all in printings, which the card page takes off. */
    public function hint(): ?string
    {
        return $this->count > 0 && ! $this->removable ? 'Remove it from a printing on the card page.' : null;
    }

    public function hintId(): string
    {
        return "counter-hint-{$this->game}-{$this->card}-{$this->list}-{$this->printing}";
    }

    public function countClass(): string
    {
        return $this->size === 'row' ? 'min-w-7 text-lg' : 'min-w-5 text-[15px]';
    }

    /** For collectionCounter's start(): whether "−" starts on, as a data attribute. */
    public function removableFlag(): string
    {
        return $this->removable ? '1' : '0';
    }

    public function isZero(): bool
    {
        return $this->count < 1;
    }

    public function render(): View
    {
        return view('components.collection.counter');
    }
}
