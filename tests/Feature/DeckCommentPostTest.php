<?php

use App\Models\Comment;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Without a previous page the redirect falls back to the deck's comments section.
function deckCommentsAnchor(Deck $deck): string
{
    return route('decks.show', $deck).'#comments';
}

test('guests are sent to log in', function () {
    $deck = commentableDeck();

    $this->post(route('decks.comments.store', $deck), ['body' => 'Hi'])->assertRedirect(route('login'));
    expect(Comment::count())->toBe(0);
});

test('a player can comment on a public deck', function () {
    $deck = commentableDeck();
    $author = User::factory()->create();

    $this->actingAs($author)->post(route('decks.comments.store', $deck), ['body' => 'Love the engine'])
        ->assertRedirect(deckCommentsAnchor($deck))
        ->assertSessionHas('success', 'Comment posted.');

    $this->assertDatabaseHas('comments', [
        'user_id' => $author->id,
        'commentable_type' => 'deck',
        'commentable_id' => $deck->id,
        'body' => 'Love the engine',
    ]);
});

test('the owner can comment on their own deck', function () {
    $deck = commentableDeck();

    $this->actingAs($deck->user)->post(route('decks.comments.store', $deck), ['body' => 'Thanks!'])
        ->assertRedirect(deckCommentsAnchor($deck));

    expect($deck->comments()->count())->toBe(1);
});

test('someone else’s private deck is a 404', function () {
    $deck = Deck::factory()->create(['game' => 'yugioh']);

    $this->actingAs(User::factory()->create())->post(route('decks.comments.store', $deck), ['body' => 'Hi'])
        ->assertNotFound();
    expect(Comment::count())->toBe(0);
});

test('nobody can comment on a private deck or one with comments off', function () {
    $private = Deck::factory()->create(['game' => 'yugioh']);
    $off = commentableDeck(['allow_comments' => false]);

    $this->actingAs($private->user)->post(route('decks.comments.store', $private), ['body' => 'Note'])->assertForbidden();
    $this->actingAs($off->user)->post(route('decks.comments.store', $off), ['body' => 'Hi'])->assertForbidden();
    $this->actingAs(User::factory()->create())->post(route('decks.comments.store', $off), ['body' => 'Hi'])->assertForbidden();

    expect(Comment::count())->toBe(0);
});

test('deck comments follow the comment rules', function () {
    $deck = commentableDeck();
    $author = User::factory()->create();

    foreach (['', "   \n  ", ['x'], str_repeat('a', 501)] as $body) {
        $this->actingAs($author)->post(route('decks.comments.store', $deck), ['body' => $body])
            ->assertSessionHasErrorsIn('comment', 'body');
    }
    $this->actingAs($author)->post(route('decks.comments.store', $deck), ['body' => str_repeat("abcd\r\n", 90).'end'])
        ->assertSessionHasNoErrors();

    expect(Comment::count())->toBe(1)
        ->and(mb_strlen(Comment::first()->body))->toBe(453);
});

test('deck comments share the 5 a minute limit', function () {
    $deck = commentableDeck();
    $author = User::factory()->create();

    foreach (range(1, 5) as $i) {
        $this->actingAs($author)->post(route('decks.comments.store', $deck), ['body' => "Comment {$i}"])->assertRedirect();
    }

    $this->actingAs($author)->post(route('decks.comments.store', $deck), ['body' => 'One too many'])->assertTooManyRequests();
    expect(Comment::count())->toBe(5);
});

test('a posted deck comment lands on the first page with one fragment, a failed one keeps its page', function () {
    $deck = commentableDeck();
    $author = User::factory()->create();

    $this->actingAs($author)
        ->from(route('decks.show', [$deck, 'page' => 2]).'#comments')
        ->post(route('decks.comments.store', $deck), ['body' => 'Hello'])
        ->assertRedirect(deckCommentsAnchor($deck));

    $this->actingAs($author)
        ->from(route('decks.show', [$deck, 'page' => 2]))
        ->post(route('decks.comments.store', $deck), ['body' => str_repeat('b', 501)])
        ->assertRedirect(route('decks.show', [$deck, 'page' => 2]).'#comments');
});
