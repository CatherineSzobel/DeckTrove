<?php

namespace App\ViewModels;

use Illuminate\Support\Collection;

class CardCollectionViewModel
{
    public function __construct(protected Collection $cards) {}

    public function rarities(): array
    {
        return $this->cards
            ->map(fn (CardViewModel $card) => $card->rarity())
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * The rarity select's options: the raw rarity (what cards are filtered on) => the label shown.
     *
     * @return array<string, string>
     */
    public function rarityOptions(): array
    {
        return collect($this->rarities())->mapWithKeys(fn (string $rarity) => [$rarity => ucfirst($rarity)])->all();
    }
}
