<?php

use App\Models\YugiohSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function fakeMagicSets(): void
{
    Http::fake(['api.scryfall.com/sets' => Http::response(['data' => [[
        'code' => 'dom', 'name' => 'Dominaria', 'released_at' => '2018-04-27', 'card_count' => 280,
        'set_type' => 'expansion', 'icon_svg_uri' => 'https://svgs.scryfall.io/sets/dom.svg',
    ]]])]);
}

beforeEach(function () {
    YugiohSet::factory()->create(['code' => 'LOB', 'name' => 'Legend of Blue Eyes White Dragon', 'slug' => 'legend-of-blue-eyes-white-dragon',
        'card_count' => 126, 'released_at' => '2002-03-08', 'image_url' => 'https://images.ygoprodeck.com/images/sets/LOB.jpg']);
    YugiohSet::factory()->create(['code' => 'MRD', 'name' => 'Metal Raiders', 'slug' => 'metal-raiders',
        'card_count' => 144, 'released_at' => '2002-06-26', 'image_url' => null]);
});

test('the page has the eyebrow, the heading and one game switch', function () {
    $html = $this->get(route('packs.index', 'yugioh'))->assertOk()->getContent();

    expect($html)->toContain('<p class="eyebrow">Sets &amp; releases</p>')
        ->toContain('Yu-Gi-Oh! packs</h1>')
        ->and(substr_count($html, 'data-game-switch'))->toBe(1);
});

test('the full view shows each pack as a tile with its cover, meta line and card count', function () {
    $html = $this->get(route('packs.index', 'yugioh'))->getContent();

    expect($html)->toMatch('/data-view="full"\s+aria-current="page"/')
        ->toContain('href="'.url('/yugioh/pack/LOB/legend-of-blue-eyes-white-dragon').'" class="pack-tile')
        ->toContain('LOB · Released Mar 8, 2002')
        ->toContain('>126</span>')
        ->toContain('data-kind="art"')
        ->toContain('data-kind="code"')
        ->not->toMatch('/<a [^>]*(pack-tile[^>]*target="_blank"|target="_blank"[^>]*pack-tile)/');
});

test('the list view is a table, without a type column for yugioh', function () {
    $html = $this->get(route('packs.index', ['yugioh', 'view' => 'list']))->getContent();

    expect($html)->toContain('<table')
        ->toContain('<input type="hidden" name="view" value="list">')
        ->toContain('data-size="xs"')
        ->not->toContain('>Type</th>');
});

test('magic packs show their type and set symbol', function () {
    fakeMagicSets();

    $html = $this->get(route('packs.index', ['magic', 'view' => 'list']))->assertOk()->getContent();

    expect($html)->toContain('>Type</th>')
        ->toContain('>Expansion</td>')
        ->toContain('data-kind="symbol"');
});

test('a search says how many packs match and can be cleared; view links keep it', function () {
    $html = $this->get(route('packs.index', ['yugioh', 'search' => 'metal']))->getContent();

    expect($html)->toContain('1 pack matching “metal”')
        ->toContain('href="'.route('packs.index', 'yugioh').'" class="focus-ring rounded-sm font-bold text-accent-ink">Clear search</a>')
        ->toContain('href="'.e(route('packs.index', ['yugioh', 'view' => 'list', 'search' => 'metal'])).'"')
        ->toContain('value="metal"')
        ->not->toContain('Legend of Blue Eyes');
});

test('an array search is ignored', function () {
    $this->get(route('packs.index', ['yugioh', 'search' => ['metal']]))->assertOk()->assertSee('Metal Raiders');
});

test('no matches show the empty state with clear search', function () {
    $this->get(route('packs.index', ['yugioh', 'search' => 'zzzz']))->assertOk()
        ->assertSee('No packs found')
        ->assertSee('Try another name or code.')
        ->assertSee('class="btn-secondary">Clear search</a>', false);
});

test('pages are numbered squares with the current one marked', function () {
    YugiohSet::factory()->count(30)->create();

    expect($this->get(route('packs.index', 'yugioh'))->getContent())
        ->toContain('<nav aria-label="Pagination"')
        ->toContain('<span aria-current="page" class="page-link">1</span>')
        ->toContain('aria-label="Next page"')
        ->not->toContain('aria-label="Previous page"')
        ->not->toContain('relative inline-flex items-center');

    $this->get(route('packs.index', ['yugioh', 'page' => 2]))
        ->assertSee('<span aria-current="page" class="page-link">2</span>', false)
        ->assertSee('aria-label="Previous page"', false);
});
