<?php

use App\Models\Deck;
use App\Models\DeckLike;
use App\Models\User;
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

test('a user can like and unlike a public deck', function () {
    $deck = Deck::factory()->public()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('public-deck'))
        ->post(route('decks.like', $deck))
        ->assertRedirect(route('public-deck'));

    expect($deck->likes()->where('user_id', $user->id)->exists())->toBeTrue();

    $this->actingAs($user)
        ->from(route('public-deck'))
        ->delete(route('decks.unlike', $deck))
        ->assertRedirect(route('public-deck'));

    expect($deck->likes()->exists())->toBeFalse();
});

test('without a previous page, liking redirects to the deck', function () {
    $deck = Deck::factory()->public()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('decks.like', $deck))
        ->assertRedirect(route('decks.show', $deck));
});

test("JSON requests get the like state and the deck's total like count", function () {
    $deck = Deck::factory()->public()->create();
    DeckLike::factory()->for($deck)->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('decks.like', $deck))
        ->assertOk()
        ->assertExactJson(['liked' => true, 'likes_count' => 2]);

    $this->actingAs($user)
        ->deleteJson(route('decks.unlike', $deck))
        ->assertOk()
        ->assertExactJson(['liked' => false, 'likes_count' => 1]);
});

test('liking twice keeps one like, and unliking without a like is fine', function () {
    $deck = Deck::factory()->public()->create();
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('decks.like', $deck))->assertOk();
    $this->actingAs($user)->postJson(route('decks.like', $deck))->assertOk()->assertJsonPath('likes_count', 1);

    expect($deck->likes()->count())->toBe(1);

    $this->actingAs($user)->deleteJson(route('decks.unlike', $deck))->assertOk();
    $this->actingAs($user)->deleteJson(route('decks.unlike', $deck))->assertOk()->assertJsonPath('likes_count', 0);
});

test('you cannot like your own deck', function () {
    $deck = Deck::factory()->public()->create();

    $this->actingAs($deck->user)->post(route('decks.like', $deck))->assertForbidden();
    $this->actingAs($deck->user)
        ->postJson(route('decks.like', $deck))
        ->assertForbidden()
        ->assertJsonPath('message', "You can't like your own deck.");

    expect($deck->likes()->exists())->toBeFalse();
});

test("someone else's private deck cannot be liked or unliked", function () {
    $deck = Deck::factory()->create(['is_public' => false]);
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.like', $deck))->assertNotFound();
    $this->actingAs($user)->delete(route('decks.unlike', $deck))->assertNotFound();

    expect($deck->likes()->exists())->toBeFalse();
});

test('guests are sent to log in', function () {
    $deck = Deck::factory()->public()->create();

    $this->post(route('decks.like', $deck))->assertRedirect(route('login'));
    $this->postJson(route('decks.like', $deck))->assertUnauthorized();
    $this->deleteJson(route('decks.unlike', $deck))->assertUnauthorized();
});

test('likes have their own rate limit, separate from imports', function () {
    $deck = Deck::factory()->public()->create();
    $user = User::factory()->create();

    foreach (range(1, 10) as $attempt) {
        $this->actingAs($user)->postJson(route('decks.like', $deck))->assertOk();
    }

    // Imports allow 10 a minute; liking must not use up that budget.
    expect($this->actingAs($user)->post(route('decks.import', 'yugioh'))->status())->not->toBe(429);

    foreach (range(11, 30) as $attempt) {
        $this->actingAs($user)->postJson(route('decks.like', $deck))->assertOk();
    }

    $this->actingAs($user)->postJson(route('decks.like', $deck))->assertTooManyRequests();
});
