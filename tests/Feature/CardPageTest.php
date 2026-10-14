<?php

use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

const MAGIC_CARD_ID = '11111111-1111-4111-8111-111111111111';

function fakeScryfallCard(array $card): void
{
    Http::fake([
        'api.scryfall.com/cards/search*' => Http::response(['data' => [], 'total_cards' => 0, 'has_more' => false]),
        'api.scryfall.com/cards/*' => Http::response(['id' => MAGIC_CARD_ID, 'name' => 'Llanowar Elves', 'set' => 'dom', 'set_name' => 'Dominaria', ...$card]),
    ]);
}

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician', 'type' => 'Normal Monster', 'race' => 'Spellcaster', 'archetype' => 'Dark Magician'])->create();
    YugiohCard::factory()->fromCard(['id' => 38033121, 'name' => 'Dark Magician Girl', 'archetype' => 'Dark Magician'])->create();
});

test('the page has a breadcrumb back to the database', function () {
    $this->get(route('cards.show', ['yugioh', '46986414']))->assertOk()
        ->assertSee('<nav aria-label="Breadcrumb"', false)
        ->assertSee('href="'.route('cards.index', 'yugioh').'"', false)
        ->assertSee('Yu-Gi-Oh! cards')
        ->assertSee('<span aria-current="page" class="text-ink">Dark Magician</span>', false);
});

test('the card page has no game switch', function () {
    $this->get(route('cards.show', ['yugioh', '46986414']))->assertOk()->assertDontSee('data-game-switch', false);
});

test('the type line is the eyebrow above the name', function () {
    $this->get(route('cards.show', ['yugioh', '46986414']))
        ->assertSee('<p class="eyebrow">Normal Monster — Spellcaster</p>', false)
        ->assertSee('Dark Magician</h1>', false);
});

test('a monster shows its stats and price in boxes', function () {
    expect($this->get(route('cards.show', ['yugioh', '46986414']))->getContent())
        ->toContain('<dl class="flex flex-wrap gap-3">')
        ->toMatch('/>ATK<\/dt>\s*<dd[^>]*>2500<\/dd>/')
        ->toMatch('/>DEF<\/dt>\s*<dd[^>]*>2100<\/dd>/')
        ->toMatch('/>Price<\/dt>\s*<dd[^>]*>'.preg_quote(config('app.currency.symbol')).'0\.10<\/dd>/');
});

test('a spell only shows its price', function () {
    YugiohCard::factory()->fromCard(['id' => 55144522, 'name' => 'Pot of Greed', 'type' => 'Spell Card', 'race' => 'Normal', 'atk' => null, 'def' => null, 'level' => null])->create();

    $this->get(route('cards.show', ['yugioh', '55144522']))->assertOk()
        ->assertSee('>Price</dt>', false)
        ->assertDontSee('>ATK</dt>', false);
});

test('printings are listed with their pack codes', function () {
    $this->get(route('cards.show', ['yugioh', '46986414']))
        ->assertSee('Printings')
        ->assertSee('href="'.route('packs.show', ['yugioh', 'LOB', 'legend-of-blue-eyes-white-dragon']).'"', false)
        ->assertSee('>LOB</span>', false)
        ->assertDontSee('more-printings', false);
});

test('more than four printings hide the rest behind show more', function () {
    $sets = collect(range(1, 6))->map(fn ($i) => ['set_name' => "Set $i", 'set_code' => "S$i-EN001", 'set_rarity' => 'Common'])->all();
    YugiohCard::factory()->fromCard(['id' => 11111111, 'name' => 'Reprinted', 'card_sets' => $sets])->create();

    $content = $this->get(route('cards.show', ['yugioh', '11111111']))
        ->assertSee('x-data="printList(2)"', false)
        ->assertSee('id="more-printings" data-collapsed="true"', false)
        ->assertSee('Show 2 more')
        ->getContent();

    expect($content)->toMatch('/<button[^>]*js-only[^>]*>Show 2 more<\/button>/')
        ->not->toMatch('/<button[^>]*x-cloak/');
});

test('a card without printings says so', function () {
    YugiohCard::factory()->fromCard(['id' => 22222222, 'name' => 'Unprinted', 'card_sets' => []])->create();

    $this->get(route('cards.show', ['yugioh', '22222222']))->assertSee('No printings listed.');
});

test('open in deck builder searches the card in the builder', function () {
    $this->get(route('cards.show', ['yugioh', '46986414']))
        ->assertSee('href="'.e(route('decks.builder', ['yugioh', 'search' => 'Dark Magician'])).'"', false)
        ->assertSee('Open in deck builder')
        ->assertSee('Back to the database');
});

test('related cards are shown under the series label, and hidden when there are none', function () {
    $this->get(route('cards.show', ['yugioh', '46986414']))
        ->assertSee('More from this archetype')
        ->assertSee('href="'.route('cards.show', ['yugioh', 38033121]).'" aria-label="Dark Magician Girl" class="card-tile"', false);

    YugiohCard::factory()->fromCard(['id' => 33333333, 'name' => 'Loner', 'archetype' => null])->create();
    $this->get(route('cards.show', ['yugioh', '33333333']))->assertDontSee('More from this archetype');
});

test('a magic card shows its type line, mana cost, power and toughness', function () {
    fakeScryfallCard(['mana_cost' => '{G}', 'type_line' => 'Creature — Elf Druid', 'power' => '1', 'toughness' => '1',
        'image_uris' => ['normal' => 'https://cards.scryfall.io/normal/front/elves.jpg', 'small' => 'https://cards.scryfall.io/small/front/elves.jpg']]);

    expect($this->get(route('cards.show', ['magic', MAGIC_CARD_ID]))->assertOk()->getContent())
        ->toContain('<p class="eyebrow">Creature — Elf Druid</p>')
        ->toMatch('/>Mana cost<\/dt>\s*<dd[^>]*>\{G\}<\/dd>/')
        ->toMatch('/>Power<\/dt>\s*<dd[^>]*>1<\/dd>/')
        ->not->toContain('cardFaces(');
});

test('a double-faced card starts on its front and can show the other face', function () {
    fakeScryfallCard(['type_line' => 'Creature — Human Wizard // Creature — Human Insect', 'card_faces' => [
        ['mana_cost' => '{U}', 'image_uris' => ['normal' => 'https://cards.scryfall.io/normal/front/delver.jpg']],
        ['mana_cost' => '', 'image_uris' => ['normal' => 'https://cards.scryfall.io/normal/back/delver.jpg']],
    ]]);

    $content = $this->get(route('cards.show', ['magic', MAGIC_CARD_ID]))->assertOk()
        ->assertSee('x-data="cardFaces(', false)
        ->assertSee('src="https://cards.scryfall.io/normal/front/delver.jpg"', false)
        ->assertSee('Show other face')
        ->getContent();

    expect($content)->toMatch('/<button[^>]*js-only[^>]*>Show other face<\/button>/')
        ->not->toMatch('/<button[^>]*x-cloak/');
});
