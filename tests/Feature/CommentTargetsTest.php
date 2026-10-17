<?php

use App\Comments\Commentable;
use App\Comments\CommentsRedirect;
use App\Models\Comment;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('a profile is a comment target owned by its user', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(Commentable::class)
        ->and($user->commentOwnerId())->toBe($user->id)
        ->and($user->acceptsComments())->toBeTrue()
        ->and($user->commentsFallbackUrl())->toBe(route('users.show', $user));

    $user->allow_comments = false;
    expect($user->acceptsComments())->toBeFalse();
});

test('a deck accepts comments only while public and allowed', function () {
    $deck = Deck::factory()->public()->create();

    expect($deck)->toBeInstanceOf(Commentable::class)
        ->and($deck->commentOwnerId())->toBe($deck->user_id)
        ->and($deck->acceptsComments())->toBeTrue()
        ->and($deck->commentsFallbackUrl())->toBe(route('decks.show', $deck));

    $deck->allow_comments = false;
    expect($deck->acceptsComments())->toBeFalse();

    $deck->allow_comments = true;
    $deck->is_public = false;
    expect($deck->acceptsComments())->toBeFalse();
});

test('decks allow comments unless turned off', function () {
    expect((new Deck)->allow_comments)->toBeTrue()
        ->and(Deck::factory()->create()->fresh()->allow_comments)->toBeTrue()
        ->and(Deck::factory()->create(['allow_comments' => false])->fresh()->allow_comments)->toBeFalse();
});

test('a deck lists its comments', function () {
    $deck = Deck::factory()->public()->create();
    $comment = Comment::factory()->on($deck)->create();
    Comment::factory()->on($deck->user)->create();

    expect($deck->comments()->pluck('id')->all())->toBe([$comment->id])
        ->and($comment->fresh()->commentable_type)->toBe('deck');
});

test('without a previous page the redirect goes to the target at its comments', function () {
    $deck = Deck::factory()->public()->create();
    $user = User::factory()->create();

    expect(CommentsRedirect::url($deck))->toBe(route('decks.show', $deck).'#comments')
        ->and(CommentsRedirect::url($user))->toBe(route('users.show', $user).'#comments');
});

// Checking who may delete each comment must not load its profile or deck again, one query per comment.
function queriesFor(callable $request): int
{
    $count = 0;
    DB::listen(function () use (&$count) {
        $count++;
    });
    $request();

    return $count;
}

test('the delete check does not query the target once per comment', function () {
    $viewer = User::factory()->create();
    $deck = Deck::factory()->public()->create(['game' => 'yugioh']);
    $profile = User::factory()->create();
    $authors = User::factory()->count(6)->create();

    $deckQueries = fn () => queriesFor(fn () => $this->actingAs($viewer)->get(route('decks.show', $deck))->assertOk());
    $profileQueries = fn () => queriesFor(fn () => $this->actingAs($viewer)->get(route('users.comments', $profile))->assertOk());

    Comment::factory()->by($authors[0])->on($deck)->create();
    Comment::factory()->by($authors[0])->on($profile)->create();
    [$deckFew, $profileFew] = [$deckQueries(), $profileQueries()];

    foreach ($authors->slice(1) as $author) {
        Comment::factory()->by($author)->on($deck)->create();
        Comment::factory()->by($author)->on($profile)->create();
    }

    expect($deckQueries())->toBe($deckFew)
        ->and($profileQueries())->toBe($profileFew);
});
