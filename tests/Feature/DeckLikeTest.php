<?php

use App\Models\DeckLike;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a like belongs to a deck and a user', function () {
    $like = DeckLike::factory()->create();

    expect($like->deck->likes->sole()->is($like))->toBeTrue()
        ->and($like->user->likes->sole()->is($like))->toBeTrue()
        ->and($like->created_at)->not->toBeNull();
});

test('a user can like a deck only once', function () {
    $like = DeckLike::factory()->create();

    DeckLike::factory()->for($like->deck)->for($like->user)->create();
})->throws(UniqueConstraintViolationException::class);

test('deleting a deck or a user removes their likes', function () {
    $deckLike = DeckLike::factory()->create();
    $userLike = DeckLike::factory()->create();

    $deckLike->deck->delete();
    $userLike->user->delete();

    expect(DeckLike::count())->toBe(0);
});
