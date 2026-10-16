<?php

use App\Models\Deck;
use App\Models\DeckLike;
use App\Models\User;
use App\Profiles\ProfilePage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a profile shows the user and only their public decks', function () {
    $user = User::factory()->create(['username' => 'deckmaster', 'about' => 'I build combo decks.', 'email' => 'secret@example.com']);
    Deck::factory()->public()->for($user)->create(['name' => 'Shared Deck']);
    Deck::factory()->for($user)->create(['name' => 'Hidden Deck']);
    Deck::factory()->public()->create(['name' => 'Someone Else']);

    $this->get(route('users.show', $user))
        ->assertOk()
        ->assertSee('deckmaster')
        ->assertSee('I build combo decks.')
        ->assertSee('Shared Deck')
        ->assertDontSee('Hidden Deck')
        ->assertDontSee('Someone Else')
        ->assertDontSee('secret@example.com')
        ->assertDontSee('Edit profile')
        ->assertViewHas('page', fn (ProfilePage $page) => ! $page->isOwner());
});

test('the owner sees their public view with an edit link, still without private decks', function () {
    $user = User::factory()->create();
    Deck::factory()->for($user)->create(['name' => 'Hidden Deck']);

    $this->actingAs($user)
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertSee('Edit profile')
        ->assertSee(route('profile.edit'))
        ->assertDontSee('Hidden Deck')
        ->assertViewHas('page', fn (ProfilePage $page) => $page->isOwner());
});

test('unknown usernames are not found', function () {
    $this->get('/u/nobody-here')->assertNotFound();
});

test('usernames with dashes and underscores resolve', function () {
    $user = User::factory()->create(['username' => 'deck_master-1']);

    $this->get('/u/deck_master-1')
        ->assertOk()
        ->assertViewHas('user', fn (User $shown) => $shown->is($user));
});

test('the profile header shows when the user joined and their stats', function () {
    $user = User::factory()->create(['username' => 'deckmaster', 'created_at' => '2026-03-14 12:00:00']);
    $deck = Deck::factory()->public()->for($user)->create();
    DeckLike::factory()->count(3)->for($deck)->create();

    expect($this->get(route('users.show', $user))->getContent())
        ->toContain('Player · joined March 2026')
        ->toMatch('/Public decks\s*<b[^>]*>1<\/b>/')
        ->toMatch('/Likes received\s*<b[^>]*>3<\/b>/');
});

test('an empty profile says so, and points the owner to their decks', function () {
    $user = User::factory()->create(['username' => 'newcomer']);

    $this->get(route('users.show', $user))
        ->assertSee('No public decks yet')
        ->assertSee('newcomer hasn’t shared any decks yet.')
        ->assertDontSee(route('decks'));

    $this->actingAs($user)->get(route('users.show', $user))
        ->assertSee('You haven’t shared a deck yet')
        ->assertSee(route('decks'));
});

test('your edit profile page links to your public profile', function () {
    $user = User::factory()->create(['username' => 'deckmaster']);

    $this->actingAs($user)->get(route('profile.edit'))->assertSee(route('users.show', $user));
});
