<?php

namespace App\Support;

/** Prices as the site shows them. */
final class Money
{
    /** A price with the currency symbol, e.g. "€0.10". */
    public static function format(float $value): string
    {
        return config('app.currency.symbol').number_format($value, 2);
    }
}
