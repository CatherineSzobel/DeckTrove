<?php

use App\Cards\CardCache;
use App\Models\Card;
use App\Models\CollectionItem;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user has no public collection or wishlist by default', function () {
    $user = User::factory()->create();

    expect($user->collection_public)->toBeFalse()->and($user->wishlist_public)->toBeFalse()
        ->and($user->fresh()->collection_public)->toBeFalse();
});

test('a card has one row per list and printing', function () {
    $item = CollectionItem::factory()->create(['printing' => '']);

    CollectionItem::factory()->for($item->user)->create(['card_key' => $item->card_key, 'card_id' => $item->card_id, 'printing' => 'LOB-EN005']);
    CollectionItem::factory()->for($item->user)->create(['card_key' => $item->card_key, 'card_id' => $item->card_id, 'list' => CollectionItem::WANTED]);

    expect(fn () => CollectionItem::factory()->for($item->user)->create(['card_key' => $item->card_key, 'card_id' => $item->card_id, 'printing' => '']))
        ->toThrow(QueryException::class);
});

test('a collection goes with its user', function () {
    $item = CollectionItem::factory()->create();

    $item->user->delete();

    expect(CollectionItem::count())->toBe(0);
});

test('the card cache stores names and images once and returns their ids', function () {
    $ids = CardCache::store('yugioh', collect(['46986414' => ['id' => 46986414, 'name' => 'Dark Magician']]));
    $again = CardCache::store('yugioh', collect(['46986414' => ['id' => 46986414, 'name' => 'Dark Magician']]));

    expect(Card::count())->toBe(1)
        ->and($ids->all())->toBe(['46986414' => Card::sole()->id])
        ->and($again->all())->toBe($ids->all());
});
