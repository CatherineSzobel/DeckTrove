<?php

use App\Models\Deck;
use App\Models\DeckLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Each request is made as $viewer, or as a guest (actingAs would otherwise carry over to the next request).
function deckListPage(string $route, User $profile, array $query = [], ?User $viewer = null)
{
    if (! $viewer) {
        app('auth')->forgetGuards();

        return test()->get(route($route, [$profile, ...$query]));
    }

    return test()->actingAs($viewer)->get(route($route, [$profile, ...$query]));
}

test('the decks page lists only the user’s public decks, with a breadcrumb back to the profile', function () {
    $user = User::factory()->create(['username' => 'deckmaster']);
    Deck::factory()->public()->for($user)->create(['name' => 'Shared Deck']);
    Deck::factory()->for($user)->create(['name' => 'Hidden Deck']);
    Deck::factory()->public()->create(['name' => 'Someone Else']);

    expect(deckListPage('users.decks', $user)->assertOk()->getContent())
        ->toContain('<title>Public decks · deckmaster · DeckTrove</title>')
        ->toContain('href="'.route('users.show', $user).'"')
        ->toMatch('/<h1[^>]*>Public decks<\/h1>/')
        ->toContain('Shared Deck')
        ->not->toContain('Hidden Deck')
        ->not->toContain('Someone Else')
        ->not->toContain('Shared by');
});

test('the decks page can be sorted and filtered by game, but search does not apply', function () {
    $user = User::factory()->create();
    $popular = Deck::factory()->public()->for($user)->create(['name' => 'Popular', 'game' => 'magic', 'created_at' => now()->subDays(5)]);
    Deck::factory()->public()->for($user)->create(['name' => 'Fresh', 'game' => 'magic', 'created_at' => now()->subDay()]);
    Deck::factory()->public()->for($user)->create(['name' => 'Duel', 'game' => 'yugioh']);
    DeckLike::factory()->count(2)->for($popular)->create();

    $page = deckListPage('users.decks', $user, ['game' => 'magic', 'sort' => 'liked', 'q' => 'nothing matches'])->viewData('page');

    expect(collect($page->decks()->items())->pluck('name')->all())->toBe(['Popular', 'Fresh'])
        ->and($page->filters()['q'])->toBeNull();
});

test('decks page links stay on the decks page', function () {
    $user = User::factory()->create(['username' => 'prolific']);
    Deck::factory()->public()->for($user)->count(13)->create();

    $response = deckListPage('users.decks', $user);

    expect($response->viewData('page')->decks()->nextPageUrl())->toStartWith(route('users.decks', $user));
    $response->assertSee(route('users.decks', $user).'?page=2', false);
});

test('sort and series links stay on the decks page', function () {
    $user = User::factory()->create();
    Deck::factory()->public()->for($user)->create();

    expect(deckListPage('users.decks', $user, ['game' => 'magic'])->getContent())
        ->toContain('href="'.e(route('users.decks', [$user, 'sort' => 'liked', 'game' => 'magic'])).'"')
        ->toContain('href="'.e(route('users.decks', [$user, 'sort' => 'newest'])).'"')
        ->toMatch('/data-game="magic"[^>]*aria-current="true"/');
});

test('a series without decks says so on the decks page', function () {
    $user = User::factory()->create();
    Deck::factory()->public()->for($user)->create(['game' => 'yugioh']);

    expect(deckListPage('users.decks', $user, ['game' => 'magic'])->getContent())
        ->toContain('No decks in this series')
        ->toContain('href="'.e(route('users.decks', [$user, 'sort' => 'newest'])).'"');
});

test('the decks page says when nothing is shared yet', function () {
    $user = User::factory()->create(['username' => 'newcomer']);

    expect(deckListPage('users.decks', $user)->getContent())->toContain('newcomer hasn’t shared any decks yet.');
    expect(deckListPage('users.decks', $user, [], $user)->getContent())->toContain('You haven’t shared a deck yet');
});

test('the liked page is not found for visitors while liked decks are private', function () {
    $user = User::factory()->create(['show_liked_decks' => false]);

    deckListPage('users.liked', $user)->assertNotFound();
    deckListPage('users.liked', $user, [], User::factory()->create())->assertNotFound();

    expect(deckListPage('users.liked', $user, [], $user)->assertOk()->getContent())
        ->toContain('Only you can see this')
        ->toContain('<title>Liked decks · ');
});

test('the liked page shows no badge once liked decks are public', function () {
    $user = User::factory()->create(['show_liked_decks' => true]);

    deckListPage('users.liked', $user)->assertOk();
    expect(deckListPage('users.liked', $user, [], $user)->getContent())->not->toContain('Only you can see this');
});

test('the liked page lists the public decks the user liked, with their owners', function () {
    $user = User::factory()->create(['show_liked_decks' => true]);
    $owner = User::factory()->create(['username' => 'builder']);
    $liked = Deck::factory()->public()->for($owner)->create(['name' => 'Blue Eyes Deck']);
    $private = Deck::factory()->for($owner)->create(['name' => 'Hidden Gem']);
    DeckLike::factory()->for($user)->for($liked)->create();
    DeckLike::factory()->for($user)->for($private)->create();
    DeckLike::factory()->for(Deck::factory()->public()->state(['name' => 'Others Like This']))->create();

    expect(deckListPage('users.liked', $user)->getContent())
        ->toMatch('/<h1[^>]*>Liked decks<\/h1>/')
        ->toContain('Blue Eyes Deck')
        ->toContain('builder')
        ->not->toContain('Hidden Gem')
        ->not->toContain('Others Like This')
        ->not->toContain('Liked by');
});

test('sort and series links stay on the liked page', function () {
    $user = User::factory()->create(['show_liked_decks' => true]);
    DeckLike::factory()->for($user)->create();

    expect(deckListPage('users.liked', $user)->getContent())
        ->toContain('href="'.e(route('users.liked', [$user, 'sort' => 'liked'])).'"')
        ->toContain('href="'.e(route('users.liked', [$user, 'sort' => 'newest', 'game' => 'magic'])).'"');
});

test('the liked page says when nothing is liked yet', function () {
    $user = User::factory()->create(['username' => 'newcomer', 'show_liked_decks' => true]);

    expect(deckListPage('users.liked', $user, [], $user)->getContent())
        ->toContain('You haven’t liked any decks yet')
        ->toContain(route('public-deck'));
    expect(deckListPage('users.liked', $user)->getContent())
        ->toContain('No liked decks yet')
        ->toContain('newcomer hasn’t liked any decks yet.');
});
