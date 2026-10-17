<?php

use App\Models\Comment;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function deckPageHtml(Deck $deck, ?User $viewer = null, array $query = []): string
{
    $viewer ? test()->actingAs($viewer) : app('auth')->forgetGuards();

    return test()->get(route('decks.show', [$deck] + $query))->assertOk()->getContent();
}

const PRIVATE_NOTE = 'Comments are hidden while this deck is private.';
const OFF_NOTE = 'Comments are turned off for this deck. Other players can’t see or add them.';

test('logged-in players get the form, guests the log in line', function () {
    $deck = commentableDeck();

    expect(deckPageHtml($deck, User::factory()->create()))
        ->toContain('id="comments"')
        ->toContain('action="'.route('decks.comments.store', $deck).'"')
        ->toContain('x-data="charCounter(0, 500)"')
        ->toContain('No comments yet')
        ->not->toContain('to leave a comment.');

    expect(deckPageHtml($deck))
        ->toContain('id="comments"')
        ->toContain('to leave a comment.')
        ->not->toContain('action="'.route('decks.comments.store', $deck).'"');
});

test('the owner sees comments on a private deck read-only, with the private note', function () {
    $deck = commentableDeck(['is_public' => false, 'allow_comments' => false]);
    Comment::factory()->on($deck)->create(['body' => 'Kept while private']);

    expect(deckPageHtml($deck, $deck->user))
        ->toContain('id="comments"')
        ->toContain('Kept while private')
        ->toContain(PRIVATE_NOTE)
        ->not->toContain(OFF_NOTE)
        ->not->toContain('action="'.route('decks.comments.store', $deck).'"')
        ->toContain('aria-label="Delete comment by');
});

test('the owner sees comments on a turned-off deck read-only, with a link to edit', function () {
    $deck = commentableDeck(['allow_comments' => false]);
    Comment::factory()->on($deck)->create(['body' => 'Kept while off']);

    expect(deckPageHtml($deck, $deck->user))
        ->toContain('Kept while off')
        ->toContain(OFF_NOTE)
        ->toContain('href="'.route('decks.edit', $deck).'"')
        ->not->toContain('action="'.route('decks.comments.store', $deck).'"');
});

test('other players and guests see nothing while comments are off', function () {
    $deck = commentableDeck(['allow_comments' => false]);
    Comment::factory()->on($deck)->create(['body' => 'Hidden from others']);

    foreach ([User::factory()->create(), null] as $viewer) {
        expect(deckPageHtml($deck, $viewer))
            ->not->toContain('id="comments"')
            ->not->toContain('Hidden from others')
            ->not->toContain('data-comments-link');
    }
});

test('a read-only section without comments is hidden, also from the owner', function () {
    $private = commentableDeck(['is_public' => false]);
    $off = commentableDeck(['allow_comments' => false]);

    expect(deckPageHtml($private, $private->user))->not->toContain('id="comments"')->not->toContain('data-comments-link')
        ->and(deckPageHtml($off, $off->user))->not->toContain('id="comments"');
});

test('making a private deck public again shows its comments to others', function () {
    $deck = commentableDeck(['is_public' => false]);
    Comment::factory()->on($deck)->create(['body' => 'Back again']);
    $deck->update(['is_public' => true]);

    expect(deckPageHtml($deck, User::factory()->create()))->toContain('Back again')->not->toContain(PRIVATE_NOTE);
});

test('comments show newest first, 10 per page, with page links to the section', function () {
    $deck = commentableDeck();
    foreach (range(1, 11) as $i) {
        Comment::factory()->on($deck)->create(['body' => sprintf('Body-%02d', $i), 'created_at' => now()->subMinutes(20 - $i)]);
    }

    $first = deckPageHtml($deck);
    expect($first)->toContain('Body-11')->toContain('Body-02')->not->toContain('Body-01')
        ->toContain(route('decks.show', [$deck, 'page' => 2]).'#comments')
        ->and(strpos($first, 'Body-11'))->toBeLessThan(strpos($first, 'Body-10'));

    expect(deckPageHtml($deck, null, ['page' => 2]))->toContain('Body-01')->not->toContain('Body-02');
});

test('a page past the last goes to the last page', function () {
    $deck = commentableDeck();
    Comment::factory()->on($deck)->count(3)->create();

    $this->get(route('decks.show', [$deck, 'page' => 9]))
        ->assertRedirect(route('decks.show', [$deck, 'page' => 1]).'#comments');
});

test('the deck owner’s comments carry the badge and text is escaped', function () {
    $deck = commentableDeck();
    Comment::factory()->by($deck->user)->on($deck)->create(['body' => "Line one\nLine two"]);
    Comment::factory()->on($deck)->create(['body' => '<script>alert(1)</script>']);

    expect(deckPageHtml($deck))
        ->toContain('owner-badge">Deck owner</span>')
        ->toContain("Line one\nLine two")
        ->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->not->toContain('<script>alert(1)</script>');
});

test('the header links to the comments with their count', function () {
    $deck = commentableDeck();

    expect(deckPageHtml($deck))->toContain('data-comments-link')->toContain('href="#comments"')->toContain('<span>0 comments</span>');

    Comment::factory()->on($deck)->create();
    expect(deckPageHtml($deck))->toContain('<span>1 comment</span>');

    Comment::factory()->on($deck)->create();
    expect(deckPageHtml($deck))->toContain('<span>2 comments</span>');
});

test('the deck page script registers the comment counter', function () {
    expect(file_get_contents(resource_path('js/deck-page.js')))->toContain('charCounter');
});
