<?php

use App\Models\Deck;
use App\Models\DeckLike;
use App\Models\User;
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
        ->assertViewHas('isOwner', false);
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
        ->assertViewHas('isOwner', true);
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

test('likes received only count likes on public decks', function () {
    $user = User::factory()->create();
    $public = Deck::factory()->public()->for($user)->create();
    $private = Deck::factory()->for($user)->create();
    DeckLike::factory()->count(2)->for($public)->create();
    DeckLike::factory()->count(3)->for($private)->create();

    $this->get(route('users.show', $user))
        ->assertViewHas('stats', ['public_decks' => 1, 'likes_received' => 2]);
});

test('the profile can be sorted and filtered by game, but search does not apply', function () {
    $user = User::factory()->create();
    $popular = Deck::factory()->public()->for($user)->create(['name' => 'Popular', 'game' => 'magic', 'created_at' => now()->subDays(5)]);
    Deck::factory()->public()->for($user)->create(['name' => 'Fresh', 'game' => 'magic', 'created_at' => now()->subDay()]);
    Deck::factory()->public()->for($user)->create(['name' => 'Duel', 'game' => 'yugioh']);
    DeckLike::factory()->count(2)->for($popular)->create();

    $response = $this->get(route('users.show', [$user, 'game' => 'magic', 'sort' => 'liked', 'q' => 'nothing matches']));

    expect(collect($response->viewData('decks')->items())->pluck('name')->all())->toBe(['Popular', 'Fresh'])
        ->and($response->viewData('filters')['q'])->toBeNull()
        ->and($response->viewData('sorts'))->toHaveKey('trending');
});

test('profile page links stay on the profile', function () {
    $user = User::factory()->create(['username' => 'prolific']);
    Deck::factory()->public()->for($user)->count(13)->create();

    $response = $this->get(route('users.show', $user));

    expect($response->viewData('decks')->nextPageUrl())->toStartWith(route('users.show', $user));
    $response->assertSee(route('users.show', $user).'?page=2', false)
        ->assertDontSee(route('public-deck').'?page=2', false);
});

test('the profile header shows when the user joined and their stats', function () {
    $user = User::factory()->create(['username' => 'deckmaster', 'created_at' => '2026-03-14 12:00:00']);
    $deck = Deck::factory()->public()->for($user)->create();
    DeckLike::factory()->count(3)->for($deck)->create();

    expect($this->get(route('users.show', $user))->getContent())
        ->toContain('Player · joined March 2026')
        ->toMatch('/>1<\/b>\s*public deck\s*</')
        ->toMatch('/>3<\/b>\s*likes received\s*</');
});

test('sort and series links stay on the profile', function () {
    $user = User::factory()->create(['username' => 'deckmaster']);
    Deck::factory()->public()->for($user)->create();

    $html = $this->get(route('users.show', [$user, 'game' => 'magic']))->getContent();

    expect($html)
        ->toContain('href="'.e(route('users.show', [$user, 'sort' => 'liked', 'game' => 'magic'])).'"')
        ->toContain('href="'.e(route('users.show', [$user, 'sort' => 'newest'])).'"')
        ->toMatch('/data-game="magic"[^>]*aria-current="true"/');
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

test('your account page links to your public profile', function () {
    $user = User::factory()->create(['username' => 'deckmaster']);

    $this->actingAs($user)->get(route('profile'))->assertSee(route('users.show', $user));
});
