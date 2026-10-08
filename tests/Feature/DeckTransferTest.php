<?php

use App\Models\Card;
use App\Models\Deck;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

const BOLT = '00000000-0000-0000-0000-00000000b017';
const FOREST = '00000000-0000-0000-0000-0000000f0e57';

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician'])->create();
    YugiohCard::factory()->fromCard(['id' => 89631139, 'name' => 'Blue-Eyes White Dragon'])->create();
    YugiohCard::factory()->fromCard(['id' => 84013237, 'name' => 'Number 39: Utopia', 'type' => 'XYZ Monster'])->create();
});

/**
 * Fakes Scryfall's /cards/collection: answers each identifier from a tiny catalogue.
 */
function fakeScryfallCollection(): void
{
    $catalogue = [
        ['id' => BOLT, 'name' => 'Lightning Bolt', 'set' => '2x2', 'collector_number' => '117', 'type_line' => 'Instant'],
        ['id' => FOREST, 'name' => 'Forest', 'set' => 'dmu', 'collector_number' => '277', 'type_line' => 'Basic Land — Forest'],
    ];

    Http::fake(['api.scryfall.com/cards/collection' => function ($request) use ($catalogue) {
        $found = [];
        $notFound = [];

        foreach ($request['identifiers'] as $identifier) {
            $match = collect($catalogue)->first(fn ($card) => (isset($identifier['id']) && $card['id'] === $identifier['id'])
                || (isset($identifier['name']) && strcasecmp($card['name'], $identifier['name']) === 0)
                || (isset($identifier['set']) && $card['set'] === $identifier['set'] && $card['collector_number'] === $identifier['collector_number']));

            $match ? $found[] = $match : $notFound[] = $identifier;
        }

        return Http::response(['data' => $found, 'not_found' => $notFound]);
    }]);
}

function deckWith(string $game, array $zones, array $attributes = []): Deck
{
    $deck = Deck::factory()->create(['game' => $game, 'name' => 'My Deck'] + $attributes);

    foreach ($zones as $zone => $cards) {
        foreach ($cards as $externalId => $count) {
            $card = Card::firstOrCreate(['game' => $game, 'external_id' => (string) $externalId], ['name' => 'Card']);
            $deck->deckCards()->create(['card_id' => $card->id, 'zone' => $zone, 'count' => $count]);
        }
    }

    return $deck;
}

// --- Export ------------------------------------------------------------------------------------

test('yugioh decks export as a ydk file', function () {
    $deck = deckWith('yugioh', ['main' => [46986414 => 2, 89631139 => 1], 'extra' => [84013237 => 1]], ['is_public' => true]);

    $response = $this->get(route('decks.export', $deck))->assertOk();

    expect($response->headers->get('content-disposition'))->toContain('my-deck.ydk');
    expect($response->streamedContent())->toBe(implode("\n", [
        '#created by DeckTrove',
        '#main',
        '46986414',
        '46986414',
        '89631139',
        '#extra',
        '84013237',
        '!side',
        '',
    ]));
});

test('magic decks export as an arena style list', function () {
    fakeScryfallCollection();
    $deck = deckWith('magic', ['main' => [BOLT => 4, FOREST => 20], 'side' => [BOLT => 2]], ['is_public' => true]);

    $response = $this->get(route('decks.export', $deck))->assertOk();

    expect($response->headers->get('content-disposition'))->toContain('my-deck.txt');
    expect($response->streamedContent())->toBe(implode("\n", [
        'Deck',
        '4 Lightning Bolt (2X2) 117',
        '20 Forest (DMU) 277',
        '',
        'Sideboard',
        '2 Lightning Bolt (2X2) 117',
        '',
    ]));
});

test('private decks can only be exported by their owner', function () {
    $deck = deckWith('yugioh', ['main' => [46986414 => 1]]);

    $this->get(route('decks.export', $deck))->assertNotFound();
    $this->actingAs($deck->user)->get(route('decks.export', $deck))->assertOk();
});

// --- Import ------------------------------------------------------------------------------------

test('a ydk file imports as a new private deck', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->createWithContent('Spellcasters.ydk', "#created by EDOPro\n#main\n46986414\n46986414\n89631139\n#extra\n84013237\n!side\n89631139\n");

    $this->actingAs($user)->post(route('decks.import', 'yugioh'), ['file' => $file])
        ->assertRedirect(route('decks.edit', $deck = $user->decks()->sole()));

    expect($deck)->name->toBe('Spellcasters')->is_public->toBeFalse();
    expect($deck->deckCards()->where('zone', 'main')->sum('count'))->toBe(3);
    expect($deck->deckCards()->where('zone', 'extra')->sum('count'))->toBe(1);
    expect($deck->deckCards()->where('zone', 'side')->sum('count'))->toBe(1);
});

test('unknown cards are skipped and reported on import', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.import', 'yugioh'), ['list' => "#main\n46986414\n12345\n"])
        ->assertRedirect()
        ->assertSessionHas('warning', fn ($message) => str_contains($message, '12345'));

    expect($user->decks()->sole()->deckCards()->sum('count'))->toBe(1);
});

test('imports must follow the deck rules', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.import', 'yugioh'), ['list' => "#main\n".str_repeat("46986414\n", 4)])
        ->assertSessionHasErrors('cards');

    expect($user->decks()->count())->toBe(0);
});

test('a pasted magic list imports by name or by set and number', function () {
    fakeScryfallCollection();
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.import', 'magic'), ['list' => "Deck\n4 Lightning Bolt (2X2) 117\n20 forest\n1 Not A Real Card\n\nSideboard\n2 Forest\n"])
        ->assertRedirect()
        ->assertSessionHas('warning', fn ($message) => str_contains($message, 'Not A Real Card'));

    $deck = $user->decks()->sole();
    expect($deck->game)->toBe('magic');
    expect($deck->deckCards()->where('zone', 'main')->sum('count'))->toBe(24);
    expect($deck->deckCards()->where('zone', 'side')->sum('count'))->toBe(2);
});

test('mtgo style lists put cards after the blank line in the sideboard', function () {
    fakeScryfallCollection();
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.import', 'magic'), ['list' => "4 Lightning Bolt\n\n2 Forest\n"]);

    $deck = $user->decks()->sole();
    expect($deck->deckCards()->where('zone', 'main')->sum('count'))->toBe(4);
    expect($deck->deckCards()->where('zone', 'side')->sum('count'))->toBe(2);
});

test('an import needs a file or a list', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('decks.import', 'yugioh'), [])
        ->assertSessionHasErrors('list');
});

test('guests cannot import decks', function () {
    $this->post(route('decks.import', 'yugioh'), ['list' => "#main\n46986414\n"])->assertRedirect(route('login'));
});

// --- Copy --------------------------------------------------------------------------------------

test('public decks can be copied into your own private decks', function () {
    $original = deckWith('yugioh', ['main' => [46986414 => 3], 'extra' => [84013237 => 1]], ['is_public' => true, 'description' => 'Spells']);
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('decks.copy', $original))
        ->assertRedirect(route('decks.edit', $copy = $user->decks()->sole()));

    expect($copy)
        ->name->toBe('Copy of My Deck')
        ->description->toBe('Spells')
        ->is_public->toBeFalse();
    expect($copy->deckCards()->sum('count'))->toBe(4);

    // The copy is independent of the original.
    $copy->deckCards()->delete();
    expect($original->deckCards()->sum('count'))->toBe(4);
});

test('private decks of other users cannot be copied', function () {
    $deck = deckWith('yugioh', ['main' => [46986414 => 1]]);

    $this->actingAs(User::factory()->create())->post(route('decks.copy', $deck))->assertNotFound();
});

test('the deck page shows export and copy buttons', function () {
    $deck = deckWith('yugioh', ['main' => [46986414 => 1]], ['is_public' => true]);

    $this->get(route('decks.show', $deck))->assertSee(route('decks.export', $deck))->assertDontSee(route('decks.copy', $deck));
    $this->actingAs(User::factory()->create())->get(route('decks.show', $deck))->assertSee(route('decks.copy', $deck));
});
