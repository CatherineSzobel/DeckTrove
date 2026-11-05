<?php

use App\Collection\CollectionService;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician', 'card_sets' => [
    ['set_name' => 'Legend of Blue Eyes White Dragon', 'set_code' => 'LOB-EN005', 'set_rarity' => 'Ultra Rare'],
    ['set_name' => 'Starter Deck: Yugi', 'set_code' => 'SDY-006', 'set_rarity' => 'Common'],
]])->create());

/** A JSON POST to a collection route for Dark Magician, as the counter's JS sends it. */
function counterPost(User $user, string $route, array $body = [])
{
    return test()->actingAs($user)->postJson(route($route), ['game' => 'yugioh', 'card' => '46986414', ...$body]);
}

test('a counter gets back its new count and whether minus has a copy to take', function () {
    $user = User::factory()->create();

    counterPost($user, 'collection.add')->assertOk()->assertJson(['count' => 1, 'removable' => true])->assertJsonStructure(['message']);
    counterPost($user, 'collection.add', ['printing' => 'LOB-EN005'])->assertOk()->assertJson(['count' => 1, 'removable' => true]);
    counterPost($user, 'collection.remove')->assertOk()->assertJson(['count' => 1, 'removable' => false]);
    counterPost($user, 'collection.add', ['list' => 'wanted'])->assertOk()->assertJson(['count' => 1, 'removable' => true]);
});

test('the total owned is what a card-level counter shows', function () {
    $user = User::factory()->create();
    counterPost($user, 'collection.add', ['printing' => 'SDY-006']);

    counterPost($user, 'collection.add')->assertOk()->assertJson(['count' => 2, 'removable' => true]);
});

test('a refused change answers with its message', function () {
    counterPost(User::factory()->create(), 'collection.add', ['card' => '99999999'])->assertStatus(422)->assertJsonStructure(['message']);
});

test('the heart puts a card on the wishlist once', function () {
    $user = User::factory()->create();

    counterPost($user, 'collection.want', ['want' => 1])->assertOk()->assertJson(['wanted' => true]);
    counterPost($user, 'collection.want', ['want' => 1])->assertOk()->assertJson(['wanted' => true]);

    expect(wantedCount($user))->toBe(1);
});

test('the heart takes a card off the wishlist entirely, however many were wanted', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', 'wanted', '', 3);

    counterPost($user, 'collection.want', ['want' => 0])->assertOk()->assertJson(['wanted' => false]);

    expect(wantedCount($user))->toBe(0);
});

test('without JS the heart goes back to the collection row', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->from(route('cards.show', ['yugioh', '46986414']))
        ->post(route('collection.want'), ['game' => 'yugioh', 'card' => '46986414', 'want' => 1])
        ->assertRedirect(route('cards.show', ['yugioh', '46986414']).'#in-collection')
        ->assertSessionHas('success', 'Added to your wishlist.');
});

test('guests are sent to log in, and want must be a yes or no', function () {
    $this->post(route('collection.want'), ['game' => 'yugioh', 'card' => '46986414', 'want' => 1])->assertRedirect(route('login'));

    counterPost(User::factory()->create(), 'collection.want', ['want' => 'maybe'])->assertStatus(422);
});

/** Fakes Scryfall with two printings (A and B) of Lightning Bolt sharing one oracle id. */
function fakeTwoBolts(): array
{
    $a = '11111111-1111-4111-8111-111111111111';
    $b = '33333333-3333-4333-8333-333333333333';
    Http::fake(fn ($request) => Http::response([
        'id' => str_contains($request->url(), $a) ? $a : $b,
        'oracle_id' => '22222222-2222-4222-8222-222222222222', 'name' => 'Lightning Bolt', 'set' => 'm10', 'set_name' => 'Magic 2010', 'rarity' => 'common',
    ]));

    return [$a, $b];
}

test('a Magic counter counts every printing of the card, whichever one created the row', function () {
    [$a, $b] = fakeTwoBolts();
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'magic', $a);

    $this->actingAs($user)->postJson(route('collection.add'), ['game' => 'magic', 'card' => $b])
        ->assertOk()->assertJson(['count' => 2, 'removable' => true]);
});

test('the heart takes a Magic card off the wishlist from any of its printings', function () {
    [$a, $b] = fakeTwoBolts();
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'magic', $a, 'wanted');

    $this->actingAs($user)->postJson(route('collection.want'), ['game' => 'magic', 'card' => $b, 'want' => 0])->assertOk()->assertJson(['wanted' => false]);

    expect(wantedCount($user, '22222222-2222-4222-8222-222222222222'))->toBe(0);
});

test('a change also answers the card’s totals, so the page’s other counters can follow', function () {
    $user = User::factory()->create();

    counterPost($user, 'collection.add', ['printing' => 'LOB-EN005'])->assertOk()
        ->assertJson(['count' => 1, 'card' => ['owned' => 1, 'removable' => false, 'wanted' => 0, 'note' => '1 by printing']]);
});

test('without JS a card page counter comes back to where it was', function (string $anchor) {
    $user = User::factory()->create();
    $page = route('cards.show', ['yugioh', '46986414']);

    $this->actingAs($user)->from($page)->post(route('collection.add'), ['game' => 'yugioh', 'card' => '46986414', 'anchor' => $anchor])
        ->assertRedirect("$page#$anchor");
})->with(['in-collection', 'printings']);

test('an unknown anchor is refused', function () {
    $this->actingAs(User::factory()->create())->postJson(route('collection.add'), ['game' => 'yugioh', 'card' => '46986414', 'anchor' => 'javascript'])
        ->assertStatus(422);
});
