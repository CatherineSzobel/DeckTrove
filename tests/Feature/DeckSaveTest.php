<?php

use App\Models\Card;
use App\Models\Deck;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const DARK_MAGICIAN = '46986414';
const BLUE_EYES = '89631139';
const NUMBER_39 = '84013237';

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => (int) DARK_MAGICIAN, 'name' => 'Dark Magician'])->create();
    YugiohCard::factory()->fromCard(['id' => (int) BLUE_EYES, 'name' => 'Blue-Eyes White Dragon', 'race' => 'Dragon'])->create();
    YugiohCard::factory()->fromCard(['id' => (int) NUMBER_39, 'name' => 'Number 39: Utopia', 'type' => 'XYZ Monster'])->create();
});

function cardsPayload(array $zones): string
{
    return json_encode(collect($zones)->map(
        fn (array $ids) => collect($ids)->map(fn ($id) => ['id' => $id])->all()
    )->all());
}

test('guests cannot save a deck', function () {
    $this->post(route('decks.store', 'yugioh'), ['cards' => cardsPayload(['main' => [DARK_MAGICIAN]])])
        ->assertRedirect(route('login'));

    expect(Deck::count())->toBe(0);
});

test('a deck is saved with card counts per zone', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.store', 'yugioh'), [
        'deck_title' => 'Spellcasters',
        'cards' => cardsPayload(['main' => [DARK_MAGICIAN, DARK_MAGICIAN, BLUE_EYES], 'side' => [BLUE_EYES]]),
    ])->assertRedirect(route('decks'));

    $deck = $user->decks()->sole();
    expect($deck)->name->toBe('Spellcasters')->game->toBe('yugioh');
    expect($deck->deckCards()->where('zone', 'main')->sum('count'))->toBe(3);
    expect($deck->deckCards()->where('zone', 'side')->sum('count'))->toBe(1);
});

test('card names and images come from the card data, not from the client', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.store', 'yugioh'), [
        'cards' => json_encode(['main' => [[
            'id' => DARK_MAGICIAN,
            'name' => 'Totally Legit Card',
            'image' => 'https://evil.example/x.png',
        ]]]),
    ])->assertRedirect();

    $card = Card::where('external_id', DARK_MAGICIAN)->sole();
    expect($card->name)->toBe('Dark Magician');
    expect($card->image_url)->not->toContain('evil.example');
});

test('unknown card ids are rejected', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('decks.store', 'yugioh'), ['cards' => cardsPayload(['main' => ['999999999999']])])
        ->assertSessionHasErrors('cards');

    expect(Deck::count())->toBe(0);
});

test('zones that the game does not have are rejected', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('decks.store', 'magic'), ['cards' => cardsPayload(['extra' => [DARK_MAGICIAN]])])
        ->assertSessionHasErrors('cards');

    expect(Deck::count())->toBe(0);
});

test('copy limits are enforced on the server', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('decks.store', 'yugioh'), ['cards' => cardsPayload(['main' => array_fill(0, 4, DARK_MAGICIAN)])])
        ->assertSessionHasErrors('cards');

    expect(Deck::count())->toBe(0);
});

test('the deck image must be an https url', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('decks.store', 'yugioh'), [
            'cards' => cardsPayload(['main' => [DARK_MAGICIAN]]),
            'image' => 'javascript:alert(1)',
        ])
        ->assertSessionHasErrors('image');
});

test('decks below the minimum size are saved as private', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.store', 'yugioh'), [
        'cards' => cardsPayload(['main' => [DARK_MAGICIAN]]),
        'is_public' => '1',
    ])->assertRedirect();

    expect($user->decks()->sole()->is_public)->toBeFalse();
});

test('owners can update deck details and cards', function () {
    $deck = Deck::factory()->create(['game' => 'yugioh', 'name' => 'Old']);

    $this->actingAs($deck->user)->patch(route('decks.update', $deck), [
        'deck_title' => 'New name',
        'cards' => cardsPayload(['main' => [BLUE_EYES, BLUE_EYES]]),
    ])->assertRedirect(route('decks.show', $deck));

    $deck->refresh();
    expect($deck->name)->toBe('New name');
    expect($deck->deckCards()->sum('count'))->toBe(2);
});

test('extra deck monsters must go in the extra deck', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('decks.store', 'yugioh'), ['cards' => cardsPayload(['main' => [NUMBER_39]])])
        ->assertSessionHasErrors('cards');

    $this->actingAs($user)
        ->post(route('decks.store', 'yugioh'), ['cards' => cardsPayload(['extra' => [NUMBER_39], 'side' => [NUMBER_39]])])
        ->assertSessionHasNoErrors();
});
