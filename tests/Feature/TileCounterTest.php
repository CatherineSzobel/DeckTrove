<?php

use App\Collection\CollectionService;
use App\Models\User;
use App\Models\YugiohCard;
use App\Models\YugiohPrinting;
use App\Models\YugiohSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $set = YugiohSet::factory()->create(['code' => 'LOB', 'name' => 'Legend of Blue Eyes White Dragon', 'slug' => 'legend-of-blue-eyes-white-dragon',
        'card_count' => 126, 'released_at' => '2002-03-08', 'image_url' => null]);
    foreach ([[46986414, 'Dark Magician', 'LOB-EN005'], [89631139, 'Blue-Eyes White Dragon', 'LOB-EN001']] as [$id, $name, $code]) {
        YugiohCard::factory()->fromCard(['id' => $id, 'name' => $name, 'card_sets' => [['set_name' => $set->name, 'set_code' => $code, 'set_rarity' => 'Ultra Rare']]])->create();
        YugiohPrinting::create(['yugioh_card_id' => $id, 'yugioh_set_id' => $set->id, 'code' => $code, 'rarity' => 'Ultra Rare']);
    }
});

test('every card database tile has a counter with its owned count, in every view', function (string $view) {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', 'owned', '', 2);

    $html = $this->actingAs($user)->get(route('cards.index', ['yugioh', 'view' => $view]))->assertOk()->getContent();

    expect(substr_count($html, 'x-data="collectionCounter"'))->toBe(2)
        ->and($html)->toContain('aria-label="Add a copy of Dark Magician"')
        ->toContain('data-count="2"')
        ->not->toContain('collectionAdd')
        ->not->toMatch('/<a [^>]*>(?:(?!<\/a>).)*<form/s');
})->with(['images', 'full', 'list']);

test('load more results get counters too, without a query per tile', function () {
    $user = User::factory()->create();

    DB::enableQueryLog();
    $html = $this->actingAs($user)->get(route('cards.index', ['yugioh', 'view' => 'images']), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->getContent();
    $collectionQueries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'collection_items'))->count();

    expect(substr_count($html, 'x-data="collectionCounter"'))->toBe(2)
        ->and($collectionQueries)->toBe(1);
});

test('the pack page has a counter under every tile, and a minus that is off when the copies are in printings', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', 'owned', 'LOB-EN005');

    $html = $this->actingAs($user)->get(route('packs.show', ['yugioh', 'LOB', 'legend-of-blue-eyes-white-dragon']))->assertOk()->getContent();

    expect(substr_count($html, 'x-data="collectionCounter"'))->toBe(2)
        ->and($html)->toContain('Remove it from a printing on the card page.')
        ->toContain('data-deal');
});

test('guests see no counters on tiles and no collection query runs', function () {
    DB::enableQueryLog();
    $html = $this->get(route('packs.show', ['yugioh', 'LOB', 'legend-of-blue-eyes-white-dragon']))->assertOk()->getContent();

    expect($html)->not->toContain('collectionCounter')
        ->and(collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'collection_items'))->count())->toBe(0);
});
