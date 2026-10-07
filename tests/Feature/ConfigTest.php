<?php

use Illuminate\Support\Arr;

// Deploys run `php artisan config:cache`, which fails if any config value can't be exported (e.g. a closure).
test('the configuration can be cached', function () {
    $nonExportable = collect(Arr::dot(config()->all()))
        ->filter(fn ($value) => is_object($value))
        ->keys();

    expect($nonExportable)->toBeEmpty();
});
