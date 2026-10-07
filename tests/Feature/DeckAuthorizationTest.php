<?php

use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot delete a deck', function () {
    $deck = Deck::factory()->create();

    $this->delete(route('decks.destroy', $deck))->assertRedirect(route('login'));

    expect(Deck::find($deck->id))->not->toBeNull();
});

test('users cannot delete decks they do not own', function () {
    $deck = Deck::factory()->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('decks.destroy', $deck))
        ->assertForbidden();

    expect(Deck::find($deck->id))->not->toBeNull();
});

test('owners can delete their deck', function () {
    $deck = Deck::factory()->create();

    $this->actingAs($deck->user)
        ->delete(route('decks.destroy', $deck))
        ->assertRedirect(route('decks'));

    expect(Deck::find($deck->id))->toBeNull();
});

test('guests cannot update a deck', function () {
    $deck = Deck::factory()->create(['name' => 'Original']);

    $this->patch(route('decks.update', $deck), ['deck_title' => 'Hacked', 'cards' => '{}'])
        ->assertRedirect(route('login'));

    expect($deck->fresh()->name)->toBe('Original');
});

test('users cannot update decks they do not own', function () {
    $deck = Deck::factory()->create(['name' => 'Original']);

    $this->actingAs(User::factory()->create())
        ->patch(route('decks.update', $deck), ['deck_title' => 'Hacked', 'cards' => '{}'])
        ->assertForbidden();

    expect($deck->fresh()->name)->toBe('Original');
});

test('users cannot open the edit page of decks they do not own', function () {
    $deck = Deck::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('decks.edit', $deck))
        ->assertForbidden();
});

test('private decks are hidden from other users and guests', function () {
    $deck = Deck::factory()->create(['is_public' => false]);

    $this->get(route('decks.show', $deck))->assertNotFound();
    $this->actingAs(User::factory()->create())
        ->get(route('decks.show', $deck))
        ->assertNotFound();
});

test('owners can view their private deck', function () {
    $deck = Deck::factory()->create(['is_public' => false, 'game' => 'yugioh']);

    $this->actingAs($deck->user)->get(route('decks.show', $deck))->assertOk();
});

test('anyone can view a public deck', function () {
    $deck = Deck::factory()->public()->create(['game' => 'yugioh']);

    $this->get(route('decks.show', $deck))->assertOk()->assertSee($deck->name);
});

test('a non-numeric deck id returns 404 instead of a server error', function () {
    $this->get('/decks/not-a-number')->assertNotFound();
});

test('my decks page requires login and only lists my own decks', function () {
    $this->get(route('decks'))->assertRedirect(route('login'));

    $mine = Deck::factory()->create(['name' => 'Mine']);
    Deck::factory()->create(['name' => 'Someone elses']);

    $this->actingAs($mine->user)
        ->get(route('decks'))
        ->assertOk()
        ->assertSee('Mine')
        ->assertDontSee('Someone elses');
});

test('the public deck list only shows public decks and can be filtered by game', function () {
    Deck::factory()->public()->create(['name' => 'Public Magic', 'game' => 'magic']);
    Deck::factory()->public()->create(['name' => 'Public Yugioh', 'game' => 'yugioh']);
    Deck::factory()->create(['name' => 'Secret Deck']);

    $this->get(route('public-deck'))
        ->assertOk()
        ->assertSee('Public Magic')
        ->assertSee('Public Yugioh')
        ->assertDontSee('Secret Deck');

    $this->getJson(route('public-deck', ['game' => 'magic']), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertJsonPath('html', fn (string $html) => str_contains($html, 'Public Magic') && ! str_contains($html, 'Public Yugioh'));
});
