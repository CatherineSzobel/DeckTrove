<?php

use App\Models\Comment;
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
