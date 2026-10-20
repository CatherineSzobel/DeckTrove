<?php

use App\Models\Deck;
use App\Models\User;
use App\Models\YugiohCard;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('stats are the total and one tile per series', function () {
    $user = User::factory()->create();
    Deck::factory()->for($user)->count(2)->create(['game' => 'yugioh']);
    Deck::factory()->for($user)->create(['game' => 'magic']);
    Deck::factory()->create(['game' => 'magic']); // someone else's

    expect(app(DashboardService::class)->stats($user))->toBe([
        ['label' => 'Total decks', 'value' => 3, 'accent' => true],
        ['label' => 'Yu-Gi-Oh!', 'value' => 2, 'accent' => false],
        ['label' => 'Magic: The Gathering', 'value' => 1, 'accent' => false],
    ]);
});

test('stats are zero for a new account', function () {
    expect(collect(app(DashboardService::class)->stats(User::factory()->create()))->pluck('value')->all())->toBe([0, 0, 0]);
});

test('the series links are All and each series, with the current one marked', function () {
    $service = app(DashboardService::class);

    expect($service->seriesLinks(null))->toBe([
        ['label' => 'All', 'href' => route('dashboard'), 'current' => true],
        ['label' => 'Yu-Gi-Oh!', 'href' => route('dashboard', ['tcg' => 'yugioh']), 'current' => false],
        ['label' => 'Magic: The Gathering', 'href' => route('dashboard', ['tcg' => 'magic']), 'current' => false],
    ]);
    expect(collect($service->seriesLinks('magic'))->pluck('current')->all())->toBe([false, false, true]);
});

test('the empty title depends on whether the user has decks and on the filter', function () {
    $service = app(DashboardService::class);

    expect($service->emptyTitle(0, null))->toBe('You don’t have any decks yet')
        ->and($service->emptyTitle(0, 'magic'))->toBe('You don’t have any decks yet')
        ->and($service->emptyTitle(2, 'magic'))->toBe('No Magic: The Gathering decks yet');
});

test('quick links are each series’ cards, public decks and the user’s public profile', function () {
    $user = User::factory()->create(['username' => 'duelist']);

    expect(app(DashboardService::class)->quickLinks($user))->toBe([
        ['href' => route('cards.index', 'yugioh'), 'title' => 'Yu-Gi-Oh! cards'],
        ['href' => route('cards.index', 'magic'), 'title' => 'Magic: The Gathering cards'],
        ['href' => route('decks.index'), 'title' => 'Public decks'],
        ['href' => route('users.show', $user), 'title' => 'Your public profile'],
    ]);
});

test('random cards come from one series and are cached for ten minutes', function () {
    Http::fake();
    YugiohCard::factory()->count(3)->create();

    $cards = app(DashboardService::class)->randomCards('yugioh');

    expect($cards)->toHaveCount(3)
        ->and($cards->first()->link())->toContain('/yugioh/card/')
        ->and(Cache::has('dashboard-random-cards-yugioh-10'))->toBeTrue();
    Http::assertNothingSent();
});

test('the random cards switch links each series back to the section, keeping the deck filter', function () {
    $service = app(DashboardService::class);

    expect($service->randomSeriesLinks('magic', null))->toBe([
        ['label' => 'Yu-Gi-Oh!', 'href' => route('dashboard', ['cards' => 'yugioh']).'#discover', 'current' => false],
        ['label' => 'Magic: The Gathering', 'href' => route('dashboard', ['cards' => 'magic']).'#discover', 'current' => true],
    ]);
    expect($service->randomSeriesLinks('yugioh', 'magic')[1]['href'])
        ->toBe(route('dashboard', ['tcg' => 'magic', 'cards' => 'magic']).'#discover');
});
