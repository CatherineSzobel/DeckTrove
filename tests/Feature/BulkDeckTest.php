<?php

use App\Models\Deck;
use App\Models\DeckLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function bulk(User $user, array $data)
{
    return test()->actingAs($user)->from(route('profile.edit'))->post(route('decks.bulk'), $data);
}

test('guests are sent to log in', function () {
    $this->post(route('decks.bulk'), ['decks' => [1], 'action' => 'public'])->assertRedirect(route('login'));
});

test('make public changes only the chosen decks', function () {
    $user = User::factory()->create();
    [$a, $b, $c] = Deck::factory()->for($user)->count(3)->create()->all();

    bulk($user, ['decks' => [$a->id, $b->id], 'action' => 'public'])
        ->assertRedirect(route('profile.edit').'#your-decks')
        ->assertSessionHas('success', '2 decks made public.');

    expect($a->fresh()->is_public)->toBeTrue()
        ->and($b->fresh()->is_public)->toBeTrue()
        ->and($c->fresh()->is_public)->toBeFalse();
});

test('make private changes only the chosen decks', function () {
    $user = User::factory()->create();
    [$a, $b] = Deck::factory()->for($user)->public()->count(2)->create()->all();

    bulk($user, ['decks' => [$a->id], 'action' => 'private'])
        ->assertSessionHas('success', '1 deck made private.');

    expect($a->fresh()->is_public)->toBeFalse()
        ->and($b->fresh()->is_public)->toBeTrue();
});

test('delete removes only the chosen decks', function () {
    $user = User::factory()->create();
    [$a, $b, $c] = Deck::factory()->for($user)->count(3)->create()->all();

    bulk($user, ['decks' => [$a->id, $c->id], 'action' => 'delete'])
        ->assertRedirect(route('profile.edit').'#your-decks')
        ->assertSessionHas('success', '2 decks deleted.');

    expect(Deck::find($a->id))->toBeNull()
        ->and(Deck::find($c->id))->toBeNull()
        ->and(Deck::find($b->id))->not->toBeNull();
});

test('a deleted deck takes its likes with it', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->for($user)->public()->create();
    $like = DeckLike::factory()->for($deck)->create();

    bulk($user, ['decks' => [$deck->id], 'action' => 'delete'])->assertSessionHas('success', '1 deck deleted.');

    expect(DeckLike::find($like->id))->toBeNull();
});

test('someone else\'s deck is refused and nothing changes', function () {
    $user = User::factory()->create();
    $mine = Deck::factory()->for($user)->create();
    $theirs = Deck::factory()->create();

    bulk($user, ['decks' => [$mine->id, $theirs->id], 'action' => 'delete'])
        ->assertRedirect(route('profile.edit').'#your-decks')
        ->assertSessionHasErrorsIn('bulkDecks', ['decks' => 'Select only your own decks.']);

    expect(Deck::find($mine->id))->not->toBeNull()
        ->and(Deck::find($theirs->id))->not->toBeNull();
});

test('a deck that does not exist is refused', function () {
    $user = User::factory()->create();

    bulk($user, ['decks' => [999999], 'action' => 'public'])
        ->assertSessionHasErrorsIn('bulkDecks', ['decks' => 'Select only your own decks.']);
});

test('nothing selected asks you to select a deck', function () {
    $user = User::factory()->create();

    bulk($user, ['action' => 'public'])
        ->assertSessionHasErrorsIn('bulkDecks', ['decks' => 'Select at least one deck.']);
    bulk($user, ['decks' => [], 'action' => 'public'])
        ->assertSessionHasErrorsIn('bulkDecks', ['decks' => 'Select at least one deck.']);
});

test('an unknown action is refused', function () {
    $user = User::factory()->create();
    $deck = Deck::factory()->for($user)->create();

    bulk($user, ['decks' => [$deck->id], 'action' => 'archive'])
        ->assertSessionHasErrorsIn('bulkDecks', 'action');
    bulk($user, ['decks' => [$deck->id]])
        ->assertSessionHasErrorsIn('bulkDecks', 'action');

    expect($deck->fresh()->is_public)->toBeFalse();
});
