<?php

use App\Collection\CollectionService;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician', 'card_prices' => [['cardmarket_price' => '2.00']]])->create();
});

test('guests see no collection block', function () {
    $deck = collectionDeck('yugioh', ['main' => ['46986414' => 3]]);
    $deck->update(['is_public' => true]);

    $this->get(route('decks.show', $deck))->assertOk()->assertDontSee('Your collection');
});

test('players see what they own, what is missing and what it costs', function () {
    $user = User::factory()->create();
    $deck = collectionDeck('yugioh', ['main' => ['46986414' => 3]]);
    $deck->update(['is_public' => true]);
    app(CollectionService::class)->add($user, 'yugioh', '46986414');

    $this->actingAs($user)->get(route('decks.show', $deck))->assertOk()
        ->assertSee('Your collection')->assertSee('You own 1 of 3')
        ->assertSee('Dark Magician')->assertSee('need 3 · own 1')
        ->assertSee('Missing cards: about €4.00')
        ->assertSee(route('decks.collection', $deck))->assertSee(route('decks.wishlist', $deck));
});

test('a complete deck says so and offers no buttons', function () {
    $user = User::factory()->create();
    $deck = collectionDeck('yugioh', ['main' => ['46986414' => 1]]);
    $deck->forceFill(['user_id' => $user->id])->save();
    app(CollectionService::class)->add($user, 'yugioh', '46986414');

    $this->actingAs($user)->get(route('decks.show', $deck))
        ->assertSee('You own every card in this deck.')
        ->assertDontSee(route('decks.collection', $deck));
});

test('the cost line is left out when nothing missing has a price', function () {
    $user = User::factory()->create();
    $deck = collectionDeck('yugioh', ['main' => ['99999999' => 1]]);
    $deck->update(['is_public' => true]);

    $this->actingAs($user)->get(route('decks.show', $deck))->assertOk()
        ->assertSee('You own 0 of 1')->assertDontSee('Missing cards:');
});

test('a top-up error shows in the collection block', function () {
    $user = User::factory()->create();
    $deck = collectionDeck('yugioh', ['main' => ['46986414' => 1]]);
    $deck->update(['is_public' => true]);

    $this->actingAs($user)
        ->withSession(['errors' => (new ViewErrorBag)->put('collection', new MessageBag(['card' => 'You can keep at most 999 copies of a card.']))])
        ->get(route('decks.show', $deck))
        ->assertSee('You can keep at most 999 copies of a card.');
});
