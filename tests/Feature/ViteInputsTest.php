<?php

// Tests run without Vite, so these check the files rather than the pages' script tags.

test('every script and style in the Vite config exists', function () {
    preg_match_all("/'(resources\/[^']+)'/", file_get_contents(base_path('vite.config.js')), $matches);

    $missing = collect($matches[1])->reject(fn (string $path) => file_exists(base_path($path)))->values()->all();

    expect($matches[1])->not->toBeEmpty()
        ->and($missing)->toBe([]);
});

test('the old deck list filter scripts are gone', function () {
    expect(file_exists(resource_path('js/deckfilter.js')))->toBeFalse()
        ->and(file_exists(resource_path('js/mydecks.js')))->toBeFalse()
        ->and(file_get_contents(base_path('vite.config.js')))->not->toContain('deckfilter.js')->not->toContain('mydecks.js');
});

test('the old dashboard carousel and quick link component are gone', function () {
    expect(file_exists(resource_path('js/moving-carousel.js')))->toBeFalse()
        ->and(file_exists(resource_path('css/dashboard.css')))->toBeFalse()
        ->and(file_exists(resource_path('views/components/dashboard-quick-link.blade.php')))->toBeFalse()
        ->and(file_get_contents(base_path('vite.config.js')))->not->toContain('moving-carousel.js')->not->toContain('dashboard.css');
});
