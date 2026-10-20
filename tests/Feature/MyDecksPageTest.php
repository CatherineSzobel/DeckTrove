<?php

use App\Models\Deck;
use App\Models\DeckLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the stat chips count decks per series, public decks and likes received', function () {
    $user = User::factory()->create();
    $shared = Deck::factory()->public()->for($user)->create(['game' => 'yugioh']);
    Deck::factory()->for($user)->create(['game' => 'yugioh']);
    $private = Deck::factory()->for($user)->create(['game' => 'magic']);
    DeckLike::factory()->count(2)->for($shared)->create();
    DeckLike::factory()->count(5)->for($private)->create();

    $html = $this->actingAs($user)->get(route('decks.mine'))->assertOk()->getContent();

    expect($html)
        ->toMatch('/>3<\/b>\s*decks\s*</')
        ->toMatch('/>2<\/b>\s*Yu-Gi-Oh!\s*</')
        ->toMatch('/>1<\/b>\s*Magic: The Gathering\s*</')
        ->toMatch('/>1<\/b>\s*public\s*</')
        ->toMatch('/>2<\/b>\s*likes received\s*</');
});

test('each deck is an owner card with visibility, edit and delete', function () {
    $user = User::factory()->create(['username' => 'deckmaster']);
    $deck = Deck::factory()->for($user)->create(['name' => 'Secret Plan']);

    $this->actingAs($user)->get(route('decks.mine'))
        ->assertSee('Secret Plan')
        ->assertSee('Private')
        ->assertSee(route('decks.edit', $deck))
        ->assertSee('data-confirm-title="Delete this deck?"', false)
        ->assertSee(route('users.show', $user));
});

test('search and series filter the cards in place', function () {
    $user = User::factory()->create();
    Deck::factory()->for($user)->create(['name' => 'Dragon Rush', 'game' => 'yugioh']);

    $this->actingAs($user)->get(route('decks.mine'))
        ->assertSee('x-model="q"', false)
        ->assertSee('x-show="matches(0)"', false)
        ->assertSee('dragon rush', false)
        ->assertSee('No decks match')
        ->assertSee('Try another search or series.');
});

test('without decks, the page offers to build the first one', function () {
    $this->actingAs(User::factory()->create())->get(route('decks.mine'))
        ->assertSee('You don’t have any decks yet')
        ->assertSee('Create your first deck')
        ->assertDontSee('x-model="q"', false);
});

test('the controller hands My decks its stats and search data', function () {
    $user = User::factory()->create();
    $shared = Deck::factory()->public()->for($user)->create(['game' => 'yugioh', 'name' => 'Dragon Rush', 'description' => 'Big DRAGONS', 'created_at' => now()->subDay()]);
    Deck::factory()->for($user)->create(['game' => 'magic', 'name' => 'Burn', 'description' => null]);
    DeckLike::factory()->count(2)->for($shared)->create();

    $this->actingAs($user)->get(route('decks.mine'))
        ->assertViewHas('stats', ['decks' => 2, 'series' => ['yugioh' => 1, 'magic' => 1], 'public' => 1, 'likes_received' => 2])
        ->assertViewHas('searchData', [
            ['game' => 'magic', 'text' => 'burn'],
            ['game' => 'yugioh', 'text' => 'dragon rush big dragons'],
        ]);
});
