<?php

use App\Collection\CollectionService;
use App\Models\Card;
use App\Models\CollectionItem;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician'])->create();
});

function collectionUser(): User
{
    return User::factory()->create();
}

test('+ adds unspecified copies, − takes them away, and an empty row goes', function () {
    $user = collectionUser();
    $service = app(CollectionService::class);

    $service->add($user, 'yugioh', '46986414');
    $service->add($user, 'yugioh', '46986414');
    expect(ownedCount($user))->toBe(2)->and($user->collectionItems()->sole()->printing)->toBe('');

    $service->remove($user, 'yugioh', '46986414');
    $service->remove($user, 'yugioh', '46986414');
    expect($user->collectionItems()->count())->toBe(0);
});

test('copies can be recorded per printing, and they count towards the card', function () {
    $user = collectionUser();
    $service = app(CollectionService::class);

    $service->add($user, 'yugioh', '46986414');
    $service->add($user, 'yugioh', '46986414', printing: 'LOB-EN005', amount: 2);

    expect(ownedCount($user))->toBe(3)
        ->and($service->counts($user, 'yugioh', '46986414'))->toBe(['owned' => 3, 'wanted' => 0, 'printings' => ['' => 1, 'LOB-EN005' => 2]]);
});

test('− on a card whose copies are all assigned to printings asks which printing', function () {
    $user = collectionUser();
    $service = app(CollectionService::class);
    $service->add($user, 'yugioh', '46986414', printing: 'LOB-EN005');

    expect(fn () => $service->remove($user, 'yugioh', '46986414'))
        ->toThrow(ValidationException::class, 'Choose which printing to remove.');
    expect(ownedCount($user))->toBe(1);

    $service->remove($user, 'yugioh', '46986414', printing: 'LOB-EN005');
    expect(ownedCount($user))->toBe(0);
});

test('adding owned copies takes them off the wishlist, never the other way round', function () {
    $user = collectionUser();
    $service = app(CollectionService::class);

    $service->add($user, 'yugioh', '46986414', CollectionItem::WANTED, amount: 2);
    $service->add($user, 'yugioh', '46986414');
    expect(wantedCount($user))->toBe(1)->and(ownedCount($user))->toBe(1);

    $service->add($user, 'yugioh', '46986414', amount: 3);
    expect(wantedCount($user))->toBe(0)->and($user->collectionItems()->wanted()->exists())->toBeFalse();

    $service->remove($user, 'yugioh', '46986414');
    expect(wantedCount($user))->toBe(0);
});

test('wishlist rows never record a printing', function () {
    $user = collectionUser();

    app(CollectionService::class)->add($user, 'yugioh', '46986414', CollectionItem::WANTED, 'LOB-EN005');

    expect($user->collectionItems()->wanted()->sole()->printing)->toBe('');
});

test('a row holds at most 999 copies', function () {
    $user = collectionUser();
    $service = app(CollectionService::class);
    $service->add($user, 'yugioh', '46986414', amount: 999);

    expect(fn () => $service->add($user, 'yugioh', '46986414'))->toThrow(ValidationException::class, 'at most 999');
    expect(ownedCount($user))->toBe(999);
});

test('unknown cards are refused', function () {
    expect(fn () => app(CollectionService::class)->add(collectionUser(), 'yugioh', '1'))
        ->toThrow(ValidationException::class, 'could not be found');
    expect(CollectionItem::count())->toBe(0);
});

test('a card the source dropped can still be removed, without asking the source', function () {
    $user = collectionUser();
    $service = app(CollectionService::class);
    $service->add($user, 'yugioh', '46986414', amount: 2);
    YugiohCard::query()->delete();

    $service->remove($user, 'yugioh', '46986414');

    expect(ownedCount($user))->toBe(1);
});

test('removing never calls the Scryfall API for a card you hold', function () {
    $user = collectionUser();
    $card = Card::create(['game' => 'magic', 'external_id' => '0b3d6a6e-1111-4222-8333-444455556666', 'name' => 'Gone Card']);
    $user->collectionItems()->create(['game' => 'magic', 'card_key' => 'oracle-gone', 'card_id' => $card->id, 'quantity' => 1]);
    Http::fake();

    app(CollectionService::class)->remove($user, 'magic', '0b3d6a6e-1111-4222-8333-444455556666');

    expect($user->collectionItems()->count())->toBe(0);
    Http::assertNothingSent();
});

test('removing a card you hold no copy of and the source doesn’t know is a card error', function () {
    expect(fn () => app(CollectionService::class)->remove(collectionUser(), 'yugioh', '1'))
        ->toThrow(ValidationException::class, 'could not be found');
});

test('a double + that loses the race for the new row adds to the winner’s row', function () {
    $user = collectionUser();
    $service = app(CollectionService::class);

    // The row is created by "someone else" between this request's read and its insert.
    CollectionItem::creating(function (CollectionItem $item) use ($user) {
        static $done = false;
        if (! $done) {
            $done = true;
            DB::table('collection_items')->insert([
                'user_id' => $user->id, 'list' => 'owned', 'game' => 'yugioh', 'card_key' => '46986414', 'card_id' => $item->card_id,
                'printing' => '', 'quantity' => 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    });

    $service->add($user, 'yugioh', '46986414');

    expect(ownedCount($user))->toBe(2)->and($user->collectionItems()->count())->toBe(1);
});
