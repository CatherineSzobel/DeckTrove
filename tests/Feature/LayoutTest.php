<?php

use App\Models\Deck;
use App\Models\User;
use App\Models\YugiohCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
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

// --- Footer and flash messages -----------------------------------------------------------------

test('the footer credits the data and links to the source', function () {
    $this->get(route('public-deck'))->assertOk()
        ->assertSee('DeckTrove is a showcase project built with Laravel, Tailwind CSS, Alpine.js and MySQL.')
        ->assertSee('href="https://github.com/CatherineSzobel/DeckTrove"', false)
        ->assertSee('Source on GitHub')
        ->assertSee('unofficial Fan Content permitted under the Fan Content Policy')
        ->assertSee('not affiliated with or endorsed by Konami')
        ->assertDontSee('Project Overview')
        ->assertDontSee('This is a showcase project.');
});

test('flash messages are shown as notices', function (string $key) {
    $this->withSession([$key => 'Deck saved successfully!'])->get(route('public-deck'))->assertOk()
        ->assertSee('data-flash="'.$key.'"', false)
        ->assertSee('Deck saved successfully!');
})->with(['success', 'warning', 'error']);

test('notices get their look from the notice class and their type, and errors are alerts', function () {
    $html = $this->withSession(['success' => 'Saved.', 'error' => 'Oops.'])->get(route('public-deck'))->getContent();

    expect($html)
        ->toMatch('/role="status" data-flash="success"[^>]*class="notice/')
        ->toMatch('/role="alert" data-flash="error"[^>]*class="notice/')
        ->not->toContain('border-ok-line bg-ok-bg')
        ->not->toContain('border-danger-line bg-danger-bg');
});

test('flash messages are escaped', function () {
    $this->withSession(['success' => '<b>bold</b>'])->get(route('public-deck'))->assertOk()
        ->assertSee('&lt;b&gt;bold&lt;/b&gt;', false)
        ->assertDontSee('<b>bold</b>', false);
});

// --- Polish from the foundation review ---------------------------------------------------------

/** The part of the page inside the main navigation landmark. */
function mainNav(string $html): string
{
    $start = strpos($html, '<nav aria-label="Main"');

    return substr($html, $start, strpos($html, '</nav>', $start) - $start);
}

test('the mobile menu is part of the main navigation and has the account links', function () {
    expect(mainNav($this->get(route('public-deck'))->getContent()))
        ->toContain('id="mobileMenu"')
        ->toContain('href="'.route('register').'"');

    expect(mainNav($this->actingAs(User::factory()->create())->get(route('decks'))->getContent()))
        ->toContain('id="mobileMenu"')
        ->toContain('href="'.route('decks').'"');
});

test('menus close with Escape and give focus back', function () {
    $html = $this->actingAs(User::factory()->create())->get(route('decks'))->getContent();

    expect($html)
        ->toContain('x-ref="button"')
        ->toContain('$refs.button.focus()')
        ->toContain('@keydown.escape.window="menuOpen = false"');
});

test('the theme toggle is labelled by the state it switches', function () {
    $this->get(route('public-deck'))->assertSee('aria-label="Dark mode"', false);
});

test('header links have a full-size touch target', function () {
    expect($this->get(route('public-deck'))->getContent())->toMatch('/data-nav="cards"[^>]*min-h-11/');
});

test('the footer stays at the bottom of short pages', function () {
    $this->get(route('login'))->assertSee('<main id="content" class="flex-1', false);
});

test('logged in users get a link to their decks', function () {
    $this->actingAs(User::factory()->create())->get(route('decks'))
        ->assertSee('href="'.route('decks').'"', false);
});

test('the dashboard has no game switch', function () {
    Http::fake(['api.scryfall.com/cards/random' => Http::response(['id' => '00000000-0000-0000-0000-000000000001', 'name' => 'Random Magic Card'])]);

    $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()->assertDontSee('data-game-switch', false);
});

test('the logo goes to the welcome page for guests and to the dashboard when logged in', function () {
    $logo = '/<a href="([^"]*)" class="focus-ring flex items-center gap-2\.5 rounded-lg">/';

    preg_match($logo, $this->get(route('public-deck'))->getContent(), $guest);
    preg_match($logo, $this->actingAs(User::factory()->create())->get(route('public-deck'))->getContent(), $member);

    expect($guest[1])->toBe(route('index'))
        ->and($member[1])->toBe(route('dashboard'));
});

test('the page knows when JS runs, so collapsed content stays visible without it', function () {
    $this->get(route('public-deck'))->assertOk()
        ->assertSee('document.documentElement.classList.add("js");', false);

    expect(file_get_contents(resource_path('css/app.css')))
        ->toContain('.js [data-collapsed="true"]')
        ->toContain('.js .no-js-only');
});
