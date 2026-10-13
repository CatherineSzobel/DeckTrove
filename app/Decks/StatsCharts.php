<?php

namespace App\Decks;

use Illuminate\Support\Str;

/**
 * The deck page's stats sidebar, ready to draw: per zone a tab label and one chart per `deck.stats` group. The first
 * group is a stacked bar, mana curve and Level / Rank are columns, the rest are bars. Colours come from the group's
 * `colors` in config/series.php, falling back to the accent colour.
 */
final readonly class StatsCharts
{
    private const COLUMN_GROUPS = ['mana_value', 'level'];

    /**
     * @param  array<string, array{label: string, colors?: array<string, string>}>  $groups  the series' `deck.stats`
     */
    public function __construct(private DeckStatsResult $stats, private array $groups) {}

    public static function for(DeckStatsResult $stats, string $series): self
    {
        return new self($stats, config("series.$series.deck.stats", []));
    }

    /**
     * @return list<array{key: string, label: string, tab: string, count: int, price: float, charts: list<array{key: string, label: string, kind: string, values: list<array{value: string, count: int, short: string, colour: string, percent: int}>}>}>
     */
    public function zones(): array
    {
        $many = count($this->stats->zones) > 2;

        return collect($this->stats->zones)
            ->map(fn (array $zone, string $key) => [
                'key' => $key,
                'label' => $zone['label'],
                // "Main", "Extra", "Side"; with only two zones the second keeps its full name ("Sideboard").
                'tab' => $many || Str::before($zone['label'], ' ') === 'Main' ? Str::before($zone['label'], ' ') : $zone['label'],
                'count' => $zone['count'],
                'price' => $zone['price'],
                'charts' => $this->charts($zone['groups']),
            ])
            ->values()
            ->all();
    }

    /**
     * The line under the charts, e.g. "3 cards have no price · Cardmarket prices in euros".
     */
    public function priceNote(): string
    {
        $missing = $this->stats->missingPrices;
        $source = 'Cardmarket prices in euros';

        return $missing
            ? $missing.' '.Str::plural('card', $missing).' '.($missing === 1 ? 'has' : 'have').' no price · '.$source
            : $source;
    }

    private function charts(array $groups): array
    {
        $first = array_key_first($groups);

        return collect($groups)
            ->map(fn (array $group, string $key) => [
                'key' => $key,
                'label' => $group['label'],
                'kind' => $key === $first ? 'stacked' : (in_array($key, self::COLUMN_GROUPS, true) ? 'columns' : 'bars'),
                'values' => $this->values($key, $group['values']),
            ])
            ->values()
            ->all();
    }

    private function values(string $group, array $values): array
    {
        $max = max($values ?: [1]);

        return collect($values)
            ->map(fn (int $count, $value) => [
                'value' => (string) $value,
                'count' => $count,
                // Short column labels: "Level 4" → "4", "Rank 4" → "R4", "Link 2" → "L2".
                'short' => preg_replace(['/^Level /', '/^Rank /', '/^Link /'], ['', 'R', 'L'], (string) $value),
                'colour' => $this->groups[$group]['colors'][(string) $value] ?? 'var(--color-accent)',
                'percent' => (int) round($count / $max * 100),
            ])
            ->values()
            ->all();
    }
}
