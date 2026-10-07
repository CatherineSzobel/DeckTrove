<?php

namespace App\Cards;

/**
 * Yu-Gi-Oh! cards in the YGOPRODeck format.
 */
class YugiohCardMapper extends CardMapper
{
    public function image(array $card): string
    {
        return data_get($card, 'card_images.0.image_url') ?? self::PLACEHOLDER_IMAGE;
    }

    public function imageSmall(array $card): string
    {
        return data_get($card, 'card_images.0.image_url_small') ?? $this->image($card);
    }

    public function type(array $card): string
    {
        return $card['type'] ?? '';
    }

    public function subtype(array $card): string
    {
        return $card['race'] ?? '';
    }

    public function description(array $card): string
    {
        return $card['desc'] ?? '';
    }

    public function stats(array $card): array
    {
        $isLink = str_contains(strtolower($card['type'] ?? ''), 'link');

        return [
            'left' => ['label' => 'ATK', 'value' => $card['atk'] ?? null],
            'right' => $isLink
                ? ['label' => 'LINK', 'value' => $card['linkval'] ?? null]
                : ['label' => 'DEF', 'value' => $card['def'] ?? null],
        ];
    }

    public function setName(array $card): ?string
    {
        return data_get($card, 'card_sets.0.set_name');
    }

    public function rarity(array $card): ?string
    {
        return data_get($card, 'card_sets.0.set_rarity');
    }

    public function price(array $card): ?string
    {
        return data_get($card, 'card_prices.0.cardmarket_price');
    }

    public function printSets(array $card): array
    {
        return collect($card['card_sets'] ?? [])
            ->map(fn ($set) => [
                'set_code' => $set['set_code'] ?? '',
                'set_name' => $set['set_name'] ?? 'Unknown',
            ])
            ->all();
    }
}
