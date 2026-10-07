<?php

use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician', 'archetype' => 'Dark Magician'])->create();
    YugiohCard::factory()->fromCard(['id' => 38033121, 'name' => 'Dark Magician Girl', 'archetype' => 'Dark Magician'])->create();
    YugiohCard::factory()->fromCard(['id' => 89631139, 'name' => 'Blue-Eyes White Dragon', 'race' => 'Dragon'])->create();
});

function fakeScryfall(array $cards = []): void
{
    Http::fake([
        'api.scryfall.com/cards/search*' => $cards
            ? Http::response(['data' => $cards, 'total_cards' => count($cards), 'has_more' => false])
            : Http::response(['object' => 'error', 'code' => 'not_found'], 404),
        'api.scryfall.com/catalog/card-types' => Http::response(['data' => ['Creature', 'Instant']]),
        'api.scryfall.com/sets' => Http::response(['data' => [['code' => 'lea', 'name' => 'Limited Edition Alpha', 'released_at' => '1993-08-05', 'card_count' => 295, 'set_type' => 'core', 'icon_svg_uri' => 'https://svgs.scryfall.io/sets/lea.svg']]]),
        'api.scryfall.com/cards/random' => Http::response(['id' => '00000000-0000-0000-0000-000000000001', 'name' => 'Random Card']),
    ]);
}

test('the homepage loads', function () {
    $this->get('/')->assertOk()->assertSee('Welcome to DeckTrove');
});

test('the yugioh card database lists, searches and filters cards', function () {
    $this->get(route('cards.index', 'yugioh'))->assertOk()->assertSee('Dark Magician')->assertSee('Blue-Eyes White Dragon');

    $this->get(route('cards.index', ['yugioh', 'search' => 'girl']))
        ->assertOk()->assertSee('Dark Magician Girl')->assertDontSee('Blue-Eyes White Dragon');

    // Search ignores case (ILIKE on Postgres).
    $this->get(route('cards.index', ['yugioh', 'search' => 'DARK MAGICIAN GIRL']))
        ->assertOk()->assertSee('Dark Magician Girl')->assertDontSee('Blue-Eyes White Dragon');

    $this->get(route('cards.index', ['yugioh', 'race' => 'Dragon']))
        ->assertOk()->assertSee('Blue-Eyes White Dragon')->assertDontSee('Dark Magician Girl');
});

test('the card database answers AJAX requests with JSON', function () {
    $this->getJson(route('cards.index', ['yugioh', 'search' => 'blue']), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertJsonStructure(['html', 'count'])
        ->assertJsonPath('html', fn ($html) => str_contains($html, 'Blue-Eyes White Dragon'));
});

test('unknown view names fall back to the default view', function () {
    // "inner" used to make the cards-inner partial include itself forever.
    $this->get(route('cards.index', ['yugioh', 'view' => 'inner']))->assertOk();
});

test('a yugioh card page shows the card and related cards', function () {
    $this->get(route('cards.show', ['yugioh', '46986414']))
        ->assertOk()->assertSee('Dark Magician')->assertSee('More from this archetype');

    $this->get(route('cards.show', ['yugioh', '123']))->assertNotFound();
});

test('unsupported series return 404', function () {
    $this->get('/pokemon/cards')->assertNotFound();
});

test('the magic card database shows an empty state when scryfall finds nothing', function () {
    fakeScryfall();

    $this->get(route('cards.index', ['magic', 'search' => 'zzzzzz']))->assertOk()->assertSee('No cards found');
});

test('magic cards that do not exist return 404 without calling scryfall', function () {
    Http::fake();

    $this->get(route('cards.show', ['magic', 'abc']))->assertNotFound();
    Http::assertNothingSent();
});

test('pack lists can be searched', function () {
    $this->get(route('packs.index', 'yugioh'))->assertOk();

    fakeScryfall();
    $this->get(route('packs.index', ['magic', 'search' => 'alpha']))->assertOk()->assertSee('Limited Edition Alpha');
    $this->get(route('packs.index', ['magic', 'search' => 'nothing-like-this']))->assertOk()->assertSee('No packs found');
});

test('unknown packs return 404', function () {
    $this->get(route('packs.show', ['yugioh', 'NOPE']))->assertNotFound();
});

test('the dashboard loads for logged in users', function () {
    fakeScryfall();

    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()->assertSee('Your Decks');
});

test('the deck builder loads for guests and returns card results for infinite scroll', function () {
    $this->get(route('decks.builder', 'yugioh'))->assertOk()->assertSee('Login Required');

    $this->get(route('decks.builder', ['yugioh', 'page' => 1]), ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()
        ->assertHeader('X-Total', '3')
        ->assertSee('data-card-id="46986414"', false)
        ->assertDontSee('<html', false);
});
