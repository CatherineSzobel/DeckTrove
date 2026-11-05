<?php

use App\View\Components\ShowcaseShots;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the showcase shows the same four screenshots in turn order on every page that has it', function (string $route) {
    preg_match_all('/<img [^>]*alt="([^"]+)" data-shot="(\d)" class="showcase-shot">/', $this->get(route($route))->getContent(), $shots);

    expect($shots[1])->toBe(['DeckTrove card database', 'DeckTrove deck builder', 'DeckTrove deck page', 'DeckTrove pack page'])
        ->and($shots[2])->toBe(['1', '2', '3', '4']);
})->with(['login', 'register', 'index']);

test('every showcase screenshot exists and is 16:10', function () {
    expect(ShowcaseShots::SHOTS)->toHaveCount(4);

    foreach (ShowcaseShots::SHOTS as $shot) {
        expect(file_exists(base_path($shot['path'])))->toBeTrue();

        [$width, $height] = getimagesize(base_path($shot['path']));

        expect($width / $height)->toEqualWithDelta(1.6, 0.03);
    }
});

test('the showcase has a pause toggle and never adds a sideways scrollbar', function (string $route) {
    expect($this->get(route($route))->getContent())
        ->toMatch('/<div class="showcase [^"]*">\s*<div class="relative aspect-\[5\/3\.6\]">/')
        ->toContain('<label class="showcase-pause">')
        ->toContain('<input type="checkbox" class="sr-only">')
        ->toContain('Pause screenshots')
        ->toContain('overflow-x-clip');
})->with(['login', 'register', 'index']);

test('the showcase loop only runs without reduced motion and can be paused', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    preg_match('/@keyframes showcase-cycle\s*\{(.*?)\n\}/s', $css, $keyframes);
    // The showcase's own reduced-motion block (app.css has others, e.g. the pack deal-in).
    preg_match('/@media \(prefers-reduced-motion: no-preference\)\s*\{(\s*animation: showcase-cycle.*?)\n        \}/s', $css, $motion);

    expect($keyframes[1] ?? '')->toContain('var(--back)')->toContain('var(--front)')->toContain('var(--gone)')
        ->and($motion[1] ?? '')->toContain('animation: showcase-cycle 20s ease-in-out infinite')
        ->toContain('animation-delay: -6s')->toContain('animation-delay: -1s')
        ->toContain('animation-delay: -16s')->toContain('animation-delay: -11s')
        ->and($css)->toContain('.showcase:has(.showcase-pause :checked) .showcase-shot')
        ->toContain('animation-play-state: paused')
        ->toContain('&:has(:focus-visible)');
});
