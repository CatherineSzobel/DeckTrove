<?php

use App\Models\Card;
use App\Models\Deck;
use App\Models\User;
use App\Models\YugiohCard;
use App\Services\DeckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician'])->create();
    YugiohCard::factory()->fromCard(['id' => 89631139, 'name' => 'Blue-Eyes White Dragon'])->create();
    YugiohCard::factory()->fromCard(['id' => 84013237, 'name' => 'Number 39: Utopia', 'type' => 'XYZ Monster'])->create();
});

/** A deck that was imported earlier: 3 Dark Magician, a format, a cover and a description. */
function reimportDeck(User $user, array $attributes = []): Deck
{
    $deck = Deck::factory()->for($user)->create([
        'game' => 'yugioh', 'name' => 'Wrong Deck', 'description' => 'My notes', 'format' => 'tcg',
        'image' => 'https://images.ygoprodeck.com/images/cards/46986414.jpg', 'is_public' => false,
    ] + $attributes);
    $card = Card::create(['game' => 'yugioh', 'external_id' => '46986414', 'name' => 'Dark Magician']);
    $deck->deckCards()->create(['card_id' => $card->id, 'zone' => 'main', 'count' => 3]);

    return $deck;
}

test('importing on the edit page replaces the deck cards', function () {
    $user = User::factory()->create();
    $deck = reimportDeck($user);

    $this->actingAs($user)->post(route('decks.reimport', $deck), ['list' => "#main\n89631139\n89631139\n#extra\n84013237\n"])
        ->assertRedirect(route('decks.edit', $deck))
        ->assertSessionHas('success');

    expect(app(DeckService::class)->zoneCounts($deck))->toBe(['main' => ['89631139' => 2], 'extra' => ['84013237' => 1]]);

    $deck->refresh();
    expect($deck->name)->toBe('Wrong Deck')
        ->and($deck->description)->toBe('My notes')
        ->and($deck->format)->toBeNull()
        ->and($deck->image)->toBeNull();
    expect(Deck::count())->toBe(1);
});

test('a reimported file renames the deck after the file', function () {
    $user = User::factory()->create();
    $deck = reimportDeck($user);
    $file = UploadedFile::fake()->createWithContent('Right Deck.ydk', "#main\n89631139\n");

    $this->actingAs($user)->post(route('decks.reimport', $deck), ['file' => $file])->assertSessionHasNoErrors();

    expect($deck->refresh()->name)->toBe('Right Deck');
});

test('a public deck stays public only when the new list is big enough', function () {
    $user = User::factory()->create();
    $deck = reimportDeck($user, ['is_public' => true]);

    $this->actingAs($user)->post(route('decks.reimport', $deck), ['list' => "#main\n89631139\n"]);

    expect($deck->refresh()->is_public)->toBeFalse();
});

test('unknown cards are skipped and reported when replacing', function () {
    $user = User::factory()->create();
    $deck = reimportDeck($user);

    $this->actingAs($user)->post(route('decks.reimport', $deck), ['list' => "#main\n89631139\n12345\n"])
        ->assertSessionHas('warning', fn ($message) => str_contains($message, '12345'));

    expect(app(DeckService::class)->zoneCounts($deck))->toBe(['main' => ['89631139' => 1]]);
});

test('a list that breaks the deck rules leaves the deck as it was', function () {
    $user = User::factory()->create();
    $deck = reimportDeck($user);

    $this->actingAs($user)->post(route('decks.reimport', $deck), ['list' => "#main\n".str_repeat("89631139\n", 4)])
        ->assertSessionHasErrors('cards');

    expect(app(DeckService::class)->zoneCounts($deck))->toBe(['main' => ['46986414' => 3]]);
});

test('only the owner can replace a deck by importing', function () {
    $deck = reimportDeck(User::factory()->create());

    // Guest first: actingAs() stays logged in for the rest of the test.
    $this->post(route('decks.reimport', $deck), ['list' => "#main\n89631139\n"])->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->post(route('decks.reimport', $deck), ['list' => "#main\n89631139\n"])->assertForbidden();

    expect(app(DeckService::class)->zoneCounts($deck))->toBe(['main' => ['46986414' => 3]]);
});

test('the edit page offers to replace the deck with an import', function () {
    $user = User::factory()->create();
    $deck = reimportDeck($user);

    $this->actingAs($user)->get(route('decks.edit', $deck))
        ->assertSee('data-import-toggle', false)
        ->assertSee('action="'.route('decks.reimport', $deck).'"', false)
        ->assertSee('Replace this deck')
        ->assertSee('This replaces all cards in this deck.');
});
