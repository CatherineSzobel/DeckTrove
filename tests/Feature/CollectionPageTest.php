<?php

use App\Collection\CollectionService;
use App\Models\Card;
use App\Models\CollectionItem;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician', 'card_prices' => [['cardmarket_price' => '2.00']]])->create();
    YugiohCard::factory()->fromCard(['id' => 89631139, 'name' => 'Blue-Eyes White Dragon'])->create();
});

test('guests are sent to log in', function () {
    $this->get(route('collection'))->assertRedirect(route('login'));
    $this->get(route('collection.wishlist'))->assertRedirect(route('login'));
});

test('the collection lists each card once with its total copies', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', amount: 2);
    app(CollectionService::class)->add($user, 'yugioh', '46986414', printing: 'LOB-EN005');

    $this->actingAs($user)->get(route('collection'))->assertOk()
        ->assertSeeInOrder(['Unique cards', '1', 'Copies', '3'])
        // The counter shows the copies (a ×3 beside it would go stale once its + is pressed).
        ->assertSee('Dark Magician')->assertSee('data-count="3"', false)->assertDontSee('×3')
        ->assertDontSee('Blue-Eyes White Dragon')
        ->assertSee('Only you can see your collection.');
});

test('the wishlist shows its cards and what they cost', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', CollectionItem::WANTED, amount: 2);

    $this->actingAs($user)->get(route('collection.wishlist'))->assertOk()
        ->assertSeeInOrder(['To buy', 'about €4.00'])->assertSee('Dark Magician');
});

test('search filters by name and the series switch by game', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414');
    app(CollectionService::class)->add($user, 'yugioh', '89631139');

    $this->actingAs($user)->get(route('collection', ['search' => 'blue']))
        ->assertSee('Blue-Eyes White Dragon')->assertDontSee('Dark Magician');
    $this->actingAs($user)->get(route('collection', ['series' => 'magic']))
        ->assertDontSee('Dark Magician')->assertSee('No cards here yet');
});

test('each tile has plain remove and add forms', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414');

    $this->actingAs($user)->get(route('collection'))
        ->assertSee('Remove a copy of Dark Magician')->assertSee('Add a copy of Dark Magician')
        ->assertSee('x-data="collectionCounter"', false)
        ->assertSee('action="'.route('collection.add').'"', false);
});

test('the main nav has a Collection link for logged-in players only', function () {
    $this->get(route('cards.index', 'yugioh'))->assertDontSee('data-nav="collection"', false);
    $this->actingAs(User::factory()->create())->get(route('cards.index', 'yugioh'))->assertSee('data-nav="collection"', false);
});

test('each list has its own title and heading', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('collection'))
        ->assertSee('<title>Collection', false)->assertSee('Your collection');
    $this->actingAs($user)->get(route('collection.wishlist'))
        ->assertSee('<title>Wishlist', false)->assertSee('Your wishlist');
});

test('the wishlist still loads, with the price unavailable, when Scryfall is down', function () {
    Http::fake(['api.scryfall.com/*' => Http::response('', 503)]);
    $user = User::factory()->create();
    $card = Card::create(['game' => 'magic', 'external_id' => '0b3d6a6e-1111-4222-8333-444455556666', 'name' => 'Lightning Bolt']);
    $user->collectionItems()->create(['list' => 'wanted', 'game' => 'magic', 'card_key' => 'bolt', 'card_id' => $card->id, 'quantity' => 1]);

    $this->actingAs($user)->get(route('collection.wishlist', ['series' => 'magic']))->assertOk()
        ->assertSeeInOrder(['To buy', 'Price unavailable'])->assertSee('Lightning Bolt');
});

test('array query values fall back to the defaults instead of failing', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414');

    $this->actingAs($user)->get('/collection?series[]=magic&search[]=x')->assertOk()->assertSee('Dark Magician');
});

test('a card whose copies are all in printings has a disabled − and a link to choose one', function () {
    $user = User::factory()->create();
    app(CollectionService::class)->add($user, 'yugioh', '46986414', printing: 'LOB-EN005');

    $html = $this->actingAs($user)->get(route('collection'))->getContent();

    expect($html)->toContain('Choose a printing')
        ->and($html)->toContain(route('cards.show', ['yugioh', '46986414']).'#in-collection')
        ->and($html)->toContain('<button type="submit" disabled');

    app(CollectionService::class)->add($user, 'yugioh', '46986414');
    $this->actingAs($user)->get(route('collection'))->assertDontSee('Choose a printing');
});
