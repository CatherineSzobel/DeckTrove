<?php

use App\Collection\CollectionService;
use App\Models\CollectionItem;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician'])->create();
    YugiohCard::factory()->fromCard(['id' => 38033121, 'name' => 'Dark Magician Girl'])->create();
    YugiohCard::factory()->fromCard(['id' => 89631139, 'name' => 'Blue-Eyes White Dragon'])->create();
});

test('without a search there is no strip', function () {
    $this->actingAs(User::factory()->create())->get(route('collection'))
        ->assertOk()->assertDontSee('Not in your collection');
});

test('a search shows the card database’s matches you don’t have, each with a counter', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414');

    $html = $this->actingAs($user)->get(route('collection', ['search' => 'dark magician']))->assertOk()->getContent();

    $strip = str($html)->after('Not in your collection')->toString();
    expect($html)->toContain('Not in your collection')
        ->and($strip)->toContain('Dark Magician Girl')
        ->toContain('id="card-38033121" data-suggestion')
        ->toContain('Add a copy of Dark Magician Girl')
        ->toContain('name="list" value="owned"')
        ->not->toContain('Add a copy of Dark Magician"')
        ->not->toContain('Blue-Eyes');
});

test('the strip still shows when none of your cards match', function () {
    $this->actingAs(User::factory()->create())->get(route('collection', ['search' => 'blue-eyes']))
        ->assertOk()->assertSee('No cards match')->assertSee('Not in your collection')->assertSee('Add a copy of Blue-Eyes White Dragon');
});

test('the strip shows at most eight cards', function () {
    foreach (range(1, 10) as $i) {
        YugiohCard::factory()->fromCard(['id' => 1000 + $i, 'name' => "Kuriboh $i"])->create();
    }

    $html = $this->actingAs(User::factory()->create())->get(route('collection', ['search' => 'kuriboh']))->getContent();

    expect(substr_count($html, 'data-suggestion'))->toBe(8);
});

test('on the wishlist the strip offers cards you don’t want yet, for the wishlist', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', CollectionItem::WANTED);

    $html = $this->actingAs($user)->get(route('collection.wishlist', ['search' => 'dark magician']))->getContent();

    $strip = str($html)->after('Not on your wishlist')->toString();
    expect($html)->toContain('Not on your wishlist')
        ->and($strip)->toContain('Add a copy of Dark Magician Girl')->toContain('name="list" value="wanted"')
        ->not->toContain('id="card-46986414" data-suggestion');
});

test('a Scryfall outage says the card search is unavailable and the page still loads', function () {
    Http::fake(['api.scryfall.com/*' => Http::response('', 503)]);

    $this->actingAs(User::factory()->create())->get(route('collection', ['series' => 'magic', 'search' => 'bolt']))
        ->assertOk()->assertSee('The card search isn’t available right now.');
});

test('a public collection gets no strip', function () {
    $owner = User::factory()->create(['collection_public' => true]);
    app(CollectionService::class)->add($owner, 'yugioh', '46986414');

    $this->get(route('users.collection', [$owner, 'search' => 'dark']))->assertOk()->assertDontSee('Not in your collection');
});

test('the collection pages share the card search’s rate limit', function () {
    $route = app('router')->getRoutes()->getByName('collection');

    expect($route->gatherMiddleware())->toContain('throttle:card-search');
});
