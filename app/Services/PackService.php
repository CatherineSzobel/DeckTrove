<?php

namespace App\Services;

use App\Contracts\PackProvider;
use App\Exceptions\SeriesNotFoundException;

/**
 * Resolves the pack provider configured for a series in config/series.php.
 */
class PackService
{
    public function for(string $series): PackProvider
    {
        $provider = config("series.$series.pack.provider");

        if (! $provider) {
            throw new SeriesNotFoundException("Unsupported series: $series");
        }

        return app($provider);
    }
}
