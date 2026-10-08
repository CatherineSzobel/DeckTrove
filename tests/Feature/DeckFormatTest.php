<?php

use App\Models\Card;
use App\Models\Deck;
use App\Models\User;
use App\Models\YugiohCard;
use App\Services\DeckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

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

// --- Enforcing format limits -------------------------------------------------------------------

test('forbidden cards cannot be saved in the format, but can in casual', function () {
    $user = User::factory()->create();
    $cards = formatPayload(['main' => ['10000001']]);

    $this->actingAs($user)->post(route('decks.store', 'yugioh'), ['format' => 'tcg', 'cards' => $cards])
        ->assertSessionHasErrors(['cards' => 'Pot of Greed is not allowed in TCG Advanced.']);
    expect(Deck::count())->toBe(0);

    $this->actingAs($user)->post(route('decks.store', 'yugioh'), ['cards' => $cards])->assertSessionHasNoErrors();
    expect(Deck::count())->toBe(1);
});

test('limited cards are capped at one copy', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.store', 'yugioh'), ['format' => 'tcg', 'cards' => formatPayload(['main' => ['10000002', '10000002']])])
        ->assertSessionHasErrors(['cards' => 'You can only have 1 copy of Graceful Charity in TCG Advanced.']);

    $this->actingAs($user)->post(route('decks.store', 'yugioh'), ['format' => 'tcg', 'cards' => formatPayload(['main' => ['10000002']])])
        ->assertSessionHasNoErrors();
});

test('semi-limited cards are capped at two copies', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.store', 'yugioh'), ['format' => 'ocg', 'cards' => formatPayload(['main' => array_fill(0, 3, '10000002')])])
        ->assertSessionHasErrors(['cards' => 'You can only have 2 copies of Graceful Charity in OCG.']);

    $this->actingAs($user)->post(route('decks.store', 'yugioh'), ['format' => 'ocg', 'cards' => formatPayload(['main' => array_fill(0, 2, '10000002')])])
        ->assertSessionHasNoErrors();
});

test('copies in every zone count towards the format limit', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('decks.store', 'yugioh'), ['format' => 'tcg', 'cards' => formatPayload(['main' => ['10000002'], 'side' => ['10000002']])])
        ->assertSessionHasErrors(['cards' => 'You can only have 1 copy of Graceful Charity in TCG Advanced.']);
});

test('the normal copy limit still applies in a format', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('decks.store', 'yugioh'), ['format' => 'tcg', 'cards' => formatPayload(['main' => array_fill(0, 4, '10000003')])])
        ->assertSessionHasErrors(['cards' => 'You can only have 3 copies of Dark Magician.']);
});

/**
 * Fakes Scryfall's /cards/collection with two cards that have legalities.
 */
function fakeScryfallLegalities(): void
{
    $catalogue = [
        [
            'id' => '00000000-0000-0000-0000-00000000f0f0', 'name' => 'Forest', 'type_line' => 'Basic Land — Forest',
            'legalities' => ['standard' => 'legal', 'modern' => 'banned'],
        ],
        [
            'id' => '00000000-0000-0000-0000-00000000b0b0', 'name' => 'Lightning Bolt', 'type_line' => 'Instant', 'mana_cost' => '{R}',
            'legalities' => ['standard' => 'not_legal', 'vintage' => 'restricted'],
        ],
    ];

    Http::fake(['api.scryfall.com/cards/collection' => function ($request) use ($catalogue) {
        $ids = collect($request['identifiers'])->pluck('id');

        return Http::response(['data' => collect($catalogue)->whereIn('id', $ids)->values()->all(), 'not_found' => []]);
    }]);
}

test('a format limit applies to cards that are otherwise unlimited', function () {
    fakeScryfallLegalities();
    $user = User::factory()->create();
    $forests = formatPayload(['main' => array_fill(0, 20, '00000000-0000-0000-0000-00000000f0f0')]);

    $this->actingAs($user)->post(route('decks.store', 'magic'), ['format' => 'modern', 'cards' => $forests])
        ->assertSessionHasErrors(['cards' => 'Forest is not allowed in Modern.']);

    $this->actingAs($user)->post(route('decks.store', 'magic'), ['format' => 'standard', 'cards' => $forests])
        ->assertSessionHasNoErrors();
});

test('magic restricted and not legal cards are enforced', function () {
    fakeScryfallLegalities();
    $user = User::factory()->create();
    $bolts = formatPayload(['main' => array_fill(0, 2, '00000000-0000-0000-0000-00000000b0b0')]);

    $this->actingAs($user)->post(route('decks.store', 'magic'), ['format' => 'vintage', 'cards' => $bolts])
        ->assertSessionHasErrors(['cards' => 'You can only have 1 copy of Lightning Bolt in Vintage.']);

    $this->actingAs($user)->post(route('decks.store', 'magic'), ['format' => 'standard', 'cards' => $bolts])
        ->assertSessionHasErrors(['cards' => 'Lightning Bolt is not allowed in Standard.']);

    $this->actingAs($user)->post(route('decks.store', 'magic'), ['cards' => $bolts])->assertSessionHasNoErrors();
});

// --- Saved decks that no longer fit their format -----------------------------------------------

test('a saved deck reports cards that a banlist change made illegal', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('decks.store', 'yugioh'), [
        'format' => 'tcg',
        'cards' => formatPayload(['main' => ['10000003', '10000003', '10000002']]),
    ])->assertSessionHasNoErrors();
    $deck = $user->decks()->sole();

    expect(app(DeckService::class)->formatErrors($deck))->toBe(['messages' => [], 'card_ids' => []]);

    // The next banlist limits Dark Magician.
    $card = YugiohCard::find(10000003);
    $card->update(['data' => [...$card->data, 'banlist_info' => ['ban_tcg' => 'Limited']]]);

    expect(app(DeckService::class)->formatErrors($deck))->toBe([
        'messages' => ['You can only have 1 copy of Dark Magician in TCG Advanced.'],
        'card_ids' => ['10000003'],
    ]);
});

test('casual decks have no format errors', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('decks.store', 'yugioh'), ['cards' => formatPayload(['main' => ['10000001']])])
        ->assertSessionHasNoErrors();

    expect(app(DeckService::class)->formatErrors($user->decks()->sole()))->toBe(['messages' => [], 'card_ids' => []]);
});

test('cards that are no longer in the card data are skipped', function () {
    $deck = Deck::factory()->create(['game' => 'yugioh', 'format' => 'tcg']);
    $gone = Card::create(['game' => 'yugioh', 'external_id' => '99999999', 'name' => 'Removed Card']);
    $deck->deckCards()->create(['card_id' => $gone->id, 'zone' => 'main', 'count' => 3]);

    expect(app(DeckService::class)->formatErrors($deck))->toBe(['messages' => [], 'card_ids' => []]);
});

test('a format that was removed from the config is named by its key', function () {
    $deck = Deck::factory()->create(['game' => 'yugioh', 'format' => 'tcg']);
    $card = Card::create(['game' => 'yugioh', 'external_id' => '10000001', 'name' => 'Pot of Greed']);
    $deck->deckCards()->create(['card_id' => $card->id, 'zone' => 'main', 'count' => 1]);

    config(['series.yugioh.deck.formats' => ['ocg' => ['label' => 'OCG']]]);

    expect(app(DeckService::class)->formatErrors($deck)['messages'])->toBe(['Pot of Greed is not allowed in tcg.']);
});
