<?php

use App\Collection\CollectionService;
use App\Models\CollectionItem;
use App\Models\Deck;
use App\Models\User;
use App\Models\YugiohCard;
use App\Models\YugiohPrinting;
use App\Models\YugiohSet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician'])->create();
});

// The LOB pack page's set, holding Dark Magician (the page tests below need a pack to show).
function routesLobSet(): void
{
    $set = YugiohSet::factory()->create(['code' => 'LOB', 'name' => 'Legend of Blue Eyes White Dragon', 'slug' => 'legend-of-blue-eyes-white-dragon', 'card_count' => 126, 'released_at' => '2002-03-08', 'image_url' => null]);
    YugiohPrinting::create(['yugioh_card_id' => 46986414, 'yugioh_set_id' => $set->id, 'code' => 'LOB-EN005', 'rarity' => 'Ultra Rare']);
}

test('guests are sent to log in', function () {
    $this->post(route('collection.add'), ['game' => 'yugioh', 'card' => '46986414'])->assertRedirect(route('login'));
    $this->post('/yugioh/pack/LOB/collection/x')->assertRedirect(route('login'));
});

test('+ and − change the collection and go back to the page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->from('/yugioh/card/46986414')
        ->post(route('collection.add'), ['game' => 'yugioh', 'card' => '46986414'])
        ->assertRedirect('/yugioh/card/46986414#card-46986414')->assertSessionHas('success');
    $this->actingAs($user)->post(route('collection.add'), ['game' => 'yugioh', 'card' => '46986414', 'list' => 'wanted']);
    expect(ownedCount($user))->toBe(1)->and(wantedCount($user))->toBe(1); // auto-tidy runs on owned adds only

    $this->actingAs($user)->post(route('collection.remove'), ['game' => 'yugioh', 'card' => '46986414'])->assertSessionHasNoErrors();
    expect(ownedCount($user))->toBe(0)->and(wantedCount($user))->toBe(1);
});

test('bad input is refused in the collection bag', function (array $data) {
    $this->actingAs(User::factory()->create())->from('/x')
        ->post(route('collection.add'), $data)
        ->assertRedirect('/x')->assertSessionHasErrorsIn('collection');
    expect(CollectionItem::count())->toBe(0);
})->with([
    'unknown series' => [['game' => 'pokemon', 'card' => '1']],
    'unknown list' => [['game' => 'yugioh', 'card' => '46986414', 'list' => 'stolen']],
    'array card' => [['game' => 'yugioh', 'card' => ['46986414']]],
]);

test('a card the source doesn’t know is refused in the collection bag, back at its tile', function () {
    $this->actingAs(User::factory()->create())->from('/x')
        ->post(route('collection.add'), ['game' => 'yugioh', 'card' => '1'])
        ->assertRedirect('/x#card-1')->assertSessionHasErrorsIn('collection');
    expect(CollectionItem::count())->toBe(0);
});

test('with JSON, + and − answer with a message and the new count, and stay on the page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('collection.add'), ['game' => 'yugioh', 'card' => '46986414'])
        ->assertOk()->assertJson(['message' => 'Added to your collection.', 'count' => 1, 'removable' => true]);
    $this->actingAs($user)->postJson(route('collection.add'), ['game' => 'yugioh', 'card' => '46986414'])
        ->assertOk()->assertJson(['message' => 'Added to your collection.', 'count' => 2, 'removable' => true]);
    $this->actingAs($user)->postJson(route('collection.remove'), ['game' => 'yugioh', 'card' => '46986414'])
        ->assertOk()->assertJson(['message' => 'Removed from your collection.', 'count' => 1, 'removable' => true]);
    $this->actingAs($user)->postJson(route('collection.add'), ['game' => 'yugioh', 'card' => '46986414', 'list' => 'wanted'])
        ->assertOk()->assertJson(['message' => 'Added to your wishlist.', 'count' => 1, 'removable' => true]);
    expect(ownedCount($user))->toBe(1)->and(wantedCount($user))->toBe(1);
});

test('with JSON, a refused change is a 422 with the message', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', 'owned', '', 999);

    $this->actingAs($user)->postJson(route('collection.add'), ['game' => 'yugioh', 'card' => '46986414'])
        ->assertStatus(422)->assertJson(['message' => 'You can keep at most 999 copies of a card.']);
    $this->actingAs($user)->postJson(route('collection.add'), ['game' => 'yugioh', 'card' => '1'])
        ->assertStatus(422)->assertJson(['message' => 'The card 1 could not be found.']);
    $this->actingAs($user)->postJson(route('collection.add'), ['game' => 'pokemon', 'card' => '1'])
        ->assertStatus(422)->assertJsonValidationErrors('game');
});

test('a refused + shows its message on the card database and the pack page without JS', function (string $page) {
    routesLobSet();
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', 'owned', '', 999);

    $this->actingAs($user)->from($page)->followingRedirects()
        ->post(route('collection.add'), ['game' => 'yugioh', 'card' => '46986414'])
        ->assertSee('You can keep at most 999 copies of a card.');
})->with([
    'card database' => ['/yugioh/cards'],
    'pack page' => ['/yugioh/pack/LOB/legend-of-blue-eyes-white-dragon'],
]);

test('the pack page doesn’t deal its cards in again after a collection action', function () {
    routesLobSet();
    $user = User::factory()->create();

    // It comes back at the tile (#card-<id>), and the deal skips a grid holding the :target tile.
    $this->actingAs($user)->from('/yugioh/pack/LOB/legend-of-blue-eyes-white-dragon')
        ->post(route('collection.add'), ['game' => 'yugioh', 'card' => '46986414'])
        ->assertRedirect('/yugioh/pack/LOB/legend-of-blue-eyes-white-dragon#card-46986414');

    expect(file_get_contents(resource_path('css/app.css')))->toContain('[data-deal]:not(:has(> :target)) > :nth-child(-n+12)');
});

test('the card database and pack tiles are anchors the redirect lands on', function () {
    routesLobSet();
    $user = User::factory()->create();

    foreach (['images', 'full', 'list'] as $view) {
        $this->actingAs($user)->get(route('cards.index', ['yugioh', 'view' => $view]))->assertSee('id="card-46986414"', false)->assertSee('scroll-mt-24', false);
    }
    $this->actingAs($user)->get('/yugioh/pack/LOB/legend-of-blue-eyes-white-dragon')->assertSee('id="card-46986414"', false)->assertSee('scroll-mt-24', false);
});

test('a deck you can’t see can’t be added', function () {
    $private = Deck::factory()->create(['game' => 'yugioh']);

    $this->actingAs(User::factory()->create())->post(route('decks.collection', $private))->assertNotFound();
    $this->actingAs(User::factory()->create())->post(route('decks.wishlist', $private))->assertNotFound();
});

test('deck top-up and add missing say how many copies went in', function () {
    $user = User::factory()->create();
    $deck = collectionDeck('yugioh', ['main' => ['46986414' => 3]]);
    $deck->update(['is_public' => true]);

    $this->actingAs($user)->post(route('decks.collection', $deck))->assertSessionHas('success', 'Added 3 copies to your collection.');
    $this->actingAs($user)->post(route('decks.collection', $deck))->assertSessionHas('success', 'You already own everything in this deck.');
    $this->actingAs($user)->post(route('decks.wishlist', $deck))->assertSessionHas('success', 'Nothing is missing from this deck.');
});

test('add missing wishes for what the deck lacks', function () {
    $user = User::factory()->create();
    $deck = collectionDeck('yugioh', ['main' => ['46986414' => 2]]);
    $deck->update(['is_public' => true]);

    $this->actingAs($user)->post(route('decks.wishlist', $deck))->assertSessionHas('success', 'Added 2 copies to your wishlist.');
    expect(wantedCount($user))->toBe(2);
});

test('a copy past 999 is refused in the collection bag', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', 'owned', '', 999);

    $this->actingAs($user)->from('/x')->post(route('collection.add'), ['game' => 'yugioh', 'card' => '46986414'])
        ->assertRedirect('/x#card-46986414')->assertSessionHasErrorsIn('collection');
    expect(ownedCount($user))->toBe(999);
});

test('the pack route only exists for known series', function () {
    $this->actingAs(User::factory()->create())->post('/pokemon/pack/LOB/collection')->assertNotFound();
});

test('the pack route is the same with and without a product slug', function () {
    expect(route('packs.collection', ['series' => 'yugioh', 'setCode' => 'LOB'], false))->toBe('/yugioh/pack/LOB/collection')
        ->and(route('packs.collection', ['series' => 'yugioh', 'setCode' => 'LOB', 'slug' => 'x'], false))->toBe('/yugioh/pack/LOB/collection/x');

    $this->post('/yugioh/pack/LOB/collection')->assertRedirect(route('login'));
});
