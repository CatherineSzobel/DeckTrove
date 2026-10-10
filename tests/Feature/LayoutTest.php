<?php

use App\Models\Deck;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician'])->create();
});

// --- Tokens and fonts --------------------------------------------------------------------------

test('pages use the new colours and self-hosted fonts', function () {
    $this->get(route('public-deck'))->assertOk()
        ->assertSee('bg-canvas', false)
        ->assertDontSee('fonts.googleapis.com', false);

    $this->get('/')->assertOk()->assertDontSee('fonts.googleapis.com', false);
});

// --- Header ------------------------------------------------------------------------------------

test('guests see the public links and the account buttons', function () {
    $html = $this->get(route('public-deck'))->assertOk()->getContent();

    expect($html)
        ->toContain('data-nav="cards"')
        ->toContain('data-nav="packs"')
        ->toContain('data-nav="public-decks"')
        ->toContain('href="'.route('login').'"')
        ->toContain('Create account')
        ->not->toContain('data-nav="deck-builder"')
        ->not->toContain('id="userMenu"');
});

test('logged in users see the deck builder and the account menu', function () {
    $user = User::factory()->create(['username' => 'catherine']);

    $html = $this->actingAs($user)->get(route('decks'))->assertOk()->getContent();

    expect($html)
        ->toContain('data-nav="deck-builder"')
        ->toContain('id="userMenu"')
        ->toContain('href="'.route('dashboard').'"')
        ->toContain('href="'.route('profile').'"')
        ->toContain('action="'.route('logout').'"')
        ->toContain('>C</span>')
        ->not->toContain('Create account');
});

test('the account button shows the avatar when there is one', function () {
    $user = User::factory()->create(['username' => 'catherine', 'avatar' => 'avatars/catherine.png']);

    $html = $this->actingAs($user)->get(route('decks'))->assertOk()->getContent();

    expect($html)->toContain('avatars/catherine.png')->not->toContain('>C</span>');
});

test('the initial is uppercase, accents included', function () {
    $this->actingAs(User::factory()->create(['username' => 'émilie']))
        ->get(route('decks'))->assertOk()->assertSee('>É</span>', false);
});

test('header links follow the current game', function () {
    $this->get(route('cards.index', 'yugioh'))
        ->assertSee('href="'.route('packs.index', 'yugioh').'" data-nav="packs"', false);
});

test('header links use the remembered game on other pages', function () {
    $this->withSession(['series' => 'magic'])->get(route('public-deck'))
        ->assertSee('href="'.route('packs.index', 'magic').'" data-nav="packs"', false);
});

test('header links fall back to the first game', function (?string $remembered) {
    $this->withSession(array_filter(['series' => $remembered]))->get(route('public-deck'))
        ->assertSee('href="'.route('packs.index', array_key_first(config('series'))).'" data-nav="packs"', false);
})->with(['nothing remembered' => [null], 'a game that no longer exists' => ['pokemon']]);

test('the current section is marked in the header', function () {
    expect($this->get(route('cards.index', 'yugioh'))->getContent())
        ->toMatch('/data-nav="cards"\s+aria-current="page"/');

    expect($this->get(route('public-deck'))->getContent())
        ->toMatch('/data-nav="public-decks"\s+aria-current="page"/')
        ->not->toMatch('/data-nav="cards"\s+aria-current="page"/');

    $deck = Deck::factory()->create(['game' => 'yugioh']);
    expect($this->actingAs($deck->user)->get(route('decks.edit', $deck))->getContent())
        ->toMatch('/data-nav="deck-builder"\s+aria-current="page"/');
});

test('a skip link jumps to the main content', function () {
    $this->get(route('public-deck'))
        ->assertSee('href="#content"', false)
        ->assertSee('<main id="content"', false);
});

// --- Game switch -------------------------------------------------------------------------------

test('game pages offer the same section in the other game', function (string $route, array $params, string $target) {
    $html = $this->get(route($route, ['yugioh', ...$params]))->assertOk()->getContent();

    expect($html)
        ->toContain('data-game-switch')
        ->toContain('href="'.route($target, 'magic').'" data-game="magic"')
        ->toMatch('/data-game="yugioh"\s+aria-current="page"/');
})->with([
    'cards' => ['cards.index', [], 'cards.index'],
    'a card' => ['cards.show', ['46986414'], 'cards.index'],
    'packs' => ['packs.index', [], 'packs.index'],
    'deck builder' => ['decks.builder', [], 'decks.builder'],
]);

test('pages without a game have no game switch', function () {
    $this->get(route('public-deck'))->assertOk()->assertDontSee('data-game-switch', false);

    $deck = Deck::factory()->create(['game' => 'yugioh']);
    $this->actingAs($deck->user)->get(route('decks'))->assertOk()->assertDontSee('data-game-switch', false);
    $this->actingAs($deck->user)->get(route('decks.edit', $deck))->assertOk()->assertDontSee('data-game-switch', false);
});

test('a page can place the game switch itself', function () {
    Route::middleware('web')->get('/{series}/layout-default', fn () => Blade::render('<x-layout>Page body</x-layout>'));
    Route::middleware('web')->get('/{series}/layout-without-switch', fn () => Blade::render('<x-layout :game-switch="false">Page body</x-layout>'));

    $this->get('/yugioh/layout-default')->assertOk()->assertSee('data-game-switch', false);
    $this->get('/yugioh/layout-without-switch')->assertOk()->assertSee('Page body')->assertDontSee('data-game-switch', false);
});
