<?php

use App\Models\Deck;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard([
        'id' => 10000001, 'name' => 'Pot of Greed', 'type' => 'Spell Card', 'race' => 'Normal',
        'banlist_info' => ['ban_tcg' => 'Forbidden'],
    ])->create();
    YugiohCard::factory()->fromCard([
        'id' => 10000002, 'name' => 'Graceful Charity', 'type' => 'Spell Card', 'race' => 'Normal',
        'banlist_info' => ['ban_tcg' => 'Limited', 'ban_ocg' => 'Semi-Limited'],
    ])->create();
    YugiohCard::factory()->fromCard(['id' => 10000003, 'name' => 'Dark Magician'])->create();
});

/** The deck builder's `cards` field: {"main": [{"id": "..."}, ...], ...}. */
function formatPayload(array $zones): string
{
    return json_encode(collect($zones)->map(
        fn (array $ids) => collect($ids)->map(fn ($id) => ['id' => $id])->all()
    )->all());
}

// --- Storing the format ------------------------------------------------------------------------

test('a deck is saved with its format', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.store', 'yugioh'), [
        'format' => 'tcg',
        'cards' => formatPayload(['main' => ['10000003']]),
    ])->assertSessionHasNoErrors();

    expect($user->decks()->sole()->format)->toBe('tcg');
});

test('decks without a format are casual', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.store', 'yugioh'), ['cards' => formatPayload(['main' => ['10000003']])]);
    $this->actingAs($user)->post(route('decks.store', 'yugioh'), ['format' => '', 'cards' => formatPayload(['main' => ['10000003']])]);

    expect($user->decks()->pluck('format')->all())->toBe([null, null]);
});

test('formats the game does not have are rejected', function (string $format) {
    $this->actingAs(User::factory()->create())
        ->post(route('decks.store', 'yugioh'), ['format' => $format, 'cards' => formatPayload(['main' => ['10000003']])])
        ->assertSessionHasErrors('format');

    expect(Deck::count())->toBe(0);
})->with(['a magic format' => 'standard', 'goat' => 'goat', 'wrong case' => 'TCG']);

test('saving without a format keeps the deck\'s format', function () {
    $deck = Deck::factory()->create(['game' => 'yugioh', 'format' => 'tcg']);

    $this->actingAs($deck->user)->patch(route('decks.update', $deck), ['cards' => formatPayload(['main' => ['10000003']])])
        ->assertSessionHasNoErrors();
    expect($deck->refresh()->format)->toBe('tcg');

    $this->actingAs($deck->user)->patch(route('decks.update', $deck), ['format' => '', 'cards' => formatPayload(['main' => ['10000003']])])
        ->assertSessionHasNoErrors();
    expect($deck->refresh()->format)->toBeNull();
});

test('copies of a deck keep its format', function () {
    $original = Deck::factory()->public()->create(['game' => 'yugioh', 'format' => 'ocg']);
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.copy', $original))->assertRedirect();

    expect($user->decks()->sole()->format)->toBe('ocg');
});

test('imported decks are casual', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.import', 'yugioh'), ['list' => "#main\n10000003\n"])->assertRedirect();

    expect($user->decks()->sole()->format)->toBeNull();
});
