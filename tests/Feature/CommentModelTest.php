<?php

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a profile comment stores its target as a user, not a class name', function () {
    $profile = User::factory()->create();
    $author = User::factory()->create();

    $comment = Comment::factory()->by($author)->on($profile)->create(['body' => 'Nice decks!']);

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'user_id' => $author->id,
        'commentable_type' => 'user',
        'commentable_id' => $profile->id,
        'body' => 'Nice decks!',
    ]);
    expect($comment->author->is($author))->toBeTrue()
        ->and($comment->commentable->is($profile))->toBeTrue()
        ->and($profile->profileComments()->pluck('id')->all())->toBe([$comment->id])
        ->and($author->comments()->pluck('id')->all())->toBe([$comment->id])
        ->and($comment->updated_at)->toBeNull();
});

test('new users hide their liked decks and allow comments, also before a reload', function () {
    $user = User::factory()->create();

    expect($user->show_liked_decks)->toBeFalse()
        ->and($user->allow_comments)->toBeTrue()
        ->and($user->fresh()->show_liked_decks)->toBeFalse()
        ->and($user->fresh()->allow_comments)->toBeTrue();
});

test('deleting an account removes the comments they wrote and the comments on their profile', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $written = Comment::factory()->by($user)->on($other)->create();
    $received = Comment::factory()->by($other)->on($user)->create();
    $unrelated = Comment::factory()->by($other)->on($other)->create();

    $this->actingAs($user)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect(route('index'));

    expect(Comment::find($written->id))->toBeNull()
        ->and(Comment::find($received->id))->toBeNull()
        ->and(Comment::find($unrelated->id))->not->toBeNull();
});
