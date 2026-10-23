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
}
