<?php

use App\Models\Deck;
use App\Models\DeckLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the controls show the filters that were applied', function () {
    Deck::factory()->public()->create(['name' => 'Dragon Rush', 'game' => 'yugioh', 'format' => 'tcg']);

    $html = $this->get(route('public-deck', ['q' => 'dragon', 'sort' => 'trending', 'game' => 'yugioh', 'format' => 'tcg']))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('name="q" value="dragon"')
        ->toMatch('/<a[^>]*data-sort="trending"[^>]*aria-current="true"/')
        ->toMatch('/<a[^>]*data-game="yugioh"[^>]*aria-current="true"/')
        ->toMatch('/<option value="tcg"\s+selected/')
        ->toContain('name="sort" value="trending"')
        ->toContain('name="game" value="yugioh"');
});

test('sort and series links keep the other filters and start again at page one', function () {
    $html = $this->get(route('public-deck', ['q' => 'dragon', 'game' => 'yugioh', 'format' => 'tcg', 'page' => 2]))->getContent();

    // Sorting keeps the search, series and format.
    expect($html)->toContain('href="'.e(route('public-deck', ['q' => 'dragon', 'sort' => 'liked', 'game' => 'yugioh', 'format' => 'tcg'])).'"');
    // Another series keeps the search but drops the format, which belongs to the old series.
    expect($html)->toContain('href="'.e(route('public-deck', ['q' => 'dragon', 'sort' => 'newest', 'game' => 'magic'])).'"');
});

test('the format select only appears once a series is picked, with Casual first', function () {
    expect($this->get(route('public-deck'))->getContent())->not->toContain('name="format"');

    expect($this->get(route('public-deck', ['game' => 'yugioh']))->getContent())
        ->toContain('name="format"')
        ->toContain('data-autosubmit')
        ->toMatch('/All formats.*Casual.*TCG Advanced.*OCG/s');
});

test('signed-in users get the liked toggle and the new deck button, guests do not', function () {
    $this->get(route('public-deck'))
        ->assertDontSee('Liked by me')
        ->assertDontSee(route('decks.builder', 'yugioh'));

    $user = User::factory()->create();

    $this->actingAs($user)->get(route('public-deck'))
        ->assertSee('Liked by me')
        ->assertSee('href="'.e(route('public-deck', ['sort' => 'newest', 'liked' => 1])).'"', false);

    // When it's on, the toggle turns it off again.
    $this->actingAs($user)->get(route('public-deck', ['liked' => 1]))
        ->assertSee('href="'.e(route('public-deck', ['sort' => 'newest'])).'"', false);
});

test('the list shows the result count and trending chips', function () {
    $deck = Deck::factory()->public()->create();
    DeckLike::factory()->count(2)->for($deck)->create();
    Deck::factory()->public()->create();

    $this->get(route('public-deck', ['sort' => 'trending']))
        ->assertSee('2 decks')
        ->assertSee('+2 this week');
});

test('the empty states tell no decks, no matches and no likes apart', function () {
    $this->get(route('public-deck'))->assertSee('No public decks yet');

    Deck::factory()->public()->create(['name' => 'Burn']);

    $this->get(route('public-deck', ['q' => 'nothing like this']))
        ->assertSee('No decks match')
        ->assertSee('Clear filters');

    $this->actingAs(User::factory()->create())->get(route('public-deck', ['liked' => 1]))
        ->assertSee('You haven’t liked any decks yet')
        ->assertDontSee('No decks match');
});
