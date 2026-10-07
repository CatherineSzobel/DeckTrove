<?php

namespace App\Services;

use App\Contracts\CardProvider;
use App\Exceptions\SeriesNotFoundException;

/**
 * Resolves the card provider configured for a series in config/series.php.
 */
class CardService
{
    public function for(string $series): CardProvider
    {
        $provider = config("series.$series.provider");

        if (! $provider) {
            throw new SeriesNotFoundException("Unsupported series: $series");
        }

        return app($provider);
    }
}
