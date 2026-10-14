<?php

use App\Models\YugiohCard;
use App\Models\YugiohPrinting;
use App\Models\YugiohSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $set = YugiohSet::factory()->create(['code' => 'LOB', 'name' => 'Legend of Blue Eyes White Dragon', 'slug' => 'legend-of-blue-eyes-white-dragon',
        'card_count' => 126, 'released_at' => '2002-03-08', 'image_url' => null]);
    YugiohCard::factory()->fromCard(['id' => 46986414, 'name' => 'Dark Magician', 'type' => 'Normal Monster', 'race' => 'Spellcaster'])->create();
    YugiohCard::factory()->fromCard(['id' => 89631139, 'name' => 'Blue-Eyes White Dragon', 'race' => 'Dragon'])->create();
    YugiohPrinting::create(['yugioh_card_id' => 46986414, 'yugioh_set_id' => $set->id, 'code' => 'LOB-EN005', 'rarity' => 'Ultra Rare']);
    YugiohPrinting::create(['yugioh_card_id' => 89631139, 'yugioh_set_id' => $set->id, 'code' => 'LOB-EN001', 'rarity' => 'Secret Rare']);
});

function lobPage(array $query = []): string
{
    return test()->get(route('packs.show', ['yugioh', 'LOB', 'legend-of-blue-eyes-white-dragon', ...$query]))->assertOk()->getContent();
}

test('the page has a breadcrumb, the eyebrow and the heading', function () {
    expect(lobPage())
        ->toContain('<nav aria-label="Breadcrumb"')
        ->toContain('href="'.route('packs.index', 'yugioh').'"')
        ->toContain('Yu-Gi-Oh! packs')
        ->toContain('<span aria-current="page" class="text-ink">Legend of Blue Eyes White Dragon</span>')
        ->toContain('<p class="eyebrow">Yu-Gi-Oh! pack</p>')
        ->toContain('data-size="lg"');
});

test('the facts are stat boxes, with no type for yugioh', function () {
    expect(lobPage())
        ->toMatch('/>Code<\/dt>\s*<dd[^>]*>LOB<\/dd>/')
        ->toMatch('/>Cards<\/dt>\s*<dd[^>]*>126<\/dd>/')
        ->toMatch('/>Released<\/dt>\s*<dd[^>]*>Mar 8, 2002<\/dd>/')
        ->not->toContain('>Type</dt>');
});

test('the cards are tiles with their name and rarity for filtering', function () {
    expect(lobPage())
        ->toContain('data-name="dark magician" data-rarity="Ultra Rare"')
        ->toContain('<span class="truncate text-xs text-muted">Normal Monster')
        ->toContain('<span class="rarity-pill max-w-full self-start" title="Ultra Rare"><span class="min-w-0 truncate">Ultra Rare</span></span>')
        ->toContain('<option value="Secret Rare"')
        ->toContain('>2 cards</p>')
        ->toMatch('/data-no-matches x-ref="noMatches"\s+hidden/');
});

test('the name filter hides the other cards on the server', function () {
    expect(lobPage(['name' => 'DARK']))
        ->toContain('value="DARK"')
        ->toContain('>1 of 2 cards</p>')
        ->toMatch('/data-name="blue-eyes white dragon" data-rarity="Secret Rare"\s+hidden/')
        ->not->toMatch('/data-name="dark magician" data-rarity="Ultra Rare"\s+hidden/');
});

test('the rarity filter matches exactly and stays selected', function () {
    expect(lobPage(['rarity' => 'Ultra Rare']))
        ->toContain('<option value="Ultra Rare" selected')
        ->toContain('>1 of 2 cards</p>');
});

test('a filter that matches nothing says so', function () {
    expect(lobPage(['name' => 'zzz']))
        ->toContain('>0 of 2 cards</p>')
        ->toContain('No cards match. Try another name or rarity.')
        ->not->toMatch('/data-no-matches x-ref="noMatches"\s+hidden/');
});

test('array filter values are ignored', function () {
    expect(lobPage(['name' => ['dark']]))->toContain('>2 cards</p>');
});

test('a pack without cards shows the empty state instead of the filter', function () {
    YugiohSet::factory()->create(['code' => 'EMP', 'name' => 'Empty Pack', 'slug' => 'empty-pack']);

    $this->get(route('packs.show', ['yugioh', 'EMP', 'empty-pack']))->assertOk()
        ->assertSee('No cards in this pack yet')
        ->assertDontSee('Filter cards by name');
});

test('a magic pack has a type fact and capitalised rarities', function () {
    Http::fake([
        'api.scryfall.com/sets' => Http::response(['data' => [[
            'code' => 'dom', 'name' => 'Dominaria', 'released_at' => '2018-04-27', 'card_count' => 280,
            'set_type' => 'expansion', 'icon_svg_uri' => 'https://svgs.scryfall.io/sets/dom.svg',
        ]]]),
        'api.scryfall.com/cards/search*' => Http::response(['data' => [[
            'id' => '11111111-1111-4111-8111-111111111111', 'name' => 'Llanowar Elves', 'type_line' => 'Creature — Elf Druid',
            'rarity' => 'common', 'set' => 'dom', 'set_name' => 'Dominaria',
        ]], 'total_cards' => 1, 'has_more' => false]),
    ]);

    expect($this->get(route('packs.show', ['magic', 'dom']))->assertOk()->getContent())
        ->toMatch('/>Type<\/dt>\s*<dd[^>]*>Expansion<\/dd>/')
        ->toContain('<option value="common"')
        ->toContain('>Common</option>')
        ->toContain('data-rarity="common"');
});

test('the filter runs live with the pack cards component', function () {
    expect(lobPage())
        ->toContain('x-data="packCards"')
        ->toContain('x-ref="form" @submit.prevent="filter()"')
        ->toContain('@input="filter()"')
        ->toContain('@change="filter()"')
        ->toContain('class="btn-secondary no-js-only">Filter</button>')
        ->toContain('x-text="countLabel"')
        ->toContain('x-ref="grid"')
        ->toContain('x-ref="noMatches"');
});
