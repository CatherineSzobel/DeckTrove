<?php

use App\Models\Card;
use App\Models\Deck;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * A public Yu-Gi-Oh! deck. Each entry is [raw card overrides, zone, copies]; a card with 'lost' => true only exists
 * as a stored deck card, as if the source no longer had it.
 */
function deckPageDeck(array $entries, ?string $format = null, array $attributes = []): Deck
{
    $deck = Deck::factory()->public()->create(['game' => 'yugioh', 'format' => $format] + $attributes);

    foreach ($entries as [$overrides, $zone, $copies]) {
        if (! ($overrides['lost'] ?? false)) {
            YugiohCard::factory()->fromCard($overrides)->create();
        }

        $card = Card::firstOrCreate(['game' => 'yugioh', 'external_id' => (string) $overrides['id']], ['name' => $overrides['name']]);
        $deck->deckCards()->create(['card_id' => $card->id, 'zone' => $zone, 'count' => $copies]);
    }

    return $deck;
}

function darkMagicianEntry(int $copies = 3): array
{
    return [['id' => 46986414, 'name' => 'Dark Magician', 'card_prices' => [['cardmarket_price' => '0.10']]], 'main', $copies];
}

function potOfGreedEntry(): array
{
    return [['id' => 55144522, 'name' => 'Pot of Greed', 'type' => 'Spell Card', 'race' => 'Normal', 'banlist_info' => ['ban_tcg' => 'Forbidden']], 'main', 1];
}

test('a casual deck shows the casual badge', function () {
    $this->get(route('decks.show', deckPageDeck([darkMagicianEntry()])))
        ->assertOk()->assertSee('data-format-badge="casual"', false)->assertDontSee('Format problems');
});

test('a deck that fits its format shows a green badge', function () {
    $this->get(route('decks.show', deckPageDeck([darkMagicianEntry()], 'tcg')))
        ->assertOk()->assertSee('data-format-badge="legal"', false)->assertSee('Legal in TCG Advanced.');
});

test('a deck that breaks its format shows the problems and marks the cards', function () {
    $this->get(route('decks.show', deckPageDeck([darkMagicianEntry(), potOfGreedEntry()], 'tcg')))
        ->assertOk()
        ->assertSee('data-format-badge="broken"', false)
        ->assertSee('This deck no longer fits TCG Advanced')
        ->assertSee('Pot of Greed is not allowed in TCG Advanced.')
        ->assertSee('outline-danger', false);
});

test('only the owner gets the edit link in the problems box', function () {
    $deck = deckPageDeck([potOfGreedEntry()], 'tcg');

    $this->actingAs($deck->user)->get(route('decks.show', $deck))->assertSee('Edit the deck</a> to fix it', false);
    $this->actingAs(User::factory()->create())->get(route('decks.show', $deck))
        ->assertSee('The owner can fix it')->assertDontSee(route('decks.edit', $deck));
});

test('the header shows the card type chips and the deck price', function () {
    $this->get(route('decks.show', deckPageDeck([darkMagicianEntry()])))
        ->assertOk()
        ->assertSeeInOrder(['3', 'monsters'])
        ->assertSee('data-chip="price"', false)
        ->assertSee('€0.30')
        ->assertSeeText('3 cards · €0.30');
});

test('the stats sidebar shows a chart per stats group', function () {
    $this->get(route('decks.show', deckPageDeck([darkMagicianEntry()])))
        ->assertOk()
        ->assertSee('aria-label="Deck stats"', false)
        ->assertSeeInOrder(['Card types', 'Level / Rank', 'Attribute']);
});

test('the owner can edit, others can copy, everyone can export', function () {
    $deck = deckPageDeck([darkMagicianEntry()]);

    $this->actingAs($deck->user)->get(route('decks.show', $deck))
        ->assertSee(route('decks.edit', $deck))->assertSee(route('decks.export', $deck))->assertDontSee(route('decks.copy', $deck));
    $this->actingAs(User::factory()->create())->get(route('decks.show', $deck))
        ->assertSee(route('decks.copy', $deck))->assertSee(route('decks.export', $deck))->assertDontSee(route('decks.edit', $deck));
});

test('card previews use the shared panel that stays inside the window', function () {
    $this->get(route('decks.show', deckPageDeck([darkMagicianEntry()])))
        ->assertOk()
        ->assertSee('data-card-preview', false)
        ->assertSee('x-on:mouseenter="showPreview(card, $el)"', false)
        ->assertSee('x-data="cardPreview"', false)
        ->assertDontSee('group-hover:flex', false);
});

test('a card the source no longer has still shows on the deck page', function () {
    $this->get(route('decks.show', deckPageDeck([[['id' => 99999999, 'name' => 'Lost Card', 'lost' => true], 'main', 1]])))
        ->assertOk()->assertSee('alt="Lost Card"', false);
});
