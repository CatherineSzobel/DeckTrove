<?php

use App\Models\Deck;
use App\Models\DeckLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the deck page shows another user their like and the count', function () {
    $deck = Deck::factory()->public()->create(['game' => 'yugioh']);
    $viewer = User::factory()->create();
    DeckLike::factory()->for($deck)->for($viewer)->create();
    DeckLike::factory()->for($deck)->create();

    $this->actingAs($viewer)
        ->get(route('decks.show', $deck))
        ->assertOk()
        ->assertViewHas('deck', fn (Deck $shown) => (int) $shown->likes_count === 2)
        ->assertViewHas('liked', true)
        ->assertViewHas('canLike', true);
});

test('owners and guests cannot like, and have not liked', function () {
    $deck = Deck::factory()->public()->create(['game' => 'yugioh']);
    DeckLike::factory()->for($deck)->create();

    $this->get(route('decks.show', $deck))
        ->assertViewHas('deck', fn (Deck $shown) => (int) $shown->likes_count === 1)
        ->assertViewHas('liked', false)
        ->assertViewHas('canLike', false);

    $this->actingAs($deck->user)
        ->get(route('decks.show', $deck))
        ->assertViewHas('liked', false)
        ->assertViewHas('canLike', false);
});

test('My decks has like counts', function () {
    $deck = Deck::factory()->public()->create();
    DeckLike::factory()->count(3)->for($deck)->create();

    $this->actingAs($deck->user)
        ->get(route('decks.mine'))
        ->assertViewHas('decks', fn ($decks) => (int) $decks->first()->likes_count === 3);
});

test('usernames on the deck page and the public list link to the profile', function () {
    $deck = Deck::factory()->public()->create(['game' => 'yugioh']);
    $profile = route('users.show', $deck->user);

    $this->get(route('decks.show', $deck))->assertSee('href="'.$profile.'"', false);
    $this->get(route('decks.index'))->assertSee('href="'.$profile.'"', false);
});

test('the deck page shows a like button above Copy for other players', function () {
    $deck = Deck::factory()->public()->create(['game' => 'yugioh']);
    DeckLike::factory()->count(2)->for($deck)->create();

    $html = $this->actingAs(User::factory()->create())->get(route('decks.show', $deck))->getContent();

    expect($html)
        ->toContain('action="'.route('decks.like', $deck).'"')
        ->toContain('aria-pressed="false"')
        ->and(strpos($html, route('decks.like', $deck)))->toBeLessThan(strpos($html, route('decks.copy', $deck)));
});

test('owners see how many players liked their deck, guests are asked to log in', function () {
    $deck = Deck::factory()->public()->create(['game' => 'yugioh']);
    DeckLike::factory()->count(2)->for($deck)->create();

    $this->actingAs($deck->user)->get(route('decks.show', $deck))
        ->assertSee('2 players liked this deck')
        ->assertDontSee('action="'.route('decks.like', $deck).'"', false);

    auth()->logout();

    $this->get(route('decks.show', $deck))
        ->assertSee('Log in to like')
        ->assertSee('href="'.route('login').'"', false);
});
