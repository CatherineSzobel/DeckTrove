<?php

namespace App\Decks;

use JsonSerializable;

/**
 * A deck's make-up and price, built by DeckStats. Plain data, so any view can render it.
 *
 * Group values are array keys, so numeric values (e.g. mana value "3") come back as int keys. In JSON (e.g. `@json`
 * for the frontend) each group's values become a list of ['value' => '3', 'count' => 2], in the same order, because
 * keys 0, 1, 2 would otherwise encode as a plain list and lose the values.
 */
final readonly class DeckStatsResult implements JsonSerializable
{
    /**
     * @param  array<string, array{label: string, count: int, price: float, groups: array<string, array{label: string, values: array<string|int, int>}>}>  $zones  in `deck.zones` order
     * @param  float  $price  the whole deck, full precision (round when displaying)
     * @param  array{code: string, symbol: string}  $currency
     * @param  int  $missingPrices  copies without a price, which the price total leaves out
     */
    public function __construct(
        public array $zones,
        public float $price,
        public array $currency,
        public int $missingPrices,
    ) {}

    /**
     * One zone's stats, or null when the series has no such zone.
     */
    public function zone(string $key): ?array
    {
        return $this->zones[$key] ?? null;
    }

    /**
     * How many cards in a zone have a value in a group, e.g. groupCount('main', 'category', 'Monster').
     */
    public function groupCount(string $zone, string $group, string $value): int
    {
        return $this->zones[$zone]['groups'][$group]['values'][$value] ?? 0;
    }

    public function jsonSerialize(): array
    {
        $zones = array_map(fn (array $zone) => [
            ...$zone,
            'groups' => array_map(fn (array $group) => [
                'label' => $group['label'],
                'values' => array_map(
                    fn ($value, int $count) => ['value' => (string) $value, 'count' => $count],
                    array_keys($group['values']),
                    $group['values'],
                ),
            ], $zone['groups']),
        ], $this->zones);

        return [
            'zones' => $zones,
            'price' => $this->price,
            'currency' => $this->currency,
            'missingPrices' => $this->missingPrices,
        ];
    }
}
