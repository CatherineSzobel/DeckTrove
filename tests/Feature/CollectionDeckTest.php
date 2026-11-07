<?php

use App\Collection\CollectionService;
use App\Models\CollectionItem;
use App\Models\User;
use App\Models\YugiohCard;
use App\Models\YugiohPrinting;
use App\Models\YugiohSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician', 'card_prices' => [['cardmarket_price' => '2.00']]])->create();
    YugiohCard::factory()->fromCard(['id' => 89631139, 'name' => 'Blue-Eyes White Dragon', 'card_prices' => [['cardmarket_price' => '0.50']]])->create();
});

test('a deck counts what you own against what it needs, across zones', function () {
    $user = User::factory()->create();
    $deck = collectionDeck('yugioh', ['main' => ['46986414' => 3, '89631139' => 1], 'side' => ['46986414' => 1]]);
    app(CollectionService::class)->add($user, 'yugioh', '46986414', amount: 2);

    $needs = app(CollectionService::class)->deckNeeds($user, $deck);

    expect($needs->needed)->toBe(5)->and($needs->owned)->toBe(2)->and($needs->percent())->toBe(40)
        ->and($needs->missingCount())->toBe(3)->and($needs->isComplete())->toBeFalse()
        ->and($needs->missing)->toBe([
            ['external_id' => '46986414', 'name' => 'Dark Magician', 'need' => 4, 'own' => 2],
            ['external_id' => '89631139', 'name' => 'Blue-Eyes White Dragon', 'need' => 1, 'own' => 0],
        ])
        ->and($needs->missingCost)->toBe(4.5);
});

test('Magic printings of one card count as one card', function () {
    Http::fake(['api.scryfall.com/cards/collection' => Http::response(['data' => [
        ['id' => '11111111-1111-4111-8111-111111111111', 'oracle_id' => 'bolt', 'name' => 'Lightning Bolt', 'prices' => ['eur' => '1.00']],
        ['id' => '22222222-2222-4222-8222-222222222222', 'oracle_id' => 'bolt', 'name' => 'Lightning Bolt', 'prices' => ['eur' => '3.00']],
    ], 'not_found' => []]),
        // Adding one card looks it up on its own.
        'api.scryfall.com/cards/22222222-2222-4222-8222-222222222222' => Http::response(
            ['id' => '22222222-2222-4222-8222-222222222222', 'oracle_id' => 'bolt', 'name' => 'Lightning Bolt', 'prices' => ['eur' => '3.00']]),
    ]);
    $user = User::factory()->create();
    $deck = collectionDeck('magic', ['main' => ['11111111-1111-4111-8111-111111111111' => 2, '22222222-2222-4222-8222-222222222222' => 2]]);
    app(CollectionService::class)->add($user, 'magic', '22222222-2222-4222-8222-222222222222', amount: 4);

    $needs = app(CollectionService::class)->deckNeeds($user, $deck);

    expect($needs->needed)->toBe(4)->and($needs->owned)->toBe(4)->and($needs->isComplete())->toBeTrue()
        ->and($needs->missing)->toBe([])->and($needs->missingCost)->toBeNull();
});

test('a card the source dropped counts by its id and is left out of the cost', function () {
    $user = User::factory()->create();
    $deck = collectionDeck('yugioh', ['main' => ['11111111' => 2]]);

    $needs = app(CollectionService::class)->deckNeeds($user, $deck);

    expect($needs->missing)->toBe([['external_id' => '11111111', 'name' => 'Card 11111111', 'need' => 2, 'own' => 0]])
        ->and($needs->missingCost)->toBeNull()->and($needs->unpriced)->toBe(2);
});

test('topping up a deck adds only what is missing, once', function () {
    $user = User::factory()->create();
    $deck = collectionDeck('yugioh', ['main' => ['46986414' => 3]]);
    $service = app(CollectionService::class);
    $service->add($user, 'yugioh', '46986414');

    expect($service->topUpDeck($user, $deck))->toBe(2)
        ->and($service->topUpDeck($user, $deck))->toBe(0)
        ->and(ownedCount($user))->toBe(3);
});

test('adding a deck’s missing cards to the wishlist fills it up to what is missing, once', function () {
    $user = User::factory()->create();
    $deck = collectionDeck('yugioh', ['main' => ['46986414' => 3]]);
    $service = app(CollectionService::class);
    $service->add($user, 'yugioh', '46986414');
    $service->add($user, 'yugioh', '46986414', CollectionItem::WANTED);

    expect($service->wishMissing($user, $deck))->toBe(1)
        ->and($service->wishMissing($user, $deck))->toBe(0)
        ->and(wantedCount($user))->toBe(2);
});

test('the wishlist cost adds up wanted copies and skips unpriced cards', function () {
    $user = User::factory()->create();
    $service = app(CollectionService::class);
    $service->add($user, 'yugioh', '46986414', CollectionItem::WANTED, amount: 2);
    $service->add($user, 'yugioh', '89631139', CollectionItem::WANTED);
    YugiohCard::factory()->fromCard(['id' => 22222222, 'name' => 'Unpriced', 'card_prices' => []])->create();
    $service->add($user, 'yugioh', '22222222', CollectionItem::WANTED);

    expect($service->wishlistCost($user))->toBe(['total' => 4.5, 'unpriced' => 1]);
});

test('adding a set records each card with that set’s printing', function () {
    $user = User::factory()->create();
    $set = YugiohSet::factory()->create(['code' => 'LOB', 'name' => 'Legend of Blue Eyes White Dragon', 'slug' => 'legend-of-blue-eyes-white-dragon']);
    YugiohPrinting::create(['yugioh_card_id' => 46986414, 'yugioh_set_id' => $set->id, 'code' => 'LOB-EN005', 'rarity' => 'Ultra Rare']);

    $added = app(CollectionService::class)->addSet($user, 'yugioh', 'LOB', null);

    expect($added)->toBe(1)
        ->and($user->collectionItems()->sole())->printing->toBe('LOB-EN005')->quantity->toBe(1);
});

test('adding a set again adds a copy, takes it off the wishlist, and skips a printing at the maximum', function () {
    $user = User::factory()->create();
    $set = YugiohSet::factory()->create(['code' => 'LOB', 'name' => 'Legend of Blue Eyes White Dragon', 'slug' => 'legend-of-blue-eyes-white-dragon']);
    YugiohPrinting::create(['yugioh_card_id' => 46986414, 'yugioh_set_id' => $set->id, 'code' => 'LOB-EN005', 'rarity' => 'Ultra Rare']);
    $service = app(CollectionService::class);
    $service->add($user, 'yugioh', '46986414', CollectionItem::WANTED);

    expect($service->addSet($user, 'yugioh', 'LOB', null))->toBe(1)
        ->and($service->addSet($user, 'yugioh', 'LOB', null))->toBe(1)
        ->and($user->collectionItems()->owned()->sole()->quantity)->toBe(2)
        ->and($user->collectionItems()->wanted()->exists())->toBeFalse();

    $user->collectionItems()->owned()->update(['quantity' => CollectionService::MAX]);

    expect($service->addSet($user, 'yugioh', 'LOB', null))->toBe(0)
        ->and($user->collectionItems()->owned()->sole()->quantity)->toBe(CollectionService::MAX);
});

test('topping up a Magic deck looks the cards up once, not once per card', function () {
    $ids = ['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb', 'cccccccc-cccc-4ccc-8ccc-cccccccccccc'];
    Http::fake(['api.scryfall.com/cards/collection' => Http::response(['data' => collect($ids)->map(fn ($id) => [
        'id' => $id, 'oracle_id' => "o-$id", 'name' => "Card $id", 'prices' => ['eur' => '1.00'],
    ])->all(), 'not_found' => []])]);
    $user = User::factory()->create();
    $deck = collectionDeck('magic', ['main' => array_fill_keys($ids, 2)]);

    expect(app(CollectionService::class)->topUpDeck($user, $deck))->toBe(6);

    Http::assertSentCount(1);
});

test('cards the source dropped are skipped when topping up or wishing, and the rest still go in', function () {
    $user = User::factory()->create();
    $deck = collectionDeck('yugioh', ['main' => ['46986414' => 2, '11111111' => 3]]);
    $service = app(CollectionService::class);

    expect($service->wishMissing($user, $deck))->toBe(2)
        ->and($service->topUpDeck($user, $deck))->toBe(2)
        ->and(ownedCount($user))->toBe(2)->and(ownedCount($user, '11111111'))->toBe(0);
});

test('a deck action that overflows part-way changes nothing', function () {
    $user = User::factory()->create();
    $deck = collectionDeck('yugioh', ['main' => ['46986414' => 2, '89631139' => 1000]]);

    expect(fn () => app(CollectionService::class)->topUpDeck($user, $deck))->toThrow(ValidationException::class);
    expect(ownedCount($user))->toBe(0)->and(ownedCount($user, '89631139'))->toBe(0);
});
