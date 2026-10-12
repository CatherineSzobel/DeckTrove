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
        ->get(route('decks'))
        ->assertViewHas('decks', fn ($decks) => (int) $decks->first()->likes_count === 3);
});

test('usernames on the deck page and the public list link to the profile', function () {
    $deck = Deck::factory()->public()->create(['game' => 'yugioh']);
    $profile = route('users.show', $deck->user);

    $this->get(route('decks.show', $deck))->assertSee('href="'.$profile.'"', false);
    $this->get(route('public-deck'))->assertSee('href="'.$profile.'"', false);
});
