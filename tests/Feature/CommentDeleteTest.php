<?php

use App\Models\Comment;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the author can delete their comment', function () {
    $profile = User::factory()->create();
    $author = User::factory()->create();
    $comment = Comment::factory()->by($author)->on($profile)->create();

    $this->actingAs($author)->delete(route('comments.destroy', $comment))
        ->assertRedirect(route('users.show', $profile).'#comments')
        ->assertSessionHas('success', 'Comment deleted.');

    expect(Comment::find($comment->id))->toBeNull();
});

test('the profile owner can delete any comment on their profile', function () {
    $profile = User::factory()->create();
    $comment = Comment::factory()->on($profile)->create();

    $this->actingAs($profile)->delete(route('comments.destroy', $comment))->assertRedirect();

    expect(Comment::find($comment->id))->toBeNull();
});

test('other players cannot delete a comment', function () {
    $profile = User::factory()->create();
    $comment = Comment::factory()->on($profile)->create();

    $this->actingAs(User::factory()->create())->delete(route('comments.destroy', $comment))->assertForbidden();

    expect(Comment::find($comment->id))->not->toBeNull();
});

test('deleting goes back to the page it was on', function () {
    $profile = User::factory()->create();
    $comment = Comment::factory()->on($profile)->create();

    $this->actingAs($profile)
        ->from(route('users.comments', [$profile, 'page' => 2]))
        ->delete(route('comments.destroy', $comment))
        ->assertRedirect(route('users.comments', [$profile, 'page' => 2]).'#comments')
        ->assertSessionHas('success', 'Comment deleted.');
});

test('guests are sent to log in', function () {
    $comment = Comment::factory()->create();

    $this->delete(route('comments.destroy', $comment))->assertRedirect(route('login'));
    expect(Comment::find($comment->id))->not->toBeNull();
});

test('the delete button shows only to who can delete, and asks first', function () {
    $profile = User::factory()->create();
    $alice = User::factory()->create(['username' => 'alice']);
    $bob = User::factory()->create(['username' => 'bob']);
    Comment::factory()->by($alice)->on($profile)->create();
    Comment::factory()->by($bob)->on($profile)->create();
    $url = route('users.comments', $profile);

    $asAlice = $this->actingAs($alice)->get($url)->getContent();
    expect($asAlice)
        ->toContain('aria-label="Delete comment by alice"')
        ->not->toContain('aria-label="Delete comment by bob"')
        ->toContain('data-confirm-title="Delete this comment?"')
        ->toContain('data-confirm-button="Delete comment"');

    $asOwner = $this->actingAs($profile)->get($url)->getContent();
    expect(substr_count($asOwner, 'aria-label="Delete comment by'))->toBe(2);

    $asOther = $this->actingAs(User::factory()->create())->get($url)->getContent();
    expect($asOther)->not->toContain('aria-label="Delete comment by');

    app('auth')->forgetGuards();
    expect($this->get($url)->getContent())->not->toContain('aria-label="Delete comment by');
});

test('the author and the deck owner can delete a comment on a deck, others cannot', function () {
    $deck = Deck::factory()->public()->create();
    $author = User::factory()->create();
    [$first, $second, $third] = Comment::factory()->by($author)->on($deck)->count(3)->create()->all();

    $this->actingAs(User::factory()->create())->delete(route('comments.destroy', $first))->assertForbidden();

    $this->actingAs($author)->delete(route('comments.destroy', $first))
        ->assertRedirect(route('decks.show', $deck).'#comments')
        ->assertSessionHas('success', 'Comment deleted.');

    $this->actingAs($deck->user)->delete(route('comments.destroy', $second))->assertRedirect();

    $deck->update(['is_public' => false]);
    $this->actingAs($deck->user)->delete(route('comments.destroy', $third))->assertRedirect();

    expect($deck->comments()->count())->toBe(0);
});

// The policy compares owners, not ids: a user whose id equals the deck's id is not its owner.
test('a user whose id equals the deck id is not its owner', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $deck = Deck::factory()->for($owner)->public()->create(['id' => $other->id]);
    $comment = Comment::factory()->on($deck)->create();

    $this->actingAs($other)->delete(route('comments.destroy', $comment))->assertForbidden();
});
