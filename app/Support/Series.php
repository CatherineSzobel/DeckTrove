<?php

namespace App\Support;

/**
 * The card series DeckTrove supports (config/series.php), for picking one from untrusted input: a query value, the
 * session's remembered series. Anything that isn't a configured key, arrays included, counts as unknown.
 */
final class Series
{
    public static function isKnown(mixed $key): bool
    {
        return is_string($key) && array_key_exists($key, config('series'));
    }

    /** The first configured series: what a page shows when none was picked. */
    public static function default(): string
    {
        return array_key_first(config('series'));
    }

    public static function orDefault(mixed $key): string
    {
        return self::isKnown($key) ? $key : self::default();
    }

    public static function orNull(mixed $key): ?string
    {
        return self::isKnown($key) ? $key : null;
    }

    /**
     * One link per series for a series switch (x-segment-links), after an "All" link (series null) when $all names it.
     *
     * @param  callable(?string): string  $href  the link for a series, or for "All" with null
     * @return list<array{key: string, label: string, href: string, current: bool}>
     */
    public static function links(callable $href, ?string $current, ?string $all = null): array
    {
        $series = array_map(fn (array $config) => $config['label'], config('series'));
        $choices = $all === null ? $series : ['all' => $all, ...$series];

        return collect($choices)->map(fn (string $label, string $key) => [
            'key' => $key,
            'label' => $label,
            'href' => $href($key === 'all' ? null : $key),
            'current' => $key === 'all' ? $current === null : $key === $current,
        ])->values()->all();
    }
}
