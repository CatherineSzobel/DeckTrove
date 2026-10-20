<?php

use App\Models\Comment;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('deleting a deck deletes its comments, not others', function () {
    $deck = Deck::factory()->public()->create();
    $other = Deck::factory()->public()->create();
    Comment::factory()->on($deck)->count(2)->create();
    $kept = [Comment::factory()->on($other)->create()->id, Comment::factory()->on($deck->user)->create()->id];

    $this->actingAs($deck->user)->delete(route('decks.destroy', $deck))->assertRedirect(route('decks.mine'));

    expect(Comment::pluck('id')->sort()->values()->all())->toBe($kept);
});

test('deleting a deck model anywhere deletes its comments', function () {
    $deck = Deck::factory()->public()->create();
    Comment::factory()->on($deck)->count(2)->create();
    $kept = Comment::factory()->on($deck->user)->create();

    $deck->delete();

    expect(Comment::pluck('id')->all())->toBe([$kept->id]);
});

test('bulk deleting decks deletes their comments, other bulk actions keep them', function () {
    $user = User::factory()->create();
    [$a, $b, $c] = Deck::factory()->for($user)->public()->count(3)->create()->all();
    foreach ([$a, $b, $c] as $deck) {
        Comment::factory()->on($deck)->create();
    }

    $this->actingAs($user)->post(route('decks.bulk'), ['decks' => [$c->id], 'action' => 'private']);
    expect($c->comments()->count())->toBe(1);

    $this->actingAs($user)->post(route('decks.bulk'), ['decks' => [$a->id, $b->id], 'action' => 'delete']);

    expect(Comment::where('commentable_type', 'deck')->pluck('commentable_id')->all())->toBe([$c->id]);
});

test('deleting an account deletes the comments on its decks', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->for($user)->public()->create();
    $otherDeck = Deck::factory()->public()->create();
    Comment::factory()->on($deck)->count(2)->create();
    $kept = Comment::factory()->on($otherDeck)->create();

    $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect(route('index'));

    expect(Comment::pluck('id')->all())->toBe([$kept->id]);
});
