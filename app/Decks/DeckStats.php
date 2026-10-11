<?php

namespace App\Decks;

use App\ViewModels\CardViewModel;
use Illuminate\Support\Collection;

/**
 * Counts a deck's make-up and price per zone from the cards DeckService::buildSections() already loaded, so it
 * needs no queries or API calls. The groups come from the series' `deck.stats`; which value a card has in each
 * group comes from its mapper's statFacets().
 */
class DeckStats
{
    /**
     * @param  array<string, Collection<int, CardViewModel>>  $sections  one view model per copy, keyed by zone
     * @param  array  $seriesConfig  the series' entry in config/series.php
     */
    public function fromSections(array $sections, array $seriesConfig): DeckStatsResult
    {
        $groups = $seriesConfig['deck']['stats'];
        $zones = [];
        $missingPrices = 0;

        // Only the series' own zones count; anything else in the sections is old data.
        foreach ($seriesConfig['deck']['zones'] as $zone => $zoneRules) {
            $cards = $sections[$zone] ?? collect();
            $counts = array_fill_keys(array_keys($groups), []);
            $price = 0.0;

            foreach ($cards as $card) {
                $value = $card->priceValue();

                if ($value === null) {
                    $missingPrices++;
                } else {
                    $price += $value;
                }

                foreach ($card->statFacets() as $group => $facet) {
                    if (isset($counts[$group])) {
                        $counts[$group][$facet] = ($counts[$group][$facet] ?? 0) + 1;
                    }
                }
            }

            $zones[$zone] = [
                'label' => $zoneRules['label'],
                'count' => $cards->count(),
                'price' => $price,
                'groups' => collect($groups)->map(fn (array $group, string $key) => [
                    'label' => $group['label'],
                    'values' => $this->ordered($counts[$key], $group['order'] ?? []),
                ])->all(),
            ];
        }

        return new DeckStatsResult(
            zones: $zones,
            price: array_sum(array_column($zones, 'price')),
            currency: config('app.currency'),
            missingPrices: $missingPrices,
        );
    }

    /**
     * Values listed in `order` first, in that order, then the rest in natural order ("Level 2" before "Level 10").
     *
     * @param  array<string|int, int>  $counts
     * @param  list<string>  $order
     * @return array<string|int, int>
     */
    private function ordered(array $counts, array $order): array
    {
        $position = array_flip($order);

        uksort($counts, function ($a, $b) use ($position) {
            // Numeric values arrive as int keys.
            $a = (string) $a;
            $b = (string) $b;

            if (isset($position[$a]) || isset($position[$b])) {
                return ($position[$a] ?? PHP_INT_MAX) <=> ($position[$b] ?? PHP_INT_MAX);
            }

            return strnatcmp($a, $b);
        });

        return $counts;
    }
}
