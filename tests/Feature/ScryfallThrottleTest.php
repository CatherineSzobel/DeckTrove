<?php

use App\Exceptions\ScryfallUnavailableException;
use App\Services\MagicService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Sleep;

beforeEach(function () {
    Http::fake([
        'api.scryfall.com/cards/search*' => Http::response(['data' => [], 'total_cards' => 0, 'has_more' => false]),
        'api.scryfall.com/catalog/card-types' => Http::response(['data' => ['Creature']]),
        'api.scryfall.com/sets' => Http::response(['data' => []]),
    ]);
    Sleep::fake();
});

test('search calls are counted in their own limiter, other calls in the general one', function () {
    $magic = app(MagicService::class);

    $magic->search(['search' => 'goblin']);
    $magic->filterOptions();

    expect(RateLimiter::attempts(MagicService::SEARCH_LIMITER))->toBe(1)
        ->and(RateLimiter::attempts(MagicService::LIMITER))->toBe(2);
});

test('a full search limiter waits for the next second, then sends', function () {
    foreach (range(1, MagicService::SEARCH_PER_SECOND) as $i) {
        RateLimiter::hit(MagicService::SEARCH_LIMITER, 1);
    }

    // The fake sleep doesn't free the limiter, so clear it the way a passing second would.
    Sleep::whenFakingSleep(fn () => RateLimiter::clear(MagicService::SEARCH_LIMITER));

    app(MagicService::class)->search(['search' => 'goblin']);

    Sleep::assertSleptTimes(1);
    Http::assertSentCount(1);
});

test('a limiter that stays full gives up after waiting at most a second, without calling Scryfall', function () {
    foreach (range(1, MagicService::SEARCH_PER_SECOND) as $i) {
        RateLimiter::hit(MagicService::SEARCH_LIMITER, 60);
    }

    expect(fn () => app(MagicService::class)->search(['search' => 'goblin']))
        ->toThrow(ScryfallUnavailableException::class);

    Http::assertNothingSent();
    Sleep::assertSlept(fn ($duration) => $duration->totalMilliseconds <= 1000, 1);
});
