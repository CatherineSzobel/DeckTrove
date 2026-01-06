<?php

namespace App\ViewModels;

use Illuminate\Support\Collection;

class CardCollectionViewModel
{
    public function __construct(protected Collection $cards) {}

    public function rarities(): array
    {
        return $this->cards
            ->map(fn(CardViewModel $card) => $card->rarity())
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }
}
