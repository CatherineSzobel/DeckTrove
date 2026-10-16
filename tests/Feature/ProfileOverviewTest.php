<?php

use App\Models\Comment;
use App\Models\Deck;
use App\Models\DeckLike;
use App\Models\User;
use App\Profiles\ProfilePage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function overview(User $profile, array $query = [], ?User $viewer = null)
{
    if (! $viewer) {
        app('auth')->forgetGuards();

        return test()->get(route('users.show', [$profile, ...$query]))->assertOk();
    }

    return test()->actingAs($viewer)->get(route('users.show', [$profile, ...$query]))->assertOk();
}

test('the overview has the three sections with plain headings and no tabs', function () {
    $user = User::factory()->create(['username' => 'kaiba']);

    expect(overview($user, [], $user)->getContent())
        ->toContain('id="decks"')
        ->toContain('id="liked"')
        ->toContain('id="comments"')
        ->toMatch('/<h2[^>]*>Public decks<\/h2>/')
        ->toMatch('/<h2[^>]*>Liked decks<\/h2>/')
        ->toMatch('/<h2[^>]*>Comments<\/h2>/')
        ->not->toContain('Shared by')
        ->not->toContain('Liked by')
        ->not->toContain('On kaiba’s profile')
        ->not->toContain('aria-label="Profile sections"');
});

test('visitors only see liked decks while the owner shows them', function () {
    $user = User::factory()->create(['show_liked_decks' => false]);
    DeckLike::factory()->for($user)->for(Deck::factory()->public()->state(['name' => 'Secretly Liked']))->create();

    expect(overview($user)->getContent())
        ->not->toContain('id="liked"')
        ->not->toContain('href="#liked"')
        ->not->toContain('Secretly Liked');
    expect(overview($user, [], User::factory()->create())->getContent())->not->toContain('Secretly Liked');

    $user->update(['show_liked_decks' => true]);
    expect(overview($user->fresh())->getContent())->toContain('Secretly Liked')->toContain('href="#liked"');
});

test('the owner sees their private liked decks with a badge', function () {
    $user = User::factory()->create(['show_liked_decks' => false]);

    expect(overview($user, [], $user)->getContent())->toContain('Only you can see this');

    $user->update(['show_liked_decks' => true]);
    expect(overview($user->fresh(), [], $user)->getContent())->not->toContain('Only you can see this');
});

test('comments are hidden, not deleted, while the owner has them off', function () {
    $user = User::factory()->create(['allow_comments' => false]);
    Comment::factory()->on($user)->create(['body' => 'Still here']);

    expect(overview($user)->getContent())
        ->not->toContain('id="comments"')
        ->not->toContain('href="#comments"')
        ->not->toContain('Still here');

    $user->update(['allow_comments' => true]);
    expect(overview($user->fresh())->getContent())->toContain('Still here');
});

test('the jump links count what each section holds', function () {
    $user = User::factory()->create(['show_liked_decks' => true]);
    Deck::factory()->public()->for($user)->count(2)->create();
    Deck::factory()->for($user)->create();
    DeckLike::factory()->for($user)->for(Deck::factory()->public())->create();
    DeckLike::factory()->for($user)->for(Deck::factory())->create();
    Comment::factory()->on($user)->count(3)->create();

    expect(overview($user)->viewData('page')->sections())->toBe([
        ['id' => 'decks', 'label' => 'Public decks', 'count' => 2],
        ['id' => 'liked', 'label' => 'Liked decks', 'count' => 1],
        ['id' => 'comments', 'label' => 'Comments', 'count' => 3],
    ]);
});

test('likes received only count likes on public decks', function () {
    $user = User::factory()->create();
    $public = Deck::factory()->public()->for($user)->create();
    $private = Deck::factory()->for($user)->create();
    DeckLike::factory()->count(2)->for($public)->create();
    DeckLike::factory()->count(3)->for($private)->create();

    $response = overview($user);

    expect($response->viewData('page')->likesReceived())->toBe(2)
        ->and($response->getContent())->toMatch('/Likes received\s*<b[^>]*>2<\/b>/');
});

test('the overview shows the first 6 decks, 8 liked and 5 comments, with links to the rest', function () {
    $user = User::factory()->create(['show_liked_decks' => true]);
    Deck::factory()->public()->for($user)->count(7)->create();
    Deck::factory()->public()->count(9)->create()->each(fn (Deck $deck) => DeckLike::factory()->for($user)->for($deck)->create());
    Comment::factory()->on($user)->count(6)->create();

    $response = overview($user);
    $page = $response->viewData('page');

    expect($page->overviewDecks())->toHaveCount(6)
        ->and($page->overviewLiked())->toHaveCount(8)
        ->and($page->overviewComments())->toHaveCount(5)
        ->and($response->getContent())
        ->toContain('href="'.route('users.decks', $user).'"')
        ->toContain('All 7 decks')
        ->toContain('href="'.route('users.liked', $user).'"')
        ->toContain('All 9 liked')
        ->toContain('href="'.route('users.comments', $user).'"')
        ->toContain('Show older comments (1)');
});

test('no see-all links when everything fits', function () {
    $user = User::factory()->create(['show_liked_decks' => true]);
    Deck::factory()->public()->for($user)->count(6)->create();
    Deck::factory()->public()->count(8)->create()->each(fn (Deck $deck) => DeckLike::factory()->for($user)->for($deck)->create());
    Comment::factory()->on($user)->count(5)->create();

    $response = overview($user);
    $page = $response->viewData('page');

    expect([$page->moreLink('decks'), $page->moreLink('liked'), $page->moreLink('comments')])->toBe([null, null, null])
        ->and($response->getContent())
        ->not->toContain(route('users.decks', $user))
        ->not->toContain(route('users.liked', $user))
        ->not->toContain(route('users.comments', $user));
});

test('the newest decks and comments come first', function () {
    $user = User::factory()->create();
    Deck::factory()->public()->for($user)->create(['name' => 'Old Deck', 'created_at' => now()->subDays(3)]);
    Deck::factory()->public()->for($user)->create(['name' => 'New Deck', 'created_at' => now()->subDay()]);
    Comment::factory()->on($user)->create(['body' => 'Old comment', 'created_at' => now()->subHours(3)]);
    Comment::factory()->on($user)->create(['body' => 'New comment', 'created_at' => now()->subHour()]);

    $page = overview($user)->viewData('page');

    expect($page->overviewDecks()->pluck('name')->all())->toBe(['New Deck', 'Old Deck'])
        ->and($page->overviewComments()->pluck('body')->all())->toBe(['New comment', 'Old comment']);
});

test('liked decks show their owner, and a deck made private drops out of the list and the count', function () {
    $user = User::factory()->create(['show_liked_decks' => true]);
    $owner = User::factory()->create(['username' => 'builder']);
    $kept = Deck::factory()->public()->for($owner)->create(['name' => 'Blue Eyes Deck']);
    $gone = Deck::factory()->public()->for($owner)->create(['name' => 'Hidden Gem']);
    DeckLike::factory()->for($user)->for($kept)->create();
    DeckLike::factory()->for($user)->for($gone)->create();
    $gone->update(['is_public' => false]);

    $response = overview($user);

    expect($response->getContent())
        ->toContain('Blue Eyes Deck')
        ->toContain('by builder')
        ->not->toContain('Hidden Gem')
        ->and($response->viewData('page')->likedCount())->toBe(1);
});

test('empty sections say so, for the owner and for visitors', function () {
    $user = User::factory()->create(['username' => 'newcomer']);

    expect(overview($user, [], $user)->getContent())
        ->toContain('You haven’t shared a deck yet')
        ->toContain('You haven’t liked any decks yet')
        ->toContain('No comments yet');
    expect(overview($user)->getContent())
        ->toContain('newcomer hasn’t shared any decks yet.')
        ->toContain('No comments yet');
});

test('old tab links and list filters still show the plain overview', function () {
    $user = User::factory()->create(['show_liked_decks' => true]);
    Deck::factory()->public()->for($user)->create(['name' => 'Duel Deck', 'game' => 'yugioh']);

    $response = overview($user, ['tab' => 'liked', 'game' => 'magic', 'sort' => 'liked']);

    expect($response->viewData('page')->list())->toBe('overview')
        ->and($response->viewData('page')->overviewDecks()->pluck('name')->all())->toBe(['Duel Deck']);
});

test('the overview form posts a comment for logged-in players', function () {
    $user = User::factory()->create();

    expect(overview($user, [], User::factory()->create())->getContent())
        ->toContain('action="'.route('users.comments.store', $user).'"');
});

test('the profile card is capped to the viewport height so its links stay reachable', function () {
    $user = User::factory()->create();

    expect(overview($user, [], $user)->getContent())->toContain('lg:max-h-[calc(100dvh-3rem)]');
});

test('likes received sits outside the on-this-page navigation', function () {
    $user = User::factory()->create();

    expect(overview($user)->getContent())
        ->toMatch('/<\/nav>\s*<p[^>]*>\s*Likes received/')
        ->not->toMatch('/<nav aria-label="On this page"[^>]*>(?:(?!<\/nav>).)*Likes received/s');
});

test('the overview page names itself by the username', function () {
    $user = User::factory()->create(['username' => 'kaiba']);
    $page = ProfilePage::for($user, request());

    expect($page->heading())->toBe('kaiba')->and($page->title())->toBe('kaiba');
});
